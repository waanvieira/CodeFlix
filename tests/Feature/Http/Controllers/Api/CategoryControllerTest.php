<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResources;

    private $category;
    private $serializedFields = [
        'id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

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
        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => [],
            ]);

        $resource = CategoryResource::collection(collect([$this->category]));
        $this->assertResource($response, $resource);
        // $response->assertStatus(200)->assertJson([$this->category->toArray()]);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            
        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);
        // $response->assertStatus(200)->assertJson($this->category->toArray());
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testAssertInvalidationRequired()
    {
        $data = [
            'name' => '',
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    /**
     * 
     * @group Category
     * @return void
     */
    // public function testInvalidationStoreAndUpdate()
    // {
    //     $data = [
    //         'name' => ''
    //     ];

    //     $this->assertInvalidationInStoreAction($data, 'required');
    //     $this->assertInvalidationInUpdateAction($data, 'required');

    //     $data = [
    //         'name' => str_repeat('a', 256)
    //     ];

    //     $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
    //     $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

    //     $data = [
    //         'is_active' => 'a'
    //     ];
    //     $this->assertInvalidationInStoreAction($data, 'boolean');
    //     $this->assertInvalidationInUpdateAction($data, 'boolean');

    // $response = $this->json('POST', route('categories.store'), []);
    // $this->assertInvalidationRequired($response);

    // $response = $this->json('POST', route('categories.store'), [
    //     'name' => str_repeat('a', 256),
    //     'is_active' => 'a'
    // ]);

    // $this->assertInvalidationMax($response);
    // $this->assertInvalidationBoolean($response);
    // }

    public function testInvalidationData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testStore()
    {
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore($data,
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

        $data = [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ];
        $this->assertStore($data, $data + ['description' => 'description', 'is_active' => false]);

        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);
    }

    /**
     * 
     * @group Category
     * @return void
     */
    public function testUpdate()
    {
        $data = [
            'name' => 'test',
            'description' => 'test',
            'is_active' => true
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

        // $response->assertJsonStructure([
        //     'data' => $data
        // ]);

        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);

        $data = [
            'name' => 'test',
            'description' => '',
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));


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
