<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private $genre;

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
            'name' => ''
        ];

        $this->assertInvalidationInStoreStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStoreStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testInvalidationStore()
    {
        $response = $this->json('POST', route('genres.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testInvalidationUpdate()
    {
        $response = $this->json('PUT', route('genres.update', ['genre' => $this->genre->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $this->genre->id]),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    public function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    public function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    public function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testStore()
    {

        $data = [
            'name' => 'Name test'
        ];

        $this->assertStore($data, $data + ['is_active' => true]);

        // $response = $this->json('POST', route('genres.store'), [
        //     'name' => 'Name test'
        // ]);

        // $id = $response->json('id');
        // $genre = Genre::find($id);

        // $response
        //     ->assertStatus(201)
        //     ->assertJson($genre->toArray());

        // $this->assertEquals($genre->name, 'Name test');
        // $this->assertTrue($genre->is_active);

        // $response = $this->json('POST', route('genres.store'), [
        //     'name' => 'update name',
        //     'is_active' => false
        // ]);

        // $response->assertJsonFragment([
        //     'is_active' => false
        // ]);
    }

    /**
     * 
     * @group Genre
     * @return void
     */
    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name' => 'Name updated',
            'is_active' => true
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Name updated',
                'is_active' => true,
            ]);
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
