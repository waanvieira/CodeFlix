<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function createGenre($active = true)
    {
        $genre = Genre::create([
            'name' => 'New genre',
            'is_active' => $active
        ]);

        return $genre;
    }

    /**
     * @group Genre
     * @return void
     */
    public function testCreate()
    {
        $response = $this->createGenre();
        $this->assertEquals($response->name, 'New genre');
    }

    /**
     * @group Genre
     * @return void
     */
    public function testCreateIsActiveFalse()
    {
        $response = $this->createGenre(false);
        $this->assertFalse($response->is_active);
    }

    /**
     * @group Genre
     * @return void
     */
    public function testUpdateIsActiveTrue()
    {
        $response = $this->createGenre(false);
        $genre = tap(Genre::find($response->id), function ($response) {
            return $response->update(['is_active' => true]);
        });

        $this->assertTrue($genre->is_active);
    }

    /**
     * @group Genre
     * @return void
     */
    public function testUpdateIsActiveFalse()
    {
        $response = $this->createGenre();
        $genre = tap(Genre::find($response->id), function ($response) {
            return $response->update(['is_active' => false]);
        });

        $this->assertFalse($genre->is_active);
    }

    /**
     * @group Genre
     * @return void
     */
    public function testUpdateName()
    {
        $response = $this->createGenre();
        $genre = tap(Genre::find($response->id), function ($response) {
            return $response->update(['name' => 'Name updated']);
        });

        $this->assertEquals($genre->name, 'Name updated');
    }

    /**
     * @group Genre
     * @return void
     */
    public function testUuid()
    {
        $response = $this->createGenre();
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
     * @group Genre
     * @return void
     */
    public function testeGenreDelete()
    {
        $response = $this->createGenre();
        $genre = tap(Genre::find($response->id), function ($response) {
            return $response->delete();
        });
        
        $this->assertNotNull($genre->deleted_at);
    }
}
