<?php

namespace App\Http\Controllers;

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
    $request->user()->markEmailAsVerified();
    return $this->showMessage("Se verifico el correo electrónico", Response::HTTP_OK);
  }

  public function resendLinkVerification(Request $request){
    $request->user()->sendEmailVerificationNotification();
    return $this->showMessage("Se reenvio el link de verificación", Response::HTTP_OK);
  }


}
