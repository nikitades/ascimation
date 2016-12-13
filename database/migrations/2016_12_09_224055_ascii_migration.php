<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AsciiMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ascii', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->unique();
            $table->text('frames');
            $table->integer('framerate');
            $table->integer('image_id');
            $table->boolean('ready');
            $table->boolean('gif_ready')->default(0);
            $table->boolean('is_gif');
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
        Schema::drop('ascii');
    }
}
