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
Route::group(['middleware' => 'api'], function () {
    Route::get('/anodize/menu', 'App\Http\Controllers\AnodizeController@index');
    Route::post('/anodize/movetag', 'App\Http\Controllers\AnodizeController@show')->name('postMoveTag');
    Route::post('/anodize/movetag/create/bars', 'App\Http\Controllers\AnodizeController@createBars')->name('createAnoBars');
    Route::get('/anodize/print/{code}', 'App\Http\Controllers\AnodizeController@printLabel');
    Route::get('/anodize/movetag/load/{tagno}/{undelete?}', 'App\Http\Controllers\AnodizeController@loadTag');
    Route::get('/anodize/movetag/delete/{id}', 'App\Http\Controllers\AnodizeController@destroy');
    Route::post('/anodize/movetag/load/step', 'App\Http\Controllers\AnodizeController@loadStep');
    Route::post('/anodize/movetag/save/tank/info', 'App\Http\Controllers\AnodizeController@saveTankInfo');

});
