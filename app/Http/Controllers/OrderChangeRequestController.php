<?php

namespace App\Http\Controllers;

use App\Models\OrderChangeRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreOrderChangeRequest;

class OrderChangeRequestController extends Controller
{
    public function index()
    {
        $data = OrderChangeRequest::all();
        return response()->json($data);
    }

    public function store(StoreOrderChangeRequest $request){
        $request->validated();

        $changeRequest = OrderChangeRequest::create([
            'order_id' => $request->order_id,
            'user_id' => Auth::id(),
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Yêu cầu đã được gửi tới admin.',
            'data' => $changeRequest
        ], 201);
    }

}
