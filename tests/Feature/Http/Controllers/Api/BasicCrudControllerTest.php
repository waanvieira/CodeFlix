<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{
    use DatabaseMigrations;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::destroyTable();
        CategoryStub::createTable();
        $this->category = CategoryStub::create(['name' => 'testing', 'description' => 'test']);
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::destroyTable();
        parent::tearDown();
    }

    /**
     * 
     * @group CategoryStub
     * @return void
     */
    public function testIndex()
    {
        $response = $this->controller->index()->toArray();
        $this->assertEquals([$this->category->toArray()], $response);
    }

    /**
     * 
     * @group CategoryStub
     */
    public function testInvalidationDataInStore()
    {
        $this->expectException(ValidationException::class);
        /** @var Mockery */
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);
        $this->controller->store($request);
    }

    /**
     * 
     * @group CategoryStub
     */
    public function testStore()
    {
        /** @var Mockery */
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn([
                'name' => 'testing',
                'description' => 'description test'
            ]);
        $response = $this->controller->store($request);
        $this->assertEquals(CategoryStub::find($response->id)->toArray(), $response->toArray());
    }

    /**
     * 
     * @group CategoryStub
     */
    public function testIfFindOrFailFetchModel()
    {
        //API para acessar m�todos private e protected
        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $response = $reflectionMethod->invokeArgs($this->controller, [$this->category->id]);
        $this->assertInstanceOf(CategoryStub::class, $response);
    }

    /**
     * 
     * @group CategoryStub
     */
    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);
        //API para acessar m�todos private e protected
        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $response = $reflectionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(CategoryStub::class, $response);
    }

    /**
     * 
     * @group CategoryStub
     */
    public function testUpdate()
    {
        $category = $this->category;
        $data = [
            'name' => 'Name updated',
            'description' => 'updated'
        ];
        $request = new Request($data);
        $response = $this->controller->update($request, $category->id);
        $newData = [
            'name' => $response->name,
            'description' => $response->description
        ];
        $this->assertEquals($data, $newData);
    }

    /**
     * 
     * @group CategoryStub
     */
    public function testDestroy()
    {
        $id = $this->category->id;
        $response = $this->controller->destroy($id);
        $this->assertNotNull($response);
    }
}
