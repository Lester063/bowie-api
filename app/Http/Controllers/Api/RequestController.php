<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use App\Models\Requests;
use App\Models\RequestCommunication;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexAdmin()
    {
        $requests = Requests::join('items', 'items.id', '=', 'requests.idItem')
        ->join('users','users.id','=','requests.idRequester')
        ->select('*','requests.id as id')->orderBy('requests.created_at','desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $requests,
        ], 200);
        
    }

    public function indexUser()
    {
        $requests = Requests::where('idRequester', Auth::id())
        ->join('items', 'items.id', '=', 'requests.idItem')
        ->join('users','users.id','=','requests.idRequester')
        ->select('*','requests.id as id')->orderBy('requests.created_at','desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $requests,
        ], 200);
    }

    public function viewRequest($id) {
        $requests = Requests::where('requests.id', $id)
        ->join('items', 'items.id', '=', 'requests.idItem')
        ->join('users','users.id','=','requests.idRequester')
        ->select('*','requests.id as id')->orderBy('requests.created_at','desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $requests,
        ], 200);
    }
    
    public function store(Request $request)
    {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

        $validator = Validator::make($request->all(),[
            'idRequester' => 'required|string|max:8|exists:users,id',
            'idItem' => 'required|string|max:8|exists:items,id',
            'statusRequest' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        } else {
            $getItem = Item::find($request['idItem']);
            $isItemAvailable = $getItem['isAvailable'];

            $userRequestPendingCount = Requests::where('idItem', $request['idItem'])
            ->where('idRequester', $request['idRequester'])
            ->where('statusRequest', 'Pending')->count();
            if(!$isItemAvailable) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Item is not available.',
                ], 422);
            }
            else if($userRequestPendingCount > 0){ //may need to add other statuses like 'Being Process'
                return response()->json([
                    'status' => 422,
                    'message' => 'You have already requested this item.',
                ], 400);
            }
            else {
                $requestdata = Requests::create([
                    'idRequester' => $request['idRequester'],
                    'idItem' => $request['idItem'],
                    'statusRequest' => $request['statusRequest'],
                    'isReturnSent' => false,
                ]);
                //send a notification to all admin
                $user = User::find(Auth::id());
                //e.g Lester is requesting the item OGE
                $notificationType = 'requesting the item';
                $notificationMessage = $notificationController->generateNotificationMessage([
                    'firstName' => $user['firstName'],
                    'type' => $notificationType,
                    'itemCode' => $getItem['itemCode']
                ]);
                
                $allAdmin = User::where('isAdmin', true)->get();
                foreach($allAdmin as $admin) {
                    $notification = $notificationController->sendNotification([
                        'recipientUserId' => $admin['id'],
                        'senderUserId' => Auth::id(),
                        'type' => $notificationType,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueId' => $requestdata['id']
                    ]);
                }
    
                return response()->json([
                    'message' => 'Request sent successfully.',
                    'data' => $requestdata,
                    'notification' => $notification,
                ], 200);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function actionRequest(Request $request, string $id)
    {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

        $requests = Requests::find($id);
        $allrequests = Requests::where('idItem', $requests['idItem'])->where('statusRequest', 'Pending')->get();
        $item = Item::find($requests->idItem);

        if(!$requests) {
            return response()->json([
                'message' => 'Unable to find the request.'
            ],422);
        }
        else if($requests['statusRequest'] === 'Closed') {
            return response()->json([
                'message' => 'Unable to make any action.'
            ],422);
        }
        else if($item['isDeleted']) {
            return response()->json([
                'message' => 'Item is deleted.'
            ],422);
        }
        else {
            $item = Item::find($requests['idItem']);
            if(!$item['isAvailable']) {
                return response()->json([
                    'message' => 'Item is not available at the moment.'
                ],422);
            }
            else {

                $approver = User::find(Auth::id());

                if($request['action']==='Approving') {
                    foreach($allrequests as $singlerequest){
                        if($singlerequest['id'] !== $requests['id']) {
                            $singlerequest->update([
                                'statusRequest' => 'Closed'
                            ]);
    
                            RequestCommunication::create([
                                'idRequest' => $singlerequest['id'],
                                'message' => 'The item you have requested has been processed to other User. 
                                 Therefore, this request will be closed, thank you.',
                                'idSender' => Auth::id(),
                            ]);

                            //unable to make a real time notif because we do not return a response each loop
                            //e.g Lester close the request of item OGE
                            $notificationType = 'close the request';
                            $notificationMessage = $notificationController->generateNotificationMessage([
                                'firstName' => $approver['firstName'],
                                'type' => $notificationType,
                                'itemCode' => $item['itemCode']
                            ]);

                            $notification = $notificationController->sendNotification([
                                'recipientUserId' => $singlerequest['idRequester'],
                                'senderUserId' => Auth::id(),
                                'type' => $notificationType,
                                'notificationMessage' => $notificationMessage,
                                'isRead' => false,
                                'typeValueId' => $id
                            ]);

                        }
                    }

                    $requests->update([
                        'statusRequest' => 'Approved'
                    ]);

                    RequestCommunication::create([
                        'idRequest' => $id,
                        'message' => 'Your request for this item has been approved.',
                        'idSender' => Auth::id(),
                    ]);
                    //e.g Lester approve the request of item OGE
                    $notificationType = 'approve the request';
                    $notificationMessage = $notificationController->generateNotificationMessage([
                        'firstName' => $approver['firstName'],
                        'type' => $notificationType,
                        'itemCode' => $item['itemCode']
                    ]);
                    $notification = $notificationController->sendNotification([
                        'recipientUserId' => $requests['idRequester'],
                        'senderUserId' => Auth::id(),
                        'type' => $notificationType,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueId' => $id
                    ]);

                    $item->update([
                        'isAvailable' => false
                    ]);

                    $requestnewdata = Requests::find($id);
                    return response()->json([
                        'message' => 'Request was approved.',
                        'data' => $requestnewdata,
                        'notification' => $notification
                    ], 200);
                }
                else if($request->action==='Declining') {
                    $requests->update([
                        'statusRequest' => 'Declined'
                    ]);

                    //e.g Lester decline the request of item OGE
                    $notificationType = 'decline the request';
                    $notificationMessage = $notificationController->generateNotificationMessage([
                        'firstName' => $approver['firstName'],
                        'type' => $notificationType,
                        'itemCode' => $item['itemCode']
                    ]);
                    $notification = $notificationController->sendNotification([
                        'recipientUserId' => $requests['idRequester'],
                        'senderUserId' => Auth::id(),
                        'type' => $notificationType,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueId' => $id
                    ]);

                    $requestnewdata = Requests::find($id);
                    return response()->json([
                        'message' => 'Request was declined.',
                        'data' => $requestnewdata,
                        'notification' => $notification
                    ], 200);
                }
                else if($request->action==='Closing') {
                    $requests->update([
                        'statusRequest' => 'Closed'
                    ]);

                }
                else {
                    return response()->json([
                        'message' => 'Unidentified action.'
                    ],422);
                }
            }
        }
    }

}
