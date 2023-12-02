<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RequestCommunication;
use App\Models\Requests;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RequestCommunicationController extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
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
                $sendmessage = RequestCommunication::create([
                    'idrequest' => $request->input('idrequest'),
                    'message' => $request->input('message'),
                    'idsender' => Auth::id(),
                ]);
    
                return response()->json([
                    'message' => 'Message sent successfully.',
                    'data' => $sendmessage,
                ], 200);
            }
        }
    }

    public function show($id) {
        //add idrequester to reqcomm and validate if id is equal to Auth
        $comms = RequestCommunication::where('idrequest', $id)->get();
        $checkAuth = false;
        foreach($comms as $comm) {
            if($comm->idsender == Auth::id()) {
                $checkAuth = true;
            }
        }

        if($comms->count() < 1) {
            return response()->json([
                'error' => 'Request does not exist.'
            ], 422);
        }
        else {
            if(!$checkAuth) {
                return response()->json([
                    'error' => 'You are not allowed to view the messages on this request.',
                ], 422);
            }
            else {
                return response()->json([
                    'data' => $comms,
                ], 200);
            }
        }
    }
}
