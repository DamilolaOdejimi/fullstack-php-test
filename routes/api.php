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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['api'], 'prefix' => 'v1', 'namespace' => 'Api'], function () {
    Route::get('/hmos', 'HmoController@index');
    Route::get('/providers', 'ProviderController@index');
    // Route::get('/orders/hmo/{hmoId}', 'OrderController@getHmoOrders');
    Route::post('/submit-order', 'OrderController@submitOrder');
    Route::post('/process-orders', 'OrderController@processOrders'); // pass batch label
});
