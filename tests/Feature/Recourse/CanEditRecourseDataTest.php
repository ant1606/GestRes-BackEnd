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

        $recourseUpdate = array_merge(
            $recourse->toArray(),
            [
                'name' => 'Recurso Actualizado',
                'author' => null,
                'editorial' => null
            ]
        );

        $response = $this->putJson(route('recourse.update', $recourse), $recourseUpdate);

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseCount('recourses', 1);

        $this->assertDatabaseHas('recourses', [
            "name" => $recourseUpdate['name'],
            "source" => $recourseUpdate['source'],
            "author" => $recourseUpdate['author'],
            "editorial" => $recourseUpdate['editorial'],
            "type_id" => $recourseUpdate['type_id'],
            "total_pages" => $recourseUpdate['total_pages'],
            "total_chapters" => $recourseUpdate['total_chapters'],
            "total_videos" => $recourseUpdate['total_videos'],
            "total_hours" => $recourseUpdate['total_hours']
        ]);
    }

    /** @test */
    public function recourse_can_be_edited_when_change_all_values()
    {
        $recourse = Recourse::factory()->create();

        $this->assertDatabaseCount('recourses', 1);

        $recourseUpdate = array_merge(
            $recourse->toArray(),
            [
                'name' => 'Mi Recurso Actualizado',
                'source' => 'https://www.notion.so/Laravel-f5419942aac64e65a8b2593fd2ab4e29',
                'author' => null,
                'editorial' => 'NO tiene editorial',
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
                'total_pages' => null,
                'total_chapters' => null,
                'total_videos' => 28,
                'total_hours' => '05:30:00',
            ]
        );

        $response = $this->putJson(route('recourse.update', $recourse), $recourseUpdate);

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseCount('recourses', 1);

        $this->assertDatabaseHas('recourses', [
            "name" => $recourseUpdate['name'],
            "source" => $recourseUpdate['source'],
            "author" => $recourseUpdate['author'],
            "editorial" => $recourseUpdate['editorial'],
            "type_id" => $recourseUpdate['type_id'],
            "total_pages" => $recourseUpdate['total_pages'],
            "total_chapters" => $recourseUpdate['total_chapters'],
            "total_videos" => $recourseUpdate['total_videos'],
            "total_hours" => $recourseUpdate['total_hours']
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

        $recourseUpdate = array_merge(
            $recourse->toArray(),
            [
                'name' => null,
                'source' => null,
                'editorial' => null,
                'type_id' => null,
                'total_pages' => null,
                'total_chapters' => null,
                'total_videos' => null,
                'total_hours' => null
            ]
        );

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
