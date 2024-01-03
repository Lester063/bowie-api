<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use App\Models\Requests;
use App\Models\Notification;
use App\Models\User;
use App\Models\Returns;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function userNotificationIndex() {
        $getUserNotifications = Notification::where('recipientUserId', Auth::id())->latest()->get();
        $readNotification = Notification::where('recipientUserId', Auth::id())->where('isRead', true)->count();
        $unreadNotification = Notification::where('recipientUserId', Auth::id())->where('isRead', false)->count();

        foreach($getUserNotifications as $userNotification) {
            $getMessage = $this->generateNotificationMessage($userNotification->id);
            $userNotification->update([
                'notificationMessage' => $getMessage
            ]);
        }
        return response()->json([
            'message' => 'Success',
            'readnotification' => $readNotification,
            'unreadnotification' => $unreadNotification,
            'data' => $getUserNotifications,
        ], 200);

    }

    public function generateNotificationMessage($id) {
        $notification = Notification::find($id);
        if($notification->type === 'approve the request' || $notification->type === 'decline the request' || $notification->type === 'close the request') {
            $requests = Requests::find($notification->typeValueID);
            $user = User::find($notification->senderUserId);
            $item = Item::find($requests->iditem);
            $notificationmessage = $user->name.' '. $notification->type.' of item with code '.$item->itemcode.'.';
        }

        if($notification->type === 'approve the return') {
            $return = Returns::find($notification->typeValueID);
            $requests = Requests::find($return->idrequest);
            $user = User::find($notification->senderUserId);
            $item = Item::find($requests->iditem);
            $notificationmessage = $user->name.' '. $notification->type.' of the requested item with code '.$item->itemcode.'.';
        }

        return $notificationmessage;

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
