<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('is_admin', 1)->first() ?? App\Models\User::first();
    Illuminate\Support\Facades\Auth::login($user);
    $view = app(App\Http\Controllers\Admin\DashboardController::class)->index();
    echo "SUCCESS\n";
    // echo substr($view->render(), 0, 100);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine();
}
