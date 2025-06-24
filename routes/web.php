<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogoutController;
use App\Livewire\
{
    Auth\Login
};

use \App\Livewire\Admin\{
    Dashboard,
    Admin\Index,
    Admin\Create,
  };




 Route::get('/login', Login::class)->name('login');
 Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');


 Route::prefix('superadmin')->as('superadmin.')->middleware(['web', 'auth', 'role:superadmin'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::prefix('admin')->as('admin.')->group(function () {
        Route::get('/', Index::class)->name('index');
        Route::get('/create', Create::class)->name('create');
    });
});




// Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.', 'middleware' => ['web', 'auth', 'role:superadmin']], function(){
    Route::get('/', function () {
        return view('welcome');
    })->name('dashboard');
//});

