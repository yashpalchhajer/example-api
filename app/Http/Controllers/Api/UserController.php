<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function check(Request $request)
    {
        $response = [
            'status'    =>  '',
            'message'   =>  '',
            'data'  =>  [
                'name'  =>  auth('sanctum')->user()->name,
                'email' =>  auth('sanctum')->user()->email
            ]
        ];

        return response()->json($response);
    }

    public function register(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:1',
                'email' => 'required|email',
                'password' => 'required|string|min:1'
            ]);

            $response = [
                'status'    =>  '',
                'message'   =>  '',
                'data'  =>  []
            ];


            if ($validator->fails()) {

                $response['status'] =   'fails';
                $response['message'] =   'Validation error';
                $response['data']   =   $validator->messages();

                return response()->json($response, 400);
            }

            $user = new User();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);

            $user->save();

            $response['status'] =   'success';
            $response['message'] =   'User registered successfully';
            $response['data']   =   $user;

            return response()->json($response);

        } catch ( \Exception $e ) {
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }


    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' =>  'required|email',
            'password'  =>  'required|string'
        ]);

        $response = [
            'status'    =>  '',
            'message'   =>  '',
            'data'  =>  []
        ];

        if($validator->fails()){
            $response['status'] =   'fails';
            $response['message'] = 'Validation fails';
            $response['data'] = $validator->messages();

            return response()->json($response, 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error'=>'The email or password is incorrect, please try again'], 422);
        }


        $token = $user->createToken(Str::random(40));


        $response['status'] =   'success';
        $response['message'] = 'Log in successfully';
        $response['data'] = ['token'=> $token->plainTextToken];

        return response()->json($response);

    }
}
