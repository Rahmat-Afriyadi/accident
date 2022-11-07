<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function uploadFile(Request $request, $field)
    {
        try {
            $validator = Validator::make($request->all(), [
                $field => "mimes:png,jpg,jpeg"
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()]);
            }
            $file = $request->file($field);
            $file_name = time() . $file->getClientOriginalName();
            $file->storeAs($field, $file_name);
            return $file_name;
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 403);
        }
    }
}
