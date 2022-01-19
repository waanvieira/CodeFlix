<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
//Testes unitários
class CategoryTest extends TestCase
{
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();
    }
    
    /**     
     *
     * @group category
     * @return void
     */
    public function testsFillableAttributes()
    {
        $expected = ['name', 'description', 'is_active'];        
        $this->assertEquals($expected, $this->category->getFillable());
    }

    /**     
     *
     * @group category
     * @return void
     */
    public function testIfUserTraits()
    {
        $traits = [
            SoftDeletes::class, UuidTrait::class
        ];

        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    /**     
     *
     * @group category
     * @return void
     */
    public function testCasts()
    {
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $this->assertEquals($casts, $this->category->getCasts());
    }

    /**     
     *
     * @group category
     * @return void
     */
    public function testIncrementing()
    {
        $this->assertFalse($this->category->incrementing);
    }

    /**     
     *
     * @group category
     * @return void
     */
    public function testDatesAttributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        //Método que verifica os arrays sem se importar com a sequencia dos indices
        $this->assertEqualsCanonicalizing($dates, $this->category->getDates());
    }
}
