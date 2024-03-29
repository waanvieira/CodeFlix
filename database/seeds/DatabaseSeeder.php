<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CategoriesTableSeeder::class);
        $this->call(GenreTableSeeder::class);
        $this->call(CastMemberTableSeeder::class);
        $this->call(VideoTableSeeder::class);
    }
}
