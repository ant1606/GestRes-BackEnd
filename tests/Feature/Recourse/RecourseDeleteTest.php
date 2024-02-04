<?php

namespace Tests\Feature\Recourse;

use App\Models\Recourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RecourseDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test **/
  public function a_recourse_can_be_soft_deleted()
  {
    // $this->withoutExceptionHandling();
    $user = User::factory()->create();
    $recourseDelete = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount("recourses", 1);
    $this->assertDatabaseCount("progress_histories", 1);
    $this->assertDatabaseCount("status_histories", 1);

    $response = $this->actingAs($user)->deleteJson(route('recourses.destroy', $recourseDelete));
    $response->assertStatus(Response::HTTP_ACCEPTED);
    $this->assertDatabaseCount("recourses", 0);
    $this->assertDatabaseCount("progress_histories", 0);
    $this->assertDatabaseCount("status_histories", 0);

  }
}
