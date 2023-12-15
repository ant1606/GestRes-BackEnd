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
    Schema::create('youtube_subscriptions', function (Blueprint $table) {
      $table->string('id', 60)->primary()->index();
      $table->unsignedBigInteger('user_id');
      $table->string('channel_id', 30);
      $table->string('title', 150)->index();
      $table->date('published_at');
      $table->longText('description');
      $table->string('thumbnail_default', 250);
      $table->string('thumbnail_medium', 250);
      $table->string('thumbnail_high', 250);
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('youtube_subscriptions');
  }
};
