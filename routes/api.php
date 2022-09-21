<?php

use App\Http\Controllers\ProgressHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecourseController;
use App\Http\Controllers\StatusHistoryController;
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
Route::post('recourse', [RecourseController::class, 'store'])->name('recourse.store');
Route::get('recourse/{recourse}', [RecourseController::class, 'show'])->name('recourse.show');

Route::post('recourse/{recourse}/status', [StatusHistoryController::class, 'store'])->name('status.store');
Route::post('recourse/{recourse}/progress', [ProgressHistoryController::class, 'store'])->name('progress.store');
