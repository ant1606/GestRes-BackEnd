<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagUpdateTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function tag_can_be_updated()
  {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $updatedTag = [
      "id" => $tag->id,
      "name" => "etiqueta actualizada",
    ];

    $response = $this->actingAs($user)->putJson(route('tag.update', $updatedTag["id"]), $updatedTag);

    $response->assertStatus(Response::HTTP_ACCEPTED);

    $this->assertDatabaseCount("tags", 1);

    $this->assertDatabaseHas("tags", [
      "name" => Str::upper($updatedTag["name"]),
      "id" => $updatedTag["id"],
    ]);
  }

  /** @test */
  public function tag_can_not_be_updated_if_the_name_doesnt_change()
  {
    $user = User::factory()->create();

    $tag = Tag::create([
      "name" => "MI ETIQUETA",
      "style" => "estilo"
    ]);

    $response = $this->actingAs($user)->putJson(route('tag.update', $tag["id"]), ["id" => $tag->id, "name" => $tag->name]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseCount("tags", 1);
    $response->assertJsonStructure([
      "error" => [
        "status",
        "detail"
      ]
    ]);
  }
}
