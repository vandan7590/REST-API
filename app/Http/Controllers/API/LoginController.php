<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hobby;
use Illuminate\Http\Request;
use Auth;

class LoginController extends BaseController
{
    public function userLogin(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = \Auth::user();
            $user['hobbies'] = Hobby::where('user_id',$user->id)->get();
            $user['token'] = $user->createToken('REST-API')->accessToken;
            return $this->sendResponse($user, 'Login Successful');
        }else{
            return $this->sendError('Email Or Password Invalid');
        }
    }
}
