<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(
 *     title="Banque API Documentation",
 *     description="API pour la gestion bancaire - Comptes, Transactions, Authentification",
 *     version="1.0.0"
 * )
 *
 * @OA\Server(
 *     url="https://api-laravel-ym60.onrender.com",
 *     description="Serveur de production"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Serveur de développement local"
 * )
 *
 * @OA\Tag(
 *     name="Authentification",
 *     description="Endpoints d'authentification"
 * )
 *
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 *
 * @OA\Tag(
 *     name="Transactions",
 *     description="Gestion des transactions"
 * )
 *
 * @OA\PathItem(path="/api/login")
 * @OA\PathItem(path="/api/comptes")
 * @OA\PathItem(path="/api/transactions")
 * @OA\PathItem(path="/api/transactions/{transaction}")
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     description="Enter token in format (Bearer <token>)",
 *     name="Authorization",
 *     in="header"
 * )
 */

class AuthController extends Controller
{

    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService; 
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentification"},
     *     summary="Connexion utilisateur",
     *     description="Authentifier un utilisateur et obtenir un token d'accès",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides"
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        try
        {
            $payload = $this->authService->loginService($request);
            return response()->json($payload);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 401);
        }

    }
   

    /**
     * Logout user (revoke token).
     */
    // public function logout(Request $request)
    // {
    //     $request->user()->token()->revoke();

    //     return response()->json(['message' => 'Successfully logged out']);
    // }

    /**
     * Get authenticated user.
     */
    // public function user(Request $request)
    // {
    //     return response()->json($request->user());
    // }
}
