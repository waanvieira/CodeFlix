<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class para testar o crud controller sendo um esboço de classe
 */
class CategoryStub extends Model
{
    protected $table = 'categories_stubs';
    protected $fillable = ['name', 'description'];

    public static function createTable()
    {
        Schema::create('categories_stubs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public static function destroyTable()
    {
        Schema::dropIfExists('categories_stubs');
    }
}
