<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Compte;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionService extends BaseService
{
    public function __construct(TransactionRepository $repository)
    {
        parent::__construct($repository);
    }

    public function createTransaction(array $data): array
    {
        $this->validateTransactionData($data);

        try {
            return DB::transaction(function () use ($data) {
                // Récupérer le compte
                $compte = Compte::findOrFail($data['compte_id']);

                // Calculer le solde actuel du compte
                $soldeActuel = $this->calculerSoldeCompte($compte->id);

                // Générer le numéro de transaction
                $numeroTransaction = $this->genererNumeroTransaction();

                // Calculer le nouveau solde
                $nouveauSolde = $this->calculerNouveauSolde($soldeActuel, $data['type'], $data['montant']);

                // Créer la transaction
                $transaction = Transaction::create([
                    'numero_transaction' => $numeroTransaction,
                    'compte_id' => $data['compte_id'],
                    'type' => $data['type'],
                    'montant' => $data['montant'],
                    'devise' => $data['devise'],
                    'description' => $data['description'] ?? null,
                    'solde_avant' => $soldeActuel,
                    'solde_apres' => $nouveauSolde,
                    'statut' => $data['validation_automatique'] ?? false ? 'VALIDE' : 'EN_ATTENTE',
                    'cree_par' => auth()->id(),
                    'date_creation' => now(),
                ]);

                // Charger les relations nécessaires
                $transaction->load(['compte.client.user', 'createur']);

                return [
                    'transaction' => $transaction,
                    'compte' => $compte,
                    'solde_avant' => $soldeActuel,
                    'solde_apres' => $nouveauSolde,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la transaction : ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    private function validateTransactionData(array $data): void
    {
        $required = ['compte_id', 'type', 'montant', 'devise'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ {$field} est requis");
            }
        }

        if (!in_array($data['type'], ['DEPOT', 'RETRAIT', 'TRANSFERT'])) {
            throw new \InvalidArgumentException("Type de transaction invalide");
        }

        if ($data['montant'] <= 0) {
            throw new \InvalidArgumentException("Le montant doit être positif");
        }
    }

    private function calculerSoldeCompte(string $compteId): float
    {
        // Calculer le solde basé sur les transactions validées
        $debits = Transaction::where('compte_id', $compteId)
            ->where('statut', 'VALIDE')
            ->whereIn('type', ['RETRAIT', 'TRANSFERT'])
            ->sum('montant');

        $credits = Transaction::where('compte_id', $compteId)
            ->where('statut', 'VALIDE')
            ->where('type', 'DEPOT')
            ->sum('montant');

        return $credits - $debits;
    }

    private function calculerNouveauSolde(float $soldeActuel, string $type, float $montant): float
    {
        if ($type === 'DEPOT') {
            return $soldeActuel + $montant;
        } elseif (in_array($type, ['RETRAIT', 'TRANSFERT'])) {
            return $soldeActuel - $montant;
        }

        return $soldeActuel;
    }

    private function genererNumeroTransaction(): string
    {
        $date = now()->format('Ymd');
        $count = Transaction::whereDate('created_at', today())->count() + 1;
        return 'TR' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}