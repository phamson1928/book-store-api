<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Payment;
class CleanUpUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-up-unpaid-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expireMinutes = 30; // thời gian cho phép user thanh toán
        $expireTime = Carbon::now()->subMinutes($expireMinutes);

        // Xóa Order chưa thanh toán
        $orders = Order::where('payment_status', 'Chưa thanh toán')
            ->where('created_at', '<', $expireTime)
            ->get();

        foreach ($orders as $order) {
            $this->info("Deleting Order #{$order->id}");

            // Xóa payment liên quan
            Payment::where('order_id', $order->id)->delete();

            // Xóa order items
            $order->orderItems()->delete();

            // Xóa chính order
            $order->delete();
        }

        // Xóa payment pending không gắn order (nếu có)
        Payment::where('status', 'pending')
            ->where('created_at', '<', $expireTime)
            ->delete();

        $this->info('Dọn dẹp xong.');
    }
}
