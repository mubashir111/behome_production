<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use Illuminate\Http\Request;

class BarcodeWebController extends Controller
{
    public function index()
    {
        $barcodes = Barcode::latest()->paginate(30);
        return view('admin.barcodes.index', compact('barcodes'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:barcodes,name']);
        Barcode::create(['name' => $request->name]);
        return back()->with('success', 'Barcode type added.');
    }

    public function destroy(Barcode $barcode)
    {
        $barcode->delete();
        return back()->with('success', 'Barcode type deleted.');
    }
}
