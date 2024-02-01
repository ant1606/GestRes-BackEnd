<?php

  namespace Tests\Feature\Authentication;

  use App\Models\User;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Illuminate\Testing\Fluent\AssertableJson;
  use Symfony\Component\HttpFoundation\Response;
  use Tests\TestCase;

  class CheckRememberTokenTest extends TestCase
  {
    use RefreshDatabase;

    /** @test * */
    public function get_credentials_of_user_if_remember_token_is_valid()
    {
      $user = User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);
      $this->postJson(route('login'), ['password' => 'password', 'email' => 'admin@mail.com', 'remember_me' => true]);
      $user = User::find($user->id);

      $response = $this->postJson(route('remember'), ['remember_token' => $user->remember_token]);

      $response->assertStatus(Response::HTTP_OK);
      $response->assertJson(
        fn(AssertableJson $json) => $json
          ->whereType('status', 'string')
          ->whereType('code', 'integer')
          ->whereType('data', 'array')
          ->whereType('data.bearer_token', 'string')
          ->whereType('data.bearer_expire', 'string')
          ->whereType('data.user.remember_token', 'string')
          ->whereNot('data.bearer_token', $user->bearer_token)
          ->whereNot('data.bearer_expire', $user->bearer_expire)
      );
    }

    /** @test * */
    public function can_not_get_credentials_when_send_invalid_remember_token(){
      User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);
      $this->postJson(route('login'), ['password' => 'password', 'email' => 'admin@mail.com', 'remember_me' => true]);

      $response = $this->postJson(route('remember'), ['remember_token' => "Invalid Token"]);

      $response->assertStatus(Response::HTTP_UNAUTHORIZED);
      $response->assertJsonStructure([
        "status",
        "code",
        "error"=>[
          "message",
          "details"
        ]
      ]);
      $response->assertJsonFragment(["message"=>"Usuario no autentificado"]);
    }

    /** @test * */
    public function user_can_not_refresh_token_access_without_remember_token(){
      User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);
      $this->postJson(route('login'), ['password' => 'password', 'email' => 'admin@mail.com', 'remember_me' => true]);

      $response = $this->postJson(route('remember'), ['remember_token' => ""]);

      $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
      $response->assertJsonStructure([
        "status",
        "code",
        "error"=>[
          "message",
          "details"=>["remember_token"]
        ]
      ]);
      $response->assertJsonFragment(["remember_token"=>["The remember token field is required."]]);
    }
  }
