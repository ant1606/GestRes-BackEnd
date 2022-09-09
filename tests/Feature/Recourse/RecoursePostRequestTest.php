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
    public function the_name_is_required()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData(['name' => null])
        );

        $response->assertJsonStructure([
            'error' => ['name']
        ]);

        // $response->assertJson([
        //     'error.name'
        // ])
        // $response->assertJson(function (AssertableJson $json) {
        //     $json->has('code');
        //     // ->has('error.name');
        // });
    }

    /** @test */
    public function the_name_may_not_be_greater_than_150_characters()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData(['name' => Str::random(151)])
        );

        $response->assertJsonStructure([
            'error' => ['name']
        ]);
    }
    #endregion

    #region SOURCE
    /** @test */
    public function the_source_is_required()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData(['source' => null])
        );

        $response->assertJsonStructure([
            'error' => ['source']
        ]);
    }

    /** @test */
    public function the_source_may_not_be_greater_than_255_characters()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData(['source' => Str::random(256)])
        );

        $response->assertJsonStructure([
            'error' => ['source']
        ]);
    }
    #endregion

    #region AUTHOR

    /** @test */
    public function the_author_may_not_be_greater_than_75_characters()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData(['author' => Str::random(76)])
        );

        $response->assertJsonStructure([
            'error' => ['author']
        ]);
    }

    #endregion

    #region EDITORIAL

    /** @test */
    public function the_editorial_may_not_be_greater_than_75_characters()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData(['editorial' => Str::random(76)])
        );

        $response->assertJsonStructure([
            'error' => ['editorial']
        ]);
    }

    #endregion

    #region TYPE_ID

    /** @test */
    public function the_type_id_is_required()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData(['type_id' => null])
        );

        $response->assertJsonStructure([
            'error' => ['type_id']
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
            route('recourse.store'),
            $this->recourseValidData(['type_id' => $type_id])
        );

        $response->assertJsonStructure([
            'error' => ['type_id']
        ]);
    }

    #endregion

    #region TOTAL_PAGES

    /** @test */
    public function the_total_pages_is_required_when_recourse_is_type_libro()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'total_pages' => null
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_pages']
        ]);
    }

    /** @test */
    public function the_total_pages_must_be_an_integer()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'total_pages' => Str::random(10)
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_pages']
        ]);
    }

    #endregion

    #region TOTAL_CHAPTERS

    /** @test */
    public function the_total_chapters_is_required_when_recourse_is_type_libro()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'total_chapters' => null
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_chapters']
        ]);
    }

    /** @test */
    public function the_total_chapters_must_be_an_integer()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'total_chapters' => Str::random(10)
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_chapters']
        ]);
    }
    #endregion

    #region TOTAL_VIDEOS

    /** @test */
    public function the_total_videos_is_required_when_recourse_is_type_video()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
                'total_videos' => null
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_videos']
        ]);
    }

    /** @test */
    public function the_total_videos_must_be_an_integer()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'total_videos' => Str::random(10)
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_videos']
        ]);
    }

    #endregion

    #region TOTAL_HOURS

    /** @test */
    public function the_total_hours_is_required_when_recourse_is_type_video()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
                'total_hours' => null
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_hours']
        ]);
    }

    /** @test */
    public function the_total_hours_must_be_a_time()
    {
        $response = $this->postJson(
            route('recourse.store'),
            $this->recourseValidData([
                'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
                'total_hours' => Str::random(10)
            ])
        );

        $response->assertJsonStructure([
            'error' => ['total_hours']
        ]);
    }

    #endregion
}
