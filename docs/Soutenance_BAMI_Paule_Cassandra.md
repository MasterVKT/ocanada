# Soutenance BTS GSI — BAMI Paule Cassandra | Ô Canada RH

## Informations Générales

- **Titre du projet**: Ô Canada RH
- **Sous-titre**: Conception et réalisation d'une application de gestion des présences du personnel et de suivi des visites — application web multi-rôles avec intelligence artificielle intégrée
- **Étudiante**: BAMI Paule Cassandra
- **Formation**: BTS GSI 2e année
- **Institution**: IUGET · South Polytech
- **Matricule**: 26GSI0063
- **Année académique**: 2025-2026
- **Encadreur académique**: M. MBIANDJI Jonathan
- **Encadreur professionnel**: M. MPEKE Ramiro
- **Période de stage**: 05 Août – 06 Septembre 2025

---

## Plan de la Soutenance

1. Présentation de l'entreprise (Ô Canada SARLU — contexte, organisation, SWOT)
2. Diagnostic du système existant (Dysfonctionnements, flux, problématique centrale)
3. Expression des besoins (Besoins fonctionnels, non fonctionnels, contraintes)
4. Conception du système (MERISE + UML, MCD, MLD, architecture MVC)
5. Réalisation & Sécurité (Modules développés, stack technique, mesures de sécurité)
6. Tests, Impact & Perspectives (Résultats des tests, gains organisationnels, évolutions futures)

**Problématique centrale**: Comment la conception et le déploiement d'un système d'information intégré de gestion des ressources humaines et des visiteurs peuvent-ils renforcer la performance organisationnelle d'Ô Canada tout en assurant la conformité au droit du travail camerounais ?

---

## Chapitre I — Présentation de l'Entreprise

### Fiche Signalétique

| Champ | Valeur |
|-------|--------|
| Forme juridique | SARLU |
| Directeur Général | LEUNDJOU MPEKE Ramiro |
| Siège social | Douala, derrière Lycée Makepé |
| Date de création | 23 Septembre 2024 |
| Capital social | 1 000 000 FCFA |
| Effectif | 13 collaborateurs |
| Activité principale | Immigration & mobilité internationale |

### Description de l'Entreprise

Ô Canada accompagne les candidats souhaitant immigrer, étudier ou travailler au Canada, en France, Belgique, Allemagne, Chine et Malte. Son approche repose sur l'écoute, la transparence et un suivi rigoureux des dossiers.

### Structure Organisationnelle

- **CEO**: Ramiro Mpeke
- **DGA**: Rosny Kontcha
- **Services**:
  - Service Juridique
  - Comptabilité
  - Service Informatique (×2)
  - Direction Marketing
  - Direction Ventes
- **Équipe opérationnelle**:
  - Responsable Marketing
  - 3 Conseillères Clientèle

### Destinations Supportées

Canada · France · Belgique · Allemagne · Chine · Malte

---

## Analyse SWOT

### Forces (Strengths)

- Forte demande d'immigration vers le Canada au Cameroun
- Expertise légale interne pour les dossiers complexes
- Structure organisationnelle claire et fonctionnelle
- Volonté de professionnalisation des processus internes

### Faiblesses (Weaknesses)

- Seulement 13 collaborateurs — capacité limitée
- Absence de système formalisé de gestion RH
- Dépendance aux pratiques manuelles (WhatsApp, oral)
- Marque encore peu connue face à la concurrence

### Opportunités (Opportunities)

- Augmentation constante des demandes d'immigration Canada
- Différenciation par l'innovation technologique
- Élargissement vers d'autres destinations
- Partenariats potentiels avec universités canadiennes

### Menaces (Threats)

- Nombreuses agences concurrentes à Douala
- Modifications fréquentes des lois d'immigration
- Risque de non-conformité au Code du Travail camerounais
- Risque de départ de collaborateurs clés

**Conclusion SWOT**: L'analyse révèle un risque croissant de non-conformité juridique (Code du Travail, cadre OHADA) lié à l'absence de données RH structurées.

---

## Chapitre II — Diagnostic du Système Existant

### Quatre Dysfonctionnements Majeurs

#### Dysfonctionnement 1 : Absence de traçabilité des présences

- Aucun registre ni outil numérique
- Heures d'arrivée/départ communiquées oralement ou par WhatsApp
- Sans archivage ni consolidation
- Calcul des heures travaillées impossible

#### Dysfonctionnement 2 : Gestion désorganisée des congés

- Demandes transmises verbalement
- Sans formulaire ni vérification de solde
- Risque réel de violation du Code du Travail camerounais et du cadre OHADA

#### Dysfonctionnement 3 : Absence de gestion des visiteurs

- Aucune consignation des entrées/sorties
- Aucun badge, aucun historique
- Exposition à des risques de sécurité et de confidentialité des données clients

#### Dysfonctionnement 4 : Absence d'indicateurs RH

- Sans données centralisées
- La direction ne dispose d'aucune base fiable pour piloter la présence
- Impossible d'anticiper les besoins ou d'estimer le coût de l'absentéisme

### Synthèse des Flux d'Information Existants

| Domaine | Méthode actuelle | Traçabilité |
|---------|------------------|-------------|
| Présences | Oral / WhatsApp | Aucune |
| Congés | Oral / WhatsApp | Aucune |
| Visiteurs | Oral uniquement | Aucune |

---

## Expression des Besoins

### Besoins Fonctionnels (6 domaines → 19 modules développés)

**11 grandes catégories/besoins identifiés :**

1. **Gestion des utilisateurs & accès**
   - 3 profils : Administrateur, Employé, Agent d'accueil
   - RBAC complet

2. **Gestion des présences** *(comprend 4 modules)*
   - Pointage sécurisé au kiosque
   - Calcul automatique retards/absences
   - Récapitulatifs

3. **Gestion des congés OHADA** *(comprend 3 modules)*
   - Demandes en ligne
   - Calcul automatique jours ouvrables camerounais
   - Soldes

4. **Gestion des visiteurs** *(comprend 3 modules)*
   - Enregistrement
   - Badge QR code
   - Historique
   - Vue temps réel

5. **Tableau de bord & KPIs**
   - Indicateurs clés
   - Coût absentéisme en XAF
   - Graphiques Chart.js

6. **Fonctionnalités IA**
   - Assistant rédaction congé
   - Chatbot RH via API Anthropic Claude

> **Note** : Les 11 besoins fonctionnels identifiés ont été déclinés en **19 modules techniques** lors de la réalisation (par exemple : "Gestion des présences" = Pointage Kiosque + Gestion Présences + Correction Manuelle + Cron Absences).

### Besoins Non Fonctionnels

| Domaine | Spécifications |
|---------|----------------|
| Sécurité | bcrypt, PDO préparé, CSRF, restriction IP kiosque |
| Conformité légale | Art. 89 Code du Travail, calendrier camerounais, OHADA |
| Performance | Pages < 3s, kiosque stable toute la journée |
| Localisation | 100% français, UTC+1, FCFA, JJ/MM/AAAA |

### Contrainte Économique Clé

Stack 100% open source (CodeIgniter 4, MySQL 8, Bootstrap 5, Chart.js) — zéro coût de licence. L'IA (API Claude) est la seule dépendance payante, avec rate limiting configurable par l'administrateur.

---

## Chapitre III — Conception du Système

### Méthodologie

**Choix méthodologique**: MERISE comme socle principal (MCD, MLD, MCT) — notation familière en formation GSI au Cameroun — enrichie par les diagrammes UML (cas d'utilisation, séquence, classes) pour la lisibilité universelle.

### Modélisation MERISE

1. **MCD — Modèle Conceptuel de Données**
   - 13 entités
   - 14 associations
   - Notation crow's foot

2. **MLD — Modèle Logique de Données**
   - 13 tables MySQL 8
   - Clés étrangères
   - Contraintes d'unicité

3. **MCT — Modèle Conceptuel des Traitements**
   - 3 processus : Pointage, Congés, Visiteurs

4. **Diagrammes UML**
   - Cas d'utilisation (4 acteurs)
   - Séquence (kiosque + IA)
   - Classes (24 classes, 4 couches)

### Architecture MVC — 5 Couches

| Couche | Technologies |
|--------|--------------|
| Présentation | Views + Layout (Bootstrap 5, Vanilla JS, Chart.js) |
| Contrôle | Controllers (PHP 8.2, CodeIgniter 4), Filters (Auth, Role, IP) |
| Métier | Services (OHADA, Notifs, IA) |
| Données | Models PHP (PDO, QueryBuilder), MySQL 8.0 (13 tables, ACID) |
| IA Externe | AnthropicService (claude-sonnet-4, HTTPS backend) |

---

## MCD — Modèle Conceptuel de Données

### 13 Entités

1. JOURS_FERIES — Jours fériés officiels camerounais
2. UTILISATEURS — Comptes utilisateurs (admin, employé, agent)
3. NOTIFICATIONS — Messages in-app pour les utilisateurs
4. SHIFTS_MODELES — Modèles d'horaires de travail
5. DEMANDES_CONGE — Demandes de congé des employés
6. EMPLOYES — Fiche employee avec données personnelles
7. PRESENCES — Enregistrements de pointage
8. VISITEURS — Visiteurs enregistrés
9. SOLDES_CONGES — Soldes de congés par année
10. AUDIT_LOG — Journal d'audit immuable
11. AFFECTATIONS_SHIFTS — Assignation des employés aux shifts
12. DOCUMENTS_RH — Documents RH uploadés
13. CONFIG_SYSTEME — Configuration du système

### 14 Associations

| Association | Cardinalités | Description |
|-------------|--------------|-------------|
| Possède | UTILISATEURS(1,1) — EMPLOYES(0,1) | Un utilisateur possède un employé |
| Soumet | EMPLOYES(1,1) — DEMANDES_CONGE(0,n) | Un employé soumet des demandes de congé |
| Génère | EMPLOYES(1,1) — PRESENCES(0,n) | Un employé génère des présences |
| Notifie | UTILISATEURS(1,1) — NOTIFICATIONS(0,n) | Un utilisateur notifie |
| Reçoit | EMPLOYES(1,1) — VISITEURS(0,n) | Un employé reçoit des visiteurs |
| Traite | UTILISATEURS — DEMANDES_CONGE | Un utilisateur traite les demandes |
| Enregistre | UTILISATEURS — VISITEURS | Un utilisateur enregistre les visiteurs |
| Détient | EMPLOYES — SOLDES_CONGES | Un employé détient ses soldes |
| Journalise | (0,n)-(0,n) | Journalisation des événements |
| Est affecté | EMPLOYES — AFFECTATIONS_SHIFTS | Affectation aux shifts |
| Référence | SHIFTS — PRESENCES | Référence des shifts |
| Concerne | SHIFTS — AFFECTATIONS | Concerne les shifts |
| Possède doc | EMPLOYES+UTIL — DOCUMENTS_RH | Possède des documents |
| Uploade | (0,n) | Upload de documents |

### Couleurs des Entités (Légende)

- **Noyau central**: UTILISATEURS · EMPLOYES (couleur or)
- **Présences / Shifts**: couleur bleue
- **Congés / Soldes**: couleur verte
- **Visiteurs**: couleur rouge
- **Transversal/RH**: couleur violette

---

## MLD — Modèle Logique de Données

### 13 Tables MySQL 8

#### Table UTILISATEURS
- **Clé primaire**: id INT AUTO_INCREMENT
- **Champs**: email VARCHAR (UNIQUE), mot_de_passe (bcrypt), role ENUM(admin...), actif TINYINT

#### Table EMPLOYES
- **Clé primaire**: id INT AUTO_INCREMENT
- **Clé étrangère**: utilisateur_id (FK)
- **Champs**: matricule (UNIQUE), date_embauche DATE, salaire_journalier XAF, pin_kiosque (bcrypt)

#### Table PRESENCES
- **Clé primaire**: id INT AUTO_INCREMENT
- **Clés étrangères**: employe_id (FK), shift_modele_id (FK)
- **Champs**: heure_arrivee TIME, statut_arrivee ENUM
- **Contrainte**: UNIQUE(employe_id, date_presence)

#### Table DEMANDES_CONGE
- **Clé primaire**: id INT AUTO_INCREMENT
- **Clés étrangères**: employe_id (FK), traite_par (FK NULL)
- **Champs**: jours_ouvrables INT, statut ENUM, type_conge, date_debut, date_fin

#### Table VISITEURS
- **Clé primaire**: id INT AUTO_INCREMENT
- **Clés étrangères**: agent_id (FK), personne_a_voir_id (FK)
- **Champs**: numero_badge (UNIQUE), heure_arrivee, heure_sortie, statut ENUM(present, sorti)

#### Table SOLDES_CONGES
- **Clé primaire**: id INT AUTO_INCREMENT
- **Clé étrangère**: employe_id (FK)
- **Champs**: annee INT, jours_acquis DECIMAL, jours_restants DECIMAL
- **Contrainte**: UNIQUE(employe_id, annee)

#### Table AFFECTATIONS_SHIFTS
- **Clé primaire**: id INT AUTO_INCREMENT
- **Clés étrangères**: employe_id (FK), shift_id (FK)
- **Champs**: date_debut DATE, date_fin DATE

#### Table AUDIT_LOG ⚠
- **Clé primaire**: id INT AUTO_INCREMENT
- **Clé étrangère**: utilisateur_id (FK NULL)
- **Champs**: type_evenement VARCHAR, donnees_avant JSON, donnees_apres JSON
- **Important**: INSERT ONLY — Aucun UPDATE possible

### Tables Additionnelles (5)

- SHIFTS_MODELES
- NOTIFICATIONS
- DOCUMENTS_RH
- JOURS_FERIES
- CONFIG_SYSTEME

### Règles de Transformation (R1-R5)

- **R1**: Entité → table PK AUTO_INCREMENT
- **R2**: Assoc.(1,1)-(0,n) → FK côté (0,n)
- **R3**: Assoc.(n,m) → table association
- **R4**: Unicité composite sur règles métier
- **R5**: Identifiant MCD → PK + UNIQUE NOT NULL

---

## MCT — Modèle Conceptuel des Traitements

### Processus 1 : Pointage Kiosque

**Flux**: Employé saisit matricule + PIN → clique "Pointer"

**Opérations**:
1. Vérifier adresse IP dans config_systeme (ip_kiosque_autorisees)
2. Rechercher employé par matricule — vérifier statut = actif
3. Comparer PIN (bcrypt) — 3 erreurs = blocage 10 min sur ce terminal
4. Vérifier fenêtre horaire (arrivée 06h00-10h30 / départ 15h00-21h00)
5. Vérifier cohérence (doublon arrivée / départ sans arrivée)
6. INSERT presences + calcul statut_arrivee + INSERT audit_log

**Résultats**:
- Succès: Confirmation 4s (nom, photo, heure, statut)
- Échec: Message erreur spécifique — aucune insertion en base

### Processus 2 : Gestion des Congés

**Flux**: Employé remplit formulaire de demande (type, dates, motif)

**Opérations**:
1. Calculer jours ouvrables (excl. week-ends + jours fériés camerounais)
2. Vérifier solde disponible ≥ jours demandés (si congé annuel)
3. Détecter chevauchements avec demandes existantes approuvées
4A. ADMIN Approuve → UPDATE demande + UPDATE soldes (transaction MySQL atomique)
4B. ADMIN Refuse → UPDATE statut=refusee + commentaire_admin REQUIS
5. INSERT notification → Employé + INSERT audit_log (TRAITEMENT_CONGE)

**Cron 23h59**: NOTIF_CONGE_ATTENTE_48H → ADMIN si demande en attente depuis plus de 48h sans traitement

### Processus 3 : Gestion des Visiteurs

**Flux**: Visiteur se présente — Agent ouvre le formulaire

**Opérations**:
1. Saisir identité visiteur : nom, type pièce, N° pièce, téléphone
2. Motif visite + personne à voir + capture webcam (optionnel)
3. Générer badge : V[AAAAMMJJ]-[NNN] + QR code (qrcode.js)
4. INSERT visiteurs (heure_arrivee = heure serveur, statut = present)
5. Durée > seuil → NOTIF_VISITEUR_LONG → ADMIN + AGENT
6. Agent clique sortie → UPDATE heure_sortie + duree_visite_minutes

**Cron 23h59**: UPDATE statut=sorti + note "Sortie non enregistrée" pour visites encore ouvertes

---

## Diagramme de Cas d'Utilisation — 4 Acteurs

### Acteur ADMIN
- Gérer employés · Dashboard KPIs · Audit
- Consulter présences · Corriger pointage
- Approuver/Refuser congés · Rapports
- Chatbot RH IA · Notifications

### Acteur EMPLOYÉ
- Pointer arrivée/départ (kiosque PIN)
- Soumettre congé · Assistant IA rédaction
- Consulter solde · Planning · Docs RH
- Chatbot RH IA · Notifications

### Acteur AGENT
- Enregistrer visiteur · Badge QR · Sortie
- Vue unifiée temps réel · Historique visites
- Chatbot RH IA · Notifications

### Système (CRON)
- Absences auto · Clôture visites · Alertes

---

## Diagramme de Séquence — Pointage Kiosque

### Scénario Nominal

**Acteurs/Objets**: Employé → Page Kiosque → KioskController → EmployeModel → PresenceModel → AuditLogModel → MySQL 8

**Séquence**:
1. Employé saisit matricule + PIN → "Pointer arrivée"
2. Page Kiosque → POST /kiosque/pointer {matricule, pin, action}
3. KioskController vérifie IP config
4. KioskController → EmployeModel.findByMatricule()
5. MySQL 8 → SELECT * FROM employes WHERE matricule=?
6. EmployeModel → réponse employé trouvé
7. KioskController vérifie PIN bcrypt
8. KioskController → PresenceModel.enregistrerPointage(employe_id, heure, statut)
9. MySQL 8 → INSERT presences + calcul statut
10. KioskController → AuditLogModel.journaliser(POINTAGE, employe_id, ip, statut)
11. Page Kiosque → Employé: ✅ Confirmation 4s (nom, photo, heure, statut)

### Scénarios Alternatifs (échec)

- IP non autorisée → page bloquée
- PIN incorrect ×3 → blocage 10min + audit
- Hors fenêtre → message erreur
- Doublon arrivée → "Déjà pointé"
- Départ sans arrivée → "Aucune arrivée enregistrée"

### Note sur l'Assistant IA

L'appel API Anthropic Claude est effectué exclusivement côté serveur PHP par AnthropicService → la clé API ne transite jamais côté client navigateur. Rate limiting implémenté en MySQL (3 appels/h pour l'assistant congé, 20 messages/h pour le chatbot).

---

## Diagramme de Classes — 24 Classes en 4 Couches

### Controllers (8 classes)

- AuthController
- KioskController (IP+PIN)
- PresenceController
- EmployeController
- CongeController
- VisiteurController
- IAController (AJAX+JSON)
- ChatbotController

**Classe parent**: BaseController CI4 (abstract)

### Models (8 classes)

- UtilisateurModel
- EmployeModel (PIN)
- PresenceModel
- DemandeCongeModel
- SoldeCongeModel
- VisiteurModel
- NotificationModel
- AuditLogModel ⚠ (INSERT ONLY)

**Classe parent**: Model CI4 (abstract)

### Services (4 classes)

1. **CalendrierService**
   - Jours ouvrables camerounais
   - Jours fériés officiels
   - Timezone Africa/Douala

2. **SoldeCongeService**
   - Calcul OHADA Art.89
   - 1,5j/mois travaillé
   - Majorations ancienneté par tranche

3. **AnthropicService**
   - claude-sonnet-4
   - Prompt engineering
   - Rate limiting MySQL
   - Clé API côté serveur uniquement

4. **NotificationService**
   - 8 types d'alertes RH
   - Génération selon déclencheurs métier

### Filters (2 classes)

1. **AuthFilter**
   - Vérifie session active avant chaque contrôleur

2. **RoleFilter**
   - Vérifie rôle → 403 + INSERT audit_log si refus

### Relations UML

- **Héritage (extends)**: Controllers → BaseController, Models → Model
- **Dépendance (uses)**: Controller instancie Models + Services pour les traitements

---

## Chapitre IV — Réalisation

### 19 Modules en 5 Domaines

#### Domaine 1 : Auth & Accès (3 modules)

1. **Authentification**: bcrypt · 5 tentatives
2. **Mode Kiosque**: PIN · Restriction IP
3. **Gestion Rôles**: ADMIN · Employé · Agent

#### Domaine 2 : Présences (4 modules)

4. **Pointage Kiosque**: Arrivée · Départ
5. **Gestion Présences**: Vue quotidienne · Stats
6. **Correction Manuelle**: ADMIN · Journalisé
7. **Cron Absences**: Auto 23h59

#### Domaine 3 : Congés (3 modules)

8. **Demande Congé**: Calcul jours OHADA
9. **Approbation**: Transaction atomique
10. **Assistant IA Congé**: claude-sonnet-4

#### Domaine 4 : Visiteurs (3 modules)

11. **Enregistrement Visiteur**: Badge QR · Webcam
12. **Gestion Sorties**: Durée · Alerte seuil
13. **Vue Temps Réel**: AJAX · 2 min refresh

#### Domaine 5 : Transversaux (6 modules)

14. **Notifications**: 8 types d'alertes
15. **Journal d'Audit**: 21 types · Immuable
16. **Chatbot RH IA**: 20 msg/h/user
17. **Dashboard Financier**: Coût XAF · Graphiques
18. **Rapports & Exports**: PDF · CSV · 4 types
19. **Planning & Shifts**: Modèles · Affectations

---

## Stack Technique — 100% Open Source

### Technologies Utilisées

| Technologie | Usage | Statut |
|-------------|-------|--------|
| PHP 8.2 + CodeIgniter 4 | Backend MVC | 100% |
| MySQL 8.0 | 13 tables ACID | 100% |
| Bootstrap 5 + Chart.js | Frontend responsive | 100% |
| API Anthropic Claude (claude-sonnet-4) | IA intégrée | 85% |
| TCPDF / DOMPDF | Génération PDF | 90% |
| qrcode.js (local) | Badges visiteurs | 100% |

### Principe Architectural

**Défense en profondeur**:
- Vérification IP sur le kiosque
- Filtres AuthFilter + RoleFilter sur toutes les routes
- Clé API Anthropic stockée côté serveur uniquement
- Fichiers RH hors racine web avec noms UUID

---

## Sécurité — 11 Mesures Implémentées

1. **Anti-injection SQL**: QueryBuilder CodeIgniter 4 + PDO paramétré — zéro concaténation SQL directe
2. **Protection XSS**: htmlspecialchars() sur toutes les sorties HTML — couche Vue
3. **Tokens CSRF**: Formulaires POST + endpoints AJAX (header X-CSRF-Token)
4. **Hachage bcrypt (coût=12)**: Mots de passe + PIN kiosque — jamais stockés en clair
5. **Brute Force Login**: Blocage 15 min après 5 tentatives échouées, journalisé
6. **Restriction IP Kiosque**: Pointage possible uniquement depuis l'IP du terminal physique (config_systeme)
7. **RBAC complet (AuthFilter + RoleFilter)**: Appliqués sur tous les groupes de routes — redirection 403 + audit si refus
8. **Fichiers hors racine web**: Documents RH stockés avec noms UUID, type MIME vérifié côté serveur
9. **Clé API IA sécurisée**: config_systeme BDD — jamais transmise côté client
10. **Journal d'Audit immuable**: AuditLogModel : INSERT uniquement — aucun UPDATE/DELETE possible — 21 types d'événements
11. **Données confidentielles**: Salaires journaliers visibles ADMIN uniquement — filtrage SQL par rôle

### Résultat Tests de Sécurité

**6 tests réalisés — 6/6 réussis (100%)**:
- Injections SQL bloquées
- CSRF rejeté
- Accès hors rôle redirigé en 403
- Pointage depuis IP non autorisée bloqué
- Brute force PIN neutralisé

---

## Tests & Validation

### Stratégie de Tests à 4 Niveaux

| Type | Score | Détails |
|------|-------|---------|
| Tests unitaires (PHPUnit) | 10/10 | calculerJoursOuvrables, calculerSoldeOHADA, verifierFenetrePointage, calculerStatutArrivee |
| Tests d'intégration | 10/10 | Pointage nominal, PIN incorrect × 3, Congé solde insuffisant, Approbation ADMIN, Chatbot RH, Accès non autorisé |
| Tests fonctionnels | 24/25 | 25 scénarios · 1 anomalie corrigée (fuseau horaire UTC → Africa/Douala) |
| Tests de sécurité | 6/6 | Injection SQL, CSRF, Fichiers RH, Hors rôle, Brute force PIN, IP non autorisée |
| Tests multi-navigateurs | 3/3 | Chrome, Firefox, Edge — comportement identique |

### Statistiques Globales

| Métrique | Valeur |
|----------|--------|
| Taux de réussite global | 98,1% |
| Cas testés | 54 |
| Réussis | 53 |
| Anomalies corrigées | 1 |

### Anomalie Détectée et Corrigée

Les horodatages des présences étaient générés en UTC au lieu du fuseau Africa/Douala (UTC+1). Correction appliquée dans la configuration avant la mise en production.

---

## Impact & Gains Organisationnels

### Comparaison Avant / Après

| Domaine | ❌ AVANT | ✅ APRÈS |
|---------|----------|----------|
| Présences | Aucun enregistrement — information orale ou WhatsApp — aucun calcul possible | Pointage horodaté au kiosque (anti-fraude IP) — calcul automatique statuts — audit complet |
| Congés | Demande verbale ou SMS — aucun formulaire — solde non calculé — chevauchements ignorés | Formulaire structuré + calcul jours ouvrables camerounais — solde temps réel — assistant IA rédaction |
| Visiteurs | Aucun enregistrement — identité non collectée — aucun historique — aucune visibilité | Enregistrement complet — badge QR imprimable — vue unifiée temps réel — historique consultable |

### Gains Mesurables

| Gain | Description |
|------|-------------|
| ⏰ Temps | 3-4h / semaine économisées pour le responsable RH (calculs manuels supprimés) |
| ⚖️ Conformité | OHADA — Calcul 1,5 j/mois + ancienneté art. 89 Code du Travail |
| 📊 KPIs | Décisions basées sur des données factuelles actualisées |
| 🔐 Traçabilité | 21 types d'événements journalisés — immuables |

---

## Conclusion & Perspectives

### Ce que ce Projet Démontre

1. **Réponse concrète à la problématique**: Le système calcule automatiquement les droits à congé (Art. 89), intègre le calendrier officiel camerounais, sécurise le pointage par IP et offre une vue unifiée temps réel.

2. **Maîtrise complète du cycle GSI**: De l'analyse (MERISE, UML) au déploiement (serveur local, cron PHP), en passant par la conception, le développement et les tests.

3. **Adaptation au contexte camerounais**: FCFA, fériés nationaux, Code du Travail OHADA, contraintes d'infrastructure locale intégrés dans chaque décision technique.

4. **Innovation IA dans une PME de 13 personnes**: L'intégration de Claude Sonnet-4 démontre qu'une petite structure peut accéder aux outils d'IA avec un budget maîtrisé.

### Perspectives d'Évolution

#### Court Terme (C)
- Lecteur biométrique sur le terminal kiosque
- Application mobile Android pour les employés

#### Moyen Terme (M)
- Connexion avec les logiciels de paie camerounais
- Intégration CNPS (Caisse Nationale de Prévoyance Sociale)

#### Long Terme (L)
- Tableau de bord prédictif IA
- Anticipation des tendances d'absentéisme
- Redis pour le rate limiting à grande échelle

### Citation de Conclusion

> Ce projet illustre qu'avec des technologies open-source accessibles et une démarche rigoureuse, une PME de 13 personnes peut se doter d'outils de gestion à la hauteur de ses ambitions — et qu'un stagiaire de BTS GSI peut livrer une solution opérationnelle, sécurisée et conforme.

---

## Résumé Final

| Métrique | Valeur |
|----------|--------|
| Cas testés | 54 |
| Taux de réussite | 98,1% |
| Modules livrés | 19 |
| Domaines | 5 |
| Tables BDD | 13 |
| Classes PHP | 24 |
| Conformité | Code du Travail camerounais · Cadre OHADA |

---

## Coordonnées de l'Étudiante

- **Nom**: BAMI Paule Cassandra
- **Formation**: BTS GSI 2e année
- **Institution**: IUGET / South Polytech
- **Matricule**: 26GSI0063
- **Encadreur académique**: M. MBIANDJI Jonathan
- **Encadreur professionnel**: M. MPEKE Ramiro
- **Entreprise**: Ô Canada SARLU
- **Période de stage**: 05 Août – 06 Septembre 2025
