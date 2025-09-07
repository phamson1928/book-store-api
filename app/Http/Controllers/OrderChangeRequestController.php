<?php

namespace App\Http\Controllers;

use App\Models\OrderChangeRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreOrderChangeRequest;
use App\Http\Requests\UpdateOrderChangeRequest;
use App\Models\Order;

class OrderChangeRequestController extends Controller
{
    public function index()
    {
        $data = OrderChangeRequest::with('user')->get();
        return response()->json($data);
    }

    public function store(StoreOrderChangeRequest $request, $id){
        $request->validated();

        $order = Order::where('id', $id)    
        ->where('user_id', Auth::id())
        ->firstOrFail();

        $changeRequest = OrderChangeRequest::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Yêu cầu đã được gửi tới admin.',
            'data' => $changeRequest
        ], 201);
    }

    public function showAdminResponse($id)
    {
        $data = OrderChangeRequest::where($id,Auth::id())->select('status','admin_response')->with('order')->get();
        return response()->json($data);
    }

    public function updateAdminResponse(UpdateOrderChangeRequest $request, $id)
    {
        $request->validated();

        $changeRequest = OrderChangeRequest::where('order_id', $id)->update([
            'admin_response' => $request->admin_response,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Cập nhật yêu cầu thành công.',
            'data' => $changeRequest
        ], 200);
    }

    public function destroy($id)
    {
        OrderChangeRequest::where('id', $id)->delete();
        return response()->json([
            'message' => 'Xóa yêu cầu thành công.',
        ], 200);
    }

    public function destroyAll(){
        OrderChangeRequest::where('status','Hoàn thành')->where('status','Đã từ chối')->delete();
        return response()->json([
            'message' => 'Xóa tất cả yêu cầu thành công.',
        ], 200);
    }
}
