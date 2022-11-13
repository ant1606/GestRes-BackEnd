<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TagCanGetDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_list_of_tags()
    {
        $this->withoutExceptionHandling();

        Tag::Factory(10)->create();

        $response = $this->getJson(route('tag.index'));

        $response->assertStatus(Response::HTTP_ACCEPTED);
        // dd($response);
        $response->assertJsonStructure([
            "data" => [
                [
                    "identificador",
                    "nombre",
                    "estilos",
                ]
            ]
        ]);
        // dd($tags);
    }

    /** @test */
    public function can_search_tags_with_minimun_length_of_query_value_is_equals_or_higher_than_2()
    {
        $this->withoutExceptionHandling();

        Tag::Factory(10)->create();
        $filter = "MI ETIQUETA";
        Tag::create(['name' => "MI ETIQUETA"]);

        $response = $this->getJson(route('tag.index',  ["searchNombre" => $filter]));

//         dd($response->getContent());
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            "data" => [
                [
                    "identificador",
                    "nombre",
                    "estilos",
                ]
            ]
        ]);
//        $response->assertJsonCount(1);
        // dd($tags);
    }

    /** @test */
    public function get_response_when_does_not_found_results_from_filter()
    {
        $this->withoutExceptionHandling();

        Tag::Factory(10)->create();
        $filter = "asdasd";


        $response = $this->getJson(route('tag.index', ["searchNombre" => $filter]));

        // Cuando se tiene un codigo de respuesta 204, no devuelve un body,
        // $response->assertStatus(Response::HTTP_NO_CONTENT);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data',
            'message'
        ]);
    }

    // /** @test */
    // public function can_not_get_results_when_filter_is_less_or_equal_to_2()
    // {
    //     $this->withoutExceptionHandling();

    //     Tag::Factory(10)->create();
    //     $filter = "Mi";
    //     $tag = Tag::create(['name' => "Mi Etiqueta"]);

    //     $response = $this->getJson(route('tag.index', ["filter" => $filter]));

    //     $response->assertStatus(Response::HTTP_LENGTH_REQUIRED);
    //     $response->assertJsonStructure([
    //         'error',
    //         'code'
    //     ]);
    //     // $response->assertJsonCount(1);
    //     // dd($tags);
    // }
}
