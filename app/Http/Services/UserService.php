<?php

  namespace App\Http\Services;
  use App\Models\User;
  use Illuminate\Auth\Events\Registered;
  use Illuminate\Support\Facades\Hash;

  class UserService
  {
    public function save_user(string $name, string $email, string $password)
    {
      $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
      ]);

      event(new Registered($user));
//      $token_expiring_date = Carbon::now()->add(new \DateInterval('PT6H'));
//      $token = $user->createToken("API-TOKEN", ['*'], $token_expiring_date);
      return $user;
    }
  }
