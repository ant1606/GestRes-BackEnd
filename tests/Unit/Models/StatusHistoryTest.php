<?php

namespace Tests\Unit\Models;

// use PHPUnit\Framework\TestCase;

use App\Models\Recourse;
use App\Models\StatusHistory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_status_belongs_to_a_recourse()
    {
        $status = StatusHistory::factory()->create();

        $this->assertInstanceOf(Recourse::class, $status->recourse);
    }
}
