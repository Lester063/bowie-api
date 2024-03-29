<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use App\Models\Requests;
use App\Models\Notification;
use App\Models\User;
use App\Models\Returns;
use App\Models\RequestCommunication;
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

    //approve the request typeValueID = request id
    //approve the return typeValueID = return id
    //sent a message typeValueID = request id
    //requesting the item typeValueID = request id
    //returning the item typeValueID = return id
    public function generateNotificationMessage($id) {
        $notification = Notification::find($id);
        if($notification->type === 'approve the request' || $notification->type === 'decline the request' || $notification->type === 'close the request') {
            $requests = Requests::find($notification->typeValueID);
            $user = User::find($notification->senderUserId);
            $item = Item::find($requests->iditem);
            $notificationmessage = $user->first_name.' '. $notification->type.' of item with code '.$item->itemcode.'.';
        }

        if($notification->type === 'approve the return') {
            $return = Returns::find($notification->typeValueID);
            $requests = Requests::find($return->idrequest);
            $user = User::find($notification->senderUserId);
            $item = Item::find($requests->iditem);
            $notificationmessage = $user->first_name.' '. $notification->type.' of the requested item with code '.$item->itemcode.'.';
        }

        if($notification->type === 'sent a message') {
            $requests = Requests::find($notification->typeValueID);
            $user = User::find($notification->senderUserId);
            $item = Item::find($requests->iditem);
            $notificationmessage = $user->first_name.' '. $notification->type.' on the request item with id '.$requests->id.'.';
        }

        if($notification->type === 'requesting the item') {
            $requests = Requests::find($notification->typeValueID);
            $user = User::find($notification->senderUserId);
            $item = Item::find($requests->iditem);
            $notificationmessage = $user->first_name.' is '.$notification->type.' with item code '.$item->itemcode.'.';
        }

        if($notification->type === 'returning the item') {
            $return = Returns::find($notification->typeValueID);
            $requests = Requests::find($return->idrequest);
            $user = User::find($notification->senderUserId);
            $item = Item::find($requests->iditem);
            $notificationmessage = $user->first_name.' is '.$notification->type.' with item code '.$item->itemcode.'and return id '.$notification->typeValueID.'.';
        }

        return $notificationmessage;

    }

    public function readUnreadUserNotification() {
        $getUnreadUserNotification = Notification::where('recipientUserId', Auth::id())
        ->where('isRead', false)->get();

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
