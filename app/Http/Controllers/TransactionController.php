<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="numero_transaction", type="string"),
 *     @OA\Property(property="compte_id", type="integer"),
 *     @OA\Property(property="type", type="string", enum={"depot", "retrait", "virement"}),
 *     @OA\Property(property="montant", type="number"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="statut", type="string", enum={"en_attente", "validee", "annulee"}),
 *     @OA\Property(property="date_creation", type="string", format="date-time")
 * )
 */

class TransactionController extends Controller
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @OA\Post(
     *     path="/api/transactions",
     *     tags={"Transactions"},
     *     summary="Créer une nouvelle transaction",
     *     description="Créer une nouvelle transaction bancaire",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_id","type","montant","description"},
     *             @OA\Property(property="compte_id", type="integer", description="ID du compte"),
     *             @OA\Property(property="type", type="string", enum={"depot", "retrait", "virement"}, description="Type de transaction"),
     *             @OA\Property(property="montant", type="number", description="Montant de la transaction"),
     *             @OA\Property(property="description", type="string", description="Description de la transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction", ref="#/components/schemas/Transaction")
     *             )
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
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->transactionService->createTransaction($data);

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction' => [
                        'id' => $result['transaction']->id,
                        'numero_transaction' => $result['transaction']->numero_transaction,
                        'compte_id' => $result['transaction']->compte_id,
                        'compte_numero' => $result['compte']->num_compte,
                        'titulaire' => $result['transaction']->compte->client->user->name,
                        'type' => $result['transaction']->type,
                        'montant' => $result['transaction']->montant,
                        'montant_formatte' => $result['transaction']->montant_formatte,
                        'solde_avant' => $result['solde_avant'],
                        'solde_apres' => $result['solde_apres'],
                        'description' => $result['transaction']->description,
                        'statut' => $result['transaction']->statut,
                        'date_creation' => $result['transaction']->date_creation->toISOString(),
                        'cree_par' => $result['transaction']->createur->name
                    ]
                ],
                'message' => 'Transaction créée avec succès'
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Erreur lors de la création de la transaction', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     tags={"Transactions"},
     *     summary="Lister les transactions",
     *     description="Récupérer la liste des transactions avec filtres optionnels",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="compte_id",
     *         in="query",
     *         @OA\Schema(type="integer"),
     *         description="Filtrer par ID de compte"
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         @OA\Schema(type="string", enum={"depot", "retrait", "virement"}),
     *         description="Filtrer par type de transaction"
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         @OA\Schema(type="string", enum={"en_attente", "validee", "annulee"}),
     *         description="Filtrer par statut"
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
     *         description="Liste des transactions récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Transaction"))
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
            $this->authorize('viewAny', Transaction::class);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
                'error' => 'Vous n\'avez pas les permissions nécessaires pour voir les transactions'
            ], 403);
        }

        // Filtres
        $filters = $request->only([
            'compte_id',
            'type',
            'statut',
            'date_debut',
            'date_fin',
        ]);

        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        $transactions = $this->transactionService->list($filters, $page, $limit);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/transactions/{transaction}",
     *     tags={"Transactions"},
     *     summary="Afficher une transaction",
     *     description="Récupérer les détails d'une transaction spécifique",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="transaction",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID de la transaction"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la transaction",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction non trouvée"
     *     )
     * )
     */
    public function show(Transaction $transaction): JsonResponse
    {
        try {
            $this->authorize('view', $transaction);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
                'error' => 'Vous n\'avez pas les permissions nécessaires pour voir cette transaction'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction->load(['compte.client.user', 'createur']),
        ]);
    }
}
