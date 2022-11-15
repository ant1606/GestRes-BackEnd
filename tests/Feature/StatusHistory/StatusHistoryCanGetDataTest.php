<?php

namespace Tests\Feature\StatusHistory;

use App\Models\StatusHistory;
use App\Models\Recourse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StatusHistoryCanGetDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function get_all_progress_history_from_a_recourse_exists(){
        $recourse = Recourse::factory()->create();
        $statusHistories = StatusHistory::factory(4)->create([
            "recourse_id" => $recourse->id
        ]);

        $response = $this->getJson(route('status.index', $recourse));

        $response->assertStatus(Response::HTTP_OK);
        // Tener en cuenta que puede variar segun la cantidad de resultados por pagina en ApiResponser
        $response->assertJsonCount(5, "data");
    }

    /** @test **/
    public function can_not_get_progress_history_from_a_recourse_that_not_exists(){
        $recourse = Recourse::factory()->create();
        $statusHistories = StatusHistory::factory(4)->create([
            "recourse_id" => $recourse->id
        ]);

        $response = $this->getJson(route('status.index', 152));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJsonStructure([
            "error"=>[
                [
                    "status", "detail"
                ]
            ]
        ]);
    }
}
