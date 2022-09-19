<?php

namespace Tests\Feature\ProgressHistory;

use App\Http\Requests\RecoursePostRequest;
use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Recourse;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProgressHistoryStoreRequestTest extends TestCase
{
    use RefreshDatabase;
    use ProgressHistoryDataTrait;

    #region DONE Field

    /** @test */
    public function the_done_is_required()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'done' => null
            ])
        );

        $response->assertJsonStructure([
            'error' => ['done']
        ]);
    }

    /** @test */
    public function the_done_may_not_be_equals_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'done' => 0
            ])
        );

        $response->assertJsonStructure([
            'error' => ['done']
        ]);
    }

    /** @test */
    public function the_done_may_not_be_below_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'done' => -15
            ])
        );

        $response->assertJsonStructure([
            'error' => ['done']
        ]);
    }

    /** @test */
    public function the_done_must_be_an_integer()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'done' => Str::random(10)
            ])
        );

        $response->assertJsonStructure([
            'error' => ['done']
        ]);
    }

    #endregion

    #region PENDING Field

    /** @test */
    public function the_pending_is_required()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'pending' => null
            ])
        );

        $response->assertJsonStructure([
            'error' => ['pending']
        ]);
    }

    /** @test */
    public function the_pending_may_not_be_below_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'pending' => -15
            ])
        );

        $response->assertJsonStructure([
            'error' => ['pending']
        ]);
    }

    /** @test */
    public function the_pending_can_be_be_equals_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'pending' => 0
            ])
        );

        $response->assertStatus(201);
    }

    /** @test */
    public function the_pending_must_be_an_integer()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'pending' => Str::random(10)
            ])
        );

        $response->assertJsonStructure([
            'error' => ['pending']
        ]);
    }

    #endregion

    #region DATE Field

    /** @test */
    public function the_date_is_required()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'date' => null
            ])
        );

        $response->assertJsonStructure([
            'error' => ['date']
        ]);
    }

    /** @test */
    public function the_date_must_be_a_date()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'date' => Str::random(10)
            ])
        );

        $response->assertJsonStructure([
            'error' => ['date']
        ]);
    }

    #endregion

    #region COMMENT Field

    /** @test */
    public function the_comment_may_not_be_greater_than_100_characters()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData(['comment' => Str::random(101)])
        );

        $response->assertJsonStructure([
            'error' => ['comment']
        ]);
    }

    /** @test */
    public function the_comment_can_be_nullable()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData(['comment' => null])
        );

        $response->assertStatus(201);
    }

    #endregion

}
