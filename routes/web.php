<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::group(['middleware' => 'auth'], function () {

    Route::get('/', function () {
        return view('home');
    })->name('home');

    //Route::get('/anodize/movetag/{id}', 'App\Http\Controllers\AnodizeController@show');
    Route::get('/training', 'App\Http\Controllers\TrainingController@index');
    Route::get('/report/labor/daily/{view?}', 'App\Http\Controllers\LaborReportController@index');

    Route::get('{any}', function ($any) {
        return view('home');
    })->where('any', '.*');
});
