<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Genre;
use Tests\Exceptions\TestException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private $genre;
    private $fieldsSerialized = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testGetAll()
    {
        $response = $this->get(route('genres.index'));
        $response->assertStatus(200)->assertJson([$this->genre->toArray()]);
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response->assertStatus(200)->assertJson($this->genre->toArray());
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'categories_id' => ''
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testStore()
    {
        $categoriesId = factory(Category::class)->create()->id;
        $data = [
            'name' => 'test'
        ];

        $response = $this->assertStore(
            $data + ['categories_id' => [$categoriesId]],
            $data + ['is_active' => true, 'deleted_at' => null]
        );

        $response->assertJsonStructure([
            'created_at',
            'updated_at'
        ]);

        $this->assertHasCategory($response->json('id'), $categoriesId);

        $data = [
            'name' => 'test',
            'is_active' => false
        ];

        $this->assertStore(
            $data + ['categories_id' => [$categoriesId]],
            $data + ['is_active' => false]
        );
    }

    protected function assertHasCategory($genreId, $categoriyId)
    {
        $this->assertDatabaseHas(
            'category_genre',
            [
                'genre_id' => $genreId,
                'category_id' => $categoriyId
            ]
        );
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    // public function testSave()
    // {
    //     $categoryId = factory(Category::class)->create()->id;
    //     $data = [
    //         [
    //             'send_data' => [
    //                 'name' => 'test',
    //                 'categories_id' => [$categoryId]
    //             ],
    //             'test_data' => [
    //                 'name' => 'test',
    //                 'is_active' => true
    //             ]
    //         ],
    //         [
    //             'send_data' => [
    //                 'name' => 'test',
    //                 'is_active' => false,
    //                 'categories_id' => [$categoryId]
    //             ],
    //             'test_data' => [
    //                 'name' => 'test',
    //                 'is_active' => false
    //             ]
    //         ]
    //     ];

    //     foreach ($data as $test) {
    //         $response = $this->assertStore($test['send_data'], $test['test_data']);
    //         $response->assertJsonStructure([
    //             'data' => $this->fieldsSerialized
    //         ]);
    //         $this->assertResource($response, new GenreResource(
    //             Genre::find($response->json('id'))
    //         ));
    //         $response = $this->assertUpdate($test['send_data'], $test['test_data']);
    //         $response->assertJsonStructure([
    //             'data' => $this->fieldsSerialized
    //         ]);
    //         $this->assertResource($response, new GenreResource(
    //             Genre::find($response->json('id'))
    //         ));
    //     }
    // }

    /**
     * 
     * @group Genre
     * @return void
     */
    // public function testUpdate()
    // {
    //     $genre = factory(Genre::class)->create([
    //         'is_active' => false,
    //         'categories_id' => 1
    //     ]);

    //     $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
    //         'name' => 'Name updated',
    //         'is_active' => true
    //     ]);

    //     $response
    //         ->assertStatus(200)
    //         ->assertJsonFragment([
    //             'name' => 'Name updated',
    //             'is_active' => true,
    //         ]);
    // }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesId[0]]
        ];

        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('id')
        ]);

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ];
        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $response->json('id')]),
            $sendData
        );
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[1],
            'genre_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[2],
            'genre_id' => $response->json('id')
        ]);
    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test',
            ]);

        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new Execption());

        $request = \Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->store($request);
        } catch (Exception $exception) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test',
            ]);

        $controller
            ->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }
    /**
     * 
     * @group Genre
     * @return void
     */
    public function testDestroy()
    {
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]));
        $response->assertStatus(204);
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    public function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    public function model()
    {
        return Genre::class;
    }
}
