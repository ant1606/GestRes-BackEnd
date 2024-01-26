<?php

  namespace Tests\Feature\Tags;

  use App\Models\Tag;
  use App\Models\User;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Illuminate\Testing\Fluent\AssertableJson;
  use Symfony\Component\HttpFoundation\Response;
  use Tests\TestCase;

  class TagDeleteTest extends TestCase
  {
    use RefreshDatabase;

    /** @test */
    public function can_delete_a_tag()
    {
      $user = User::factory()->create();
      $tag = Tag::factory()->create();

      $response = $this->actingAs($user)->deleteJson(route('tag.destroy', $tag));

      $response->assertStatus(Response::HTTP_ACCEPTED);
      $this->assertDatabaseCount("tags", 0);
      $response->assertJson(fn(AssertableJson $json) => $json->has("status")
        ->has("code")
        ->has("data", fn(AssertableJson $json) => $json->where('identificador', $tag->id)
          ->where('nombre', $tag->name)
          ->where("estilos", $tag->style)
          ->has("total")
        )
      );
    }

    /** @test */
    public function can_not_delete_a_tag_has_doesnt_exists()
    {
      $user = User::factory()->create();
      Tag::factory(10)->create();
      $tag = 150;

      $response = $this->actingAs($user)->deleteJson(route('tag.destroy', $tag));

      $response->assertStatus(Response::HTTP_NOT_FOUND);
      $this->assertDatabaseCount("tags", 10);
      $response->assertJsonStructure([
        "status",
        "code",
        "error" => [
          "message",
          "details"
        ]
      ]);
      $response->assertJsonPath('error.message', 'No se encontró el recurso');
    }
  }
