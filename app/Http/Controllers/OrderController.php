<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
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

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        
        try {
            // Tính tổng số lượng và tổng tiền
            $totalQuantity = 0;
            $totalCost = 0;
            
            foreach ($data['items'] as $item) {
                $book = Book::findOrFail($item['book_id']);
                $price = $book->discount_price ?? $book->price;
                $totalQuantity += $item['quantity'];
                $totalCost += $price * $item['quantity'];
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'address' => $data['address'],
                'quantity' => $totalQuantity,
                'total_cost' => $totalCost,
                'state' => 'pending',
            ]);

            // Tạo order items
            foreach ($data['items'] as $item) {
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
            
            // Load relationships for response
            $order->load(['orderItems.book', 'user']);
            
            return response()->json($order, 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to create order'], 500);
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
