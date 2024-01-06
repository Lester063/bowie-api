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
        $requests = Requests::join('items', 'items.id', '=', 'requests.iditem')->join('users','users.id','=','requests.idrequester')
        ->select('*','requests.id as id')->orderBy('requests.created_at','desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $requests,
        ], 200);
        
    }

    public function indexUser()
    {
        //need to check, unable to get some request because the item is deleted, need to add is_deleted column in items table
        $requests = Requests::where('idrequester', Auth::id())
        ->join('items', 'items.id', '=', 'requests.iditem')
        ->join('users','users.id','=','requests.idrequester')
        ->select('*','requests.id as id')->orderBy('requests.created_at','desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $requests,
        ], 200);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'idrequester' => 'required|string|max:8|exists:users,id',
            'iditem' => 'required|string|max:8|exists:items,id',
            'statusrequest' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        } else {
            $getItem = Item::find($request->iditem);
            $isItemAvailable = $getItem->is_available;

            $userRequestPendingCount = Requests::where('iditem', $request->iditem)->where('idrequester', $request->idrequester)->where('statusrequest', 'Pending')->count();
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
                    'idrequester' => $request->input('idrequester'),
                    'iditem' => $request->input('iditem'),
                    'statusrequest' => $request->input('statusrequest'),
                    'isreturnsent' => false,
                ]);
                //send a notification to all admin
                $user = User::find(Auth::id());
                $type = 'requesting the item';
                $notificationMessage = $user->name.' is '.$type.' with item code '.$getItem->itemcode.'.';
                $allAdmin = User::where('is_admin', true)->get();
                foreach($allAdmin as $admin) {
                    $notification = Notification::create([
                        'recipientUserId' => $admin->id,
                        'senderUserId' => Auth::id(),
                        'type' => $type,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueID' => $requestdata->id
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
        $requests = Requests::find($id);
        $allrequests = Requests::where('iditem', $requests->iditem)->where('statusrequest', 'Pending')->get();
        $item = Item::find($requests->iditem);

        if(!$requests) {
            return response()->json([
                'message' => 'Unable to find the request.'
            ],422);
        }
        else if($requests->statusrequest === 'Closed') {
            return response()->json([
                'message' => 'Unable to make any action.'
            ],422);
        }
        else if($item->is_deleted) {
            return response()->json([
                'message' => 'Item is deleted.'
            ],422);
        }
        else {
            $item = Item::find($requests->iditem);
            if(!$item->is_available) {
                return response()->json([
                    'message' => 'Item is not available at the moment.'
                ],422);
            }
            else {

                $approver = User::find(Auth::id());

                if($request->action==='Approving') {
                    foreach($allrequests as $singlerequest){
                        if($singlerequest->id !== $requests->id) {
                            $singlerequest->update([
                                'statusrequest' => 'Closed'
                            ]);
    
                            RequestCommunication::create([
                                'idrequest' => $singlerequest->id,
                                'message' => 'The item you have requested has been processed to other User. Therefore, this request will be closed, thank you.',
                                'idsender' => Auth::id(),
                            ]);

                            //unable to make a real time notif because we do not return a response each loop
                            $type = 'close the request';
                            $notificationMessage = $approver->name.' '.$type.' of the item with code '.$item->itemcode;
                            $notification = Notification::create([
                                'recipientUserId' => $singlerequest->idrequester,
                                'senderUserId' => Auth::id(),
                                'type' => $type,
                                'notificationMessage' => $notificationMessage,
                                'isRead' => false,
                                'typeValueID' => $id
                            ]);

                        }
                    }

                    $requests->update([
                        'statusrequest' => 'Approved'
                    ]);

                    RequestCommunication::create([
                        'idrequest' => $id,
                        'message' => 'Your request for this item has been approved.',
                        'idsender' => Auth::id(),
                    ]);
                    $type = 'approve the request';
                    $notificationMessage = $approver->name.' '.$type.' of the item with code '.$item->itemcode;

                    $notification = Notification::create([
                        'recipientUserId' => $requests->idrequester,
                        'senderUserId' => Auth::id(),
                        'type' => $type,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueID' => $id
                    ]);

                    $item->update([
                        'is_available' => false
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
                        'statusrequest' => 'Declined'
                    ]);

                    $type = 'decline the request';
                    $notificationMessage = $approver->name.' '.$type.' of the item with code '.$item->itemcode;

                    $notification = Notification::create([
                        'recipientUserId' => $requests->idrequester,
                        'senderUserId' => Auth::id(),
                        'type' => $type,
                        'notificationMessage' => $notificationMessage,
                        'isRead' => false,
                        'typeValueID' => $id
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
                        'statusrequest' => 'Closed'
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
