<?php

namespace App\Http\Controllers\Frontend;


use App\Enums\Status;
use App\Models\Analytic;
use App\Models\ThemeSetting;
use App\Http\Controllers\Controller;

class RootController extends Controller
{
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect(env('FRONTEND_URL', 'http://localhost:3000'));
    }

    public function admin(): \Illuminate\Contracts\View\View
    {
        return view('master', [
            'favicon'   => \App\Models\ThemeSetting::where(['key' => 'theme_favicon_logo'])->first()?->faviconLogo,
            'analytics' => \App\Models\Analytic::with('analyticSections')->get()
        ]);
    }
}
