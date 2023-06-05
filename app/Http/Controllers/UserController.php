<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      //TODO ENcerrar en un trycatch
      $validated = $request->validate([
        'name' => 'required',
        'email' => 'required|unique:users',
        'password'=> 'required|confirmed',
      ]);

      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password'=> Hash::make($request->password),
      ]);

      event(new Registered($user));

      $token_expiring_date = date_create("now")->add(new \DateInterval('PT6H'));
      $token = $user->createToken("API-TOKEN",['*'], $token_expiring_date);

      return $this->showOne([
        "bearer_token"=>$token->plainTextToken,
        "bearer_expire"=>$token_expiring_date->format(\DateTimeInterface::RFC7231),
        "user" => [
          "name" => $user->name,
          "email" => $user->email,
        ]
      ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
