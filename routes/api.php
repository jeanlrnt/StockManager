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
    Route::delete('/{customer}', 'App\Http\Controllers\Api\CustomerController@destroy')->name('destroy');
    Route::delete('/{customer}/force-delete', 'App\Http\Controllers\Api\CustomerController@forceDelete')->name('force-delete');
    Route::post('/{customer}/restore', 'App\Http\Controllers\Api\CustomerController@restore')->name('restore');
});

Route::prefix('providers')->name('providers.')->group(function (){
    Route::get('/', 'App\Http\Controllers\Api\ProviderController@index')->name('index');
    Route::get('/{provider}', 'App\Http\Controllers\Api\ProviderController@show')->name('show');
    Route::post('/', 'App\Http\Controllers\Api\ProviderController@store')->name('store');
    Route::put('/{provider}', 'App\Http\Controllers\Api\ProviderController@update')->name('update');
    Route::delete('/{provider}', 'App\Http\Controllers\Api\ProviderController@destroy')->name('destroy');
    Route::delete('/{provider}/force-delete', 'App\Http\Controllers\Api\ProviderController@forceDelete')->name('force-delete');
    Route::post('/{provider}/restore', 'App\Http\Controllers\Api\ProviderController@restore')->name('restore');
});

