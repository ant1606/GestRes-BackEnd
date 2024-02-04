<?php

  namespace Tests\Feature\Authentication;

  use App\Models\User;
  use Illuminate\Foundation\Testing\RefreshDatabase;
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
      $response->assertJson(
        fn(AssertableJson $json) => $json
          ->whereType('status', 'string')
          ->whereType('code', 'integer')
          ->whereType('data', 'array')
          ->whereType('data.bearer_token', 'string')
          ->whereType('data.bearer_expire', 'string')
          ->where('data.user.remember_token', null)
      );

    }

    /** @test * */
    public function user_can_login_with_remember()
    {
      User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

      $response = $this->postJson(route('login'), ['password' => 'password', 'email' => 'admin@mail.com', 'remember_me' => true]);

      $response->assertStatus(Response::HTTP_OK);
      $response->assertJson(
        fn(AssertableJson $json) => $json
          ->whereType('status', 'string')
          ->whereType('code', 'integer')
          ->whereType('data', 'array')
          ->whereType('data.bearer_token', 'string')
          ->whereType('data.bearer_expire', 'string')
          ->whereType('data.user.remember_token', 'string')
      );
    }

    /** @test * */
    public function user_can_not_login_without_email()
    {
      User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

      $response = $this->postJson(route('login'), ['password' => 'password', 'email' => '', 'remember_me' => true]);

      $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
      $response->assertJsonStructure([
        'status',
        'code',
        'error' => [
          'message',
          'details' => ['email']
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
        'status',
        'code',
        'error' => [
          'message',
          'details' => ['password']
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
        'status',
        'code',
        'error' => [
          'message',
          'details'
        ]
      ]);
      $response->assertJson(
        fn(AssertableJson $json) => $json
          ->has('status')
          ->has('code')
          ->hasAll(['error.details.password', 'error.details.email'])
      );
    }

    /** @test * */
    public function user_can_not_login_if_does_not_exists()
    {
//      $this->withoutExceptionHandling();
      User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

      $response = $this->postJson(route('login'), ['password' => 'falsypassword', 'email' => 'falsyemail@mail.com', 'remember_me' => false]);

      $response->assertStatus(Response::HTTP_UNAUTHORIZED);

      $response->assertJsonStructure([
        'status',
        'code',
        'error' => [
          'message',
          'details'
        ]
      ]);
      $response->assertJson(
        fn(AssertableJson $json) => $json
          ->has('status')
          ->has('code')
          ->has('error')
          ->has('error.message')
          ->missingAll(['error.details.password', 'error.details.email'])
      );
      $response->assertJsonFragment(['message' => 'Usuario no autentificado']);
    }
  }
