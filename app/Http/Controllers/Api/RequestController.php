<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use App\Models\Requests;
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

        $requests = Requests::all();

        return response()->json([
            'status' => 200,
            'message' => $requests,
        ], 200);
        
    }

    public function indexUser()
    {
        $requests = Requests::where('idrequester', Auth::id())
        ->join('items', 'items.id', '=', 'requests.iditem')->get();

        $aw = Requests::where('idrequester', Auth::id())
        ->join('items', 'items.id', '=', 'requests.iditem')->select('*','requests.id as requestid')->get();

        return response()->json([
            'status' => 200,
            'message' => $aw,
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
                ]);
    
                return response()->json([
                    'message' => 'Request sent successfully.',
                    'data' => $requestdata,
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
}
