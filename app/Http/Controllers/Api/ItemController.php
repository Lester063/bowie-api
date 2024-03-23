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
            'item_image' => 'file|mimes:jpg,jpeg,png|max:2058',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        else if($this->verifyCodeIfExisting($request->itemcode, null)) {
            return response()->json([
                'status' => 422,
                'errors' => ['itemcode' => 'Item code is already taken.'],
            ], 422);
        }

        else {
            if($request->item_image) {
                $image_path = $request->file('item_image')->store('image', 'public');
            }
            else {
                $image_path = null;
            }
            $items = Item::create([
                'itemname' => $request->itemname,
                'itemcode' => $request->itemcode,
                'item_image' => $image_path,
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

    //we pass $id parameter for the verification when editing, null if creating
    public function verifyCodeIfExisting($itemcode, $id) {
        if(!is_null($id)) {
            if(Item::where('itemcode', $itemcode)->where('id','!=',$id)->count() > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            if(Item::where('itemcode', $itemcode)->count() > 0) {
                return true;
            }
            else {
                return false;
            }
        }
    }

    public function update(Request $request, int $id) {
        $item = Item::find($id);
        //$verifyCode = Item::where('itemcode', $request->itemcode)->where('id','!=',$id)->count();

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
            else if($this->verifyCodeIfExisting($request->itemcode, $id)) {
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
                $newdata = Item::find($id);
                return response()->json([
                    'status' => 200,
                    'message' => 'Data has been updated successfully.',
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
        }
        else {
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
                ], 500);
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
