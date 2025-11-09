<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\BaseRepositoryInterface;

class TransactionRepository extends BaseRepository implements BaseRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function findByNumeroTransaction(string $numero): ?Transaction
    {
        return $this->model->where('numero_transaction', $numero)->first();
    }

    public function getTransactionsByCompte(string $compteId, array $filters = [], int $page = 1, int $limit = 10)
    {
        $query = $this->model->where('compte_id', $compteId)
            ->with(['compte.client.user', 'createur'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (isset($filters['date_debut'])) {
            $query->whereDate('created_at', '>=', $filters['date_debut']);
        }

        if (isset($filters['date_fin'])) {
            $query->whereDate('created_at', '<=', $filters['date_fin']);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function getSoldeByCompte(string $compteId): float
    {
        $debits = $this->model->where('compte_id', $compteId)
            ->where('statut', 'VALIDE')
            ->whereIn('type', ['RETRAIT', 'TRANSFERT'])
            ->sum('montant');

        $credits = $this->model->where('compte_id', $compteId)
            ->where('statut', 'VALIDE')
            ->where('type', 'DEPOT')
            ->sum('montant');

        return $credits - $debits;
    }
}