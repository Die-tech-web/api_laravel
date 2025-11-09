<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Créer une nouvelle transaction
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
     * Afficher la liste des transactions
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
     * Afficher une transaction spécifique
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
