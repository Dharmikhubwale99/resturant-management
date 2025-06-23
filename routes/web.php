<?php

use Illuminate\Support\Facades\Route;



Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.', 'middleware' => ['web', 'auth', 'role:superadmin']], function(){
    Route::get('/', function () {
        return view('welcome');
    });
});
