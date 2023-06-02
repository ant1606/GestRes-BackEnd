<?php

namespace Tests\Feature\Authentication;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function user_can_login(){
    $this->withoutExceptionHandling();
    //Creo un usuario en la BD
    $user = User::factory()->create(['name'=>'admin', 'email'=>'admin@mail.com']);
//    dd($user);
    //Intento hacer login con el usuario
    $response = $this->postJson(route('login'), ['password'=>'password', 'email'=>'admin@mail.com']);

    //TODO AQUI DEBO USAR EL TOKEN Y SANCTUM
    //Espero una respuesta OK por parte del servidor
    //Espero el token -- Usar Sanctum
    $response->assertStatus(Response::HTTP_OK);
  }
}
