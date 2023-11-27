<?php

namespace Tests\Feature\Recourse;

use Carbon\Carbon;
use App\Models\Tag;
use Tests\TestCase;
use App\Models\Recourse;
use App\Models\Settings;
use App\Enums\TypeRecourseEnum;
use App\Enums\StatusRecourseEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Recourse\RecourseDataTrait;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecourseRegistrationTest extends TestCase
{
  use RefreshDatabase;
  use RecourseDataTrait;

  /** @test */
  public function recourses_can_be_register_with_minimul_values()
  {
    // $this->withoutExceptionHandling();

    $user = User::factory()->create();
    /* Given */
    $recourse = $this->recourseValidData([
      'autor' => null,
      'editorial' => null,
    ]);

    $response = $this->actingAs($user)
      ->postJson(route('recourses.store'), $recourse);

    // dd($response->getContent());
    $response->assertStatus(Response::HTTP_CREATED);

    //        dd($response->getContent());
    $this->assertDatabaseHas('recourses', [
      "name" => $recourse['name'],
      "source" => $recourse['source'],
      "author" => $recourse['author'],
      "editorial" => $recourse['editorial'],
      "type_id" => $recourse['type_id'],
      "total_pages" => $recourse['total_pages'],
      "total_chapters" => $recourse['total_chapters'],
      "total_videos" => $recourse['total_videos'],
      "total_hours" => $recourse['total_hours'],
    ]);

    $this->assertDatabaseHas('progress_histories', [
      "recourse_id" => 1,
      "done" => 0,
      "pending" => Settings::getKeyfromId($recourse['type_id']) === TypeRecourseEnum::TYPE_LIBRO->name ?
        $recourse['total_pages'] : $recourse['total_videos'],
      // "date" => Carbon::now(),
      "comment" => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA",
    ]);

    $this->assertDatabaseHas('status_histories', [
      "recourse_id" => 1,
      "status_id" => Settings::getData(StatusRecourseEnum::STATUS_REGISTRADO->name, "id"),
      // "date" => Carbon::now(),
      "comment" => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA",
    ]);

    $response->assertJson([
      'data' => [
        "nombre" => $recourse['name'],
        "ruta" => $recourse['source'],
        "autor" => $recourse['author'],
        "editorial" => $recourse['editorial'],
        "tipoId" => $recourse['type_id'],
        "totalPaginas" => $recourse['total_pages'],
        "totalCapitulos" => $recourse['total_chapters'],
        "totalVideos" => $recourse['total_videos'],
        "totalHoras" => $recourse['total_hours'],
        // "id" => $recourse->id
      ]
    ]);
  }

  /** @test */
  public function recourses_can_be_register_with_all_values()
  {
    // $this->withoutExceptionHandling();

    $user = User::factory()->create();

    $tags = Tag::factory(5)->create();
    $selectedTags = $tags->pluck('id')->random(3);
    $recourse = $this->recourseValidData(["tags" => $selectedTags,]);

    $response = $this->actingAs($user)->postJson(route('recourses.store'), $recourse);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('recourses', [
      "name" => $recourse['name'],
      "source" => $recourse['source'],
      "author" => $recourse['author'],
      "editorial" => $recourse['editorial'],
      "type_id" => $recourse['type_id'],
      "total_pages" => $recourse['total_pages'],
      "total_chapters" => $recourse['total_chapters'],
      "total_videos" => $recourse['total_videos'],
      "total_hours" => $recourse['total_hours'],
    ]);

    $this->assertDatabaseHas('progress_histories', [
      "Recourse_id" => 1,
      "done" => 0,
      "pending" => Settings::getKeyfromId($recourse['type_id']) === TypeRecourseEnum::TYPE_LIBRO->name ?
        $recourse['total_pages'] : $recourse['total_videos'],
      // "date" => Carbon::now(),
      "comment" => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA",
    ]);

    $this->assertDatabaseHas('status_histories', [
      "Recourse_id" => 1,
      "status_id" => Settings::getData(StatusRecourseEnum::STATUS_REGISTRADO->name, "id"),
      // "date" => Carbon::now(),
      "comment" => "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA",
    ]);

    $selectedTags->each(function ($item, $key) {
      $this->assertDatabaseHas('taggables', [
        "tag_id" => $item,
        "taggable_id" => 1,
        "taggable_type" => Recourse::class,
      ]);
    });

    $response->assertJson([
      'data' => [
        "nombre" => $recourse['name'],
        "ruta" => $recourse['source'],
        "autor" => $recourse['author'],
        "editorial" => $recourse['editorial'],
        "tipoId" => $recourse['type_id'],
        "totalPaginas" => $recourse['total_pages'],
        "totalCapitulos" => $recourse['total_chapters'],
        "totalVideos" => $recourse['total_videos'],
        "totalHoras" => $recourse['total_hours'],
      ]
    ]);
  }

  /** @test */
  public function recourses_can_not_be_register_without_required_values()
  {
    // $this->withoutExceptionHandling();
    // $this->withExceptionHandling();
    $user = User::factory()->create();

    $tags = Tag::factory(5)->create();

    $selectedTags = $tags->pluck('id')->random(3);
    $recourse = $this->recourseValidData([
      "name" => null,
      "source" => null,
      "type_id" => null,
      "tags" => $selectedTags,
    ]);

    $response = $this->actingAs($user)->postJson(route('recourses.store'), $recourse);
    // dd($response->exception->getMessage());
    // dd($response->exception->getCode());
    // dd($response->getContent());
    // dd(gettype($response->exception));
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseCount('recourses', 0);
    $this->assertDatabaseCount('progress_histories', 0);
    $this->assertDatabaseCount('status_histories', 0);
    $this->assertDatabaseCount('taggables', 0);

    // dd($response->getContent());
    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }

  /** @test */
  public function recourses_can_not_be_register_when_an_error_occurs_in_transaction()
  {
    $user = User::factory()->create();

    $tags = Tag::factory(5)->create();
    // Enviamos incorrectamente la data de los tags, para ocasionar un error en la transaccion
    // Solo enviamos los nombres de las etiquetas y no los ids que necesita la operacion
    $selectedTags = $tags->pluck('name')->random(3);

    $recourse = $this->recourseValidData([
      "tags" => $selectedTags,
    ]);

    $response = $this->actingAs($user)->postJson(route('recourses.store'), $recourse);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertDatabaseCount('recourses', 0);
    $this->assertDatabaseCount('progress_histories', 0);
    $this->assertDatabaseCount('status_histories', 0);
    $this->assertDatabaseCount('taggables', 0);
    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }
}
