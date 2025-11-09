<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    
   public function loginService(LoginRequest $request)
   {
        $validatedData = $request->validated();

        $user = User::where('email', $validatedData["email"])->first();

        if(!$user || !Hash::check($validatedData['password'], $user->password))
        {
             throw new \Exception('Email ou mot de passe incorrect', 401);
        }

        // Créer un token avec Passport
        try {
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->accessToken;
        } catch (\Exception $tokenException) {
            throw new \Exception('Erreur lors de la création du token: ' . $tokenException->getMessage(), 500);
        }

         return [
            'data' => [
                'id' => $user->id,
                'titulaire' => $user->titulaire,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];

   }
}
