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
            'idrequest' => 'required|max:11',
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        else {
            $return = Returns::where('idrequest', $request->idrequest)->first();
            if($return->is_reviewed) {
                return response()->json([
                    'errors' => ['message' => 'You can only review once per completed request.']
                ], 400);
            }
            else {
                $commentreview = CommentReview::create([
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'idrequest' => $request->idrequest,
                ]);
                $return->update([
                    'is_reviewed' => true
                ]);

                return response()->json([
                    'message' => 'You have reviewed on this request successfully.',
                    'data' => $commentreview
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
        $requests = Requests::where('iditem', $id)->get();
        $reviews = CommentReview::whereIn('idrequest', $requests->pluck('id'))
                    ->join('requests', 'requests.id', '=', 'comment_reviews.idrequest')
                    ->join('users', 'users.id', '=', 'requests.idrequester')->get();
        return response()->json([
            'data' => $reviews,
        ], 200);

    }
}
