<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use Tests\TestCase;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
//Testes unitarios
class VideoTest extends TestCase
{
    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();
    }

    /**
     *
     * @group video
     * @return void
     */
    public function testsFillableAttributes()
    {
        $expected = ['title', 'description', 'year_launched', 'opened', 'rating', 'duration'];
        $this->assertEquals($expected, $this->video->getFillable());
    }

    /**
     *
     * @group video
     * @return void
     */
    public function testIfUserTraits()
    {
        $traits = [
            SoftDeletes::class, UuidTrait::class
        ];

        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    /**
     *
     * @group video
     * @return void
     */
    public function testCasts()
    {
        $casts = [
            'id' => 'string',
            'opened' => 'boolean',
            'year_launched' => 'integer',
            'duration' => 'integer'
        ];
        $this->assertEquals($casts, $this->video->getCasts());
    }

    /**
     *
     * @group video
     * @return void
     */
    public function testIncrementing()
    {
        $this->assertFalse($this->video->incrementing);
    }

    /**
     *
     * @group video
     * @return void
     */
    public function testDatesAttributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $this->assertEqualsCanonicalizing($dates, $this->video->getDates());
    }
}
