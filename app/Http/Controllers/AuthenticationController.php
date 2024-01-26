<?php

  namespace App\Http\Controllers;

  use App\Models\User;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;
  use Symfony\Component\HttpFoundation\Response;

  /**
   * @OA\Info(
   *      version="1.0.0",
   *      title="AuthenticationController",
   *      description="Controller que gestiona el login del usuario al API",
   * )
   * @OA\Server(url="http://apiparaprincipiantesv10.test:8081")
   */
  class AuthenticationController extends ApiController
  {
    /**
     * @OA\Get(
     *     path="/user/logout",
     *     tags={"user"},
     *     summary="Logs out current logged in user session",
     *     operationId="logoutUser",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
      $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
      ]);

      if (Auth::attempt($credentials, $request->get("remember_me"))) {

        $usuario = Auth::user();
        $token_expiring_date = date_create("now")->add(new \DateInterval('PT6H'));
        $token = $usuario->createToken("API-TOKEN", ['*'], $token_expiring_date);
        return $this->sendResponse(
          [
            "bearer_token" => $token->plainTextToken,
            "bearer_expire" => $token_expiring_date->format(\DateTimeInterface::RFC7231),
            "user" => [
              "id" => $usuario->id,
              "name" => $usuario->name,
              "email" => $usuario->email,
              "remember_token" => $usuario->remember_token,
              "is_verified" => $usuario->hasVerifiedEmail()
            ]
          ],
          Response::HTTP_OK,
          false
        );
      }

      return $this->sendError(
        Response::HTTP_UNAUTHORIZED,
        "Usuario no autentificado"
      );
    }

    public function check_remember(Request $request): JsonResponse
    {
      //TODO Hacer Tests de esta funcionalidad
      $credentials = $request->validate([
        'remember_me' => ['required'],
      ]);
      $usuario = User::where("remember_token", $request->get("remember_me"))->first();
      if ($usuario) {
        $token_expiring_date = date_create("now")->add(new \DateInterval('P1DT6H'));
        $token = $usuario->createToken("API-TOKEN", ['*'], $token_expiring_date);

        return $this->sendResponse(
          [
            "bearer_token" => $token->plainTextToken,
            "bearer_expire" => $token_expiring_date->format(\DateTimeInterface::RFC7231),
            "user" => [
              "id" => $usuario->id,
              "name" => $usuario->name,
              "email" => $usuario->email,
              "remember_token" => $usuario->remember_token,
              "is_verified" => $usuario->hasVerifiedEmail()
            ]
          ],
          Response::HTTP_OK,
          false
        );
      }

      return $this->sendError(
        Response::HTTP_UNAUTHORIZED,
        "Usuario no autentificado"
      );
    }

    public function logout(Request $request): JsonResponse
    {
      try {
        $usuario = Auth::user();
        $usuario->remember_token = null;
        $usuario->tokens()->delete();
        $usuario->save();

        // Auth::logout();
        return $this->sendMessage(
          "Se cerro la sesión correctamente",
          Response::HTTP_OK
        );
      } catch (\Throwable $th) {
        // TODO: Generar Log de $th
        return $this->sendError(
          Response::HTTP_NOT_FOUND,
          "Ocurrió un problema al cerrar sesión"
        );
      }
    }
  }
