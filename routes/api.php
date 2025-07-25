<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\RequestCommunicationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CommentReviewController;
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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
//display items
Route::get('items', [ItemController::class,'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('uploadprofile', [AuthController::class,'uploadProfile']);

    //request item
    Route::post('requests', [RequestController::class,'requestItem']);

    //get user request/s, pass the request id
    Route::get('userrequest', [RequestController::class,'getUserAllRequest']);
    Route::get('userrequest/{id}', [RequestController::class,'getUserSpecificRequest']);

    //request comm -As as User, I would like to follow up with my request status or any queries.
    Route::post('requestcommunication', [RequestCommunicationController::class,'store']);
    Route::get('requestcommunication/{id}', [RequestCommunicationController::class,'show']);
    Route::put('readmessage/{id}', [RequestCommunicationController::class,'readUnreadMessage']);

    //return item
    Route::post('return', [ReturnController::class,'returnItem']);
    //return - user view
    Route::get('userreturns', [ReturnController::class,'indexUser']);
    Route::get('return/{id}', [ReturnController::class,'viewReturn']);

    //notifications
    Route::get('notifications', [NotificationController::class,'userNotificationIndex']);
    Route::put('notifications', [NotificationController::class,'readUnreadUserNotification']);
    Route::get('notifications/{id}', [NotificationController::class,'generateNotificationMessage']);

    //commentreview
    Route::post('comment', [CommentReviewController::class, 'store']);
    Route::get('itemreviews/{id}', [CommentReviewController::class, 'showItemReviews']);
    Route::get('items/{id}', [ItemController::class,'show']);

});

Route::middleware('auth:sanctum', 'isAdmin')->group(function(){

    Route::post('items', [ItemController::class,'store']);
    Route::get('items/{id}/edit', [ItemController::class,'edit']);
    Route::get('items/{id}/itemrequest', [ItemController::class,'getItemRequestFromUser']);
    Route::post('items/{id}/edit', [ItemController::class,'update']);
    Route::delete('items/{id}/delete', [ItemController::class,'delete']);

    Route::get('requests', [RequestController::class,'getAllRequests']);

    //request -admin
    Route::put('actionrequest/{id}/edit', [RequestController::class,'actionRequest']);


    //return - approve 
    Route::put('return/{id}/approve', [ReturnController::class,'approveReturn']);
    //return - admin view
    Route::get('returns', [ReturnController::class,'indexAdmin']);

});


