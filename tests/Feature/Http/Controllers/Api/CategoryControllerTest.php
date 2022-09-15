<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private $category;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    //Outro m�todo que uso, por�m toda vez que for utilizar cria uma nova category
    // com o m�todo acima n�o faz isso e fica mais elegante
    // private function createCategory()
    // {
    //     return factory(Category::class)->create();
    // }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testGetAll()
    {
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200)->assertJson([$this->category->toArray()]);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response->assertStatus(200)->assertJson($this->category->toArray());
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testInvalidationData()
    {
        //M�todo comum. faz direcionamento para uma p�gin
        // $response = $this->post(route('categories.store'), []);
        //M�todo correto para testar de fato uma API
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
    public function testInvalidationStoreAndUpdate()
    {
        $data = [
            'name' => ''
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

        // $response = $this->json('POST', route('categories.store'), []);
        // $this->assertInvalidationRequired($response);

        // $response = $this->json('POST', route('categories.store'), [
        //     'name' => str_repeat('a', 256),
        //     'is_active' => 'a'
        // ]);

        // $this->assertInvalidationMax($response);
        // $this->assertInvalidationBoolean($response);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testInvalidationUpdate()
    {
        $response = $this->json('PUT', route('categories.update', ['category' => $this->category->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $this->category->id]),
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
        $this->assertInvalidationFields($response, ['name'], 'required');
        $response->assertJsonMissingValidationErrors(['is_active']);
    }

    public function assertInvalidationMax(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['name'], 'max.string',  ['max' => 255]);
    }

    public function assertInvalidationBoolean(TestResponse $response)
    {
        $this->assertInvalidationFields($response, ['is_active'], 'boolean');
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testStore()
    {
        $data = [
            'name' => 'Name test'
        ] ;
        
        $this->assertStore($data, $data + ['description' => null, 'is_active' => true]);
         
        //Forma antiga para validar
        // $response = $this->json('POST', route('categories.store'), [
        //     'name' => 'Name test'
        // ]);

        // $id = $response->json('id');
        // $category = Category::find($id);

        // $response
        //     ->assertStatus(201)
        //     ->assertJson($category->toArray());

        // $this->assertEquals($category->name, 'Name test');
        // $this->assertTrue($category->is_active);
        // $this->assertNull($category->description);

        // $response = $this->json('POST', route('categories.store'), [
        //     'name' => 'update name',
        //     'description' => 'description',
        //     'is_active' => false
        // ]);

        // $response->assertJsonFragment([
        //     'description' => 'description',
        //     'is_active' => false
        // ]);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);

        $data = [
            'name' => 'Name test',
            'description' => 'testing',
            'is_active' => false
        ] ;

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'Name test',
            'description' => ''
        ];

        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        //M�todos antigos, apenas para consulta
        // $data = [
        //     'name' => 'Name test',
        //     'description' => 'testing',
        //     'is_active' => false
        // ] ;

        // $this->assertUpdate($data, $data);

        // $category = factory(Category::class)->create([
        //     'description' => 'description',
        //     'is_active' => false
        // ]);

        // $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
        //     'name' => 'Name update',
        //     'description' => 'updated',
        //     'is_active' => true
        // ]);

        // $response
        //     ->assertStatus(200)
        //     ->assertJsonFragment([
        //         'name' => 'Name update',
        //         'description' => 'updated',
        //         'is_active' => true,
        //     ]);

        // $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
        //     'name' => 'Name update',
        //     'description' => ''
        // ]);

        // $response
        //     ->assertStatus(200)
        //     ->assertJsonFragment([
        //         'name' => 'Name update',
        //         'description' => null
        //     ]);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testDestroy()
    {
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $this->category->id]));
        $response->assertStatus(204);
    }

    public function routeStore()
    {
        return route('categories.store');
    }

    public function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    public function model()
    {
        return Category::class;
    }
}
