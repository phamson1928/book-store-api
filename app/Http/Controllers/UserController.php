<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function stats(){
        $onlineUsers = User::where('last_seen', '>=', now()->subMinutes(5))->count();
        $adminUsers = User::where('role','admin')->count();
        $userUsers = User::where('role','user')->count();
        $newUsers = User::whereDate('created_at',today())->count(); 

        return response()->json([
            'onlineUsers' => $onlineUsers,
            'adminUsers' => $adminUsers,
            'userUsers' => $userUsers,
            'newUsers' => $newUsers
        ]);
    }
    
    public function index(){
        return response()->json(User::all());
    }

    public function delete($id){
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
