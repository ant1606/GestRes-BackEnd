<?php

namespace Tests\Feature\ProgressHistory;

use App\Enums\UnitMeasureProgressEnum;
use App\Models\Settings;
use Tests\TestCase;
use App\Models\Recourse;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProgressHistoryStoreRequestTest extends TestCase
{
  use RefreshDatabase;
  use ProgressHistoryDataTrait;

  #region ADVANCED Field

  /** @test */
  public function the_advanced_is_required()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $recourse_measure_is_hour =Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData($recourse_measure_is_hour, ['advanced' => null])
    );

    $response->assertJsonFragment([
      "advanced" => ["The advanced field is required."],
    ]);

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>[
          "advanced"
        ]
      ]
    ]);
    $response->assertJsonFragment(["advanced"=>["The advanced field is required."]]);
  }

  /** @test */
  public function the_advanced_may_not_be_equals_to_zero()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $recourse_measure_is_hour =Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData($recourse_measure_is_hour, ['advanced' => $recourse_measure_is_hour ? "00:00:00" : "0"])
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>[
          "advanced"
        ]
      ]
    ]);
    $response->assertJsonFragment([
      "advanced" => ["El valor avanzado debe ser mayor a 00:00:00 o a 0"],
    ]);
  }

  /** @test */
  public function the_advanced_may_not_be_below_to_zero()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $recourse_measure_is_hour =Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData($recourse_measure_is_hour, ['advanced' => $recourse_measure_is_hour ?  "00:00:00" : "-15"])
    );

    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>[
          "advanced"
        ]
      ]
    ]);
    $response->assertJsonFragment([
      "advanced" => ["El valor avanzado debe ser mayor a 00:00:00 o a 0"],
    ]);
  }

  #endregion

  #region PENDING Field - Ahora es autogenerado en el Controller

  // /** @test */
  // public function the_pending_is_required()
  // {
  //   $user = User::factory()->create();
  //   $recourse = Recourse::factory()->create(["user_id" => $user->id]);
  //   $response = $this->actingAs($user)->postJson(
  //     route('progress.store', $recourse),
  //     $this->progressHistoryValidData([
  //       'pending' => null
  //     ])
  //   );

  //   $response->assertJsonFragment([
  //     "pending" => ["The advanced must be a number."],
  //   ]);

  //   $response->assertJsonStructure([
  //     "error" => [
  //       "status", "detail"
  //     ]
  //   ]);
  // }

  // /** @test */
  // public function the_pending_may_not_be_below_to_zero()
  // {
  //   $user = User::factory()->create();
  //   $recourse = Recourse::factory()->create(["user_id" => $user->id]);
  //   $response = $this->actingAs($user)->postJson(
  //     route('progress.store', $recourse),
  //     $this->progressHistoryValidData([
  //       'pending' => -15
  //     ])
  //   );

  //   $response->assertJsonFragment([
  //     "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
  //     "inputName" => "pending"
  //   ]);
  // }

  // /** @test */
  // public function the_pending_can_be_be_equals_to_zero()
  // {
  //   $user = User::factory()->create();
  //   $recourse = Recourse::factory()->create(["user_id" => $user->id]);
  //   $response = $this->actingAs($user)->postJson(
  //     route('progress.store', $recourse),
  //     $this->progressHistoryValidData([
  //       'pending' => 0
  //     ])
  //   );

  //   $response->assertStatus(201);
  // }

  // /** @test */
  // public function the_pending_must_be_an_integer()
  // {
  //   $user = User::factory()->create();
  //   $recourse = Recourse::factory()->create(["user_id" => $user->id]);
  //   $response = $this->actingAs($user)->postJson(
  //     route('progress.store', $recourse),
  //     $this->progressHistoryValidData([
  //       'pending' => Str::random(10)
  //     ])
  //   );

  //   $response->assertJsonFragment([
  //     "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
  //     "inputName" => "pending"
  //   ]);
  // }

  #endregion

  #region DATE Field

  /** @test */
  public function the_date_is_required()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $recourse_measure_is_hour =Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData( $recourse_measure_is_hour, ['date' => null])
    );

    $response->assertJsonFragment([
      "date" => ["The date field is required."],
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>[
          "date"
        ]
      ]
    ]);
  }

  /** @test */
  public function the_date_must_be_a_date()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $recourse_measure_is_hour =Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData($recourse_measure_is_hour, ['date' => Str::random(10)])
    );

    $response->assertJsonFragment([
      "date" => ["The date is not a valid date."],
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>[
          "date"
        ]
      ]
    ]);
  }

  #endregion

  #region COMMENT Field

  /** @test */
  public function the_comment_may_not_be_greater_than_1000_characters()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $recourse_measure_is_hour =Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData( $recourse_measure_is_hour, ['comment' => Str::random(1001)])
    );

    $response->assertJsonFragment([
      "comment" => ["The comment must not be greater than 1000 characters."],
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>[
          "comment"
        ]
      ]
    ]);
  }

  /** @test */
  public function the_comment_can_be_nullable()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $recourse_measure_is_hour =Settings::getKeyfromId($recourse['unit_measure_progress_id']) === UnitMeasureProgressEnum::UNIT_HOURS->name;
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData($recourse_measure_is_hour, ['comment' => null])
    );

    $response->assertStatus(201);
    $response->assertJsonFragment(["status"=>"success"]);
    $this->assertDatabaseCount('progress_histories', 2 );

  }

  #endregion

}
