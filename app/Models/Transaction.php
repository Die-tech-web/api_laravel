<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_transaction',
        'compte_id',
        'type',
        'montant',
        'devise',
        'description',
        'solde_avant',
        'solde_apres',
        'statut',
        'cree_par',
        'date_creation',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'solde_avant' => 'decimal:2',
        'solde_apres' => 'decimal:2',
        'date_creation' => 'datetime',
    ];

    protected $appends = [
        'montant_formatte',
    ];

    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function getMontantFormatteAttribute(): string
    {
        $signe = in_array($this->type, ['DEPOT']) ? '+' : '-';
        return $signe . number_format($this->montant, 0, ',', ' ') . ' FCFA';
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'EN_ATTENTE');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'VALIDE');
    }

    public function scopeAnnule($query)
    {
        return $query->where('statut', 'ANNULE');
    }
}
