<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Requests;
use App\Models\CommentReview;
use Illuminate\Support\Facades\Auth;

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
        $getRequest = Requests::where('id', $request->idrequest)
        ->where('statusrequest', 'Completed')
        ->where('idrequester', Auth::id())->count();
        if($getRequest > 0) {
            if(CommentReview::where('idrequest', $request->idrequest)->count() > 0) {
                return response()->json([
                    'message' => 'You can only review once per completed request.'
                ], 400);
            }
            else {
                $commentreview = CommentReview::create([
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'idrequest' => $request->idrequest,
                ]);

                return response()->json([
                    'message' => 'You have reviewed on this request successfully.',
                    'data' => $commentreview
                ], 200);
            }
        }
        else {
            return response()->json([
                'message' => 'Something is wrong.',
            ], 400);
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
        $reviews = [];
        foreach($requests as $req) {
            $review = CommentReview::where('idrequest', $req->id)
            ->join('requests', 'requests.id', '=', 'comment_reviews.idrequest')
            ->join('users', 'users.id', '=', 'requests.idrequester')
            ->get();

            array_push($reviews, $review);

        }
        
        return response()->json([
            'data' => $reviews,
        ], 200);

    }
}
