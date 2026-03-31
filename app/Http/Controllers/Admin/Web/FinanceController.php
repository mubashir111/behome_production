<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\CurrencyService;
use App\Services\TaxService;
use App\Http\Requests\CurrencyRequest;
use App\Http\Requests\TaxRequest;
use App\Http\Requests\PaginateRequest;
use Smartisan\Settings\Facades\Settings;

class FinanceController extends Controller
{
    public function currency(PaginateRequest $request, CurrencyService $service)
    {
        $currencies         = Currency::latest()->paginate(10);
        $defaultCurrencyId  = (int) Settings::group('site')->get('site_default_currency');
        $editCurrency       = null;

        return view('admin.finance.currency', compact('currencies', 'editCurrency', 'defaultCurrencyId'));
    }

    public function editCurrency(Currency $currency)
    {
        $currencies        = Currency::latest()->paginate(10);
        $editCurrency      = $currency;
        $defaultCurrencyId = (int) Settings::group('site')->get('site_default_currency');

        return view('admin.finance.currency', compact('currencies', 'editCurrency', 'defaultCurrencyId'));
    }

    public function storeCurrency(CurrencyRequest $request, CurrencyService $service)
    {
        try {
            $service->store($request);
            return back()->with('success', 'Currency added.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateCurrency(CurrencyRequest $request, \App\Models\Currency $currency, CurrencyService $service)
    {
        try {
            $service->update($request, $currency);
            return back()->with('success', 'Currency updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyCurrency(\App\Models\Currency $currency, CurrencyService $service)
    {
        try {
            $service->destroy($currency);
            return back()->with('success', 'Currency deleted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function setDefaultCurrency(Currency $currency, CurrencyService $service)
    {
        try {
            $service->setDefault($currency);
            return back()->with('success', 'Default currency updated.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tax(PaginateRequest $request, TaxService $service)
    {
        $taxes = \App\Models\Tax::latest()->paginate(10);
        return view('admin.finance.tax', compact('taxes'));
    }

    public function storeTax(TaxRequest $request, TaxService $service)
    {
        try {
            $service->store($request);
            return back()->with('success', 'Tax rule created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateTax(TaxRequest $request, \App\Models\Tax $tax, TaxService $service)
    {
        try {
            $service->update($request, $tax);
            return back()->with('success', 'Tax rule updated.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyTax(\App\Models\Tax $tax, TaxService $service)
    {
        try {
            $service->destroy($tax);
            return back()->with('success', 'Tax rule deleted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
