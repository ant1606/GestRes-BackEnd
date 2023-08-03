<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationController extends ApiController
{
  public function notify(Request $request): JsonResponse
  {
    return $this->showMessage("Se debe verificar el correo electrónico", Response::HTTP_OK);
  }

  public function verify(Request $request, $id, $hash,): JsonResponse
  {
    //TODO Verificar si el email ya ha sido verificado para brindar una respuesta adecuada
    $usuario = User::find($id);

    if ($usuario !== null) {
      $usuario->markEmailAsVerified();

      $token_expiring_date = date_create("now")->add(new \DateInterval('PT6H'));
      $token = $usuario->createToken("API-TOKEN", ['*'], $token_expiring_date);

      return $this->showOne([
        "bearer_token" => $token->plainTextToken,
        "bearer_expire" => $token_expiring_date->format(\DateTimeInterface::RFC7231),
        "user" => [
          "id" => $usuario->id,
          "name" => $usuario->name,
          "email" => $usuario->email,
          "remember_token" => $usuario->getRememberToken(),
          "is_verified" =>  $usuario->hasVerifiedEmail()
        ]
      ], Response::HTTP_OK);
    }
    return $this->errorResponse(["api_response" => ["No se encontró al usuario"]], Response::HTTP_UNAUTHORIZED);
  }

  public function resendLinkVerification(Request $request)
  {
    $request->user()->sendEmailVerificationNotification();
    return $this->showMessage([
      "message" => "Se reenvío el link de verificación"
    ], Response::HTTP_OK);
  }
}
