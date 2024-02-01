<?php

  namespace App\Http\Controllers;

  use App\Models\User;
  use DateInterval;
  use DateTimeInterface;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;
  use OpenApi\Annotations as OA;
  use Symfony\Component\HttpFoundation\Response;
  use Throwable;


  class AuthenticationController extends ApiController
  {

    /**
     * Login de usuario
     * @OA\Post(
     *    path="/login",
     *    operationId="Login",
     *    tags={"Authentication"},
     *    summary="Login user",
     *    description="Login user",
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"email", "password", "remember_me"},
     *          @OA\Property(
     *            property="email",
     *            type="string"
     *          ),
     *          @OA\Property(
     *            property="password",
     *            type="string"
     *          ),
     *          @OA\Property(
     *            property="remember_me",
     *            type="boolean"
     *          ),
     *          example={"email":"mimail@example.com","password":"abcxd232sx","remember_me":false}
     *        )
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(property="bearer_token", type="string", example="41|ylunYCnOo71w2xzckQEnnXLq4m1Qc6HG5JqbJmkZgd4b82742"),
     *          @OA\Property(property="bearer_expire", type="string", example="Wed, 31 Jan 2024 23:16:03 GMT"),
     *          @OA\Property(
     *            property="user",
     *            type="object",
     *            @OA\Property(property="id", type="number", example=1),
     *            @OA\Property(property="name", type="string", example="Dummy User"),
     *            @OA\Property(property="email", type="string", example="dummye@email.com"),
     *            @OA\Property(property="remember_token", type="string", example=null),
     *            @OA\Property(property="is_verified", type="boolean", example=true)
     *          ),
     *        ),
     *      )
     *    ),
     *    @OA\Response(
     *      response="401",
     *      description="User unauthenticated",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="error"),
     *        @OA\Property(property="code", type="number", example=401),
     *        @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(property="message", type="string", example="Usuario no autentificado"),
     *          @OA\Property(property="details", type="object", example="[]")
     *        ),
     *      )
     *    ),
     *    @OA\Response(
     *      response="422",
     *      description="Validation Exception",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="error"),
     *        @OA\Property(property="code", type="number", example=422),
     *        @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(property="message", type="string", example=""),
     *          @OA\Property(
     *            property="details",
     *            type="object",
     *            @OA\Property(
     *              property="password",
     *              type="array",
     *              @OA\Items(type="string", description="The password field is required.", example="The password field is required.")
     *            ),
     *          )
     *        ),
     *      )
     *    )
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
        $token_expiring_date = date_create("now")->add(new DateInterval('PT6H'));
        $token = $usuario->createToken("API-TOKEN", ['*'], $token_expiring_date);
        return $this->sendResponse(
          [
            "bearer_token" => $token->plainTextToken,
            "bearer_expire" => $token_expiring_date->format(DateTimeInterface::RFC7231),
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

    /**
     * Verificando token remember_me
     * @OA\Post(
     *    path="/remember",
     *    operationId="RememberMe",
     *    tags={"Authentication"},
     *    summary="Verificando token remember_me de usuario autentificado",
     *    description="Verificando token remember_me de usuario autentificado",
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          required={"remember_me"},
     *          @OA\Property(
     *            property="remember_me",
     *            type="string"
     *          ),
     *          example={"remember_me":"lunYCnOo71w2xzckQEnnXLq4m1Qc6HG5JqbJm$%kxcjq412"}
     *        )
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="code", type="number", example=200),
     *        @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(property="bearer_token", type="string", example="41|ylunYCnOo71w2xzckQEnnXLq4m1Qc6HG5JqbJmkZgd4b82742"),
     *          @OA\Property(property="bearer_expire", type="string", example="Wed, 31 Jan 2024 23:16:03 GMT"),
     *          @OA\Property(
     *            property="user",
     *            type="object",
     *            @OA\Property(property="id", type="number", example=1),
     *            @OA\Property(property="name", type="string", example="Dummy User"),
     *            @OA\Property(property="email", type="string", example="dummye@email.com"),
     *            @OA\Property(property="remember_token", type="string", example="lunYCnOo71w2xzckQEnnXLq4m1Qc6HG5JqbJm$%kxcjq412"),
     *            @OA\Property(property="is_verified", type="boolean", example=true)
     *          ),
     *        ),
     *      )
     *    ),
     *    @OA\Response(
     *      response="401",
     *      description="User unauthenticated",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="error"),
     *        @OA\Property(property="code", type="number", example=401),
     *        @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(property="message", type="string", example="Usuario no autentificado"),
     *          @OA\Property(property="details", type="object", example="[]")
     *        ),
     *      )
     *    ),
     *    @OA\Response(
     *      response="422",
     *      description="Validation Exception",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="error"),
     *        @OA\Property(property="code", type="number", example=422),
     *        @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(property="message", type="string", example=""),
     *          @OA\Property(
     *            property="details",
     *            type="object",
     *            @OA\Property(
     *              property="remember_me",
     *              type="array",
     *              @OA\Items(type="string", description="The remember me field is required.", example="The remember me field is required.")
     *            ),
     *          )
     *        ),
     *      )
     *    )
     * )
     */
    public function check_remember(Request $request): JsonResponse
    {
      //TODO Hacer Tests de esta funcionalidad
      $credentials = $request->validate([
        'remember_me' => ['required'],
      ]);
      $usuario = User::where("remember_token", $request->get("remember_me"))->first();
      if ($usuario) {
        $token_expiring_date = date_create("now")->add(new DateInterval('P1DT6H'));
        $token = $usuario->createToken("API-TOKEN", ['*'], $token_expiring_date);

        return $this->sendResponse(
          [
            "bearer_token" => $token->plainTextToken,
            "bearer_expire" => $token_expiring_date->format(DateTimeInterface::RFC7231),
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

    /**
     * Logout del usuario autentificado
     * @OA\Post(
     *    path="/logout",
     *    operationId="Logout",
     *    tags={"Authentication"},
     *    summary="LogoOut current user authenticated",
     *    description="LogoOut current user authenticated",
     *    @OA\Response(
     *       response="200",
     *       description="Se cerro la sesión correctamente",
     *       @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="code", type="number", example=200),
     *         @OA\Property(property="message", type="string", example="Se cerro la sesión correctamente"),
     *       )
     *    ),
     *    @OA\Response(
     *      response="401",
     *      description="User unauthenticated",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="error"),
     *        @OA\Property(property="code", type="number", example=401),
     *        @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(property="message", type="string", example="No esta autorizado para continuar"),
     *          @OA\Property(property="details", type="object", example="[]")
     *        ),
     *      )
     *    ),
     *    @OA\Response(
     *      response="404",
     *      description="Error closing session user authenticated",
     *      @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="error"),
     *        @OA\Property(property="code", type="number", example=404),
     *        @OA\Property(
     *          property="error",
     *          type="object",
     *          @OA\Property(property="message", type="string", example="Ocurrió un problema al cerrar sesión"),
     *          @OA\Property(property="details", type="object", example="[]")
     *        ),
     *      )
     *    ),
     * )
     */
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
      } catch (Throwable $th) {
        // TODO: Generar Log de $th
        return $this->sendError(
          Response::HTTP_NOT_FOUND,
          "Ocurrió un problema al cerrar sesión"
        );
      }
    }
  }
