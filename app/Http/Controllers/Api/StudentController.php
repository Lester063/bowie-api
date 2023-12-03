<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index() {
        $students = Student::all();
        return response()->json([
            'status' => 200,
            'message' => $students
        ],200);

    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:191',
            'course' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:'.Student::class,
            'phone' => 'required|digits:11',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        } else {
            $students = Student::create([
                'name' => $request->name,
                'course' => $request->course,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);
        }

        if($students) {
            return response()->json([
                'status' => 200,
                'message' => 'Student was created successfully.',
                'data' => $students
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function show($id) {
        $student = Student::find($id);

        if(!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the student.'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'data' => $student
            ], 200);
        }
    }

    public function edit($id) {
        $student = Student::find($id);

        if(!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the student.'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'data' => $student
            ], 200);
        }
    }

    public function update(Request $request, int $id) {
        $student = Student::find($id);

        if(!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the student.'
            ], 404);
        } else {
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:191',
                'course' => 'required|string|max:191',
                'email' => 'required|email|max:191',
                'phone' => 'required|digits:11',
            ]);
            if($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages()
                ], 422);
            } else {
                $student->update([
                    'name' => $request->name,
                    'course' => $request->course,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);
                $newdata=Student::find($id);
                return response()->json([
                    'status' => 200,
                    'newdata' => $newdata
                ], 200);
            }
        }
    }

    public function delete($id) {
        $student = Student::find($id);
        if(!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Unable to find the student.'
            ], 404);
        } else {
            $student->delete();
            if($student) {
                $getAllStudent = Student::all();
                return response()->json([
                    'status' => 200,
                    'message' => 'Student has been deleted successfully.',
                    'data' => $getAllStudent
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
