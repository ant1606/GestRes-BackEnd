<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Recourse;
use App\Models\ProgressHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;

class ProgressHistoryTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function a_progress_belongs_to_a_recourse()
  {
    $this->withoutExceptionHandling();

    $progress = ProgressHistory::factory()->create();

    $this->assertInstanceOf(Recourse::class, $progress->recourse);
  }
}
