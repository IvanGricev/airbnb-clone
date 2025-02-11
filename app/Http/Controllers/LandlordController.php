<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LandlordApplication;

class LandlordController extends Controller
{
    public function showApplyForm()
    {
        return view('landlord.apply');
    }

    public function apply(Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:10',
        ]);

        LandlordApplication::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'status'  => 'pending',
        ]);

        return redirect()->route('home')->with('success', 'Ваша заявка отправлена на рассмотрение.');
    }
}
