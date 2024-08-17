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
            'idrequest' => 'required|string|exists:requests,id',
            'message' => 'required|string|max:255',
        ]);

        $requests = Requests::find($request->idrequest);
        $user = User::find(Auth::id());

        if($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages(),
            ],422);
        }
        else {
            if($requests->idrequester != Auth::id() && !$user->is_admin) {
                return response()->json([
                    'requestsid' => $requests->idrequester,
                    'error' => 'You are not allowed to send a message with this request.',
                ],422);
            }

            else {
                $notificationType = 'sent a message';
                $notificationMessage = $notificationController->generateNotificationMessage([
                    'firstName' => $user->first_name,
                    'type' => $notificationType,
                    'requestID' => $requests->id
                ]);

                //if sender is an admin
                if($user->is_admin) {
                    //for the other admin aside from the admin sender, if there are any
                    $isThereOtherAdmin = User::where('is_admin', true)->where('id', '!=', Auth::id())->get();
                    if($isThereOtherAdmin) {
                        foreach($isThereOtherAdmin as $otherAdmin) {
                            //loop each admin -> delete existing notif related to this request -> create
                            $isOtherAdminHasNotif = Notification::where('type', $notificationType)
                            ->where('typeValueID', $request->idrequest)
                            ->where('senderUserId', Auth::id())
                            ->where('recipientUserId', $otherAdmin->id);
                            if($isOtherAdminHasNotif) {
                                $isOtherAdminHasNotif->delete();
                            }

                            $notification = $notificationController->sendNotification([
                                'recipientUserId' => $otherAdmin->id,
                                'senderUserId' => Auth::id(),
                                'type' => $notificationType,
                                'notificationMessage' => $notificationMessage,
                                'isRead' => false,
                                'typeValueID' => $request->idrequest
                            ]);
                        }
                    }

                    //To the sender, delete existing notif -> create
                    $isThereNotif = Notification::where('type', $notificationType)
                    ->where('typeValueID', $request->idrequest)
                    ->where('senderUserId', Auth::id())
                    ->where('recipientUserId', $requests->idrequester);
                    if($isThereNotif) {
                        $isThereNotif->delete();
                    }
                    $notification = $notificationController->sendNotification([
                        'recipientUserId' => $requests->idrequester,
                        'senderUserId' => Auth::id(),
                        'type' => $notificationType,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueID' => $request->idrequest
                    ]);
                } else {
                    //If the sender is not an admin, the message will be sent to all admin
                    $allAdmin = User::where('is_admin', true)->get();
                    foreach($allAdmin as $admin) {
                        $isThereNotif = Notification::where('type', $notificationType)
                        ->where('typeValueID', $request->idrequest)
                        ->where('senderUserId', Auth::id())
                        ->where('recipientUserId', $admin->id);
                        if($isThereNotif) {
                            $isThereNotif->delete();
                        }

                        $notification = $notificationController->sendNotification([
                            'recipientUserId' => $admin->id,
                            'senderUserId' => Auth::id(),
                            'type' => $notificationType,
                            'notificationMessage' => $notificationMessage,
                            'isRead' => false,
                            'typeValueID' => $request->idrequest
                        ]);
                    }
                }

                //send message
                $sendmessage = RequestCommunication::create([
                    'idrequest' => $request->input('idrequest'),
                    'message' => $request->input('message'),
                    'idsender' => Auth::id(),
                ]);
                
                return response()->json([
                    'message' => 'Message sent successfully.',
                    'sendername' => $user->first_name,
                    'data' => $sendmessage,
                    'notification' => $notification
                ], 200);

            }
        }
    }

    public function show($id) {
        //add idrequester to reqcomm and validate if id is equal to Auth
        $getRequest = Requests::find($id);
        $user = User::find(Auth::id());
        if(!$getRequest) {
            return response()->json([
                'message' => 'Unable to find the request id.'
            ], 422);
        }
        else {
            $comms = RequestCommunication::where('idrequest', $id)
            ->join('users','users.id', '=', 'request_communications.idsender')
            ->select('*', 'request_communications.id as id',
            'request_communications.created_at as created_at',
            'request_communications.updated_at as updated_at'
            )->get();
            $checkAuth = false;
            foreach($comms as $comm) {
                if($comm->idsender == Auth::id()) {
                    $checkAuth = true;
                }
            }
    
            if(!$checkAuth && !$user->is_admin && $getRequest->idrequester != Auth::id()) {
                return response()->json([
                    'message' => 'You are not allowed to view the messages on this request.',
                ], 422);
            }
            else {
                $this->readUnreadMessage($id);
                return response()->json([
                    'statusrequest' => $getRequest->statusrequest,
                    'data' => $comms,
                ], 200);
            }
        }
    }

    public function readUnreadMessage($id) {
        $unreadMessages = RequestCommunication::where('idrequest', $id)
        ->where('idsender', '!=', Auth::id())
        ->where('isRead', false)->get();
        $checkAuth = false;
        $user = User::find(Auth::id());
        $getRequest = Requests::find($id);

        if($getRequest) {
            if(!$user->is_admin && $getRequest->idrequester != Auth::id()) {
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
