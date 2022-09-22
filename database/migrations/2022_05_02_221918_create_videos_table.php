<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('banner_file')->nullable();
            $table->string('trailer_file')->nullable();
            $table->string('thumb_file')->nullable();
            $table->string('video_file')->nullable();
            $table->string('rating', 3);
            $table->text('description');
            $table->smallInteger('year_launched');
            $table->smallInteger('duration');
            $table->boolean('opened')->default(false)->comment('Indica se o filme é lançamento ou não');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
