<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompteRequest;
use App\Models\Compte;
use App\Models\User;
use App\Services\CompteService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="num_compte", type="string"),
 *     @OA\Property(property="type_compte", type="string", enum={"courant", "epargne"}),
 *     @OA\Property(property="solde", type="number"),
 *     @OA\Property(property="devise", type="string"),
 *     @OA\Property(property="status", type="string", enum={"actif", "inactif"})
 * )
 */

class CompteController extends Controller
{


    private CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * @OA\Get(
     *     path="/api/comptes",
     *     tags={"Comptes"},
     *     summary="Lister les comptes",
     *     description="Récupérer la liste des comptes avec filtres optionnels",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         @OA\Schema(type="string", enum={"actif", "inactif"}),
     *         description="Filtrer par statut du compte"
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Filtrer par type de compte"
     *     ),
     *     @OA\Parameter(
     *         name="solde_min",
     *         in="query",
     *         @OA\Schema(type="number"),
     *         description="Solde minimum"
     *     ),
     *     @OA\Parameter(
     *         name="solde_max",
     *         in="query",
     *         @OA\Schema(type="number"),
     *         description="Solde maximum"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer", default=1),
     *         description="Numéro de page"
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         @OA\Schema(type="integer", default=10),
     *         description="Nombre d'éléments par page"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Compte::class);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
                'error' => 'Vous n\'avez pas les permissions nécessaires pour voir les comptes'
            ], 403);
        }

        // Récupère uniquement les filtres envoyés par l'utilisateur
        $filters = $request->only([
            'status',
            'type',
            'solde_min',
            'solde_max',
            'date_debut',
            'date_fin',
        ]);

        // Valeur par défaut si 'status' n'est pas défini
        $filters['status'] = $filters['status'] ?? 'actif';

        $user = auth()->user();

        // Si l'utilisateur est un client, ajouter le filtre client_id APRÈS avoir récupéré les autres filtres
        if ($user && $user->client) {
            $filters['client_id'] = $user->client->id;
        }

        // Pagination
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        // Délégation au service
        $comptes = $this->compteService->list($filters, $page, $limit);

        return response()->json([
            'success' => true,
            'data' => $comptes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreCompteRequest $request)
    // {
    //     // $this->authorize('create', Compte::class);
    //     try
    //     {
    //         $data = $request->validated();
    //         $result = $this->compteService->createCompte($data);
    //         return response()->json([
    //             'message' => 'Compte créé avec succès.',
    //             'compte' => $result['compte'],
    //             'client' => $result['client'],
    //             'user' => $result['user'],

    //         ], 201);
    //     } catch (\Throwable $e)
    //     {
    //         return response()->json(['message' => 'Erreur lors de la création du compte','error' => $e->getMessage()], 500);
    //     }
    // }

    // public function store(StoreCompteRequest $request)
    // {
    //     try
    //     {

    //         $this->authorize('create', Compte::class);
    //         $data = $request->validated();

    //         $result = $this->compteService->createCompte($data);

    //         if (!$result) {
    //             throw new \Exception('Aucun résultat retourné par le service');
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Compte créé avec succès.',
    //             'compte' => $result['compte'],
    //             'client' => $result['client'],
    //             'user' => $result['user'],
    //         ], 201, [], JSON_PRETTY_PRINT);
    //     }
    //     catch (\Throwable $e)
    //     {

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur lors de la création du compte',
    //             'error' => $e->getMessage()
    //         ], 500, [], JSON_PRETTY_PRINT);
    //     }
    // }


    /**
     * @OA\Post(
     *     path="/api/comptes",
     *     tags={"Comptes"},
     *     summary="Créer un nouveau compte",
     *     description="Créer un nouveau compte bancaire",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id","type_compte","devise"},
     *             @OA\Property(property="client_id", type="integer", description="ID du client"),
     *             @OA\Property(property="type_compte", type="string", enum={"courant", "epargne"}, description="Type de compte"),
     *             @OA\Property(property="devise", type="string", default="XAF", description="Devise du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="compte", ref="#/components/schemas/Compte"),
     *             @OA\Property(property="client", type="object"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     )
     * )
     */
    public function store(StoreCompteRequest $request)
    {
        try {
            // Gestion de l'autorisation avec try-catch
            try {
                $this->authorize('create', Compte::class);
            } catch (AuthorizationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                    'error' => 'Vous n\'avez pas les permissions nécessaires'
                ], 403);
            }

            $data = $request->validated();
            $result = $this->compteService->createCompte($data);

            if (!$result) {
                throw new \Exception('Aucun résultat retourné par le service');
            }

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès.',
                'compte' => $result['compte'],
                'client' => $result['client'],
                'user' => $result['user'],
            ], 201, [], JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
