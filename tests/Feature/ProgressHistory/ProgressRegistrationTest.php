<?php

namespace Tests\Feature\ProgressHistory;

use App\Models\ProgressHistory;
use App\Models\Recourse;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProgressRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_progress_can_be_register()
    {
        // $this->withoutExceptionHandling();

        $recourse = Recourse::factory()->create();

        $recourseTotal = $recourse->total_pages ? $recourse->total_pages : $recourse->total_videos;
        $done = floor($recourseTotal / 2);
        $pending = $recourseTotal - $done;
        $dateProgress = Carbon::now()->toDateString();

        $progress = [
            'done' => $done,
            'pending' => $pending,
            'date' => $dateProgress,
            'comment' => null,
        ];

        $reponse = $this->postJson(route('progress.store', $recourse), $progress);

        $reponse->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount('progress_histories', 2);

        $this->assertDatabaseHas('progress_histories', [
            'done' => $done,
            'pending' => $pending,
            'date' => $dateProgress,
            'comment' => null,
        ]);
    }
}
