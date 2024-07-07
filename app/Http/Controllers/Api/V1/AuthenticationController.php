<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\V1\LogSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends BaseApiController
{

    public function authenticated()
    {
        return $this->sendResponse('authenticated');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $data_input = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email
        ];
        $user = User::create($data_input);

        # set Role guest
        $user->assignRole(2);

        return $this->sendResponse('Success created user.', [
            'token' => $user->createToken($user->email . '_tokens')->plainTextToken
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:8|max:50'
        ]);
        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $loginValue = $request->email;
        $password = $request->password;
        // Checking type of request email, mobile, or username
        $login_type = self::getLoginType($loginValue);

        if (!Auth::attempt([
            $login_type => $loginValue,
            'password' => $password
        ])) {
            return $this->sendError('Credentials not match');
        }
        if(!auth()->user()->activated){
            return $this->sendError('Your account was blocked.');
        }
        if (!empty(auth()->user()->first_login))
            return $this->sendResponse('Success logged as ' . auth()->user()->fullname, [
                'first_login' => auth()->user()->first_login,
            ]);
        $token = auth()->user()->createToken(auth()->user()->email . '_token')->plainTextToken;
        [$token_id, $token_value] = explode('|', $token, 2);

        LogSession::create([
            'last_login' => \Carbon\Carbon::now()->toDateTimeString(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'token_id' => $token_id,
            'data' => json_encode([
                'url' => \Illuminate\Support\Facades\Request::fullUrl(),
                'method' => \Illuminate\Support\Facades\Request::method(),
                'description' => 'User logged.'
            ]),
            'ip' => \Illuminate\Support\Facades\Request::ip() ?? null,
            'agent' => \Illuminate\Support\Facades\Request::header('user-agent') ?? null,
        ]);

        return $this->sendResponse('Success logged as ' . auth()->user()->fullname, [
            'token' => $token
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|max:50|required_with:confirmation_password|same:confirmation_password',
            'confirmation_password' => 'string|min:8|max:50',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        if ($user = User::where('first_login', $request->token)->first()) {
            $user->update([
                'first_login' => null,
                'password' => Hash::make($request->password),
            ]);
            return $this->sendResponse('Success Change Password');
        } else {
            return $this->sendError('Error Change Password');
        }
    }

    public function logout(Request $request)
    {
        try {
            if ($request->bearerToken()) {
                [$token_id, $token] = explode('|', $request->bearerToken(), 2);
                // Delete log sessions
                LogSession::where('token_id', $token_id)->delete();
                // Delete token
                auth()->user()->tokens()->where('token', hash('sha256', $token))->delete();
            } else {
                auth()->user()->tokens()->delete();
            }
            return $this->sendResponse('Success logout');
        } catch (\Exception $e) {
            return $this->sendError('Error logout', $e->getMessage());
        }
    }

    /**
     * 
     * Check user login type email phone and username
     * @param mixed $loginValue
     * @return string
     * 
     */

    private static function getLoginType($loginValue)
    {
        return filter_var($loginValue, FILTER_VALIDATE_EMAIL)
            ? 'email' : ((preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $loginValue)) ? 'phone' : 'username');
    }
}
