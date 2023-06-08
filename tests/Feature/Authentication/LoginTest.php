<?php

namespace Tests\Feature\Authentication;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  /** @test * */
  public function user_can_login_without_remember()
  {
    User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->postJson(route('login'), ['password' => 'password', 'email' => 'admin@mail.com', 'remember_me' => false]);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(fn(AssertableJson $json) => $json->whereAllType([
      'data.bearer_token' => 'string',
      'data.bearer_expire' => 'string',
      'data.user.remember_token' => 'null'
    ])
    );
  }

  /** @test * */
  public function user_can_login_with_remember()
  {
    User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->postJson(route('login'), ['password' => 'password', 'email' => 'admin@mail.com', 'remember_me' => true]);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(fn(AssertableJson $json) => $json->whereType('data.user.remember_token', 'string')
    );
  }

  /** @test * */
  public function user_can_not_login_without_email()
  {
    User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->postJson(route('login'), ['password' => 'password', 'email' => '', 'remember_me' => true]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
//    dd($response->getContent());
    $response->assertJsonStructure([
      'error' => [
        [
          'status',
          'detail' => ['email']
        ]
      ]
    ]);
  }

  /** @test * */
  public function user_can_not_login_without_password()
  {
    User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->postJson(route('login'), ['password' => '', 'email' => 'admin@mail.com', 'remember_me' => true]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonStructure([
      'error' => [
        [
          'status',
          'detail' => ['password']
        ]
      ]
    ]);
  }

  /** @test * */
  public function user_can_not_login_without_credentials()
  {
    User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->postJson(route('login'), ['password' => '', 'email' => '', 'remember_me' => false]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonStructure([
      'error' => [
        [
          'status',
          'detail' => ['password', 'email']
        ]
      ]
    ]);
  }

  /** @test * */
  public function user_can_not_login_if_does_not_exists()
  {
    $this->withoutExceptionHandling();
    User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->postJson(route('login'), ['password' => 'falsypassword', 'email' => 'falsyemail@mail.com', 'remember_me' => false]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
//    dd($response->getContent());
    $response->assertJsonStructure([
      'error' => [
        [
          'status',
          'detail' => ['api_response']
        ]
      ]
    ]);


  }


}
