<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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
            ->where('state','Đã giao')->sum('total_price');

        // Doanh thu tháng trước
        $revenueLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('state','Đã giao')->sum('total_price');

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

        //Doanh thu từng tháng
        $revenueByMonth = DB::table('orders')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('state', 'Đã giao')
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();


        return response()->json([
            'revenueThisMonth' => [
                'value' => $revenueThisMonth,
                'change' => round($revenueChange, 2)
            ],
            'booksTotal' => [
                'value' => $totalBooks,
                'change' => null
            ],
            'ordersToday' => [
                'value' => $ordersToday,
                'change' => null
            ],
            'newUsers' => [
                'value' => $newUsersThisMonth,
                'change' => round($userChange, 2)
            ],
            'revenueByMonth' =>[
                'value' => $revenueByMonth,
                'change' => null
            ],
        ]);
    }
}
