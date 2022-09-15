<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testGetAll()
    {
        $response = $this->get(route('cast_members.index'));        
        $response->assertStatus(200)->assertJson([$this->castMember->toArray()]);
    }

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(200)->assertJson($this->castMember->toArray());
    }

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'type' => ''
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testInvalidationStore()
    {
        $response = $this->json('POST', route('cast_members.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('cast_members.store'), [
            'name' => str_repeat('a', 256),
            'type' => 1
        ]);

        $this->assertInvalidationMax($response);
    }

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testInvalidationUpdate()
    {
        $response = $this->json('PUT', route('cast_members.update', ['cast_member' => $this->castMember->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'PUT',
            route('cast_members.update', ['cast_member' => $this->castMember->id]),
            [
                'name' => str_repeat('a', 256),
                'type' => 1
            ]
        );

        $this->assertInvalidationMax($response);
    }

    public function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['type'])
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

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testStore()
    {
        $data = [
            'name' => 'Name test',
            'type' => CastMember::TYPE_DIRECTOR
        ];

        $this->assertStore($data, $data);

        $data = [
            'name' => 'Name test 2',
            'type' => CastMember::TYPE_ACTOR
        ];

        $this->assertStore($data, $data);
    }

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testUpdate()
    {
        $castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);

        $response = $this->json('PUT', route('cast_members.update', ['cast_member' => $castMember->id]), [
            'name' => 'Name updated',
            'type' => CastMember::TYPE_ACTOR
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Name updated',
                'type' => 2,
            ]);
    }

    /**
     * 
     * @group CastMember
     * @return void
     */
    public function testDestroy()
    {
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(204);
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    public function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }

    public function model()
    {
        return CastMember::class;
    }
}
