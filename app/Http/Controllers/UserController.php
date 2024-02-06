<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\UserPostRequest;
  use App\Http\Services\UserService;
  use Symfony\Component\HttpFoundation\Response;

  class UserController extends ApiController
  {
    public function __construct(protected UserService $userService)
    {
    }

    public function store(UserPostRequest $request)
    {
      $this->userService->save_user(
        $request->input('name'),
        $request->input('email'),
        $request->input('password'),
      );

      return $this->sendMessage("Registro satisfactorio", Response::HTTP_OK);
    }
  }
