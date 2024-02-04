<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends ApiController
{

  public function forgotPassword(Request $request)
  {
    $request->validate(['email' => 'required|email']);

    if (!User::where('email', $request->get('email'))->first())
      return $this->sendError(Response::HTTP_BAD_GATEWAY, "No se encontró al usuario", ["email" => ["No se encuentra el usuario"]]);

    $status = Password::sendResetLink(
      $request->only('email'),
    );

    return $status === Password::RESET_LINK_SENT
      ? $this->sendMessage("Se envió el link para reseteo de link a su correo", Response::HTTP_OK)
      : $this->sendError(Response::HTTP_BAD_GATEWAY, "Hubo un problema");
  }

  public function resetPassword(Request $request)
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    return $status === Password::PASSWORD_RESET
      ? $this->sendMessage("Se actualizó su contraseña", Response::HTTP_OK)
      : $this->sendError(Response::HTTP_BAD_GATEWAY, "Hubo un problema");
  }
}
