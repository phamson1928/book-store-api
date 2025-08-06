<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['orderItems.book', 'user'])->get();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        
        try {
            // Tính tổng số lượng và tổng tiền
            $totalQuantity = 0;
            $totalCost = 0;
            
            foreach ($request->items as $item) {
                $book = Book::findOrFail($item['book_id']);
                $price = $book->discount_price ?? $book->price;
                $totalQuantity += $item['quantity'];
                $totalCost += $price * $item['quantity'];
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'address' => $request->address,
                'quantity' => $totalQuantity,
                'total_cost' => $totalCost,
                'state' => 'pending',
            ]);

            // Tạo chi tiết đơn hàng (order_items)
            foreach ($request->items as $item) {
                $book = Book::findOrFail($item['book_id']);
                $price = $book->discount_price ?? $book->price;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item['book_id'],
                    'quantity' => $item['quantity'],
                    'price' => $price,
                ]);
            }

            DB::commit();
            
            // Load lại order với chi tiết
            $order->load(['orderItems.book']);
            
            return response()->json([
                'message' => 'Đơn hàng đã được tạo thành công',
                'order' => $order
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with(['orderItems.book', 'user'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'address' => 'sometimes|required|string',
            'state' => 'sometimes|required|string|in:pending,confirmed,shipping,delivered,cancelled',
        ]);
        
        $order = Order::findOrFail($id);
        $order->update($request->only(['address', 'state']));
        
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
}
