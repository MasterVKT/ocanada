**Ô CANADA**

Application de Gestion RH

**PLAN DE DÉVELOPPEMENT**

  -----------------------------------------------------------------------
  **Version**             **Date**                **Durée totale
                                                  estimée**
  ----------------------- ----------------------- -----------------------
  1.0                     Mars 2026               17 semaines (\~4 mois)

  -----------------------------------------------------------------------

**1. Présentation du Projet**

**1.1 Rappel du périmètre**

L\'application Ô Canada est une solution de gestion RH complète pour une
entreprise de 13 employés basée à Douala, Cameroun. Elle couvre :
gestion des présences et pointage, gestion des congés, gestion des
visiteurs, notifications, planning, documents RH, rapports, et deux
modules d\'intelligence artificielle (assistant rédaction et chatbot
RH).

**1.2 Objectifs du plan de développement**

-   Fournir une feuille de route précise et ordonnée par priorité et
    dépendances techniques.

-   Identifier les phases critiques et les risques associés.

-   Définir les livrables et critères d\'acceptation pour chaque phase.

-   Servir de référence pour le suivi d\'avancement du projet.

**1.3 Résumé du calendrier**

  -----------------------------------------------------------------------------------
  **Phase**   **Module(s)**                           **Durée**   **Semaines**
  ----------- --------------------------------------- ----------- -------------------
  0           Setup environnement, infrastructure,    1 sem.      S1
              base de données                                     

  1           Authentification, Gestion des employés  2 sem.      S2--S3

  2           Présences, Mode kiosque de pointage     2 sem.      S4--S5

  3           Congés (demandes + approbations +       2 sem.      S6--S7
              soldes), Planning & Shifts                          

  4           Gestion visiteurs, Vue unifiée temps    1.5 sem.    S8--S9
              réel                                                

  5           Notifications, Documents RH, Rapports & 2 sem.      S10--S11
              Exports                                             

  6           Tableaux de bord (Admin, Employé,       1.5 sem.    S12--S13
              Agent), Calendrier camerounais                      

  7           Modules IA (Assistant congé + Chatbot   1.5 sem.    S13--S14
              RH)                                                 

  8           Tableau de bord financier, Journal      1 sem.      S15
              d\'audit                                            

  9           Tests, QA, corrections, recette client  2 sem.      S16--S17

  10          Déploiement production, formation,      1 sem.      S17
              documentation utilisateur                           
  -----------------------------------------------------------------------------------

> *La Phase 9 (Tests & QA) est un minimum vital et ne doit pas être
> réduite, même sous pression de délai. Les bugs découverts lors de la
> recette client peuvent allonger le calendrier de 1 à 2 semaines
> supplémentaires.*

**2. Prérequis et Standards**

**2.1 Environnement de développement**

  -----------------------------------------------------------------------
  **Outil**           **Usage**               **Configuration**
  ------------------- ----------------------- ---------------------------
  PHP 8.2+            Interpréteur backend    Extensions : pdo_mysql,
                                              mbstring, curl, fileinfo,
                                              gd, intl, zip activées

  MySQL 8.0+          Base de données de      Mode strict activé, utf8mb4
                      développement           

  Composer 2.x        Gestionnaire de         CodeIgniter 4.5.x, DOMPDF
                      dépendances PHP         via composer.json

  Serveur local       Serveur de              PHP built-in (php spark
                      développement           serve) ou
                                              XAMPP/WAMP/Laragon

  VS Code / PhpStorm  IDE                     Extensions PHP, CI4
                                              snippets recommandées

  Git                 Contrôle de version     Repository principal +
                                              branches par phase

  MySQL Workbench /   Administration DB       Visualisation et exécution
  DBeaver                                     des migrations

  Postman             Test des endpoints AJAX Collections pour tous les
                                              endpoints JSON
  -----------------------------------------------------------------------

**2.2 Standards de développement**

**Conventions de nommage**

-   **Contrôleurs** : PascalCase, suffixe Controller (ex:
    EmployeesController)

-   **Modèles** : PascalCase, suffixe Model (ex: EmployeModel)

-   **Vues** : snake_case, nom descriptif (ex:
    admin/employes/create.php)

-   **Méthodes publiques** : camelCase (ex: getActiveList())

-   **Variables PHP** : snake_case (ex: \$date_debut)

-   **Variables JavaScript** : camelCase (ex: dateDebut)

-   **Classes CSS** : kebab-case (ex: kpi-card-primary)

-   **Tables DB** : snake_case, pluriel (ex: presences, soldes_conges)

-   **Colonnes DB** : snake_case (ex: date_embauche, heure_arrivee)

**Standards de code PHP**

-   PSR-12 pour le style de code (indentation 4 espaces, accolades,
    etc.).

-   Typage strict déclaré : declare(strict_types=1) en tête de chaque
    fichier PHP.

-   Docblocks PHPDoc pour toutes les méthodes publiques des contrôleurs
    et modèles.

-   Aucune logique métier dans les vues --- les vues reçoivent
    uniquement des données formatées.

-   Toutes les requêtes SQL via le Query Builder CI4 ou requêtes
    préparées (jamais de string concaténée avec des variables
    utilisateur).

**Standards de code JavaScript**

-   ES6+ : utilisation de const/let (pas de var), arrow functions,
    template literals.

-   Toutes les requêtes AJAX via fetch() avec async/await et gestion
    d\'erreur try/catch.

-   Token CSRF inclus dans chaque requête POST AJAX (lu depuis le meta
    tag csrf-token dans le \<head\>).

-   Aucune bibliothèque externe sauf celles listées dans les SFD
    (Bootstrap, Chart.js, qrcode.js).

**2.3 Gestion du code source (Git)**

-   **Branche main** : code de production stable uniquement.

-   **Branche develop** : intégration des phases en cours.

-   **Branches de phase** : feature/phase-N-description (ex:
    feature/phase-2-kiosque).

-   **Commits** : convention \"\[Phase N\] Description courte de la
    modification\" (ex: \"\[Phase 2\] Ajout validation fenêtre horaire
    kiosque\").

-   **Merges** : via Pull Request (ou merge request) avec revue de code
    avant intégration dans develop.

**3. Phases de Développement Détaillées**

  --------- -------------------------------------------- -----------------
  **Phase   **Setup & Infrastructure**                   **1 semaine**
  0**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Mettre en place l\'environnement complet et fonctionnel sur lequel
toutes les phases suivantes s\'appuieront. Aucun raccourci ne doit être
pris à cette étape.

**Tâches détaillées**

-   **T0.1 --- Initialisation du projet CI4** : Installation de
    CodeIgniter 4 via Composer (composer create-project
    codeigniter4/appstarter ocanada), configuration du .env (base URL,
    DB credentials, timezone Africa/Douala), activation du mode
    développement.

-   **T0.2 --- Configuration de la base de données** : Création de la
    base de données ocanada_db en MySQL (utf8mb4, mode strict), création
    de l\'utilisateur DB avec permissions minimales (SELECT, INSERT,
    UPDATE, DELETE, CREATE, DROP sur la DB uniquement).

-   **T0.3 --- Migrations DB** : Écriture et exécution des 8 migrations
    CI4 (toutes les tables du modèle de données des SFD). Vérification
    des contraintes de clés étrangères et des index.

-   **T0.4 --- Seeders** : Écriture et exécution du InitialDataSeeder :
    jours fériés 2026, config_systeme par défaut, shift standard, compte
    admin initial.

-   **T0.5 --- Structure des répertoires** : Création de la structure
    app/Controllers/ (sous-répertoires Admin, Employee, Agent),
    app/Views/ (sous-répertoires par module), storage/uploads/
    (employees, documents, visitors), public/assets/ (css, js, img).

-   **T0.6 --- Assets de base** : Intégration Bootstrap 5.3 (CDN dans
    layout), Bootstrap Icons (CDN), Inter + Roboto Mono (Google Fonts
    CDN). Création du fichier public/assets/css/ocanada.css avec les
    variables CSS Bootstrap surchargées (couleurs de la Charte
    Graphique).

-   **T0.7 --- Layouts principaux** : Création des 3 layouts (main.php,
    kiosque.php, auth.php). Implémentation de la sidebar (3 versions :
    admin, employé, agent), topbar, footer. Ces layouts doivent être
    100% fonctionnels visuellement.

-   **T0.8 --- Filtres CI4** : Implémentation et test de AuthFilter,
    RoleFilter, KiosqueIPFilter. Configuration dans
    app/Config/Filters.php.

-   **T0.9 --- BaseController et services communs** : Implémentation de
    BaseController, des helpers partagés (formatage date FR, formatage
    XAF, calcul jours ouvrables).

-   **T0.10 --- Page d\'erreur** : Implémentation des pages 403, 404,
    500 avec le style de la charte graphique.

**Critères d\'acceptation Phase 0**

-   La structure de base du projet est opérationnelle et accessible via
    navigateur.

-   Toutes les tables DB sont créées avec les bonnes contraintes.

-   Le layout principal s\'affiche correctement sur desktop et tablette.

-   Les 3 filtres CI4 fonctionnent (test manuel avec une route
    protégée).

  --------- -------------------------------------------- -----------------
  **Phase   **Authentification & Gestion des Employés**  **2 semaines**
  1**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Permettre à l\'administrateur de se connecter, de gérer les comptes
employés, et de naviguer dans l\'application.

**Tâches --- Semaine 1 : Authentification**

-   **T1.1 --- Page de connexion** : Formulaire HTML/Bootstrap (charte
    graphique), validation client-side minimale (champs non vides),
    soumission POST vers AuthController.

-   **T1.2 --- Logique de connexion** : AuthController::login() ---
    vérification email/statut/mot de passe bcrypt, gestion des 5
    tentatives et blocage 15 min via cache CI4, création session,
    redirection selon rôle, journalisation audit_log.

-   **T1.3 --- Page mot de passe oublié** : Génération token 64 chars,
    stockage en DB, affichage du lien (SMTP optionnel). Page de
    réinitialisation avec validation des contraintes mot de passe.

-   **T1.4 --- Déconnexion** : Destruction session, redirection,
    journalisation.

-   **T1.5 --- Middleware de protection** : Test que toutes les routes
    protégées redirigent vers /login sans session valide.

**Tâches --- Semaine 2 : Gestion des employés**

-   **T1.6 --- Liste des employés** : Tableau paginé (10/page) avec
    recherche temps réel, filtres statut/département, boutons Actions.

-   **T1.7 --- Formulaire de création (wizard 3 étapes)** : Étape 1
    données personnelles, étape 2 données professionnelles, étape 3
    accès et sécurité. Navigation entre étapes avec validation
    progressive. Génération automatique du matricule EMP-XXXX. Upload
    photo de profil.

-   **T1.8 --- Fiche employé et modification** : Affichage de toutes les
    informations. Formulaire de modification avec journalisation des
    champs sensibles.

-   **T1.9 --- Désactivation** : Confirmation modale, désactivation
    compte, conservation historique.

-   **T1.10 --- Gestion du profil utilisateur** : Page profil (tous
    rôles) avec changement de mot de passe et changement de PIN kiosque.

**Critères d\'acceptation Phase 1**

-   L\'admin peut se connecter et se déconnecter.

-   Le blocage après 5 tentatives fonctionne.

-   L\'admin peut créer un employé avec toutes ses données et un compte
    de connexion.

-   La liste est paginée, filtrable et recherchable.

-   La désactivation empêche la connexion de l\'employé désactivé.

  --------- -------------------------------------------- -----------------
  **Phase   **Présences & Mode Kiosque**                 **2 semaines**
  2**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Mettre en place le cœur de la gestion des présences : le terminal de
pointage sécurisé et la gestion/consultation des présences par l\'admin.

**Tâches --- Semaine 1 : Mode kiosque**

-   **T2.1 --- Layout kiosque** : Page plein écran fond #1A365D, logo,
    date en toutes lettres, horloge temps réel JavaScript (mise à jour
    seconde par seconde via setInterval).

-   **T2.2 --- KiosqueIPFilter opérationnel** : Test avec la valeur
    ip_kiosque_autorisees de config_systeme. Message d\'erreur si IP non
    autorisée.

-   **T2.3 --- Formulaire de pointage** : Champ matricule/email, champ
    PIN masqué, boutons Arrivée et Départ (taille et couleurs kiosque
    selon charte). Validation client-side (champs requis, PIN uniquement
    numérique).

-   **T2.4 --- Logique pointage arrivée** :
    KiosqueController::pointArrivee() --- identification employé,
    vérification PIN bcrypt, vérification fenêtre horaire
    (06h00--10h30), vérification doublon, calcul statut (présent/retard)
    via PresenceCalculator, insertion en DB, journalisation audit_log.

-   **T2.5 --- Logique pointage départ** : Vérification arrivée
    existante, enregistrement départ, calcul durée travaillée, fenêtre
    15h00--21h00.

-   **T2.6 --- Zone de confirmation kiosque** : Affichage photo + nom +
    heure + statut pendant 4 secondes après pointage réussi, puis
    réinitialisation automatique du formulaire.

-   **T2.7 --- Gestion PIN --- 3 erreurs consécutives** : Blocage
    temporaire 10 min du compte sur le terminal, journalisation
    ECHEC_PIN_KIOSQUE.

**Tâches --- Semaine 2 : Gestion des présences**

-   **T2.8 --- Vue quotidienne (admin)** : Tableau de tous les employés
    actifs pour la date du jour. Code couleur des lignes. Sélecteur de
    date.

-   **T2.9 --- Historique des présences** : Filtres (employé, période,
    statut). Calcul des totaux en bas de tableau.

-   **T2.10 --- Correction manuelle** : Formulaire de correction avec
    commentaire obligatoire, flag source=correction_admin, conservation
    dans audit_log.

-   **T2.11 --- Statistiques présences** : Tableau récapitulatif mensuel
    par employé.

-   **T2.12 --- Script CRON mark-absences** : Implémentation de la CI4
    Command ocanada:mark-absences. Test en simulation (exécution
    manuelle).

-   **T2.13 --- Vue personnelle employé** : L\'employé voit ses propres
    présences avec ses statistiques du mois.

**Critères d\'acceptation Phase 2**

-   Le kiosque refuse les IP non autorisées.

-   Un pointage d\'arrivée réussi crée un enregistrement correct dans
    presences.

-   Le statut retard est calculé correctement selon l\'heure de début du
    shift.

-   La correction manuelle est journalisée dans audit_log.

-   Le script CRON mark-absences insère les bonnes absences lors d\'une
    simulation.

  --------- -------------------------------------------- -----------------
  **Phase   **Congés & Planning**                        **2 semaines**
  3**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Implémenter le circuit complet de gestion des congés (demande →
approbation → solde) et le module de planning des shifts.

**Tâches --- Semaine 1 : Gestion des congés**

-   **T3.1 --- Initialisation des soldes** :
    SoldeCongeModel::initForEmployee() --- calcul automatique selon
    ancienneté OHADA (1.5 j/mois × mois travaillés + majoration
    ancienneté). Initialisation lors de la création d\'un employé (Phase
    1 rétro-fit).

-   **T3.2 --- Formulaire de demande (employé)** : Sélection type, dates
    (date picker), calcul temps réel des jours ouvrables via
    WorkingDaysCalculator (appel AJAX vers /employe/conges/calc-jours),
    champ motif, validations (solde suffisant, pas de chevauchement, ≥ 1
    jour ouvrable).

-   **T3.3 --- Soumission et notification** : Enregistrement en DB,
    création notification NOTIF_CONGE_SOUMIS pour l\'admin.

-   **T3.4 --- Interface admin de gestion des congés** : Liste toutes
    demandes avec filtres, tri par statut + date. Fiche demande avec
    solde courant, chevauchements.

-   **T3.5 --- Approbation / refus** : Mise à jour statut,
    déduction/restitution jours solde (dans transaction DB),
    notification employé, journalisation.

-   **T3.6 --- Vue des soldes (admin)** : Tableau récapitulatif tous
    employés avec filtre par année. Ajustement manuel avec commentaire
    obligatoire.

-   **T3.7 --- Historique congés (employé)** : Ses propres demandes avec
    statut et commentaire admin.

-   **T3.8 --- CRON pending-leaves** : Script de notification demandes
    en attente \> 48h.

**Tâches --- Semaine 2 : Planning & Shifts**

-   **T3.9 --- CRUD modèles de shifts** : Formulaire
    création/modification shift (nom, heure début/fin, pause, jours
    actifs).

-   **T3.10 --- Affectation de shifts aux employés** : Interface
    d\'affectation avec période de validité.

-   **T3.11 --- Vue calendrier hebdomadaire (admin)** : Grille semaine ×
    employés avec shifts affectés et indicateurs de présence réelle pour
    les jours passés.

-   **T3.12 --- Vue planning employé** : Affichage de ses horaires sur 2
    semaines.

-   **T3.13 --- Intégration shift dans calcul présence** : Assurer que
    le calcul de retard utilise l\'heure de début du shift affecté à
    l\'employé (et non la valeur par défaut en dur).

**Critères d\'acceptation Phase 3**

-   Le calcul des jours ouvrables exclut correctement week-ends et jours
    fériés camerounais.

-   Un congé approuvé déduit les jours du solde dans la même transaction
    DB.

-   Le blocage de soumission fonctionne si solde insuffisant.

-   L\'intégration shift → calcul retard est fonctionnelle.

  --------- -------------------------------------------- -----------------
  **Phase   **Visiteurs & Vue Temps Réel**               **1.5 semaines**
  4**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Implémenter la gestion des visiteurs et la vue unifiée en temps réel,
fonctionnalité différenciante principale de l\'application.

**Tâches**

-   **T4.1 --- Formulaire d\'enregistrement visiteur** : Tous les champs
    définis dans les SFD. Capture photo webcam (optionnel, via
    JavaScript MediaDevices API). Génération automatique du numéro de
    badge V\[YYYYMMDD\]-\[NNN\].

-   **T4.2 --- Badge visiteur** : Affichage écran du badge avec QR code
    (qrcode.js intégré localement). Feuille de style \@media print pour
    impression propre du badge uniquement. Bouton Imprimer.

-   **T4.3 --- Liste visiteurs présents** : Tableau temps réel avec
    durées de présence calculées en JavaScript (mise à jour toutes les
    minutes). Bouton Enregistrer la sortie par ligne.

-   **T4.4 --- Enregistrement sortie** : Confirmation, enregistrement
    heure_sortie, calcul durée, journalisation.

-   **T4.5 --- Alerte visiteur long séjour** : Mise en rouge des lignes
    dépassant le seuil configurable. Notification NOTIF_VISITEUR_LONG
    pour admin et agent.

-   **T4.6 --- Historique des visiteurs** : Filtres (date, nom,
    CNI/passeport, personne visitée).

-   **T4.7 --- CRON close-visits** : Fermeture automatique des visites
    ouvertes à 23h59.

-   **T4.8 --- Vue unifiée temps réel** : Page combinant employés
    présents (de la table presences, date du jour, sans heure_depart) et
    visiteurs présents (statut=present). Compteurs globaux en tête.
    Actualisation automatique toutes les 2 minutes via AJAX ou
    rechargement partiel. Section employés absents (masquable).

-   **T4.9 --- Tableau de bord agent d\'accueil** : La vue temps réel
    est la page d\'accueil de l\'agent. Son menu est restreint aux
    modules visiteurs, notifications, chatbot.

**Critères d\'acceptation Phase 4**

-   Le badge s\'affiche avec un QR code valide et s\'imprime
    correctement.

-   La vue temps réel s\'actualise automatiquement sans rechargement
    manuel.

-   La durée de présence d\'un visiteur se met à jour en temps réel côté
    JavaScript.

-   Le CRON close-visits fonctionne en simulation.

  --------- -------------------------------------------- -----------------
  **Phase   **Notifications, Documents & Rapports**      **2 semaines**
  5**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Implémenter le système de notifications internes, la gestion
documentaire RH et la génération des rapports PDF/CSV.

**Tâches --- Semaine 1 : Notifications et Documents**

-   **T5.1 --- Système de notifications (backend)** : NotificationModel
    et NotificationService. Vérifier que tous les déclencheurs de
    notifications (congés, absences, visiteurs, contrats) appellent bien
    NotificationService depuis leurs contrôleurs respectifs.

-   **T5.2 --- Cloche de notification (frontend)** : Badge numérique
    dans la topbar. Au clic : panneau déroulant des 10 dernières non
    lues. Polling AJAX toutes les 60 secondes pour actualiser le badge.
    Marquer lue au clic sur une notification.

-   **T5.3 --- Page des notifications** : Liste paginée, filtres,
    marquer lu/tout lire.

-   **T5.4 --- Upload de documents RH (admin)** : Validation type MIME,
    taille, UUID filename, stockage dans storage/, référence en DB.

-   **T5.5 --- Téléchargement de documents** : Contrôle des droits,
    service du fichier via readfile() avec headers HTTP corrects.

-   **T5.6 --- Interface documents admin** : Liste par employé, filtre
    par type, bouton supprimer avec confirmation et journalisation.

-   **T5.7 --- Interface documents employé** : Ses propres documents en
    lecture seule.

**Tâches --- Semaine 2 : Rapports**

-   **T5.8 --- Intégration DOMPDF** : Installation via Composer,
    vérification du rendu HTML→PDF. Création d\'un template PDF de base
    avec logo Ô Canada, en-tête, pied de page.

-   **T5.9 --- Rapport présences mensuel** : PDF A4, un tableau par
    employé. Export CSV correspondant.

-   **T5.10 --- Rapport congés annuels** : PDF, soldes et détail des
    congés pris.

-   **T5.11 --- Rapport journal des visiteurs** : PDF et CSV, toutes les
    colonnes.

-   **T5.12 --- Rapport absentéisme** : PDF uniquement, taux par
    employé + comparaison mensuelle.

-   **T5.13 --- Journalisation des rapports** : Chaque génération de
    rapport insère un événement GENERATION_RAPPORT dans audit_log.

-   **T5.14 --- CRON check-contracts** : Notifications pour contrats CDD
    expirant dans 30 jours.

**Critères d\'acceptation Phase 5**

-   Toutes les notifications se créent correctement lors de leurs
    déclencheurs respectifs.

-   Un document uploadé est accessible uniquement par l\'admin ou
    l\'employé concerné.

-   Les 4 rapports PDF se génèrent sans erreur et sont lisibles.

  --------- -------------------------------------------- -----------------
  **Phase   **Tableaux de Bord & Calendrier              **1.5 semaines**
  6**       Camerounais**                                

  --------- -------------------------------------------- -----------------

**Objectif**

Finaliser les tableaux de bord de chaque rôle et implémenter le module
de calendrier camerounais et conformité OHADA.

**Tâches**

-   **T6.1 --- Tableau de bord admin (KPIs)** : Calcul et affichage des
    4 blocs de KPIs (présences, visiteurs, congés, alertes). Boutons
    d\'accès rapide.

-   **T6.2 --- Graphiques du tableau de bord admin** : Chart.js intégré.
    Graphique 1 : courbe des 30 derniers jours (taux de présence), en
    excluant jours non ouvrables. Graphique 2 : camembert répartition
    statuts du mois. Graphique 3 : barres visiteurs par semaine (4
    semaines).

-   **T6.3 --- Tableau de bord employé** : Bloc de bienvenue avec statut
    de pointage du jour. Statistiques du mois. Solde de congés.
    Mini-calendrier mensuel avec codes couleur.

-   **T6.4 --- Tableau de bord agent** : Redirection vers vue temps
    réel + accès rapides visiteurs.

-   **T6.5 --- Module calendrier camerounais** : Interface de
    configuration admin (ajout, modification, suppression de jours
    fériés). Affichage du calendrier pour l\'année courante.

-   **T6.6 --- Conformité OHADA dans les soldes** : Vérification et test
    du calcul des droits à congé : 1.5 j/mois, majorations par
    ancienneté, prorata pour embauche en cours d\'année, congé maternité
    non décompté du solde annuel.

**Critères d\'acceptation Phase 6**

-   Les 3 graphiques s\'affichent et se mettent à jour correctement.

-   Le mini-calendrier de l\'employé affiche les bons statuts.

-   L\'administrateur peut ajouter un jour férié exceptionnel et les
    calculs de jours ouvrables sont immédiatement impactés.

  --------- -------------------------------------------- -----------------
  **Phase   **Modules d\'Intelligence Artificielle**     **1.5 semaines**
  7**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Intégrer l\'API Anthropic Claude pour l\'assistant de rédaction de congé
et le chatbot RH interne.

**Tâches**

-   **T7.1 --- AnthropicClient** : Implémentation de la library
    AnthropicClient.php. Test de connectivité avec l\'API Anthropic
    (appel simple depuis un script PHP de test). Gestion des timeouts et
    erreurs HTTP.

-   **T7.2 --- RateLimiter** : Implémentation du rate limiter via cache
    CI4. Test de la limite 3 appels/heure (assistant) et 20/heure
    (chatbot).

-   **T7.3 --- Assistant rédaction de congé** : Panneau UI (bouton ✨,
    mini-formulaire, zone de résultat avec chargement). Endpoint PHP
    /ia/assistant-conge. Construction du prompt système. Test avec
    différents types de congés et formulations. Boutons Utiliser ce
    motif / Regénérer.

-   **T7.4 --- Chatbot RH --- Frontend** : Bouton flottant
    (bottom-right), panneau chat (overlay), interface messages alternés
    gauche/droite, indicateur typing, gestion de l\'historique en
    JavaScript (sessionStorage ou variable JS), limite 10 échanges.

-   **T7.5 --- Chatbot RH --- Backend** : Endpoint /ia/chatbot.
    Récupération des données contextuelles selon le rôle. Construction
    du prompt système. Appel API avec historique. Test des questions
    types des SFD (solde congés, présences, règles RH).

-   **T7.6 --- Tests des limites du chatbot** : Vérifier que le chatbot
    refuse les questions hors périmètre RH. Vérifier qu\'un employé ne
    peut pas obtenir les données d\'un autre employé.

-   **T7.7 --- Configuration de la clé API** : Interface d\'admin dans
    Config pour saisir/modifier la clé API Anthropic (stockée dans
    config_systeme).

**Critères d\'acceptation Phase 7**

-   L\'assistant de congé génère un motif formel en français en \< 3
    secondes.

-   Le chatbot répond correctement aux 6 questions types listées dans
    les SFD pour les employés.

-   Le rate limit bloque un utilisateur ayant atteint sa limite horaire.

-   En cas d\'indisponibilité API, le message de fallback s\'affiche
    correctement.

  --------- -------------------------------------------- -----------------
  **Phase   **Tableau de Bord Financier & Journal        **1 semaine**
  8**       d\'Audit**                                   

  --------- -------------------------------------------- -----------------

**Objectif**

Finaliser les deux derniers modules fonctionnels : le tableau de bord RH
financier et le journal d\'audit complet.

**Tâches**

-   **T8.1 --- Tableau de bord financier** : Calcul du coût estimé des
    absences (jours_absence × salaire_journalier). Calcul impact des
    retards (équivalent en jours). Graphique comparatif 6 mois.
    Classement employés par taux de présence. Vérification que les
    salaires individuels ne sont jamais affichés.

-   **T8.2 --- Journal d\'audit --- interface** : Tableau paginé
    (25/page), filtres par type/utilisateur/dates. Modale de détail avec
    données avant/après. Export CSV.

-   **T8.3 --- Vérification de couverture d\'audit** : Passer en revue
    les 21 types d\'événements listés dans les SFD. Vérifier que chaque
    action sensible dans l\'application insère bien un événement dans
    audit_log.

-   **T8.4 --- Immutabilité de l\'audit_log** : Vérifier qu\'aucune
    route ni méthode ne permet de modifier ou supprimer des entrées du
    journal.

  --------- -------------------------------------------- -----------------
  **Phase   **Tests, QA et Recette Client**              **2 semaines**
  9**                                                    

  --------- -------------------------------------------- -----------------

**Objectif**

Valider l\'ensemble de l\'application par des tests fonctionnels
exhaustifs, corriger les anomalies et préparer la recette client.

**Plan de tests**

  ------------------------------------------------------------------------
  **Type de test**    **Scope**                **Outils / Méthode**
  ------------------- ------------------------ ---------------------------
  Tests unitaires PHP Modèles, Libraries       PHPUnit (CI4 intégré)
                      (PresenceCalculator,     
                      WorkingDaysCalculator,   
                      calcul OHADA)            

  Tests               Flux complets : pointage Scripts PHP de test +
  d\'intégration      → présence →             inspection DB
                      statistique. Soumission  
                      → approbation congé →    
                      déduction solde.         

  Tests fonctionnels  Chaque écran et          Checklist manuelle +
                      fonctionnalité selon les navigateur
                      SFD                      

  Tests de sécurité   Injection SQL            Tests manuels + OWASP
                      (tentatives via          checklist
                      formulaires), CSRF       
                      (désactivation token),   
                      accès non autorisés      
                      (manipulation URLs),     
                      accès fichiers directs   

  Tests de navigation Vérifier que chaque rôle Connexion avec 3 comptes
  rôles               (admin, employé, agent)  différents
                      accède uniquement aux    
                      ressources autorisées    

  Tests responsive    Affichage sur 1280px,    DevTools navigateur +
                      1024px, 768px            tablette réelle si
                                               disponible

  Tests CRON          Exécution manuelle des 4 Ligne de commande php spark
                      scripts CRON avec        
                      vérification des         
                      résultats en DB          

  Tests IA            Assistant congé et       Tests manuels + simulation
                      chatbot RH :             d\'erreur API
                      fonctionnement, rate     
                      limiting, messages       
                      d\'erreur                
  ------------------------------------------------------------------------

**Checklist de recette client (partielle)**

-   L\'admin peut créer un employé complet (wizard 3 étapes) et
    l\'employé peut se connecter.

-   L\'employé peut pointer son arrivée sur le kiosque et le statut
    correct apparaît (présent/retard).

-   L\'employé peut soumettre une demande de congé et l\'admin peut
    l\'approuver.

-   Un visiteur peut être enregistré et son badge imprimé.

-   La vue temps réel affiche correctement employés et visiteurs.

-   Le chatbot répond à \"Combien de jours de congé me reste-t-il ?\"

-   L\'admin peut générer et télécharger le rapport mensuel des
    présences.

-   L\'interface est lisible et utilisable sur tablette (768px).

  --------- -------------------------------------------- -----------------
  **Phase   **Déploiement, Formation & Documentation**   **1 semaine**
  10**                                                   

  --------- -------------------------------------------- -----------------

**Tâches**

-   **T10.1 --- Déploiement production** : Installation du serveur,
    configuration Apache/Nginx, HTTPS (Let\'s Encrypt), exécution des
    migrations sur la DB de production, configuration des CRON sur le
    serveur.

-   **T10.2 --- Configuration initiale** : Saisie de l\'IP du kiosque
    dans config_systeme, saisie de la clé API Anthropic, ajout des jours
    fériés de l\'année, création des comptes utilisateurs initiaux.

-   **T10.3 --- Manuel utilisateur** : Guide d\'utilisation par rôle
    (admin, employé, agent). Avec captures d\'écran annotées. Format
    PDF.

-   **T10.4 --- Guide administrateur technique** : Procédures de
    sauvegarde DB, gestion des CRON, mise à jour de l\'application,
    résolution des problèmes courants.

-   **T10.5 --- Session de formation** : Formation de l\'administrateur
    d\'Ô Canada (1 session de 2-3h). Prise en main guidée des
    fonctionnalités principales.

-   **T10.6 --- Période de stabilisation** : Support post-déploiement
    pendant 2 semaines pour corrections des anomalies découvertes en
    production.

**4. Gestion des Risques**

**4.1 Registre des risques**

  -------------------------------------------------------------------------------------
  **Risque**             **Probabilité**   **Impact**   **Mitigation**
  ---------------------- ----------------- ------------ -------------------------------
  Connectivité Internet  Moyenne           Moyen        Les modules IA sont
  insuffisante pour                                     non-bloquants : en cas
  l\'API Anthropic                                      d\'indisponibilité,
  (Douala)                                              l\'assistant et le chatbot
                                                        affichent un message de
                                                        fallback. L\'app reste 100%
                                                        fonctionnelle sans IA.

  Changements de         Moyenne           Élevé        Gel des spécifications après
  spécifications en                                     validation de la Phase 0. Tout
  cours de projet                                       changement doit passer par une
                                                        nouvelle version de SFD et un
                                                        avenant au planning.

  Disponibilité du       Faible            Élevé        Préparer l\'environnement de
  serveur de production                                 production dès la Phase 0 (même
                                                        hébergement). Test de
                                                        déploiement à la Phase 8.

  Complexité du kiosque  Moyenne           Moyen        Phase 2 alloue 1 semaine
  (gestion PIN, IP,                                     entière au kiosque. Tests avec
  fenêtres)                                             différentes configurations IP.

  Calculs OHADA          Faible            Élevé        Tests unitaires dédiés au
  incorrects                                            calcul des droits à congé.
                                                        Validation par le client des
                                                        résultats calculés en Phase 3.

  Performance requêtes   Faible            Moyen        Index stratégiques définis en
  sur MySQL (rapports                                   Phase 0. Vérification EXPLAIN
  lents)                                                des requêtes complexes des
                                                        rapports.

  Abandon du projet API  Très faible       Faible       L\'architecture AnthropicClient
  Anthropic ou                                          est isolée. Migration vers un
  changement de                                         autre modèle ne nécessite que
  tarification                                          la modification de cette
                                                        classe.
  -------------------------------------------------------------------------------------

**5. Jalons et Livrables**

  --------------------------------------------------------------------------------------
  **Jalon**   **Semaine**   **Description**                 **Validation**
  ----------- ------------- ------------------------------- ----------------------------
  J0          S1            Infrastructure opérationnelle,  Revue technique
                            DB créée, layouts fonctionnels  

  J1          S3            Admin peut se connecter et      Demo client
                            gérer les employés              

  J2          S5            Kiosque de pointage             Demo client
                            opérationnel, présences gérées  

  J3          S7            Circuit congés complet          Demo client
                            fonctionnel + planning          

  J4          S9            Visiteurs + Vue temps réel      Demo client
                            opérationnels                   

  J5          S11           Notifications + Documents +     Demo client
                            Rapports PDF                    

  J6          S13           Tableaux de bord complets +     Demo client
                            Calendrier camerounais          

  J7          S14           Modules IA intégrés et          Demo client
                            fonctionnels                    

  J8          S15           Application complète, audit et  Revue technique
                            finances                        

  J9          S17           Recette client validée,         Signature recette
                            application prête pour          
                            production                      

  J10         S17           Mise en production, formation   Go live
                            effectuée                       
  --------------------------------------------------------------------------------------

> *Chaque jalon correspondant à une demo client doit être précédé d\'un
> test interne de 24h minimum. Les demos se font sur l\'environnement de
> développement avec des données de test réalistes.*
