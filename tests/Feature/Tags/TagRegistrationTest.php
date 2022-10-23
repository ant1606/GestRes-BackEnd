<?php

namespace Tests\Feature\Tags;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TagRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tag_can_be_register()
    {
        // $this->withoutExceptionHandling();

        $tag = [
            "name" => "Etiqueta de prueba",
            "style" => "bg-gray-700",
        ];

        $response = $this->postJson(route("tag.store"), $tag);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas("tags", [
            "name" => "Etiqueta de prueba",
            "style" => "bg-gray-700",
        ]);
    }

    /** @test */
    public function tag_cannot_register_with_empty_name()
    {
        // $this->withoutExceptionHandling();

        $tag = [
            "name" => "",
            "style" => "bg-gray-700",
        ];

        $response = $this->postJson(route("tag.store"), $tag);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount('tags', 0);

        $response->assertJsonStructure([
            'error',
            'code'
        ]);
    }
}
