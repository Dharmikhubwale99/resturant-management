<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogoutController;
use App\Livewire\
{
    Auth\Login
};

use \App\Livewire\Admin\{
    Dashboard,
    Settings,
    Admin\Index,

    Admin\Create,
    Admin\Edit,

    Plan\Index as PlanIndex,
    Plan\Create as PlanCreate,
    Plan\Edit as PlanEdit

  };
use \App\Livewire\Resturant\{
    Dashboard as ResturantDashboard,

    Auth\Register ,
};

Route::get('superadmin/login', Login::class)->name('superadmin.login');
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('resturant/register', Register::class)->name('resturant.register');

 Route::prefix('superadmin')->as('superadmin.')->middleware(['web', 'auth', 'role:superadmin'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/settings', Settings::class)->name('settings');

    Route::prefix('admin')->as('admin.')->group(function () {

        Route::get('/', Index::class)->name('index');
        Route::get('/create', Create::class)->name('create');
        Route::get('/edit/{id}', Edit::class)->name('edit');

        Route::get('/', Index::class)->name('index');
    });

    Route::prefix('plans')->as('plans.')->group(function () {
        Route::get('/', PlanIndex::class)->name('index');
        Route::get('/create', PlanCreate::class)->name('create');
        Route::get('/edit/{id}', PlanEdit::class)->name('edit');
    });
 });

Route::prefix('resturant')->as('resturant.')->middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/', ResturantDashboard::class)->name('dashboard');

});
