<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\V1\LogSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseApiController
{
    // private $page = 'User';

    function __construct()
    {
        $this->middleware('permission:Update Profile', ['only' => ['updateProfile']]);
        $this->middleware('permission:Deactivate Account', ['only' => ['deactivate']]);
    }

    public function profile(Request $request)
    {
        $user = auth()->user();
        $data = [];
        $data['id'] = $user->id;

        $data['first_name'] = $user->first_name;
        $data['last_name'] = $user->last_name;
        $data['fullname'] = $user->fullname;
        $data['username'] = $user->username;
        $data['phone'] = $user->phone;
        $data['email'] = $user->email;
        $data['roles'] = $user->roles->map(function ($data) {
            return $data->name;
        })->implode(",");
        $data['permissions'] = $user->getAllPermissions()->pluck('name');
        $data['photo'] = $user->photo ? asset($user->photo) : asset('/uploads/no_avatar.png');
        $data['activated'] = $user->activated;
        return $data;
    }

    public function updateProfile(Request $request)
    {
        if (!auth()->check()) return $this->sendError('The record not found!');
        $user = auth()->user();
        $rules['first_name'] = 'required';
        $rules['last_name'] = 'required';

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $photo = $user->photo;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photo = time() . '.' . $file->extension();
            $file->move(public_path('uploads'), $photo);
            if(!empty($user->photo) && file_exists(public_path($user->photo))){
                 unlink(public_path($user->photo));
            }
            $photo = 'uploads/'.$photo;
           
        }

        $data_input = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'photo' => $photo,
        ];

        if ($user->update($data_input)) {
            return $this->sendResponse('Success changed.');
        } else {
            return $this->sendError('Error changed.');
        }
    }

    public function updateProfileUsername(Request $request)
    {
        if (!auth()->check()) return $this->sendError('The record not found!');

        $id = auth()->id();
        $user = auth()->user();

        $rules['password'] = 'required|string|min:8|max:50';
        $rules['username'] = 'required|string|unique:users,username,' . $id;

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }

        if (!Hash::check($request->password, $user->password))
            return $this->sendError('The password not match!');
        $data_input = [
            'username' => $request->username,
        ];

        if ($user->update($data_input)) {
            return $this->sendResponse('Success updated.');
        } else {
            return $this->sendError('Error updated.');
        }
    }

    public function updateProfileEmail(Request $request)
    {
        if (!auth()->check()) return $this->sendError('The record not found!');

        $id = auth()->id();
        $user = auth()->user();

        $rules['password'] = 'required';
        $rules['email'] = 'required|email|unique:users,email,' . $id;

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }

        if (!Hash::check($request->password, $user->password))
            return $this->sendError('The password not match!');
        $data_input = [
            'email' => $request->email,
        ];

        if ($user->update($data_input)) {
            return $this->sendResponse('Success updated.');
        } else {
            return $this->sendError('Error updated.');
        }
    }

    public function updateProfilePassword(Request $request)
    {
        if (!auth()->check()) return $this->sendError('The record not found!');

        $user = auth()->user();
        $rules['current_password'] = 'required|string|min:8';
        $rules['new_password'] = 'string|min:8|required_with:confirmation_password|same:confirmation_password';
        $rules['confirmation_password'] = 'string|min:8';

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        if (!Hash::check($request->current_password, $user->password))
            return $this->sendError('The password not match!');

        $data_input['password'] = Hash::make($request->new_password);

        if ($user->update($data_input)) {
            return $this->sendResponse('Success updated.');
        } else {
            return $this->sendError('Error updated.');
        }
    }

    public function deactivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'confirm' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        if ($request->confirm) {
            LogSession::where('user_id', auth()->id())->delete();
            auth()->user()->tokens()->delete();
            auth()->user()->delete();

            return $this->sendResponse('Account has been successfully deleted!');
        } else {
            return $this->sendError('Please check the box to deactivate your account');
        }
    }

}
