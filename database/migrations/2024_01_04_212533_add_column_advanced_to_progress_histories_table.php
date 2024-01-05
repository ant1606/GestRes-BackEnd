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
    Schema::table('progress_histories', function (Blueprint $table) {
      $table->after('done', function (Blueprint $table) {
        $table->unsignedInteger('advanced');
      });
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    if (Schema::hasColumn('progress_histories', 'advanced')) {
      Schema::table('progress_histories', function (Blueprint $table) {
        $table->dropColumn('advanced');
      });
    };
  }
};
