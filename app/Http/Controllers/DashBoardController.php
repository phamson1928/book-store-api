<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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
            ->where('state','Đã giao')->sum('total_cost');

        // Doanh thu tháng trước
        $revenueLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('state','Đã giao')->sum('total_cost');

        // % thay đổi doanh thu
        $revenueChange = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : null;

        // Tổng sách
        $totalBooks = Book::count();

        // Người dùng mới tháng này
        $newUsersThisMonth = User::whereBetween('created_at', [$startOfMonth, $now])->count();
        $newUsersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $userChange = $newUsersLastMonth > 0
            ? (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100
            : null;

        // Đơn hàng hôm nay
        $ordersToday = Order::whereDate('created_at', $now->toDateString())->count();
        // % đơn so với tháng trước
        $orderThisMonth = Order::whereBetween('created_at', [$startOfMonth, $now])->count();
        $orderLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $orderChange = $orderLastMonth > 0
            ? (($orderThisMonth - $orderLastMonth) / $orderLastMonth) * 100
            : null;
        
        //Doanh thu từng tháng
        $revenueByMonth = DB::table('orders')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_cost) as revenue')
            )
            ->where('state', 'Đã giao')
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();


        return response()->json([
        'revenueThisMonth' => $revenueThisMonth,
        'revenueChange' => $revenueChange !== null ? round($revenueChange, 2) : null,

        'booksTotal' => $totalBooks,

        'ordersToday' => $ordersToday,
        'orderChange' => $orderChange !== null ? round($orderChange, 2) : null,

        'newUsers' => $newUsersThisMonth,
        'userChange' => $userChange !== null ? round($userChange, 2) : null,

        'revenueByMonth' => $revenueByMonth,
        ]);
    }
}
