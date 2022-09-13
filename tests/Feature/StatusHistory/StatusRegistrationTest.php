<?php

namespace Tests\Feature\StatusHistory;

use App\Enums\StatusRecourseEnum;
use App\Models\Recourse;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StatusRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_status_can_be_register()
    {
        // $this->withoutExceptionHandling();
        $recourse = Recourse::factory()->create();

        $date = Carbon::now()->toDateString();
        $status = [
            'status_id' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
            'date' => $date,
            'comment' => 'Curso a punto de empezar'
        ];

        $response = $this->postJson(route('status.store', $recourse->id), $status);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('status_histories', [
            'recourse_id' => $recourse->id,
            'status_id' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
            'date' => $date,
            'comment' => 'Curso a punto de empezar'
        ]);

        $this->assertDatabaseCount('status_histories', 2);
    }

    /** @test */
    public function a_status_can_not_be_register_with_a_invalid_date()
    {
        $this->withoutExceptionHandling();
        $recourse = Recourse::factory()->create();

        $date = Carbon::now()->subDays(15);
        $status = [
            'status_id' => Settings::getData(StatusRecourseEnum::STATUS_POREMPEZAR->name, "id"),
            'date' => $date,
            'comment' => 'Curso a punto de empezar'
        ];

        $response = $this->postJson(route('status.store', $recourse->id), $status);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount('status_histories', 1);
    }
}
