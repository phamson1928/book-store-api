<?php

namespace App\Http\Controllers;

use App\Models\OrderChangeRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreOrderChangeRequest;
use App\Http\Requests\UpdateOrderChangeRequest;
use App\Models\Order;
use App\Models\Notification;

class OrderChangeRequestController extends Controller
{
    public function index()
    {
        $data = OrderChangeRequest::with('user')->orderBy('created_at','desc')->get();
        return response()->json($data);
    }

    public function show($id)
    {
        $data = OrderChangeRequest::where('order_id', $id)->value('admin_response');
        return response()->json([
            'admin_response' => $data
        ]);
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

        Notification::create([
            'user_id' => $changeRequest->user_id,
            'message' => 'Bạn đã gửi yêu cầu thay đổi thông tin đơn hàng',
        ]);

        return response()->json([
            'message' => 'Yêu cầu đã được gửi tới admin.',
            'data' => $changeRequest
        ], 201);
    }

    public function updateAdminResponse(UpdateOrderChangeRequest $request, $id)
    {
        $request->validated();

        $changeRequest = OrderChangeRequest::where('order_id', $id)->update([
            'admin_response' => $request->admin_response,
            'status' => $request->status,
        ]);

        if ($request->status == 'Hoàn thành'){
            Notification::create([
                'user_id' => OrderChangeRequest::where('order_id', $id)->value('user_id'),
                'message' => 'Admin đã cập nhật yêu cầu thay đổi thông tin đơn hàng của bạn',
            ]);
        }
        else if ($request->status == 'Đã từ chối'){
            Notification::create([
                'user_id' => OrderChangeRequest::where('order_id', $id)->value('user_id'),
                'message' => 'Admin đã từ chối yêu cầu thay đổi thông tin đơn hàng của bạn.Liên hệ qua số hotline hoặc vào lịch sử đơn hàng để biết thêm chi tiết',
            ]);
        }

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
