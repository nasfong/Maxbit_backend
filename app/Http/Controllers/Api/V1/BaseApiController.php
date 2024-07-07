<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($message = null, $result = null)
    {
        $response['status'] = 'success';

        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($result)) {
            $response['data'] = $result;
        }

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $code = 200)
    {
        $response = [
            'status' => 'error',
            'message' => $error,
        ];
        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendValidation($errorMessages = [], $code = 200)
    {
        $response = [
            'status' => 'validation',
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
