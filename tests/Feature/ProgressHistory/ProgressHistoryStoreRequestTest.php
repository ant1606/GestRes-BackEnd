<?php

namespace Tests\Feature\ProgressHistory;

use Tests\TestCase;
use App\Models\Recourse;
use Illuminate\Support\Str;
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
                'realizado' => null
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "realizado"
        ]);
    }

    /** @test */
    public function the_done_may_not_be_equals_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'realizado' => 0
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "realizado"
        ]);
    }

    /** @test */
    public function the_done_may_not_be_below_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'realizado' => -15
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "realizado"
        ]);
    }

    /** @test */
    public function the_done_must_be_an_integer()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'realizado' => Str::random(10)
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "realizado"
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
                'pendiente' => null
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "pendiente"
        ]);
    }

    /** @test */
    public function the_pending_may_not_be_below_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'pendiente' => -15
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "pendiente"
        ]);
    }

    /** @test */
    public function the_pending_can_be_be_equals_to_zero()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'pendiente' => 0
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
                'pendiente' => Str::random(10)
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "pendiente"
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
                'fecha' => null
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "fecha"
        ]);
    }

    /** @test */
    public function the_date_must_be_a_date()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData([
                'fecha' => Str::random(10)
            ])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "fecha"
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
            $this->progressHistoryValidData(['comentario' => Str::random(101)])
        );

        $response->assertJsonFragment([
            "status" => \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
            "inputName" => "comentario"
        ]);
    }

    /** @test */
    public function the_comment_can_be_nullable()
    {
        $recourse = Recourse::factory()->create();
        $response = $this->postJson(
            route('progress.store', $recourse),
            $this->progressHistoryValidData(['comentario' => null])
        );

        $response->assertStatus(201);
    }

    #endregion

}
