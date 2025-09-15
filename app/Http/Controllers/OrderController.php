<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Discount;


class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['orderItems.book', 'user'])->get();
        return response()->json($orders);
    }

    public function store(Request $request)
{
    $userId = Auth::id();
    if (!$userId) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    $cart = Cart::with('items.book')->where('user_id', $userId)->first();
    if (!$cart || $cart->items->isEmpty()) {
        return response()->json(['error' => 'Giỏ hàng trống'], 400);
    }

    return DB::transaction(function () use ($request, $cart, $userId) {
        $totalQuantity = 0;
        $totalCost = 0;

        foreach ($cart->items as $item) {
            if (!$item->book) {
                return response()->json(['error' => 'Sản phẩm không còn tồn tại'], 400);
            }
            $price = $item->book->discount_price ?? $item->book->price;
            $totalQuantity += $item->quantity;
            $totalCost += $price * $item->quantity;
        }

        $discount = Discount::where('active', true)->value('discount_percent');
        $discountAmount = $discount ? $totalCost * ($discount / 100) : 0;
        $total = $totalCost - $discountAmount;

        $order = Order::create([
            'user_id'        => $userId,
            'payment_method' => $request->input('payment_method'),
            'phone'          => $request->input('phone'),
            'address'        => $request->input('address'),
            'total_cost'     => $total,
            'discount' => $discountAmount,
            'quantity' => $totalQuantity,
            'state'         => 'Chờ xác nhận',
            'payment_status' => 'Chưa thanh toán',
        ]);

        foreach ($cart->items as $item) {
            $price = $item->book->discount_price ?? $item->book->price;
            OrderItem::create([
                'order_id' => $order->id,
                'book_id'  => $item->book_id,
                'quantity' => $item->quantity,
                'price'    => $price,
            ]);
        }

        $cart->items()->delete(); 

        Notification::create([
            'user_id' => $userId,
            'message' => 'Đơn hàng #' . $order->id . ' đã được tạo thành công. Vui lòng đợi người quản trị duyệt đơn của bạn',
        ]);

        return response()->json([
            'message' => 'Đặt hàng thành công!',
            'order'   => $order->load('orderItems.book'),
        ]);
    });
}

    public function show($id)
    {
        $order = Order::with(['orderItems.book', 'user'])->findOrFail($id);
        return response()->json($order);
    }

    public function showByUser()
    {
        $userId = Auth::id();
        $orders = Order::with(['orderItems.book', 'user'])->where('user_id', $userId)->get();
        return response()->json($orders);
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        $data = $request->validated();

        $order = Order::findOrFail($id);
        if ($order->state == 'Đã giao') {
            $data['payment_status'] = 'Đã thanh toán';
        }
        $order->update($data);

        if ($order->state == 'Đã giao') {
            Notification::create([
                'user_id' => $order->user_id,
                'message' => 'Đơn hàng #' . $order->id . ' đã được giao thành công. Vui lòng check lịch sử đơn hàng để kiểm tra',
            ]);
        }
        if ($order->state == 'Đã hủy') {
            Notification::create([
                'user_id' => $order->user_id,
                'message' => 'Đơn hàng #' . $order->id . ' đã bị hủy. Vui lòng check lịch sử đơn hàng để kiểm tra, hoặc liên hệ admin để biết thêm chi tiết',
            ]);
        }
        if ($order->state == 'Chờ xác nhận') {
            Notification::create([
                'user_id' => $order->user_id,
                'message' => 'Đơn hàng #' . $order->id . ' đã được xác nhận. Vui lòng check lịch sử đơn hàng để kiểm tra',
            ]);
        }
        if ($order->state == 'Đang vận chuyển') {
            Notification::create([
                'user_id' => $order->user_id,
                'message' => 'Đơn hàng #' . $order->id . ' đã được chuyển qua đơn vị vận chuyển. Vui lòng check lịch sử đơn hàng để kiểm tra',
            ]);
        }
        
        return response()->json([
            'message' => 'Cập nhật đơn hàng thành công',
            'order' => $order->load(['orderItems.book'])
        ]);
    }

    public function destroy($id)
    {
        Order::findOrFail($id)->delete();
        return response()->json([
            'message' => 'Xóa đơn hàng thành công'
        ]);
    }

    public function stats(){
        $orderTotal = Order::count();
        $deliveredOrder = Order::where('state','Đã giao')->count();
        $pendingOrder = Order::where('state','Chờ xác nhận')->count();
        $totalRevenue = Order::where('state','Đã giao')->sum('total_cost');
        return response()->json([
            'orderTotal' => $orderTotal,
            'deliveredOrder' => $deliveredOrder,
            'pendingOrder' => $pendingOrder,
            'totalRevenue' => $totalRevenue
        ]);
    }

}