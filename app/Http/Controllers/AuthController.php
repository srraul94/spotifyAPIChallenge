<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle the registration of a new user.
     *
     * Validates the input data, creates a new user in the database, and generates
     * an auth token for the registered user.
     *
     * @param Request $request The HTTP request instance containing user data.
     * @return array An array with the created user and the authentication token.
     * @throws ValidationException If validation fails.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Handle the login process for an existing user.
     *
     * Validates the input data, verifies the user's credentials,
     * and generates an authentication token if the credentials are valid.
     * Returns an error response if the credentials are invalid.
     *
     * @param Request $request The HTTP request instance containing login data.
     * @return array|Illuminate\Http\Response An array with the authenticated user and the authentication token, or an error response on failure.
     * @throws ValidationException If validation fails.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !\Hash::check($data['password'], $user->password)) {
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Retrieve the authenticated user from the request.
     *
     * Returns the currently authenticated user based on the provided HTTP request.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Contracts\Auth\Authenticatable|null The authenticated user or null if not authenticated.
     */
    public function getUser(Request $request)
    {
        return $request->user();
    }


}
