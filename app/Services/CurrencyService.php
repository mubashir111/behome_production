<?php

namespace App\Services;


use Exception;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CurrencyRequest;
use App\Http\Requests\PaginateRequest;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Support\Facades\Artisan;
use Smartisan\Settings\Facades\Settings;

class CurrencyService
{
    public function __construct(
        private readonly EnvEditor $envEditor
    ) {
    }

    protected $currencyFilter = [
        'name',
        'symbol',
        'code',
        'is_cryptocurrency',
        'exchange_rate'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Currency::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->currencyFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(CurrencyRequest $request)
    {
        try {
            return Currency::create($request->validated());
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(CurrencyRequest $request, Currency $currency)
    {
        try {
            return tap($currency)->update($request->validated());
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Currency $currency): void
    {
        try {
            if (Settings::group('site')->get("site_default_currency") != $currency->id) {
                $currency->delete();
            } else {
                throw new Exception("Default currency not deletable", 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function setDefault(Currency $currency): void
    {
        try {
            $siteSettings = Settings::group('site')->all();

            Settings::group('site')->set(array_merge($siteSettings, [
                'site_default_currency'        => $currency->id,
                'site_default_currency_symbol' => $currency->symbol,
            ]));

            $currencyPosition = $siteSettings['site_currency_position'] ?? env('CURRENCY_POSITION', 5);
            $decimalPoint     = $siteSettings['site_digit_after_decimal_point'] ?? env('CURRENCY_DECIMAL_POINT', 2);

            $this->envEditor->addData([
                'CURRENCY'               => $currency->code,
                'CURRENCY_SYMBOL'        => $currency->symbol,
                'CURRENCY_POSITION'      => $currencyPosition,
                'CURRENCY_DECIMAL_POINT' => $decimalPoint,
            ]);

            Artisan::call('optimize:clear');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
