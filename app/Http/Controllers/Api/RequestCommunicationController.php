<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RequestCommunication;
use App\Models\Requests;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class RequestCommunicationController extends Controller
{
    public function store(Request $request) {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $validator = Validator::make($request->all(),[
            //validates if idrequest does exist on requests table -id
            'idRequest' => 'required|string|exists:requests,id',
            'message' => 'required|string|max:255',
        ]);

        $requests = Requests::find($request['idRequest']);
        $user = User::find(Auth::id());

        if($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages(),
            ],422);
        }
        else {
            if($requests['idRequester'] != Auth::id() && !$user['isAdmin']) {
                return response()->json([
                    'requestsid' => $requests['idRequester'],
                    'error' => 'You are not allowed to send a message with this request.',
                ],422);
            }

            else {
                $notificationType = 'sent a message';
                $notificationMessage = $notificationController->generateNotificationMessage([
                    'firstName' => $user['firstName'],
                    'type' => $notificationType,
                    'requestID' => $requests['id']
                ]);

                //if sender is an admin
                if($user['isAdmin']) {
                    //for the other admin aside from the admin sender, if there are any
                    $isThereOtherAdmin = User::where('isAdmin', true)->where('id', '!=', Auth::id())->get();
                    if($isThereOtherAdmin) {
                        foreach($isThereOtherAdmin as $otherAdmin) {
                            //loop each admin -> delete existing notif related to this request -> create
                            $isOtherAdminHasNotif = Notification::where('type', $notificationType)
                            ->where('typeValueId', $request['idRequest'])
                            ->where('senderUserId', Auth::id())
                            ->where('recipientUserId', $otherAdmin['id']);
                            if($isOtherAdminHasNotif) {
                                $isOtherAdminHasNotif->delete();
                            }

                            $notification = $notificationController->sendNotification([
                                'recipientUserId' => $otherAdmin['id'],
                                'senderUserId' => Auth::id(),
                                'type' => $notificationType,
                                'notificationMessage' => $notificationMessage,
                                'isRead' => false,
                                'typeValueId' => $request['idRequest']
                            ]);
                        }
                    }

                    //To the sender, delete existing notif -> create
                    $isThereNotif = Notification::where('type', $notificationType)
                    ->where('typeValueId', $request['idRequest'])
                    ->where('senderUserId', Auth::id())
                    ->where('recipientUserId', $requests['idRequester']);
                    if($isThereNotif) {
                        $isThereNotif->delete();
                    }
                    $notification = $notificationController->sendNotification([
                        'recipientUserId' => $requests['idRequester'],
                        'senderUserId' => Auth::id(),
                        'type' => $notificationType,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueId' => $request['idRequest']
                    ]);
                } else {
                    //If the sender is not an admin, the message will be sent to all admin
                    $allAdmin = User::where('isAdmin', true)->get();
                    foreach($allAdmin as $admin) {
                        $isThereNotif = Notification::where('type', $notificationType)
                        ->where('typeValueId', $request['idRequest'])
                        ->where('senderUserId', Auth::id())
                        ->where('recipientUserId', $admin['id']);
                        if($isThereNotif) {
                            $isThereNotif->delete();
                        }

                        $notification = $notificationController->sendNotification([
                            'recipientUserId' => $admin['id'],
                            'senderUserId' => Auth::id(),
                            'type' => $notificationType,
                            'notificationMessage' => $notificationMessage,
                            'isRead' => false,
                            'typeValueId' => $request['idRequest']
                        ]);
                    }
                }

                //send message
                $sendmessage = RequestCommunication::create([
                    'idRequest' => $request['idRequest'],
                    'message' => $request['message'],
                    'idSender' => Auth::id(),
                ]);
                
                return response()->json([
                    'message' => 'Message sent successfully.',
                    'sendername' => $user['firstName'],
                    'data' => $sendmessage,
                    'notification' => $notification
                ], 200);

            }
        }
    }

    public function show($id) {
        //add idRequester to reqcomm and validate if id is equal to Auth
        $getRequest = Requests::find($id);
        $user = User::find(Auth::id());
        if(!$getRequest) {
            return response()->json([
                'message' => 'Unable to find the request id.'
            ], 422);
        }
        else {
            $comms = RequestCommunication::where('idRequest', $id)
            ->join('users','users.id', '=', 'request_communications.idSender')
            ->select('*', 'request_communications.id as id',
            'request_communications.created_at as created_at',
            'request_communications.updated_at as updated_at'
            )->get();
            $checkAuth = false;
            foreach($comms as $comm) {
                if($comm['idSender'] == Auth::id()) {
                    $checkAuth = true;
                }
            }
    
            if(!$checkAuth && !$user['isAdmin'] && $getRequest['idRequester'] != Auth::id()) {
                return response()->json([
                    'message' => 'You are not allowed to view the messages on this request.',
                ], 422);
            }
            else {
                $this->readUnreadMessage($id);
                return response()->json([
                    'statusRequest' => $getRequest['statusRequest'],
                    'data' => $comms,
                ], 200);
            }
        }
    }

    public function readUnreadMessage($id) {
        $unreadMessages = RequestCommunication::where('idRequest', $id)
        ->where('idSender', '!=', Auth::id())
        ->where('isRead', false)->get();
        $checkAuth = false;
        $user = User::find(Auth::id());
        $getRequest = Requests::find($id);

        if($getRequest) {
            if(!$user['isAdmin'] && $getRequest['idRequester'] != Auth::id()) {
                return response()->json([
                    'message' => 'You are not allowed to view the messages on this request.',
                ], 422);
            }
            else {
                if($unreadMessages->count() > 0) {
                    foreach($unreadMessages as $unreadMessage) {
                        $unreadMessage->update([
                            'isRead' => true
                        ]);
                    }
                }
        
                return response()->json([
                    'message' => 'success'
                ], 200);
            }
        }
        else {
            return response()->json([
                'message' => 'Unable to find the request id.'
            ], 422);
        }
    }
}
