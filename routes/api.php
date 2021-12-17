<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyController;
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

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'authenticate']);
Route::get('/properties', [PropertyController::class, 'index']);

Route::group(
    ['middleware' => ['jwt.verify']],
    function () {
        Route::get('user', [UserController::class, 'getAuthenticatedUser']);
        Route::put('user', [UserController::class, 'update']);
        Route::delete('user', [UserController::class, 'delete']);

        //Properties 
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::get('/properties/{property}', [PropertyController::class, 'show']);
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::put('/properties/{property}', [PropertyController::class, 'update']);
        Route::delete('/properties/{property}', [PropertyController::class, 'delete']);
    }
);
