<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\V1\LogSession;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\LogSessionsResource;

class LogSessionController extends BaseApiController
{
    public function allSessionUser(Request $request, $user_id)
    {
        $skip_id = null;
        if ($request->bearerToken()) {
            [$token_id, $token] = explode('|', $request->bearerToken(), 2);
            $skip_id = $token_id;
        }

        $data = LogSession::where('user_id', $user_id)
            ->where('token_id', '<>', $skip_id)
            ->latest()
            ->paginate(5);
        return LogSessionsResource::collection($data);
    }

    public function destroyToken(Request $request, $id)
    {
        try {
            $skip_id = null;
            if ($request->bearerToken()) {
                [$token_id, $token] = explode('|', $request->bearerToken(), 2);
                $skip_id = $token_id;
            }
            if ($log = LogSession::where('id', $id)->where('token_id', '<>', $skip_id)->first()) {
                $log->delete();
                DB::table('personal_access_tokens')->where('id', $id)->delete();
                return $this->sendResponse('Success sign out');
            } else {
                return $this->sendError('The record not found!');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function destroyUser(Request $request, $user_id)
    {
        $skip_id = null;
        if ($request->bearerToken()) {
            [$token_id, $token] = explode('|', $request->bearerToken(), 2);
            $skip_id = $token_id;
        }
        try {
            LogSession::where('user_id', $user_id)->where('token_id', '<>', $skip_id)->delete();
            DB::table('personal_access_tokens')->where('tokenable_id', $user_id)->delete();
            return $this->sendResponse('Success sign out');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
