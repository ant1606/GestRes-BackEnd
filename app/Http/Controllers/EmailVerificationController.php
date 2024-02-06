<?php

  namespace App\Http\Controllers;

  use App\Http\Services\AuthenticationService;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Hacer test de este controlador
  class EmailVerificationController extends ApiController
  {

    public function __construct(protected AuthenticationService $authenticationService)
    {
    }

    public function notify(Request $request): JsonResponse
    {
      return $this->sendMessage("Se debe verificar el correo electrónico", Response::HTTP_OK);
    }

    public function verify(Request $request, $id, $hash): JsonResponse
    {
      $data = $this->authenticationService->verify_email_user($id);

      return !empty($data)
        ? $this->sendResponse($data, Response::HTTP_OK, false)
        : $this->sendError(Response::HTTP_UNAUTHORIZED, "No se encontró al usuario");

    }

    public function resendLinkVerification(Request $request): JsonResponse
    {
      $request->user()->sendEmailVerificationNotification();
      return $this->sendMessage(
        "Se reenvío el link de verificación"
        , Response::HTTP_OK);
    }
  }
