<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SiteService;
use App\Services\CompanyService;
use App\Services\ThemeService;
use App\Services\ShippingSetupService;
use App\Services\NotificationService;
use App\Services\NotificationAlertService;
use App\Services\MailService;
use App\Http\Requests\SiteRequest;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\ThemeRequest;
use App\Http\Requests\ShippingSetupRequest;
use App\Http\Requests\NotificationRequest;
use App\Http\Requests\MailRequest;
use App\Http\Requests\OtpRequest;
use App\Models\NotificationAlert;
use App\Models\SmsGateway;
use App\Models\GatewayOption;
use App\Services\OtpService;
use App\Services\SmsGatewayService;
use App\Enums\SwitchBox;
use Smartisan\Settings\Facades\Settings;

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

    public function seo()
    {
        $settings = Settings::group('seo')->all();
        return view('admin.settings.seo', compact('settings'));
    }

    public function updateSeo(Request $request)
    {
        try {
            $data = $request->only([
                'seo_site_title', 'seo_title_separator', 'seo_meta_description',
                'seo_meta_keywords', 'seo_google_analytics_id', 'seo_google_tag_manager_id', 'seo_robots_txt',
            ]);
            Settings::group('seo')->set($data); // Removed array_filter to allow empty/null values
            if ($request->hasFile('seo_og_image')) {
                $setting = \App\Models\ThemeSetting::where('key', 'seo_og_image')->firstOrCreate(['key' => 'seo_og_image', 'value' => 'seo_og_image']);
                $setting->clearMediaCollection('seo-og-image');
                $setting->addMediaFromRequest('seo_og_image')->toMediaCollection('seo-og-image');
            }
            
            \App\Models\AdminNotification::record('info', 'SEO Settings Updated', 'Global SEO configuration was updated by ' . (auth()->user()->name ?? 'Admin'));
            
            return back()->with('success', 'SEO settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function smtp()
    {
        $settings = Settings::group('mail')->all();
        return view('admin.settings.smtp', compact('settings'));
    }

    public function updateSmtp(MailRequest $request, MailService $service)
    {
        try {
            $service->update($request);
            \App\Models\AdminNotification::record('warning', 'SMTP Settings Updated', 'System mail configuration was modified by ' . (auth()->user()->name ?? 'Admin'));
            return back()->with('success', 'Mail settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function testSmtp(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);
        try {
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email from Behome admin panel. Your SMTP configuration is working correctly.',
                function ($msg) use ($request) {
                    $msg->to($request->test_email)->subject('Behome — SMTP Test Email');
                }
            );
            return back()->with('success', 'Test email sent to ' . $request->test_email);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function integrations()
    {
        $settings = Settings::group('integrations')->all();
        return view('admin.settings.integrations', compact('settings'));
    }

    public function updateIntegrations(Request $request)
    {
        try {
            $data = $request->only([
                'stripe_refund_enabled', 'stripe_refund_secret_key',
                'google_client_id', 'google_client_secret',
                'facebook_app_id', 'facebook_app_secret',
            ]);
            
            // Map checkboxes to ensure they are 0 if missing from request
            $data['stripe_refund_enabled'] = $request->has('stripe_refund_enabled') ? 1 : 0;
            
            Settings::group('integrations')->set($data); // Removed array_filter to allow disabling features

            // Sync Google keys to .env so services.php and frontend pick them up
            $envEditor = app(\Dipokhalder\EnvEditor\EnvEditor::class);
            $envData = [];
            if ($request->filled('google_client_id'))     $envData['GOOGLE_CLIENT_ID']     = $request->google_client_id;
            if ($request->filled('google_client_secret')) $envData['GOOGLE_CLIENT_SECRET'] = $request->google_client_secret;
            
            if (!empty($envData)) {
                $envEditor->addData($envData);
                // Hazardous Artisan::call('optimize:clear') removed from web thread.
                // Recommend manual clearing or targeted config refresh in production.
            }

            \App\Models\AdminNotification::record('warning', 'Integrations Updated', 'Third-party integration keys (Stripe/Google) were updated by ' . (auth()->user()->name ?? 'Admin'));

            return back()->with('success', 'Integration settings updated. Note: You may need to clear your application cache for changes to take effect everywhere.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function notificationAlerts(NotificationAlertService $service)
    {
        $alerts = $service->list();
        return view('admin.settings.notification_alerts', compact('alerts'));
    }

    public function updateNotificationAlerts(Request $request)
    {
        try {
            $alertsData = $request->input('alerts', []);
            foreach ($alertsData as $id => $data) {
                NotificationAlert::where('id', $id)->update([
                    'mail'                      => isset($data['mail']) ? SwitchBox::ON : SwitchBox::OFF,
                    'sms'                       => isset($data['sms']) ? SwitchBox::ON : SwitchBox::OFF,
                    'push_notification'         => isset($data['push_notification']) ? SwitchBox::ON : SwitchBox::OFF,
                    'mail_message'              => $data['mail_message'] ?? '',
                    'sms_message'               => $data['sms_message'] ?? '',
                    'push_notification_message' => $data['push_notification_message'] ?? '',
                ]);
            }
            return back()->with('success', 'Notification alerts updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function otp(OtpService $service)
    {
        $settings = $service->list();
        return view('admin.settings.otp', compact('settings'));
    }

    public function updateOtp(OtpRequest $request, OtpService $service)
    {
        try {
            // Update OTP group settings
            $service->update($request);
            
            // Update Site group settings (Registration rules)
            $siteData = $request->only(['site_phone_verification', 'site_email_verification']);
            if (!empty($siteData)) {
                Settings::group('site')->set(array_filter($siteData, fn($v) => !is_null($v)));
            }

            return back()->with('success', 'Verification & OTP settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function smsGateway(SmsGatewayService $service)
    {
        $gateways = SmsGateway::with('gatewayOptions')->get();
        return view('admin.settings.sms_gateway', compact('gateways'));
    }

    public function updateSmsGateway(Request $request, SmsGatewayService $service)
    {
        try {
            $gateway = SmsGateway::where('slug', $request->sms_type)->firstOrFail();
            $className = 'App\\Http\\SmsGateways\\Requests\\' . ucfirst($gateway->slug);
            $gatewayRequest = new $className;
            $validationRequests = $request->validate($gatewayRequest->rules());
            
            $service->update($validationRequests);
            return back()->with('success', 'SMS gateway settings updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
