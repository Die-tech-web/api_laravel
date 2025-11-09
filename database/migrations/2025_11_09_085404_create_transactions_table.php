<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_transaction')->unique();
            $table->foreignUuid('compte_id')->constrained('comptes');
            $table->enum('type', ['DEPOT', 'RETRAIT', 'TRANSFERT']);
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3);
            $table->text('description')->nullable();
            $table->decimal('solde_avant', 15, 2)->default(0);
            $table->decimal('solde_apres', 15, 2)->default(0);
            $table->enum('statut', ['EN_ATTENTE', 'VALIDE', 'ANNULE'])->default('EN_ATTENTE');
            $table->foreignUuid('cree_par')->constrained('users');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
