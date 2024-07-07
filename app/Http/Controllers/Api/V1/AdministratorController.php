<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UsersResource;
use Illuminate\Support\Facades\Validator;

class AdministratorController extends BaseApiController
{
    private $page = 'Administrator';
    function __construct()
    {
        $this->middleware('permission:' . $this->page . ' List', ['only' => ['index']]);
        $this->middleware('permission:' . $this->page . ' View', ['only' => ['show']]);
        $this->middleware('permission:' . $this->page . ' Create', ['only' => ['store']]);
        $this->middleware('permission:' . $this->page . ' Edit', ['only' => ['update']]);
        $this->middleware('permission:' . $this->page . ' Trash', ['only' => ['destroy']]);
        $this->middleware('permission:' . $this->page . ' Restore', ['only' => ['restore']]);
        $this->middleware('permission:' . $this->page . ' Delete', ['only' => ['delete']]);
    }

    public function index(Request $request)
    {
        $data = User::search($request)
            ->latest()
            ->paginate(12);
        return UsersResource::collection($data);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'email' => 'required|email|string|min:3|max:50|unique:users,email',
            'phone' => 'required|string|max:50|min:7|unique:users,phone',
            'password' => 'required|min:8|max:50',
            'roles' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $photo = '';
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photo = time() . '.' . $file->extension();
            $file->move(public_path('uploads'), $photo);
            $photo = 'uploads/' . $photo;
        }

        $data_input = [
            'author_id' => auth()->id(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'photo' => $photo,
            'activated' => $request->activated == 'true'  ? 1 : 0,
            'first_login' => $request->first_login == 'true' ?  Str::random(60) : null,
        ];
        // return $this->sendResponse('',$data_input);
        if ($user = User::myCreate($data_input)) {
            try {
                /* Assign role to user */
                // if ($request->has('roles')) {
                //     $roles = explode(',',$request->roles);
                //     $user->assignRole($roles);
                // }
                if ($request->has('roles')) $user->assignRole($request->roles);
                return $this->sendResponse('Success added.', $user);
            } catch (\Exception $e) {
                $user->delete();
                return $this->sendError($e->getMessage());
            }
        } else {
            return $this->sendError('Error added.');
        }
    }

    public function show($id)
    {
        $data = User::find($id);
        return $data ? new UserResource($data) : '';
    }

    public function update(Request $request, $id)
    {
        // return response()->json($request->all());

        $user = User::find($id);
        if (!$user) return $this->sendError('The record not found!');

        $rules['first_name'] = 'required';
        $rules['last_name'] = 'required';
        $rules['username'] = 'required|string|min:3|max:50|unique:users,username,' . $id;
        $rules['email'] = 'required|email|string|min:3|max:50|unique:users,email,' . $id;
        $rules['phone'] = 'required|string|max:50|min:7|unique:users,phone,' . $id;
        $rules['roles'] = 'required';
        if (!empty($request->password)) {
            $rules['password'] = 'required|string|min:8|max:50';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $photo = $user->photo;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photo = time() . '.' . $file->extension();
            $file->move(public_path('uploads'), $photo);
            if (!empty($user->photo) && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }
            $photo = 'uploads/' . $photo;
        }

        $data_input = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'photo' => $photo,
            'activated' => $request->activated == 'true' ? 1 : 0,
            'first_login' => $request->first_login == 'true' ?  Str::random(60) : null,
        ];

        // check if have password
        if (!empty($request->password)) {
            $data_input['password'] = Hash::make($request->password);
        } else {
            $data_input = array_except($data_input, array('password'));
        }

        if ($user->update($data_input)) {
            /* Assign role to user */
            // if ($request->has('roles')) {
            //     $roles = explode(',',$request->roles);
            //     $user->syncRoles($roles);
            // }
            if ($request->has('roles')) $user->syncRoles($request->roles);
            return $this->sendResponse('Success updated.', $user);
        } else {
            return $this->sendError('Error updated.');
        }
    }

    public function restore($id)
    {
        try {
            User::withTrashed()->where('id', $id)->restore();
            return $this->sendResponse('Success restored.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);
            $user->delete();
            return $this->sendResponse('Success trashed.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            if ($user = User::withTrashed()->find($id)) {
                if (!empty($user->photo) && file_exists(public_path($user->photo))) {
                    unlink(public_path($user->photo));
                }
                $user->forceDelete();
                return $this->sendResponse('Success deleted.');
            } else {
                return $this->sendError('The record not found!');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
