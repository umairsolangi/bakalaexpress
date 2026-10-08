<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 


class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->get();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(Request $request)
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead(); 

        return redirect()->back(); 
    }
    public function redirectToService($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $serviceUrl = $notification->data['service_url']; 

        return redirect($serviceUrl);
    }
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return redirect()->back();
    }

  
}
