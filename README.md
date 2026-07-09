# HealthCenter — Guide d'installation

## Prérequis
- PHP >= 8.2
- Composer 2+
- PostgreSQL 15+
- Node.js 18+ (pour Tailwind en prod)

## Installation

### 1. Cloner et installer les dépendances
```bash
git clone https://github.com/votre-repo/healthcenter.git
cd healthcenter
composer install
```

### 2. Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Éditez `.env` et configurez vos paramètres PostgreSQL :
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=health_center
DB_USERNAME=postgres
DB_PASSWORD=votre_mot_de_passe
```

### 3. Base de données
Créez la base dans PostgreSQL :
```sql
CREATE DATABASE health_center;
```

Option A — Utiliser le schéma SQL fourni (recommandé) :
```bash
psql -U postgres -d health_center -f health_center_db.sql
php artisan db:seed
```

Option B — Utiliser les migrations Laravel :
```bash
php artisan migrate --seed
```

### 4. Publication des packages
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 5. Enregistrer les Observers dans AppServiceProvider
```php
// app/Providers/AppServiceProvider.php
use App\Models\Stock;
use App\Models\Invoice;
use App\Observers\StockObserver;
use App\Observers\InvoiceObserver;

public function boot(): void
{
    Stock::observe(StockObserver::class);
    Invoice::observe(InvoiceObserver::class);
}
```

### 6. Enregistrer les Middlewares dans bootstrap/app.php (Laravel 11)
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'audit'      => \App\Http\Middleware\AuditLogger::class,
        'check.role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

### 7. Storage link
```bash
php artisan storage:link
```

### 8. Lancer le serveur
```bash
php artisan serve
```

Accédez à : http://localhost:8000

## Comptes de test (après seeding)

| Rôle              | Username        | Mot de passe |
|-------------------|-----------------|--------------|
| Administrateur    | admin           | password     |
| Médecin généraliste| dr.martin      | password     |
| Pharmacien        | pharmacien      | password     |
| Caissier          | caissier        | password     |
| Réceptionniste    | receptionniste  | password     |
| Directeur         | directeur       | password     |

## Structure des fichiers générés

```
app/
├── Models/          (31 modèles Eloquent)
├── Services/        (6 services métier)
├── Traits/          HasAuditLog, GeneratesCode
├── Observers/       StockObserver, InvoiceObserver
├── Exceptions/      PatientNotFoundException, InsufficientStockException
├── Http/
│   ├── Controllers/ (par module)
│   ├── Middleware/  AuditLogger, CheckRole
│   └── Requests/    (validation par module)
resources/views/
├── layouts/         app, auth, partials
├── auth/            login
├── dashboard/       index (role-aware)
├── patients/        index, show (dossier complet à onglets)
├── pharmacy/stock/  index (avec badges stock bas)
└── billing/invoices/show (facture imprimable)
routes/web.php       (toutes les routes par rôle)
```

## Commandes artisan utiles
```bash
php artisan queue:work          # Traiter les jobs en file
php artisan schedule:run        # Lancer le planificateur
php artisan tinker              # Console interactive
php artisan route:list          # Lister toutes les routes
php artisan model:show Patient  # Inspecter un modèle
```
