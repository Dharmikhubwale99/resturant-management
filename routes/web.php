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

    Auth\Register,
    Auth\Login as ResturantLogin,
    Auth\RestoRegister as RestoRegister,
    PlanPurchase as ResturantPlanPurchase,

    Category\Index as CategoryIndex,
    Category\Create as CategoryCreate,
    Category\Edit as CategoryEdit,

    Item\Index as ItemIndex,
    Item\Create as ItemCreate,
    Item\Edit as ItemEdit,
    Item\Show as ItemShow,

    Area\Index as AreaIndex,
    Area\Create as AreaCreate,
    Area\Edit as AreaEdit,

    Table\Index as TableIndex,
    Table\Create as TableCreate,
    Table\Edit as TableEdit,
    Table\Show as TableShow,

    User\Index as UserIndex,
    User\Create as UserCreate,
    User\Edit as UserEdit,
};
use App\Http\Controllers\PaymentController;

Route::get('superadmin/login', Login::class)->name('superadmin.login');
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('register', Register::class)->name('register');
Route::get('/', ResturantLogin::class)->name('login');

Route::get('/create-razorpay-order/{plan}', [PaymentController::class, 'createRazorpayOrder']);
Route::post('/razorpay/callback', [PaymentController::class, 'handleCallback'])->name('razorpay.callback');

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

Route::get('/plan-purchase', ResturantPlanPurchase::class)->name('plan.purchase');

Route::prefix('restaurant')->as('restaurant.')->middleware(['web', 'auth', 'role:admin', 'check.restaurant.plan'])->group(function () {
    Route::get('/resto-register', RestoRegister::class)->name('resto-register');
    Route::get('/', ResturantDashboard::class)->name('dashboard');

    Route::prefix('categories')->as('categories.')->group(function () {
        Route::get('/', CategoryIndex::class)->name('index');
        Route::get('/create', CategoryCreate::class)->name('create');
        Route::get('/edit/{id}', CategoryEdit::class)->name('edit');
    });

    Route::prefix('items')->as('items.')->group(function () {
        Route::get('/', ItemIndex::class)->name('index');
        Route::get('/create', ItemCreate::class)->name('create');
        Route::get('/edit/{id}', ItemEdit::class)->name('edit');
        Route::get('/show/{id}', ItemShow::class)->name('show');
    });

     Route::prefix('areas')->as('areas.')->group(function () {
        Route::get('/', AreaIndex::class)->name('index');
        Route::get('/create', AreaCreate::class)->name('create');
        Route::get('/edit/{id}', AreaEdit::class)->name('edit');
    });

    Route::prefix('tables')->as('tables.')->group(function () {
        Route::get('/', TableIndex::class)->name('index');
        Route::get('/create', TableCreate::class)->name('create');
        Route::get('/edit/{id}', TableEdit::class)->name('edit');
        Route::get('/show/{id}', TableShow::class)->name('show');
    });

    Route::prefix('users')->as('users.')->group(function () {
        Route::get('/', UserIndex::class)->name('index');
        Route::get('/create', UserCreate::class)->name('create');
        Route::get('/edit/{id}', UserEdit::class)->name('edit');
    });

});

// Route::get('/table/{qr_token}/menu', function($qr_token) {
//     return "Menu for table token: " . $qr_token;
// })->name('table.menu');
