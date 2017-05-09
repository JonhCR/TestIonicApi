<?php

use Illuminate\Http\Request;


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

//Rutas protegidas por autenticacion
Route::group(['prefix' => 'user' ,  'middleware' => ['jwt.auth'] ], function() {
    Route::get('/dashboard', 'ApiController@userDashboard');
});

 Route::post('/register','ApiController@register');
 Route::post('/login', 'ApiController@login');
 Route::get('/logout', 'ApiController@logout');


