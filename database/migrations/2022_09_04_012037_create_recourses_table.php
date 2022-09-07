<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recourses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('source', 255);
            $table->string('author', 75)->nullable()->default(null);
            $table->string('editorial', 75)->nullable()->default(null);
            $table->integer('type_id');
            $table->integer('total_pages')->nullable()->default(null);
            $table->integer('total_chapters')->nullable()->default(null);
            $table->integer('total_videos')->nullable()->default(null);
            $table->time('total_hours')->nullable()->default(null);

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
        Schema::dropIfExists('recourses');
    }
};
