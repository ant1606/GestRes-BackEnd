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
    use ProgressHistoryDataTrait;
    /** @test */
    public function a_progress_can_be_register()
    {
        // $this->withoutExceptionHandling();

        $recourse = Recourse::factory()->create();

        $recourseTotal = $recourse->total_pages ? $recourse->total_pages : $recourse->total_videos;
        $done = floor($recourseTotal / 2);
        $pending = $recourseTotal - $done;
        $dateProgress = Carbon::now()->toDateString();

        $progress = $this->progressHistoryValidData([
            'realizado' => $done,
            'pendiente' => $pending,
            'fecha' => $dateProgress,
            'comentario' => null,
        ]);

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

    /** @test */
    public function a_progress_can_not_be_register_with_a_invalid_date()
    {
//        $this->withoutExceptionHandling();
        $recourse = Recourse::factory()->create();

        $recourseTotal = $recourse->total_pages ? $recourse->total_pages : $recourse->total_videos;
        $done = floor($recourseTotal / 2);
        $pending = $recourseTotal - $done;
        $dateProgress = Carbon::now()->subDays(15)->toDateString();

        $progress = $this->progressHistoryValidData([
            'realizado' => $done,
            'pendiente' => $pending,
            'fecha' => $dateProgress,
            'comentario' => null,
        ]);

        $response = $this->postJson(route('progress.store', $recourse), $progress);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('progress_histories', 1);
    }
}
