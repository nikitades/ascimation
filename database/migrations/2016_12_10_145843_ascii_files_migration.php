<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AsciiFilesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ascii_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pos');
            $table->integer('parent_id')->unsigned();
            $table->foreign('parent_id')->references('id')->on('ascii')->onDelete('cascade');
            $table->integer('file_id')->unsigned();
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
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
        Schema::drop('ascii_files');
    }
}
