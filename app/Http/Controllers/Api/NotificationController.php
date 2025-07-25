<?php

namespace App\Http\Controllers\Api;

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
            $getMessage = $this->regenerateNotificationMessage($userNotification['id']);
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
            'typeValueId' => $data['typeValueId']
        ]);

        return $notification;
    }

    public function generateNotificationMessage($notificationData) {
        switch($notificationData['type']) {
            case 'requesting the item' :
            case 'returning the item' :
                //e.g Lester is requesting the item OGE
                $notificationMessage = $notificationData['firstName'].' is '
                .$notificationData['type'].' '.$notificationData['itemCode'].'.';
                break;
            case 'approve the request' :
            case 'close the request' :
            case 'decline the request' :
            case 'approve the return' :
                //e.g Lester approve the return of the requested item OGE
                $notificationMessage = $notificationData['firstName'].' '
                . $notificationData['type'].' of item '.$notificationData['itemCode'].'.';
                break;
            case 'sent a message' :
                //e.g Lester sent a message on the request item with Reference #1
                $notificationMessage = $notificationData['firstName'].' '. $notificationData['type']
                .' on the request item with Reference #00'.$notificationData['requestID'].'.';
                break;
            default: $notificationMessage = 'Invalid';
        }
        return $notificationMessage;
    }

    //approve the request typeValueId = request id
    //approve the return typeValueId = return id
    //sent a message typeValueId = request id
    //requesting the item typeValueId = request id
    //returning the item typeValueId = return id
    public function regenerateNotificationMessage($id) {
        $notification = Notification::find($id);
        switch($notification['type']) {
            case 'requesting the item':
                //e.g Lester is requesting the item OGE
                $requests = Requests::find($notification['typeValueId']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['idItem']);
                $notificationMessage = $user['firstName'].' is '.$notification['type']
                .' '.$item['itemCode'].'.';
                break;
            case 'approve the request' :
            case 'close the request' :
            case 'decline the request' :
                //e.g Lester approve the request of item OGE
                $requests = Requests::find($notification['typeValueId']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['idItem']);
                $notificationMessage = $user['firstName'].' '. $notification['type']
                .' of item '.$item['itemCode'].'.';
                break;
            case 'returning the item' :
                $return = Returns::find($notification['typeValueId']);
                $requests = Requests::find($return['idRequest']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['idItem']);
                $notificationMessage = $user['firstName'].' is '.$notification['type'].' '.$item['itemCode'].'.';
                break;
            case 'approve the return' :
                $return = Returns::find($notification['typeValueId']);
                $requests = Requests::find($return['idRequest']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['idItem']);
                $notificationMessage = $user['firstName'].' '.$notification['type'].' '.$item['itemCode'].'.';
                break;
            case 'sent a message' :
                //e.g Lester sent a message on the request item with Reference #1
                $requests = Requests::find($notification['typeValueId']);
                $user = User::find($notification['senderUserId']);
                $item = Item::find($requests['idItem']);
                $notificationMessage = $user['firstName'].' '. $notification['type']
                .' on the request item with Reference #00'.$requests['id'].'.';
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
