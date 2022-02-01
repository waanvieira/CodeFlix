<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 
     * @group Category
     * @return void
     */
    public function testGetAll()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200)->assertJson([$category->toArray()]);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));
        $response->assertStatus(200)->assertJson($category->toArray());
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testInvalidationData()
    {
        //Método comum. faz direcionamento para uma págin
        // $response = $this->post(route('categories.store'), []);
        //Método correto para testar de fato uma API
        $response = $this->json('POST', route('categories.store'), []);
        //Verificar o conteudo da resposta para um debug melhor
        // dd($response->content());
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testInvalidationStore()
    {
        $response = $this->json('POST', route('categories.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testInvalidationUpdate()
    {
        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
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
     * @group Category
     * @return void
     */
    public function testStore()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'Name test'
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());

        $this->assertEquals($category->name, 'Name test');
        $this->assertTrue($category->is_active);
        $this->assertNull($category->description);

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'update name',
            'description' => 'description',
            'is_active' => false
        ]);

        $response->assertJsonFragment([
            'description' => 'description',
            'is_active' => false
        ]);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'Name update',
            'description' => 'updated',
            'is_active' => true
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Name update',
                'description' => 'updated',
                'is_active' => true,
            ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'Name update',
            'description' => ''
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Name update',
                'description' => null
            ]);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testDestroy()
    {
        $category = factory(Category::class)->create([]);
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $category->id]));
        $response->assertStatus(204);
    }
}
