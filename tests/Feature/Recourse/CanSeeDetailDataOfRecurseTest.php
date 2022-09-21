<?php

namespace Tests\Feature\Recourse;

use Tests\TestCase;
use App\Models\Recourse;
use App\Models\Tag;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CanSeeDetailDataOfRecurseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_detailed_data_of_recourse()
    {
        // $this->withoutExceptionHandling();

        $recourse = Recourse::factory()->create();

        $tags = Tag::factory(5)->create();
        $recourse->tags()->syncWithoutDetaching($tags->pluck('id'));

        $response = $this->getJson(route('recourse.show', $recourse->id));
        // dd($response->getContent());

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            "data" => [
                "name",
                "source",
                "author",
                "editorial",
                "type_id",
                "total_pages",
                "total_chapters",
                "total_videos",
                "total_hours",
                "status",
                "progress",
                "tags"
            ]
        ]);
    }

    /** @test */
    public function get_detailed_data_of_recourse_without_tags()
    {
        // $this->withoutExceptionHandling();

        $recourse = Recourse::factory()->create();

        $response = $this->getJson(route('recourse.show', $recourse->id));

        $response->assertStatus(Response::HTTP_OK);

        // dd($response->getContent());
        $response->assertJsonStructure([
            "data" => [
                "name",
                "source",
                "author",
                "editorial",
                "type_id",
                "total_pages",
                "total_chapters",
                "total_videos",
                "total_hours",
                "status",
                "progress",
                "tags"
            ]
        ]);
    }

    //TODO Hacer el test en el caso de uso que un recourse este eliminado y se deniegue el acceso
}
