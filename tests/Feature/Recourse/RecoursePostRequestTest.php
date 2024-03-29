<?php

namespace Tests\Feature\Recourse;

use App\Enums\TypeRecourseEnum;
use App\Enums\UnitMeasureProgressEnum;
use App\Models\Settings;
use App\Models\User;
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
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['name' => Str::random(151)])
    );

    $response->assertJsonFragment(
      ["name" => ["The name must not be greater than 150 characters."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["name"]
      ]
    ]);
  }

  /** @test */
  public function the_name_is_required()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['name' => null])
    );

    $response->assertJsonFragment(
      ["name" => ["The name field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["name"]
      ]
    ]);
  }

  #endregion

  #region SOURCE


  /** @test */
  public function the_source_is_required()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['source' => null])
    );

    $response->assertJsonFragment(
      ["source" => ["The source field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["source"]
      ]
    ]);
  }

  /** @test */
  public function the_source_may_not_be_greater_than_255_characters()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['source' => Str::random(256)])
    );

    $response->assertJsonFragment(
      ["source" => ["The source must not be greater than 255 characters."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["source"]
      ]
    ]);
  }
  #endregion

  #region AUTHOR

  /** @test */
  public function the_author_may_not_be_greater_than_75_characters()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['author' => Str::random(76)])
    );

    $response->assertJsonFragment(
      ["author" => ["The author must not be greater than 75 characters."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["author"]
      ]
    ]);
  }

  #endregion

  #region EDITORIAL

  /** @test */
  public function the_editorial_may_not_be_greater_than_75_characters()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['editorial' => Str::random(76)])
    );

    $response->assertJsonFragment(
      ["editorial" => ["The editorial must not be greater than 75 characters."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["editorial"]
      ]
    ]);
  }

  #endregion

  #region TYPE_ID

  /** @test */
  public function the_type_id_is_required()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['type_id' => null])
    );

    $response->assertJsonFragment(
      ["type_id" => ["The type id field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["type_id"]
      ]
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

    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['type_id' => $type_id])
    );

    $response->assertJsonFragment(
      ["type_id" => ["The selected type id is invalid."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["type_id"]
      ]
    ]);
  }

  #endregion

  #region UNIT_MEASURE_PROGRESS_ID

  /** @test */
  public function the_unit_measure_progress_id_is_required()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['unit_measure_progress_id' => null])
    );

    $response->assertJsonFragment(
      ["unit_measure_progress_id" => ["The unit measure progress id field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["unit_measure_progress_id"]
      ]
    ]);
  }

  /** @test */
  public function the_unit_measure_progress_id_can_not_be_a_value_different_to_UnitMeasureProgressEnum_cases_id()
  {
    $acceptedId = [
      Settings::getData(UnitMeasureProgressEnum::UNIT_VIDEOS->name, "id"),
      Settings::getData(UnitMeasureProgressEnum::UNIT_PAGES->name, "id"),
      Settings::getData(UnitMeasureProgressEnum::UNIT_HOURS->name, "id"),
      Settings::getData(UnitMeasureProgressEnum::UNIT_CHAPTERS->name, "id"),
    ];

    do {
      $unit_measure_progress_id = random_int(1, 50);
    } while (in_array($unit_measure_progress_id, $acceptedId));

    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData(['unit_measure_progress_id' => $unit_measure_progress_id])
    );

    $response->assertJsonFragment(
      ["unit_measure_progress_id" => ["The selected unit measure progress id is invalid."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["unit_measure_progress_id"]
      ]
    ]);
  }

  #endregion

  #region TOTAL_PAGES

  /** @test */
  public function the_total_pages_is_required_when_recourse_is_type_libro()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
        'total_pages' => null
      ])
    );

    $response->assertJsonFragment(
      ["total_pages" => ["The total pages field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_pages"]
      ]
    ]);
  }

  /** @test */
  public function the_total_pages_must_be_an_integer()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
        'total_pages' => Str::random(10)
      ])
    );

    $response->assertJsonFragment(
      ["total_pages" => ["The total pages must be an integer."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_pages"]
      ]
    ]);
  }

  #endregion

  #region TOTAL_CHAPTERS

  /** @test */
  public function the_total_chapters_is_required_when_recourse_is_type_libro()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
        'total_chapters' => null
      ])
    );

    $response->assertJsonFragment(
      ["total_chapters" => ["The total chapters field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_chapters"]
      ]
    ]);
  }

  /** @test */
  public function the_total_chapters_must_be_an_integer()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
        'total_chapters' => Str::random(10)
      ])
    );

    $response->assertJsonFragment(
      ["total_chapters" => ["The total chapters must be an integer."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_chapters"]
      ]
    ]);
  }
  #endregion

  #region TOTAL_VIDEOS

  /** @test */
  public function the_total_videos_is_required_when_recourse_is_type_video()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
        'total_videos' => null
      ])
    );

    $response->assertJsonFragment(
      ["total_videos" => ["The total videos field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_videos"]
      ]
    ]);
  }

  /** @test */
  public function the_total_videos_must_be_an_integer()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
        'total_videos' => Str::random(10)
      ])
    );

    $response->assertJsonFragment(
      ["total_videos" => ["The total videos must be an integer."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_videos"]
      ]
    ]);
  }

  #endregion

  #region TOTAL_HOURS

  /** @test */
  public function the_total_hours_is_required_when_recourse_is_type_video()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
        'total_hours' => null
      ])
    );

    $response->assertJsonFragment(
      ["total_hours" => ["The total hours field is required."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_hours"]
      ]
    ]);
  }

  /** @test */
  public function the_total_hours_must_be_a_time()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(
      route('recourses.store'),
      $this->recourseValidData([
        'type_id' => Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"),
        'total_hours' => Str::random(10)
      ])
    );

    $response->assertJsonFragment(
      ["total_hours" => ["The total hours format is invalid."]]
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["total_hours"]
      ]
    ]);
  }

  #endregion
}
