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
        $validator = Validator::make($request->all(),[
            'idrequest' => 'required|string|exists:requests,id', //validates if idrequest does exist on requests table -id
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
                $type = 'sent a message';
                $notificationMessage = $user->name.' '.$type.' on the request item with id '.$requests->id.'.';

                //if sender is an admin
                if($user->is_admin) {
                    //for the other admin aside from the sender, if there are any
                    $isThereOtherAdmin = User::where('is_admin', true)->where('id', '!=', Auth::id())->get();
                    if($isThereOtherAdmin) {
                        foreach($isThereOtherAdmin as $otherAdmin) {
                            $isOtherAdminHasNotif = Notification::where('type', $type)->where('typeValueID', $request->idrequest)
                            ->where('senderUserId', Auth::id())->where('recipientUserId', $otherAdmin->id);
                            if($isOtherAdminHasNotif) {
                                $isOtherAdminHasNotif->delete();
                            }
                            $notification = Notification::create([
                                'recipientUserId' => $otherAdmin->id,
                                'senderUserId' => Auth::id(),
                                'type' => $type,
                                'notificationMessage' => $notificationMessage,
                                'isRead' => false,
                                'typeValueID' => $request->idrequest
                            ]);
                        }
                    }

                    //for the sender who have requested the item
                    $isThereNotif = Notification::where('type', $type)->where('typeValueID', $request->idrequest)
                    ->where('senderUserId', Auth::id())->where('recipientUserId', $requests->idrequester);
                    if($isThereNotif) {
                        $isThereNotif->delete();
                    }
                    $notification = Notification::create([
                        'recipientUserId' => $requests->idrequester,
                        'senderUserId' => Auth::id(),
                        'type' => $type,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueID' => $request->idrequest
                    ]);

                } else {
                    //if the sender is not an admin, it will get all the admin and will loop the message so that the message will be sent to all admins.
                    $allAdmin = User::where('is_admin', true)->get();
                    foreach($allAdmin as $admin) {
                        $isThereNotif = Notification::where('type', $type)->where('typeValueID', $request->idrequest)
                        ->where('senderUserId', Auth::id())->where('recipientUserId', $admin->id);
                        if($isThereNotif) {
                            $isThereNotif->delete();
                        }
                        $notification = Notification::create([
                            'recipientUserId' => $admin->id,
                            'senderUserId' => Auth::id(),
                            'type' => $type,
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
            ->join('users','users.id', '=', 'request_communications.idsender')->select('*','request_communications.id as id')->get();
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
                return response()->json([
                    'statusrequest' => $getRequest->statusrequest,
                    'data' => $comms,
                ], 200);
            }
        }
    }
}
