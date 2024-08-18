<?php

namespace App\Http\Controllers\Api;

use App\Models\Requests;
use Illuminate\Http\Request;
use App\Models\CommentReview;
use App\Models\Returns;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'rating' => 'required|int|max:11',
            'comment' => 'required|string|max:191|',
            'idRequest' => 'required|max:11',
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        else {
            $return = Returns::where('idRequest', $request['idRequest'])->first();
            if($return['isReviewed']) {
                return response()->json([
                    'errors' => ['message' => 'You can only review once per completed request.']
                ], 400);
            }
            else {
                $commentReview = CommentReview::create([
                    'rating' => $request['rating'],
                    'comment' => $request['comment'],
                    'idRequest' => $request['idRequest'],
                ]);
                $return->update([
                    'isReviewed' => true
                ]);

                return response()->json([
                    'message' => 'You have reviewed on this request successfully.',
                    'data' => $commentReview
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

    public function showItemReviews($id) {
        $requests = Requests::where('idItem', $id)->get();
        $reviews = CommentReview::whereIn('idRequest', $requests->pluck('id'))
                    ->join('requests', 'requests.id', '=', 'comment_reviews.idRequest')
                    ->join('users', 'users.id', '=', 'requests.idRequester')->get();
        return response()->json([
            'data' => $reviews,
        ], 200);

    }
}
