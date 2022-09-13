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
}
