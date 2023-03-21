<?php

namespace Tests\Feature\Recourse;

use App\Enums\TypeRecourseEnum;
use App\Models\Recourse;
use App\Models\Settings;
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
        $recourse = Recourse::factory()->create();

        $this->assertDatabaseCount('recourses', 1);

        $recourseUpdate = [
            "nombre" => "Recurso Actualizado",
            "ruta" => $recourse['source'],
            "tipoId" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name,"id"),
            "totalPaginas" => 257,
            "totalCapitulos" => 13,
        ];

        $response = $this->putJson(route('recourses.update', $recourse), $recourseUpdate);
//        dd($response->getContent());
        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseCount('recourses', 1);

        $this->assertDatabaseHas('recourses', [
            "name" => $recourseUpdate["nombre"],
            "source" => $recourse["source"],
            "author" => $recourse["author"],
            "editorial" => $recourse["editorial"],
            "type_id" => $recourseUpdate["tipoId"],
            "total_pages" => $recourseUpdate["totalPaginas"],
            "total_chapters" => $recourseUpdate["totalCapitulos"],
            "total_videos" => $recourse["total_videos"],
            "total_hours" => $recourse["total_hours"]
        ]);
    }

    /** @test */
    public function recourse_can_be_edited_when_change_all_values()
    {
        $recourse = Recourse::factory()->create();

        $this->assertDatabaseCount('recourses', 1);

        $recourseUpdate = $this->recourseValidData([ "nombre" => 'Mi Recurso Actualizado nuevo' ] );

        $response = $this->putJson(route('recourses.update', $recourse), $recourseUpdate);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $this->assertDatabaseCount('recourses', 1);
        $this->assertDatabaseHas('recourses', [
            "name" => $recourseUpdate["nombre"],
            "source" => $recourseUpdate["ruta"],
            "author" => $recourseUpdate["autor"],
            "editorial" => $recourseUpdate["editorial"],
            "type_id" => $recourseUpdate["tipoId"],
            "total_pages" => $recourseUpdate["totalPaginas"],
            "total_chapters" => $recourseUpdate["totalCapitulos"],
            "total_videos" => $recourseUpdate["totalVideos"],
            "total_hours" => $recourseUpdate["totalHoras"]
        ]);
    }

    /** @test */
    public function recourse_cannot_be_edited_when_any_values_have_not_changed()
    {
        $recourse = Recourse::factory()->create();

        $this->assertDatabaseCount('recourses', 1);

        $response = $this->putJson(route('recourses.update', $recourse), $recourse->toArray());

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
        $recourse = Recourse::factory()->create();

        $this->assertDatabaseCount('recourses', 1);

        $recourseUpdate = $this->recourseValidData([
            "nombre" => null,
            "autor" => null,
            "tipoId" => null,
            "totalPaginas" => null,
            "totalCapitulos" => null,
            "totalVideos" => null
        ]);

        $response = $this->putJson(route('recourses.update', $recourse), $recourseUpdate);

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
