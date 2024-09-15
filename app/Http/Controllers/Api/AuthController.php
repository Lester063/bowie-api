<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(),[
            'firstName' => 'required|string|max:32',
            'middleName' => 'max:32',
            'lastName' => 'required|string|max:32',
            'email' => 'required|email|max:32|unique:'.User::class,
            'password' => 'required|min:8',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        } else {
            $user = User::create([
                'firstName' => $request['firstName'],
                'middleName' => $request['middleName'],
                'lastName' => $request['lastName'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'isAdmin' => false, // new users are automatically not admin
            ]);
        }

        if($user) {
            return response()->json([
                'message' => 'Success',
                'data' => $user
            ], 200);
        }

    }

    public function login(Request $request) {
        if(!Auth::attempt($request->only('email', 'password'))) {
            return response([
                'message' => 'Invalid Credentials',
                'statuscode' => 422,
            ], 422);
        }

        //$user = Auth::user();
        $token = Auth::user()->createToken('token')->plainTextToken;

        $cookie = cookie('jwt', $token, 60 * 24);

        return response([
            'message' => 'success',
            'statuscode' => 200,
            'data' => Auth::user()
        ])->withCookie($cookie);
    }

    public function user() {
        return Auth::user();
    }

    public function logout() {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'Success'
        ])->withCookie($cookie);
    }

    public function uploadProfile(Request $request) {
        $user = User::find(Auth::id());
        if($user) {
            $this->validate($request, [
                'profileImage' => 'file|mimes:jpg,jpeg,png|max:2058',
            ]);
            $imagePath = $request->file('profileImage')->store('image', 'public');
    
            $user->update([
                'profileImage' => $imagePath,
            ]);
    
            return response()->json([
                'message' => 'Success',
                'path' => $imagePath
            ], 200);
        }
        else {
            return response()->json([
                'message' => 'Unable to find the User.',
                'id' => $user
            ], 400);
        }
    }

}
