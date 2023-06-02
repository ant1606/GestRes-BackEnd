<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends ApiController
{
    public function login(Request $request){

      $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
      ]);

      if (Auth::attempt($credentials, $request->get("remember_me"))) {
//        $request->session()->regenerate();

        $usuario = Auth::user();
        $token_expiring_date = date_create("now")->add(new \DateInterval('PT6H'));
        $token = $usuario->createToken("API-TOKEN",['*'], $token_expiring_date);

        return $this->showOne([
          "bearer_token"=>$token->plainTextToken,
          "bearer_expire"=>$token_expiring_date->format(\DateTimeInterface::RFC7231),
          "user" => [
            "name" => $usuario->name,
            "email" => $usuario->email,
            "remember_token" =>$usuario->getRememberToken()
          ]
        ], Response::HTTP_OK);
      }

      return  $this->errorResponse(Response::HTTP_NOT_FOUND, "Usuario no existe");
//
//      return back()->withErrors([
//        'email' => 'The provided credentials do not match our records.',
//      ])->onlyInput('email');

//
//      dd($request->only(['email', 'password']));
//
//      if(Auth::attempt($request->only(['email','password']))){
//        $usuario = User::where('email',$request->get('email'))->first();
//        return $this->showOne($usuario, Response::HTTP_OK);
//      }
//      return  $this->errorResponse(Response::HTTP_NOT_FOUND, "Usuario no existe");
    }
}
