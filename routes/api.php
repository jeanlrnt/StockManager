<?php

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

Route::get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', 'App\Http\Controllers\Api\CustomerController@index')->name('index');
    Route::get('/{customer}', 'App\Http\Controllers\Api\CustomerController@show')->name('show');
    Route::post('/', 'App\Http\Controllers\Api\CustomerController@store')->name('store');
    Route::put('/{customer}', 'App\Http\Controllers\Api\CustomerController@update')->name('update');
});

