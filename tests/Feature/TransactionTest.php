<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $client;
    private $compte;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un admin
        $adminUser = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin = Admin::factory()->create(['user_id' => $adminUser->id]);

        // Créer un client
        $clientUser = User::factory()->create(['email' => 'client@test.com']);
        $this->client = Client::factory()->create(['user_id' => $clientUser->id]);

        // Créer un compte pour le client
        $this->compte = Compte::factory()->create([
            'client_id' => $this->client->id,
            'num_compte' => 'C001456',
            'type' => 'epargne',
            'devise' => 'XOF',
            'status' => 'actif'
        ]);
    }

    public function test_admin_can_create_depot_transaction()
    {
        $this->actingAs($this->admin->user, 'api');

        $transactionData = [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 500000,
            'devise' => 'XOF',
            'description' => 'Dépôt espèces guichet',
            'validation_automatique' => true
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Transaction créée avec succès'
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'transaction' => [
                            'id',
                            'numero_transaction',
                            'compte_id',
                            'compte_numero',
                            'titulaire',
                            'type',
                            'montant',
                            'montant_formatte',
                            'solde_avant',
                            'solde_apres',
                            'description',
                            'statut',
                            'date_creation',
                            'cree_par'
                        ]
                    ],
                    'message'
                ]);

        // Vérifier que la transaction a été créée en base
        $this->assertDatabaseHas('transactions', [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 500000,
            'statut' => 'VALIDE'
        ]);
    }

    public function test_client_can_create_depot_transaction()
    {
        $this->actingAs($this->client->user, 'api');

        $transactionData = [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 100000,
            'devise' => 'XOF',
            'description' => 'Dépôt client',
            'validation_automatique' => false
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'transaction' => [
                            'statut' => 'EN_ATTENTE'
                        ]
                    ]
                ]);
    }

    public function test_transaction_validation_fails_with_invalid_data()
    {
        $this->actingAs($this->admin->user, 'api');

        $invalidData = [
            'compte_id' => 'invalid',
            'type' => 'INVALID_TYPE',
            'montant' => -100,
            'devise' => 'INVALID',
        ];

        $response = $this->postJson('/api/transactions', $invalidData);

        $response->assertStatus(500); // Le service lance une exception qui est catchée en 500
    }

    public function test_unauthenticated_user_cannot_create_transaction()
    {
        $transactionData = [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 50000,
            'devise' => 'XOF',
        ];

        $response = $this->postJson('/api/transactions', $transactionData);

        $response->assertStatus(401);
    }

    public function test_transaction_creates_unique_numero_transaction()
    {
        $this->actingAs($this->admin->user, 'api');

        // Créer deux transactions
        $data1 = [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 100000,
            'devise' => 'XOF',
            'validation_automatique' => true
        ];

        $data2 = [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 200000,
            'devise' => 'XOF',
            'validation_automatique' => true
        ];

        $this->postJson('/api/transactions', $data1);
        $this->postJson('/api/transactions', $data2);

        // Vérifier que les numéros sont différents
        $transactions = Transaction::all();
        $this->assertCount(2, $transactions);
        $this->assertNotEquals($transactions[0]->numero_transaction, $transactions[1]->numero_transaction);
    }

    public function test_depot_increases_account_balance()
    {
        $this->actingAs($this->admin->user, 'api');

        // Solde initial devrait être 0
        $this->assertEquals(0, $this->compte->fresh()->solde_actuel);

        // Créer un dépôt
        $this->postJson('/api/transactions', [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 300000,
            'devise' => 'XOF',
            'validation_automatique' => true
        ]);

        // Vérifier que le solde a augmenté
        $this->assertEquals(300000, $this->compte->fresh()->solde_actuel);
    }

    public function test_retrait_decreases_account_balance()
    {
        $this->actingAs($this->admin->user, 'api');

        // D'abord créer un dépôt
        $this->postJson('/api/transactions', [
            'compte_id' => $this->compte->id,
            'type' => 'DEPOT',
            'montant' => 500000,
            'devise' => 'XOF',
            'validation_automatique' => true
        ]);

        // Ensuite créer un retrait
        $this->postJson('/api/transactions', [
            'compte_id' => $this->compte->id,
            'type' => 'RETRAIT',
            'montant' => 200000,
            'devise' => 'XOF',
            'validation_automatique' => true
        ]);

        // Vérifier que le solde a diminué
        $this->assertEquals(300000, $this->compte->fresh()->solde_actuel);
    }
}
