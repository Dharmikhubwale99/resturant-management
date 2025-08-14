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
    EditProfile as AdminEditProfile,

    Admin\Create,
    Admin\Edit,
    Admin\Show,

    Plan\Index as PlanIndex,
    Plan\Create as PlanCreate,
    Plan\Edit as PlanEdit,
    Plan\Report as PlanReport,

    UserAccess
  };
use \App\Livewire\Resturant\{
    Dashboard as ResturantDashboard,
    EditProfile,

    Auth\Register,
    Auth\Login as ResturantLogin,
    Auth\RestoRegister as RestoRegister,
    Auth\ResetPassword as ResetPassword,
    Auth\ForgotPassword as ForgotPassword,
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
    Discount\Edit as DiscountEdit,

    Party\Index as PartyIndex,
    Party\Create as PartyCreate,
    Party\Edit as PartyEdit,

    Kitchen\Dashboard as AdminKitchenDashboard,

    Transaction\MoneyIn\MoneyIn as MoneyMaintain,
    Transaction\MoneyIn\Create as MoneyInCreate,
    Transaction\MoneyOut\MoneyOutForm as MoneyMainOut,
    Transaction\MoneyOut\MoneyOutIndex as MoneyOutIndex,

    Report\Index as ReportIndex,
    Report\SalesReport,
    Report\PaymentReport,
    Report\StaffWise,
    Report\MoneyIn,
    Report\MoneyOut,
    Report\ExpenseReport,
    Report\ItemSaleReport,
    Report\ItemSalePaymentReport
};

use \App\Livewire\Kitchen\{
    Dashboard as KitchenDashboard,
};

use App\Livewire\Waiter\{
    Dashboard as WaiterDashboard,

    Item,
    KotPrint,
    PendingKotOrders,
    BillPrint,
    PickupCreate,
    PickupItem,

    AdvancBook\Index as AdvancBookIndex,
    AdvancBook\Create as AdvancBookCreate
};
use App\Http\Controllers\PaymentController;
use UniSharp\LaravelFilemanager\Lfm;


Route::group([
    'prefix' => 'laravel-filemanager',
    'middleware' => ['web','auth','enforce.restaurant.storage'],
], function () {
    Lfm::routes();
});

Route::get('superadmin/login', Login::class)->name('superadmin.login');
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('register', Register::class)->name('register');
Route::get('/', ResturantLogin::class)->name('login');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request');

Route::get('/create-razorpay-order/{plan}', [PaymentController::class, 'createRazorpayOrder']);
Route::post('/razorpay/callback', [PaymentController::class, 'handleCallback'])->name('razorpay.callback');
Route::post('/activate-free-plan/{plan}', [PaymentController::class, 'activateFreePlan']);


 Route::prefix('superadmin')->as('superadmin.')->middleware(['web', 'auth', 'role:superadmin'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/settings', Settings::class)->name('settings');
    Route::get('/edit-profile', AdminEditProfile::class)->name('edit-profile');

    Route::prefix('admin')->as('admin.')->group(function () {

        Route::get('/', Index::class)->name('index');
        Route::get('/create', Create::class)->name('create');
        Route::get('/edit/{id}', Edit::class)->name('edit');
        Route::get('/show/{id}', Show::class)->name('show');

        Route::get('/', Index::class)->name('index');

        Route::get('/access/{id}', UserAccess::class)->name('access');
    });

    Route::prefix('plans')->as('plans.')->group(function () {
        Route::get('/', PlanIndex::class)->name('index');
        Route::get('/create', PlanCreate::class)->name('create');
        Route::get('/edit/{id}', PlanEdit::class)->name('edit');
        Route::get('/report', PlanReport::class)->name('report');

    });
 });

Route::get('/plan-purchase', ResturantPlanPurchase::class)->name('plan.purchase');

Route::prefix('restaurant')->as('restaurant.')->middleware(['web', 'auth', 'role:admin|waiter|kitchen', 'check.restaurant.plan'])->group(function () {
    Route::get('/resto-register', RestoRegister::class)->name('resto-register');
    Route::get('/', ResturantDashboard::class)->name('dashboard');
    Route::get('/edit-profile', EditProfile::class)->name('edit-profile');

    Route::prefix('categories')->as('categories.')->group(function () {
        Route::get('/', CategoryIndex::class)->name('index')->middleware('can:category-index');
        Route::get('/create', CategoryCreate::class)->name('create')->middleware('can:category-create');
        Route::get('/edit/{id}', CategoryEdit::class)->name('edit')->middleware('can:category-edit');
    });

    Route::prefix('items')->as('items.')->group(function () {
        Route::get('/', ItemIndex::class)->name('index')->middleware('can:item-index');
        Route::get('/create', ItemCreate::class)->name('create')->middleware('can:item-create');
        Route::get('/edit/{id}', ItemEdit::class)->name('edit')->middleware('can:item-edit');
        Route::get('/show/{id}', ItemShow::class)->name('show')->middleware('can:item-show');
    });

     Route::prefix('areas')->as('areas.')->group(function () {
        Route::get('/', AreaIndex::class)->name('index')->middleware('can:area-index');
        Route::get('/create', AreaCreate::class)->name('create')->middleware('can:area-create');
        Route::get('/edit/{id}', AreaEdit::class)->name('edit')->middleware('can:area-edit');
    });

    Route::prefix('tables')->as('tables.')->group(function () {
        Route::get('/', TableIndex::class)->name('index')->middleware('can:table-index');
        Route::get('/create', TableCreate::class)->name('create')->middleware('can:table-create');
        Route::get('/edit/{id}', TableEdit::class)->name('edit')->middleware('can:table-edit');
        Route::get('/show/{id}', TableShow::class)->name('show')->middleware('can:table-show');
    });

     Route::prefix('expense-types')->as('expense-types.')->group(function () {
        Route::get('/', ExpenseTypeIndex::class)->name('index')->middleware('can:expensetype-index');
        Route::get('/create', ExpenseTypeCreate::class)->name('create')->middleware('can:expensetype-create');
        Route::get('/edit/{id}', ExpenseTypeEdit::class)->name('edit')->middleware('can:expensetype-edit');
    });

     Route::prefix('expenses')->as('expenses.')->group(function () {
        Route::get('/', ExpensesIndex::class)->name('index')->middleware('can:expenses-index');
        Route::get('/create', ExpensesCreate::class)->name('create')->middleware('can:expenses-create');
        Route::get('/edit/{id}', ExpensesEdit::class)->name('edit')->middleware('can:expenses-edit');
        Route::get('/show/{id}', ExpensesShow::class)->name('show')->middleware('can:expenses-show');
    });

    Route::prefix('users')->as('users.')->group(function () {
        Route::get('/', UserIndex::class)->name('index')->middleware('can:user-index');
        Route::get('/create', UserCreate::class)->name('create')->middleware('can:user-create');
        Route::get('/edit/{id}', UserEdit::class)->name('edit')->middleware('can:user-edit');
    });

    Route::prefix('discount')->as('discount.')->group(function () {
        Route::get('/', DiscountIndex::class)->name('index')->middleware('can:discount-index');
        Route::get('/create', DiscountCreate::class)->name('create')->middleware('can:discount-create');
        Route::get('/edit/{id}', DiscountEdit::class)->name('edit')->middleware('can:discount-edit');
    });

    Route::prefix('kitchen')->as('kitchen.')->group(function () {
        Route::get('/index', AdminKitchenDashboard::class)->name('index')->middleware('can:kitchen-dashboard');
    });


    Route::get('/waiter-order', WaiterDashboard::class)->name('waiter.dashboard')->middleware('can:order');

    Route::get('/item/{table_id}', Item::class)->name('item');
    Route::get('/kot-print/{kot_id}', KotPrint::class)->name('kot.print');
    Route::get('/kots/pending', PendingKotOrders::class)->name('kots.pending');
    Route::get('/bill-print/{order}', BillPrint::class)->name('bill.print');
    Route::get('/pickup', PickupCreate::class)->name('pickup.create');
    Route::get('/pickup/item/{id}', PickupItem::class)->name('pickup.item');

    Route::get('/report', ReportIndex::class)->name('report')->middleware('can:report-index');
    Route::get('/sales-report', SalesReport::class)->name('sales-report');
    Route::get('/payment-report', PaymentReport::class)->name('payment-report');
    Route::get('/staff-wise-report', StaffWise::class)->name('staffwise-report');
    Route::get('/money-in-report', MoneyIn::class)->name('moneyin-report');
    Route::get('/money-out-report', MoneyOut::class)->name('moneyout-report');
    Route::get('/expense-report', ExpenseReport::class)->name('expense-report');
    Route::get('/item-sale-report', ItemSaleReport::class)->name('item-sale-report');
    Route::get('/item-sale-payment-report', ItemSalePaymentReport::class)->name('item-sale-payment-report');

    Route::get('/money-maintain', MoneyMaintain::class)->name('money-maintain')->middleware('can:moneyin-index');
    Route::get('/money-in-create', MoneyInCreate::class)->name('money-in.create')->middleware('can:moneyin-create');

    Route::get('/money-out-index', MoneyOutIndex::class)->name('money-out')->middleware('can:moneyout-index');
    Route::get('/money-out-create', MoneyMainOut::class)->name('money-out.create')->middleware('can:moneyout-create');

    Route::get('/party', PartyIndex::class)->name('party')->middleware('can:party-index');
    Route::get('/party/create', PartyCreate::class)->name('party.create')->middleware('can:party-create');
    Route::get('/party/edit/{id}', PartyEdit::class)->name('party.edit');

    Route::get('/advance-booking', AdvancBookIndex::class)->name('advance-booking');
    Route::get('/advance-booking/create', AdvancBookCreate::class)->name('advance-booking.create');
});

// Route::prefix('waiter')->as('waiter.')->middleware(['web', 'auth', 'role:admin|waiter'])->group(function () {
//     Route::get('/', WaiterDashboard::class)->name('dashboard');

//     Route::get('/item/{table_id}', Item::class)->name('item');
//     Route::get('/kot-print/{kot_id}', KotPrint::class)->name('kot.print');
//     Route::get('/kots/pending', PendingKotOrders::class)->name('kots.pending');
//     Route::get('/bill-print/{order}', BillPrint::class)->name('bill.print');
//     Route::get('/pickup', PickupCreate::class)->name('pickup.create');
//     Route::get('/pickup/item/{id}', PickupItem::class)->name('pickup.item');
// });

// Route::prefix('kitchen')->as('kitchen.')->middleware(['web', 'auth', 'role:kitchen'])->group(function () {
//     Route::get('/', KitchenDashboard::class)->name('dashboard');
// });

