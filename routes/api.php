<?php

use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\UserController;
use Doctrine\DBAL\Driver\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('login', [LoginController::class, 'userLogin']);
Route::post('user', [UserController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::group(['middleware' => 'admin'], function(){
        Route::get('user', [UserController::class, 'index']);
    });

    Route::group(['middleware' => 'user'], function(){
        Route::get('user/{id}', [UserController::class, 'show']);
        Route::PATCH('user/{id}', [UserController::class, 'update']);
        Route::DELETE('user/{id}', [UserController::class, 'destroy']);
        Route::PATCH('hobby', [UserController::class, 'hobbyUpdate']);
    });
});



