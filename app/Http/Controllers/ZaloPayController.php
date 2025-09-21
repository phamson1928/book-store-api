<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\ZaloPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZaloPayController extends Controller
{
    protected $zaloPayService;

    public function __construct(ZaloPayService $zaloPayService)
    {
        $this->zaloPayService = $zaloPayService;
    }

    /**
     * Tạo đơn hàng thanh toán ZaloPay
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1000' // Tối thiểu 10,000 VND
        ]);

        $orderId = $request->order_id;
        $amount = $request->amount;

        // Kiểm tra quyền truy cập đơn hàng
        $order = Order::findOrFail($orderId);
        if (Auth::id() !== $order->user_id) {
            return response()->json(['error' => 'Không có quyền truy cập đơn hàng này'], 403);
        }

        // Kiểm tra đơn hàng đã có thanh toán thành công chưa
        if ($order->payment_status === 'Đã thanh toán') {
            return response()->json(['error' => 'Đơn hàng đã được thanh toán'], 400);
        }

        try {
            DB::beginTransaction();

            // Tạo payment record
            $payment = Payment::create([
                'order_id' => $orderId,
                'payment_method' => 'zalopay',
                'app_trans_id' => '', // Sẽ được cập nhật sau
                'amount' => $amount,
                'description' => "Thanh toán đơn hàng #{$orderId}",
                'status' => 'pending'
            ]);

            // Tạo đơn hàng trên ZaloPay
            $result = $this->zaloPayService->createOrder(
                $orderId,
                $amount,
                "Thanh toán đơn hàng #{$orderId}"
            );

            if ($result['success']) {
                // Cập nhật payment với thông tin từ ZaloPay
                $payment->update([
                    'app_trans_id' => $result['app_trans_id'],
                    'zalopay_trans_id' => $result['zalopay_trans_id'],
                    'zalopay_response' => $result['data']
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Tạo đơn hàng thanh toán thành công',
                    'payment' => $payment,
                    'order_url' => $result['order_url'],
                    'app_trans_id' => $result['app_trans_id']
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'error' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ZaloPay Create Payment Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Có lỗi xảy ra khi tạo thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái thanh toán
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'app_trans_id' => 'required|string'
        ]);

        $appTransId = $request->app_trans_id;

        // Tìm payment record
        $payment = Payment::where('app_trans_id', $appTransId)->first();
        if (!$payment) {
            return response()->json(['error' => 'Không tìm thấy thanh toán'], 404);
        }

        // Kiểm tra quyền truy cập
        if (Auth::id() !== $payment->order->user_id) {
            return response()->json(['error' => 'Không có quyền truy cập'], 403);
        }

        // Kiểm tra trạng thái trên ZaloPay
        $result = $this->zaloPayService->getOrderStatus($appTransId);

        if ($result['success']) {
            $status = $result['status'];
            
            // Cập nhật trạng thái payment
            if ($status == 1) { // Thành công
                $payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                    'zalopay_response' => $result['data']
                ]);

                // Cập nhật trạng thái đơn hàng
                $payment->order->update([
                    'payment_status' => 'Đã thanh toán'
                ]);

                // Xóa giỏ hàng khi thanh toán thành công
                $cart = \App\Models\Cart::where('user_id', $payment->order->user_id)->first();
                if ($cart) {
                    $cart->items()->delete();
                }

            } elseif ($status == -1) { // Thất bại
                $payment->update([
                    'status' => 'failed',
                    'zalopay_response' => $result['data']
                ]);
            }

            return response()->json([
                'payment' => $payment->fresh(),
                'status' => $status,
                'message' => $this->getStatusMessage($status)
            ]);
        }

        return response()->json([
            'error' => $result['message']
        ], 400);
    }

    /**
     * Webhook callback từ ZaloPay
     */
    public function callback(Request $request)
    {
        $data = $request->all();
        
        Log::info('ZaloPay Callback Received', $data);

        $result = $this->zaloPayService->handleCallback($data);

        if (!$result['success']) {
            return response()->json([
                'return_code' => -1,
                'return_message' => $result['message']
            ], 400);
        }

        $appTransId = $result['app_trans_id'];
        $status = $result['status'];

        try {
            DB::beginTransaction();

            // Tìm payment record
            $payment = Payment::where('app_trans_id', $appTransId)->first();
            if (!$payment) {
                Log::warning('ZaloPay Callback: Payment not found', ['app_trans_id' => $appTransId]);
                DB::rollBack();
                return response()->json([
                    'return_code' => -1,
                    'return_message' => 'Payment not found'
                ], 404);
            }

            // Cập nhật trạng thái payment
            if ($status == 1) { // Thành công
                $payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                    'zalopay_response' => $result['data']
                ]);

                // Cập nhật trạng thái đơn hàng
                $payment->order->update([
                    'payment_status' => 'Đã thanh toán'
                ]);

                // Xóa giỏ hàng khi thanh toán thành công
                $cart = \App\Models\Cart::where('user_id', $payment->order->user_id)->first();
                if ($cart) {
                    $cart->items()->delete();
                    Log::info('Cart cleared after successful payment', [
                        'user_id' => $payment->order->user_id,
                        'order_id' => $payment->order_id
                    ]);
                }

                Log::info('ZaloPay Payment Success', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id
                ]);

            } elseif ($status == -1) { // Thất bại
                $payment->update([
                    'status' => 'failed',
                    'zalopay_response' => $result['data']
                ]);

                Log::info('ZaloPay Payment Failed', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id
                ]);
            }

            DB::commit();

            return response()->json([
                'return_code' => 1,
                'return_message' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ZaloPay Callback Error', [
                'app_trans_id' => $appTransId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'return_code' => -1,
                'return_message' => 'Internal error'
            ], 500);
        }
    }

    /**
     * Lấy danh sách thanh toán của user
     */
    public function getUserPayments()
    {
        $userId = Auth::id();
        
        $payments = Payment::with(['order.orderItems.book'])
            ->whereHas('order', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }

    /**
     * Lấy chi tiết thanh toán
     */
    public function getPaymentDetails($id)
    {
        $payment = Payment::with(['order.orderItems.book', 'order.user'])
            ->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (Auth::id() !== $payment->order->user_id) {
            return response()->json(['error' => 'Không có quyền truy cập'], 403);
        }

        return response()->json($payment);
    }

    /**
     * Hoàn tiền
     */
    public function refund(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255'
        ]);

        $payment = Payment::findOrFail($request->payment_id);

        // Kiểm tra quyền truy cập (chỉ admin hoặc chủ đơn hàng)
        if (Auth::id() !== $payment->order->user_id && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Không có quyền truy cập'], 403);
        }

        // Kiểm tra trạng thái thanh toán
        if (!$payment->isSuccess()) {
            return response()->json(['error' => 'Chỉ có thể hoàn tiền cho thanh toán thành công'], 400);
        }

        if (!$payment->zalopay_trans_id) {
            return response()->json(['error' => 'Không có thông tin giao dịch ZaloPay'], 400);
        }

        try {
            $result = $this->zaloPayService->refund(
                $payment->zalopay_trans_id,
                $request->amount,
                $request->description ?? 'Hoàn tiền đơn hàng #' . $payment->order_id
            );

            if ($result['success']) {
                return response()->json([
                    'message' => 'Yêu cầu hoàn tiền đã được gửi',
                    'data' => $result['data']
                ]);
            }

            return response()->json([
                'error' => $result['message']
            ], 400);

        } catch (\Exception $e) {
            Log::error('ZaloPay Refund Error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Có lỗi xảy ra khi hoàn tiền: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông báo trạng thái
     */
    private function getStatusMessage($status)
    {
        switch ($status) {
            case 1:
                return 'Thanh toán thành công';
            case -1:
                return 'Thanh toán thất bại';
            case 0:
                return 'Đang chờ thanh toán';
            default:
                return 'Trạng thái không xác định';
        }
    }
}
