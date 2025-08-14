<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConceptController extends ResponseController
{
    use ValidateRequest;

    /**
     * Get all the concepts
     */
    public function viewAll(Request $request)
    {
        //Get all the concepts
        $concepts = Concept::all();

        //Return the response with the status code 200
        return $this->respondSuccess($concepts);
    }

    /**
     * Find a concept by id
     */
    public function view(string $id)
    {
        //Find the concept
        $concept = Concept::find($id);

        //If the concept is not found, return a response
        if (null === $concept) {
            return $this->respondNotFound('Concept not found');
        }

        //Return the response
        return $this->respondSuccess($concept);
    }

    /**
     * Find a concept by name
     */
    public function viewByName(string $name)
    {
        //Find the concept
        $concept = Concept::where('name', $name)->first();

        //If the concept is not found, return a response
        if (null === $concept) {
            return $this->respondNotFound('Concept not found');
        }

        //Return the response
        return $this->respondSuccess($concept);
    }

    /**
     * Create a new concept
     */
    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'name' => 'required|string|unique:concepts,name',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Create the concept
        $concept = Concept::create($data);

        //Return the response with the status code 201
        return $this->respondSuccess($concept, 201);
    }

    /**
     * Update a concept
     */
    public function update(Request $request, string $id)
    {
        //Validation rules
        $rules = [
            'name' => 'string|unique:concepts,name',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Find the concept
        $concept = Concept::find($id);

        //If the concept is not found, return a response
        if (null === $concept) {
            return $this->respondNotFound('concept not found');
        }

        //Update the concept
        $concept->update($data);

        //Find invoices that use this concept and update them
        Invoice::where('concept', $concept->getOriginal('name'))
            ->update(['concept' => $concept->name]);

        //Find suppliers that use this concept and update them
        Supplier::where('concept', $concept->getOriginal('name'))
            ->update(['concept' => $concept->name]);

        //Return the response with the status code 200
        return $this->respondSuccess($concept);
    }

    /**
     * Delete a concept
     */
    public function delete(string $id)
    {
        //Find the concept
        $concept = Concept::find($id);

        //If the concept is not found, return a response
        if (null === $concept) {
            return $this->respondNotFound('Concept not found');
        }

        //Check if the concept is used in some invoice
        if (Invoice::where('concept', $concept->name)->exists()) {
            return $this->respondError('Concept cannot be deleted because it is used in some invoice', 400);
        }

        //Delete the concept
        $concept->delete();

        //Return the response with the status code 200
        return $this->respondSuccess('Concept deleted successfully');
    }
}
