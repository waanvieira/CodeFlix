<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
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
    public function testInvalidationData()
    {
        $data = [
            'title' => ''
        ];

        $this->assertInvalidationInStoreStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'title' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStoreStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'opened' => 'a'
        ];
        $this->assertInvalidationInStoreStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testInvalidationStore()
    {
        $response = $this->json('POST', route('videos.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('videos.store'), [
            'title' => str_repeat('a', 256),
            'opened' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testInvalidationUpdate()
    {
        $response = $this->json('PUT', route('videos.update', ['video' => $this->video->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $this->video->id]),
            [
                'title' => str_repeat('a', 256),
                'opened' => 'a'
            ]
        );

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    public function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title'])
            ->assertJsonMissingValidationErrors(['opened'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'title'])
            ]);
    }

    public function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'title', 'max' => 255])
            ]);
    }

    public function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['opened'])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is opened'])
            ]);
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testStore()
    {

        $data = [
            'title' => 'Name test',
            'description' => 'description',
            'year_launched' => 2022,
            'opened' => 1,
            'rating' => Video::RATING_LIST[array_rand(Video::RATING_LIST)],
            'duration' => 10
        ];

        $this->assertStore($data, $data);
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testUpdate()
    {
        $video = factory(Video::class)->create([
            'opened' => 0
        ]);

        $response = $this->json('PUT', route('videos.update', ['video' => $video->id]), [
            'title' => 'Name updated'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Name updated'
            ]);
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
