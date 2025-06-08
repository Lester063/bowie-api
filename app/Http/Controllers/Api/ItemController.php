<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index() {
        $items = Item::where('isDeleted', false)->get();
        return response()->json([
            'status' => 200,
            'data' => $items
        ],200);

    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'itemName' => 'required|string|max:191',
            'itemCode' => 'required|string|max:191',
            'itemImage' => 'file|mimes:jpg,jpeg,png|max:2058',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        else if($this->verifyCodeIfExisting($request['itemCode'], null)) {
            return response()->json([
                'status' => 422,
                'errors' => ['itemCode' => 'Item code is already taken.'],
            ], 422);
        }

        else {
            $imagePath = $this->generateImagePath($request['itemImage']);
            $items = Item::create([
                'itemName' => $request['itemName'],
                'itemCode' => $request['itemCode'],
                'itemImage' => $imagePath,
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
                'errors' => 'Unable to find the item.'
            ], 404);
        } else {
            $validator = Validator::make($request->all(),[
                'itemName' => 'required|string|max:191',
                'itemCode' => 'required|string|max:191|',
            ]);
            if($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages()
                ], 422);
            }
            else if($this->verifyCodeIfExisting($request['itemCode'], $id)) {
                return response()->json([
                    'status' => 422,
                    'errors' => [
                        'itemCode' => 'Item code is already taken.'
                    ],
                ], 422);
            }
            else {
                $imagePath = $this->generateImagePath($request['itemImage']);
                $item->update([
                    'itemName' => $request['itemName'],
                    'itemCode' => $request['itemCode'],
                    'itemImage' => $imagePath == null ? $item['itemImage'] : $imagePath,
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
                'isDeleted' => true
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

    public function getItemRequestFromUser($id) {
        $item = Item::find($id);
        $getAllItemRequest = Requests::where('idItem', $id)->get();
        $getPendingItemRequest = Requests::where('idItem', $id)->where('statusRequest', 'Pending')->get();

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

    //we pass $id parameter for the verification when editing, null if creating
    public function verifyCodeIfExisting($itemCode, $id) {
        if(!is_null($id)) {
            if(Item::where('itemCode', $itemCode)->where('id','!=',$id)->count() > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            if(Item::where('itemCode', $itemCode)->count() > 0) {
                return true;
            }
            else {
                return false;
            }
        }
    }

    public function generateImagePath($itemImage) {
        if($itemImage) {
            $imagePath = $itemImage->store('image', 'public');
        }
        else {
            $imagePath = null;
        }
        return $imagePath;
    }
}
