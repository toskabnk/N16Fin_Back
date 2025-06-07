<?php

namespace App\Http\Controllers;

use App\Traits\ValidateRequest;
use Illuminate\Http\Request;
use App\Models\BusinessLine;
use Illuminate\Http\JsonResponse;

class BussinessLineController extends ResponseController
{
    use ValidateRequest;

    /**
     * Get all the business lines
     */
    public function viewAll(Request $request)
    {
        // Get all the business lines
        $businessLines = BusinessLine::all();

        // Return the response with the status code 200
        return $this->respondSuccess($businessLines);
    }

    /**
     * Find a business line by id
     */
    public function view(string $id)
    {
        // Find the business line
        $businessLine = BusinessLine::find($id);

        // If the business line is not found, return a response
        if (null === $businessLine) {
            return $this->respondNotFound('Business line not found');
        }

        // Return the response
        return $this->respondSuccess($businessLine);
    }

    /**
     * Create a new business line
     */
    public function create(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string',
            'acronym' => 'required|string',
            'description' => 'required|string',
        ];

        // Validate request
        $data = $this->validateData($request, $rules);
        // If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        // Create a new business line
        $businessLine = BusinessLine::create($data);
        
        // Return the response with the status code 201
        return $this->respondSuccess($businessLine, 201);
    }

    /**
     * Update a business line
     */
    public function update(Request $request, string $id)
    {
        // Find the business line
        $businessLine = BusinessLine::find($id);

        // If the business line is not found, return a response
        if (null === $businessLine) {
            return $this->respondNotFound('Business line not found');
        }

        // Validation rules
        $rules = [
            'name' => 'sometimes|required|string',
            'acronym' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
        ];

        // Validate request
        $data = $this->validateData($request, $rules);
        // If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        // Update the business line
        $businessLine->update($data);

        // Return the response with the status code 200
        return $this->respondSuccess($businessLine);
    }

    /**
     * Delete a business line
     */
    public function delete(string $id)
    {
        // Find the business line
        $businessLine = BusinessLine::find($id);

        // If the business line is not found, return a response
        if (null === $businessLine) {
            return $this->respondNotFound('Business line not found');
        }

        // Check if the business line has any invoices associated with it
        if ($businessLine->invoices()->count() > 0) {
            return $this->respondBadRequest('Cannot delete business line with associated invoices');
        }

        // Delete the business line
        $businessLine->delete();

        // Return the response with the status code 204
        return $this->respondNoContent();
    }
}
