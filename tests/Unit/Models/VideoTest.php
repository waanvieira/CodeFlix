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
     * @group Video
     * @return void
     */
    public function testsFillableAttributes()
    {
        $expected = ['title', 'video_file', 'description', 'year_launched', 'opened', 'rating', 'duration'];
        $this->assertEquals($expected, $this->video->getFillable());
    }

    /**
     *
     * @group Video
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
     * @group Video
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
     * @group Video
     * @return void
     */
    public function testIncrementing()
    {
        $this->assertFalse($this->video->incrementing);
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testDatesAttributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $this->assertEqualsCanonicalizing($dates, $this->video->getDates());
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testRatingList()
    {
        $rating = [
            'L', '10', '12', '14', '16', '18'
        ];

        $videoRatingList = Video::RATING_LIST;
        $this->assertEquals($rating, $videoRatingList);
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testVideoSizeMax()
    {
        $this->assertEquals(100000, Video::VIDEO_FILE_MAX_SIZE);
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testBannerSizeMax()
    {
        $this->assertEquals(1000, Video::BANNER_FILE_MAX_SIZE);
    }

    /**
     *
     * @group Video
     * @return void
     */
    public function testTrailerSizeMax()
    {
        $this->assertEquals(10000, Video::TRAILER_FILE_MAX_SIZE);
    }
}
