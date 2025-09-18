<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function showForAdmin()
    {
        $data = Notification::where('type', 'admin')->select('id','message','created_at','updated_at')->orderBy('created_at', 'desc')->get();
        return response()->json($data);
    }

    public function storeByAdmin(Request $request)
    {
        $data = Notification::create([
            'user_id' => $request->user_id,
            'message' => $request->message,
            'type' => 'admin',
        ]);
        return response()->json([
        'status' => 'success',
        'message' => 'Thông báo đã được tạo thành công',
        'data' => $data
    ], 201);

    }

    public function updateByAdmin(Request $request, $id)
    {
        $data = Notification::where('id', $id)->where('type', 'admin')->update([
            'message' => $request->message,
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Thông báo đã được cập nhật thành công',
            'data' => $data
        ], 200);
    }

    public function showForUser()
{
    $data = Notification::where('is_read', false)
        ->where(function ($q) {
            $q->where('user_id', Auth::id())
              ->orWhereNull('user_id');
        })
        ->select('message','type','created_at')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($data);
}

    public function destroyByAdmin($id)
    {
        Notification::where('id', $id)->where('type','admin')->delete();
        return response()->json(['message' => 'Đã xóa thành công']);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc'
        ]);
    }

}
