<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use App\Models\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index() {
        $items = Item::where('is_deleted', false)->get();
        return response()->json([
            'status' => 200,
            'data' => $items
        ],200);

    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'itemname' => 'required|string|max:191',
            'itemcode' => 'required|string|max:191',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        } else {
            $items = Item::create([
                'itemname' => $request->itemname,
                'itemcode' => $request->itemcode,
            ]);
        }

        if($items) {
            return response()->json([
                'status' => 200,
                'message' => 'Item was created successfully.',
                'data' => $items
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function show($id) {
        $item = Item::find($id);

        if(!$item) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the item.'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'data' => $item
            ], 200);
        }
    }

    public function edit($id) {
        $item = Item::find($id);

        if(!$item) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the item.'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'data' => $item
            ], 200);
        }
    }

    public function update(Request $request, int $id) {
        $item = Item::find($id);
        $verifyCode = Item::where('itemcode', $request->itemcode)->where('id','!=',$id)->count();

        if(!$item) {
            return response()->json([
                'status' => 404,
                'errors' => 'Unable to find the item.'
            ], 404);
        } else {
            $validator = Validator::make($request->all(),[
                'itemname' => 'required|string|max:191',
                'itemcode' => 'required|string|max:191|',
            ]);
            if($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages()
                ], 422);
            } 

            else if($verifyCode > 0) {
                return response()->json([
                    'status' => 422,
                    'errors' => [
                        'itemcode' => 'Item code is already taken.'
                    ],
                ], 422);
            }
            
            else {
                $item->update([
                    'itemname' => $request->itemname,
                    'itemcode' => $request->itemcode,
                ]);
                $newdata=Item::find($id);
                return response()->json([
                    'status' => 200,
                    'message' => 'Data has been updated successfully.',
                    'newdata' => $newdata
                ], 200);
            }
        }
    }

    public function delete($id) {
        //add is_deleted column
        $item = Item::find($id);
        if(!$item) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the item.'
            ], 404);
        } else {
            $item->update([
                'is_deleted' => true
            ]);

            if($item) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Item has been deleted successfully.',
                    'data' => $item
                ],200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong.'
                ],500);
            }
        }
    }

    public function itemRequest($id) {
        $item = Item::find($id);
        $getAllItemRequest = Requests::where('iditem', $id)->get();
        $getPendingItemRequest = Requests::where('iditem', $id)->where('statusrequest', 'Pending')->get();

        if($item) {
            return response()->json([
                'count' => $getAllItemRequest->count(),
                'allitemrequest' => $getAllItemRequest,
                'countpending' => $getPendingItemRequest->count(),
                'pendingrequest' => $getPendingItemRequest,
            ], 200);
        }
        else {
            return response()->json([
                'message' => 'Unable to find the item.'
            ], 404);
        }
    }
}
