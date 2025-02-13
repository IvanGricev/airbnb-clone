<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\LandlordApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LandlordController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

}