<?php

  namespace App\Http\Controllers;

  use App\Models\User;
  use Illuminate\Auth\Events\Registered;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Hash;
  use Symfony\Component\HttpFoundation\Response;

  class UserController extends ApiController
  {
    public function store(Request $request)
    {
      //TODO ENcerrar en un trycatch
      $validated = $request->validate([
        'name' => 'required',
        'email' => 'required|unique:users',
        'password' => 'required|confirmed',
      ]);

      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
      ]);

      event(new Registered($user));

      //TODO ya no enviar el beareToekn ya que se verificar la validacion del correo al hacer login
      $token_expiring_date = date_create("now")->add(new \DateInterval('PT6H'));
      $token = $user->createToken("API-TOKEN", ['*'], $token_expiring_date);

      return $this->sendMessage(
        "Registro satisfactorio",
        Response::HTTP_OK);
    }
  }
