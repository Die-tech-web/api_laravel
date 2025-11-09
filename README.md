<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.
## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# 🏦 API Documentation - Système Bancaire

## 📋 Vue d'ensemble

Cette API permet de gérer un système bancaire complet avec authentification, gestion des comptes et transactions.

## 🔐 Authentification

### Connexion
**POST** `/api/login`

**Body:**
```json
{
  "email": "admin@test.com",
  "password": "password123"
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
  }
}
```

---

## 💰 Transactions

### Créer une transaction
**POST** `/api/transactions`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body - Dépôt:**
```json
{
  "compte_id": "01HQXYZ123456789ABCDEF",
  "type": "DEPOT",
  "montant": 500000,
  "devise": "XOF",
  "description": "Dépôt espèces guichet",
  "validation_automatique": true
}
```

**Body - Retrait:**
```json
{
  "compte_id": "01HQXYZ123456789ABCDEF",
  "type": "RETRAIT",
  "montant": 200000,
  "devise": "XOF",
  "description": "Retrait DAB",
  "validation_automatique": true
}
```

**Body - Transfert:**
```json
{
  "compte_id": "01HQXYZ123456789ABCDEF",
  "type": "TRANSFERT",
  "montant": 100000,
  "devise": "XOF",
  "description": "Transfert vers compte épargne",
  "validation_automatique": false
}
```

**Réponse (201):**
```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": 1,
      "numero_transaction": "TR20251109-0001",
      "compte_id": "01HQXYZ123456789ABCDEF",
      "compte_numero": "C001456",
      "titulaire": "John Doe",
      "type": "DEPOT",
      "montant": 500000,
      "montant_formatte": "+500 000 FCFA",
      "solde_avant": 1000000,
      "solde_apres": 1500000,
      "description": "Dépôt espèces guichet",
      "statut": "VALIDE",
      "date_creation": "2025-11-09T09:15:00.000000Z",
      "cree_par": "Admin User"
    }
  },
  "message": "Transaction créée avec succès"
}
```

### Lister les transactions
**GET** `/api/transactions`

**Headers:**
```
Authorization: Bearer {token}
```

**Paramètres optionnels:**
- `page=1` - Page de pagination
- `limit=10` - Nombre d'éléments par page
- `compte_id={id}` - Filtrer par compte
- `type=DEPOT` - Filtrer par type (DEPOT, RETRAIT, TRANSFERT)
- `statut=VALIDE` - Filtrer par statut (EN_ATTENTE, VALIDE, ANNULE)

**Exemple:** `/api/transactions?page=1&limit=5&compte_id=01HQXYZ123456789ABCDEF`

### Afficher une transaction
**GET** `/api/transactions/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

---

## 📊 Comptes

### Créer un compte
**POST** `/api/comptes`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "cni": "123456789",
  "telephone": "771234567",
  "type": "epargne",
  "devise": "XOF"
}
```

### Lister les comptes
**GET** `/api/comptes`

**Headers:**
```
Authorization: Bearer {token}
```

**Paramètres optionnels:**
- `page=1` - Page de pagination
- `limit=10` - Nombre d'éléments par page
- `status=actif` - Filtrer par statut
- `type=epargne` - Filtrer par type

---

## ⚠️ Codes d'erreur

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Accès non autorisé",
  "error": "Vous n'avez pas les permissions nécessaires"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "The compte_id field must exist in comptes. (and X more errors)",
  "errors": {
    "compte_id": ["The compte_id field must exist in comptes."],
    "type": ["The type field must be one of: DEPOT, RETRAIT, TRANSFERT."]
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Erreur lors de la création de la transaction",
  "error": "Détails de l'erreur"
}
```

---

## 🔑 Rôles et Permissions

- **Admin**: Peut créer et voir toutes les transactions et comptes
- **Client**: Peut créer des transactions sur ses propres comptes et voir ses propres transactions

---

## 📝 Notes importantes

1. Tous les endpoints nécessitent une authentification sauf `/api/login`
2. Les transactions avec `validation_automatique: true` sont automatiquement validées
3. Le solde des comptes est calculé dynamiquement basé sur les transactions validées
4. Les numéros de transaction sont générés automatiquement (format: TRYYYYMMDD-XXXX)
5. Les montants sont stockés en centimes pour éviter les problèmes de précision
6. L'API utilise la pagination Laravel pour les listes

---

## 🧪 Tests

Pour exécuter les tests :
```bash
php artisan test
```

Tests spécifiques aux transactions :
```bash
php artisan test --filter=TransactionTest
```
  