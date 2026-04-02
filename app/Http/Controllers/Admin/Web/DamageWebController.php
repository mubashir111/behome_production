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
        $damage->stocks()->delete();
        $damage->delete();
        return back()->with('success', 'Damage record deleted.');
    }
}
