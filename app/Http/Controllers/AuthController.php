<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login']]);
    // }

    //register user
    public function register(Request $request)
    {
        // validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users',
            'transportation_type' => 'required|in:CARS,TRUCK,MOTORCYCLE',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Failed', 'state' => '100', 'message' => $validator->errors()]);
        }
        $name = $request->name;
        $email = $request->email;
        $transportation_type = $request->transportation_type;
        $phone = $request->phone;
        $password = $request->password;

        // create new user
        try {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->transportation_type = $transportation_type;
            $user->phone = $phone;
            $user->password = Hash::make($password);
            if ($user->save()) {
                // request to laravel passport routes

                $credentials = request(['email', 'password']);

                if (!$token = auth('api')->attempt($credentials)) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                // call function to get verif email
                // event(new Registered($user));
                return response()->json(['user' => $user, 'token' => $this->respondWithToken($token)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    // login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|exists:users,email']);
        if ($validator->fails()) {
            return response()->json(['status' => 'Failed', 'state' => '100', 'message' => $validator->errors()]);
        }
        $credentials = request(['email', 'password']);
        $user = User::where('email', $request->email)->first();

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized, Wrong password'], 401);
        }
        return response()->json(['user' => $user, 'token' => $this->respondWithToken($token)]);
    }

    public function me(Request $request)
    {
        $user = auth('api')->user();
        $user->role;
        // return response()->json($user);
        return response()->json($user);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return response()->json($this->respondWithToken(auth('api')->refresh()));
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 99999999999
        ];
    }

    // request forgot password
    public function requestForgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        // Requesting A Password Reset Link
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
    // get token to reset password
    public function getToken($token)
    {
        return response()->json($token);
    }
    // udpate password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // validate the password has been changed
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->login($request)
            : back()->withErrors(['email' => [__($status)]]);
    }
}
