<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Damage;
use Illuminate\Http\Request;

class DamageWebController extends Controller
{
    public function index()
    {
        $damages = Damage::latest()->paginate(20);
        return view('admin.damages.index', compact('damages'));
    }

    public function destroy(Damage $damage)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($damage) {
                $productName = $damage->product->name ?? 'Unknown Product';
                $quantity    = $damage->quantity;
                
                $damage->stocks()->delete();
                $damage->delete();
                
                \App\Models\AdminNotification::record('warning', 'Damage Record Deleted', "A damage adjustment of {$quantity} units for '{$productName}' was removed by " . (auth()->user()->name ?? 'Admin'));
            });
            return back()->with('success', 'Damage record deleted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
