<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Cart;

use App\Models\CartItem;

use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Lấy giỏ hàng của user
    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cart->load('items.book');

        return response()->json($cart);
    }

    // Thêm sản phẩm vào giỏ
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        // Nếu sản phẩm đã có trong giỏ thì cộng thêm số lượng
        $item = $cart->items()->where('book_id', $request->book_id)->first();
        if ($item) {
            $item->update([
                'quantity' => $item->quantity + $request->quantity
            ]);
        } else {
            $cart->items()->create([
                'book_id' => $request->book_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json(['message' => 'Đã thêm vào giỏ hàng']);
    }

    // Cập nhật số lượng sản phẩm trong giỏ
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::findOrFail($id);
        if ($item->cart->user_id != Auth::id()) {
            return response()->json(['error' => 'Không có quyền'], 403);
        }

        $item->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Cập nhật số lượng thành công']);
    }

    // Xóa sản phẩm khỏi giỏ
    public function destroy($id)
    {
        $item = CartItem::findOrFail($id);
        if ($item->cart->user_id != Auth::id()) {
            return response()->json(['error' => 'Không có quyền'], 403);
        }

        $item->delete();
        return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng']);
    }

    // Xóa toàn bộ giỏ hàng
    public function clear()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cart->items()->delete();

        return response()->json(['message' => 'Đã xóa toàn bộ giỏ hàng']);
    }
}
