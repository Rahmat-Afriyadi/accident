<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\UserUploadImage;
use App\Http\Requests\User\UserUpdate;

class UserController extends Controller
{
    //
    public function uploadImageProfile(UserUploadImage $request)
    {
        $user = User::find(Auth::id());
        if ($user->update(["image" => $this->uploadFile($request, "image")])) {
            return response()->json(["message" => "image successfully upload"], 200);
        }
    }

    public function userUpdate(UserUpdate $request)
    {
        $input = $request->all();
        $user = User::find(Auth::id());
        if ($user->update($input)) {
            return response()->json(["message" => "use profile successfully update"], 200);
        }
    }
}
