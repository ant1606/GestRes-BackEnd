<?php

namespace Tests\Feature\WebPage;

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
  }
}
