<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TagCanGetDataTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_get_list_of_tags()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    Tag::Factory(10)->create();

    $response = $this->actingAs($user)->getJson(route('tag.index'));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
      "status",
      "code",
      "data" => [
        "*"=>[
          "identificador",
          "nombre",
          "estilos",
        ]
      ],
      "meta"=>[
        "path",
        "currentPage",
        "perPage",
        "totalPages",
        "from",
        "to",
        "total"
      ],
      "links"=>[
        "self",
        "first",
        "last",
        "next",
        "prev"
      ]
    ]);
  }

  /** @test */
  public function can_search_tags_with_minimum_length_of_query_value_is_equals_or_higher_than_2()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    Tag::factory(10)->create();
    Tag::factory()->create(['name' => "MI ETIQUETA"]);
    $filter = "MI ETIQUETA";

    $response = $this->actingAs($user)->getJson(route('tag.index',  ["searchNombre" => $filter]));
    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
      "status",
      "code",
      "data" => [
        [
          "identificador",
          "nombre",
          "estilos",
        ]
      ],
      "meta",
      "links"
    ]);
    $response->assertJsonFragment(['nombre' => 'MI ETIQUETA']);
  }

  /** @test */
  public function get_response_with_no_data_when_does_not_found_results_from_filter()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    Tag::factory(10)->create();
    $filter = "asdasd";

    $response = $this->actingAs($user)->getJson(route('tag.index', ["searchNombre" => $filter]));

    // Cuando se tiene un codigo de respuesta 204, no devuelve un body,
    // $response->assertStatus(Response::HTTP_NO_CONTENT);
    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
      'status',
      'code',
      'data',
      'message'
    ]);
    $response->assertJsonCount(0, 'data');
    $response->assertJsonPath('message', 'No se encontraron resultados');
  }

   /** @test */
   public function can_not_get_results_when_filter_is_less_or_equal_to_2()
   {
       $user = User::factory()->create();
       Tag::factory(10)->create();
       Tag::factory()->create(['name' => "Mi Etiqueta"]);
       $filter = "Mi";

       $response = $this->actingAs($user)->getJson(route('tag.index', ["searchNombre" => $filter]));

       $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
       $response->assertJsonStructure([
         "status",
         "code",
         "error" => [
           "message",
           "details"
         ]
       ]);
   }
}
