<?php

namespace App\Http\Controllers\api;

use App\Models\Returns;
use App\Models\Requests;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexAdmin()
    {
        //$returns = Returns::all();
        $returns = Returns::join('requests','requests.id','=','returns.idRequest')
        ->join('items','items.id','=','requests.idItem')
        ->join('users', 'users.id','=','returns.idReturner')
        ->select('*','returns.id as id')->orderBy('returns.created_at','desc')->get();

        return response()->json([
            'data' => $returns
        ], 200);
    }

    public function indexUser()
    {
        //$returns = Returns::all();
        $returns = Returns::where('idReturner', Auth::id())
        ->join('requests','requests.id','=','returns.idRequest')
        ->join('items','items.id','=','requests.idItem')
        ->join('users', 'users.id','=','returns.idReturner')
        ->select('*','returns.id as id')
        ->orderBy('returns.created_at','desc')->get();

        return response()->json([
            'data' => $returns
        ], 200);
    }

    public function viewReturn($id) {
        $returns = Returns::where('returns.id', $id)
        ->join('requests','requests.id','=','returns.idRequest')
        ->join('items','items.id','=','requests.idItem')
        ->join('users', 'users.id','=','returns.idReturner')
        ->select('*','returns.id as id')
        ->orderBy('returns.created_at','desc')->get();

        return response()->json([
            'data' => $returns
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function returnItem(Request $request)
    {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

        $getrequest = Requests::find($request['idRequest']);
        // var $hasReturnPending = false;

        $hasPendingReturn = Returns::where('idRequest', $request['idRequest'])
        ->where('isApprove', 0)->count();
        // foreach($getrequest)

        if(!$getrequest) {
            return response()->json([
                'message' => 'Unable to find the request.',
            ], 422);
        } 
        else if($getrequest['idRequester'] != Auth::id()) {
            return response()->json([
                'message' => 'You cannot return this item.',
            ], 422);
        }
        else if($hasPendingReturn >= 1) {
            return response()->json([
                'message' => 'There is already a pending return for this request.',
            ], 422);
        }
        else {
            $return = Returns::create([
                'idRequest' => $request['idRequest'],
                'idReturner' => Auth::id(),
                'isApprove' => false,
            ]);

            if($return) {
                $getrequest->update([
                    'isReturnSent' => true
                ]);
            }
            //send a notification to all admin
            $user = User::find(Auth::id());
            $item = Item::find($getrequest['idItem']);
            $notificationType = 'returning the item';
            $notificationMessage = $notificationController->generateNotificationMessage([
                'firstName' => $user['firstName'],
                'type' => $notificationType,
                'itemCode' => $item['itemCode']
            ]);
            $allAdmin = User::where('isAdmin', true)->get();
            foreach($allAdmin as $admin) {
                $notification = $notificationController->sendNotification([
                    'recipientUserId' => $admin['id'],
                    'senderUserId' => Auth::id(),
                    'type' => $notificationType,
                    'notificationMessage' => $notificationMessage,
                    'isRead' => false,
                    'typeValueId' => $return['id']
                ]);
            }

            return response()->json([
                'message' => 'Request return was sent successfully.',
                'data' => $return,
                'notification' => $notification,
            ], 200);
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

    public function approveReturn(Request $request, string $id)
    {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $return = Returns::find($id);

        if(!$return) {
            return response()->json([
                'message' => 'Unable to find the return request.',
            ], 422);
        }
        else if($return['isApprove']) {
            return response()->json([
                'message' => 'Return request is already approved.',
            ], 200);
        }
        else {
            $return->update([
                'isApprove' => true
            ]);
            
            $returnnewdata = Returns::find($id);
            if($returnnewdata['isApprove']) {
                $requests = Requests::find($return['idRequest']);
                $item = Item::find($requests['idItem']);

                $item->update([
                    'isAvailable' => true
                ]);

                $requests->update([
                    'statusRequest' => 'Completed'
                ]);

                $approver = User::find(Auth::id());
                $notificationType = 'approve the return';
                $notificationMessage = $notificationController->generateNotificationMessage([
                    'firstName' => $approver['firstName'],
                    'type' => $notificationType,
                    'itemCode' => $item['itemCode']
                ]);

                $notification = $notificationController->sendNotification([
                    'recipientUserId' => $return['idReturner'],
                    'senderUserId' => Auth::id(),
                    'type' => $notificationType,
                    'notificationMessage' => $notificationMessage,
                    'isRead' => false,
                    'typeValueId' => $id
                ]);
            }

            return response()->json([
                'message' => 'Request for return has been approved.',
                'data' => $returnnewdata,
                'notification' => $notification
            ], 200);
        }
    }
}
