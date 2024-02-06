<?php

  namespace App\Http\Services;

  use App\Models\User;
  use Carbon\Carbon;
  use DateInterval;
  use DateTimeInterface;
  use Exception;
  use Illuminate\Support\Facades\Auth;
  use Throwable;

  class AuthenticationService
  {

    public function login(array $credentials, bool $remember_me): array
    {
      if (Auth::attempt($credentials, $remember_me)) {
        $usuario = Auth::user();
        return $this->generate_credentials_user($usuario);
      }
      return [];
    }

    public function check_remember_token(string $remember_token): array
    {
      $usuario = User::where("remember_token", $remember_token)->first();
      if ($usuario) {
        return $this->generate_credentials_user($usuario);
      }
      return [];
    }

    public function verify_email_user(int $id_user): array
    {
      $usuario = User::find($id_user);
      //TODO Verificar si el email ya ha sido verificado para brindar una respuesta adecuada
      if ($usuario !== null) {
        $usuario->markEmailAsVerified();
        return $this->generate_credentials_user($usuario);
      }
      return [];
    }

    /**
     * @throws Exception
     */
    public function logout($user): bool
    {
      try {
        $user->remember_token = null;
        $user->tokens()->delete();
        $user->save();
        return true;
      } catch (Throwable $th) {
        // TODO: Generar Log de $th
        throw new Exception("Ocurrió un problema al cerrar sesión");
      }
    }

    private function generate_credentials_user($user): array
    {
      $token_expiring_date = Carbon::now()->add(new DateInterval('PT6H'));
      $token = $user->createToken("API-TOKEN", ['*'], $token_expiring_date);
      return [
        "bearer_token" => $token->plainTextToken,
        "bearer_expire" => $token_expiring_date->format(DateTimeInterface::RFC7231),
        "user" => [
          "id" => $user->id,
          "name" => $user->name,
          "email" => $user->email,
          "remember_token" => $user->remember_token,
          "is_verified" => $user->hasVerifiedEmail()
        ]
      ];
    }
  }
