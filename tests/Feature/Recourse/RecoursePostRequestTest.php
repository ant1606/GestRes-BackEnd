<?php

namespace Tests\Feature\Recourse;

use App\Enums\TypeRecourseEnum;
use App\Models\Settings;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecoursePostRequestTest extends TestCase
{
    use RefreshDatabase;
    use RecourseDataTrait;

    #region NAME
    /** @test */
    public function the_name_may_not_be_greater_than_150_characters()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['nombre' => Str::random(151)])
        );

        $response->assertJsonFragment(
            [
                "status" => 422,
                "inputName" => "nombre",
            ]
        );
    }

    /** @test */
    public function the_name_is_required()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['nombre' => null])
        );

        $response->assertJsonStructure([
            'error' => [
                [
                    "status",
                    "inputName",
                    "detail"
                ]
            ]
        ]);

        // $response->assertJson([
        //     'error.name'
        // ])
        // $response->assertJson(function (AssertableJson $json) {
        //     $json->has('code');
        //     // ->has('error.name');
        // });
    }

    #endregion

    #region SOURCE


    /** @test */
    public function the_source_is_required()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['ruta' => null])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "ruta",
        ]);
    }

    /** @test */
    public function the_source_may_not_be_greater_than_255_characters()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['ruta' => Str::random(256)])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "ruta",
        ]);
    }
    #endregion

    #region AUTHOR

    /** @test */
    public function the_author_may_not_be_greater_than_75_characters()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['autor' => Str::random(76)])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "autor",
        ]);
    }

    #endregion

    #region EDITORIAL

    /** @test */
    public function the_editorial_may_not_be_greater_than_75_characters()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['editorial' => Str::random(76)])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "editorial",
        ]);
    }

    #endregion

    #region TYPE_ID

    /** @test */
    public function the_type_id_is_required()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['tipoId' => null])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "tipoId",
        ]);
    }

    /** @test */
    public function the_type_id_can_not_be_a_value_different_to_TypeRecourseEnum_cases_id()
    {
        $acceptedId = [
            Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
            Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id")
        ];

        do {
            $type_id = random_int(1, 50);
        } while (in_array($type_id, $acceptedId));

        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData(['tipoId' => $type_id])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "tipoId",
        ]);
    }

    #endregion

    #region TOTAL_PAGES

    /** @test */
    public function the_total_pages_is_required_when_recourse_is_type_libro()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'totalPaginas' => null
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalPaginas",
        ]);
    }

    /** @test */
    public function the_total_pages_must_be_an_integer()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'totalPaginas' => Str::random(10)
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalPaginas",
        ]);
    }

    #endregion

    #region TOTAL_CHAPTERS

    /** @test */
    public function the_total_chapters_is_required_when_recourse_is_type_libro()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'totalCapitulos' => null
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalCapitulos",
        ]);
    }

    /** @test */
    public function the_total_chapters_must_be_an_integer()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'totalCapitulos' => Str::random(10)
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalCapitulos",
        ]);
    }
    #endregion

    #region TOTAL_VIDEOS

    /** @test */
    public function the_total_videos_is_required_when_recourse_is_type_video()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
                'totalVideos' => null
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalVideos",
        ]);
    }

    /** @test */
    public function the_total_videos_must_be_an_integer()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'totalVideos' => Str::random(10)
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalVideos",
        ]);
    }

    #endregion

    #region TOTAL_HOURS

    /** @test */
    public function the_total_hours_is_required_when_recourse_is_type_video()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
                'totalHoras' => null
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalHoras",
        ]);
    }

    /** @test */
    public function the_total_hours_must_be_a_time()
    {
        $response = $this->postJson(
            route('recourses.store'),
            $this->recourseValidData([
                'tipoId' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'totalHoras' => Str::random(10)
            ])
        );

        $response->assertJsonFragment([
            "status" => 422,
            "inputName" => "totalHoras",
        ]);
    }

    #endregion
}
