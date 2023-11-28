<?php

namespace Tests\Feature\Recourse;

use App\Enums\TypeRecourseEnum;
use App\Models\Recourse;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CanEditRecourseDataTest extends TestCase
{
  use RefreshDatabase;
  use RecourseDataTrait;

  //TODO GEnerar los test unitarios para el StoreUpdateRequest
  //TODO Generar los test para el siguiente caso :  Al momento de editar un recurso y si se modifica el tipo y los totales, mandar un mensaje de confirmacion indicando que se borraran los avances registrados de estado y avance del recurso
  /** @test */
  public function recourse_can_be_edited_when_change_only_required_values()
  {
    //        dd(Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name,"id"));
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount('recourses', 1);

    $recourseUpdate = [
      "name" => "Recurso Actualizado",
      "source" => $recourse['source'],
      "type_id" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
      "total_pages" => 257,
      "total_chapters" => 13,
    ];

    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourseUpdate);
    //        dd($response->getContent());
    $response->assertStatus(Response::HTTP_ACCEPTED);

    $this->assertDatabaseCount('recourses', 1);

    $this->assertDatabaseHas('recourses', [
      "name" => $recourseUpdate["name"],
      "source" => $recourse["source"],
      "author" => $recourse["author"],
      "editorial" => $recourse["editorial"],
      "type_id" => $recourseUpdate["type_id"],
      "total_pages" => $recourseUpdate["total_pages"],
      "total_chapters" => $recourseUpdate["total_chapters"],
      "total_videos" => $recourse["total_videos"],
      "total_hours" => $recourse["total_hours"]
    ]);
  }

  /** @test */
  public function recourse_can_be_edited_when_change_all_values()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount('recourses', 1);

    $recourseUpdate = $this->recourseValidData(["name" => 'Mi Recurso Actualizado nuevo']);

    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourseUpdate);

    $response->assertStatus(Response::HTTP_ACCEPTED);
    $this->assertDatabaseCount('recourses', 1);
    $this->assertDatabaseHas('recourses', [
      "name" => $recourseUpdate["name"],
      "source" => $recourseUpdate["source"],
      "author" => $recourseUpdate["author"],
      "editorial" => $recourseUpdate["editorial"],
      "type_id" => $recourseUpdate["type_id"],
      "total_pages" => $recourseUpdate["total_pages"],
      "total_chapters" => $recourseUpdate["total_chapters"],
      "total_videos" => $recourseUpdate["total_videos"],
      "total_hours" => $recourseUpdate["total_hours"]
    ]);
  }

  /** @test */
  public function recourse_cannot_be_edited_when_any_values_have_not_changed()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount('recourses', 1);

    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourse->toArray());

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseHas('recourses', [
      "name" => $recourse->name,
      "source" => $recourse->source,
      "author" => $recourse->author,
      "editorial" => $recourse->editorial,
      "type_id" => $recourse->type_id,
      "total_pages" => $recourse->total_pages,
      "total_chapters" => $recourse->total_chapters,
      "total_videos" => $recourse->total_videos,
      "total_hours" => $recourse->total_hours
    ]);
  }

  /** @test */
  public function recourse_cannot_be_edited_when_only_have_optionals_values()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount('recourses', 1);

    $recourseUpdate = $this->recourseValidData([
      "name" => null,
      "author" => null,
      "type_id" => null,
      "total_pages" => null,
      "total_chapters" => null,
      "total_videos" => null
    ]);

    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourseUpdate);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseHas('recourses', [
      "name" => $recourse->name,
      "source" => $recourse->source,
      "author" => $recourse->author,
      "editorial" => $recourse->editorial,
      "type_id" => $recourse->type_id,
      "total_pages" => $recourse->total_pages,
      "total_chapters" => $recourse->total_chapters,
      "total_videos" => $recourse->total_videos,
      "total_hours" => $recourse->total_hours
    ]);
  }
}
