<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compte extends Model
{
    use HasFactory, HasUuids;

      protected $fillable = [
        'id',          
        'num_compte',
        'devise',
        'status',
        'client_id',
        'type'   
    ];

   

    public function client():BelongsTo{
        return $this->belongsTo(Client::class);
    }

    protected $appends = ['solde_actuel'];


    public function getSoldeActuelAttribute(): float
    {
        return $this->calculerSolde();
    }

    public function calculerSolde(): float
    {
        // Calculer le solde basé sur les transactions validées
        $debits = $this->transactions()
            ->where('statut', 'VALIDE')
            ->whereIn('type', ['RETRAIT', 'TRANSFERT'])
            ->sum('montant');

        $credits = $this->transactions()
            ->where('statut', 'VALIDE')
            ->where('type', 'DEPOT')
            ->sum('montant');

        return $credits - $debits;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}
