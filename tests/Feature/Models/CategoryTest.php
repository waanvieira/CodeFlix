<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
//Testes de integração
class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function createCategory($active = true)
    {
        $category = Category::create([
            'name' => 'New Category',
            'description' => 'Description category',
            'is_active' => $active
        ]);

        return $category;
    }

    /**
     * @group Category
     * @return void
     */
    public function testCreate()
    {
        $response = $this->createCategory();
        $this->assertEquals($response->name, "New Category");
    }

    /**
     * @group Category
     * @return void
     */
    public function testCreateIsActiveFalse()
    {
        $response = $this->createCategory(false);
        $this->assertFalse($response->is_active);
    }

    /**
     * @group Category
     * @return void
     */
    public function testUpdateIsActiveTrue()
    {
        $response = $this->createCategory(false);
        $category = tap(Category::find($response->id), function ($response) {
            return $response->update(['is_active' => true]);
        });

        $this->assertTrue($category->is_active);
    }

    /**
     * @group Category
     * @return void
     */
    public function testUpdateIsActiveFalse()
    {
        $response = $this->createCategory();
        $category = tap(Category::find($response->id), function ($response) {
            return $response->update(['is_active' => false]);
        });

        $this->assertFalse($category->is_active);
    }

    /**
     * @group Category
     * @return void
     */
    public function testUpdateName()
    {
        $response = $this->createCategory();
        $category = tap(Category::find($response->id), function ($response) {
            return $response->update(['name' => 'Name updated']);
        });

        $this->assertEquals($category->name, 'Name updated');
    }

    /**
     * @group Category
     * @return void
     */
    public function testUpdateDecription()
    {
        $response = $this->createCategory();
        $category = tap(Category::find($response->id), function ($response) {
            return $response->update(['description' => 'Description updated']);
        });

        $this->assertEquals($category->description, 'Description updated');
    }

    /**
     * @group Category
     * @return void
     */
    public function testUuid()
    {
        $response = $this->createCategory();
        $uuid = $this->isUUID($response->id);
        $this->assertTrue($uuid);
    }

    /**
     * Verify uuid
     *
     * @param string $uuid
     * @return boolean
     */
    public function isUUID($uuid)
    {
        $regex = '/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}/';
        if (preg_match($regex, $uuid)) {
            return true;
        }

        return false;
    }

    /**
     * @group Category
     * @return void
     */
    public function testeCategoryDelete()
    {
        $response = $this->createCategory();
        $category = tap(Category::find($response->id), function ($response) {
            return $response->delete();
        });
        
        $this->assertNotNull($category->deleted_at);
    }
}
