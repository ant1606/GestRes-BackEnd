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
    /** @test */
    public function recourse_can_be_edited_when_change_only_required_values()
    {
        $recourse = Recourse::factory()->create();

        $this->assertDatabaseCount('recourses', 1);

        $recourseUpdate = [
            "nombre" => "Recurso Actualizado",
            "ruta" => $recourse['source'],
            "autor" => $recourse['author'],
            "editorial" => $recourse['editorial'],
            "tipoId" => $recourse['type_id'],
            "totalPaginas" => $recourse['total_pages'],
            "totalCapitulos" => $recourse['total_chapters'],
            "totalVideos" => $recourse['total_videos'],
            "totalHoras" => $recourse['total_hours'],
        ];

        $response = $this->putJson(route('recourse.update', $recourse), $recourseUpdate);

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
    public function recourse_can_be_edited_when_change_all_values()
    {
        $recourse = Recourse::factory()->create();

        $this->assertDatabaseCount('recourses', 1);

        $recourseUpdate = $this->recourseValidData([ "nombre" => 'Mi Recurso Actualizado nuevo' ] );

        $response = $this->putJson(route('recourse.update', $recourse), $recourseUpdate);

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

        $response = $this->putJson(route('recourse.update', $recourse), $recourse->toArray());

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

        $response = $this->putJson(route('recourse.update', $recourse), $recourseUpdate);

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
