<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends ResponseController
{
    /**
     * Validate the request data
     *
     * @param Request $request
     * @param array $rules
     * @return array|JsonResponse
     */
    protected function validateData(Request $request, $rules ){
        //Request validation with the rules
        $validation = Validator::make($request->all(),$rules);

        //If validation fails, send a reponse with the errors
        if($validation->fails())
        {
            return $this->respondUnprocessableEntity('Validation errors', $validation->errors());
        }

        //Save validated data
        return $validation->validated();
    }

    /**
     * Login the user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        //Validation rules
        $rules = [
            'email' => 'required|string',
            'password' => 'required|string',
        ];
        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $this->respondUnauthorized($data);
        }

        //Check credentials with email and username
        if (!Auth::attempt($data)) {
            return $this->respondUnauthorized('Invalid credentials');
        }

        //Get the auth user and generate access token
        /** @var \App\Models\User */
        $currentUser = Auth::user();
        $accessToken = $currentUser->createToken('authToken')->plainTextToken;

        //Return the response
        return response()->json([
            'status' => 'success',
            'user' => $currentUser,
            'authorisation' => [
                'token' => $accessToken,
                'type' => 'bearer',
            ],
        ]);
    }

    /**
     * Logout the user
     *
     * @return JsonResponse
     */
    public function logout()
    {
        //Get the auth user
        $currentUser = Auth::user();

        //If null, respond unauthorized
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Revoke the user's token
        $currentUser->token()->revoke();

        //Return the response
        return $this->respondSuccess('Successfully logged out');
    }
}
