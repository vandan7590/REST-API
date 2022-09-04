<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hobby;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Validator;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(\Auth::guard('api')->check()){

            $user = \Auth::guard('api')->user();
            if($user->role_id == 1){
                $hobby = $request->get('hobby');
                $users = User::where('role_id',2)->select('*');

                if ($users->count() <= 0) {
                    return $this->sendError('User not found.');
                }

                if($hobby){
                    $users = $users->join('hobbies','users.id','=','hobbies.user_id')
                        ->where('hobbies.name',$hobby)
                        ->get();
                }else{
                    $users = $users->with('hobbies')->get();
                }

                return $this->sendResponse($users,'Users list.');
            }else{
                return $this->sendError("Unauthorized");
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        /* User Validation  */
        $validator = Validator::make($input, [
            'first_name' => 'required|alpha',
            'last_name' => 'alpha',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'user_photo' => 'required|mimes:jpeg,jpg,png,gif',
            'mobile_no' => 'required|regex:/^\\+?[1-9][0-9]{7,11}$/'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        /* check user photo */
        if($input['user_photo']){
            $file = $request->file('user_photo');
            $fileName = $file->getClientOriginalName();
            $file->storeAs('public/user',$fileName);
        }

        try{
            $user = new User();
            $user->first_name = $input['first_name'];
            $user->last_name = $input['last_name'] ?? null;
            $user->email = $input['email'];
            $user->password = bcrypt($input['password']);
            $user->user_photo = $input['user_photo'] ? $fileName : null;
            $user->mobile_no = $input['mobile_no'];
            $user->status = $input['status'];
            $user->role_id = 2;
            $user->save();

            $hobbies = new Hobby();
            $hobbies->user_id = $user->id;
            $hobbies->name = $input['hobby_name'];
            $hobbies->save();
            $user['hobbies'] = $hobbies->name;
            $user['role'] = Role::where('id',$user->role_id)->pluck('role')->first();

            /* create token for authenticatin */
            $user['token'] = $user->createToken('REST-API')->accessToken;

            return $this->sendResponse($user, 'User Added Successfully');
        }catch(Exception $e){
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(\Auth::guard('api')->check()){
            $user = User::with('hobbies')->find($id);
            if (is_null($user)) {
                return $this->sendError('User not found');
            }
            return $this->sendResponse($user,'User retrieved successfully');
        }else{
            return $this->sendError('Invalid Token');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(\Auth::guard('api')->check()){
            $user = User::find($id);

            /* User Validation  */
            $validator = Validator::make($request->all(), [
                'first_name' => 'alpha',
                'last_name' => 'alpha',
                'email' => 'unique:users,email,'.$user->id,
                'user_photo' => 'mimes:jpeg,jpg,png,gif',
                'mobile_no' => 'regex:/^\\+?[1-9][0-9]{7,11}$/'
            ]);

            if($validator->fails()){
                return $this->sendError($validator->errors());
            }

            /* check user photo */
            if($request->file('user_photo')){
                $file = $request->file('user_photo');
                $fileName = $file->getClientOriginalName();
                $file->storeAs('public/user',$fileName);
            }

            try{
                User::where('id',$id)->update([
                    'first_name' => $request->first_name ? $request->first_name : $user->first_name,
                    'last_name' => $request->last_name ? $request->last_name : $user->last_name,
                    'email' => $request->email ? $request->email : $user->email,
                    'user_photo' => $fileName ? $fileName : $user->user_photo,
                    'mobile_no' => $user->mobile_no = $request->mobile_no ? $request->mobile_no : $user->mobile_no
                ]);
                $data = User::with('hobbies')->where('id',$id)->first();
                return $this->sendResponse($data, 'User Updated Successfully');

            }catch(Exception $e){
                return $this->sendError($e->getMessage());
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Update the specified user's hobbies from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function hobbyUpdate(Request $request)
    {
        if(\Auth::guard('api')->check()){

            $user = \Auth::guard('api')->user();

            $hobby = Hobby::find($request->id);

            /* User Validation  */
            $validator = Validator::make($request->all(), [
                'name' => 'alpha',
            ]);

            if($validator->fails()){
                return $this->sendError($validator->errors());
            }

            try{
                if($user->id == $hobby->user_id){
                    Hobby::where('id',$request->id)->update([
                        'name' => $request->name ? $request->name : $hobby->name,
                    ]);
                    $data = User::with('hobbies')->where('id',$hobby->user_id)->first();
                    return $this->sendResponse($data, 'User`s Hobby Updated Successfully');
                }else{
                    return $this->sendResponse("","Unauthorized");
                }
            }catch(Exception $e){
                return $this->sendError($e->getMessage());
            }
        }
    }
}
