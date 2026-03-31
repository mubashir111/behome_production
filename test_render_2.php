<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('is_admin', 1)->first() ?? App\Models\User::first();
    Illuminate\Support\Facades\Auth::login($user);
    $view = app(App\Http\Controllers\Admin\DashboardController::class)->index();
    $html = $view->render();
    echo "SUCCESS: Rendered " . strlen($html) . " bytes.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
