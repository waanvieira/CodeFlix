<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Lang;
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
        $this->category = factory(Genre::class)->create();
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

        $this->assertInvalidationInStoreStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        // $response
        //     ->assertStatus(422)
        //     ->assertJsonValidationErrors(['title'])
        //     ->assertJsonMissingValidationErrors(['opened'])
        //     ->assertJsonFragment([
        //         Lang::get('validation.required', ['attribute' => 'title'])
        //     ]);
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

        $this->assertInvalidationInStoreStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mkv')->size(20000);
        $data = [
            'file' => $file,
        ];
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
        $this->assertInvalidationInStoreStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
        // $response
        //     ->assertStatus(422)
        //     ->assertJsonValidationErrors(['opened'])
        //     ->assertJsonFragment([
        //         Lang::get('validation.boolean', ['attribute' => 'is opened'])
        //     ]);
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

        $this->assertInvalidationInStoreStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'genres_id' => 'a'
        ];

        $this->assertInvalidationInStoreStoreAction($data, 'array');
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

        $this->assertInvalidationInStoreStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');



        $data = [
            'genres_id' => [100]
        ];

        $this->assertInvalidationInStoreStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    /**
     * 
     * @group Video
     * @return void
     */
    // public function testStore()
    // {
    //     \Storage::fake();
    //     $file = UploadedFile::fake()->create('video.mp4');

    //     $data = [
    //         'title' => 'Name test',
    //         'description' => 'description',
    //         'year_launched' => 2022,
    //         'opened' => 1,
    //         'rating' => Video::RATING_LIST[array_rand(Video::RATING_LIST)],
    //         'duration' => 10,
    //         'categories_id' => [$this->category->id],
    //         'genres_id' => [$this->genre->id],
    //         'file' => $file
    //     ];

    //     $this->assertStore($data, $data);
    // }

    /**
     * 
     * @group Video
     * @return void
     */
    // public function testUpdate()
    // {
    //     $video = factory(Video::class)->create([
    //         'opened' => false
    //     ]);

    //     $response = $this->json('PUT', route('videos.update', ['video' => $video->id]), [
    //         'title' => 'Name updated'
    //     ]);

    //     $response
    //         ->assertStatus(200)
    //         ->assertJsonFragment([
    //             'title' => 'Name updated'
    //         ]);
    // }

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
