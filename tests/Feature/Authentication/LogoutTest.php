<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LogoutTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function user_can_logout(){
    $user = User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->actingAs($user)->postJson(route('logout'));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
      'status',
      'code',
      'message'
    ]);
    $response->assertJsonFragment(['message' => 'Se cerro la sesión correctamente']);
  }

  /** @test **/
  public function user_can_not_logout_if_attempt_logout_with_other_user(){
    $this->markTestSkipped();
    //TODO Hacer este test, eliminando el token de autentificación o mockeando la clase para retornar el bloque catch
    $user = User::factory()->create(['name' => 'admin', 'email' => 'admin@mail.com']);

    $response = $this->actingAs($user)->postJson(route('logout'));

    $response->assertStatus(Response::HTTP_NOT_FOUND);
    $response->assertJsonStructure([
      'status',
      'code',
      'error' => [
        'message',
      ],
    ]);
    $response->assertJsonFragment(['message' => 'Todo esta correcto']);
  }

}
