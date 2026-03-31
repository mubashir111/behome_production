<?php

use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\RootController;
use App\Http\Controllers\Installer\InstallerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('install')->name('installer.')->middleware(['web'])->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('index');
    Route::get('/requirement', [InstallerController::class, 'requirement'])->name('requirement');
    Route::get('/permission', [InstallerController::class, 'permission'])->name('permission');
    Route::get('/license', [InstallerController::class, 'license'])->name('license');
    Route::post('/license', [InstallerController::class, 'licenseStore'])->name('licenseStore');
    Route::get('/site', [InstallerController::class, 'site'])->name('site');
    Route::post('/site', [InstallerController::class, 'siteStore'])->name('siteStore');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'databaseStore'])->name('databaseStore');
    Route::get('/final', [InstallerController::class, 'final'])->name('final');
    Route::get('/final-store', [InstallerController::class, 'finalStore'])->name('finalStore');
});

Route::get('/', [RootController::class, 'index'])->middleware(['installed'])->name('home');

// Redirect @2x retina image requests to the regular version (template JS requests these even when they don't exist)
Route::get('/storage/{path}', function (string $path) {
    if (str_contains($path, '@2x')) {
        $regular = str_replace('@2x', '', $path);
        return redirect('/storage/' . $regular, 301);
    }
    abort(404);
})->where('path', '.*@2x.*');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login'])->name('login.post');
    });

    Route::middleware(['auth', 'is_admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('logout');

        Route::post('products/{product}/images/{index}', [\App\Http\Controllers\Admin\Web\ProductController::class, 'replaceImage'])->name('products.images.replace');
        Route::delete('products/{product}/images/{index}', [\App\Http\Controllers\Admin\Web\ProductController::class, 'deleteImage'])->name('products.images.delete');
        Route::post('products/{product}/upload-block-image', [\App\Http\Controllers\Admin\Web\ProductController::class, 'uploadBlockImage'])->name('products.upload-block-image');
        Route::post('products/{product}/upload-gallery-image', [\App\Http\Controllers\Admin\Web\ProductController::class, 'uploadGalleryImageAjax'])->name('products.upload-gallery-image');
        Route::post('products/{product}/replace-gallery-image/{index}', [\App\Http\Controllers\Admin\Web\ProductController::class, 'replaceGalleryImageAjax'])->name('products.replace-gallery-image');
        Route::resource('products', \App\Http\Controllers\Admin\Web\ProductController::class);
        Route::resource('categories', \App\Http\Controllers\Admin\Web\CategoryController::class);
        Route::resource('orders', \App\Http\Controllers\Admin\Web\OrderController::class)->only(['index', 'show', 'update', 'destroy']);
        
        // Product Variations (Web UI)
        Route::prefix('products/{product}/variations')->name('products.variations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ProductVariationController::class, 'index'])->name('index');
            Route::post('/store', [\App\Http\Controllers\Admin\ProductVariationController::class, 'store'])->name('store');
            Route::post('/update-simple/{productVariation}', [\App\Http\Controllers\Admin\ProductVariationController::class, 'updateSimple'])->name('update-simple');
            Route::delete('/destroy/{productVariation}', [\App\Http\Controllers\Admin\ProductVariationController::class, 'destroy'])->name('destroy');
        });
        Route::put('orders/{order}/payment-status', [\App\Http\Controllers\Admin\Web\OrderController::class, 'updatePaymentStatus'])->name('orders.payment-status.update');
        Route::post('orders/{order}/reply', [\App\Http\Controllers\Admin\Web\OrderController::class, 'reply'])->name('orders.reply');
        Route::get('order-messages', [\App\Http\Controllers\Admin\Web\OrderMessageWebController::class, 'index'])->name('order-messages.index');
        Route::post('order-messages/{order}/reply', [\App\Http\Controllers\Admin\Web\OrderMessageWebController::class, 'reply'])->name('order-messages.reply');
        Route::resource('customers', \App\Http\Controllers\Admin\Web\CustomerController::class);
        Route::resource('reviews', \App\Http\Controllers\Admin\Web\ReviewController::class)->only(['index', 'show', 'destroy']);
        Route::get('returns', [\App\Http\Controllers\Admin\Web\ReturnAndRefundWebController::class, 'index'])->name('returns.index');
        Route::get('returns/{return}', [\App\Http\Controllers\Admin\Web\ReturnAndRefundWebController::class, 'show'])->name('returns.show');
        Route::post('returns/{return}/change-status', [\App\Http\Controllers\Admin\Web\ReturnAndRefundWebController::class, 'changeStatus'])->name('returns.change-status');
        Route::post('returns/{return}/process-refund', [\App\Http\Controllers\Admin\Web\ReturnAndRefundWebController::class, 'processRefund'])->name('returns.process-refund');
        Route::get('messages', [\App\Http\Controllers\Admin\Web\ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [\App\Http\Controllers\Admin\Web\ContactMessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [\App\Http\Controllers\Admin\Web\ContactMessageController::class, 'destroy'])->name('messages.destroy');

        // Settings
        Route::get('settings/site', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'site'])->name('settings.site');
        Route::post('settings/site', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'updateSite'])->name('settings.site.update');
        Route::get('settings/company', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'company'])->name('settings.company');
        Route::post('settings/company', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'updateCompany'])->name('settings.company.update');
        Route::get('settings/theme', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'theme'])->name('settings.theme');
        Route::post('settings/theme', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'updateTheme'])->name('settings.theme.update');
        Route::get('settings/shipping', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'shipping'])->name('settings.shipping');
        Route::post('settings/shipping', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'updateShipping'])->name('settings.shipping.update');
        Route::get('settings/notification', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'notification'])->name('settings.notification');
        Route::post('settings/notification', [\App\Http\Controllers\Admin\Web\SettingsController::class, 'updateNotification'])->name('settings.notification.update');

        Route::get('shipping/order-areas', [\App\Http\Controllers\Admin\Web\ShippingController::class, 'orderAreas'])->name('shipping.order-areas');
        Route::post('shipping/order-areas', [\App\Http\Controllers\Admin\Web\ShippingController::class, 'storeOrderArea'])->name('shipping.order-areas.store');
        Route::put('shipping/order-areas/{orderArea}', [\App\Http\Controllers\Admin\Web\ShippingController::class, 'updateOrderArea'])->name('shipping.order-areas.update');
        Route::delete('shipping/order-areas/{orderArea}', [\App\Http\Controllers\Admin\Web\ShippingController::class, 'destroyOrderArea'])->name('shipping.order-areas.destroy');

        Route::get('finance/currency', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'currency'])->name('finance.currency');
        Route::get('finance/currency/{currency}/edit', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'editCurrency'])->name('finance.currency.edit');
        Route::post('finance/currency', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'storeCurrency'])->name('finance.currency.store');
        Route::put('finance/currency/{currency}', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'updateCurrency'])->name('finance.currency.update');
        Route::post('finance/currency/{currency}/default', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'setDefaultCurrency'])->name('finance.currency.default');
        Route::delete('finance/currency/{currency}', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'destroyCurrency'])->name('finance.currency.destroy');

        Route::get('finance/tax', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'tax'])->name('finance.tax');
        Route::post('finance/tax', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'storeTax'])->name('finance.tax.store');
        Route::put('finance/tax/{tax}', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'updateTax'])->name('finance.tax.update');
        Route::delete('finance/tax/{tax}', [\App\Http\Controllers\Admin\Web\FinanceController::class, 'destroyTax'])->name('finance.tax.destroy');

        Route::get('users', [\App\Http\Controllers\Admin\Web\UsersController::class, 'index'])->name('users.index');
        Route::get('users/create', [\App\Http\Controllers\Admin\Web\UsersController::class, 'create'])->name('users.create');
        Route::post('users', [\App\Http\Controllers\Admin\Web\UsersController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\Web\UsersController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\Admin\Web\UsersController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\Admin\Web\UsersController::class, 'destroy'])->name('users.destroy');

        Route::get('roles', [\App\Http\Controllers\Admin\Web\RolesController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [\App\Http\Controllers\Admin\Web\RolesController::class, 'create'])->name('roles.create');
        Route::post('roles', [\App\Http\Controllers\Admin\Web\RolesController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [\App\Http\Controllers\Admin\Web\RolesController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [\App\Http\Controllers\Admin\Web\RolesController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [\App\Http\Controllers\Admin\Web\RolesController::class, 'destroy'])->name('roles.destroy');

        Route::get('permissions/{role}/edit', [\App\Http\Controllers\Admin\Web\PermissionsController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{role}', [\App\Http\Controllers\Admin\Web\PermissionsController::class, 'update'])->name('permissions.update');

        Route::resource('payment-gateways', \App\Http\Controllers\Admin\Web\PaymentGatewayController::class)->only(['index', 'edit', 'update']);
        Route::resource('coupons', \App\Http\Controllers\Admin\Web\CouponController::class);
        Route::resource('suppliers', \App\Http\Controllers\Admin\Web\SupplierController::class);
        Route::resource('purchases', \App\Http\Controllers\Admin\Web\PurchaseController::class);
        Route::resource('sliders', \App\Http\Controllers\Admin\Web\SliderController::class);
        Route::post('sliders/{slider}/toggle-status', [\App\Http\Controllers\Admin\Web\SliderController::class, 'toggleStatus'])->name('sliders.toggle-status');

        Route::resource('promotions', \App\Http\Controllers\Admin\Web\PromotionWebController::class)->except(['show']);
        Route::resource('benefits', \App\Http\Controllers\Admin\Web\BenefitWebController::class)->except(['show']);
        Route::resource('brands', \App\Http\Controllers\Admin\Web\BrandWebController::class)->except(['show']);

        // Static Pages & FAQ
        Route::get('pages', [\App\Http\Controllers\Admin\Web\StaticPageController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}/edit', [\App\Http\Controllers\Admin\Web\StaticPageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [\App\Http\Controllers\Admin\Web\StaticPageController::class, 'update'])->name('pages.update');

        Route::get('faq', [\App\Http\Controllers\Admin\Web\FaqWebController::class, 'index'])->name('faq.index');
        Route::get('faq/create', [\App\Http\Controllers\Admin\Web\FaqWebController::class, 'create'])->name('faq.create');
        Route::post('faq', [\App\Http\Controllers\Admin\Web\FaqWebController::class, 'store'])->name('faq.store');
        Route::get('faq/{faq}/edit', [\App\Http\Controllers\Admin\Web\FaqWebController::class, 'edit'])->name('faq.edit');
        Route::put('faq/{faq}', [\App\Http\Controllers\Admin\Web\FaqWebController::class, 'update'])->name('faq.update');
        Route::delete('faq/{faq}', [\App\Http\Controllers\Admin\Web\FaqWebController::class, 'destroy'])->name('faq.destroy');

        // Blog
        Route::resource('blog', \App\Http\Controllers\Admin\Web\BlogPostController::class)->except(['show']);

        // Blog Comments
        Route::get('blog-comments', [\App\Http\Controllers\Admin\Web\BlogCommentController::class, 'index'])->name('blog-comments.index');
        Route::patch('blog-comments/{comment}/approve', [\App\Http\Controllers\Admin\Web\BlogCommentController::class, 'approve'])->name('blog-comments.approve');
        Route::delete('blog-comments/{comment}', [\App\Http\Controllers\Admin\Web\BlogCommentController::class, 'destroy'])->name('blog-comments.destroy');

        // Subscribers
        Route::get('subscribers', [\App\Http\Controllers\Admin\Web\SubscriberController::class, 'index'])->name('subscribers.index');
        Route::delete('subscribers/{subscriber}', [\App\Http\Controllers\Admin\Web\SubscriberController::class, 'destroy'])->name('subscribers.destroy');
        Route::post('blog/{blog}/toggle-publish', [\App\Http\Controllers\Admin\Web\BlogPostController::class, 'togglePublish'])->name('blog.toggle-publish');

        // Notification poll — lightweight JSON endpoint for JS polling
        Route::get('notifications/poll', function (\Illuminate\Http\Request $request) {
            $since = $request->get('since')
                ? \Carbon\Carbon::createFromTimestampMs((int) $request->get('since'))
                : now()->subSeconds(35);

            $newOrders = \App\Models\Order::where('created_at', '>', $since)->latest()->take(5)->get(['id','order_serial_no','total','created_at']);
            $newOrderCount = $newOrders->count();

            $unviewedTotal  = \App\Models\Order::whereNull('admin_viewed_at')->count();
            $unreadMessages = \App\Models\OrderMessage::where('sender_type', 'customer')->where('is_read', false)->count();
            $cancellations  = \App\Models\OrderMessage::where('message', 'like', '[CANCELLATION REQUEST]%')
                ->where('created_at', '>', $since)->count();

            $newReturns      = \App\Models\ReturnAndRefund::where('created_at', '>', $since)->count();
            $pendingReturns  = \App\Models\ReturnAndRefund::where('status', \App\Enums\ReturnOrderStatus::PENDING)->count();
            $unviewedReturns = \App\Models\ReturnAndRefund::whereNull('admin_viewed_at')->count();

            return response()->json([
                'new_orders'       => $newOrderCount,
                'orders'           => $newOrders,
                'unviewed_total'   => $unviewedTotal,
                'unread_msgs'      => $unreadMessages,
                'cancellations'    => $cancellations,
                'new_returns'      => $newReturns,
                'pending_returns'  => $pendingReturns,
                'unviewed_returns' => $unviewedReturns,
                'server_time'      => now()->valueOf(),
            ]);
        })->name('notifications.poll');

        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
    });
});


Route::prefix('payment')->name('payment.')->middleware(['installed'])->group(function () {
    Route::get('/{paymentGateway:slug}/pay/{order}', [PaymentController::class, 'index'])->name('index');
    Route::post('/{order}/pay', [PaymentController::class, 'payment'])->name('store');
    Route::match(['get', 'post'], '/{paymentGateway:slug}/{order}/success', [PaymentController::class, 'success'])->name('success');
    Route::match(['get', 'post'], '/{paymentGateway:slug}/{order}/fail', [PaymentController::class, 'fail'])->name('fail');
    Route::match(['get', 'post'], '/{paymentGateway:slug}/{order}/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    Route::get('/successful/{order}', [PaymentController::class, 'successful'])->name('successful');
});
