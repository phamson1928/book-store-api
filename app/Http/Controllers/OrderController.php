<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Discount;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;


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

        $paymentMethod = $request->input('payment_method', 'cod');
        $paymentStatus = 'Chưa thanh toán';


        $order = Order::create([
            'user_id'        => $userId,
            'payment_method' => $paymentMethod,
            'phone'          => $request->input('phone'),
            'address'        => $request->input('address'),
            'total_cost'     => $total,
            'discount' => $discountAmount,
            'quantity' => $totalQuantity,
            'state'         => 'Chờ xác nhận',
            'payment_status' => $paymentStatus,
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

        // Chỉ xóa giỏ hàng nếu là COD (Cash on Delivery)
        // Với ZaloPay, sẽ xóa giỏ hàng khi thanh toán thành công
        if ($paymentMethod === 'cod') {
            $cart->items()->delete();
            Notification::create([
                'user_id' => $userId,
                'message' => 'Đơn hàng #' . $order->id . ' đã được tạo thành công. Vui lòng đợi người quản trị duyệt đơn của bạn',
            ]);
        }
        
        return response()->json([
            'message' => 'Đặt hàng thành công!',
            'order'   => $order->load('orderItems.book'),
            'payment_method' => $paymentMethod,
            'clear_cart' => $paymentMethod === 'cod'
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
        if ($order->state == 'Đã xác nhận') {
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

    public function destroyByUser($id)
    {
        Order::where('id', $id)->where('user_id', Auth::id())->where('payment_status', 'Chưa thanh toán')->where('state', 'Chờ xác nhận')->delete();
        return response()->json([
            'message' => 'Xóa đơn hàng thành công'
        ]);
    }

    public function stats(){
        $orderTotal = Order::count();
        $deliveredOrder = Order::where('state','Đã giao')->count();
        $pendingOrder = Order::where('state','Chờ xác nhận')->count();
        $totalRevenue = Order::where('payment_status','Đã thanh toán')->sum('total_cost');
        return response()->json([
            'orderTotal' => $orderTotal,
            'deliveredOrder' => $deliveredOrder,
            'pendingOrder' => $pendingOrder,
            'totalRevenue' => $totalRevenue
        ]);
    }

    /**
     * Lấy thông tin thanh toán của đơn hàng
     */
    public function getOrderPayment($id)
    {
        $order = Order::with(['payments', 'orderItems.book'])->findOrFail($id);
        
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $order->user_id) {
            return response()->json(['error' => 'Không có quyền truy cập đơn hàng này'], 403);
        }

        return response()->json([
            'order' => $order,
            'payments' => $order->payments,
            'latest_payment' => $order->latestPayment
        ]);
    }

    /**
     * Hủy đơn hàng và restore giỏ hàng
     */
    public function cancelOrder($id)
    {
        $order = Order::with(['orderItems', 'payments'])->findOrFail($id);
        
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $order->user_id) {
            return response()->json(['error' => 'Không có quyền truy cập đơn hàng này'], 403);
        }

        // Chỉ cho phép hủy đơn hàng chưa thanh toán hoặc thanh toán thất bại
        if ($order->payment_status === 'Đã thanh toán') {
            return response()->json(['error' => 'Không thể hủy đơn hàng đã thanh toán'], 400);
        }

        try {
            DB::beginTransaction();

            // Cập nhật trạng thái đơn hàng thành "Đã hủy"
            $order->update([
                'state' => 'Đã hủy',
                'payment_status' => 'Đã hủy'
            ]);

            // Restore giỏ hàng nếu đơn hàng chưa thanh toán
            if ($order->payment_status !== 'Đã thanh toán') {
                $cart = Cart::where('user_id', $order->user_id)->first();
                if (!$cart) {
                    $cart = Cart::create(['user_id' => $order->user_id]);
                }

                // Thêm lại items vào giỏ hàng
                foreach ($order->orderItems as $orderItem) {
                    $existingCartItem = $cart->items()->where('book_id', $orderItem->book_id)->first();
                    
                    if ($existingCartItem) {
                        $existingCartItem->update([
                            'quantity' => $existingCartItem->quantity + $orderItem->quantity
                        ]);
                    } else {
                        $cart->items()->create([
                            'book_id' => $orderItem->book_id,
                            'quantity' => $orderItem->quantity
                        ]);
                    }
                }

                Log::info('Cart restored after order cancellation', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]);
            }

            // Tạo thông báo
            Notification::create([
                'user_id' => $order->user_id,
                'message' => 'Đơn hàng #' . $order->id . ' đã được hủy. Sản phẩm đã được thêm lại vào giỏ hàng.',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Đơn hàng đã được hủy thành công. Sản phẩm đã được thêm lại vào giỏ hàng.',
                'order' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Có lỗi xảy ra khi hủy đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

}