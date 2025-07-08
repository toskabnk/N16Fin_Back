<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ValidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends ResponseController
{
    use ValidateRequest;
    
    /**
     * Create a new user
     */
    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'user_role' => 'required|string',
            'password' => 'required|string',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Generate hash of the password
        $data['password'] = Hash::make($data['password']);

        //Create the user
        $user = User::create($data);

        //Return the response with the status code 201
        return $this->respondSuccess($user, 201);
    }

    /**
     * Find a user by id
     */
    public function view(string $id)
    {
        //Find the user
        $user = User::find($id);

        //If the user is not found, return a response
        if (null === $user) {
            return $this->respondNotFound('User not found');
        }

        //Return the response
        return $this->respondSuccess($user);
    }

    /**
     * Update the password of a user
     */
    public function updatePassword(Request $request, string $id)
    {
        //Validation rules
        $rules = [
            'current_password' => 'required|string',
            'password' => 'required|string',
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        /** @var User $user */
        $user = User::find($id);

        //Check if the user exists
        if ($user === null) {
            return $this->respondNotFound('User not found');
        }

        //Check if the current password is correct
        if (!Hash::check($data['current_password'], $user->password)) {
            return $this->respondUnauthorized('Current password is incorrect');
        }

        //Update the password
        $user->update([
            'password' => Hash::make($data['password']),
        ]);
        $user->save();

        //Return the response
        return $this->respondSuccess("Password updated successfully");
    }

    /**
     * Update a user
     */
    public function update(Request $request, string $id)
    {

        //Validation rules
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'user_role' => 'required|string',
        ];

        //Find the user
        $user = User::find($id);
        if ($user === null) {
            return $this->respondNotFound('User not found');
        }

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        $user->update($data);

        //Return the response
        return $this->respondSuccess($user);
    }

    /**
     * Get all users
     */
    public function viewAll(Request $request)
    {

        //Get the authenticated user
        $user = Auth::user();

        //Query to get the users
        $users = User::query();

        //If the request has a name, get the users with the name
        if ($request->query->get('name')) {
            $users->where('name', 'LIKE', "%" . $request->query->get('name') . "%");
        }

        //Complete the query
        $users = $users->get();

        //Return the response
        return $this->respondSuccess($users);
    }

    /**
     * Delete a user
     */
    public function delete(string $id)
    {
        //Find the user
        $user = User::find($id);

        //If the user is not found, return a response
        if ($user === null) {
            return $this->respondNotFound('User not found');
        }

        //Delete the user
        User::destroy($id);

        //Return the response
        return $this->respondNoContent();
    }

    public function me()
    {
        //Get the authenticated user
        $user = Auth::user();

        //If the user is not found, return a response
        if ($user === null) {
            return $this->respondNotFound('User not found');
        }

        //Return the response
        return $this->respondSuccess($user);
    }
}
