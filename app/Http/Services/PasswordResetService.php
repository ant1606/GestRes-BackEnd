<?php

  namespace App\Http\Services;

  use App\Models\User;
  use Exception;
  use Illuminate\Auth\Events\PasswordReset;
  use Illuminate\Support\Facades\Hash;
  use Illuminate\Support\Facades\Password;
  use Illuminate\Support\Str;
  use Symfony\Component\HttpFoundation\Response;

  class PasswordResetService
  {
    /**
     * @throws Exception
     */
    public function send_link_to_reset_password(string $email): bool
    {
      if (!User::where('email', $email)->first())
        throw new Exception("No se encontró al usuario", Response::HTTP_BAD_GATEWAY);
//      return $this->sendError(, "No se encontró al usuario", ["email" => ["No se encuentra el usuario"]]);

      $status = Password::sendResetLink(["email" => $email]);

      return $status === Password::RESET_LINK_SENT;
    }

    public function reset_password(string $token, string $email, string $password, string $password_confirmation): bool
    {
      $status = Password::reset([
          'email' => $email,
          'password' => $password,
          'password_confirmation' => $password_confirmation,
          'token' => $token
        ],
        function (User $user, string $password) {
          $user->forceFill([
            'password' => Hash::make($password)
          ])->setRememberToken(Str::random(60));

          $user->save();

          event(new PasswordReset($user));
        }
      );

      return $status === Password::PASSWORD_RESET;
    }
  }
