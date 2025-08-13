<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['orderItems.book', 'user'])->get();
        return response()->json($orders);
    }

    public function store()
    {
        $user = Auth::user();

        // Lấy giỏ hàng
        $cart = Cart::with('items.book')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Giỏ hàng trống'], 400);
        }

        DB::beginTransaction();
        try {
            // Tính tổng tiền
            $totalQuantity = 0;
            $totalCost = 0;

            foreach ($cart->items as $item) {
                $price = $item->book->discount_price ?? $item->book->price;
                $totalQuantity += $item->quantity;
                $totalCost += $price * $item->quantity;
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user->id,
                'total_quantity' => $totalQuantity,
                'total_cost' => $totalCost,
                'status' => 'pending',
            ]);

            // Tạo chi tiết đơn hàng
            foreach ($cart->items as $item) {
                $price = $item->book->discount_price ?? $item->book->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item->book_id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ]);
            }

            // Xóa giỏ hàng
            $cart->items()->delete();

            DB::commit();

            return response()->json(['message' => 'Đặt hàng thành công', 'order' => $order]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi khi đặt hàng', 'details' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with(['orderItems.book', 'user'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        $data = $request->validated();

        $order = Order::findOrFail($id);
        $order->update($data);
        
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
        $orderTotal = Order::all()->count();
        $deliveredOrder = Order::where('state','delivered')->count();
        $pendingOrder = Order::where('state','pending')->count();
        $totalRevenue = Order::sum('totalCost');
        return response()->json([
            'orderTotal' => $orderTotal,
            'deliveredOrder' => $deliveredOrder,
            'pendingOrder' => $pendingOrder,
            'totalRevenue' => $totalRevenue
        ]);
    }
}
