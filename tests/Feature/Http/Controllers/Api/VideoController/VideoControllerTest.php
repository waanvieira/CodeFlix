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

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
        $this->genre = factory(Genre::class)->create();
        $this->category = factory(Category::class)->create();
        $this->data = [
            'title' => 'Name test',
            'description' => 'description',
            'year_launched' => 2022,
            'opened' => true,
            'rating' => Video::RATING_LIST[array_rand(Video::RATING_LIST)],
            'duration' => 10
        ];
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
        $file = UploadedFile::fake()->create('video.mkv')->size(60000000);
        $data = [
            'video_file' => $file,
        ];

        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);

        $file = UploadedFile::fake()->create('video.mkv')->size(6000);
        $data = [
            'thumb_file' => $file,
        ];

        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::THUMB_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::THUMB_FILE_MAX_SIZE]);

        $file = UploadedFile::fake()->create('video.mkv')->size(2000000);
        $data = [
            'trailer_file' => $file,
        ];

        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::TRAILER_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::TRAILER_FILE_MAX_SIZE]);

        $file = UploadedFile::fake()->create('video.mkv')->size(20000);
        $data = [
            'banner_file' => $file,
        ];

        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::BANNER_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::BANNER_FILE_MAX_SIZE]);
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
    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $sendData = $this->data + ['categories_id' => [$categoriesId[0]], 'genres_id' => [$this->genre->id]];

        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $response->json('id')
        ]);


        $sendData = $this->data + [
            'categories_id' => [$categoriesId[1], $categoriesId[2]],
            'genres_id' => [$this->genre->id]
        ];
        $sendData['title'] = 'updated';

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $sendData
        );

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' => $response->json('id')
        ]);
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testSyncGenres()
    {
        $genres = factory(Genre::class, 3)->create();
        $genresId = $genres->pluck('id')->toArray();
        $categoriyId = factory(Category::class)->create()->id;
        $genres->each(function ($genre) use ($categoriyId) {
            $genre->categories()->sync($categoriyId);
        });

        $sendData = $this->data + ['categories_id' => [$categoriyId], 'genres_id' => [$genresId['0']]];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $response->json('id')
        ]);

        $sendData = $this->data + [
            'genres_id' => [$genresId[1], $genresId[2]],
            'categories_id' => [$categoriyId]
        ];

        $sendData['title'] = 'updated';
        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $sendData
        );

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1],
            'video_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2],
            'video_id' => $response->json('id')
        ]);
    }

    protected function assertHasCategory($categoriyId, $videoId)
    {
        $this->assertDatabaseHas(
            'category_video',
            [
                'category_id' => $categoriyId,
                'video_id' => $videoId
            ]
        );
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas(
            'category_genre',
            [
                'video_id' => $videoId,
                'genre_id' => $genreId
            ]
        );
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

        $this->assertStore(
            $this->data +
                [
                    'categories_id' => [$this->category->id],
                    'genres_id' => [$this->genre->id]
                ],
            $this->data
        );
    }

    /**
     * 
     * @group Video
     * @return void
     */
    public function testUpdate()
    {
        $sendData = $this->data + [
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id]
        ];

        $sendData['opened'] = true;
        $this->assertUpdate($sendData, $this->data);
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
