<?php

namespace App\Http\Controllers\api;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index() {
        $items = Item::all();
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
        $item = Item::find($id);
        if(!$item) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the item.'
            ], 404);
        } else {
            $item->delete();
            if($item) {
                $getAllItem = Item::all();
                return response()->json([
                    'status' => 200,
                    'message' => 'Item has been deleted successfully.',
                    'data' => $getAllItem
                ],200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong.'
                ],500);
            }
        }
    }
}
