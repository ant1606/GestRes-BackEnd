<?php

namespace Tests\Unit\Models;

use App\Models\Recourse;
use App\Models\StatusHistory;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
// use PHPUnit\Framework\TestCase;

class RecourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recourse_has_many_tags()
    {
        // $this->withExceptionHandling();

        $recourse = Recourse::factory()->create();
        $tags = Tag::factory(3)->create();

        $recourse->tags()->syncWithoutDetaching($tags->pluck('id'));

        $this->assertInstanceOf(Tag::class, $recourse->tags->first());
    }

    /** @test */
    public function a_recourse_has_many_status()
    {
        // $this->withExceptionHandling();

        $recourse = Recourse::factory()->create();

        StatusHistory::factory()->create([
            'recourse_id' => $recourse->id
        ]);

        $this->assertInstanceOf(StatusHistory::class, $recourse->status->first());
    }
}
