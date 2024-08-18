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
            $getMessage = $this->regenerateNotificationMessage($userNotification->id);
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

    public function sendNotification($data) {
        $notification = Notification::create([
            'recipientUserId' => $data['recipientUserId'],
            'senderUserId' => $data['senderUserId'],
            'type' => $data['type'],
            'notificationMessage' => $data['notificationMessage'],
            'isRead' => $data['isRead'],
            'typeValueID' => $data['typeValueID']
        ]);

        return $notification;
    }

    public function generateNotificationMessage($notificationData) {
        switch($notificationData['type']) {
            case 'requesting the item' :
            case 'returning the item' :
                //e.g Lester is requesting the item OGE
                $notificationMessage = $notificationData['firstName'].' is '.$notificationData['type'].' '.$notificationData['itemCode'].'.';
                break;
            case 'approve the request' :
            case 'close the request' :
            case 'decline the request' :
            case 'approve the return' :
                //e.g Lester approve the return of the requested item OGE
                $notificationMessage = $notificationData['firstName'].' '. $notificationData['type'].' of item '.$notificationData['itemCode'].'.';
                break;
            case 'sent a message' :
                //e.g Lester sent a message on the request item with Reference #1
                $notificationMessage = $notificationData['firstName'].' '. $notificationData['type'].' on the request item with Reference #00'.$notificationData['requestID'].'.';
                break;
            default: $notificationMessage = 'Invalid';
        }
        return $notificationMessage;
    }

    //approve the request typeValueID = request id
    //approve the return typeValueID = return id
    //sent a message typeValueID = request id
    //requesting the item typeValueID = request id
    //returning the item typeValueID = return id
    public function regenerateNotificationMessage($id) {
        $notification = Notification::find($id);
        switch($notification['type']) {
            case 'requesting the item':
            case 'returning the item' :
                //e.g Lester is requesting the item OGE
                $requests = Requests::find($notification['typeValueID']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['iditem']);
                $notificationMessage = $user['first_name'].' is '.$notification['type'].' '.$item['itemcode'].'.';
                break;
            case 'approve the request' :
            case 'close the request' :
            case 'decline the request' :
            case 'approve the return' :
                //e.g Lester approve the request of item OGE
                $requests = Requests::find($notification['typeValueID']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['iditem']);
                $notificationMessage = $user['first_name'].' '. $notification['type'].' of item '.$item['itemcode'].'.';
                break;
            case 'sent a message' :
                //e.g Lester sent a message on the request item with Reference #1
                $requests = Requests::find($notification['typeValueID']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['iditem']);
                $notificationMessage = $user['first_name'].' '. $notification['type'].' on the request item with Reference #00'.$requests['id'].'.';
                break;
            default: $notificationMessage = 'Invalid';
        }
        return $notificationMessage;

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
