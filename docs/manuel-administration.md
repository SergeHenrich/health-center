# Manuel d'utilisation — Module Administration

## HealthCenter — Centre de Santé

---

# 1. Présentation

Le module **Administration** est le centre de contrôle du système HealthCenter. Il est exclusivement accessible aux utilisateurs possédant le rôle **administrator**. Il permet la gestion centralisée des utilisateurs, la configuration des paramètres système, la supervision des journaux d'audit et la gestion des rôles et permissions.

---

# 2. Accès au module

- **Prérequis** : Être connecté avec un compte disposant du rôle `administrator`.
- **Identifiants par défaut** : `admin` / `Admin123`
- Le module est accessible via la barre latérale gauche, dans la section **Administration**.
- L'accès aux routes admin est protégé par le middleware `role:administrator`. Toute tentative d'accès depuis un autre rôle retourne une erreur `403 Accès non autorisé`.

**Barrière de sécurité** : Le middleware `CheckRole` (app/Http/Middleware/CheckRole.php) vérifie que l'utilisateur authentifié possède au moins un des rôles requis. Si l'utilisateur n'est pas authentifié, il est redirigé vers la page de connexion.

---

# 3. Tableau de bord (vue administrateur)

Lorsqu'un administrateur se connecte, le tableau de bord affiche des statistiques globales de l'établissement à travers **6 indicateurs clés** :

| Indicateur | Description | Icône |
|---|---|---|
| **Patients actifs** | Nombre total de patients actifs dans le système | 👤 |
| **RDV aujourd'hui** | Nombre de rendez-vous programmés pour la journée | 📅 |
| **Recette du jour** | Montant total des paiements enregistrés ce jour (en XAF) | 💰 |
| **Stock bas** | Nombre de médicaments dont le stock est inférieur au seuil minimum | 💊 |
| **Factures impayées** | Nombre de factures en souffrance (dépassées) | 📄 |
| **Recette mensuelle** | Montant total des paiements du mois en cours (en XAF) | 📈 |

Ces statistiques sont calculées par la méthode `adminStats()` du `DashboardController`.

---

# 4. Onglet « Utilisateurs » (Gestion des utilisateurs)

**Route** : `/admin/users` — visible uniquement pour le rôle `administrator`.

Cet onglet permet de gérer l'ensemble des comptes utilisateurs du système.

## 4.1 Liste des utilisateurs

La page d'index affiche un tableau paginé (20 utilisateurs par page) avec les colonnes suivantes :

- **Nom complet** (prénom + nom)
- **Email**
- **Nom d'utilisateur**
- **Rôle** (affiché sous forme de badge coloré)
- **Statut** (Actif / Inactif)
- **Date de dernière connexion**
- **Actions** (modifier, activer/désactiver, réinitialiser mot de passe)

### Filtres disponibles

- **Barre de recherche** (`q`) : Recherche par prénom, nom ou email (recherche partielle insensible à la casse).
- **Filtre par rôle** (`role`) : Sélection via une liste déroulante des rôles disponibles.

## 4.2 Création d'un utilisateur

Formulaire avec les champs suivants :

| Champ | Règles de validation |
|---|---|
| **Prénom** (`first_name`) | Requis, max 100 caractères |
| **Nom** (`last_name`) | Requis, max 100 caractères |
| **Email** (`email`) | Requis, format email valide, unique dans la table |
| **Nom d'utilisateur** (`username`) | Requis, min 3 caractères, unique dans la table |
| **Mot de passe** (`password`) | Requis, min 8 caractères, confirmation requise |
| **Téléphone** (`phone`) | Optionnel, max 20 caractères |
| **Rôle** (`role`) | Requis, doit correspondre à un rôle existant dans la base |

À la création, le compte est automatiquement activé (`is_active = true`). Le rôle est assigné via `assignRole()`.

### Messages d'erreur personnalisés

- `email.unique` → « Cet email est déjà utilisé. »
- `username.unique` → « Ce nom d'utilisateur est déjà pris. »
- `password.confirmed` → « La confirmation du mot de passe ne correspond pas. »

## 4.3 Modification d'un utilisateur

Permet de modifier :
- Prénom, nom, téléphone
- Statut actif/inactif (case à cocher)
- Réaffectation du rôle

La modification déclenche `syncRoles()` pour remplacer les rôles existants.

## 4.4 Activation / Désactivation d'un compte

Un bouton d'action permet d'activer ou désactiver un compte utilisateur.

- Un compte **désactivé** ne peut plus se connecter au système.
- La méthode `toggleActive()` alterne entre `activate()` et `deactivate()` sur le modèle User.
- Un message de confirmation est affiché : « Compte activé. » / « Compte désactivé. »

## 4.5 Réinitialisation du mot de passe

Un administrateur peut réinitialiser le mot de passe de n'importe quel utilisateur.

- Le formulaire demande un nouveau mot de passe (min 8 caractères) avec confirmation.
- Appelle `AuthService::changePassword()` qui met à jour le mot de passe et révoque tous les tokens API existants.
- L'utilisateur concerné devra utiliser le nouveau mot de passe à sa prochaine connexion.

---

# 5. Onglet « Paramètres » (Configuration système)

**Route** : `/admin/settings` — visible uniquement pour le rôle `administrator`.

Cet onglet permet de configurer les paramètres globaux du système.

## Fonctionnement

Les paramètres sont stockés dans la table `settings` avec la structure suivante :

| Colonne | Type | Description |
|---|---|---|
| `key` | string (unique) | Identifiant du paramètre |
| `value` | text | Valeur du paramètre |
| `type` | string | Type de donnée (string, boolean, integer, etc.) |
| `group` | string | Groupe de rattachement pour l'organisation |
| `description` | text | Description explicative du paramètre |
| `is_public` | boolean | Visibilité du paramètre |

## Affichage

La page d'index regroupe les paramètres par leur colonne `group`. Chaque groupe est présenté dans une section distincte avec un titre, et chaque paramètre est affiché avec :
- Son nom (clé)
- Sa description
- Un champ de saisie adapté au type du paramètre

## Modification

Tous les paramètres sont regroupés dans un formulaire unique. La soumission du formulaire appelle `SettingController::update()` qui boucle sur l'ensemble des champs et met à jour chaque paramètre via la méthode `Setting::set($key, $value)`.

Un message de confirmation « Paramètres enregistrés. » est affiché après la sauvegarde.

---

# 6. Gestion des rôles et permissions

## 6.1 Rôles disponibles

Le système définit **9 rôles** via le `RoleSeeder` :

| Rôle | Description | Accès admin |
|---|---|---|
| **administrator** | Super-administrateur, accès total au système | ✅ Toutes les routes admin |
| **director** | Direction, vue consolidée en lecture seule | ❌ |
| **general_practitioner** | Médecin généraliste | ❌ |
| **specialist** | Médecin spécialiste | ❌ |
| **nurse** | Personnel infirmier | ❌ |
| **pharmacist** | Pharmacien | ❌ |
| **cashier** | Caissier | ❌ |
| **receptionist** | Réceptionniste | ❌ |
| **patient** | Patient (portail patient) | ❌ |

## 6.2 Permissions administrateur

Permissions spécifiques au module admin définies dans `RoleSeeder` :

| Permission | Description |
|---|---|
| `admin.settings.edit` | Modification des paramètres système |
| `admin.audit.view` | Consultation des journaux d'audit |
| `admin.roles.manage` | Gestion des rôles |

Le rôle **administrator** se voit attribuer **toutes les permissions** du système via la variable `$permissions`.

---

# 7. Journal d'audit (Audit Log)

Le système enregistre de manière automatique toutes les actions critiques via deux mécanismes :

## 7.1 Middleware AuditLogger

Toutes les requêtes HTTP de type `POST`, `PUT`, `PATCH`, `DELETE` passées par un utilisateur authentifié sont enregistrées dans la table `audit_logs` avec :

| Champ | Description |
|---|---|
| `user_id` | Identifiant de l'utilisateur connecté |
| `event` | Méthode HTTP (post, put, patch, delete) |
| `auditable_type` | Toujours `http_request` |
| `auditable_id` | Toujours `0` |
| `new_values` | Données soumises (hors mots de passe et token CSRF) |
| `url` | URL complète de la requête |
| `ip_address` | Adresse IP du client |
| `user_agent` | User-Agent du navigateur |

## 7.2 Trait HasAuditLog

Le trait `HasAuditLog` est utilisé par le modèle `User`. Il s'accroche aux événements Eloquent (`created`, `updated`, `deleted`) et enregistre les modifications apportées au modèle, y compris les valeurs avant/après modification.

## 7.3 Consultation

La permission `admin.audit.view` permet de consulter les journaux d'audit (interface non encore développée).

---

# 8. Système de notifications

Le module admin dispose d'un système de notifications intégrées (table `notifications` et table pivot `notification_user`).

**Accès** : Cloche dans la barre de navigation (visible pour tous les utilisateurs connectés).

**Fonctionnalités** :
- Affichage des 5 dernières notifications dans un menu déroulant
- Compteur de notifications non lues (badge rouge)
- Marqueur visuel : notification non lue en gras, notification lue en gris
- Chaque notification contient un titre et une date relative

---

# 9. Comptes préconfigurés (seeder)

Le `UserSeeder` crée les comptes suivants au déploiement initial :

| Identifiant | Mot de passe | Rôle | Nom complet |
|---|---|---|---|
| `admin` | `Admin123` | administrator | Admin Système |
| `dr.martin` | `Password1` | general_practitioner | Jean Martin |
| `dr.dubois` | `Password1` | specialist | Sophie Dubois |
| `infirmiere.kamga` | `Password1` | nurse | Marie Kamga |
| `pharmacien` | `Password1` | pharmacist | Paul Mbarga |
| `caissier` | `Password1` | cashier | Alice Fotso |
| `receptionniste` | `Password1` | receptionist | David Njoya |
| `directeur` | `Password1` | director | Christine Talla |

---

# 10. Architecture technique

## 10.1 Routes

Les routes admin sont définies dans `routes/web.php` :

```php
Route::middleware('role:administrator')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('settings', SettingController::class)->only(['index', 'update']);
    });
```

## 10.2 Contrôleurs

| Contrôleur | Méthodes | Fonction |
|---|---|---|
| `UserController` | index, create, store, edit, update, toggleActive, resetPassword | Gestion complète des utilisateurs |
| `SettingController` | index, update | Gestion des paramètres système |

## 10.3 Modèles

| Modèle | Attributs clés | Relations |
|---|---|---|
| `User` | first_name, last_name, email, username, password, phone, avatar, is_active, last_login_at, failed_login_attempts, locked_until | roles, permissions, consultations, appointments, auditLogs, notifications |
| `Setting` | key, value, type, group, description, is_public | — |
| `AuditLog` | user_id, event, auditable_type, auditable_id, old_values, new_values, url, ip_address, user_agent | — |

## 10.4 Services

| Service | Méthodes | Utilisation |
|---|---|---|
| `AuthService` | login, logout, createUser, changePassword | Création d'utilisateurs, réinitialisation de mots de passe, connexion avec verrouillage |

## 10.5 Middleware

| Middleware | Rôle |
|---|---|
| `CheckRole` | Vérifie que l'utilisateur authentifié possède un des rôles spécifiés, sinon retourne 403 |
| `AuditLogger` | Enregistre les requêtes HTTP d'écriture dans les journaux d'audit |

---

# 11. Navigation dans l'interface

## Barre latérale (sidebar)

La sidebar est organisée en sections avec des icônes Font Awesome :

| Section | Lien | Icône | Rôle requis |
|---|---|---|---|
| **Tableau de bord** | `/dashboard` | gauge | Tous les rôles |
| **Patients** | `/patients` | user-injured | administrator, receptionist, gp, specialist, nurse |
| **Rendez-vous** | `/appointments` | calendar-check | administrator, receptionist, gp, specialist, nurse |
| **File d'attente** | `/queue` | list-ol | administrator, receptionist, gp, specialist, nurse |
| **Consultations** | `/consultations` | stethoscope | gp, specialist, administrator |
| **Laboratoire** | `/lab-requests` | flask | gp, specialist, administrator |
| **Stock** | `/stock` | pills | pharmacist, administrator |
| **Livret** | `/formulary` | book-medical | pharmacist, administrator |
| **Dispensation** | `/dispensations` | prescription-bottle | pharmacist, administrator |
| **Validation** | `/validations` | clipboard-check | pharmacist, administrator |
| **Commandes** | `/purchase-orders` | truck-medical | pharmacist, administrator |
| **Fournisseurs** | `/suppliers` | truck-field | pharmacist, administrator |
| **Dépôts** | `/warehouses` | warehouse | pharmacist, administrator |
| **Stupéfiants** | `/narcotics` | skull-crossbones | pharmacist, administrator |
| **Indicateurs** | `/kpi` | chart-line | pharmacist, administrator |
| **Événements** | `/medication-events` | triangle-exclamation | pharmacist, administrator |
| **Documents** | `/pharmacy-documents` | folder-open | pharmacist, administrator |
| **Lots** | `/batches` | cubes | pharmacist, administrator |
| **Rapports** | `/reports/stock` | chart-bar | pharmacist, administrator |
| **Chambres** | `/rooms` | bed | gp, specialist, nurse, administrator |
| **Admissions** | `/hospitalizations` | bed-pulse | gp, specialist, nurse, administrator |
| **Factures** | `/invoices` | file-invoice-dollar | cashier, administrator, director |
| **Paiements** | `/payments` | money-bill-wave | cashier, administrator, director |
| **Utilisateurs** | `/admin/users` | users-gear | **administrator uniquement** |
| **Paramètres** | `/admin/settings` | gear | **administrator uniquement** |

La section **Administration** (Utilisateurs + Paramètres) n'apparaît que si l'utilisateur connecté a le rôle `administrator`.

## Barre de navigation (navbar)

La barre supérieure contient :
- **Bouton hamburger** (mobile) : ouvre/ferme la sidebar
- **Titre de la page** : correspond à la section active
- **Notifications** : icône cloche avec compteur de notifications non lues
- **Menu utilisateur** : initiales de l'utilisateur, nom complet, menu déroulant avec l'option de déconnexion

---

# 12. Flux de connexion

1. L'utilisateur accède à `/login`
2. Il saisit son nom d'utilisateur (ou email) et son mot de passe
3. `AuthService::login()` vérifie :
   - Si le compte existe et est actif
   - Si le compte n'est pas verrouillé (après 5 tentatives échouées, verrouillage de 15 minutes)
   - Si le mot de passe est correct
4. En cas de succès : connexion établie, dernière connexion enregistrée
5. En cas d'échec : incrémentation du compteur d'échecs, message d'erreur approprié

---

# 13. Recommandations de déploiement

- **Sécurité** : Modifier immédiatement le mot de passe du compte `admin` par défaut après l'installation.
- **Sauvegarde** : Les paramètres système sont stockés en base de données. Inclure la table `settings` dans les sauvegardes régulières.
- **Audit** : Surveiller régulièrement la table `audit_logs` pour détecter toute activité suspecte.
- **Rôles** : Limiter le nombre d'utilisateurs disposant du rôle `administrator` au strict nécessaire (principe du moindre privilège).

---

*Document généré le 22 juin 2026 — HealthCenter v1.0*
