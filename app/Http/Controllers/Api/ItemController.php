<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Requests;

class ItemController extends Controller
{
    public function index() {
        $items = Item::all()->where('is_deleted', false);
        return response()->json([
            'status' => 200,
            'message' => $items
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

        if(!$item) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the item.'
            ], 404);
        } else {
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
                $item->update([
                    'itemname' => $request->itemname,
                    'itemcode' => $request->itemcode,
                ]);
                $newdata=Item::find($id);
                return response()->json([
                    'status' => 200,
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
        $getItemRequest = Requests::where('iditem', $id)->get();

        if($item) {
            return response()->json([
                'count' => $getItemRequest->count(),
                'message' => $getItemRequest
            ], 200);
        }
        else {
            return response()->json([
                'message' => 'Unable to find the item.'
            ], 404);
        }
    }
}
