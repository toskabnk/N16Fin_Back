<?php

namespace App\Http\Controllers;

use App\Models\ObjectiveAndResult;
use App\Models\Year;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YearController extends ResponseController
{
    use ValidateRequest;

    /**
     * Get all the years
     */
    public function viewAll(Request $request)
    {
        // Get all the years
        $years = Year::all();

        // Return the response with the status code 200
        return $this->respondSuccess($years);
    }
    /**
     * Find a year by id
     */
    public function view(string $id)
    {
        // Find the year
        $year = Year::find($id);

        // If the year is not found, return a response
        if (null === $year) {
            return $this->respondNotFound('Year not found');
        }

        // Return the response
        return $this->respondSuccess($year);
    }

    /** 
     * Get the current year
     */

    public function viewCurrentYear()
    {
        // Get the current year
        if (date('n') < 9) {
            // If the current month is before September, use the previous year
            $currentYear = Year::where('year', (date('Y') - 1) . '/' . date('Y'))->first();
        } else {
            // If the current month is September or later, use the current year
            $currentYear = Year::where('year', date('Y') . '/' . (date('Y') + 1))->first();
        }

        // If the current date is september or later and the current year does not exist, create it
        if (date('n') >= 9 && !$currentYear) {
            $currentYear = Year::create(['year' => date('Y') . '/' . (date('Y') + 1)]);
        }

        // Return the response with the current year
        return $this->respondSuccess($currentYear);
    }

    /**
     * Create a new year
     */
    public function create(Request $request)
    {
        // Validation rules
        $rules = [
            'year' => 'required|string|regex:/^\d{4}\/\d{4}$/',
        ];

        // Check if the year already exists
        $existingYear = Year::where('year', $request->input('year'))->first();
        if ($existingYear) {
            return $this->respondBadRequest('The year already exists.');
        }

        // Validate request
        $data = $this->validateData($request, $rules);

        // If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        // Create the year
        $year = Year::create($data);

        // Return the response with the status code 201
        return $this->respondSuccess($year);
    }

    /**
     * Update a year
     */
    public function update(Request $request, string $id)
    {
        // Find the existing year
        $year = Year::find($id);

        // If not found, return a not found response
        if (!$year) {
            return $this->respondNotFound('Year not found.');
        }

        // Validation rules
        $rules = [
            'year' => 'REQUIRED|string|regex:/^\d{4}\/\d{4}$/',
        ];

        // Validate request
        $data = $this->validateData($request, $rules);

        // If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        // Check if the year already exists
        $existingYear = Year::where('year', $request->input('year'))->first();
        if ($existingYear) {
            return $this->respondBadRequest('The year already exists.');
        }

        // Update the year with the validated data
        $year->update($data);

        // Return the updated record with a success response
        return $this->respondSuccess($year);
    }

    /**
     * Delete a year
     */
    public function delete(string $id)
    {
        // Find the existing year
        $year = Year::find($id);

        // If not found, return a not found response
        if (!$year) {
            return $this->respondNotFound('Year not found.');
        }

        $objectiveAndResults = ObjectiveAndResult::where('year', $year->year)->get();
        // Check if the year has associated objectives and results
        if ($objectiveAndResults->isNotEmpty()) {
            // If there are associated objectives and results, return a bad request response
            return $this->respondBadRequest('Cannot delete year with associated objectives and results.');
        }

        // Delete the year
        $year->delete();

        // Return a success response
        return $this->respondSuccess(null, 204);
    }
}
