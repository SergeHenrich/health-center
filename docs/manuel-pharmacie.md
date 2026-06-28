# Manuel d'utilisation — Module Pharmacie

## HealthCenter — Centre de Santé

---

# 1. Présentation

Le module **Pharmacie** est le centre de gestion pharmaceutique du système HealthCenter. Il est accessible aux utilisateurs possédant les rôles `pharmacist` ou `administrator`. Il couvre l'intégralité du circuit du médicament : réception, stockage, dispensation, validation pharmaceutique, gestion des stupéfiants, pharmacovigilance, et pilotage.

- **Rôle requis** : `pharmacist` | `administrator`
- **15 contrôleurs** dans `app/Http/Controllers/Pharmacy/`
- **8 services spécialisés** dans `app/Services/Pharmacy/`
- **21 modèles** liés à la pharmacie
- **36 vues Blade** dans `resources/views/pharmacy/`

---

# 2. Navigation dans le module

La section Pharmacie apparaît dans la barre latérale gauche pour les rôles `pharmacist` et `administrator`. Elle est divisée en **13 onglets** :

| Icône | Onglet | Route | URL |
|---|---|---|---|
| 💊 | **Stock** | `stock.index` | `/stock` |
| 📖 | **Livret** | `formulary.index` | `/formulary` |
| 🧴 | **Dispensation** | `dispensations.index` | `/dispensations` |
| ✅ | **Validation** | `validations.index` | `/validations` |
| 🚚 | **Commandes** | `purchase-orders.index` | `/purchase-orders` |
| 🚛 | **Fournisseurs** | `suppliers.index` | `/suppliers` |
| 🏢 | **Dépôts** | `warehouses.index` | `/warehouses` |
| ☠️ | **Stupéfiants** | `narcotics.index` | `/narcotics` |
| 📈 | **Indicateurs** | `kpi.index` | `/kpi` |
| ⚠️ | **Événements** | `medication-events.index` | `/medication-events` |
| 📁 | **Documents** | `pharmacy-documents.index` | `/pharmacy-documents` |
| 📦 | **Lots** | `batches.index` | `/batches` |
| 📊 | **Rapports** | `reports.stock` | `/reports/stock` |

---

# 3. Onglet « Stock » — Gestion des stocks

**Route** : `GET /stock` — **nom** : `stock.index`

## 3.1 Vue d'ensemble

La page affiche un tableau de bord des médicaments avec :

- **Barre de statistiques** (3 cartes) :
  - Nombre total de médicaments (page courante)
  - **Stock bas** (fond ambre) — médicaments sous le seuil minimum
  - **Rupture de stock** (fond rouge) — médicaments à zéro

## 3.2 Filtres et recherche

- **Recherche textuelle** (`q`) : recherche par nom de médicament
- **Filtre par statut** (`filter`) : Tous / Stock bas / Rupture

## 3.3 Actions rapides

- **« Nouvelle commande »** (bouton vert) → lien vers `purchase-orders.create`
- **« Ajouter médicament »** (bouton bleu) → lien vers `medicines.create`

## 3.4 Tableau des médicaments

| Colonne | Détail |
|---|---|
| **Code** | Texte monospace |
| **Médicament** | Nom + nom générique (sous-ligne grise) |
| **Forme** | Capitalisée (Comprimé, Gélule, Sirop, etc.) |
| **Catégorie** | Texte libre |
| **Disponible** | Aligné à droite, gras, coloré (rouge/ambre/noir) |
| **Minimum** | Aligné à droite |
| **Statut** | Badge : Rupture (rouge) / Stock bas (ambre) / Normal (vert) |
| **Prix unitaire** | En XAF, formaté |

Lignes surlignées : fond rouge si rupture, fond ambre si stock bas.

## 3.5 Pagination

20 éléments par page avec conservation des paramètres de filtre.

---

# 4. Onglet « Médicaments » — Gestion du catalogue

## 4.1 Création d'un médicament

**Route** : `GET /medicines/create` — **nom** : `medicines.create`

Formulaire avec les champs suivants :

| Champ | Type | Règle |
|---|---|---|
| **Code** | Texte | Requis, unique |
| **Forme** | Select | Comprimé, Gélule, Sirop, Injectable, Crème, Gouttes, Autre |
| **Nom** | Texte | Requis |
| **Nom générique** | Texte | Optionnel |
| **Catégorie** | Texte | Optionnel |
| **Dosage** | Texte (placeholder "500mg") | Optionnel |
| **Fabricant** | Texte | Optionnel |
| **Prix unitaire** | Nombre (step 0.01) | Requis, min 0 |
| **Stock minimum** | Nombre (défaut 10) | Optionnel |
| **Prescription requise** | Checkbox | Cochée par défaut |

## 4.2 Modification d'un médicament

**Route** : `GET /medicines/{medicine}/edit` — **nom** : `medicines.edit`

Même formulaire que la création, avec :
- **Code** désactivé (lecture seule, fond gris)
- Champs pré-remplis avec les valeurs actuelles

## 4.3 Suppression (archivage)

Suppression logique (soft delete) via `MedicineController::destroy()`.

---

# 5. Onglet « Livret » — Livret thérapeutique

**Route** : `GET /formulary` — **nom** : `formulary.index`

## 5.1 Liste du livret

Tableau complet des médicaments avec leur statut d'inscription au livret thérapeutique.

### Filtres

| Filtre | Type | Détail |
|---|---|---|
| **Recherche** | Texte (`q`) | Libellé |
| **Statut** | Select | Tous / Inscrit / Substituable / Non inscrit / Retiré |
| **Classe thérapeutique** | Select | Dynamique (distinct de la table) |
| **Stupéfiants** | Checkbox | Filtre exclusif |

### Colonnes du tableau

- **Code** (monospace)
- **DCI / Spécialité** (nom + générique, lien vers le détail)
- **ATC** (code anatomique)
- **Classe thérapeutique**
- **Statut livret** (badges : Inscrit=vert / Substituable=bleu / Hors livret=rouge / Retiré=gris)
- **Stock** (coloré selon le niveau)
- **Réglementé** (badges : Stupéfiant=rouge / Psychotrope=violet / Chaîne du froid=cyan)

## 5.2 Détail d'un médicament du livret

**Route** : `GET /formulary/{medicine}` — **nom** : `formulary.show`

La page de détail est organisée en **4 sections** :

### Section 1 : Informations générales
Grille 4 colonnes : Code, ATC, Classe thérapeutique, Forme, Dosage, Stock central, Prix unitaire, Statut livret (badge), badges réglementés.

### Section 2 : Mise à jour du statut
Formulaire pour modifier :
- **Statut livret** : Inscrit / Substituable / Non inscrit / Retiré
- **Code ATC** (texte)
- **Classe thérapeutique** (texte)
- **Stupéfiant** (toggle switch rouge)
- **Psychotrope** (toggle switch violet)

### Section 3 : Décisions de la Commission du Livret
- Bouton « + Nouvelle décision » (Alpine.js toggle)
- Formulaire : Décision (Admission/Renouvellement/Rejet/Retrait/Modification), Date de décision, Date de révision, Justification, Document de référence
- Historique : tableau des décisions passées (date, décision, justification, décideur, révision)

### Section 4 : Substitutions thérapeutiques
- Bouton « + Ajouter » (Alpine.js toggle)
- Formulaire : Médicament substitut, Type (Générique/Thérapeutique/Alternative), Motif
- Liste des substitutions avec badge de type, statut actif/inactif, bouton « Désactiver »

---

# 6. Onglet « Dispensation »

## 6.1 Historique des dispensations

**Route** : `GET /dispensations` — **nom** : `dispensations.index`

Tableau listant toutes les dispensations effectuées :

| Colonne | Détail |
|---|---|
| **Ordonnance** | N° d'ordonnance (monospace) |
| **Patient** | Nom complet |
| **Pharmacien** | Nom du pharmacien |
| **Date** | Format `d/m/Y H:i` |
| **Actions** | Icône œil → Détails |

Bouton « Nouvelle dispensation » en haut à droite.

## 6.2 Création d'une dispensation

**Route** : `GET /dispensations/create` — **nom** : `dispensations.create`

- Si aucune ordonnance en attente : message vert « Aucune ordonnance en attente de dispensation »
- Sinon :
  - **Selecteur d'ordonnance** : affiche le N°, le patient et le nombre d'articles
  - **Détails de l'ordonnance** (visible après sélection) : tableau des médicaments, dosage, quantité prescrite, déjà dispensé
  - Bouton « Dispenser »

Le dispatching des lots se fait automatiquement via la méthode FEFO (*First Expiry First Out*) implémentée dans `BatchService::pickBatches()`.

## 6.3 Détail d'une dispensation

**Route** : `GET /dispensations/{dispensation}` — **nom** : `dispensations.show`

- **Carte d'information** : Ordonnance, Patient, Médecin, Pharmacien, Date, Statut ordonnance (badge vert/ambre)
- **Tableau des articles** : Médicament, Quantité, Prix unitaire, Total (avec somme en pied de tableau)
- **Bouton Retour**

## 6.4 Annulation d'une dispensation

`DispensationController::destroy()` — Appelle `PharmacyService::reverseDispensation()` qui restaure les stocks et supprime la dispensation.

---

# 7. Onglet « Validation » — Validation pharmaceutique

## 7.1 Liste des ordonnances en attente

**Route** : `GET /validations` — **nom** : `validations.index`

Affiche les ordonnances en attente de validation sous forme de cartes :

- N° d'ordonnance + badge de statut (En attente / Avec interventions)
- Nom du patient
- Médecin prescripteur + date
- Liste des articles prescrits (médicament × quantité, avec ⚠️ si stupéfiant)
- Bouton « Valider » → lien vers le détail

## 7.2 Détail et validation

**Route** : `GET /validations/{prescription}` — **nom** : `validations.show`

4 sections :

### Section 1 : Informations de l'ordonnance
N°, Patient, Prescripteur, Date.

### Section 2 : Articles prescrits
Tableau : Médicament, Dosage, Quantité, Stock disponible, Statut livret (badge vert/rouge), Alertes (Stupéfiant).

### Section 3 : Formulaire de validation
- **Décision** : Approuvée / Approuvée avec modifications / Refusée
- **Notes** (textarea)
- Bouton « Valider l'ordonnance »

### Section 4 : Historique des validations
Liste des validations précédentes avec pharmacien, date, statut (badge), notes, et interventions pharmaceutiques.

---

# 8. Onglet « Commandes » — Bons de commande fournisseurs

## 8.1 Liste des commandes

**Route** : `GET /purchase-orders` — **nom** : `purchase-orders.index`

| Colonne | Détail |
|---|---|
| **N° commande** | Monospace |
| **Fournisseur** | Nom |
| **Pharmacien** | Nom |
| **Date** | Format `d/m/Y` |
| **Statut** | Badge : draft (gris) / approved (bleu) / received (vert) |
| **Total** | En XAF |
| **Actions** | Icône œil |

## 8.2 Création d'une commande

**Route** : `GET /purchase-orders/create` — **nom** : `purchase-orders.create`

- **Sélection du fournisseur** : avec Alpine.js, le contact se remplit automatiquement
- **Livraison prévue** (date)
- **Notes** (textarea)
- **Articles** (dynamique Alpine.js) :
  - Sélecteur de médicament (code + nom)
  - Quantité commandée
  - Prix unitaire
  - Bouton supprimer (si > 1 ligne)
  - Bouton « Ajouter une ligne »

Le numéro de commande est auto-généré au format `PO-YYYY-XXXXX`.

## 8.3 Détail d'une commande

**Route** : `GET /purchase-orders/{purchaseOrder}` — **nom** : `purchase-orders.show`

- **Carte d'information** : Fournisseur, Contact, Pharmacien, Approuvé par, Date, Livraison prévue, Statut (badge), Notes
- **Tableau des articles** : Médicament, Qté, Prix unitaire, Total, Reçu (vert si > 0)
- **Boutons d'action** :
  - **« Approuver »** (bleu, visible si `draft`, avec confirmation)
  - **« Réceptionner »** (vert, visible si `approved` ou `draft`, avec confirmation)
  - **« Retour »**

L'approbation enregistre l'utilisateur connecté comme `approved_by`. La réception crée les lots (`StockBatch`), met à jour le stock central, et enregistre les mouvements de stock.

---

# 9. Onglet « Fournisseurs »

## 9.1 Liste des fournisseurs

**Route** : `GET /suppliers` — **nom** : `suppliers.index`

Filtres : recherche textuelle + filtre Actifs/Tous.

| Colonne | Détail |
|---|---|
| **Code** | Monospace |
| **Nom** | Lien vers le détail + contact + téléphone |
| **Contact** | Nom du contact |
| **Ville** | Texte |
| **Contrats** | Nombre (centré) |
| **Commandes** | Nombre (centré) |
| **Statut** | Badge : Actif (vert) / Inactif (rouge) |
| **Actions** | Lien Modifier |

## 9.2 Création / Modification

**Routes** : `suppliers.create` / `suppliers.edit`

Champs : Code, Nom, Personne à contacter, Catégorie, Email, Téléphone, Adresse, Ville, N° fiscal (NIU), Notes.

En modification : champ Code désactivé, case à cocher « Fournisseur actif ».

## 9.3 Détail d'un fournisseur

**Route** : `GET /suppliers/{supplier}` — **nom** : `suppliers.show`

4 sections :

### Section 1 : Informations générales
Code, Contact, Email, Téléphone, Ville, N° fiscal, Catégorie, Statut, Adresse. Bouton « Modifier ».

### Section 2 : Dernières commandes
Tableau : N°, Date, Montant, Statut.

### Section 3 : Contrats
- Bouton « + Ajouter » (Alpine.js)
- Formulaire : N° contrat, Date début (requis), Date fin, Remise (%), Conditions
- Tableau : N°, Début, Fin, Remise, Statut

### Section 4 : Évaluations
- Bouton « + Évaluer » (Alpine.js)
- Formulaire : Qualité (1-10), Livraison (1-10), Prix (1-10), Commentaires
- Le score global est calculé automatiquement (moyenne des 3 notes)
- Tableau : Date, Qualité, Livraison, Prix, Global (gras), Évaluateur

---

# 10. Onglet « Dépôts » — Gestion des entrepôts

## 10.1 Liste des dépôts

**Route** : `GET /warehouses` — **nom** : `warehouses.index`

Affichage en **grille de cartes** :

- Nom (lien), Code, Type (Pharmacie centrale / Unité de soins / Urgences / Bloc opératoire / Autre)
- Badge Actif (vert) / Inactif (rouge)
- Statistiques : Médicaments (nombre), Unités totales (formaté)
- Liens : « Voir le stock » / « Modifier »

Types disponibles :
- `central` → Pharmacie centrale
- `unit_care` → Unité de soins
- `emergency` → Urgences
- `bloc` → Bloc opératoire
- `other` → Autre

## 10.2 Création / Modification

**Routes** : `warehouses.create` / `warehouses.edit`

Champs : Code, Type (select), Nom, Emplacement.

En modification : case à cocher « Dépôt actif ».

## 10.3 Détail d'un dépôt

**Route** : `GET /warehouses/{warehouse}` — **nom** : `warehouses.show`

### Transfert de stock
Formulaire caché (Alpine.js) avec :
- **Vers** : sélecteur de dépôt destinataire
- **Médicament** : selecteur
- **Quantité**
- **Motif**
- Bouton « Transférer »

### Tableau des stocks du dépôt
| Colonne | Détail |
|---|---|
| **Médicament** | Nom + code |
| **Disponible** | Gras, coloré si bas |
| **Minimum** | — |
| **Maximum** | — |
| **Statut** | Badge : Rupture / Stock bas / Normal |
| **Actions** | Formulaire inline d'ajustement : Type (Entrée/Sortie/Ajuster), Quantité, Motif, OK |

Filtres : recherche textuelle + case à cocher « Stock bas ». Pagination incluse.

---

# 11. Onglet « Stupéfiants » — Registre des stupéfiants

**Route** : `GET /narcotics` — **nom** : `narcotics.index`

## 11.1 Vue d'ensemble

- **Grille des médicaments stupéfiants** : cartes fond rouge avec nom, code, forme, dosage, stock actuel (gras rouge)
- Si aucun stupéfiant : lien vers le livret pour en marquer un

## 11.2 Filtres et saisie

- Filtres : Médicament, Date de début, Date de fin
- Bouton « Nouvelle entrée » → formulaire caché (Alpine.js)

### Formulaire d'entrée
- **Médicament** (select avec stock actuel)
- **Entrée** (nombre, valeur par défaut 0)
- **Sortie** (nombre, valeur par défaut 0)
- **N° lot**
- **Prescripteur** (texte)
- Bouton « Enregistrer »

Le solde est calculé automatiquement par `NarcoticService::recordEntry()`.

## 11.3 Registre

Tableau : Date, Médicament (lien), N° lot, Entrée (vert), Sortie (rouge), Solde (gras), Pharmacien.

## 11.4 Détail par médicament

**Route** : `GET /narcotics/{medicine}` — **nom** : `narcotics.show`

- **Carte de solde** : Stock actuel en 3xl (rouge si 0), Code, Forme/Dosage
- **Registre** : Date, N° lot, Entrée, Sortie, Solde, Pharmacien, Patient

---

# 12. Onglet « Indicateurs » — Tableau de bord de pilotage

**Route** : `GET /kpi` — **nom** : `kpi.index`

Tableau de bord avec graphiques Chart.js. 4 lignes de cartes :

## Ligne 1 — Dispensation & Stock
| Indicateur | Valeur affichée |
|---|---|
| **Dispensations (mois)** | Nombre du mois + total cumulé |
| **Ordonnances en attente** | Nombre + moyenne/jour |
| **Santé du stock** | Pourcentage + ruptures/épuisés |
| **Valeur du stock** | Montant en F + articles en dépôts |

## Ligne 2 — Qualité & Conformité
| Indicateur | Valeur affichée |
|---|---|
| **Validations pharmaceutiques** | % + approuvées/total + interventions |
| **Couverture livret** | % + inscrits/hors livret |
| **Stupéfiants** | Nombre + mouvements + solde total |
| **Événements** | Ouverts + total + critiques |

## Ligne 3 — Graphiques
- **Dispensations (6 mois)** : histogramme bleu
- **Événements signalés (6 mois)** : courbe rouge avec remplissage

## Ligne 4 — Types d'événements
- Erreurs médicamenteuses
- Effets indésirables
- Presque-accidents
- Incidents qualité

---

# 13. Onglet « Événements » — Pharmacovigilance

## 13.1 Liste des événements

**Route** : `GET /medication-events` — **nom** : `medication-events.index`

### Filtres
- Recherche textuelle
- Type : Tous / Erreur méd. / Effet indés. / Presque-accident / Incident qualité
- Sévérité : Tous / Faible / Moyenne / Haute / Critique
- Statut : Tous / Ouvert / Résolu

### Tableau
| Colonne | Détail |
|---|---|
| **Date** | Format `d/m/Y` |
| **Type** | Badge coloré |
| **Description** | Lien vers le détail (tronqué) |
| **Sévérité** | Texte coloré |
| **Signalé par** | Nom |
| **Statut** | Badge : Ouvert (rouge) / Résolu (vert) |
| **Actions** | Lien « Voir » |

## 13.2 Signalement d'un événement

**Route** : `GET /medication-events/create` — **nom** : `medication-events.create`

Formulaire complet :

| Champ | Type |
|---|---|
| **Type d'événement** | Select (Erreur médicamenteuse / Effet indésirable / Presque-accident / Incident qualité) |
| **Sévérité** | Select (Faible / Moyenne / Haute / Critique) |
| **Médicament concerné** | Select (tous les actifs) |
| **Patient concerné** | Select (tous les patients) |
| **Description détaillée** | Textarea (requis) |
| **Cause identifiée** | Textarea |
| **Action immédiate** | Textarea |
| **Date de l'événement** | Datetime-local |
| **Notes complémentaires** | Textarea |

## 13.3 Détail d'un événement

**Route** : `GET /medication-events/{medicationEvent}` — **nom** : `medication-events.show`

- Grille d'information : Type, Sévérité (coloré), Statut (badge + date résolution), Signalé par, Assigné à, Date, Médicament, Patient
- Sections : Description, Cause identifiée, Action entreprise (fond gris)
- **Formulaire de résolution** (si statut `open`) : Action entreprise, Actions correctives, bouton « Résoudre »
- **Assignation** (via route `medication-events.assign`) : assignation à un utilisateur

---

# 14. Onglet « Documents » — Gestion documentaire

## 14.1 Liste des documents

**Route** : `GET /pharmacy-documents` — **nom** : `pharmacy-documents.index`

Filtres : recherche textuelle + type (Tous/SOP/Contrat/Réglementaire/Référence/Autre).

Affichage en liste de cartes avec :
- Icône fichier (fond bleu)
- Titre (lien)
- Badge de type
- Version
- Nom du fichier
- Taille (Ko)
- Description (tronquée 120 caractères)
- Date d'expiration (rouge si dépassée)
- Icône crayon (modifier)

## 14.2 Ajout d'un document

**Route** : `GET /pharmacy-documents/create` — **nom** : `pharmacy-documents.create`

| Champ | Type |
|---|---|
| **Titre** | Texte (requis, max 200) |
| **Type** | Select (SOP/Contrat/Réglementaire/Référence/Autre) |
| **Référence** | Texte |
| **Description** | Textarea |
| **Fichier** | File (PDF/DOC/XLS/PPT/PNG/JPG, max 20 Mo) |
| **Version** | Texte (défaut "1.0") |
| **Date d'expiration** | Date |
| **Médicament lié** | Select (optionnel) |
| **Fournisseur lié** | Select (optionnel) |

Le fichier est stocké sur le disque public dans le dossier `pharmacy-documents/`.

## 14.3 Modification / Suppression

- **Modification** : même formulaire, fichier optionnel, case « Document actif »
- **Suppression** : bouton rouge avec confirmation, supprime le fichier du disque

## 14.4 Détail d'un document

Grille d'information : Type, Version, Référence, Fichier (nom + taille), Uploadé par, Date d'expiration, Médicament lié, Fournisseur lié, Publié le. Section Description.

---

# 15. Onglet « Lots » — Gestion des lots de stock

## 15.1 Liste des lots

**Route** : `GET /batches` — **nom** : `batches.index`

### Filtres
- **Statut** : Tous / Actif / Épuisé / Périmé / Mis au rebut
- **Péremption < 90j** (checkbox)
- **Périmé** (checkbox)

### Tableau
| Colonne | Détail |
|---|---|
| **Médicament** | Lien vers le détail |
| **N° lot** | Monospace |
| **Péremption** | Date + indicateur (périmé rouge / bientôt ambre) |
| **Disponible** | Monospace |
| **Initial** | Gris, plus petit |
| **Statut** | Badge : Actif (vert) / Épuisé (gris) / Périmé (rouge) / Rebus (orange) |
| **Actions** | Lien « Détails » |

Lignes surlignées en rouge si périmé.

## 15.2 Détail d'un lot

**Route** : `GET /batches/{stockBatch}` — **nom** : `batches.show`

### Carte d'information
Médicament, N° lot, Date de péremption (rouge si périmé), Statut (badge), Quantité disponible (2xl), Quantité initiale, Coût unitaire, Reçu le.

### Actions (si actif et quantité > 0)
- **« Mettre au rebut »** (rouge) : quantité, motif (expired/damaged/theft/other)
- **« Retour fournisseur »** (orange) : quantité

### Mouvements du lot
Tableau : Date, Type (Entrée=vert / Sortie=rouge), Qté, Raison.

---

# 16. Onglet « Rapports »

## 16.1 Rapport de stock

**Route** : `GET /reports/stock` — **nom** : `reports.stock`

### Export
- **PDF** (bouton rouge)
- **Excel** (bouton émeraude)

### Cartes de synthèse
- Total lots
- Qté disponible (totale)
- Qté initiale (totale)
- Lots périmés (rouge)

### Tableau complet
Médicament, N° lot, Péremption, Disponible, Initiale, Statut.

## 16.2 Rapport de consommation

**Route** : `GET /reports/consumption` — **nom** : `reports.consumption`

### Export
- **PDF** (bouton rouge)
- **Excel** (bouton émeraude)

### Sélecteur de période
Semaine / Mois / Année (auto-submit au changement).

### Graphique
Histogramme « Évolution de la consommation » avec Chart.js.

## 16.3 Classes d'export

- `ConsumptionReportExport` (Maatwebsite/Laravel Excel)
- `StockReportExport` (Maatwebsite/Laravel Excel)

Les vues PDF se trouvent dans `resources/views/pdf/pharmacy/`.

---

# 17. Profil médicamenteux du patient

**Route** : `GET /patients/{patient}/medication-profile` — **nom** : `patients.medication-profile`

Consultation transverse du dossier médicamenteux d'un patient.

### Section 1 : Informations patient
N° dossier, Âge, Poids, Allergies.

### Section 2 : Ordonnances actives
Cartes avec N° ordonnance, Médecin, Date, Statut (En cours / Partiellement dispensé). Liste des articles avec quantité prescrite / dispensée. Lien « Voir ».

### Section 3 : Historique des dispensations
Tableau : Date, Ordonnance (lien), Pharmacien, Articles.

### Section 4 : Événements médicamenteux
Tableau : Date, Type, Sévérité, Statut (Résolu/Ouvert).

---

# 18. Architecture technique

## 18.1 Routes

Toutes les routes pharmacie sont groupées sous :
```php
Route::middleware('role:pharmacist|administrator')->group(function () { ... });
```

## 18.2 Contrôleurs

| Contrôleur | Méthodes principales |
|---|---|
| `MedicineController` | index, create, store, edit, update, destroy |
| `StockController` | index, adjust |
| `DispensationController` | index, create, store, show, destroy |
| `FormularyController` | index, show, updateStatus, storeCommissionDecision, storeSubstitution, destroySubstitution |
| `PurchaseOrderController` | index, create, store, show, approve, receive |
| `SupplierController` | index, create, store, show, edit, update, destroy, storeContract, storeEvaluation |
| `WarehouseController` | index, create, store, show, edit, update, adjustStock, transfer |
| `PharmaceuticalValidationController` | index, show, validatePrescription |
| `NarcoticRegisterController` | index, show, store |
| `KpiController` | index |
| `MedicationEventController` | index, create, store, show, resolve, assign |
| `PharmacyDocumentController` | index, create, store, show, edit, update, destroy |
| `BatchController` | index, show, writeOff, returnToSupplier |
| `ReportController` | stockReport, consumptionReport, stockReportPdf, stockReportExcel, consumptionReportPdf, consumptionReportExcel |
| `PatientMedicationController` | __invoke |

## 18.3 Services spécialisés

| Service | Rôle |
|---|---|
| `BatchService` | Gestion des lots, FEFO picking, write-off, retour fournisseur |
| `ClinicalPharmacyService` | Validation pharmaceutique, analyse d'ordonnance |
| `FormularyService` | Gestion du livret, décisions commission, substitutions |
| `KpiService` | Calcul des indicateurs et données de graphiques |
| `MedicationEventService` | Signalement, résolution, assignation d'événements |
| `NarcoticService` | Registre des stupéfiants, calcul des soldes |
| `PharmacyDocumentService` | Upload, mise à jour, suppression de documents |
| `StockDistributionService` | Gestion des stocks par dépôt, transferts |

## 18.4 Service principal

`PharmacyService` (dans `app/Services/PharmacyService.php`) :
- `dispense()` : dispensation complète avec FEFO, création des mouvements, mise à jour des stocks
- `reverseDispensation()` : annulation complète d'une dispensation
- `checkStock()` : vérification de disponibilité
- `receiveStock()` : réception de stock depuis un bon de commande

---

# 19. Bonnes pratiques et recommandations

- **Circuit du médicament** : Respecter l'ordre Validation → Dispensation pour garantir la sécurité des patients
- **Stupéfiants** : Saisir toutes les entrées/sorties en temps réel pour garantir la traçabilité
- **Lots** : Privilégier la méthode FEFO (First Expiry First Out) pour la dispensation
- **Documents** : Maintenir les SOP à jour et surveiller les dates d'expiration
- **Pharmacovigilance** : Signaler tout événement indésirable, même mineur
- **Commandes** : Le workflow recommandé est Brouillon → Approuvé → Réceptionné
- **Livret** : Les décisions de la commission doivent être documentées pour chaque modification

---

*Document généré le 22 juin 2026 — HealthCenter v1.0*
