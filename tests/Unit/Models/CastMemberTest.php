<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use Tests\TestCase;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
//Testes unitários
class CastMemberTest extends TestCase
{
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = new CastMember();
    }
    
    /**     
     *
     * @group CastMember
     * @return void
     */
    public function testsFillableAttributes()
    {
        $expected = ['name', 'type'];        
        $this->assertEquals($expected, $this->castMember->getFillable());
    }

    /**     
     *
     * @group CastMember
     * @return void
     */
    public function testIfUserTraits()
    {
        $traits = [
            SoftDeletes::class, UuidTrait::class
        ];

        $categoryTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    /**     
     *
     * @group CastMember
     * @return void
     */
    public function testCasts()
    {
        $casts = ['id' => 'string', 'type' => 'numeric'];
        $this->assertEquals($casts, $this->castMember->getCasts());
    }

    /**     
     *
     * @group CastMember
     * @return void
     */
    public function testIncrementing()
    {
        $this->assertFalse($this->castMember->incrementing);
    }

    /**     
     *
     * @group CastMember
     * @return void
     */
    public function testDatesAttributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        //Método que verifica os arrays sem se importar com a sequencia dos indices
        $this->assertEqualsCanonicalizing($dates, $this->castMember->getDates());
    }
}
