<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SiteService;
use App\Services\CompanyService;
use App\Services\ThemeService;
use App\Services\ShippingSetupService;
use App\Services\NotificationService;
use App\Http\Requests\SiteRequest;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\ThemeRequest;
use App\Http\Requests\ShippingSetupRequest;
use App\Http\Requests\NotificationRequest;

class SettingsController extends Controller
{
    public function site(SiteService $service)
    {
        $settings = $service->list();
        return view('admin.settings.site', compact('settings'));
    }

    public function company(CompanyService $service)
    {
        $settings = $service->list();
        return view('admin.settings.company', compact('settings'));
    }

    public function theme(ThemeService $service)
    {
        $settings = $service->list();
        return view('admin.settings.theme', compact('settings'));
    }

    public function shipping(ShippingSetupService $service)
    {
        $settings = $service->list();
        return view('admin.settings.shipping', compact('settings'));
    }

    public function notification(NotificationService $service)
    {
        $settings = $service->list();
        return view('admin.settings.notification', compact('settings'));
    }

    public function updateSite(SiteRequest $request, SiteService $service)
    {
        try {
            $service->update($request);
            return back()->with('success', 'Site settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateCompany(CompanyRequest $request, CompanyService $service)
    {
        try {
            $service->update($request);
            return back()->with('success', 'Company settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateTheme(ThemeRequest $request, ThemeService $service)
    {
        try {
            $service->update($request);
            return back()->with('success', 'Theme updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateShipping(ShippingSetupRequest $request, ShippingSetupService $service)
    {
        try {
            $service->update($request);
            return back()->with('success', 'Shipping settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateNotification(NotificationRequest $request, NotificationService $service)
    {
        try {
            $service->update($request);
            return back()->with('success', 'Notification settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
