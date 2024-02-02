<?php

namespace Tests\Feature\Dashboard;

use App\Models\Recourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetAmountRecoursesByStateTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function get_summary_of_amount_recourses_by_amount()
  {
    $user = User::factory()->create();
    Recourse::factory(5)->create(["user_id" => $user->id]);

    $response = $this->actingAs($user)->get(route('dashboard.getAmountByState'));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
      "status",
      "code",
      "data"=>[
        "REGISTRADO",
        "POR EMPEZAR",
        "EN PROCESO",
        "CULMINADO",
        "DESCARTADO",
        "DESFASADO",
      ]
    ]);

    $response->assertJsonFragment(["REGISTRADO"=>5]);
  }

  /** @test */
  public function get_summary_of_amount_recourses_by_amount_only_by_user()
  {
    $user = User::factory()->create();
    Recourse::factory(5)->create(["user_id" => $user->id]);


    $user_without_recourses =User::factory()->create();
    $response = $this->actingAs($user_without_recourses)->get(route('dashboard.getAmountByState'));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure([
      "status",
      "code",
      "data"=>[
        "REGISTRADO",
        "POR EMPEZAR",
        "EN PROCESO",
        "CULMINADO",
        "DESCARTADO",
        "DESFASADO",
      ]
    ]);

    $response->assertJsonFragment(["REGISTRADO"=>0]);
  }
}
