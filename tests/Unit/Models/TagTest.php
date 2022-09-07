<?php

namespace Tests\Unit\Models;

use App\Models\Recourse;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
// use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function a_tag_belongs_to_many_recourse()
    {
        $this->withExceptionHandling();

        $tag = Tag::factory()->create();
        $recourse = Recourse::factory(3)->create();

        $tag->recourses()->syncWithoutDetaching($recourse->pluck('id'));

        $this->assertInstanceOf(Recourse::class, $tag->recourses->first());
    }
}
