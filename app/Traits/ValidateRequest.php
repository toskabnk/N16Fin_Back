<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidateRequest
{
    /**
     * Validate the request data
     *
     * @param Request $request
     * @param array $rules
     * @return array|JsonResponse
     */
    protected function validateData(Request $request, $rules)
    {
        // Request validation with the rules
        $validation = Validator::make($request->all(), $rules);

        // If validation fails, send a response with the errors
        if ($validation->fails()) {
            return $this->respondUnprocessableEntity('Validation errors', $validation->errors());
        }

        // Save validated data
        return $validation->validated();
    }
}
