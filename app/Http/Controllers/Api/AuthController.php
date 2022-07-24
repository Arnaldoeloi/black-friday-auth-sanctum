<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{


    public function createUser(Request $request){

        try {
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'nickname' => 'required|unique:users,nickname',
                'email'=>'required|email|unique:users,email',
                'password'=> 'required',
            ]);
            
            if($validateUser->fails()){
                return response()->json([
                    'status'=>'false',
                    'message'=>'validation error',
                    'error'=>$validateUser->errors()
                ],401);
            }

            $user = User::create([
                'name'=>$request->name,
                'nickname'=>$request->nickname,
                'email'=>$request->email,
                'password'=>Hash::make($request->password)
            ]);

            return response()->json([
                'status'=>'true',
                'user' => [
                    'id'      => $user->id,
                    'email'   => $user->email,
                    'nickname'=> $user->nickname,
                    'name'    => $user->name,
                ],
                'message'=>'User created successfully',
                'token'=>$user->createToken("API TOKEN")->plainTextToken
            ],200);

        }catch(\Throwable $th){
            return response()->json([
                'status'=>'false',
                'message'=>$th->getMessage(),
            ],500);
            
        }
    }

    public function loginUser(Request $request){
        try{
            $validateUser = Validator::make($request->all(),[
                'email'=>'required|email',
                'password'=>'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>'validation error',
                    'errors'=>$validateUser->errors()
                ], 401);
            }
            
            if(!Auth::attempt($request->only(['email','password']))){
                return response()->json([
                    'status'=>false,
                    'message'=>'Email and password does not match any of our records.',
                ], 401);
            }

            $user = User::where('email',$request->email)->first();

            return response()->json([
                'status'=>'true',
                'user' => [
                    'id'      => $user->id,
                    'email'   => $user->email,
                    'nickname'=> $user->nickname,
                    'name'    => $user->name,
                ],
                'message'=>'User logged successfully',
                'token'=>$user->createToken("API TOKEN")->plainTextToken
            ],200);


        } catch(\Throwable $th){
            return response()->json([
                'status'=>false,
                'message'=>$th->getMessage(),
            ], 500);

        }
    }
}
