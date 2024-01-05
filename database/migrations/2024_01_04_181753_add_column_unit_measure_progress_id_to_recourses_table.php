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
    Schema::table('recourses', function (Blueprint $table) {
      $table->after('type_id', function (Blueprint $table) {
        $table->integer('unit_measure_progress_id');
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
    if (Schema::hasColumn('recourses', 'unit_measure_progress_id')) {
      Schema::table('recourses', function (Blueprint $table) {
        $table->dropColumn('unit_measure_progress_id');
      });
    };
  }
};
