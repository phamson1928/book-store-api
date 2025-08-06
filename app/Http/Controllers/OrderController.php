<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        return Order::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'address'=>'required|string',
            'quantity'=>'required|integer',
            'total_cost'=>'required|numeric',
            'state'=>'required|string',
        ]);
        $data = Order::create([
            'user_id'=>Auth::id(),
            'address'=>$request->address,
            'quantity'=>$request->quantity,
            'total_cost'=>$request->total_cost,
            'state'=>'pending',
        ]);
        return response()->json($data);
    }

    public function show($id)
    {
        return response()->json(Order::findOrFail($id));
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'address'=>'required|string',
            'quantity'=>'required|integer',
            'total_cost'=>'required|numeric',
            'state'=>'required|string',
        ]);
        $data = Order::findOrFail($id)->update($request->all());
        return response()->json(['message'=>'Chinh sửa thành công',$data]);
    }

    public function destroy($id)
    {
        return response()->json(['message'=>'Xóa thành công',Order::findOrFail($id)->delete()]);
    }
}
