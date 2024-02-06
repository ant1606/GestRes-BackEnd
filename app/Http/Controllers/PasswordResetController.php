<?php

  namespace App\Http\Controllers;

  use App\Http\Services\PasswordResetService;
  use Exception;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Hacer test de este controlador
  class PasswordResetController extends ApiController
  {

    public function __construct(protected PasswordResetService $passwordResetService)
    {
    }

    /**
     * @throws Exception
     */
    public function forgotPassword(Request $request): JsonResponse
    {
      $request->validate(['email' => 'required|email']);

      $data = $this->passwordResetService->send_link_to_reset_password($request->input('email'));
      return $data
        ? $this->sendMessage("Se envió el link para reseteo de link a su correo", Response::HTTP_OK)
        : $this->sendError(Response::HTTP_BAD_GATEWAY, "Hubo un problema");
    }

    public function resetPassword(Request $request): JsonResponse
    {
      $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
      ]);

      $data = $this->passwordResetService->reset_password(
        $request->input('token'),
        $request->input('email'),
        $request->input('password'),
        $request->input('password_confirmation'),
      );

      return $data
        ? $this->sendMessage("Se actualizó su contraseña", Response::HTTP_OK)
        : $this->sendError(Response::HTTP_BAD_GATEWAY, "Hubo un problema");
    }
  }
