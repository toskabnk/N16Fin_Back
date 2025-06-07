<?php

namespace App\Http\Controllers;

use App\Models\ShareType;
use App\Traits\ValidateRequest;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareTypeController extends ResponseController
{
    use ValidateRequest;

    /**
     * Get all share types
     */
    public function viewAll(Request $request)
    {
        // Get all share types
        $shareTypes = ShareType::all();

        // Return the response with the status code 200
        return $this->respondSuccess($shareTypes);
    }

    /**
     * Find a share type by id
     */
    public function view(string $id)
    {
        // Find the share type
        $shareType = ShareType::find($id);

        // If the share type is not found, return a response
        if (null === $shareType) {
            return $this->respondNotFound('Share type not found');
        }

        // Return the response
        return $this->respondSuccess($shareType);
    }
    /**
     * Create a new share type
     */
    public function create(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ];

        // Validate request
        $data = $this->validateData($request, $rules);

        // If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        // Create the share type
        $shareType = ShareType::create($data);

        // Return the response with the status code 201
        return $this->respondSuccess($shareType, 201);
    }

    /**
     * Update a share type
     */
    public function update(Request $request, string $id)
    {
        // Find the share type
        $shareType = ShareType::find($id);

        // If the share type is not found, return a response
        if (null === $shareType) {
            return $this->respondNotFound('Share type not found');
        }

        // Validation rules
        $rules = [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ];

        // Validate request
        $data = $this->validateData($request, $rules);

        // If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        // Update the share type
        $shareType->update($data);

        // Return the response with the status code 200
        return $this->respondSuccess($shareType);
    }
    /**
     * Delete a share type
     */
    public function delete(string $id)
    {
        // Find the share type
        $shareType = ShareType::find($id);

        // If the share type is not found, return a response
        if (null === $shareType) {
            return $this->respondNotFound('Share type not found');
        }

        //Check if the share type is used in any invoice
        if ($shareType->invoices()->count() > 0) {
            return $this->respondBadRequest('Share type cannot be deleted because it is used in one or more invoices');
        }

        // Delete the share type
        $shareType->delete();

        // Return the response with the status code 204
        return $this->respondNoContent();
    }
}
