<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentGatewayService;
use App\Models\PaymentGateway;
use App\Http\Requests\PaginateRequest;
use Exception;

class PaymentGatewayController extends Controller
{
    private PaymentGatewayService $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function index(PaginateRequest $request)
    {
        try {
            $paymentGateways = $this->paymentGatewayService->list($request);
            return view('admin.payment-gateways.index', compact('paymentGateways'));
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function edit(PaymentGateway $paymentGateway)
    {
        $paymentGateway->load('gatewayOptions');
        return view('admin.payment-gateways.edit', compact('paymentGateway'));
    }

    public function update(Request $request, PaymentGateway $paymentGateway)
    {
        $className = 'App\\Http\\PaymentGateways\\Requests\\' . ucfirst($paymentGateway->slug);
        $rules = [];

        if (class_exists($className)) {
            $gateway = new $className;
            $rules   = $gateway->rules();
        }

        $rules['gateway_status'] = ['required', 'numeric'];

        $validationRequests                       = $request->validate($rules);
        $validationRequests['payment_gateway_id'] = $paymentGateway->id;

        try {
            $this->paymentGatewayService->update($validationRequests);
            return redirect()->route('admin.payment-gateways.index')->with('success', 'Payment gateway updated successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }
}
