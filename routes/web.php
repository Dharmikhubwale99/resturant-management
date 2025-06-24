<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogoutController;
use App\Livewire\
{
    Auth\Login
};

use \App\Livewire\Admin\{
    Admin\Index
  };




 Route::get('/login', Login::class)->name('login');
 Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');


Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.', 'middleware' => ['web', 'auth', 'role:superadmin']], function(){
    Route::get('/', function () {
        return view('welcome');
    });
});
Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.'], function(){
    Route::get('/admin', Index::class)->name('admin.index');
});


// Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.', 'middleware' => ['web', 'auth', 'role:superadmin']], function(){
    Route::get('/', function () {
        return view('welcome');
    })->name('dashboard');
//});

