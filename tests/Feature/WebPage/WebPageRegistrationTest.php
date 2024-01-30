<?php

namespace Tests\Feature\WebPage;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class WebPageRegistrationTest extends TestCase
{
  use RefreshDatabase;
  //use WebPageDataTrait;

  /** @test */
  public function webpages_can_be_register_with_minimum_values()
  {
    // $this->withoutExceptionHandling();

    $user = User::factory()->create();

    $webpage = [
      'url' => "http://www.edteam.pe",
      'name' => null,
      'description' => null,
      'count_visits' => 0
    ];

    $response = $this->actingAs($user)->postJson(route('webpage.store'), $webpage);

    $response->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseHas('web_pages', [
      "url" => $webpage['url'],
      "name" => $webpage['name'],
      "description" => $webpage['description'],
      "count_visits" => $webpage['count_visits']
    ]);

    $response->assertJson([
      'data' => [
        "url" => $webpage['url'],
        "nombre" => $webpage['name'],
        "descripcion" => $webpage['description'],
        "totalVisitas" => $webpage['count_visits']
      ]
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "data"=>[
        "url",
        "nombre",
        "descripcion",
        "totalVisitas",
      ]
    ]);
  }

  /** @test */
  public function webpages_can_be_register_with_tags()
  {
    $this->withoutExceptionHandling();

    $user = User::factory()->create();
    $tags = Tag::factory(5)->create();

    $webpage = [
      'url' => "http://www.edteam.pe",
      'name' => null,
      'description' => null,
      'count_visits' => 0,
      'tags' => $tags->pluck('id')->all()
    ];

    $response = $this->actingAs($user)->postJson(route('webpage.store'), $webpage);

    $response->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseHas('web_pages', [
      "url" => $webpage['url'],
      "name" => $webpage['name'],
      "description" => $webpage['description'],
      "count_visits" => $webpage['count_visits']
    ]);

    $this->assertDatabaseCount('taggables', 5);

    $response->assertJson([
      'data' => [
        "url" => $webpage['url'],
        "nombre" => $webpage['name'],
        "descripcion" => $webpage['description'],
        "totalVisitas" => $webpage['count_visits'],
        "tags" => [],
      ]
    ]);

    $response->assertJsonStructure([
      "status",
      "code",
      'data' => [
        "url",
        "nombre",
        "descripcion",
        "totalVisitas",
        "tags" => [
          '*' => [
            'identificador',
            'nombre',
            'estilos',
            'total',
          ],
        ],
      ]
    ]);
  }
}
