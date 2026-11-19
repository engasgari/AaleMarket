<?php

use App\Http\Controllers\PaymentCallbackController;
use App\Modules\Admin\Livewire\Admin\ActivityLogIndex;
use App\Modules\Admin\Livewire\Brands\BrandForm;
use App\Modules\Admin\Livewire\Brands\BrandIndex;
use App\Modules\Admin\Livewire\Catalog\ProductForm;
use App\Modules\Admin\Livewire\Catalog\ProductIndex as AdminProductIndex;
use App\Modules\Admin\Livewire\Categories\CategoryForm;
use App\Modules\Admin\Livewire\Categories\CategoryIndex as AdminCategoryIndex;
use App\Modules\Admin\Livewire\Coupons\CouponForm;
use App\Modules\Admin\Livewire\Coupons\CouponIndex;
use App\Modules\Admin\Livewire\Customers\CustomerIndex;
use App\Modules\Admin\Livewire\Dashboard;
use App\Modules\Admin\Livewire\Inventory\MovementIndex;
use App\Modules\Admin\Livewire\Inventory\StockIndex;
use App\Modules\Admin\Livewire\Inventory\WarehouseForm;
use App\Modules\Admin\Livewire\Inventory\WarehouseIndex;
use App\Modules\Admin\Livewire\Notifications\SmsLogIndex;
use App\Modules\Admin\Livewire\Orders\OrderIndex;
use App\Modules\Admin\Livewire\Orders\OrderShow;
use App\Modules\Admin\Livewire\Payments\PaymentIndex;
use App\Modules\Admin\Livewire\Settings\SettingsPage;
use App\Modules\Admin\Livewire\Shipping\ShippingMethodForm;
use App\Modules\Admin\Livewire\Shipping\ShippingMethodIndex;
use App\Modules\Cart\Livewire\CartPage;
use App\Modules\Catalog\Livewire\CategoryIndex;
use App\Modules\Catalog\Livewire\CategoryShow;
use App\Modules\Catalog\Livewire\HomePage;
use App\Modules\Catalog\Livewire\ProductIndex;
use App\Modules\Catalog\Livewire\ProductShow;
use App\Modules\Catalog\Livewire\SearchPage;
use App\Modules\Checkout\Livewire\CheckoutPage;
use App\Modules\Customers\Livewire\AddressBookPage;
use App\Modules\Customers\Livewire\ProfilePage;
use App\Modules\Identity\Livewire\Login;
use App\Modules\Identity\Livewire\Register;
use App\Modules\Orders\Livewire\OrderListPage;
use App\Modules\Orders\Livewire\OrderShowPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::get('/products', ProductIndex::class)->name('products.index');
Route::get('/products/{product:slug}', ProductShow::class)->name('products.show');
Route::get('/categories', CategoryIndex::class)->name('categories.index');
Route::get('/categories/{category:slug}', CategoryShow::class)->name('categories.show');
Route::get('/search', SearchPage::class)->name('search');
Route::get('/cart', CartPage::class)->name('cart.index');

Route::get('/payments/zarinpal/callback', [PaymentCallbackController::class, 'zarinpal'])
    ->name('payments.zarinpal.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/account', 'storefront.account')->name('account.index');
    Route::get('/account/profile', ProfilePage::class)->name('account.profile');
    Route::get('/account/addresses', AddressBookPage::class)->name('account.addresses.index');
    Route::get('/account/orders', OrderListPage::class)->name('account.orders.index');
    Route::get('/account/orders/{order}', OrderShowPage::class)->name('account.orders.show');
    Route::get('/checkout', CheckoutPage::class)->name('checkout');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        Route::get('/products', AdminProductIndex::class)->name('products.index');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');

        Route::get('/categories', AdminCategoryIndex::class)->name('categories.index');
        Route::get('/categories/create', CategoryForm::class)->name('categories.create');
        Route::get('/categories/{category}/edit', CategoryForm::class)->name('categories.edit');

        Route::get('/brands', BrandIndex::class)->name('brands.index');
        Route::get('/brands/create', BrandForm::class)->name('brands.create');
        Route::get('/brands/{brand}/edit', BrandForm::class)->name('brands.edit');

        Route::get('/warehouses', WarehouseIndex::class)->name('warehouses.index');
        Route::get('/warehouses/create', WarehouseForm::class)->name('warehouses.create');
        Route::get('/warehouses/{warehouse}/edit', WarehouseForm::class)->name('warehouses.edit');

        Route::get('/inventory', StockIndex::class)->name('inventory.index');
        Route::get('/inventory/movements', MovementIndex::class)->name('inventory.movements');

        Route::get('/orders', OrderIndex::class)->name('orders.index');
        Route::get('/orders/{order}', OrderShow::class)->name('orders.show');

        Route::get('/customers', CustomerIndex::class)->name('customers.index');
        Route::get('/payments', PaymentIndex::class)->name('payments.index');

        Route::get('/coupons', CouponIndex::class)->name('coupons.index');
        Route::get('/coupons/create', CouponForm::class)->name('coupons.create');
        Route::get('/coupons/{coupon}/edit', CouponForm::class)->name('coupons.edit');

        Route::get('/shipping', ShippingMethodIndex::class)->name('shipping.index');
        Route::get('/shipping/create', ShippingMethodForm::class)->name('shipping.create');
        Route::get('/shipping/{shippingMethod}/edit', ShippingMethodForm::class)->name('shipping.edit');

        Route::get('/sms-logs', SmsLogIndex::class)->name('sms-logs.index');
        Route::get('/activity-logs', ActivityLogIndex::class)->name('activity-logs.index');
        Route::get('/settings', SettingsPage::class)->name('settings.index');
    });
