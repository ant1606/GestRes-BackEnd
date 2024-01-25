<?php

namespace Tests\Feature\ProgressHistory;

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
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData([
        'advanced' => null
      ])
    );

    $response->assertJsonFragment([
      "advanced" => ["The advanced field is required."],
    ]);

    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }

  /** @test */
  public function the_advanced_may_not_be_equals_to_zero()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData([
        'advanced' => 0
      ])
    );

    $response->assertJsonFragment([
      "advanced" => ["The advanced must be at least 1."],
    ]);

    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }

  /** @test */
  public function the_advanced_may_not_be_below_to_zero()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData([
        'advanced' => -15
      ])
    );

    $response->assertJsonFragment([
      "advanced" => ["The advanced must be at least 1."],
    ]);

    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }

  /** @test */
  public function the_advanced_must_be_an_integer()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData([
        'advanced' => Str::random(10)
      ])
    );

    $response->assertJsonFragment([
      "advanced" => ["The advanced must be a number."],
    ]);

    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
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
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData([
        'date' => null
      ])
    );

    $response->assertJsonFragment([
      "date" => ["The date field is required."],
    ]);

    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }

  /** @test */
  public function the_date_must_be_a_date()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData([
        'date' => Str::random(10)
      ])
    );

    $response->assertJsonFragment([
      "date" => ["The date is not a valid date."],
    ]);

    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }

  #endregion

  #region COMMENT Field

  /** @test */
  public function the_comment_may_not_be_greater_than_100_characters()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData(['comment' => Str::random(101)])
    );

    $response->assertJsonFragment([
      "comment" => ["The comment must not be greater than 100 characters."],
    ]);

    $response->assertJsonStructure([
      "error" => [
        "status", "detail"
      ]
    ]);
  }

  /** @test */
  public function the_comment_can_be_nullable()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $response = $this->actingAs($user)->postJson(
      route('progress.store', $recourse),
      $this->progressHistoryValidData(['comment' => null])
    );

    // dd($response->getContent());
    $response->assertStatus(201);
  }

  #endregion

}
