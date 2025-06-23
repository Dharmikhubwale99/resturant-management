<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\
{
    Auth\Login
};


// Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.', 'middleware' => ['web', 'auth', 'role:superadmin']], function(){
    Route::get('/', function () {
        return view('welcome');
    });
// });
    Route::get('/login', Login::class)->name('login');
