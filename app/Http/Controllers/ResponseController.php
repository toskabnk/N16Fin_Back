<?php

namespace App\Http\Controllers;

class ResponseController extends Controller
{
    protected function respond($data, $statusCode = 200)
    {
        return response()->json($data, $statusCode);
    }

    protected function respondSuccess($data, $statusCode = 200)
    {
        return $this->respond([
            'status' => 'success',
            'data' => $data
        ], $statusCode);
    }

    protected function respondError($message, $statusCode, $errors = '')
    {
        return $this->respond([
            'status' => 'error',
            'data' => [
                'message' => $message,
                'code' => $statusCode,
                'errors' => $errors
            ]
        ], $statusCode);
    }

    protected function respondUnauthorized($message = 'Unauthorized')
    {
        return $this->respondError($message, 401);
    }

    protected function respondForbidden($message = 'Forbidden')
    {
        return $this->respondError($message, 403);
    }

    protected function respondNotFound($message = 'Not Found', $errors = '')
    {
        return $this->respondError($message, 404, $errors);
    }

    protected function respondUnprocessableEntity($message = 'Unprocessable Entity', $errors = '')
    {
        return $this->respondError($message, 422, $errors);
    }

    protected function respondBadRequest($message = 'Bad Request', $errors = '')
    {
        return $this->respondError($message, 400, $errors);
    }

    protected function respondNoContent()
    {
        return $this->respond('', 204);
    }
}
