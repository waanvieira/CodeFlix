<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
//Testes unitários
class GenreTest extends TestCase
{
    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = new Genre();
    }
    
    /**
     *
     * @group genre
     * @return void
     */
    public function testsFillableAttributes()
    {
        $expected = ['name', 'is_active'];
        $this->assertEquals($expected, $this->genre->getFillable());
    }

    /**
     *
     * @group genre
     * @return void
     */
    public function testIfUserTraits()
    {
        $traits = [
            SoftDeletes::class, UuidTrait::class
        ];

        $genreTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($traits, $genreTraits);
    }

    /**
     *
     * @group genre
     * @return void
     */
    public function testCasts()
    {
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $this->assertEquals($casts, $this->genre->getCasts());
    }

    /**
     *
     * @group genre
     * @return void
     */
    public function testIncrementing()
    {
        $this->assertFalse($this->genre->incrementing);
    }

    /**
     *
     * @group genre
     * @return void
     */
    public function testDatesAttributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        //Método que verifica os arrays sem se importar com a sequencia dos indices
        $this->assertEqualsCanonicalizing($dates, $this->genre->getDates());
    }
}
