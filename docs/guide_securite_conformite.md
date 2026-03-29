**Ô CANADA**

Application de Gestion RH

**GUIDE DE SÉCURITÉ & CONFORMITÉ**

  ---------------------------------------------------------------------------
  **Version**     **Date**       **Classification**   **Audience**
  --------------- -------------- -------------------- -----------------------
  1.0             Mars 2026      CONFIDENTIEL         Équipe développement &
                                                      Administrateur système

  ---------------------------------------------------------------------------

**1. Principes de Sécurité Fondamentaux**

**1.1 Philosophie de sécurité**

L\'application Ô Canada gère des données sensibles : informations
personnelles des employés, données salariales, historiques de présence,
documents RH confidentiels. La sécurité est une exigence fondamentale
intégrée à chaque couche de l\'architecture.

Le modèle de sécurité s\'appuie sur les principes suivants :

-   **Défense en profondeur** : plusieurs couches de sécurité
    indépendantes. La compromission d\'une couche seule ne suffit pas à
    accéder aux données sensibles.

-   **Moindre privilège** : chaque utilisateur et processus n\'a accès
    qu\'aux données et fonctionnalités strictement nécessaires à son
    rôle.

-   **Refus par défaut** : tout accès non explicitement autorisé est
    interdit (filtres CI4 sur toutes les routes protégées).

-   **Traçabilité totale** : toute action sensible est journalisée dans
    audit_log de manière immuable.

-   **Validation côté serveur** : la validation JavaScript est un
    confort UX. La sécurité est garantie par la validation PHP/CI4 côté
    serveur.

-   **Séparation des responsabilités** : l\'API Anthropic est appelée
    uniquement côté serveur, les fichiers uploadés ne sont jamais
    accessibles directement via URL, les salaires ne sont jamais
    affichés en clair.

**1.2 Données sensibles et niveaux de protection**

  -----------------------------------------------------------------------------------------
  **Donnée**                      **Sensibilité**   **Contrôles appliqués**
  ------------------------------- ----------------- ---------------------------------------
  Mots de passe utilisateurs      Critique          Hash bcrypt (cost 12) uniquement.
                                                    Jamais stocké en clair, jamais loggué,
                                                    jamais transmis.

  PINs kiosque                    Critique          Hash bcrypt distinct du mot de passe.
  (employes.pin_kiosque)                            Blocage 10 min après 3 erreurs.

  Salaires journaliers            Très sensible     Accès ADMIN uniquement. Jamais retourné
  (employes.salaire_journalier)                     aux vues employé/agent. Jamais dans les
                                                    exports CSV.

  Numéros CNI / passeport         Sensible          Accès selon rôle. Employé : ses propres
                                                    données. Visiteurs : admin + agent.

  Documents RH (contrats,         Sensible          Stockage hors racine web. Accès via
  bulletins)                                        contrôleur PHP après vérification des
                                                    droits.

  Clé API Anthropic               Critique          Stockée en DB (table config_systeme).
                                                    Jamais envoyée au frontend. Jamais
                                                    loguée. Accès serveur uniquement.

  Données de présence             Modéré            Employé : WHERE employe_id = own_id
  individuelles                                     UNIQUEMENT. Filtre DB obligatoire dans
                                                    chaque requête.

  Photos de profil employés       Modéré            Stockage hors racine web. Servies
                                                    uniquement via contrôleur PHP
                                                    authentifié.

  Token réinitialisation mot de   Sensible          64 chars aléatoires (CSPRNG). Valide 2
  passe                                             heures. Usage unique --- invalidé après
                                                    utilisation.

  Soldes de congés                Modéré            Employé : ses propres données
                                                    uniquement. Admin : tous les employés.
  -----------------------------------------------------------------------------------------

**2. Authentification et Gestion des Sessions**

**2.1 Flux de connexion sécurisé**

Le flux de connexion implémente les protections suivantes de manière
séquentielle dans AuthController::login() :

-   **Étape 1 --- Validation des entrées** : email validé par type (rule
    valid_email CI4). Mot de passe vérifié non vide. Les deux champs
    sanitizés avant toute opération. Aucune donnée utilisateur
    concaténée dans des requêtes SQL.

-   **Étape 2 --- Vérification du compte** : recherche de l\'email dans
    la table utilisateurs via requête préparée. Si non trouvé OU statut
    = inactif : message générique identique \"Email ou mot de passe
    incorrect\" (prévention de l\'énumération de comptes --- ne jamais
    préciser lequel est faux).

-   **Étape 3 --- Vérification du mot de passe** :
    password_verify(\$motDePasseSaisi, \$hashStocke). bcrypt compare
    sans jamais déhasher. Résistant aux attaques temporelles.

-   **Étape 4 --- Vérification du blocage** : si compteur de tentatives
    ≥ 5 dans les 15 dernières minutes (clé cache CI4 :
    login_attempts\_\[email\]) : connexion refusée. Message : \"Compte
    temporairement bloqué. Réessayez dans \[X\] minutes.\"
    Journalisation ECHEC_CONNEXION dans audit_log.

-   **Étape 5 --- Création de session sécurisée** :
    session()-\>regenerate(true) --- détruit l\'ancienne session et en
    crée une nouvelle (prévention fixation de session). Données stockées
    en session : user_id, role, nom_complet, photo_profil. Jamais le mot
    de passe ni le PIN.

-   **Étape 6 --- Journalisation** : insertion dans audit_log : type
    CONNEXION ou ECHEC_CONNEXION, user_id (si trouvé), ip_adresse,
    date_evenement.

**2.2 Politique de session**

  ----------------------------------------------------------------------------
  **Paramètre**           **Valeur**              **Fichier de configuration**
  ----------------------- ----------------------- ----------------------------
  Durée d\'inactivité     7200 secondes (2        app/Config/App.php :
                          heures)                 sessionExpiration

  Régénération à la       true (destroy + new     app/Config/App.php :
  connexion               session)                sessionRegenerateDestroy

  Cookie HttpOnly         true                    app/Config/App.php :
                                                  cookieHTTPOnly

  Cookie Secure (HTTPS    true en production      app/Config/App.php :
  uniquement)                                     cookieSecure

  Cookie SameSite         Lax                     app/Config/App.php :
                                                  cookieSameSite

  Vérification statut en  À chaque requête        app/Filters/AuthFilter.php
  cours de session        (AuthFilter)            
  ----------------------------------------------------------------------------

> **ATTENTION AuthFilter vérifie non seulement la présence de la session
> mais aussi que le compte est toujours actif en DB à chaque requête. Si
> un ADMIN désactive un compte pendant une session active,
> l\'utilisateur est déconnecté à sa prochaine requête.**

**2.3 Gestion des mots de passe**

**Règles de complexité et hachage**

-   **Longueur minimale** : 8 caractères. Validation CI4
    min_length\[8\].

-   **Complexité requise** : au moins 1 lettre majuscule et au moins 1
    chiffre. Validation par regex CI4 ou vérification PHP preg_match().

-   **Algorithme de hachage** : bcrypt (PHP PASSWORD_BCRYPT, cost factor
    12). Ce coût rend les attaques par dictionnaire extrêmement
    coûteuses en temps CPU.

-   **Jamais stocké en clair** : même temporairement. Le mot de passe
    haché est stocké uniquement dans utilisateurs.mot_de_passe.

> *ℹ️ Pourquoi bcrypt et non MD5 ou SHA-256 ? MD5/SHA-256 sont des
> algorithmes rapides (milliards de calculs/seconde sur GPU). bcrypt
> avec cost=12 nécessite \~250 ms par calcul, rendant une attaque par
> force brute 1 milliard de fois plus lente. C\'est intentionnel.*

**Réinitialisation de mot de passe**

-   **Génération du token** : bin2hex(random_bytes(32)) --- 64
    caractères hexadécimaux via CSPRNG PHP. Cryptographiquement
    sécurisé.

-   **Stockage** : token stocké en clair dans
    utilisateurs.token_reinitialisation (déjà aléatoire sur 256 bits,
    hachage superflu). Date d\'expiration dans
    utilisateurs.token_expiration (NOW() + 2 heures).

-   **Invalidation immédiate** : à l\'utilisation réussie, token mis à
    NULL en DB. Un token ne peut être utilisé qu\'une seule fois.

-   **URL de réinitialisation** : /reset-password/{token} --- jamais
    l\'identifiant utilisateur dans l\'URL.

-   **Token expiré** : message \"Ce lien de réinitialisation est expiré
    ou invalide.\" Sans indication sur l\'existence du compte.

**2.4 PIN kiosque**

-   **PIN distinct du mot de passe** : compromis du PIN n\'implique pas
    la compromission du compte de connexion et vice-versa.

-   **Hachage bcrypt identique** : password_hash(\$pin, PASSWORD_BCRYPT,
    \[\'cost\' =\> 12\]) dans employes.pin_kiosque.

-   **Blocage progressif** : après 3 erreurs de PIN consécutives sur le
    kiosque (compteur en cache CI4 clé :
    kiosque_pin_fails\_\[employe_id\]), blocage de 10 minutes.
    Journalisation ECHEC_PIN_KIOSQUE avec IP et employe_id.

-   **Communication du PIN initial** : défini par l\'ADMIN à la création
    du compte. Communiqué à l\'employé hors système (verbalement ou
    SMS). Jamais par écrit dans l\'application.

-   **Changement de PIN** : l\'employé change son PIN depuis son
    dashboard, en vérifiant d\'abord son mot de passe de connexion
    (double vérification).

**3. Contrôle d\'Accès**

**3.1 Double couche de contrôle d\'accès**

Le contrôle d\'accès est implémenté à deux niveaux indépendants et
complémentaires :

-   **Niveau 1 --- Routes CI4 (AuthFilter + RoleFilter)** : chaque route
    est protégée par un filtre déclaré dans Routes.php. Une requête vers
    une route admin sans session admin valide est rejetée AVANT
    d\'atteindre le contrôleur. Réponse : redirection vers /login ou
    page 403.

-   **Niveau 2 --- Filtre des données SQL (data-level security)** : dans
    les contrôleurs et modèles, toutes les requêtes SQL filtrent
    systématiquement sur l\'identifiant de l\'utilisateur connecté pour
    les vues personnelles. Un employé authentifié qui manipule l\'URL ne
    peut jamais obtenir les données d\'un collègue.

> **CRITIQUE La data-level security (niveau 2) est aussi critique que le
> contrôle des routes (niveau 1). Ne jamais faire confiance aux
> paramètres d\'URL ou POST pour identifier quel utilisateur afficher
> --- toujours utiliser la session courante comme référence.**

**3.2 Matrice de filtrage des données par rôle**

  -----------------------------------------------------------------------------------
  **Ressource / Donnée**        **ADMIN**         **EMPLOYÉ**       **AGENT**
  ----------------------------- ----------------- ----------------- -----------------
  presences                     Toutes (tous      WHERE employe_id  Aucun accès
                                employés)         = own_id          

  demandes_conge                Toutes            WHERE employe_id  Aucun accès
                                                  = own_id          

  soldes_conges                 Tous              WHERE employe_id  Aucun accès
                                                  = own_id          

  employes.salaire_journalier   Visible (admin    Jamais retourné   Jamais retourné
                                seulement)                          

  visiteurs                     Lecture complète  Aucun accès       CRUD complet

  documents_rh                  Tous              WHERE employe_id  Aucun accès
                                                  = own_id, lecture 

  audit_log                     Lecture complète  Aucun accès       Aucun accès

  config_systeme                Lecture +         Aucun accès       Aucun accès
                                écriture                            

  notifications                 WHERE             WHERE             WHERE
                                destinataire_id = destinataire_id = destinataire_id =
                                own_id            own_id            own_id

  employes (liste)              Tous champs       Ses propres       Noms + postes
                                                  données (profil)  uniquement
                                                                    (select dropdown)
  -----------------------------------------------------------------------------------

**3.3 Vérification d\'ownership avant accès aux fichiers**

Avant de servir tout fichier (photo de profil, document RH), le
contrôleur effectue une vérification d\'appartenance :

-   **Pour un ADMIN** : accès autorisé à tous les fichiers.

-   **Pour un EMPLOYÉ** : SELECT COUNT(\*) FROM documents_rh WHERE id =
    ? AND employe_id = ? --- si résultat = 0 : réponse HTTP 403. Cette
    vérification est obligatoire dans DocumentsController::download()
    côté employé.

-   **Pour les photos de profil** : accessibles à tous les utilisateurs
    connectés (affichées dans les tableaux de présence et de visiteurs),
    mais uniquement via le contrôleur PHP. Jamais par URL directe dans
    le répertoire storage/.

**4. Protection contre les Attaques Web**

**4.1 Injections SQL**

**Règle absolue : zéro concaténation de variables utilisateur dans les
requêtes SQL**

-   **Query Builder CI4 (méthode privilégiée)** :
    \$this-\>db-\>where(\'email\', \$email)-\>get(\'utilisateurs\').
    Toutes les valeurs passées au Query Builder sont automatiquement
    échappées par PDO.

-   **Requêtes préparées directes** : \$this-\>db-\>query(\'SELECT \*
    FROM employes WHERE matricule = ?\', \[\$matricule\]). Le paramètre
    lié ne peut jamais être interprété comme du SQL.

> **CRITIQUE INTERDIT : \$this-\>db-\>query(\"SELECT \* FROM employes
> WHERE matricule = \'\" . \$matricule . \"\'\"). Si \$matricule
> contient des guillemets ou du SQL, cette requête est injectable.
> Aucune exception à cette règle.**

**Validation stricte des entrées**

-   **Types de données attendus** : integer pour les IDs (rule
    is_natural_no_zero), date pour les dates (rule valid_date), email
    pour les adresses (rule valid_email), alpha_numeric pour les
    matricules.

-   **Longueurs maximales** : toutes les colonnes VARCHAR ont une limite
    définie. Les règles CI4 max_length\[N\] sont appliquées sur tous les
    champs texte.

-   **Aucune donnée utilisateur non validée** : toute variable provenant
    de \$\_GET, \$\_POST ou \$\_REQUEST est traitée comme non fiable
    jusqu\'à validation complète.

**4.2 Cross-Site Scripting (XSS)**

-   **Encodage HTML systématique** : toutes les variables PHP affichées
    dans les vues utilisent la fonction esc() de CI4 : \<?=
    esc(\$employe\[\'nom\'\]) ?\>. Cette fonction applique
    htmlspecialchars() avec ENT_QUOTES et UTF-8.

-   **Contenus générés par l\'IA** : les réponses du chatbot et les
    motifs générés par l\'assistant sont affichés via
    element.textContent = response en JavaScript (jamais innerHTML).
    Cela empêche toute injection HTML même si la réponse de l\'API
    contient des balises.

-   **Attributs HTML** : les données insérées dans des attributs HTML
    (data-id, title, alt) sont encodées avec esc(\$data, \'attr\') en
    CI4.

-   **URLs** : les données insérées dans des URLs (ex : href ou action)
    sont encodées avec esc(\$url, \'url\') en CI4.

**4.3 Cross-Site Request Forgery (CSRF)**

-   **Protection globale CI4** : le filtre CSRF CI4 est activé pour
    toutes les routes POST dans app/Config/Filters.php. Toute requête
    POST sans token CSRF valide est rejetée avec une erreur 403.

-   **Formulaires HTML** : chaque formulaire inclut \<?= csrf_field()
    ?\> qui génère un champ hidden avec le token CSRF de la session
    courante.

-   **Requêtes AJAX POST** : le token CSRF est placé dans un meta tag
    dans le layout principal : \<meta name=\"csrf-token\" content=\"\<?=
    csrf_hash() ?\>\"\>. Chaque appel fetch() AJAX POST lit ce meta tag
    et inclut le token dans le body ou dans le header X-CSRF-TOKEN.

-   **Renouvellement du token** : le token CSRF est renouvelé après
    chaque soumission de formulaire réussie (comportement par défaut CI4
    avec regenerate = true).

**4.4 Contrôle d\'accès aux fichiers uploadés**

-   **Répertoire storage/ hors racine web** : configuré avec Deny from
    all (.htaccess) ou location \~ \^/storage { deny all; } (Nginx).
    Aucun fichier du répertoire storage/ n\'est jamais accessible
    directement par URL HTTP.

-   **Nommage UUID** : chaque fichier uploadé reçoit un nom UUID v4
    généré par PHP (Ramsey\\Uuid\\Uuid::uuid4()-\>toString() ou
    uniqid() + random_bytes()). Il est impossible de deviner le nom
    d\'un fichier (2\^122 possibilités).

-   **Validation type MIME réel** : la bibliothèque finfo PHP (FileInfo)
    vérifie le type MIME réel du fichier (analyse du contenu binaire) et
    non uniquement l\'extension déclarée. Un fichier PHP renommé en .jpg
    est détecté et refusé.

-   **Taille maximale** : 2 Mo pour les photos (vérification PHP +
    directive upload_max_filesize dans php.ini). 5 Mo pour les documents
    RH.

-   **Types autorisés** : photos : image/jpeg, image/png uniquement.
    Documents : application/pdf, image/jpeg, image/png uniquement.

**4.5 En-têtes HTTP de sécurité**

Les en-têtes suivants sont configurés sur le serveur web (vhost Apache
ou bloc server Nginx) pour toutes les réponses HTTP :

  -----------------------------------------------------------------------------------
  **En-tête HTTP**            **Valeur recommandée**            **Protection**
  --------------------------- --------------------------------- ---------------------
  X-Frame-Options             SAMEORIGIN                        Clickjacking
                                                                (intégration
                                                                malveillante en
                                                                iframe)

  X-Content-Type-Options      nosniff                           MIME sniffing
                                                                (exécution de
                                                                fichiers avec mauvais
                                                                Content-Type)

  X-XSS-Protection            1; mode=block                     XSS réfléchi
                                                                (navigateurs legacy)

  Referrer-Policy             strict-origin-when-cross-origin   Fuite d\'URL dans les
                                                                en-têtes Referer

  Permissions-Policy          geolocation=(), microphone=(),    Abus d\'API
                              camera=(self)                     navigateur (caméra
                                                                autorisée uniquement
                                                                pour capture
                                                                visiteur)

  Strict-Transport-Security   max-age=31536000;                 Downgrade HTTPS →
                              includeSubDomains                 HTTP (HSTS)

  Content-Security-Policy     default-src \'self\'; script-src  Injection de scripts
                              \'self\' cdn.jsdelivr.net;        (XSS avancé,
                              style-src \'self\'                chargement ressources
                              \'unsafe-inline\'                 non autorisées)
                              cdn.jsdelivr.net                  
                              fonts.googleapis.com; font-src    
                              fonts.gstatic.com                 
  -----------------------------------------------------------------------------------

**5. Sécurité du Mode Kiosque**

**5.1 Menaces spécifiques au kiosque**

Le mode kiosque est exposé à des menaces particulières car il est
accessible sans authentification préalable (l\'employé s\'identifie sur
le terminal) et est destiné à fonctionner sur une machine physique
partagée.

  ------------------------------------------------------------------------------
  **Menace**             **Probabilité**   **Mitigation implémentée**
  ---------------------- ----------------- -------------------------------------
  Pointage à distance    Élevée sans       Filtre IP strict : seul le terminal
  (depuis son bureau)    protection        physique autorisé peut accéder à
                                           /kiosque. Vérification à chaque
                                           requête.

  Pointage par           Élevée sans       PIN personnel hashé bcrypt. Le
  procuration (collègue  protection        collègue ne connaît pas le PIN.
  pointe à la place)                       

  Attaque par force      Moyenne           Blocage de 10 min après 3 erreurs.
  brute sur les PINs                       Journalisation de chaque tentative.

  Injection SQL via les  Faible (mais      Requêtes préparées. Validation des
  champs du formulaire   grave)            entrées (matricule : alphanumérique,
                                           PIN : numérique uniquement).

  Accès aux autres pages Possible          Page kiosque sans menu, sans
  via le terminal                          navigation. Pas de lien vers
  kiosque                                  d\'autres parties de l\'app.

  Modification de        Impossible via    L\'heure est enregistrée par le
  l\'heure de pointage   kiosque           serveur (time() PHP). L\'employé ne
  par l\'employé                           peut pas la modifier.

  Contournement de la    Faible            Fenêtre vérifiée côté serveur (pas
  fenêtre horaire                          uniquement côté client). Hors fenêtre
                                           → message d\'erreur, pas
                                           d\'enregistrement.
  ------------------------------------------------------------------------------

**5.2 Configuration sécurisée du terminal kiosque**

-   **IP statique obligatoire** : le terminal kiosque doit avoir une
    adresse IP statique sur le réseau local. Cette IP est enregistrée
    dans config_systeme (clé ip_kiosque_autorisees). Toute tentative
    d\'accès depuis une autre IP affiche uniquement \"Terminal non
    habilité\" sans aucun formulaire.

-   **Configuration du navigateur** : le navigateur du terminal kiosque
    doit être configuré en mode kiosque navigateur (plein écran, sans
    barre d\'adresse). Chrome : \--kiosk flag. Cela empêche la
    navigation vers d\'autres URLs.

-   **Verrouillage de la session OS** : le compte Windows/Linux du
    terminal doit être configuré avec des permissions minimales (pas
    d\'accès administrateur). L\'écran de veille doit verrouiller la
    session après inactivité.

-   **Pas de mémorisation de credentials** : le navigateur du kiosque
    doit avoir la mémorisation de mots de passe désactivée.

-   **Changement de la liste IP** : modifiable uniquement par l\'ADMIN
    via l\'interface de configuration de l\'application (menu
    Configuration → Paramètres système). Toute modification est
    journalisée dans audit_log (type MODIF_CONFIG).

**6. Sécurité des Modules d\'Intelligence Artificielle**

**6.1 Protection de la clé API Anthropic**

-   **Stockage en base de données** : la clé API est stockée dans la
    table config_systeme (clé : anthropic_api_key, valeur : clé API).
    Cette table n\'est accessible qu\'aux scripts PHP backend via
    ConfigSystemeModel::get().

-   **Jamais dans le code source** : la clé API ne doit jamais être
    hardcodée dans un fichier PHP, JavaScript ou de configuration
    committé dans git.

-   **Jamais dans le frontend** : aucun fichier JavaScript côté client
    ne contient ni ne reçoit la clé API. Les appels à l\'API Anthropic
    sont exclusivement effectués depuis AnthropicClient.php côté
    serveur.

-   **Jamais dans les logs** : AnthropicClient.php ne logue jamais la
    clé API, ni dans les logs CI4, ni dans audit_log. Les logs d\'erreur
    PHP ne doivent pas inclure les headers de la requête (qui
    contiennent x-api-key).

-   **Rotation de la clé** : en cas de suspicion de compromission,
    l\'ADMIN peut modifier la clé API via l\'interface Configuration de
    l\'application (modification en DB). La nouvelle clé est effective
    immédiatement.

**6.2 Sécurité des données transmises à l\'API**

-   **Données transmises dans le prompt assistant congé** : uniquement
    le texte informel de l\'employé (max 100 chars), le type de congé et
    la durée. Aucun identifiant, aucun nom complet, aucune donnée
    personnelle identifiante.

-   **Données transmises dans le prompt chatbot** : données
    contextuelles agrégées (solde congés, nombre de jours présents,
    nombre de retards). Aucun salaire, aucun numéro de pièce
    d\'identité, aucune donnée bancaire.

-   **Historique de conversation** : maintenu uniquement en mémoire
    JavaScript (sessionStorage non utilisé). Limité aux 10 derniers
    échanges. Effacé à la fermeture du panneau chatbot. Non stocké en
    DB.

-   **Politique de données Anthropic** : selon les conditions
    d\'utilisation de l\'API Anthropic, les données envoyées via l\'API
    ne sont pas utilisées pour l\'entraînement des modèles (mode API,
    non Claude.ai). Vérifier les conditions actuelles sur anthropic.com.

**6.3 Rate limiting et protection contre les abus**

  --------------------------------------------------------------------------
  **Module IA**       **Limite**   **Fenêtre**   **Message si dépassement**
  ------------------- ------------ ------------- ---------------------------
  Assistant rédaction 3 appels     Par heure et  \"Limite d\'utilisation
  congé                            par           atteinte. Réessayez dans
                                   utilisateur   une heure.\"

  Chatbot RH          20 messages  Par heure et  \"Vous avez atteint la
                                   par           limite de messages.
                                   utilisateur   Réessayez dans une heure.\"
  --------------------------------------------------------------------------

Implémentation : RateLimiter.php utilise le cache CI4 (FileCache ou
DatabaseCache selon la configuration). Clé de cache :
rate_limit\_\[userId\]\_\[action\]. Compteur incrémenté à chaque appel.
Expiré après 3600 secondes.

**7. Journal d\'Audit et Traçabilité**

**7.1 Principes d\'immutabilité**

-   **Aucune suppression** : la table audit_log ne possède pas de
    méthode de suppression dans AuditLogModel. Aucune route ni
    contrôleur n\'expose une fonctionnalité de suppression d\'entrées
    d\'audit.

-   **Aucune modification** : idem --- aucune méthode UPDATE n\'est
    exposée sur audit_log. Une fois insérée, une entrée est permanente.

-   **Permissions DB minimales** : l\'utilisateur MySQL de
    l\'application peut avoir INSERT sur audit_log mais pas DELETE ni
    UPDATE. Cette contrainte est appliquée au niveau du moteur de base
    de données.

-   **Conservation permanente** : les entrées d\'audit ne sont jamais
    archivées automatiquement. Elles sont conservées indéfiniment.

**7.2 Couverture des événements à journaliser**

Les 21 types d\'événements suivants doivent être couverts. Cette matrice
sert de référence lors des tests de Phase 8 (vérification de couverture
d\'audit) :

  --------------------------------------------------------------------------------------------
  **Code**                  **Déclencheur**        **Données avant/après   **Responsable**
                                                   requises**              
  ------------------------- ---------------------- ----------------------- -------------------
  CONNEXION                 Standard               Authentification        ☐

  ECHEC_CONNEXION           Standard               Authentification        ☐

  DECONNEXION               Standard               Authentification        ☐

  CREATION_EMPLOYE          **Critique**           Gestion employés        ☐

  MODIFICATION_EMPLOYE      Critique ---           Gestion employés        ☐
                            avant/après                                    

  DESACTIVATION_EMPLOYE     **Critique**           Gestion employés        ☐

  POINTAGE                  Standard               Kiosque                 ☐

  CORRECTION_PRESENCE       Important ---          Présences               ☐
                            avant/après                                    

  SOUMISSION_CONGE          Standard               Congés                  ☐

  TRAITEMENT_CONGE          Important              Congés                  ☐

  ANNULATION_CONGE          Important              Congés                  ☐

  MODIF_SOLDE_CONGE         Critique ---           Congés                  ☐
                            avant/après                                    

  ENREGISTREMENT_VISITEUR   Standard               Visiteurs               ☐

  SORTIE_VISITEUR           Standard               Visiteurs               ☐

  UPLOAD_DOCUMENT           Standard               Documents RH            ☐

  SUPPRESSION_DOCUMENT      Important              Documents RH            ☐

  GENERATION_RAPPORT        Standard               Rapports                ☐

  MODIF_CONFIG              Critique ---           Configuration           ☐
                            avant/après                                    

  MODIF_JOURS_FERIES        Important              Configuration           ☐

  ECHEC_PIN_KIOSQUE         Important              Kiosque                 ☐

  ACCES_NON_AUTORISE        Important              Sécurité                ☐
  --------------------------------------------------------------------------------------------

> *ℹ️ La colonne \'Données avant/après requises\' indique si les champs
> donnees_avant et donnees_apres de audit_log doivent être renseignés
> (format JSON) pour cet événement. Obligatoire pour tous les événements
> de modification de données.*

**8. Données Personnelles et Conformité**

**8.1 Données personnelles collectées**

L\'application collecte et traite les données personnelles suivantes des
employés et visiteurs :

  -------------------------------------------------------------------------
  **Catégorie de      **Base légale**   **Durée de       **Accès**
  donnée**                              conservation**   
  ------------------- ----------------- ---------------- ------------------
  Données             Exécution du      Durée emploi +   ADMIN uniquement
  d\'identification   contrat de        archivage légal  
  (nom, prénom, date  travail                            
  naissance, CNI)                                        

  Coordonnées         Exécution du      Durée emploi +   ADMIN uniquement
  (téléphone,         contrat de        archivage        
  adresse)            travail                            

  Données de présence Obligation        Durée emploi + 5 ADMIN + EMPLOYÉ
  (heures             légale + intérêt  ans minimum      (ses propres
  arrivée/départ)     légitime                           données)

  Données salariales  Exécution du      Durée emploi +   ADMIN uniquement
  (salaire            contrat de        archivage        --- confidentiel
  journalier)         travail                            

  Documents RH        Exécution du      Durée emploi +   ADMIN + EMPLOYÉ
  (contrats,          contrat +         archivage légal  (lecture)
  bulletins)          obligation légale                  

  Données de          Intérêt légitime  12 mois          ADMIN + AGENT
  visiteurs (nom,     (sécurité des     glissants        
  CNI, motif)         locaux)           recommandé       

  Photos de profil    Consentement      Durée emploi     Utilisateurs
                      implicite (compte                  connectés
                      employé)                           

  Logs d\'audit       Obligation de     Permanente (non  ADMIN uniquement
                      sécurité + légale supprimable)     
  -------------------------------------------------------------------------

**8.2 Mesures de protection des données**

-   **Minimisation** : seules les données nécessaires aux fonctions RH
    sont collectées. Aucune donnée de géolocalisation, aucun suivi
    comportemental.

-   **Confidentialité des salaires** : le champ
    employes.salaire_journalier n\'est jamais retourné dans les requêtes
    destinées aux employés ou aux agents. Il n\'apparaît jamais dans les
    vues, exports CSV, ni rapports publics.

-   **Soft delete** : les données ne sont jamais supprimées physiquement
    de la base de données. La désactivation d\'un employé préserve
    l\'intégralité de son historique (présences, congés, documents).

-   **Chiffrement en transit** : HTTPS obligatoire en production (TLS
    1.2 minimum, TLS 1.3 recommandé). Le certificat SSL doit être
    renouvelé avant expiration.

-   **Chiffrement au repos** : optionnel mais recommandé pour le
    répertoire storage/ contenant les documents RH sensibles, via
    chiffrement du système de fichiers ou du volume.

**8.3 Conformité OHADA et droit du travail camerounais**

-   **Calcul des congés** : implémenté selon l\'article 89 du Code du
    Travail camerounais (1,5 jour ouvrable par mois de travail effectif,
    majorations par ancienneté). Voir Section 17 des SFD pour le détail.

-   **Jours fériés légaux** : les 11 jours fériés officiels du Cameroun
    sont pré-intégrés dans jours_feries. Ils sont exclus des calculs de
    jours ouvrables pour les présences et les congés.

-   **Congé maternité** : disponible comme type de congé distinct, non
    décompté du solde annuel, conformément au Code du Travail
    camerounais (14 semaines).

-   **Conservation des données** : les données RH sont conservées
    conformément aux obligations légales camerounaises en matière de
    droit du travail.

**9. Sauvegardes et Continuité**

**9.1 Stratégie de sauvegarde recommandée**

  ---------------------------------------------------------------------------
  **Composant**   **Fréquence**   **Méthode**               **Rétention**
  --------------- --------------- ------------------------- -----------------
  Base de données Quotidienne     mysqldump                 30 jours
  MySQL                           \--single-transaction     
                                  \--routines \| gzip \>    
                                  backup_YYYYMMDD.sql.gz    

  Répertoire      Hebdomadaire    tar czf                   3 mois
  storage/                        storage_YYYYMMDD.tar.gz   
  (fichiers)                      storage/                  

  Code source     À chaque        Tag git + archive du      Permanent (git)
  application     déploiement     répertoire app/           

  Fichier .env et À chaque        Copie sécurisée hors      Permanent
  config          modification    serveur (gestionnaire de  
                                  secrets)                  
  ---------------------------------------------------------------------------

> **ATTENTION Les sauvegardes de la base de données contiennent des
> données personnelles et des mots de passe hachés. Elles doivent être
> stockées dans un emplacement sécurisé (accès restreint) et chiffrées
> si stockées hors site.**

**9.2 Procédure de restauration**

-   **Restauration DB** : gunzip \< backup_YYYYMMDD.sql.gz \| mysql -u
    \[user\] -p ocanada_db. Vérifier l\'intégrité des tables après
    restauration : CHECK TABLE sur les tables principales.

-   **Restauration fichiers** : tar xzf storage_YYYYMMDD.tar.gz -C
    /chemin/vers/application/. Vérifier les permissions
    (www-data:www-data, 755 pour les répertoires, 644 pour les
    fichiers).

-   **Test de restauration** : effectuer un test de restauration complet
    au moins une fois par trimestre sur un environnement de test.
    Documenter les résultats.

**10. Checklist de Sécurité --- Pré-déploiement Production**

Cette checklist doit être complétée et signée avant toute mise en
production. Cocher chaque élément après vérification effective.

**10.1 Configuration et infrastructure**

  ---------------------------------------------------------------------------------
  **Élément à vérifier**                  **Criticité**   **Domaine**      **OK**
  --------------------------------------- --------------- ---------------- --------
  HTTPS activé avec certificat SSL valide **Critique**    Infrastructure   ☐
  (TLS 1.2+)                                                               

  CI_ENVIRONMENT = production dans .env   **Critique**    Configuration    ☐
  (désactive les messages d\'erreur                                        
  détaillés)                                                               

  Répertoire storage/ inaccessible        **Critique**    Infrastructure   ☐
  directement via URL                                                      

  Répertoire app/ inaccessible            **Critique**    Infrastructure   ☐
  directement via URL                                                      

  En-têtes HTTP de sécurité configurés    Important       Infrastructure   ☐
  sur le serveur web                                                       

  Mode strict MySQL activé                Important       Base de données  ☐
  (STRICT_TRANS_TABLES)                                                    

  Utilisateur MySQL application avec      **Critique**    Base de données  ☐
  permissions minimales (pas de GRANT,                                     
  DROP sur toutes les DB)                                                  

  Fichier .env non accessible via URL     **Critique**    Configuration    ☐

  Clé API Anthropic configurée dans       **Critique**    Configuration    ☐
  config_systeme (pas dans .env)                                           

  IP du kiosque configurée dans           **Critique**    Kiosque          ☐
  config_systeme                                                           

  Logs PHP configurés hors du répertoire  Important       Infrastructure   ☐
  public/                                                                  

  CRON jobs configurés et testés sur le   Important       Infrastructure   ☐
  serveur de production                                                    
  ---------------------------------------------------------------------------------

**10.2 Code et sécurité applicative**

  -----------------------------------------------------------------------------------
  **Élément à vérifier**                  **Criticité**   **Domaine**        **OK**
  --------------------------------------- --------------- ------------------ --------
  Toutes les routes protégées ont un      **Critique**    Authentification   ☐
  filtre auth ou role déclaré                                                

  Aucune requête SQL avec concaténation   **Critique**    Injection SQL      ☐
  de variables utilisateur                                                   

  Toutes les variables PHP affichées dans **Critique**    XSS                ☐
  les vues passent par esc()                                                 

  Protection CSRF activée sur tous les    **Critique**    CSRF               ☐
  formulaires POST                                                           

  Token CSRF inclus dans les requêtes     **Critique**    CSRF               ☐
  AJAX POST                                                                  

  Validation type MIME réel (finfo) sur   Important       Upload             ☐
  les uploads                                                                

  Fichiers uploadés nommés avec UUID (pas Important       Upload             ☐
  le nom original)                                                           

  Vérification d\'ownership avant         **Critique**    Contrôle accès     ☐
  téléchargement de fichier (employé)                                        

  Données salaire jamais retournées aux   **Critique**    Confidentialité    ☐
  vues employé/agent                                                         

  Clé API Anthropic jamais envoyée au     **Critique**    API IA             ☐
  frontend JavaScript                                                        

  Rate limiting IA fonctionnel (3/h       Standard        API IA             ☐
  assistant, 20/h chatbot)                                                   

  Audit log : aucune route de suppression **Critique**    Traçabilité        ☐
  ou modification                                                            

  Tous les 21 types d\'événements         Important       Traçabilité        ☐
  d\'audit sont journalisés                                                  
  -----------------------------------------------------------------------------------

**10.3 Comptes et accès**

  -----------------------------------------------------------------------------------
  **Élément à vérifier**                  **Criticité**   **Domaine**        **OK**
  --------------------------------------- --------------- ------------------ --------
  Compte admin initial avec mot de passe  **Critique**    Comptes            ☐
  fort (pas celui du .env.example)                                           

  Aucun compte de test actif en           **Critique**    Comptes            ☐
  production                                                                 

  Blocage après 5 tentatives de connexion Important       Authentification   ☐
  échouées fonctionnel                                                       

  Blocage PIN kiosque après 3 erreurs     Important       Kiosque            ☐
  fonctionnel                                                                

  PINs initiaux des employés communiqués  Important       Kiosque            ☐
  hors application                                                           

  Session expire après 2 heures           Standard        Session            ☐
  d\'inactivité (test manuel)                                                

  Déconnexion détruit complètement la     Important       Session            ☐
  session                                                                    
  -----------------------------------------------------------------------------------

> OK Cette checklist doit être remplie conjointement par le développeur
> responsable et l\'administrateur système avant le Go Live. Toute case
> non cochée bloque le déploiement en production.

**11. Gestion des Incidents de Sécurité**

**11.1 Types d\'incidents et réponses**

  -----------------------------------------------------------------------
  **Incident**        **Indicateurs**         **Réponse immédiate**
  ------------------- ----------------------- ---------------------------
  Compromission       Connexions depuis IP    Désactiver le compte
  possible d\'un      inhabituelles, actions  immédiatement (ADMIN).
  compte utilisateur  suspectes dans          Forcer la réinitialisation
                      audit_log               du mot de passe. Analyser
                                              audit_log.

  Fuite possible de   Utilisation anormale de Révoquer la clé sur
  la clé API          l\'API (coûts élevés,   console.anthropic.com.
  Anthropic           appels non initiés)     Générer une nouvelle clé.
                                              Mettre à jour
                                              config_systeme. Analyser
                                              les logs.

  Accès non autorisé  Entrées                 Identifier la machine.
  au terminal kiosque ACCES_NON_AUTORISE      Isoler si nécessaire.
                      répétées dans audit_log Vérifier si des pointages
                      depuis une IP inconnue  frauduleux ont eu lieu.
                                              Informer la direction.

  Upload de fichier   Fichier PHP détecté     Supprimer immédiatement le
  malveillant         malgré la validation    fichier du serveur.
                      (faille finfo)          Vérifier s\'il a été
                                              exécuté (logs serveur web).
                                              Patcher la validation.

  Suspicion           Données corrompues,     Mettre l\'application en
  d\'injection SQL    erreurs SQL inattendues maintenance. Analyser les
  réussie             dans les logs           logs MySQL. Restaurer
                                              depuis la dernière
                                              sauvegarde saine si
                                              nécessaire.
  -----------------------------------------------------------------------

**11.2 Contacts en cas d\'incident**

En cas d\'incident de sécurité avéré ou suspecté :

-   **Responsable technique projet** : contacter immédiatement le
    développeur principal du projet.

-   **Direction Ô Canada** : informer la direction dès qu\'un incident
    impliquant des données d\'employés est avéré.

-   **Journalisation de l\'incident** : documenter l\'incident, les
    actions prises et les conclusions dans un rapport d\'incident daté
    et signé.
