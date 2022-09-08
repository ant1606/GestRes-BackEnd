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
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    //TODO Hacer un caso donde se verifique el registro correcto del recurso mediante Transactions
    //TODO Hacer un caso donde se verifique que no se registro nada si es que ocurrio un error durante el registro de
    //     recurso o HistorialdeProgreso o historialdeEstado
    //TODO Hacer test de las reglas de validacion
    /** @test */
    public function recourses_can_be_register_with_minimul_values()
    {
        // $this->withoutExceptionHandling();

        /* Given */
        //Creamos un objeto de tipo Recourse con sus datos
        $recourse = [
            "name" => 'Nombre de mi recurso',
            "source" => 'D://micarpeta/misvideos/micurso',
            "author" => null,
            "editorial" => null,
            "type_id" => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
            "total_pages" => null,
            "total_chapters" => null,
            "total_videos" => 150,
            "total_hours" => "15:30:12"
        ];

        $response = $this->postJson(route('recourse.store'), $recourse);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('recourses', [
            "name" => 'Nombre de mi recurso',
            "source" => 'D://micarpeta/misvideos/micurso',
            "author" => null,
            "editorial" => null,
            "type_id" => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
            "total_pages" => null,
            "total_chapters" => null,
            "total_videos" => 150,
            "total_hours" => "15:30:12"
        ]);

        $this->assertDatabaseHas('progress_histories', [
            "recourse_id" => 1,
            "done" => 0,
            "pending" => 150,
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
                "name" => 'Nombre de mi recurso',
                "source" => 'D://micarpeta/misvideos/micurso',
                "author" => null,
                "editorial" => null,
                "type_id" => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
                "total_videos" => 150,
                "total_hours" => "15:30:12",
                "total_pages" => null,
                "total_chapters" => null,
                // "id" => $recourse->id
            ]
        ]);
    }

    /** @test */
    public function recourses_can_be_register_with_all_values()
    {
        $this->withoutExceptionHandling();

        $tags = Tag::factory(5)->create();

        $selectedTags = $tags->pluck('id')->random(3);
        $recourse = [
            "name" => 'Nombre de mi recurso',
            "source" => 'D://micarpeta/misvideos/micurso',
            "author" => "Pepe LUna",
            "editorial" => "Mi editorial Nroam",
            "type_id" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
            "total_pages" => 250,
            "total_chapters" => 10,
            "total_videos" => null,
            "total_hours" => null,
            "tags" => $selectedTags,
        ];

        $response = $this->postJson(route('recourse.store'), $recourse);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('recourses', [
            "name" => 'Nombre de mi recurso',
            "source" => 'D://micarpeta/misvideos/micurso',
            "author" => "Pepe LUna",
            "editorial" => "Mi editorial Nroam",
            "type_id" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
            "total_pages" => 250,
            "total_chapters" => 10,
            "total_videos" => null,
            "total_hours" => null
        ]);

        $this->assertDatabaseHas('progress_histories', [
            "Recourse_id" => 1,
            "done" => 0,
            "pending" => 250,
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
                "name" => 'Nombre de mi recurso',
                "source" => 'D://micarpeta/misvideos/micurso',
                "author" => "Pepe LUna",
                "editorial" => "Mi editorial Nroam",
                "type_id" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                "total_pages" => 250,
                "total_chapters" => 10,
                "total_videos" => null,
                "total_hours" => null
                // "id" => $recourse->id
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
        $recourse = [
            "name" => null,
            "source" => null,
            "author" => "Pepe LUna",
            "editorial" => "Mi editorial Nroam",
            "type_id" => null,
            "total_pages" => 250,
            "total_chapters" => 10,
            "total_videos" => null,
            "total_hours" => null,
            "tags" => $selectedTags,
        ];

        $response = $this->postJson(route('recourse.store'), $recourse);
        // dd($response->exception->getMessage());
        // dd($response->exception->getCode());
        // dd(gettype($response->exception));
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount('recourses', 0);
        $this->assertDatabaseCount('progress_histories', 0);
        $this->assertDatabaseCount('status_histories', 0);
        $this->assertDatabaseCount('taggables', 0);

        $response->assertJsonStructure([
            'error',
            'code'
        ]);

        // $response->assertJson([
        //     'data' => [
        //         "name" => 'Nombre de mi recurso',
        //         "source" => 'D://micarpeta/misvideos/micurso',
        //         "author" => "Pepe LUna",
        //         "editorial" => "Mi editorial Nroam",
        //         "type_id" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
        //         "total_pages" => 250,
        //         "total_chapters" => 10,
        //         "total_videos" => null,
        //         "total_hours" => null
        //         // "id" => $recourse->id
        //     ]
        // ]);
    }
}
