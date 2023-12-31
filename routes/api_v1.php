<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\AuthorizationOAuthGooleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProgressHistoryController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecourseController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatusHistoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WebPageController;
use App\Http\Controllers\YoutubeSubscriptionController;
use App\Models\StatusHistory;
use App\Models\YoutubeSubscription;

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

  Route::post('register', [UserController::class, 'store'])->name('register');

  Route::post('login', [AuthenticationController::class, 'login'])->name('login');
  Route::post('remember', [AuthenticationController::class, 'check_remember'])->name('remember');

  Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.email');
  Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

  Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');

  // TODO Verificar si es mejor ponerle autentificacion a estas 2 rutas de settings
  Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
  Route::get('settings/{value}', [SettingsController::class, 'show'])->name('settings.show');




  //Rutas con autentificación  
  Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('logout', [AuthenticationController::class, 'logout'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notify'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resendLinkVerification'])->middleware(['throttle:6,1'])->name('verification.send');

    Route::get('recourses/{recourse}/status', [StatusHistoryController::class, 'index'])->name('status.index');
    Route::post('recourses/{recourse}/status', [StatusHistoryController::class, 'store'])->name('status.store');
    Route::get('recourses/{recourse}/progress', [ProgressHistoryController::class, 'index'])->name('progress.index');
    Route::post('recourses/{recourse}/progress', [ProgressHistoryController::class, 'store'])->name('progress.store');

    Route::delete('status/{statusHistory}', [StatusHistoryController::class, 'destroy'])->name('status.destroy');

    Route::delete('progress/{progressHistory}', [ProgressHistoryController::class, 'destroy'])->name('progress.destroy');

    Route::get('dashboard/getTop5Recourses', [DashboardController::class, 'getTop5Recourses'])->name('dashboard.getTop5Recourses');
    Route::get('dashboard/getAmountByState', [DashboardController::class, 'getAmountByState'])->name('dashboard.getAmountByState');

    Route::get('tag/getTagsForTagSelector', [TagController::class, 'getTagsForTagSelector'])->name('tag.getTagForTagSelector');

    Route::get('youtube-subscription', [YoutubeSubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('youtube-subscription/checkStatus', [YoutubeSubscriptionController::class, 'checkProcessStatus'])->name('subscription.checking');
    Route::post('youtube-subscription', [YoutubeSubscriptionController::class, 'store'])->name('subscription.store');
    Route::put('youtube-subscription/{subscription}', [YoutubeSubscriptionController::class, 'update'])->name('subscription.update');

    Route::post('webpage', [WebPageController::class, 'store'])->name('webpage.store');
    Route::get('webpage', [WebPageController::class, 'index'])->name('webpage.index');

    Route::resource('recourses', RecourseController::class)->except(['create', 'edit']);
    Route::resource('tag', TagController::class)->except(['create', 'edit']);
  });
});
