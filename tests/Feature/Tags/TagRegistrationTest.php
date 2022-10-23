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

    /** @test */
    public function tag_cannot_register_when_exists_in_database()
    {
        // $this->withoutExceptionHandling();

        $tagDuplicated = [
            "name" => Str::headline("mi etiqueta"),
            "style" => "bg-gray-700",
        ];

        Tag::factory()->create($tagDuplicated);

        $response = $this->postJson(route("tag.store"), $tagDuplicated);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonStructure([
            'error',
            'code'
        ]);

        $this->assertDatabaseCount("tags", 1);

        $this->assertDatabaseHas("tags", $tagDuplicated);
    }
}
