<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('summaries', [ApiController::class, 'getSummaries'])->name('getSummaries');
Route::post('summaries', [ApiController::class, 'postSummary'])->name('postSummary');
Route::get('testAuthentication', [ApiController::class, 'testAuthentication'])->name('testAuthentication');
