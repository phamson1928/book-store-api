<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {

    }

    public function store(Request $request)
    {
        $data = Notification::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);
        return response()->json($data);
    }

    public function showForUser()
    {
        $data = Notification::where('id', Auth::id())->where('is_read', false)->select('message')->get();
        return response()->json($data);
    }


    public function update(Request $request, Notification $notification)
    {
        //
    }

    public function destroy(Notification $notification)
    {
        //
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
