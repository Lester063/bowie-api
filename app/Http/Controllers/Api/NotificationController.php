<?php

namespace App\Http\Controllers\api;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function userNotificationIndex() {
        $getUserNotifications = Notification::where('recipientUserId', Auth::id())->get();

        return response()->json([
            'message' => 'Success',
            'data' => $getUserNotifications,
        ], 200);
    }

    public function readUnreadUserNotification() {
        $getUnreadUserNotification = Notification::where('recipientUserId', Auth::id())->where('isRead', false)->get();

        if($getUnreadUserNotification->count() > 0) {
            foreach($getUnreadUserNotification as $unreadNotification) {
                $unreadNotification->update([
                    'isRead' => true,
                ]);
            }
        }

        return response()->json([
            'message' => 'Success',
        ], 200);
    }
}
