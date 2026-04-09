<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlineBilling;

class OnlineBillingController extends Controller
{
    public function index()
    {
        return response()->json(
            OnlineBilling::all()
        );
    }

    public function show($id)
    {
        return response()->json(
            OnlineBilling::find($id)
        );
    }
}
