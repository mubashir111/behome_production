<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AddressController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $addresses = Address::where('user_id', Auth::id())->get();
            return $this->successResponse($addresses, 'Addresses retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name'    => 'required|string|max:191',
                'email'        => 'required|email|max:191',
                'phone'        => 'required|string|max:191',
                'country'      => 'required|string|max:191',
                'address'      => 'required|string',
                'city'         => 'required|string|max:191',
                'state'        => 'nullable|string|max:191',
                'zip_code'     => 'required|string|max:191',
                'country_code' => 'nullable|string|max:10',
            ]);

            $address = Address::create($validated + ['user_id' => Auth::id()]);
            return $this->successResponse($address, 'Address created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function show(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }
        return $this->successResponse($address, 'Address details retrieved successfully');
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        try {
            $validated = $request->validate([
                'full_name'    => 'nullable|string|max:191',
                'email'        => 'nullable|email|max:191',
                'phone'        => 'nullable|string|max:191',
                'country'      => 'nullable|string|max:191',
                'address'      => 'nullable|string',
                'city'         => 'nullable|string|max:191',
                'state'        => 'nullable|string|max:191',
                'zip_code'     => 'nullable|string|max:191',
                'country_code' => 'nullable|string|max:10',
            ]);

            $address->update($validated);
            return $this->successResponse($address, 'Address updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        try {
            $address->delete();
            return $this->successResponse(null, 'Address deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
