<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PingController extends Controller
{
    public function ping(Request $request)
    {
        $request->user()->update(['last_seen' => now()]);

        return response()->json(['status' => 'ok']);
    }

}
