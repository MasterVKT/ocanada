**Ô CANADA**

Application de Gestion RH

**ARCHITECTURE TECHNIQUE**

  -----------------------------------------------------------------------
  **Version**             **Date**                **Client**
  ----------------------- ----------------------- -----------------------
  1.0                     Mars 2026               Ô Canada, Douala ---
                                                  Cameroun

  -----------------------------------------------------------------------

**1. Vue d\'Ensemble de l\'Architecture**

**1.1 Paradigme architectural**

L\'application Ô Canada est une application web multi-couches basée sur
le pattern MVC (Modèle-Vue-Contrôleur), implémenté via le framework
CodeIgniter 4 (CI4). Il s\'agit d\'une application web traditionnelle
avec rendu côté serveur (Server-Side Rendering), enrichie de
comportements dynamiques côté client via JavaScript (Vanilla JS + AJAX).

> *Architecture choisie : MVC Server-Side avec CodeIgniter 4 + PHP 8.x +
> MySQL 8.x + Bootstrap 5 + JavaScript Vanilla. Pas de framework SPA
> (Vue.js, React, Angular). Navigation par rechargement de page complet,
> sauf pour les interactions AJAX définies.*

**1.2 Diagramme logique des couches**

L\'architecture se compose de 5 couches distinctes (de haut en bas) :

  -----------------------------------------------------------------------
  **Couche**      **Technologies**        **Responsabilités**
  --------------- ----------------------- -------------------------------
  Présentation    HTML5, Bootstrap 5,     Rendu HTML, styles CSS,
  (Client)        JavaScript Vanilla,     interactions JS côté
                  Bootstrap Icons,        navigateur, graphiques, QR
                  Chart.js, qrcode.js     codes

  Application Web CodeIgniter 4 (PHP 8.x) Contrôleurs MVC, routage,
                                          gestion des sessions, filtres
                                          d\'authentification, validation

  Logique Métier  Modèles CI4, Libraries, Règles de gestion, calculs
                  Helpers                 (présences, congés, coûts),
                                          orchestration

  Intégration     API Anthropic Claude    Assistant IA rédaction de
  Externe         (HTTPS/REST)            congé, Chatbot RH interne

  Persistance     MySQL 8.x               Stockage données, contraintes
                                          d\'intégrité, indexation
  -----------------------------------------------------------------------

**1.3 Stack technique complète**

  -------------------------------------------------------------------------------------------
  **Composant**       **Technologie**   **Version**                **Rôle**
  ------------------- ----------------- -------------------------- --------------------------
  Framework Backend   CodeIgniter       4.5.x (LTS)                MVC, routing, sessions,
                                                                   ORM léger

  Langage Backend     PHP               8.2+                       Logique serveur,
                                                                   traitement, sécurité

  Base de données     MySQL             8.0+                       Stockage relationnel de
                                                                   toutes les données

  Frontend CSS        Bootstrap         5.3.x                      Grille responsive,
                                                                   composants UI

  Frontend JS         JavaScript        ES6+                       AJAX, horloge temps réel,
                      Vanilla                                      QR code, chatbot

  Graphiques          Chart.js          4.x (CDN)                  Graphiques tableaux de
                                                                   bord (courbes, secteurs,
                                                                   barres)

  QR Code             qrcode.js         1.5.x (local)              Génération QR badge
                                                                   visiteur côté client

  Icônes              Bootstrap Icons   1.11.x (CDN)               Iconographie cohérente
                                                                   dans toute l\'app

  PDF serveur         DOMPDF            2.x (Composer)             Génération rapports PDF
                                                                   côté serveur

  API IA              Anthropic Claude  claude-sonnet-4-20250514   Assistant congé et chatbot
                                                                   RH

  Polices             Google Fonts      Inter, Roboto Mono         Typographie principale
                      (CDN)                                        

  Serveur web         Apache / Nginx    2.4+ / 1.20+               Serveur HTTP (production)
  -------------------------------------------------------------------------------------------

**2. Architecture CodeIgniter 4**

**2.1 Structure des répertoires**

L\'application suit la structure standard CI4, organisée comme suit :

  -----------------------------------------------------------------------
  **Répertoire / Fichier**        **Description**
  ------------------------------- ---------------------------------------
  app/                            Répertoire principal de l\'application
                                  (code métier)

  app/Config/                     Fichiers de configuration CI4 (App.php,
                                  Database.php, Routes.php, Filters.php,
                                  etc.)

  app/Controllers/                Contrôleurs MVC --- un par module
                                  fonctionnel

  app/Models/                     Modèles de données CI4 --- un par table
                                  principale (avec logique métier)

  app/Views/                      Vues PHP/HTML --- organisées par module
                                  en sous-répertoires

  app/Libraries/                  Classes métier personnalisées (ex:
                                  PlanningCalc, AbsenceCron, CongeHelper)

  app/Filters/                    Filtres CI4 : AuthFilter, RoleFilter,
                                  KiosqueIPFilter

  app/Helpers/                    Fonctions utilitaires globales (dates,
                                  formatage XAF, jours ouvrables)

  app/Language/fr/                Fichiers de traduction FR pour les
                                  messages d\'erreur et l\'interface

  public/                         Racine web publique --- seul répertoire
                                  accessible directement par HTTP

  public/index.php                Point d\'entrée unique de
                                  l\'application (front controller CI4)

  public/assets/                  Fichiers statiques publics (CSS, JS,
                                  images non protégées)

  public/assets/css/              Bootstrap 5 compilé + ocanada.css
                                  (surcharges custom)

  public/assets/js/               JS Vanilla de l\'app + qrcode.js
                                  (local) + chart.min.js (optionnel
                                  local)

  public/assets/img/              Images statiques (logo, icônes SVG de
                                  l\'app)

  storage/                        Répertoire HORS racine web --- fichiers
                                  uploadés (non accessibles directement)

  storage/uploads/employees/      Photos de profil des employés

  storage/uploads/documents/      Documents RH uploadés (contrats, fiches
                                  de paie, etc.)

  storage/uploads/visitors/       Photos visiteurs (si capturées)

  storage/logs/                   Logs applicatifs CI4

  vendor/                         Dépendances Composer (CI4, DOMPDF,
                                  etc.)

  composer.json                   Déclaration des dépendances PHP

  .htaccess (ou nginx.conf)       Réécriture URL (suppression /index.php/
                                  du chemin)
  -----------------------------------------------------------------------

> *SÉCURITÉ CRITIQUE : Le répertoire storage/ doit être placé HORS de la
> racine web public/ ou protégé par .htaccess/nginx afin qu\'aucun
> fichier uploadé ne soit accessible directement via URL.*

**2.2 Configuration CI4 --- fichiers clés**

**app/Config/App.php**

Ce fichier configure : baseURL (URL de base de l\'application),
defaultLocale = \'fr\', negotiateLocale = false, supportedLocales =
\[\'fr\'\], appTimezone = \'Africa/Douala\', charset = \'UTF-8\',
sessionDriver = \'CodeIgniter\\Session\\Handlers\\FileHandler\',
sessionSavePath = WRITEPATH . \'session\', sessionExpiration = 7200 (2h
d\'inactivité), sessionCookieName = \'ocanada_session\',
sessionRegenerateDestroy = true (sécurité).

**app/Config/Database.php**

Configuration de la connexion MySQL : hostname, username, password,
database = \'ocanada_db\', DBDriver = \'MySQLi\', DBPrefix vide, charset
= \'utf8mb4\', DBCollat = \'utf8mb4_unicode_ci\', strictOn = true (MySQL
strict mode). Les credentials sont lus depuis des variables
d\'environnement via getenv() ou depuis un fichier .env CI4 (non commité
dans git).

**app/Config/Routes.php**

Voir section 2.4 --- Système de routage.

**app/Config/Filters.php**

Déclaration des filtres globaux et par route. Configuration : \$globals
contient les filtres appliqués à toutes les routes (csrf, honeypot).
\$filters\[\'auth\'\] référence AuthFilter. \$filters\[\'role:admin\'\]
référence RoleFilter avec paramètre. \$filters\[\'kiosque\'\] référence
KiosqueIPFilter.

**2.3 Contrôleurs --- Architecture détaillée**

> *Tous les contrôleurs étendent BaseController (CI4). Un contrôleur
> AuthController gère l\'authentification. Les contrôleurs protégés
> vérifient l\'authentification et le rôle via les filtres CI4 déclarés
> dans Routes.php.*

**Contrôleur BaseController (app/Controllers/BaseController.php)**

Étend CI4\\Controller. Attributs protégés communs : \$session (service
session CI4), \$db (connexion DB), \$currentUser (tableau données
utilisateur connecté depuis session). Méthode protégée getUserData() :
récupère les données complètes de l\'utilisateur connecté (join
utilisateurs + employes). Méthode protégée renderView(view, data) :
charge le layout principal avec header/sidebar/footer en passant les
données. Méthode protégée jsonResponse(data, code=200) : retourne une
réponse JSON avec les headers appropriés (pour les endpoints AJAX).

**Contrôleur AuthController (app/Controllers/AuthController.php)**

Routes sans filtre d\'auth. Méthodes : index() --- affiche le formulaire
de connexion (GET /login). login() --- traite le formulaire de connexion
(POST /login), vérifie email/mot de passe/statut, crée la session, gère
le compteur d\'échecs et le blocage 15min, journalise dans audit_log,
redirige selon le rôle. logout() --- détruit la session, journalise,
redirige vers /login. forgotPassword() --- affiche formulaire mot de
passe oublié (GET) et traite la demande (POST) : génère token, stocke en
DB. resetPassword(\$token) --- vérifie token valide et non expiré
(GET/POST), met à jour le mot de passe.

**Contrôleur AdminDashboard
(app/Controllers/Admin/DashboardController.php)**

Filtre : auth + role:admin. Méthode index() : charge toutes les KPIs du
tableau de bord (présents du jour, visiteurs, congés en attente,
alertes) via appels aux modèles correspondants, calcule les données des
3 graphiques, passe le tout à la vue admin/dashboard.

**Contrôleur EmployeesController
(app/Controllers/Admin/EmployeesController.php)**

Filtre : auth + role:admin. Méthodes CRUD : index() --- liste paginée
avec filtres. show(\$id) --- fiche détaillée. create() GET ---
formulaire 3 étapes (wizard). store() POST --- valide et persiste, crée
compte utilisateur, initialise solde congés, journalise. edit(\$id) GET
--- formulaire de modification. update(\$id) POST --- valide, met à
jour, journalise les champs sensibles. deactivate(\$id) POST ---
désactive le compte.

**Contrôleur PresencesController
(app/Controllers/Admin/PresencesController.php)**

Filtre : auth + role:admin. Méthodes : index() --- vue quotidienne (date
= today par défaut, sélectable). history() --- historique avec filtres
(employé, période, statut). correct(\$id) POST --- correction manuelle
d\'un pointage (avec commentaire obligatoire), journalise. stats() ---
tableau récapitulatif mensuel. getByDate() AJAX --- retourne les
présences d\'une date en JSON pour le sélecteur de date.

**Contrôleur LeaveController
(app/Controllers/Admin/LeaveController.php)**

Filtre : auth + role:admin. Méthodes : index() --- liste toutes les
demandes avec filtres. show(\$id) --- détail d\'une demande + solde
employé + chevauchements. approve(\$id) POST --- approuve, met à jour
solde, notifie employé, journalise. reject(\$id) POST --- refuse avec
commentaire obligatoire, notifie employé, journalise. balances() ---
tableau des soldes de tous les employés. adjustBalance(\$empId) POST ---
modification manuelle de solde avec commentaire.

**Contrôleur EmployeeLeaveController
(app/Controllers/Employee/LeaveController.php)**

Filtre : auth + role:employe. Méthodes : index() --- liste des demandes
personnelles. create() GET --- formulaire de demande (avec appel AJAX
pour calcul jours ouvrables). store() POST --- valide et soumet la
demande, notifie l\'admin. calcDays() AJAX POST --- reçoit date_debut et
date_fin, retourne jours_ouvrables en JSON (utilisé par le formulaire en
temps réel).

**Contrôleur KiosqueController (app/Controllers/KiosqueController.php)**

Filtre : kiosque (vérifie IP). Méthodes : index() --- affiche la page
kiosque (pas de menu, plein écran). pointArrivee() POST --- traite
pointage d\'arrivée : identifie employé (matricule ou email), vérifie
PIN (bcrypt), vérifie fenêtre horaire, vérifie doublon, insère dans
presences, calcule statut, journalise. Retourne JSON avec statut et
données de confirmation (nom, photo, heure, statut). pointDepart() POST
--- traite pointage de départ : vérifie arrivée existante, enregistre
départ, calcule durée, journalise. Retourne JSON.

**Contrôleur VisitorController (app/Controllers/VisitorController.php)**

Filtre : auth + role:admin\|agent. Méthodes : index() --- liste des
visiteurs présents (vue temps réel). register() GET --- formulaire
d\'enregistrement. store() POST --- valide, génère numéro badge
(V\[YYYYMMDD\]-\[NNN\]), enregistre heure arrivée, journalise.
checkout(\$id) POST --- enregistre sortie, calcule durée, journalise.
history() --- historique filtrable. badge(\$id) --- affiche le badge
visiteur (avec QR code).

**Contrôleur RealtimeController
(app/Controllers/RealtimeController.php)**

Filtre : auth + role:admin\|agent. Méthode index() : requête SQL
combinant presences (du jour, sans heure_depart) et visiteurs
(statut=present). Calcule les durées de présence en PHP (datetime diff).
Passe données à la vue temps réel. Méthode refresh() AJAX GET : retourne
les mêmes données en JSON pour l\'actualisation automatique toutes les 2
minutes.

**Contrôleur PlanningController
(app/Controllers/Admin/PlanningController.php)**

Filtre : auth + role:admin. Méthodes : index() --- vue calendrier
hebdomadaire. createShift() --- formulaire + traitement création modèle
de shift. assignShift() POST --- affecte un shift à un/plusieurs
employés. weekData(\$date) AJAX GET --- retourne les données de la
semaine en JSON pour le calendrier.

**Contrôleur NotificationsController
(app/Controllers/NotificationsController.php)**

Filtre : auth (tous rôles). Méthodes : index() --- liste paginée de
toutes les notifications de l\'utilisateur connecté. markRead(\$id) POST
AJAX --- marque une notification lue. markAllRead() POST AJAX --- marque
tout lu. getUnread() AJAX GET --- retourne nombre et 10 dernières non
lues en JSON (pour le badge de la cloche).

**Contrôleur DocumentsController
(app/Controllers/Admin/DocumentsController.php)**

Filtre : auth + role:admin. Méthodes : index() --- liste des documents
avec filtre par employé. upload() POST --- valide type MIME réel
(finfo), taille max 5Mo, génère UUID pour le nom de fichier, stocke dans
storage/uploads/documents/, insère référence en DB, journalise.
download(\$id) --- vérifie droits, sert le fichier via readfile() avec
headers Content-Type et Content-Disposition. delete(\$id) POST ---
supprime référence DB (fichier physique conservé), journalise.

**Contrôleur EmployeeDocumentsController
(app/Controllers/Employee/DocumentsController.php)**

Filtre : auth + role:employe. Méthode index() : liste des documents de
l\'employé connecté (lecture seule). Méthode download(\$id) : vérifie
que le document appartient bien à l\'employé connecté avant de servir le
fichier.

**Contrôleur ReportsController
(app/Controllers/Admin/ReportsController.php)**

Filtre : auth + role:admin. Méthodes : presenceMonthly() --- génère
rapport PDF présences mensuel via DOMPDF, journalise. leaveAnnual() ---
génère rapport PDF congés annuels. visitorsLog() --- génère rapport
PDF/CSV journal visiteurs. absenteeismReport() --- génère rapport PDF
absentéisme. exportCsv(\$type) --- génère et télécharge un CSV pour les
rapports compatibles.

**Contrôleur FinanceController
(app/Controllers/Admin/FinanceController.php)**

Filtre : auth + role:admin. Méthode index() : calcule les indicateurs
financiers (coût absences, impact retards, comparaison mensuelle) en
joinant presences et employes.salaire_journalier. Ne retourne jamais les
salaires bruts, seulement les coûts agrégés.

**Contrôleur AuditController
(app/Controllers/Admin/AuditController.php)**

Filtre : auth + role:admin. Méthode index() : liste paginée (25/page) du
journal d\'audit avec filtres. Méthode detail(\$id) AJAX : retourne
données avant/après d\'un événement en JSON pour la modale de détail.
Méthode exportCsv() : export CSV du journal filtré.

**Contrôleur AIController (app/Controllers/AIController.php)**

Filtre : auth (rôles employe et admin pour le chatbot, employe
uniquement pour l\'assistant). Méthodes : assistantConge() POST ---
endpoint AJAX pour l\'assistant de rédaction de congé : vérifie rate
limit (3/heure/user via cache CI4), construit le prompt système, appelle
l\'API Anthropic via HTTP request CI4, retourne le texte généré en JSON.
chatbot() POST --- endpoint AJAX pour le chatbot RH : vérifie rate limit
(20/heure/user), récupère les données contextuelles de l\'utilisateur en
DB, construit le prompt système avec ces données, appelle l\'API
Anthropic avec l\'historique de conversation, retourne la réponse en
JSON.

**Contrôleur ConfigController
(app/Controllers/Admin/ConfigController.php)**

Filtre : auth + role:admin. Méthodes : holidays() --- liste et gestion
CRUD des jours fériés. updateConfig() POST --- mise à jour des
paramètres système (config_systeme), journalise chaque modification.

**Contrôleur ProfileController (app/Controllers/ProfileController.php)**

Filtre : auth (tous rôles). Méthodes : show() --- affiche le profil de
l\'utilisateur connecté. updatePassword() POST --- change le mot de
passe (vérification ancien mdp requis). updatePin() POST --- change le
PIN kiosque (vérification mdp de connexion requise).

**Contrôleur EmployeeDashboardController
(app/Controllers/Employee/DashboardController.php)**

Filtre : auth + role:employe. Méthode index() : charge le statut de
pointage du jour, les statistiques du mois, le solde de congés, les
données du mini-calendrier mensuel (30 derniers jours avec statuts), les
accès rapides.

**Contrôleur AgentDashboardController
(app/Controllers/Agent/DashboardController.php)**

Filtre : auth + role:agent. Redirige directement vers la vue temps réel.
Le menu de l\'agent est restreint aux modules visiteurs et
notifications.

**2.4 Modèles --- Architecture détaillée**

> *Tous les modèles étendent CI4\\Model. Chaque modèle déclare \$table,
> \$primaryKey, \$allowedFields, \$useTimestamps, \$validationRules. Les
> requêtes complexes (jointures, agrégations) sont écrites en SQL
> préparé via \$db-\>query() ou le Query Builder CI4.*

**Modèle UtilisateurModel**

Table : utilisateurs. Méthodes personnalisées : findByEmail(\$email) ---
recherche par email (pour login). incrementLoginAttempts(\$id) /
resetLoginAttempts(\$id) --- gestion du compteur d\'échecs.
lockAccount(\$id, \$minutes) / isLocked(\$id) --- gestion du blocage
temporaire (via cache CI4 ou colonne DB). generateResetToken(\$email)
--- génère et stocke le token de réinitialisation.
validateResetToken(\$token) --- vérifie token valide et non expiré.

**Modèle EmployeModel**

Table : employes (avec jointure utilisateurs pour les requêtes de
liste). Méthodes : getAll(\$filters) --- liste filtrée et paginée
(statut, département, recherche texte). getActiveList() --- liste des
employés actifs (utilisée pour les select dropdowns). getWithUser(\$id)
--- données complètes employé + compte utilisateur. generateMatricule()
--- génère le prochain matricule EMP-XXXX disponible.
calcAnciennete(\$employeId) --- calcule l\'ancienneté en années et
retourne la tranche de congé. getByKiosqueId(\$matriculeOrEmail) ---
recherche pour le kiosque (matricule ou email).

**Modèle PresenceModel**

Table : presences. Méthodes : getByDate(\$date) --- toutes les présences
d\'une date avec jointure employes. getByEmploye(\$employeId,
\$dateDebut, \$dateFin) --- historique d\'un employé sur une période.
getStatsMois(\$employeId, \$annee, \$mois) --- statistiques mensuelles :
jours présents, retards, absences, heures total. getMonthlyRecap() ---
récapitulatif de tous les employés pour un mois (pour rapport).
existsArrivee(\$employeId, \$date) --- vérifie si une arrivée existe
déjà pour ce jour. calcDureeTravaillee(\$arrivee, \$depart) --- retourne
la durée en minutes. markAbsences(\$date) --- appelé par le CRON :
insère les absences automatiques pour une date.

**Modèle VisiteurModel**

Table : visiteurs. Méthodes : getPresents() --- liste des visiteurs
actuellement dans les locaux (statut=present). getByDate(\$date) ---
tous les visiteurs d\'une date. generateBadgeNumber(\$date) --- génère
le prochain numéro de badge V\[YYYYMMDD\]-\[NNN\].
closeOpenVisits(\$date) --- CRON : ferme les visites non terminées à
23h59. getHistory(\$filters) --- historique filtrable.

**Modèle CongeModel**

Table : demandes_conge. Méthodes : getPending() --- demandes en attente
(pour l\'admin). getByEmploye(\$id, \$statut=null) --- historique d\'un
employé. getPendingOver48h() --- demandes en attente depuis plus de 48h
(pour alerte NOTIF_CONGE_ATTENTE_48H). hasOverlap(\$employeId,
\$dateDebut, \$dateFin, \$excludeId=null) --- vérifie chevauchement.
getEmployesEnConge(\$dateDebut, \$dateFin) --- liste des employés en
congé sur une période.

**Modèle SoldeCongeModel**

Table : soldes_conges. Méthodes : getByEmploye(\$id, \$annee=null) ---
solde d\'un employé. initForEmployee(\$employeId) --- initialise le
solde selon ancienneté et Code du Travail camerounais.
deductDays(\$employeId, \$jours, \$annee) --- déduit des jours lors
d\'une approbation. restoreDays(\$employeId, \$jours, \$annee) ---
restitue des jours lors d\'un refus/annulation.
calcJoursTotal(\$ancienneteAnnees, \$moisTravailles) --- calcule le
total de jours selon les règles OHADA. getAllBalances(\$annee) ---
tableau récapitulatif de tous les employés.

**Modèle ShiftModel et AffectationShiftModel**

Tables : shifts_modeles et affectations_shifts. ShiftModel : getActifs()
--- shifts actifs disponibles. AffectationShiftModel :
getShiftForEmploye(\$employeId, \$date) --- retourne le shift applicable
pour un employé à une date donnée (en tenant compte de l\'historique des
affectations).

**Modèle NotificationModel**

Table : notifications. Méthodes : create(\$destinataireId, \$type,
\$message, \$lien=null) --- crée une notification. getUnread(\$userId,
\$limit=10) --- 10 dernières non lues. countUnread(\$userId) --- compte
pour le badge. markAsRead(\$id, \$userId) --- marque une notification
lue. markAllRead(\$userId) --- tout marquer lu. getAll(\$userId,
\$filters) --- toutes les notifications paginées.

**Modèle DocumentRHModel**

Table : documents_rh. Méthodes : getByEmploye(\$id) --- documents d\'un
employé. upload(\$data, \$file) --- valide et stocke le fichier, insère
en DB, retourne l\'id. getFilePath(\$id, \$checkOwnership=\$userId) ---
retourne le chemin du fichier après vérification des droits.

**Modèle JourFerieModel**

Table : jours_feries. Méthodes : getForYear(\$annee) --- tous les jours
fériés d\'une année. isFerie(\$date) --- vérifie si une date est fériée.
calcJoursOuvrables(\$dateDebut, \$dateFin) --- calcule le nombre de
jours ouvrables entre deux dates (exclut week-ends et jours fériés de
l\'année).

**Modèle ConfigSystemeModel**

Table : config_systeme (clé/valeur). Méthodes : get(\$cle) --- retourne
une valeur de configuration. set(\$cle, \$valeur) --- met à jour ou
insère. getAll() --- toutes les configurations. Clés importantes :
ip_kiosque_autorisees (CSV), seuil_alerte_visiteur_heures,
heure_debut_pointage_arrivee, heure_fin_pointage_arrivee,
heure_debut_pointage_depart, heure_fin_pointage_depart, samedi_ouvrable,
shift_defaut_id, anthropic_api_key.

**Modèle AuditLogModel**

Table : audit_log. Méthodes : log(\$type, \$description, \$userId,
\$before=null, \$after=null) --- insère un événement d\'audit.
getAll(\$filters) --- liste paginée avec filtres. getById(\$id) ---
détail d\'un événement. Note : aucune méthode de suppression ou de
modification n\'est exposée dans ce modèle (immutabilité garantie au
niveau applicatif).

**2.5 Filtres CI4 (Middleware)**

  ----------------------------------------------------------------------------------------
  **Filtre**          **Fichier**                       **Logique**
  ------------------- --------------------------------- ----------------------------------
  AuthFilter          app/Filters/AuthFilter.php        Vérifie qu\'une session active
                                                        existe (user_id présent dans
                                                        session). Si non : redirige vers
                                                        /login. Vérifie aussi que le
                                                        compte est toujours actif en DB
                                                        (pour les désactivations en cours
                                                        de session).

  RoleFilter          app/Filters/RoleFilter.php        Reçoit le rôle requis en paramètre
                                                        (ex: role:admin). Compare avec le
                                                        rôle stocké en session. Si non
                                                        autorisé : redirige vers une page
                                                        403 avec message \"Accès non
                                                        autorisé\".

  KiosqueIPFilter     app/Filters/KiosqueIPFilter.php   Récupère l\'IP cliente de la
                                                        requête
                                                        (\$\_SERVER\[\"REMOTE_ADDR\"\]).
                                                        Compare avec la liste
                                                        ip_kiosque_autorisees de
                                                        config_systeme. Si non autorisée :
                                                        affiche uniquement le message
                                                        \"Terminal non habilité\" sans
                                                        aucun formulaire. Journalise les
                                                        tentatives non autorisées dans
                                                        audit_log.

  CSRFFilter          CI4 natif                         Protection CSRF automatique sur
                                                        tous les formulaires POST. Token
                                                        généré par CI4, vérifié à chaque
                                                        POST.
  ----------------------------------------------------------------------------------------

**2.6 Libraries métier**

**Library PresenceCalculator (app/Libraries/PresenceCalculator.php)**

Classe de calcul des présences. Méthodes statiques :
calculateStatus(\$heureArrivee, \$heureDebutShift) --- retourne
\'present\' ou \'retard\' et les minutes de retard.
calculateDuration(\$arrivee, \$depart) --- retourne durée en minutes.
calculateOvertime(\$duree, \$shift) --- retourne les heures
supplémentaires. calculateAbsenteeismCost(\$joursAbsents,
\$salaireJournalier) --- retourne le coût estimé.

**Library WorkingDaysCalculator
(app/Libraries/WorkingDaysCalculator.php)**

Méthode principale calculate(\$dateDebut, \$dateFin,
\$samediOuvrable=false) : itère sur chaque jour de la période, exclut
les samedis (si non ouvrable), les dimanches, et les jours fériés (via
JourFerieModel::isFerie()). Retourne le nombre de jours ouvrables.
Utilisée par : CongeController (calcul temps réel), scripts cron,
rapports.

**Library NotificationService (app/Libraries/NotificationService.php)**

Centralise la création des notifications. Méthodes publiques :
notifyCongeSubmitted(\$demande, \$adminIds),
notifyCongeDecision(\$demande), notifyAbsence(\$absence, \$adminIds),
notifyVisiteurLong(\$visiteur, \$adminIds, \$agentIds),
notifyContratExpiration(\$employe, \$adminIds),
notifyDepartManquant(\$employe, \$adminIds). Chaque méthode construit le
message à partir des données et appelle NotificationModel::create().

**Library AnthropicClient (app/Libraries/AnthropicClient.php)**

Wrapper pour l\'API Anthropic. Méthode call(\$systemPrompt, \$messages,
\$maxTokens=500) : récupère la clé API depuis ConfigSystemeModel,
effectue un appel HTTP POST vers https://api.anthropic.com/v1/messages
via la classe HTTP CI4 (ou cURL), avec les headers requis (x-api-key,
anthropic-version, content-type). Gère les erreurs HTTP (timeout 30s,
retry non implémenté --- message d\'erreur renvoyé au frontend).
Retourne le texte de la première réponse content\[0\].text.

**Library RateLimiter (app/Libraries/RateLimiter.php)**

Gestion des rate limits pour les appels API IA. Utilise le cache CI4
(FileCache ou autre driver). Méthode check(\$userId, \$action, \$limit,
\$windowSeconds) : vérifie si l\'utilisateur a dépassé sa limite
d\'appels sur la fenêtre de temps. Méthode increment(\$userId, \$action)
: incrémente le compteur. Retourne true si autorisé, false si limite
atteinte.

**2.7 Système de routage (app/Config/Routes.php)**

Organisation des routes par groupe selon le rôle. Utilisation des
filtres CI4 via le paramètre \'filter\'. Structure complète :

**[Routes publiques (sans filtre d\'auth)]{.underline}**

-   **/login** --- GET : affichage formulaire --- POST : traitement
    connexion

-   **/logout** --- GET : déconnexion

-   **/forgot-password** --- GET/POST : mot de passe oublié

-   **/reset-password/(:alphanum)** --- GET/POST : réinitialisation mot
    de passe

-   **/kiosque** --- GET : page kiosque \[filtre: kiosque\]

-   **/kiosque/pointer-arrivee** --- POST : traitement pointage
    \[filtre: kiosque\]

-   **/kiosque/pointer-depart** --- POST : traitement pointage \[filtre:
    kiosque\]

**[Routes Administrateur (filtre: auth, role:admin)]{.underline}**

-   **/admin/dashboard** --- GET : tableau de bord

-   **/admin/employes** --- GET : liste --- POST : création

-   **/admin/employes/create** --- GET : formulaire création

-   **/admin/employes/(:num)** --- GET : fiche --- PUT : modification
    --- DELETE/POST désactivation

-   **/admin/presences** --- GET : vue quotidienne

-   **/admin/presences/historique** --- GET : historique filtrable

-   **/admin/presences/corriger/(:num)** --- POST : correction manuelle

-   **/admin/conges** --- GET : liste --- POST : traitement

-   **/admin/conges/(:num)/approuver** --- POST : approbation

-   **/admin/conges/(:num)/refuser** --- POST : refus

-   **/admin/conges/soldes** --- GET : soldes --- POST : ajustement

-   **/admin/visiteurs** --- GET : présents et historique

-   **/admin/planning** --- GET/POST : planning et shifts

-   **/admin/rapports/(:alpha)** --- GET : génération rapport

-   **/admin/finances** --- GET : tableau de bord financier

-   **/admin/documents** --- GET/POST : gestion documents

-   **/admin/audit** --- GET : journal d\'audit

-   **/admin/config** --- GET/POST : configuration système et jours
    fériés

**[Routes Employé (filtre: auth, role:employe)]{.underline}**

-   **/employe/dashboard** --- GET : tableau de bord

-   **/employe/conges** --- GET : mes demandes --- POST : nouvelle
    demande

-   **/employe/conges/calc-jours** --- POST AJAX : calcul jours
    ouvrables

-   **/employe/presences** --- GET : mes présences

-   **/employe/planning** --- GET : mon planning

-   **/employe/documents** --- GET : mes documents

**[Routes Agent d\'accueil (filtre: auth, role:agent)]{.underline}**

-   **/accueil/dashboard** --- GET : redirige vers vue temps réel

-   **/accueil/visiteurs/enregistrer** --- GET/POST : enregistrement
    visiteur

-   **/accueil/visiteurs/sortie/(:num)** --- POST : enregistrement
    sortie

-   **/accueil/visiteurs/badge/(:num)** --- GET : affichage badge

-   **/accueil/visiteurs/historique** --- GET : historique

**[Routes communes (filtre: auth, tous rôles)]{.underline}**

-   **/temps-reel** --- GET : vue unifiée temps réel

-   **/temps-reel/refresh** --- GET AJAX : actualisation données JSON

-   **/notifications** --- GET : liste complète

-   **/notifications/(:num)/lire** --- POST AJAX : marquer lue

-   **/notifications/tout-lire** --- POST AJAX : tout marquer lu

-   **/notifications/non-lues** --- GET AJAX : données badge cloche

-   **/profil** --- GET/POST : profil utilisateur

-   **/ia/assistant-conge** --- POST AJAX : assistant rédaction

-   **/ia/chatbot** --- POST AJAX : chatbot RH

**2.8 Organisation des Vues**

Les vues CI4 sont organisées en sous-répertoires par rôle et module dans
app/Views/ :

  --------------------------------------------------------------------------------------
  **Répertoire**                             **Contenu**
  ------------------------------------------ -------------------------------------------
  app/Views/layouts/                         Gabarits de mise en page principaux

  app/Views/layouts/main.php                 Layout admin/employé : sidebar + topbar +
                                             footer + slot contenu

  app/Views/layouts/kiosque.php              Layout page kiosque (plein écran, fond bleu
                                             nuit)

  app/Views/layouts/auth.php                 Layout pages login/mot de passe (centré,
                                             fond rouge)

  app/Views/auth/                            login.php, forgot_password.php,
                                             reset_password.php

  app/Views/kiosque/                         index.php (page kiosque complète)

  app/Views/admin/                           Sous-répertoires par module : dashboard/,
                                             employes/, presences/, conges/, visiteurs/,
                                             planning/, rapports/, finance/, documents/,
                                             audit/, config/

  app/Views/admin/dashboard/                 index.php (KPIs + graphiques)

  app/Views/admin/employes/                  index.php (liste), show.php (fiche),
                                             create.php (wizard 3 étapes), edit.php

  app/Views/admin/presences/                 today.php, history.php, stats.php

  app/Views/admin/conges/                    index.php, show.php, balances.php

  app/Views/admin/visiteurs/                 index.php (temps réel), register.php,
                                             history.php, badge.php

  app/Views/admin/planning/                  calendar.php, create_shift.php

  app/Views/admin/finance/                   index.php

  app/Views/admin/audit/                     index.php

  app/Views/admin/config/                    index.php, holidays.php

  app/Views/employe/                         dashboard.php, presences.php, planning.php

  app/Views/employe/conges/                  index.php, create.php

  app/Views/employe/documents/               index.php

  app/Views/agent/                           dashboard.php (redirige vers realtime)

  app/Views/shared/                          Vues partagées entre rôles

  app/Views/shared/realtime.php              Vue unifiée temps réel (partagée admin +
                                             agent)

  app/Views/shared/notifications.php         Page liste des notifications

  app/Views/shared/profil.php                Page profil utilisateur

  app/Views/components/                      Composants réutilisables PHP (partials)

  app/Views/components/sidebar_admin.php     Sidebar admin avec menu complet

  app/Views/components/sidebar_employe.php   Sidebar employé (menu simplifié)

  app/Views/components/sidebar_agent.php     Sidebar agent d\'accueil

  app/Views/components/topbar.php            Barre supérieure (notifications, avatar,
                                             logout)

  app/Views/components/chatbot.php           Panneau chatbot flottant (HTML + JS)

  app/Views/components/kpi_card.php          Composant carte KPI réutilisable

  app/Views/components/pagination.php        Composant pagination Bootstrap

  app/Views/errors/                          Pages d\'erreur 403.php, 404.php, 500.php
  --------------------------------------------------------------------------------------

**3. Architecture Base de Données**

**3.1 Caractéristiques MySQL**

-   **Moteur de tables** : InnoDB (support des contraintes de clés
    étrangères, transactions ACID)

-   **Encodage** : utf8mb4 (support complet Unicode, emojis, caractères
    camerounais)

-   **Collation** : utf8mb4_unicode_ci (tri insensible à la casse et aux
    accents)

-   **Mode SQL** :
    STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
    (MySQL Strict Mode activé)

**3.2 Indexation stratégique**

En plus des clés primaires et étrangères, les index suivants sont créés
pour les performances :

  --------------------------------------------------------------------------
  **Table**        **Colonne(s)           **Type       **Justification**
                   indexée(s)**           d\'index**   
  ---------------- ---------------------- ------------ ---------------------
  utilisateurs     email                  UNIQUE       Recherche rapide à la
                                                       connexion

  employes         matricule              UNIQUE       Identification
                                                       kiosque

  employes         departement            INDEX        Filtrage par
                                                       département

  employes         statut                 INDEX        Filtrage
                                                       actifs/inactifs

  presences        (employe_id,           UNIQUE       Contrainte doublon +
                   date_presence)                      recherche rapide

  presences        date_presence          INDEX        Filtrage par date
                                                       (usage très fréquent)

  presences        statut_arrivee         INDEX        Filtrage par statut

  demandes_conge   statut                 INDEX        Filtrage par statut
                                                       (en_attente)

  demandes_conge   (employe_id,           INDEX        Vérification
                   date_debut, date_fin)               chevauchements

  visiteurs        statut                 INDEX        Filtrage visiteurs
                                                       présents

  visiteurs        heure_arrivee          INDEX        Filtrage par date

  notifications    (destinataire_id, lue) INDEX        Badge de
                                                       notifications non
                                                       lues

  audit_log        type_evenement         INDEX        Filtrage par type

  audit_log        utilisateur_id         INDEX        Filtrage par
                                                       utilisateur

  jours_feries     date_ferie             UNIQUE       Vérification rapide
                                                       d\'un jour férié
  --------------------------------------------------------------------------

**3.3 Transactions et intégrité**

Les opérations suivantes sont enveloppées dans des transactions MySQL
(DB::transStart() / DB::transComplete() en CI4) pour garantir
l\'atomicité :

-   **Approbation d\'un congé** : mise à jour demandes_conge + mise à
    jour soldes_conges + création notification --- tout ou rien.

-   **Création d\'un employé** : insertion employes + insertion
    utilisateurs + création solde_conges initial.

-   **Correction manuelle de présence** : mise à jour presences +
    insertion audit_log --- pour garantir la traçabilité.

-   **Annulation d\'un congé approuvé** : mise à jour demandes_conge +
    restitution solde + notification + audit_log.

**3.4 Script de migration et données initiales**

La base de données est initialisée via des migrations CI4
(app/Database/Migrations/) dans l\'ordre suivant :

-   **Migration 001** : Création table utilisateurs

-   **Migration 002** : Création table employes

-   **Migration 003** : Création tables presences, shifts_modeles,
    affectations_shifts

-   **Migration 004** : Création tables demandes_conge, soldes_conges

-   **Migration 005** : Création table visiteurs

-   **Migration 006** : Création tables notifications, documents_rh

-   **Migration 007** : Création tables jours_feries, config_systeme

-   **Migration 008** : Création table audit_log

Un Seeder CI4 (app/Database/Seeds/InitialDataSeeder.php) insère :

-   Les jours fériés camerounais de l\'année en cours dans jours_feries.

-   Les valeurs par défaut dans config_systeme (IP kiosque, fenêtres de
    pointage, seuils).

-   Le shift par défaut (Journée standard 08h-17h Lun-Ven) dans
    shifts_modeles.

-   Un compte administrateur initial (email et mot de passe définis en
    variables d\'environnement).

**4. Architecture de Sécurité**

**4.1 Authentification et sessions**

-   Sessions PHP gérées par CI4 (FileHandler par défaut, configurable en
    DatabaseHandler).

-   Session regenerate à la connexion (sessionRegenerateDestroy = true
    en CI4 config) pour prévenir la fixation de session.

-   Durée de session : 7200 secondes (2 heures) d\'inactivité. Paramètre
    configurable dans App.php.

-   Cookie de session : HttpOnly = true, Secure = true (en production
    HTTPS), SameSite = Lax.

-   Blocage compte après 5 échecs consécutifs : 15 minutes. Implémenté
    via un compteur en cache CI4 (ou colonne login_attempts +
    locked_until en DB).

**4.2 Mots de passe et PINs**

-   Tous les mots de passe hashés avec bcrypt (PHP password_hash() avec
    PASSWORD_BCRYPT, cost factor 12).

-   PINs kiosque hashés de la même façon (bcrypt) --- stockés dans
    employes.pin_kiosque.

-   Politique mot de passe : minimum 8 caractères, au moins 1 majuscule,
    1 chiffre.

-   Token de réinitialisation : 64 caractères hexadécimaux
    (bin2hex(random_bytes(32))), durée de validité 2 heures.

**4.3 Protection CSRF**

-   Token CSRF CI4 natif sur tous les formulaires POST
    (form_hidden(csrf_token(), csrf_hash()) dans chaque formulaire).

-   Vérification automatique par le filtre CSRF CI4 sur toutes les
    routes POST, PUT, DELETE.

-   Pour les requêtes AJAX POST : le token CSRF est inclus dans les
    données POST ou dans le header X-CSRF-TOKEN (lu via JavaScript
    depuis un meta tag).

**4.4 Injections SQL**

-   100% des requêtes SQL utilisent des requêtes préparées via PDO
    (Query Builder CI4 ou \$db-\>query() avec paramètres liés).

-   Aucune concaténation de variables utilisateur dans les requêtes SQL.

-   Validation stricte des entrées au niveau des Modèles CI4
    (\$validationRules).

**4.5 Gestion des fichiers uploadés**

-   Validation côté serveur : type MIME réel vérifié via finfo (PHP),
    pas uniquement l\'extension.

-   Types autorisés : image/jpeg, image/png pour les photos.
    application/pdf pour les documents. image/jpeg, image/png pour les
    documents image.

-   Taille maximale : 2 Mo pour les photos de profil, 5 Mo pour les
    documents RH.

-   Nom de fichier : UUID v4 généré côté serveur (exemple :
    a3f8c2e1-9d4b-4a7f-b3c2-1e8d5f7a0b9c.pdf). Jamais le nom original.

-   Stockage hors racine web : storage/uploads/ n\'est pas accessible
    directement via HTTP.

-   Accès aux fichiers : uniquement via DocumentsController::download()
    qui vérifie les droits avant de servir le fichier.

**4.6 Clé API Anthropic**

-   Stockée dans config_systeme (colonne valeur de la clé
    anthropic_api_key).

-   Jamais exposée côté client (les appels API sont faits depuis
    AnthropicClient.php côté serveur uniquement).

-   Jamais loguée dans les logs applicatifs ou l\'audit_log.

-   Accessible uniquement aux scripts PHP authentifiés via
    ConfigSystemeModel::get(\'anthropic_api_key\').

**5. Processus Planifiés (CRON)**

**5.1 Scripts CRON requis**

Deux scripts CLI CI4 (app/Commands/) sont exécutés régulièrement via
crontab sur le serveur :

  ----------------------------------------------------------------------------------
  **Script CI4 Command**    **Fréquence    **Heure**   **Actions**
                            CRON**                     
  ------------------------- -------------- ----------- -----------------------------
  ocanada:mark-absences     Quotidien      23h59       Marque les absences
                                                       automatiques pour J : insère
                                                       des enregistrements
                                                       \"absent\" dans presences
                                                       pour chaque employé actif
                                                       sans présence et sans congé
                                                       approuvé pour la date. Génère
                                                       les notifications
                                                       NOTIF_ABSENCE_NON_JUSTIFIEE
                                                       pour l\'admin. Signale les
                                                       absences de départ manquant
                                                       (NOTIF_DEPART_MANQUANT).

  ocanada:close-visits      Quotidien      23h59       Ferme les visites visiteurs
                                                       non terminées : met
                                                       statut=sorti,
                                                       heure_sortie=23:59,
                                                       note=\"Sortie non
                                                       enregistrée\". Génère les
                                                       alertes visiteurs longue
                                                       durée si configuré.

  ocanada:check-contracts   Hebdomadaire   Lundi 08h00 Vérifie les contrats CDD dont
                                                       la date_fin_contrat est dans
                                                       les 30 prochains jours.
                                                       Génère les notifications
                                                       NOTIF_CONTRAT_EXPIRATION pour
                                                       l\'admin.

  ocanada:pending-leaves    Quotidien      09h00       Vérifie les demandes de congé
                                                       en attente depuis plus de
                                                       48h. Génère les notifications
                                                       NOTIF_CONGE_ATTENTE_48H pour
                                                       l\'admin.
  ----------------------------------------------------------------------------------

> *Les CI4 Commands sont invoquées via : php spark ocanada:mark-absences
> depuis le répertoire racine du projet. La configuration crontab
> utilise le chemin absolu vers l\'interpréteur PHP et vers le projet.*

**5.2 Initialisation annuelle des soldes de congés**

Un script supplémentaire ocanada:init-leave-balances est exécuté
manuellement par l\'administrateur en début d\'année (ou en automatique
le 1er janvier à 00h05) :

-   Pour chaque employé actif : crée un enregistrement dans
    soldes_conges pour la nouvelle année avec jours_total calculé selon
    ancienneté et Code du Travail camerounais.

-   Les soldes non consommés de l\'année précédente sont perdus par
    défaut (pas de report automatique, sauf modification manuelle par
    l\'admin).

**6. Intégration API Anthropic**

**6.1 Module Assistant Rédaction de Congé**

Flux de traitement de l\'appel API pour l\'assistant de rédaction :

-   **Étape 1 --- Frontend** : L\'employé saisit son texte informel dans
    le panneau assistant. Un clic sur \"Générer\" déclenche un appel
    AJAX POST vers /ia/assistant-conge avec : { texte_informel,
    type_conge, nb_jours, csrf_token }.

-   **Étape 2 --- Contrôleur AIController::assistantConge()** : Vérifie
    le token CSRF. Vérifie le rate limit (3/heure pour cet utilisateur)
    via RateLimiter. Prépare les données pour AnthropicClient.

-   **Étape 3 --- Construction du prompt système** : Le prompt système
    est construit en PHP avec : contexte entreprise camerounaise,
    instruction de ton (formel, courtois, français), contrainte de
    longueur (2-4 phrases), interdiction d\'inventer des informations
    non fournies, type de congé et durée injectés dans le prompt.

-   **Étape 4 --- Appel AnthropicClient** : POST vers
    https://api.anthropic.com/v1/messages avec :
    model=claude-sonnet-4-20250514, max_tokens=200,
    system=prompt_systeme, messages=\[{role:user,
    content:texte_informel}\]. Headers : x-api-key, anthropic-version:
    2023-06-01, content-type: application/json.

-   **Étape 5 --- Réponse** : Le texte de content\[0\].text est renvoyé
    au frontend en JSON : { success: true, motif: \"\...\"}. En cas
    d\'erreur API : { success: false, message: \"L\'assistant est
    temporairement indisponible\...\" }.

**6.2 Module Chatbot RH**

Flux de traitement du chatbot :

-   **Étape 1 --- Frontend** : L\'utilisateur envoie un message. Le
    panneau chatbot envoie AJAX POST vers /ia/chatbot avec : { message,
    history (tableau \[{role, content}\] des 10 derniers échanges),
    csrf_token }.

-   **Étape 2 --- Récupération du contexte** : Le contrôleur récupère en
    DB selon le rôle : pour un EMPLOYÉ --- solde de congés de l\'année,
    statut des demandes en cours, résumé des présences du mois (jours
    présents, retards, absences). Pour un ADMIN --- statistiques
    globales du jour (présents, absents, en attente congé). Ces données
    sont formatées en texte structuré.

-   **Étape 3 --- Construction du prompt système** : Le prompt système
    inclut : identité de l\'assistant (RH interne Ô Canada), profil de
    l\'utilisateur connecté (nom, rôle, poste), données contextuelles
    récupérées en DB, règles RH d\'Ô Canada (calcul congés, jours
    fériés, politique de présence), instruction : répondre en français,
    de façon concise, refuser poliment les questions hors périmètre RH.

-   **Étape 4 --- Appel API** : Paramètres : max_tokens=500,
    messages=historique_complet + nouveau_message. L\'historique est
    limité aux 10 derniers échanges pour contrôler la taille du
    contexte.

-   **Étape 5 --- Réponse** : { success: true, response: \"\...\"}. En
    cas d\'erreur : message de fallback.

**7. Configuration Serveur et Déploiement**

**7.1 Prérequis serveur**

  -----------------------------------------------------------------------
  **Composant**       **Requis**              **Recommandé**
  ------------------- ----------------------- ---------------------------
  PHP                 8.1 minimum             8.2 LTS

  Extensions PHP      pdo_mysql, mysqli,      Idem + opcache activé
                      mbstring, json, curl,   
                      fileinfo, gd, intl      

  MySQL               8.0+                    8.0 LTS

  Serveur web         Apache 2.4+ avec        Nginx (meilleures
                      mod_rewrite OU Nginx    performances)
                      1.20+                   

  RAM serveur         1 Go minimum            2 Go+

  Composer            2.x                     2.x latest

  Espace disque       500 Mo minimum          5 Go+ (avec stockage
                      (application)           documents)

  Connectivité        HTTPS requis en         Certificat Let\'s Encrypt
                      production              
  -----------------------------------------------------------------------

**7.2 Configuration Apache (.htaccess)**

Le fichier public/.htaccess doit activer la réécriture d\'URL pour
supprimer /index.php/ des chemins, rediriger toutes les requêtes vers
public/index.php (front controller CI4), bloquer l\'accès aux fichiers
.php hors du point d\'entrée, et forcer HTTPS en production.

**7.3 Configuration Nginx**

Le bloc server Nginx doit configurer : root pointant vers public/, index
= index.php, bloc location / avec try_files \$uri \$uri/
/index.php\$is_args\$args, bloc location \~\\.php\$ avec fastcgi_pass
vers PHP-FPM, headers de sécurité (X-Frame-Options,
X-Content-Type-Options, X-XSS-Protection, Referrer-Policy), accès
interdit à storage/ et app/.

**7.4 Variables d\'environnement (.env)**

CI4 lit le fichier .env à la racine du projet (hors public/). Ce fichier
contient (jamais commité dans git) :

-   CI_ENVIRONMENT = production (ou development)

-   app.baseURL = https://ocanada.cm/ (URL de production)

-   database.default.hostname, username, password, database

-   app.encryptionKey (clé de 32 octets pour le chiffrement CI4)

> *La clé API Anthropic est stockée dans la table config_systeme en DB
> (pas dans .env) pour permettre sa modification via l\'interface
> d\'administration sans accès au serveur.*

**7.5 Procédure de déploiement**

-   1 --- Cloner le dépôt git sur le serveur.

-   2 --- Exécuter composer install \--no-dev \--optimize-autoloader.

-   3 --- Copier .env.example vers .env et renseigner les variables.

-   4 --- Exécuter php spark migrate pour créer la structure DB.

-   5 --- Exécuter php spark db:seed InitialDataSeeder pour les données
    initiales.

-   6 --- Configurer les permissions : storage/ et writable/ en 755
    (propriétaire = www-data ou nginx).

-   7 --- Configurer le serveur web (vhost Apache ou bloc server Nginx)
    en pointant vers public/.

-   8 --- Configurer le crontab pour les scripts planifiés.

-   9 --- Activer HTTPS (Let\'s Encrypt via Certbot recommandé).

-   10 --- Tester le fonctionnement de chaque module via la checklist de
    recette.
