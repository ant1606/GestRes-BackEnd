<?php

namespace Tests\Feature\Recourse;

use Carbon\Carbon;
use App\Models\Tag;
use Tests\TestCase;
use App\Models\Recourse;
use App\Models\Settings;
use App\Enums\TypeRecourseEnum;
use App\Enums\StatusRecourseEnum;
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

        /* Given */
        $recourse = $this->recourseValidData([
            'autor' => null,
            'editorial' => null,
        ]);

        $response = $this->postJson(route('recourse.store'), $recourse);

        $response->assertStatus(Response::HTTP_CREATED);

//        dd($response->getContent());
        $this->assertDatabaseHas('recourses', [
            "name" => $recourse['nombre'],
            "source" => $recourse['ruta'],
            "author" => $recourse['autor'],
            "editorial" => $recourse['editorial'],
            "type_id" => $recourse['tipoId'],
            "total_pages" => $recourse['totalPaginas'],
            "total_chapters" => $recourse['totalCapitulos'],
            "total_videos" => $recourse['totalVideos'],
            "total_hours" => $recourse['totalHoras'],
        ]);

        $this->assertDatabaseHas('progress_histories', [
            "recourse_id" => 1,
            "done" => 0,
            "pending" => Settings::getKeyfromId($recourse['tipoId']) === TypeRecourseEnum::TYPE_LIBRO->name ?
                $recourse['totalPaginas'] : $recourse['totalVideos'],
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
                "nombre" => $recourse['nombre'],
                "ruta" => $recourse['ruta'],
                "autor" => $recourse['autor'],
                "editorial" => $recourse['editorial'],
                "tipoId" => $recourse['tipoId'],
                "totalPaginas" => $recourse['totalPaginas'],
                "totalCapitulos" => $recourse['totalCapitulos'],
                "totalVideos" => $recourse['totalVideos'],
                "totalHoras" => $recourse['totalHoras'],
                // "id" => $recourse->id
            ]
        ]);
    }

    /** @test */
    public function recourses_can_be_register_with_all_values()
    {
        // $this->withoutExceptionHandling();

        $tags = Tag::factory(5)->create();
        $selectedTags = $tags->pluck('id')->random(3);
        $recourse = $this->recourseValidData(["tags" => $selectedTags,]);

        $response = $this->postJson(route('recourse.store'), $recourse);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('recourses', [
            "name" => $recourse['nombre'],
            "source" => $recourse['ruta'],
            "author" => $recourse['autor'],
            "editorial" => $recourse['editorial'],
            "type_id" => $recourse['tipoId'],
            "total_pages" => $recourse['totalPaginas'],
            "total_chapters" => $recourse['totalCapitulos'],
            "total_videos" => $recourse['totalVideos'],
            "total_hours" => $recourse['totalHoras'],
        ]);

        $this->assertDatabaseHas('progress_histories', [
            "Recourse_id" => 1,
            "done" => 0,
            "pending" => Settings::getKeyfromId($recourse['tipoId']) === TypeRecourseEnum::TYPE_LIBRO->name ?
                $recourse['totalPaginas'] : $recourse['totalVideos'],
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
                "nombre" => $recourse['nombre'],
                "ruta" => $recourse['ruta'],
                "autor" => $recourse['autor'],
                "editorial" => $recourse['editorial'],
                "tipoId" => $recourse['tipoId'],
                "totalPaginas" => $recourse['totalPaginas'],
                "totalCapitulos" => $recourse['totalCapitulos'],
                "totalVideos" => $recourse['totalVideos'],
                "totalHoras" => $recourse['totalHoras'],
            ]
        ]);
    }

    /** @test */
    public function recourses_can_not_be_register_without_required_values()
    {
        // $this->withoutExceptionHandling();
        // $this->withExceptionHandling();

        $tags = Tag::factory(5)->create();

        $selectedTags = $tags->pluck('id')->random(3);
        $recourse = $this->recourseValidData([
            "nombre" => null,
            "ruta" => null,
            "tipoId" => null,
            "tags" => $selectedTags,
        ]);

        $response = $this->postJson(route('recourse.store'), $recourse);
        // dd($response->exception->getMessage());
        // dd($response->exception->getCode());
        // dd(gettype($response->exception));
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount('recourses', 0);
        $this->assertDatabaseCount('progress_histories', 0);
        $this->assertDatabaseCount('status_histories', 0);
        $this->assertDatabaseCount('taggables', 0);

        // dd($response->getContent());
        $response->assertJsonStructure([
            "error"=> [
                ["status", "detail"]
            ]
        ]);
    }

    /** @test */
    public function recourses_can_not_be_register_when_an_error_occurs_in_transaction()
    {
        $tags = Tag::factory(5)->create();
        // Enviamos incorrectamente la data de los tags, para ocasionar un error en la transaccion
        // Solo enviamos los nombres de las etiquetas y no los ids que necesita la operacion
        $selectedTags = $tags->pluck('name')->random(3);

        $recourse = $this->recourseValidData([
            "tags" => $selectedTags,
        ]);

        $response = $this->postJson(route('recourse.store'), $recourse);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('recourses', 0);
        $this->assertDatabaseCount('progress_histories', 0);
        $this->assertDatabaseCount('status_histories', 0);
        $this->assertDatabaseCount('taggables', 0);
        $response->assertJsonStructure([
            "error" => [
                [
                    "status", "detail"
                ]
            ]
        ]);
    }
}
