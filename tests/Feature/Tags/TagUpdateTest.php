<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tag_can_be_updated()
    {
        $tag = Tag::factory()->create();

        $updatedTag = [
            "identificador" => $tag->id,
            "nombre" => "etiqueta actualizada",
        ];

        $response = $this->putJson(route('tag.update', $updatedTag["identificador"]), $updatedTag);

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseCount("tags", 1);

        $this->assertDatabaseHas("tags", [
            "name" => Str::upper($updatedTag["nombre"]),
            "id" => $updatedTag["identificador"],
        ]);
    }

    /** @test */
    public function tag_can_not_be_updated_if_the_name_doesnt_change()
    {
        $tag = Tag::create([
            "name" => "MI ETIQUETA",
            "style" => "estilo"
        ]);

        $response = $this->putJson(route('tag.update', $tag["id"]), ["identificador" => $tag->id, "nombre" => $tag->name]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount("tags", 1);
        $response->assertJsonStructure([
            "error"=>[
                [
                    "status",
                    "detail"
                ]
            ]
        ]);
    }
}
