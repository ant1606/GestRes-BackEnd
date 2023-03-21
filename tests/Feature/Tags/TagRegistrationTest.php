<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tag_can_be_register()
    {
        // $this->withoutExceptionHandling();

        $tag = [
            "nombre" => "Etiqueta de prueba"
        ];

        $response = $this->postJson(route("tag.store"), $tag);

        $response->assertStatus(Response::HTTP_CREATED);
        // dd($response->getContent());
        $this->assertDatabaseCount("tags", 1);
        $this->assertDatabaseHas("tags", [
            "name" => Str::upper($tag['nombre']),
        ]);
    }

    /** @test */
    public function tag_cannot_register_with_empty_name()
    {
        // $this->withoutExceptionHandling();

        $tag = [
            "nombre" => "",
            "estilos" => "bg-gray-700",
        ];

        $response = $this->postJson(route("tag.store"), $tag);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount('tags', 0);

        $response->assertJsonStructure([
            'error' => [
                [
                    "status",
                    "detail"
                ]
            ]
        ]);
    }

    /** @test */
    public function tag_cannot_register_when_exists_in_database()
    {
        // $this->withoutExceptionHandling();

        $tagDuplicated = [
            "nombre" => Str::upper("mi etiqueta"),
            "estilos" => "bg-gray-700",
        ];

        Tag::factory()->create(["name"=>$tagDuplicated["nombre"], "style" => $tagDuplicated["estilos"]]);

        $this->assertDatabaseCount("tags", 1);

        $response = $this->postJson(route("tag.store"), $tagDuplicated);
        // dd($response->getContent());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonStructure([
            'error' => [
                [
                    "status",
                    "detail"
                ]
            ]
        ]);

        $this->assertDatabaseCount("tags", 1);

        $this->assertDatabaseHas("tags", [
            "name" => Str::upper($tagDuplicated['nombre']),
            "style" => "bg-gray-700",
        ]);
    }
}
