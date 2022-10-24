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
        // dd($response->getContent());
        $this->assertDatabaseCount("tags", 1);
        $this->assertDatabaseHas("tags", [
            "name" => Str::upper($tag['name']),
            "style" => $tag['style']
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
            "name" => Str::upper("mi etiqueta"),
            "style" => "bg-gray-700",
        ];

        Tag::factory()->create($tagDuplicated);

        $this->assertDatabaseCount("tags", 1);

        $response = $this->postJson(route("tag.store"), $tagDuplicated);
        // dd($response->getContent());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonStructure([
            'error',
            'code'
        ]);

        $this->assertDatabaseCount("tags", 1);

        $this->assertDatabaseHas("tags", [
            "name" => Str::upper($tagDuplicated['name']),
            "style" => "bg-gray-700",
        ]);
    }
}
