<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TagDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_delete_a_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('tag.destroy', $tag));

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseCount("tags", 0);
        $response->assertJsonStructure([
            "data" => [
                "identificador",
                "nombre",
                "estilos"
            ]
        ]);
        // $this->assertSoftDeleted($tag);
    }

    /** @test */
    public function can_not_delete_a_tag_has_doesnt_exists()
    {

        Tag::factory(10)->create();

        $tag = 150;

        $response = $this->deleteJson(route('tag.destroy', $tag));

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        // dd($response->getContent());
        $this->assertDatabaseCount("tags", 10);
        $response->assertJsonStructure([
            "error"=>[
                [
                    "status",
                    "detail"
                ]
            ]
        ]);
        // $this->assertSoftDeleted($tag);
    }
}
