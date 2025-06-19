<?php

namespace App\Http\Controllers;

use App\Models\ObjectiveAndResult;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ObjectiveAndResultController extends ResponseController
{
    use ValidateRequest;

    public function viewYearlyObjectivesAndResults(Request $request): JsonResponse
    {
        
        //Validarion rules
        $rules = [
            'id_business_line' => 'required|exists:business_lines,_id',
            'id_center' => 'sometimes|exists:centers,_id',
            'year' => 'required|string|max:9',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);
        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Validate the year format YYYY or YYYY/YYYY
        if (!preg_match('/^\d{4}\/\d{4}$/', $data['year'])) {
            return $this->respondBadRequest('Invalid year format. Please provide a valid year in YYYY/YYYY format.');
        }

        //If id_business_line or id_center is not provided, set them to null
        $id_business_line =  $data['id_business_line'] ?? null;
        $id_center = $data['id_center'] ?? null;

        //Separate the year into start and end
        list($inicio, $fin) = explode('/', $data['year']);

        //Subtract 1 from the start and end years to get the previous year
        $anioAnterior = ($inicio - 1) . '/' . ($fin - 1);

        //Fetch the objectives and results for the specified year
        $query = ObjectiveAndResult::where('year', $data['year']);
        $queryYearBefore = ObjectiveAndResult::where('year', $anioAnterior);

        //Filter by business line
        $query->where('id_business_line', $id_business_line);
        $queryYearBefore->where('id_business_line', $id_business_line);

        //If a center ID is provided, filter by it
        if ($id_center !== null) {
            $query->where('id_center', $id_center);
            $queryYearBefore->where('id_center', $id_center);
        }

        //Execute the query to get the objectives and results
        $objectivesAndResults = $query->get();
        $objectivesAndResultsYearBefore = $queryYearBefore->get();

        // If no data found, return a not found response
        if ($objectivesAndResults->isEmpty()) {
            return $this->respondNotFound('No objectives and results found for the specified year and business line.');
        }

        //If objectivesAndResultsYearBefore is not empty, add results from the previous year
        if (!$objectivesAndResultsYearBefore->isEmpty()) {
            foreach ($objectivesAndResults as $objectiveAndResult) {
                $previousYearData = $objectivesAndResultsYearBefore->firstWhere('id_business_line', $objectiveAndResult->id_business_line);
                if ($previousYearData) {
                    //Unset the results_year_before field if it exists
                    unset($objectiveAndResult->results['results_year_before']);
                    $objectiveAndResult->results['results_previous_year'] = $previousYearData->results;
                    $objectiveAndResult->results['exist_previous'] = true;
                } else {
                    $objectiveAndResult->results['exist_previous'] = false;
                }
            }
        }

        // Return the data with a success response
        return $this->respondSuccess($objectivesAndResults);
    }

    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'id_business_line' => 'required|exists:business_lines,_id',
            'id_center' => 'sometimes|exists:centers,_id',
            'factures_year_before' => 'sometimes|array',
            'results_year_before' => 'sometimes|array',
            'results' => 'required|array',
            'projected_growth' => 'required|numeric',
            'factures_1' => 'sometimes|array',
            'factures_2' => 'sometimes|array',
            'year' => 'required|string|max:9',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Check if year is in YYYY/YYYY format
        if (!preg_match('/^\d{4}\/\d{4}$/', $data['year'])) {
            return $this->respondUnprocessableEntity('Invalid year format. Please provide a valid year in YYYY/YYYY format.');
        }

        //Separate the year into start and end
        list($inicio, $fin) = explode('/', $data['year']);

        //Subtract 1 from the start and end years to get the previous year
        $anioAnterior = ($inicio - 1) . '/' . ($fin - 1);

        //Check if the are results from the previous year from the bd
        $query = ObjectiveAndResult::where('id_business_line', $data['id_business_line'])
            ->where('year', $anioAnterior);
        if (isset($data['id_center'])) {
            $query->where('id_center', $data['id_center']);
        }
        $existingObjectiveAndResultYearBefore = $query->first();

        //If an existing record is found, ignore the results_year_before and factures_year_before fields
        if ($existingObjectiveAndResultYearBefore) {
            unset($data['results_year_before']);
            unset($data['factures_year_before']);
        }

        //Create the new ObjectiveAndResult record
        $objectiveAndResult = ObjectiveAndResult::create($data);

        //Return the created record with a success response
        return $this->respondSuccess($objectiveAndResult);
    }

    public function update(Request $request, string $id)
    {
        //Find the existing record
        $objectiveAndResult = ObjectiveAndResult::find($id);

        //If not found, return a not found response
        if (!$objectiveAndResult) {
            return $this->respondNotFound('Objective and result not found.');
        }

        //Validation rules
        $rules = [
            'id_business_line' => 'sometimes|exists:business_lines,_id',
            'id_center' => 'sometimes|exists:centers,_id',
            'factures_year_before' => 'sometimes|array',
            'results_year_before' => 'sometimes|array',
            'results' => 'sometimes|array',
            'projected_growth' => 'sometimes|numeric',
            'factures_1' => 'sometimes|array',
            'factures_2' => 'sometimes|array',
            'year' => 'sometimes|string|max:9', // YYYY/YYYY format
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if ($data instanceof JsonResponse) {
            return $data;
        }

        //Separate the year into start and end
        list($inicio, $fin) = explode('/', $data['year']);

        //Subtract 1 from the start and end years to get the previous year
        $anioAnterior = ($inicio - 1) . '/' . ($fin - 1);

        //Check if the are results from the previous year from the bd
        $query = ObjectiveAndResult::where('id_business_line', $data['id_business_line'])
            ->where('year', $anioAnterior);
        if (isset($data['id_center'])) {
            $query->where('id_center', $data['id_center']);
        }
        $existingObjectiveAndResultYearBefore = $query->first();

        //If an existing record is found, ignore the results_year_before field
        if ($existingObjectiveAndResultYearBefore) {
            unset($data['results_year_before']);
        }

        //Update the record with the validated data
        $objectiveAndResult->update($data);

        //Return the updated record with a success response
        return $this->respondSuccess($objectiveAndResult);
    }
    
    public function delete(string $id)
    {
        //Find the existing record
        $objectiveAndResult = ObjectiveAndResult::find($id);

        //If not found, return a not found response
        if (!$objectiveAndResult) {
            return $this->respondNotFound('Objective and result not found.');
        }

        //Delete the record
        $objectiveAndResult->delete();

        //Return a success response
        return $this->respondNoContent();
    }
}
