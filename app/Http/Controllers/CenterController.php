<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterController extends ResponseController
{
    use ValidateRequest;

    /**
     * Get all the centers
     */
    public function viewAll(Request $request)
    {
        //Get all the centers
        $centers = Center::all();

        //Return the response with the status code 200
        return $this->respondSuccess($centers);
    }

    /**
     * Find a center by id
     */
    public function view(string $id)
    {
        //Find the center
        $center = Center::find($id);

        //If the center is not found, return a response
        if (null === $center) {
            return $this->respondNotFound('Center not found');
        }

        //Return the response
        return $this->respondSuccess($center);
    }

    /**
     * Create a new center
     */
    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'name' => 'required|string',
            'acronym' => 'required|string',
            'city' => 'required|string',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Create the center
        $center = Center::create($data);

        //Return the response with the status code 201
        return $this->respondSuccess($center, 201);
    }

    /**
     * Update a center
     */
    public function update(Request $request, string $id)
    {
        //Validation rules
        $rules = [
            'name' => 'string',
            'acronym' => 'string',
            'city' => 'string',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Find the center
        $center = Center::find($id);

        //If the center is not found, return a response
        if (null === $center) {
            return $this->respondNotFound('Center not found');
        }

        //Update the center
        $center->update($data);

        //Return the response with the status code 200
        return $this->respondSuccess($center);
    }
    /**
     * Delete a center
     */
    public function delete(string $id)
    {
        //Find the center
        $center = Center::find($id);

        //If the center is not found, return a response
        if (null === $center) {
            return $this->respondNotFound('Center not found');
        }

        //Check if the center has suppliers or invoices
        if ($center->suppliers()->exists() || $center->invoices()->exists()) {
            return $this->respondBadRequest('Cannot delete center with suppliers or invoices');
        }

        //Delete the center
        $center->delete();

        //Return the response with the status code 200
        return $this->respondSuccess('Center deleted successfully');
    }
}
