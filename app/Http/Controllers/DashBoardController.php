<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;
use App\Models\User;
use App\Models\Book;

class DashBoardController extends Controller
{
    public function stats()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Doanh thu tháng này
        $revenueThisMonth = Order::whereBetween('created_at', [$startOfMonth, $now])
            ->sum('total_price');

        // Doanh thu tháng trước
        $revenueLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_price');

        // % thay đổi doanh thu
        $revenueChange = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : 100;

        // Tổng sách
        $totalBooks = Book::count();

        // Đơn hàng hôm nay
        $ordersToday = Order::whereDate('created_at', $now->toDateString())->count();

        // Người dùng mới tháng này
        $newUsersThisMonth = User::whereBetween('created_at', [$startOfMonth, $now])->count();
        $newUsersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $userChange = $newUsersLastMonth > 0
            ? (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100
            : 100;

        return response()->json([
            'revenue' => [
                'value' => $revenueThisMonth,
                'change' => round($revenueChange, 2)
            ],
            'books' => [
                'value' => $totalBooks,
                'change' => null
            ],
            'orders_today' => [
                'value' => $ordersToday,
                'change' => null
            ],
            'new_users' => [
                'value' => $newUsersThisMonth,
                'change' => round($userChange, 2)
            ]
        ]);
    }
}
