<?php

use App\Http\Controllers\AuthenticationController;
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

 Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
     return $request->user();
 });



//
//Route::post('/tokens/create', function (Request $request) {
//  $token = $request->user()->createToken($request->token_name);
//
//  return ['token' => $token->plainTextToken];
//});

Route::middleware(['cors'])->group(function () {

  // Cuando se usa la ruta get definida como abajo y en postman se coloca queryString para filtrar datos, el controlador no los recibe
  // En cambio cuando se usa Route::resource y se hace la misma operacion, el controlador si recibe los queryString, verificar porque
  //  Route::get('recourses', [RecourseController::class, 'index'])->name('recourse.index');
  //  Route::post('recourses', [RecourseController::class, 'store'])->name('recourse.store');
  //  Route::get('recourses/{recourse}', [RecourseController::class, 'show'])->name('recourse.show');
  //  Route::put('recourses/{recourse}', [RecourseController::class, 'update'])->name('recourse.update');
  //  Route::delete('recourses/{recourse}', [RecourseController::class, 'destroy'])->name('recourse.destroy');

  Route::post('login', [AuthenticationController::class,'login'])->name('login');

  Route::get('recourses/{recourse}/status', [StatusHistoryController::class, 'index'])->name('status.index');
  Route::post('recourses/{recourse}/status', [StatusHistoryController::class, 'store'])->name('status.store');
  Route::get('recourses/{recourse}/progress', [ProgressHistoryController::class, 'index'])->name('progress.index');
  Route::post('recourses/{recourse}/progress', [ProgressHistoryController::class, 'store'])->name('progress.store');

  // StatusHistory Route
  Route::delete('status/{statusHistory}', [StatusHistoryController::class, 'destroy'])->name('status.destroy');
  //ProgressHistory Route
  Route::delete('progress/{progressHistory}', [ProgressHistoryController::class, 'destroy'])->name('progress.destroy');

  Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
  Route::get('settings/{value}', [SettingsController::class, 'show'])->name('settings.show');

  Route::resource('tag', TagController::class)->except(['create', 'edit']);
  Route::resource('recourses', RecourseController::class)->except(['create', 'edit']);
});
