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
    Plan\Edit as PlanEdit,

    UserAccess

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

    ExpenseType\Index as ExpenseTypeIndex,
    ExpenseType\Create as ExpenseTypeCreate,
    ExpenseType\Edit as ExpenseTypeEdit,

    Expenses\Index as ExpensesIndex,
    Expenses\Create as ExpensesCreate,
    Expenses\Edit as ExpensesEdit,
    Expenses\Show as ExpensesShow,

    User\Index as UserIndex,
    User\Create as UserCreate,
    User\Edit as UserEdit,

    Discount\Index as DiscountIndex,
    Discount\Create as DiscountCreate,
    Discount\Edit as DiscountEdit
};

use \App\Livewire\Kitchen\{
    Dashboard as KitchenDashboard,
};

use App\Livewire\Waiter\{
    Dashboard as WaiterDashboard,

    Item,
    KotPrint,
    PendingKotOrders
    };
use App\Http\Controllers\PaymentController;
use App\Models\ExpenseType;

Route::get('superadmin/login', Login::class)->name('superadmin.login');
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('register', Register::class)->name('register');
Route::get('/', ResturantLogin::class)->name('login');

Route::get('/create-razorpay-order/{plan}', [PaymentController::class, 'createRazorpayOrder']);
Route::post('/razorpay/callback', [PaymentController::class, 'handleCallback'])->name('razorpay.callback');
Route::post('/activate-free-plan/{plan}', [PaymentController::class, 'activateFreePlan']);


 Route::prefix('superadmin')->as('superadmin.')->middleware(['web', 'auth', 'role:superadmin'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/settings', Settings::class)->name('settings');

    Route::prefix('admin')->as('admin.')->group(function () {

        Route::get('/', Index::class)->name('index');
        Route::get('/create', Create::class)->name('create');
        Route::get('/edit/{id}', Edit::class)->name('edit');

        Route::get('/', Index::class)->name('index');

        Route::get('/access/{id}', UserAccess::class)->name('access');
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

     Route::prefix('expense-types')->as('expense-types.')->group(function () {
        Route::get('/', ExpenseTypeIndex::class)->name('index');
        Route::get('/create', ExpenseTypeCreate::class)->name('create');
        Route::get('/edit/{id}', ExpenseTypeEdit::class)->name('edit');
    });

     Route::prefix('expenses')->as('expenses.')->group(function () {
        Route::get('/', ExpensesIndex::class)->name('index');
        Route::get('/create', ExpensesCreate::class)->name('create');
        Route::get('/edit/{id}', ExpensesEdit::class)->name('edit');
        Route::get('/show/{id}', ExpensesShow::class)->name('show');
    });

    Route::prefix('users')->as('users.')->group(function () {
        Route::get('/', UserIndex::class)->name('index');
        Route::get('/create', UserCreate::class)->name('create');
        Route::get('/edit/{id}', UserEdit::class)->name('edit');
    });

    Route::prefix('discount')->as('discount.')->group(function () {
        Route::get('/', DiscountIndex::class)->name('index');
        Route::get('/create', DiscountCreate::class)->name('create');
        Route::get('/edit/{id}', DiscountEdit::class)->name('edit');
    });

});


Route::prefix('waiter')->as('waiter.')->middleware(['web', 'auth', 'role:admin|waiter'])->group(function () {
    Route::get('/', WaiterDashboard::class)->name('dashboard');

    Route::get('/item/{table_id}', Item::class)->name('item');
    Route::get('/kot-print/{kot_id}', KotPrint::class)->name('kot.print');
    Route::get('/kots/pending', PendingKotOrders::class)->name('kots.pending');
});

Route::prefix('kitchen')->as('kitchen.')->middleware(['web', 'auth', 'role:admin|kitchen'])->group(function () {
    Route::get('/', KitchenDashboard::class)->name('dashboard');
});

