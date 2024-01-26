<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagRegistrationTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function tag_can_be_register()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    $tag = [
      "name" => "Etiqueta de prueba"
    ];

    $response = $this->actingAs($user)->postJson(route("tag.store"), $tag);

    $response->assertStatus(Response::HTTP_CREATED);
//     dd($response->getContent());
    $this->assertDatabaseCount("tags", 1);
    $this->assertDatabaseHas("tags", [
      "name" => Str::upper($tag['name']),
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "data" => [
        "identificador",
        "nombre",
        "estilos",
      ],
    ]);
  }

  /** @test */
  public function tag_cannot_register_with_empty_name()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    $tag = [
      "name" => "",
      "style" => "bg-gray-700",
    ];

    $response = $this->actingAs($user)->postJson(route("tag.store"), $tag);
    $this->assertDatabaseCount('tags', 0);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["name"]
      ]
    ]);
    $response->assertJsonPath('error.details.name.0', 'The name field is required.');
  }

  /** @test */
  public function tag_cannot_register_when_exists_in_database()
  {
    $user = User::factory()->create();
    $tagDuplicated = [
      "name" => Str::upper("mi etiqueta"),
      "style" => "bg-gray-700",
    ];

    Tag::factory()->create(["name" => $tagDuplicated["name"], "style" => $tagDuplicated["style"]]);

    $this->assertDatabaseCount("tags", 1);

    $response = $this->actingAs($user)->postJson(route("tag.store"), $tagDuplicated);
//     dd($response->getContent());

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount("tags", 1);
    $this->assertDatabaseHas("tags", [
      "name" => Str::upper($tagDuplicated['name']),
      "style" => "bg-gray-700",
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["name"]
      ]
    ]);
    $response->assertJsonPath('error.details.name.0', 'The name has already been taken.');
  }
}
