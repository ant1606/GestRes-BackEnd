<?php

use App\Http\Controllers\ProgressHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecourseController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatusHistoryController;
use App\Http\Controllers\TagController;
use App\Models\StatusHistory;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware(['cors'])->group(function () {
  Route::post('recourses', [RecourseController::class, 'store'])->name('recourse.store');
  Route::get('recourses/{recourse}', [RecourseController::class, 'show'])->name('recourse.show');
  Route::put('recourses/{recourse}', [RecourseController::class, 'update'])->name('recourse.update');

  Route::post('recourses/{recourse}/status', [StatusHistoryController::class, 'store'])->name('status.store');
  Route::post('recourses/{recourse}/progress', [ProgressHistoryController::class, 'store'])->name('progress.store');

  Route::post('settings', [SettingsController::class, 'show'])->name('settings.show');

  Route::post('tag', [TagController::class, 'store'])->name('tag.store');
  //Crear ruta para enviar los datos de los settings
});
