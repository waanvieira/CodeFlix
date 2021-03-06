<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'], function () {
    $exceptionCreateEdit = [
        'except' => ['create', 'edit']
    ];

    Route::resource('categories', 'CategoryController', $exceptionCreateEdit);
    Route::resource('genres', 'GenreController', $exceptionCreateEdit);
    Route::resource('cast_members', 'CastMemberController', $exceptionCreateEdit);
    Route::resource('videos', 'VideoController', $exceptionCreateEdit);
});
