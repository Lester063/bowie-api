<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ItemController;
use App\Http\Controllers\Api\StudentController;
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


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum', 'is_admin')->group(function(){
    //student endpoint
    Route::get('students', [StudentController::class,'index']);
    Route::post('students', [StudentController::class,'store']);
    Route::get('students/{id}', [StudentController::class,'show']);
    Route::get('students/{id}/edit', [StudentController::class,'edit']);
    Route::put('students/{id}/edit', [StudentController::class,'update']);
    Route::delete('students/{id}/delete', [StudentController::class,'delete']);


    Route::get('items', [ItemController::class,'index']);
    Route::post('items', [ItemController::class,'store']);
    Route::get('items/{id}', [ItemController::class,'show']);
    Route::get('items/{id}/edit', [ItemController::class,'edit']);
    Route::put('items/{id}/edit', [ItemController::class,'update']);
    Route::delete('items/{id}/delete', [ItemController::class,'delete']);
});