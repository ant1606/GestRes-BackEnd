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
    Schema::table('settings', function (Blueprint $table) {
      $table->after('value', function (Blueprint $table) {
        $table->string('value2', 50)->nullable();
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
    if (Schema::hasColumn('settings', 'value2')) {
      Schema::table('settings', function (Blueprint $table) {
        $table->dropColumn('value2');
      });
    }
  }
};
