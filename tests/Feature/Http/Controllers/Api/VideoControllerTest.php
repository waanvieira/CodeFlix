<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
        $this->genre = factory(Genre::class)->create();
        $this->category = factory(Category::class)->create();
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testGetAll()
    {
        $response = $this->get(route('videos.index'));
        $response->assertStatus(200)->assertJson([$this->video->toArray()]);
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response->assertStatus(200)->assertJson($this->video->toArray());
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testAssertInvalidationRequired()
    {
        $data = [
            'title' => '',
            'year_launched' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testAssertInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256),
        ];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mkv')->size(200000);
        $data = [
            'file' => $file,
        ];

        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);

    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testAssertInvalidationBoolean()
    {
        $data = [
            'opened' => 's'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testAssertInvalidationArray()
    {
        $data = [
            'categories_id' => 'a'
        ];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'genres_id' => 'a'
        ];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testAssertInvalidationExists()
    {
        $data = [
            'categories_id' => [100]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');



        $data = [
            'genres_id' => [100]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testStore()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4')->size(200);
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();        
        $genre->categories()->sync($category->id);
        
        $data = [
            'title' => 'Name test',
            'description' => 'description',
            'year_launched' => 2022,
            'opened' => true,
            'rating' => Video::RATING_LIST[array_rand(Video::RATING_LIST)],
            'duration' => 10,
            'video_file' => $file
        ];

        $this->assertStore($data + 
                    ['categories_id' => [$this->category->id],
                    'genres_id' => [$this->genre->id]]
        , $data);

    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testUpdate()
    {
        $video = factory(Video::class)->create([
            'opened' => false
        ]);

        $data = [
            'title' => 'Name update',
            'description' => 'description update',
            'year_launched' => 2022,
            'opened' => 1,
            'rating' => Video::RATING_LIST[array_rand(Video::RATING_LIST)],
            'duration' => 10,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id],
        ];

        $this->assertUpdate($data, $video);
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testDestroy()
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    public function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    public function model()
    {
        return Video::class;
    }
}
