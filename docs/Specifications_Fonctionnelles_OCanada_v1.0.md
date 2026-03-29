# SPÉCIFICATIONS FONCTIONNELLES — APPLICATION DE GESTION Ô CANADA

**Document** : Spécifications Fonctionnelles Détaillées (SFD)
**Version** : 1.0
**Date** : Mars 2026
**Client** : Ô Canada — Douala, Cameroun
**Statut** : Draft validé

---

## TABLE DES MATIÈRES

1. [Présentation générale](#1-présentation-générale)
2. [Utilisateurs et rôles](#2-utilisateurs-et-rôles)
3. [Architecture fonctionnelle globale](#3-architecture-fonctionnelle-globale)
4. [Module : Authentification et sécurité](#4-module--authentification-et-sécurité)
5. [Module : Mode Kiosque de pointage (anti-fraude)](#5-module--mode-kiosque-de-pointage-anti-fraude)
6. [Module : Tableau de bord Administrateur](#6-module--tableau-de-bord-administrateur)
7. [Module : Gestion des employés](#7-module--gestion-des-employés)
8. [Module : Gestion des présences](#8-module--gestion-des-présences)
9. [Module : Gestion des congés](#9-module--gestion-des-congés)
10. [Module : Gestion des visiteurs](#10-module--gestion-des-visiteurs)
11. [Module : Planning et shifts](#11-module--planning-et-shifts)
12. [Module : Notifications internes](#12-module--notifications-internes)
13. [Module : Tableau de bord RH financier (coûts d'absentéisme)](#13-module--tableau-de-bord-rh-financier)
14. [Module : Gestion des documents RH](#14-module--gestion-des-documents-rh)
15. [Module : Rapports et exports](#15-module--rapports-et-exports)
16. [Module : Présence en temps réel — Vue unifiée (UVP 1)](#16-module--présence-en-temps-réel--vue-unifiée-uvp-1)
17. [Module : Calendrier camerounais et conformité OHADA (UVP 5)](#17-module--calendrier-camerounais-et-conformité-ohada-uvp-5)
18. [Module : Assistant IA rédaction de congé (AI 2)](#18-module--assistant-ia-rédaction-de-congé-ai-2)
19. [Module : Chatbot RH interne (AI 5)](#19-module--chatbot-rh-interne-ai-5)
20. [Module : Tableau de bord Employé](#20-module--tableau-de-bord-employé)
21. [Module : Tableau de bord Agent d'accueil](#21-module--tableau-de-bord-agent-daccueil)
22. [Base de données — Modèle de données](#22-base-de-données--modèle-de-données)
23. [Règles métier globales](#23-règles-métier-globales)
24. [Stack technique et contraintes](#24-stack-technique-et-contraintes)
25. [Journal d'audit (Audit Log)](#25-journal-daudit-audit-log)

---

## 1. PRÉSENTATION GÉNÉRALE

### 1.1 Contexte et objectif

Ô Canada est une entreprise locale basée à Douala, Cameroun, employant 13 personnes. L'application vise à remplacer intégralement le système de gestion RH manuel (sur papier) par une solution digitale centralisée, sécurisée et adaptée au contexte local camerounais.

L'application couvre trois domaines fonctionnels principaux :

- La **gestion des présences** du personnel (pointage arrivée/départ, calcul automatique des heures, détection des retards et absences)
- La **gestion des visiteurs** (enregistrement, badge, suivi des entrées/sorties)
- La **gestion des congés** (demandes, approbations, suivi des soldes)

En sus de ces fonctions de base, l'application intègre des modules différenciants qui constituent la valeur unique du produit :

- Une **vue unifiée en temps réel** de toutes les personnes présentes dans les locaux (employés + visiteurs)
- Un **mode kiosque de pointage** sécurisé sur la machine physique de l'accueil
- Une **adaptation complète au droit du travail camerounais** et aux jours fériés nationaux
- Deux **fonctionnalités d'intelligence artificielle** : un assistant de rédaction de motif de congé et un chatbot RH interne

### 1.2 Périmètre de l'application

| Domaine | Inclus | Exclu |
|---|---|---|
| Présences | Pointage, calcul heures, retards, absences | Biométrie physique |
| Congés | Demandes, flux d'approbation, soldes | Intégration paie externe |
| Visiteurs | Enregistrement, badge, historique | Contrôle d'accès physique |
| RH | Fiches employés, documents, planning | Recrutement, formation |
| Finance | Estimation coût absentéisme | Paie complète |
| IA | Assistant rédaction, chatbot interne | Analyse prédictive avancée |

### 1.3 Langue et localisation

- **Langue de l'interface** : Français
- **Fuseau horaire** : Africa/Douala (UTC+1)
- **Format de date** : JJ/MM/AAAA
- **Format d'heure** : HH:MM (24h)
- **Devise** : Franc CFA (XAF) pour les calculs financiers
- **Calendrier de référence** : Calendrier officiel de la République du Cameroun
- **Référentiel juridique** : Code du Travail camerounais, réglementation OHADA

---

## 2. UTILISATEURS ET RÔLES

### 2.1 Tableau des rôles

| ID Rôle | Libellé | Abréviation | Compte requis |
|---|---|---|---|
| R01 | Administrateur / Responsable | ADMIN | Oui |
| R02 | Employé | EMP | Oui |
| R03 | Agent d'accueil | AGENT | Oui |
| R04 | Visiteur | VISIT | Non |

### 2.2 Matrice des accès par module

| Module | ADMIN | EMP | AGENT |
|---|---|---|---|
| Tableau de bord admin | ✅ Complet | ❌ | ❌ |
| Tableau de bord employé | ✅ Lecture | ✅ Personnel uniquement | ❌ |
| Mode kiosque pointage | ✅ | ✅ | ❌ |
| Gestion des employés | ✅ CRUD | ❌ | ❌ |
| Gestion présences (tous) | ✅ | ❌ | ❌ |
| Présences personnelles | ✅ | ✅ | ❌ |
| Gestion des congés (tous) | ✅ | ❌ | ❌ |
| Demande congé | ❌ | ✅ | ❌ |
| Gestion des visiteurs | ✅ Lecture | ❌ | ✅ CRUD |
| Vue unifiée temps réel | ✅ | ❌ | ✅ Lecture |
| Planning / shifts | ✅ CRUD | ✅ Lecture | ❌ |
| Documents RH (tous) | ✅ | ❌ | ❌ |
| Documents personnels | ✅ | ✅ Lecture | ❌ |
| Rapports et exports | ✅ | ❌ | ❌ |
| Coûts absentéisme | ✅ | ❌ | ❌ |
| Notifications | ✅ | ✅ | ✅ |
| Assistant IA congé | ❌ | ✅ | ❌ |
| Chatbot RH | ✅ | ✅ | ✅ |
| Calendrier camerounais | ✅ Config | ✅ Lecture | ✅ Lecture |
| Journal d'audit | ✅ | ❌ | ❌ |

### 2.3 Règles de création de comptes

- Seul l'administrateur peut créer, modifier ou désactiver un compte utilisateur.
- Un employé ne peut pas créer son propre compte.
- Le rôle d'un utilisateur est défini à la création et ne peut être changé que par l'administrateur.
- Un visiteur n'a jamais de compte dans le système. Il est enregistré par l'agent d'accueil.
- Un employé désactivé ne peut plus se connecter mais ses données historiques sont conservées intégralement.

---

## 3. ARCHITECTURE FONCTIONNELLE GLOBALE

### 3.1 Structure de navigation par rôle

**ADMIN** accède à un tableau de bord central avec un menu latéral composé de :
- Tableau de bord (accueil)
- Vue en temps réel
- Employés
- Présences
- Congés
- Visiteurs
- Planning
- Documents RH
- Rapports
- Notifications
- Configuration (jours fériés, paramètres)
- Journal d'audit
- Chatbot RH

**EMPLOYÉ** accède à une interface épurée avec :
- Mon tableau de bord
- Pointage (redirige vers le mode kiosque)
- Mes présences
- Mes congés
- Mon planning
- Mes documents
- Notifications
- Chatbot RH

**AGENT D'ACCUEIL** accède à une interface focalisée sur :
- Vue en temps réel (accueil)
- Enregistrer un visiteur
- Visiteurs présents
- Historique des visites
- Notifications
- Chatbot RH

### 3.2 Pages spéciales non soumises au menu principal

- **Page kiosque** (`/kiosque`) : page autonome dédiée au pointage, accessible uniquement depuis la machine physique de l'accueil (vérification IP). Elle n'affiche pas le menu principal de l'application.
- **Page de connexion** (`/login`) : accessible à tous les utilisateurs non connectés.

---

## 4. MODULE : AUTHENTIFICATION ET SÉCURITÉ

### 4.1 Connexion

**Écran de connexion :**
- Champ email (type email, requis)
- Champ mot de passe (masqué, requis)
- Bouton "Se connecter"
- Lien "Mot de passe oublié ?" (fonctionnalité décrite ci-dessous)

**Logique de connexion :**
1. Vérification que l'email existe dans la table `utilisateurs`
2. Vérification que le compte est actif (`statut = 'actif'`)
3. Vérification du mot de passe par comparaison avec le hash bcrypt stocké
4. En cas de succès : création d'une session PHP avec les attributs `user_id`, `role`, `nom_complet`, `photo_profil`
5. Redirection selon le rôle :
   - ADMIN → `/admin/dashboard`
   - EMP → `/employe/dashboard`
   - AGENT → `/accueil/dashboard`
6. En cas d'échec : message d'erreur générique ("Email ou mot de passe incorrect"), sans préciser lequel est faux. Après 5 tentatives échouées consécutives : blocage du compte pendant 15 minutes, journalisation de l'événement.

### 4.2 Déconnexion

- Bouton "Déconnexion" visible dans le menu de navigation.
- Destruction complète de la session PHP.
- Redirection vers la page de connexion.
- Toute page protégée vérifie la présence d'une session active. En l'absence de session valide, redirection vers `/login`.

### 4.3 Gestion des mots de passe

**Mot de passe oublié :**
- L'utilisateur saisit son email.
- Si l'email existe et que le compte est actif, un token de réinitialisation est généré (chaîne aléatoire de 64 caractères, valide 2 heures), stocké en base.
- Un lien de réinitialisation contenant ce token est affiché à l'écran (ou envoyé par email si un serveur SMTP est configuré).
- Sur la page de réinitialisation, l'utilisateur saisit et confirme son nouveau mot de passe (minimum 8 caractères, au moins une majuscule, un chiffre).

**Changement de mot de passe (profil) :**
- L'utilisateur connecté peut changer son mot de passe en saisissant l'ancien puis le nouveau deux fois.

### 4.4 Contrôle des accès

- Chaque page PHP vérifie en entête que l'utilisateur connecté possède le rôle requis pour accéder à cette ressource.
- Toute tentative d'accès à une ressource non autorisée redirige vers une page 403 avec message "Accès non autorisé".
- Les données renvoyées par les requêtes SQL sont systématiquement filtrées par l'identifiant de l'utilisateur connecté pour les vues personnelles (ex : un employé ne peut jamais récupérer les données de présence d'un autre employé).

---

## 5. MODULE : MODE KIOSQUE DE POINTAGE (ANTI-FRAUDE)

### 5.1 Concept et justification

Le mode kiosque est une page entièrement indépendante du reste de l'application, conçue pour fonctionner exclusivement sur la machine physique dédiée au pointage (ordinateur de l'accueil). Son objectif est d'éliminer toute possibilité pour un employé de pointer à distance ou de déléguer son pointage à un collègue.

### 5.2 Accès à la page kiosque

- URL dédiée : `/kiosque`
- **Vérification de la machine autorisée** : à chaque chargement de la page, le système compare l'adresse IP de la requête avec la liste des IP autorisées, stockée dans la table `config_systeme` (paramètre `ip_kiosque_autorisees`, liste CSV d'adresses IP). Si l'IP ne correspond pas, la page affiche uniquement le message "Accès non autorisé — Ce terminal n'est pas habilité au pointage" et aucun formulaire n'est affiché.
- Si l'IP est autorisée, la page kiosque s'affiche en mode plein écran, sans menu de navigation, sans en-tête de l'application, sans possibilité de naviguer vers d'autres pages.

### 5.3 Interface de la page kiosque

**Zone d'affichage permanent (haut de page) :**
- Logo Ô Canada
- Date du jour en toutes lettres (ex : "Lundi 9 mars 2026")
- Horloge en temps réel (HH:MM:SS, mise à jour chaque seconde via JavaScript)

**Zone de saisie (centre de page) :**
- Titre : "Pointage du personnel"
- Champ "Matricule ou Email" (requis)
- Champ "Code PIN" (4 à 6 chiffres, masqué, requis)
- Deux boutons distincts et visuellement différenciés :
  - Bouton vert "✅ Pointer mon arrivée"
  - Bouton rouge "🔴 Pointer mon départ"
- Message d'instruction : "Ce terminal est réservé exclusivement au pointage."

**Zone de confirmation (apparaît après chaque pointage réussi, auto-disparaît après 4 secondes) :**
- Photo de l'employé (miniature)
- Nom complet de l'employé
- Heure enregistrée
- Statut calculé (Présent / Retard / Départ enregistré)
- Message de confirmation coloré selon le statut

### 5.4 Logique de traitement d'un pointage

**Étape 1 — Identification :**
- Le système recherche l'employé par son email ou son matricule dans la table `employes` (jointure `utilisateurs`).
- Si non trouvé ou inactif : message "Employé non reconnu".

**Étape 2 — Vérification du PIN :**
- Comparaison du PIN saisi avec le hash bcrypt du PIN stocké dans `employes.pin_kiosque`.
- Si incorrect : message "PIN incorrect". Après 3 erreurs consécutives sur le même compte dans la session courante : blocage temporaire de 10 minutes pour ce compte sur ce terminal, événement journalisé dans `audit_log`.

**Étape 3 — Vérification de la fenêtre temporelle :**
- Pointage d'arrivée autorisé uniquement entre 06h00 et 10h30.
- Pointage de départ autorisé uniquement entre 15h00 et 21h00.
- Hors de ces plages : message "Pointage non autorisé à cette heure. Contactez votre responsable."
- Un dépassement hors fenêtre peut être autorisé manuellement par l'ADMIN depuis l'interface de gestion des présences (correction manuelle avec journalisation obligatoire).

**Étape 4 — Vérification de la cohérence :**
- Pour un pointage d'arrivée : vérifier qu'il n'existe pas déjà un enregistrement d'arrivée pour cet employé à cette date sans départ correspondant. Si oui : message "Vous avez déjà pointé votre arrivée aujourd'hui".
- Pour un pointage de départ : vérifier qu'il existe bien un enregistrement d'arrivée pour cet employé à cette date. Si non : message "Aucune arrivée enregistrée aujourd'hui".

**Étape 5 — Enregistrement :**
- Insertion ou mise à jour dans la table `presences`.
- Calcul immédiat du statut (`present`, `retard`) basé sur l'heure d'arrivée et l'heure de début de travail définie dans le planning de l'employé (par défaut 08h00).
- Calcul des heures travaillées à l'enregistrement du départ.
- Journalisation dans `audit_log` : type d'événement `POINTAGE`, user_id, heure, IP, statut calculé.

### 5.5 Gestion du PIN kiosque

- Le PIN est distinct du mot de passe de connexion à l'application.
- L'ADMIN peut réinitialiser le PIN de n'importe quel employé depuis la fiche employé.
- L'employé peut changer son propre PIN depuis son tableau de bord personnel (section "Sécurité"), en saisissant d'abord son mot de passe de connexion pour validation.
- Le PIN initial est défini par l'ADMIN à la création du compte employé et communiqué à l'employé.

---

## 6. MODULE : TABLEAU DE BORD ADMINISTRATEUR

### 6.1 Indicateurs clés (KPIs) en temps réel

Le tableau de bord admin est la première page affichée après connexion. Il présente les métriques suivantes, toutes calculées dynamiquement à chaque chargement de page :

**Bloc 1 — Présences du jour :**
- Nombre total d'employés actifs
- Nombre d'employés présents aujourd'hui (ayant un enregistrement d'arrivée sans départ, ou avec départ, pour la date du jour)
- Nombre d'employés en retard aujourd'hui
- Nombre d'employés absents aujourd'hui (actifs - présents - en congé approuvé aujourd'hui)
- Taux de présence du jour (présents / total actifs × 100, affiché en %)

**Bloc 2 — Visiteurs du jour :**
- Nombre total de visiteurs enregistrés aujourd'hui
- Nombre de visiteurs actuellement dans les locaux (sans heure de sortie)

**Bloc 3 — Congés :**
- Nombre de demandes de congé en attente de traitement
- Nombre d'employés actuellement en congé approuvé

**Bloc 4 — Alertes :**
- Liste des employés absents sans justification aujourd'hui (lien vers la gestion des présences)
- Liste des demandes de congé en attente depuis plus de 48 heures (lien vers la gestion des congés)

### 6.2 Graphiques du tableau de bord

**Graphique 1 — Taux de présence des 30 derniers jours :**
Graphique en courbe, axe X = dates, axe Y = pourcentage de présence. Calculé en excluant les jours non ouvrables (week-ends et jours fériés camerounais).

**Graphique 2 — Répartition des statuts de présence du mois en cours :**
Graphique en secteurs (camembert) : Présent / Retard / Absent / En congé.

**Graphique 3 — Visiteurs par semaine (4 dernières semaines) :**
Graphique en barres, une barre par semaine.

### 6.3 Accès rapides

Depuis le tableau de bord, l'ADMIN dispose de boutons d'accès rapide vers :
- "Approuver les congés en attente"
- "Voir les absences du jour"
- "Enregistrer un visiteur" (délégation à la page agent)
- "Générer le rapport du mois"

---

## 7. MODULE : GESTION DES EMPLOYÉS

### 7.1 Liste des employés

**Affichage :**
- Tableau paginé (10 lignes par page par défaut) avec les colonnes : Photo, Matricule, Nom complet, Poste, Département, Date d'embauche, Statut (Actif/Inactif), Actions.
- Barre de recherche filtrant en temps réel sur : nom, prénom, matricule, poste.
- Filtre déroulant par statut (Tous / Actifs / Inactifs).
- Filtre déroulant par département.
- Bouton "Ajouter un employé".

### 7.2 Fiche employé — Informations stockées

**Données personnelles :**
- Matricule (généré automatiquement au format `EMP-XXXX`, unique, non modifiable après création)
- Nom de famille (requis)
- Prénom (requis)
- Date de naissance (requis, format JJ/MM/AAAA)
- Genre (Homme / Femme)
- Nationalité (par défaut : Camerounaise)
- Numéro CNI ou passeport
- Adresse complète
- Téléphone principal
- Téléphone secondaire (optionnel)
- Email professionnel (requis, unique dans le système, sert d'identifiant de connexion)
- Photo de profil (upload image, formats JPG/PNG, max 2 Mo)

**Données professionnelles :**
- Poste / Intitulé de fonction (requis)
- Département / Service (requis, liste définie par l'admin)
- Type de contrat (CDI, CDD, Stage, Consultant)
- Date d'embauche (requis)
- Date de fin de contrat (optionnel, pour CDD)
- Salaire journalier brut (requis pour calcul d'absentéisme — valeur confidentielle, visible ADMIN seulement)
- Heure de début de travail (par défaut 08h00, configurable par employé)
- Heure de fin de travail théorique (par défaut 17h00)

**Données de sécurité :**
- Mot de passe de connexion (hash bcrypt, jamais affiché)
- PIN kiosque (hash bcrypt, jamais affiché)
- Statut du compte (Actif / Inactif)
- Date de dernière connexion (lecture seule)

### 7.3 Ajout d'un employé

**Formulaire en 3 étapes (wizard) :**
1. **Étape 1 — Informations personnelles** : tous les champs personnels ci-dessus
2. **Étape 2 — Informations professionnelles** : tous les champs professionnels ci-dessus
3. **Étape 3 — Accès et sécurité** : email, mot de passe temporaire, PIN kiosque initial, rôle

À la validation de l'étape 3 :
- Création de l'enregistrement dans `employes`
- Création du compte dans `utilisateurs` avec le rôle sélectionné
- Initialisation du solde de congés dans `soldes_conges` selon les règles OHADA (détaillé en §17)
- Journalisation dans `audit_log` : type `CREATION_EMPLOYE`

### 7.4 Modification d'un employé

- L'ADMIN peut modifier tous les champs de la fiche employé à l'exception du matricule.
- Toute modification est journalisée dans `audit_log` avec les valeurs avant/après pour les champs sensibles (salaire, rôle, statut).

### 7.5 Désactivation d'un employé

- La désactivation (`statut = 'inactif'`) empêche la connexion mais conserve toutes les données historiques.
- Une désactivation déclenche un message de confirmation : "Êtes-vous sûr de vouloir désactiver [Nom] ? Cette action empêchera tout accès à l'application."
- La date de désactivation est enregistrée.
- Un employé inactif n'apparaît pas dans les calculs de présence futurs mais apparaît dans l'historique.

---

## 8. MODULE : GESTION DES PRÉSENCES

### 8.1 Vue quotidienne des présences

**Accès ADMIN — Page "Présences du jour" :**
- Tableau listant tous les employés actifs avec pour chaque ligne : Photo, Nom, Poste, Heure d'arrivée, Statut (Présent/Retard/Absent/En congé), Heure de départ (si pointé), Durée travaillée (calculée).
- Code couleur des lignes : vert = présent, orange = retard, rouge = absent, bleu = en congé.
- Sélecteur de date permettant de consulter n'importe quelle journée passée.
- Bouton "Correction manuelle" par ligne (décrit en §8.4).

### 8.2 Historique des présences

**Filtres disponibles :**
- Par employé (liste déroulante)
- Par période (date début / date fin, ou sélection rapide : cette semaine, ce mois, mois précédent)
- Par statut (Présent / Retard / Absent / En congé)

**Résultats affichés en tableau :**
- Date, Employé, Heure arrivée, Heure départ, Durée travaillée, Statut, Source (Kiosque / Correction admin)

**Totaux calculés en bas de tableau :**
- Total jours présents, total jours retard, total jours absents, total heures travaillées pour la période filtrée.

### 8.3 Calculs automatiques

**Calcul du statut d'arrivée :**
- Heure d'arrivée ≤ Heure de début configurée pour l'employé (par défaut 08h00) → statut `present`
- Heure d'arrivée > Heure de début configurée → statut `retard`, écart calculé en minutes et stocké dans `presences.minutes_retard`

**Calcul des heures travaillées :**
- Au pointage du départ : `duree_travaillee = heure_depart - heure_arrivee` (en minutes, converti en HH:MM pour l'affichage)
- Si l'heure de départ n'est pas enregistrée à J+1 (employé n'a pas pointé sa sortie) : le champ `heure_depart` reste NULL, la durée reste NULL, et le statut de la journée passe à `absence_depart_manquant` — une alerte est générée pour l'ADMIN.

**Calcul des absences :**
- Un employé actif, sans enregistrement de présence pour un jour ouvrable, sans congé approuvé pour ce jour, est automatiquement marqué absent.
- Ce calcul est effectué chaque jour à 23h59 par un script planifié (cron PHP) qui insère un enregistrement `absent` dans `presences` pour chaque employé concerné.

### 8.4 Correction manuelle d'un pointage

- Accessible uniquement à l'ADMIN.
- Depuis la vue quotidienne, bouton "Modifier" sur chaque ligne.
- Formulaire permettant de : modifier l'heure d'arrivée, modifier l'heure de départ, modifier le statut, ajouter un commentaire justificatif (requis pour toute correction).
- La correction est sauvegardée avec le flag `source = 'correction_admin'`.
- L'enregistrement original est conservé dans `audit_log` (traçabilité complète).

### 8.5 Statistiques de présence (vue ADMIN)

**Tableau récapitulatif mensuel par employé :**
Colonnes : Employé, Jours ouvrables du mois, Jours présents, Jours retard, Jours absents, Jours congé, Total heures travaillées, Taux de présence (%).

**Export disponible :** PDF et CSV (voir §15).

---

## 9. MODULE : GESTION DES CONGÉS

### 9.1 Soldes de congés

**Initialisation :**
- À la création de chaque employé, un enregistrement est créé dans `soldes_conges` avec :
  - `annee` : année en cours
  - `jours_total` : calculé selon ancienneté et Code du Travail camerounais (détaillé en §17)
  - `jours_pris` : 0
  - `jours_restants` : égal à `jours_total` initialement

**Mise à jour automatique :**
- À chaque approbation d'une demande de congé, `jours_pris` est incrémenté du nombre de jours ouvrables de la demande et `jours_restants` est décrémenté en conséquence.
- À chaque refus ou annulation, les jours sont restitués.
- Les jours non ouvrables (week-ends, jours fériés) inclus dans la période de congé ne sont pas décomptés.

**Affichage ADMIN :**
- Tableau listant tous les employés avec leurs soldes : Total / Pris / Restant / En attente d'approbation.
- Filtre par année.
- L'ADMIN peut modifier manuellement un solde avec commentaire obligatoire (ex : report de congés, régularisation), modification journalisée.

### 9.2 Demande de congé (côté employé)

**Formulaire de demande :**
- Type de congé (liste déroulante) :
  - Congé annuel
  - Congé maladie (avec mention "Un justificatif médical pourra être demandé")
  - Congé maternité / paternité
  - Congé sans solde
  - Autre (champ texte libre pour préciser)
- Date de début (date picker, ne peut pas être antérieure à aujourd'hui sauf ADMIN)
- Date de fin (date picker, doit être ≥ date de début)
- **Calcul automatique en temps réel** du nombre de jours ouvrables de la demande (excluant week-ends et jours fériés), affiché sous les champs de date
- Motif (champ texte, 500 caractères max) — peut être assisté par l'IA (voir §18)
- Bouton "Envoyer la demande"

**Validations avant soumission :**
- Vérifier que `jours_ouvrables_demandes ≤ jours_restants` pour les congés annuels. Si insuffisant, message d'alerte bloquant.
- Vérifier qu'il n'existe pas une autre demande acceptée ou en attente qui chevauche la période demandée.
- Vérifier qu'il y a au moins 1 jour ouvrable dans la période.

**Après soumission :**
- Enregistrement dans `demandes_conge` avec statut `en_attente`
- Génération d'une notification interne pour l'ADMIN (voir §12)
- Affichage d'un message de confirmation à l'employé

### 9.3 Traitement d'une demande (côté ADMIN)

**Interface de gestion des congés :**
- Liste de toutes les demandes avec filtres : statut (En attente / Approuvée / Refusée), employé, période, type de congé.
- Tri par défaut : demandes en attente en premier, puis par date de soumission décroissante.

**Fiche de demande :**
- Informations de la demande (employé, type, période, motif, jours demandés)
- Solde actuel de l'employé
- Éventuels chevauchements avec d'autres congés approuvés (liste des employés absents sur la même période)
- Bouton "Approuver" (vert)
- Bouton "Refuser" (rouge)
- Champ commentaire (optionnel pour approbation, requis pour refus)

**Après décision :**
- Statut de la demande mis à jour dans `demandes_conge`
- Mise à jour du solde si approbation
- Génération d'une notification interne pour l'employé (voir §12)
- Journalisation dans `audit_log`

### 9.4 Historique des congés

**Vue ADMIN :** toutes les demandes de tous les employés, tous statuts confondus, avec filtres.
**Vue Employé :** uniquement ses propres demandes, avec le statut et le commentaire de l'ADMIN.

---

## 10. MODULE : GESTION DES VISITEURS

### 10.1 Enregistrement d'un nouveau visiteur

**Acteur principal :** Agent d'accueil (et ADMIN en second recours)

**Formulaire d'enregistrement :**
- Nom complet (requis)
- Type de pièce d'identité (liste déroulante) : CNI / Passeport / Permis de conduire / Autre
- Numéro de la pièce d'identité (requis)
- Numéro de téléphone (requis, format camerounais de préférence)
- Entreprise / Organisation (optionnel)
- Motif de la visite (requis, liste déroulante avec option "Autre" + champ libre) :
  - Rendez-vous professionnel
  - Dépôt / Retrait de dossier
  - Démarche administrative
  - Livraison
  - Autre
- Personne à voir (liste déroulante des employés actifs, requis)
- Service concerné (liste déroulante des départements, auto-rempli selon la personne à voir)
- Photo du visiteur (capture webcam optionnelle — bouton "Prendre une photo")

**Actions automatiques à la soumission :**
- Enregistrement de l'heure d'arrivée (heure serveur)
- Génération du numéro de badge au format `V[AAAAMMJJ]-[NNN]` où NNN est un numéro séquentiel du jour (ex : `V20260309-001`)
- Statut du visiteur défini à `present`
- Affichage d'un écran de confirmation avec le badge généré (imprimable)

### 10.2 Enregistrement de la sortie

- Depuis la liste des visiteurs présents, bouton "Enregistrer la sortie" sur chaque ligne.
- Confirmation demandée : "Confirmer la sortie de [Nom] ?"
- À confirmation : enregistrement de l'heure de sortie, calcul de la durée de visite, statut passe à `sorti`.
- Journalisation dans `audit_log`.

### 10.3 Liste des visiteurs présents

- Tableau en temps réel (actualisé à chaque chargement) des visiteurs ayant un statut `present`.
- Colonnes : Heure d'arrivée, Nom, Motif, Personne visitée, Badge, Durée de présence (calculée en direct), Action [Sortie].
- Durée de présence : calculée dynamiquement côté JavaScript (`heure_actuelle - heure_arrivee`), mise à jour toutes les minutes.
- Alerte visuelle (ligne en rouge) pour tout visiteur présent depuis plus de 3 heures (seuil configurable par l'ADMIN).

### 10.4 Historique des visiteurs

- Filtres : date, nom du visiteur, personne visitée, service.
- Toutes les colonnes de l'enregistrement + durée totale de visite.
- Possibilité de rechercher un visiteur par numéro de CNI ou de passeport.

### 10.5 Badge visiteur

**Contenu du badge (affichage écran et impression) :**
- Logo Ô Canada
- Titre : "BADGE VISITEUR"
- Numéro de badge (grand, lisible)
- QR code encodant : numéro de badge, nom du visiteur, date et heure d'arrivée, personne visitée
- Nom complet du visiteur
- Date et heure d'arrivée
- Personne à voir
- Mention : "Ce badge est valable uniquement pour la visite en cours"

**Génération du QR code :**
La logique de génération du QR code utilise une bibliothèque JavaScript côté client (ex : qrcode.js, incluse localement). La chaîne encodée dans le QR code est une concaténation structurée des données clés de la visite au format texte simple. Le QR code est affiché en canvas sur la page et peut être intégré dans la version imprimable du badge.

**Impression :**
- Bouton "Imprimer le badge" sur la page de confirmation d'enregistrement.
- Une feuille de style CSS `@media print` dédiée masque tout sauf le badge et déclenche une impression propre.

---

## 11. MODULE : PLANNING ET SHIFTS

### 11.1 Concept

Le module de planning permet à l'ADMIN de définir les horaires de travail prévus pour chaque employé, par semaine et par période. Ces horaires servent de référence pour le calcul précis des retards, des heures supplémentaires et de la conformité.

### 11.2 Définition des shifts

**Types de shifts (configurables par l'ADMIN) :**
L'ADMIN crée des "modèles de shift" réutilisables, stockés dans la table `shifts_modeles` :
- Nom du shift (ex : "Journée standard", "Demi-journée matin")
- Heure de début
- Heure de fin théorique
- Durée pause (en minutes, non comptée dans les heures travaillées)
- Jours actifs (cases à cocher : Lundi à Samedi)

**Exemple de shift standard Ô Canada :**
- Nom : "Journée standard"
- Début : 08h00, Fin : 17h00, Pause : 60 min
- Jours : Lundi au Vendredi

### 11.3 Affectation des employés aux shifts

- L'ADMIN affecte un shift à un ou plusieurs employés, avec une période de validité (date de début, date de fin optionnelle).
- Si un employé n'a pas de shift affecté, le shift par défaut du système est appliqué (configurable dans `config_systeme`).
- Un historique des affectations est conservé pour permettre les calculs rétrospectifs.

### 11.4 Vue calendrier du planning

**Pour l'ADMIN :**
- Vue hebdomadaire sous forme de calendrier, un colonnes par jour, une ligne par employé.
- Chaque cellule affiche le shift affecté (couleur codée) et, si la date est passée, l'indicateur de présence réelle (Présent / Retard / Absent).
- Navigation semaine par semaine.
- Clic sur une cellule : détails du pointage de cette journée.

**Pour l'EMPLOYÉ :**
- Vue de son propre planning sur 2 semaines (semaine courante + suivante).
- Affichage de ses horaires prévus et de ses jours de congé approuvés.

### 11.5 Calcul des heures supplémentaires

- Si `duree_travaillee > (heure_fin_shift - heure_debut_shift - pause_shift)` : calcul automatique des heures supplémentaires.
- Les heures supplémentaires sont affichées dans les rapports mais ne génèrent pas de paiement automatique (hors périmètre, donnée informative uniquement).

---

## 12. MODULE : NOTIFICATIONS INTERNES

### 12.1 Concept

Les notifications sont des messages internes à l'application, sans infrastructure email requise. Elles apparaissent sous forme de badge numérique sur l'icône de cloche dans la barre de navigation de chaque utilisateur.

### 12.2 Types de notifications

| Code | Déclencheur | Destinataire | Message type |
|---|---|---|---|
| NOTIF_CONGE_SOUMIS | Employé soumet une demande de congé | ADMIN | "[Nom] a soumis une demande de congé du [date1] au [date2]" |
| NOTIF_CONGE_APPROUVE | ADMIN approuve une demande | Employé concerné | "Votre congé du [date1] au [date2] a été approuvé" |
| NOTIF_CONGE_REFUSE | ADMIN refuse une demande | Employé concerné | "Votre congé du [date1] au [date2] a été refusé. Commentaire : [commentaire]" |
| NOTIF_CONGE_ATTENTE_48H | Demande en attente > 48h | ADMIN | "La demande de congé de [Nom] est en attente depuis plus de 48h" |
| NOTIF_ABSENCE_NON_JUSTIFIEE | Script cron détecte absence | ADMIN | "[Nom] est absent aujourd'hui sans justification" |
| NOTIF_DEPART_MANQUANT | Employé n'a pas pointé son départ | ADMIN | "[Nom] n'a pas enregistré son départ hier" |
| NOTIF_VISITEUR_LONG | Visiteur présent > seuil configuré | ADMIN et AGENT | "Le visiteur [Nom] est présent depuis [durée]" |
| NOTIF_CONTRAT_EXPIRATION | Contrat CDD expire dans 30 jours | ADMIN | "Le contrat de [Nom] expire le [date]" |

### 12.3 Interface des notifications

**Cloche de notification (barre de navigation) :**
- Badge numérique rouge affichant le nombre de notifications non lues.
- Au clic, panneau déroulant listant les 10 dernières notifications non lues.
- Chaque notification : icône type, texte, heure/date, lien direct vers la ressource concernée.
- Bouton "Tout marquer comme lu".
- Lien "Voir toutes les notifications" → page dédiée.

**Page des notifications :**
- Liste paginée de toutes les notifications (lues et non lues).
- Filtre par type, par date.
- Chaque notification peut être marquée comme lue individuellement.

### 12.4 Persistance

Les notifications sont stockées dans la table `notifications` et marquées lues/non lues par utilisateur. Elles ne sont jamais supprimées automatiquement (archivage permanent).

---

## 13. MODULE : TABLEAU DE BORD RH FINANCIER

### 13.1 Objectif

Ce module fournit à l'ADMIN une estimation du coût financier de l'absentéisme et des retards sur une période donnée. Les données affichées sont informatives et non contractuelles.

### 13.2 Indicateurs affichés

**Sélecteur de période :** mois courant, mois précédent, ou plage personnalisée.

**Indicateur 1 — Coût estimé des absences :**
Pour chaque employé ayant des absences injustifiées sur la période :
- Calcul : `nombre_jours_absences_injustifiees × salaire_journalier_brut`
- Total agrégé pour tous les employés

**Indicateur 2 — Impact des retards :**
- Total des minutes de retard accumulées sur la période, par employé et global
- Équivalent en jours (total_minutes / 480, une journée = 8h)

**Indicateur 3 — Coût total estimé absentéisme (absences + retards) :**
- Somme des coûts absences + équivalent financier des retards

**Indicateur 4 — Comparaison mensuelle :**
Graphique en barres comparant le coût estimé des 6 derniers mois.

**Indicateur 5 — Classement des employés par taux de présence :**
Tableau trié du meilleur au moins bon taux de présence sur la période.

### 13.3 Confidentialité

- Les salaires journaliers des employés ne sont jamais affichés directement sur cette page — seuls les coûts estimés agrégés sont montrés.
- Cette page est accessible à l'ADMIN uniquement.

---

## 14. MODULE : GESTION DES DOCUMENTS RH

### 14.1 Concept

Chaque employé dispose d'un espace de stockage de documents RH numériques dans l'application. L'objectif est de centraliser les documents administratifs essentiels.

### 14.2 Types de documents gérés

- Contrat de travail
- Avenants au contrat
- CNI / Passeport (copie)
- Diplômes et certifications
- Attestations diverses
- Fiches de paie (upload manuel par l'ADMIN)
- Notes et avertissements
- Autre (type libre)

### 14.3 Fonctionnement

**Ajout d'un document (ADMIN uniquement) :**
- Sélection de l'employé concerné
- Type de document (liste déroulante)
- Titre/description du document
- Date du document
- Upload du fichier (PDF ou image, max 5 Mo par fichier)
- Le fichier est stocké dans un répertoire sécurisé du serveur, hors de la racine web publique, et référencé dans la table `documents_rh`.

**Consultation :**
- L'ADMIN voit tous les documents de tous les employés, avec filtre par employé et type.
- L'employé voit uniquement ses propres documents en lecture seule (téléchargement autorisé).

**Suppression :**
- Uniquement par l'ADMIN, avec confirmation et journalisation dans `audit_log`.

### 14.4 Accès aux fichiers

- Tous les téléchargements de fichiers passent par un script PHP qui vérifie les droits de l'utilisateur connecté avant de servir le fichier. Les fichiers ne sont jamais accessibles par URL directe.

---

## 15. MODULE : RAPPORTS ET EXPORTS

### 15.1 Rapports disponibles

**Rapport 1 — Présences mensuel :**
- Période : un mois sélectionnable
- Contenu : pour chaque employé, tableau des jours ouvrables avec statut, récapitulatif (présents, retards, absences, heures totales, taux de présence)
- Format export : PDF (mise en page A4, logo Ô Canada, en-tête avec mois/année) et CSV

**Rapport 2 — Congés annuels :**
- Période : une année sélectionnable
- Contenu : pour chaque employé, solde initial, jours pris (liste des congés), solde restant, détail par type de congé
- Format export : PDF et CSV

**Rapport 3 — Journal des visiteurs :**
- Période sélectionnable
- Contenu : liste de toutes les visites avec toutes les colonnes de la table `visiteurs`
- Format export : PDF et CSV

**Rapport 4 — Rapport d'absentéisme :**
- Période sélectionnable
- Contenu : taux d'absentéisme global et par employé, coûts estimés (si salaire renseigné), comparaison avec période précédente
- Format export : PDF uniquement

### 15.2 Génération des rapports

- Les rapports PDF sont générés côté serveur en PHP en utilisant une bibliothèque de génération PDF (ex : TCPDF ou DOMPDF).
- Les exports CSV sont générés directement en PHP avec les en-têtes HTTP appropriées pour déclencher le téléchargement.
- Chaque génération de rapport est journalisée dans `audit_log` (qui a généré quoi, quand).

---

## 16. MODULE : PRÉSENCE EN TEMPS RÉEL — VUE UNIFIÉE (UVP 1)

### 16.1 Concept et positionnement

Cette vue est la fonctionnalité différenciante la plus forte de l'application. Elle offre en un seul écran une vision consolidée de **toutes les personnes physiquement présentes dans les locaux d'Ô Canada** à l'instant T, en combinant les données des employés pointés et des visiteurs enregistrés.

### 16.2 Accès

- ADMIN : accès depuis le menu principal "Vue en temps réel" et depuis le tableau de bord (widget cliquable)
- AGENT D'ACCUEIL : page d'accueil par défaut après connexion

### 16.3 Interface de la vue unifiée

**En-tête de la page :**
- Titre : "Personnes présentes dans les locaux"
- Date et heure de la dernière actualisation
- Bouton "Actualiser" (rechargement de la page)
- Actualisation automatique toutes les 2 minutes (via JavaScript `setTimeout` + rechargement de la section via AJAX ou rechargement partiel de page)

**Compteurs globaux (affichage proéminent) :**
- Total personnes dans les locaux : [N] (employés + visiteurs)
- dont [X] employés
- dont [Y] visiteurs

**Section 1 — Employés présents :**
Tableau avec colonnes : Photo, Nom, Poste, Heure d'arrivée, Durée de présence, Statut (Présent / Retard)
- Ligne en orange si l'employé est marqué "Retard"
- Chaque ligne est cliquable (ADMIN uniquement) → fiche de présence de l'employé

**Section 2 — Visiteurs présents :**
Tableau avec colonnes : Badge, Nom, Heure d'arrivée, Motif, Personne visitée, Durée de présence, Action [Sortie] (AGENT et ADMIN uniquement)
- Ligne en rouge si le visiteur est présent depuis plus de 3 heures

**Section 3 — Employés absents aujourd'hui :**
Liste (masquable) des employés actifs non pointés, sans congé approuvé pour aujourd'hui.

### 16.4 Données et actualisation

- Les données sont issues d'une requête SQL combinant la table `presences` (date du jour, sans heure de départ) et la table `visiteurs` (date du jour, statut `present`).
- L'heure de la dernière actualisation est horodatée en JavaScript avec l'heure locale.

---

## 17. MODULE : CALENDRIER CAMEROUNAIS ET CONFORMITÉ OHADA (UVP 5)

### 17.1 Jours fériés officiels du Cameroun

Les jours fériés légaux suivants sont pré-intégrés dans la table `jours_feries` et exclus de tous les calculs de jours ouvrables (présences, congés, rapports) :

| Date | Désignation |
|---|---|
| 1er janvier | Jour de l'An |
| 11 février | Fête de la Jeunesse |
| Variable | Vendredi Saint (calculé) |
| Variable | Lundi de Pâques (calculé) |
| 1er mai | Fête du Travail |
| 20 mai | Fête Nationale |
| 15 août | Fête de l'Assomption |
| Variable | Fête de l'Aïd el-Fitr (estimée) |
| Variable | Fête de l'Aïd el-Adha (estimée) |
| Variable | Fête du Mouloud (estimée) |
| 25 décembre | Noël |

**Gestion des jours fériés variables :**
- Les jours fériés religieux à date variable (Pâques, fêtes islamiques) sont pré-saisis manuellement pour l'année en cours par l'ADMIN.
- L'interface de configuration (menu Configuration → Jours fériés) permet à l'ADMIN d'ajouter, modifier ou supprimer des jours fériés.
- Les jours fériés supplémentaires exceptionnels (décrets présidentiels) peuvent être ajoutés par l'ADMIN.

### 17.2 Calcul des jours ouvrables

La fonction de calcul des jours ouvrables, utilisée par tous les modules (congés, présences, rapports), applique la logique suivante :
- Un jour ouvrable est un jour de la semaine (Lundi à Vendredi par défaut) qui n'est pas un jour férié officiel camerounais.
- Le Samedi est considéré non ouvrable par défaut (configurable dans `config_systeme` selon les pratiques d'Ô Canada).
- Pour toute période [date_debut, date_fin], le calcul itère sur chaque jour de la période et exclut les jours non ouvrables.

### 17.3 Conformité avec le Code du Travail camerounais

**Calcul des droits à congé annuel :**
Selon l'article 89 du Code du Travail camerounais :
- Droit à congé annuel de base : 1,5 jour ouvrable par mois de travail effectif, soit 18 jours par an pour une année complète
- Majoration par tranche d'ancienneté :
  - De 5 à 10 ans d'ancienneté : +1 jour supplémentaire par an
  - De 10 à 15 ans : +2 jours supplémentaires
  - De 15 à 20 ans : +3 jours supplémentaires
  - Au-delà de 20 ans : +4 jours supplémentaires

**Calcul automatique de l'ancienneté :**
À chaque chargement des soldes de congés, le système calcule `anciennete_annees = (date_actuelle - date_embauche) / 365.25` et détermine la tranche applicable.

**Initialisation des soldes en cours d'année :**
Un employé embauché en cours d'année acquiert des droits au prorata. Le calcul est : `jours_total = 1.5 × nombre_mois_travailes_depuis_embauche + majoration_anciennete`.

**Congé maternité :**
Le système signale (sans automatisation complète) que le Code du Travail camerounais prévoit 14 semaines de congé maternité pour les femmes. Ce type de congé est disponible dans la liste des types de congé et n'est pas décompté du solde de congé annuel.

---

## 18. MODULE : ASSISTANT IA RÉDACTION DE CONGÉ (AI 2)

### 18.1 Concept

Sur le formulaire de demande de congé de l'employé, un bouton "✨ Aide à la rédaction" est disponible à côté du champ "Motif". Lorsqu'il est cliqué, un mini-panneau s'ouvre permettant à l'employé d'indiquer en quelques mots (langage naturel, informel) ce qu'il souhaite exprimer, et l'IA génère un motif formel et professionnel.

### 18.2 Interface utilisateur

**Formulaire principal :**
- Champ "Motif de la demande" (textarea, 500 caractères max)
- Bouton "✨ Aide IA à la rédaction" (icône étoile, couleur accentuée)

**Panneau IA (apparaît à côté ou en dessous du champ) :**
- Titre : "Assistant de rédaction"
- Label : "Décrivez brièvement votre situation (en vos propres mots) :"
- Champ texte court (100 caractères max)
- Bouton "Générer le motif"
- Zone d'affichage du motif généré (avec indicateur de chargement pendant la génération)
- Bouton "Utiliser ce motif" (copie le texte généré dans le champ Motif principal)
- Bouton "Regénérer" (nouvelle tentative)
- Lien "Fermer l'assistant"

### 18.3 Logique d'appel API

**Préparation de l'appel :**
L'appel à l'API Anthropic est effectué depuis le backend PHP (jamais directement depuis le JavaScript côté client, pour protéger la clé API). Le frontend envoie via AJAX au backend PHP les données suivantes : texte informel de l'employé, type de congé sélectionné, durée de la demande (en jours).

**Construction du prompt système (côté serveur PHP) :**
Le prompt système envoyé à l'API définit le contexte suivant : l'assistant est un aide à la rédaction RH pour une entreprise camerounaise, il doit produire un motif formel en français, professionnel, courtois, d'une longueur de 2 à 4 phrases maximum, adapté au type de congé et à la durée indiqués, sans inventer de détails que l'employé n'a pas fournis.

**Message utilisateur :**
Le texte informel de l'employé est transmis tel quel comme message utilisateur.

**Paramètres de l'appel API :**
- Modèle : `claude-sonnet-4-20250514`
- `max_tokens` : 200 (motif court suffisant)
- Temperature : 0.7 (légère créativité tout en restant formel)

**Traitement de la réponse :**
Le texte généré (premier bloc de type "text" dans `data.content`) est renvoyé au frontend en JSON et affiché dans la zone de résultat.

**Gestion des erreurs :**
Si l'appel API échoue (timeout, erreur réseau, erreur API), un message est affiché : "L'assistant est temporairement indisponible. Veuillez rédiger votre motif manuellement." Le champ motif reste disponible et la soumission du formulaire est toujours possible.

### 18.4 Sécurité et coût

- La clé API est stockée dans un fichier de configuration PHP situé hors de la racine web, jamais exposée côté client.
- Un rate limit est implémenté côté serveur PHP : maximum 3 appels par utilisateur par heure, pour limiter les coûts. Au-delà, message "Limite d'utilisation atteinte, réessayez dans une heure".
- Les textes saisis et générés ne sont pas conservés en base de données.

---

## 19. MODULE : CHATBOT RH INTERNE (AI 5)

### 19.1 Concept

Le chatbot RH est une interface conversationnelle légère intégrée dans l'application, accessible à tous les utilisateurs connectés (ADMIN, EMPLOYÉ, AGENT). Il permet de poser des questions en langage naturel sur les données RH personnelles et les règles de l'entreprise, sans naviguer dans les différents menus.

### 19.2 Interface utilisateur

**Positionnement :** Icône de chat flottante en bas à droite de toutes les pages de l'application (sauf la page kiosque). Au clic, un panneau de chat s'ouvre en superposition (overlay), sans quitter la page courante.

**Interface du panneau chat :**
- En-tête : "Assistant RH — Ô Canada" avec icône et bouton de fermeture
- Zone de messages (scrollable, messages alternant gauche/droite selon émetteur)
- Indicateur de frappe ("..." animé pendant la génération de réponse)
- Champ de saisie du message + bouton Envoyer
- Message de bienvenue automatique à l'ouverture : "Bonjour [Prénom] ! Je suis votre assistant RH. Posez-moi vos questions sur vos congés, vos présences ou les règles de l'entreprise."

### 19.3 Capacités et périmètre du chatbot

**Questions auxquelles le chatbot peut répondre (données dynamiques de la base) :**

Pour un EMPLOYÉ connecté :
- "Combien de jours de congé me reste-t-il ?" → interroge `soldes_conges` de l'employé
- "Quand était mon dernier congé ?" → interroge `demandes_conge` de l'employé
- "Combien d'heures ai-je travaillé ce mois ?" → interroge `presences` de l'employé
- "Est-ce que ma demande de congé est approuvée ?" → interroge `demandes_conge`
- "Combien de retards ai-je eu ce mois ?" → interroge `presences`
- "Quel est mon taux de présence ?" → calcul sur `presences`

Pour un ADMIN connecté :
- Toutes les questions ci-dessus pour n'importe quel employé nommé
- "Combien d'employés sont présents aujourd'hui ?"
- "Qui est en congé cette semaine ?"
- "Combien de demandes de congé sont en attente ?"

**Questions sur les règles RH (réponses statiques dans le prompt système) :**
- "Comment calculer les congés ?" → règle OHADA intégrée dans le prompt
- "Quels sont les jours fériés au Cameroun ?" → liste intégrée dans le prompt
- "Quel est le délai pour approuver une demande de congé ?"

### 19.4 Logique d'appel API

**Architecture générale :**
L'appel API est géré par un endpoint PHP dédié (`/api/chatbot.php`) qui :
1. Reçoit le message de l'utilisateur et l'historique de la conversation (envoyés depuis le frontend en AJAX)
2. Récupère en base de données les données contextuelles pertinentes de l'utilisateur connecté (solde congés, résumé présences du mois, statut des demandes en cours)
3. Construit le prompt système avec ces données et les règles RH
4. Appelle l'API Anthropic avec l'historique complet de la conversation
5. Renvoie la réponse au frontend

**Prompt système (construction côté serveur) :**
Le prompt système inclut : l'identité de l'assistant (assistant RH interne d'Ô Canada), le profil de l'utilisateur connecté (nom, rôle, poste), les données RH personnelles récupérées en base, les règles RH d'Ô Canada, les jours fériés camerounais, et une instruction pour répondre en français, de façon concise et conversationnelle, et de refuser poliment toute question hors du périmètre RH.

**Gestion de l'historique de conversation :**
L'historique des messages de la session de chat (tableau d'objets `{role, content}`) est maintenu dans l'état JavaScript côté client. À chaque nouveau message, le tableau complet est envoyé au backend. La taille de l'historique est limitée aux 10 derniers échanges pour contrôler la taille du contexte envoyé à l'API.

**Paramètres API :**
- Modèle : `claude-sonnet-4-20250514`
- `max_tokens` : 500 (réponses concises)

**Gestion des erreurs :**
Si l'API est indisponible, le chatbot affiche : "Je suis temporairement indisponible. Consultez directement votre espace pour retrouver ces informations."

**Rate limit :** Maximum 20 messages par utilisateur par heure pour contrôler les coûts.

### 19.5 Limites et comportements défensifs

- Le chatbot ne peut jamais modifier de données (lecture seule sur les requêtes SQL).
- Si la question concerne un autre employé (posée par un EMP), le chatbot répond : "Je ne peux vous fournir que vos propres informations."
- Si la question est hors périmètre RH, le chatbot répond : "Je suis spécialisé dans les questions RH de l'entreprise. Pour d'autres sujets, veuillez contacter votre responsable."
- La conversation n'est pas sauvegardée en base de données (session uniquement, disparaît à la fermeture du panneau).

---

## 20. MODULE : TABLEAU DE BORD EMPLOYÉ

### 20.1 Page d'accueil employé

**Bloc de bienvenue :**
- Message : "Bonjour [Prénom] — [Jour de la semaine] [date]"
- Statut de pointage du jour :
  - Si pas encore pointé : bouton proéminent "Aller pointer mon arrivée" (redirige vers `/kiosque`)
  - Si arrivée pointée sans départ : "Vous avez pointé à [heure] — [Présent / En retard]" + bouton "Aller pointer mon départ"
  - Si arrivée et départ pointés : "Journée terminée — [X]h[Y] travaillées"

### 20.2 Statistiques personnelles du mois

- Jours travaillés / Jours ouvrables du mois (ex : 14/22)
- Nombre de retards
- Total heures travaillées
- Taux de présence (%) avec barre de progression colorée

### 20.3 Solde de congés

- Jours de congé annuels total / pris / restants (graphique simple ou barres)
- Dernière demande et son statut

### 20.4 Mini-calendrier

Calendrier mensuel avec code couleur par jour :
- Vert : jour travaillé (présent)
- Orange : jour travaillé (retard)
- Rouge : absent
- Bleu : en congé
- Gris : weekend / jour férié

### 20.5 Accès rapides

- Bouton "Demander un congé"
- Bouton "Voir mon historique de présences"
- Bouton "Voir mes documents RH"

---

## 21. MODULE : TABLEAU DE BORD AGENT D'ACCUEIL

L'agent d'accueil atterrit directement sur la **Vue unifiée en temps réel** (§16) après connexion. Depuis son menu, il accède aux sections suivantes :

- **Vue en temps réel** (page d'accueil)
- **Enregistrer un visiteur** (formulaire §10.1)
- **Historique des visiteurs** (§10.4)
- **Notifications** (§12)
- **Chatbot RH** (§19)

L'agent d'accueil n'a pas accès aux données RH des employés (présences, congés, salaires). Sa fonction est exclusivement centrée sur la gestion des visiteurs et la vision de qui est présent.

---

## 22. BASE DE DONNÉES — MODÈLE DE DONNÉES

### 22.1 Tables principales

**Table `utilisateurs` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `email` (VARCHAR 255, UNIQUE, NOT NULL), `mot_de_passe` (VARCHAR 255, NOT NULL — hash bcrypt), `role` (ENUM : admin, employe, agent — NOT NULL), `statut` (ENUM : actif, inactif — DEFAULT actif), `date_creation` (DATETIME), `derniere_connexion` (DATETIME NULL), `token_reinitialisation` (VARCHAR 64 NULL), `token_expiration` (DATETIME NULL)

**Table `employes` :**
Colonnes : `id` (INT, PK), `utilisateur_id` (INT, FK → utilisateurs.id, UNIQUE), `matricule` (VARCHAR 20, UNIQUE, NOT NULL), `nom` (VARCHAR 100, NOT NULL), `prenom` (VARCHAR 100, NOT NULL), `date_naissance` (DATE), `genre` (ENUM : homme, femme), `nationalite` (VARCHAR 100), `numero_cni` (VARCHAR 50), `adresse` (TEXT), `telephone_1` (VARCHAR 20), `telephone_2` (VARCHAR 20 NULL), `photo` (VARCHAR 255 NULL — chemin fichier), `poste` (VARCHAR 150), `departement` (VARCHAR 100), `type_contrat` (ENUM : CDI, CDD, stage, consultant), `date_embauche` (DATE NOT NULL), `date_fin_contrat` (DATE NULL), `salaire_journalier` (DECIMAL 10,2 NULL), `heure_debut_travail` (TIME DEFAULT 08:00:00), `heure_fin_travail` (TIME DEFAULT 17:00:00), `pin_kiosque` (VARCHAR 255 NULL — hash bcrypt), `statut` (ENUM : actif, inactif — DEFAULT actif), `date_desactivation` (DATE NULL)

**Table `presences` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `employe_id` (INT, FK → employes.id, NOT NULL), `date_presence` (DATE NOT NULL), `heure_arrivee` (TIME NULL), `heure_depart` (TIME NULL), `duree_travaillee_minutes` (INT NULL), `statut_arrivee` (ENUM : present, retard, absent, conge, absence_depart_manquant NULL), `minutes_retard` (INT DEFAULT 0), `source` (ENUM : kiosque, correction_admin DEFAULT kiosque), `commentaire_admin` (TEXT NULL), `shift_modele_id` (INT NULL, FK → shifts_modeles.id), UNIQUE KEY `uk_employe_date` (`employe_id`, `date_presence`)

**Table `visiteurs` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `numero_badge` (VARCHAR 20, UNIQUE, NOT NULL), `nom_complet` (VARCHAR 255, NOT NULL), `type_piece` (ENUM : CNI, passeport, permis, autre), `numero_piece` (VARCHAR 100), `telephone` (VARCHAR 20), `entreprise` (VARCHAR 255 NULL), `motif_visite` (VARCHAR 255), `motif_detail` (TEXT NULL), `personne_a_voir_id` (INT, FK → employes.id), `service` (VARCHAR 100), `heure_arrivee` (DATETIME NOT NULL), `heure_sortie` (DATETIME NULL), `duree_visite_minutes` (INT NULL), `statut` (ENUM : present, sorti DEFAULT present), `photo` (VARCHAR 255 NULL), `agent_id` (INT, FK → utilisateurs.id — qui a enregistré)

**Table `demandes_conge` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `employe_id` (INT, FK → employes.id), `type_conge` (ENUM : annuel, maladie, maternite_paternite, sans_solde, autre), `type_detail` (VARCHAR 100 NULL), `date_debut` (DATE NOT NULL), `date_fin` (DATE NOT NULL), `jours_ouvrables` (INT NOT NULL), `motif` (TEXT), `statut` (ENUM : en_attente, approuvee, refusee, annulee DEFAULT en_attente), `date_soumission` (DATETIME NOT NULL), `date_traitement` (DATETIME NULL), `traite_par` (INT NULL, FK → utilisateurs.id), `commentaire_admin` (TEXT NULL)

**Table `soldes_conges` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `employe_id` (INT, FK → employes.id), `annee` (YEAR NOT NULL), `jours_total` (DECIMAL 5,1 NOT NULL), `jours_pris` (DECIMAL 5,1 DEFAULT 0), `jours_restants` (DECIMAL 5,1 NOT NULL), `date_mise_a_jour` (DATETIME), UNIQUE KEY `uk_employe_annee` (`employe_id`, `annee`)

**Table `shifts_modeles` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `nom` (VARCHAR 100, NOT NULL), `heure_debut` (TIME NOT NULL), `heure_fin` (TIME NOT NULL), `pause_minutes` (INT DEFAULT 60), `jours_actifs` (VARCHAR 20 — ex: "1,2,3,4,5" pour Lun-Ven), `actif` (TINYINT DEFAULT 1)

**Table `affectations_shifts` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `employe_id` (INT, FK → employes.id), `shift_id` (INT, FK → shifts_modeles.id), `date_debut` (DATE NOT NULL), `date_fin` (DATE NULL), `actif` (TINYINT DEFAULT 1)

**Table `notifications` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `destinataire_id` (INT, FK → utilisateurs.id), `type` (VARCHAR 50 — code notification), `message` (TEXT NOT NULL), `lien` (VARCHAR 255 NULL), `lue` (TINYINT DEFAULT 0), `date_creation` (DATETIME NOT NULL), `date_lecture` (DATETIME NULL)

**Table `documents_rh` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `employe_id` (INT, FK → employes.id), `type_document` (VARCHAR 100), `titre` (VARCHAR 255), `date_document` (DATE), `chemin_fichier` (VARCHAR 255 NOT NULL), `nom_original` (VARCHAR 255), `taille_octets` (INT), `uploade_par` (INT, FK → utilisateurs.id), `date_upload` (DATETIME NOT NULL)

**Table `jours_feries` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `date_ferie` (DATE UNIQUE NOT NULL), `designation` (VARCHAR 255 NOT NULL), `type` (ENUM : fixe, variable), `annee` (YEAR NOT NULL)

**Table `config_systeme` :**
Colonnes : `cle` (VARCHAR 100, PK), `valeur` (TEXT), `description` (TEXT NULL)
Entrées initiales : `ip_kiosque_autorisees`, `seuil_alerte_visiteur_heures`, `heure_debut_pointage_arrivee`, `heure_fin_pointage_arrivee`, `heure_debut_pointage_depart`, `heure_fin_pointage_depart`, `samedi_ouvrable`, `departements_liste`, `shift_defaut_id`, `anthropic_api_key`

**Table `audit_log` :**
Colonnes : `id` (INT, PK, AUTO_INCREMENT), `utilisateur_id` (INT NULL), `type_evenement` (VARCHAR 100 NOT NULL), `description` (TEXT), `donnees_avant` (JSON NULL), `donnees_apres` (JSON NULL), `ip_adresse` (VARCHAR 45), `date_evenement` (DATETIME NOT NULL)

---

## 23. RÈGLES MÉTIER GLOBALES

### 23.1 Règles de présence

- Un employé ne peut avoir qu'un seul enregistrement de présence par jour (contrainte UNIQUE sur employe_id + date_presence).
- L'heure de départ doit toujours être postérieure à l'heure d'arrivée. Toute correction manuelle violant cette règle est bloquée.
- Les jours non ouvrables (week-ends, jours fériés) ne génèrent pas d'absence automatique.
- Une journée de congé approuvée ne génère pas d'absence, même sans pointage.

### 23.2 Règles de congés

- On ne peut soumettre une demande de congé annuel si le solde restant est inférieur au nombre de jours demandés.
- Deux demandes de congé d'un même employé ne peuvent pas se chevaucher.
- Une demande approuvée ne peut être annulée que par l'ADMIN, avec commentaire obligatoire et restitution des jours au solde.
- Les jours de congé non pris à la fin de l'année sont perdus (pas de report automatique, sauf décision manuelle de l'ADMIN).

### 23.3 Règles de gestion des visiteurs

- Un visiteur sans enregistrement de sortie à 23h59 voit son statut automatiquement passé à `sorti` et son heure de sortie enregistrée à 23h59 (script cron de clôture quotidienne), avec une note "Sortie non enregistrée".
- Le numéro de badge est unique dans toute la base de données (pas seulement sur la journée).

### 23.4 Règles de sécurité

- Toute modification de données par l'ADMIN sur des enregistrements existants (présences, congés, soldes) est systématiquement journalisée dans `audit_log`.
- Aucune donnée n'est jamais supprimée physiquement (soft delete ou archivage uniquement).
- Les fichiers uploadés (documents RH, photos) doivent être nommés avec un identifiant UUID généré côté serveur, jamais avec le nom original du fichier (sécurité).

---

## 24. STACK TECHNIQUE ET CONTRAINTES

### 24.1 Technologies

| Couche | Technologie | Rôle |
|---|---|---|
| Frontend — Structure | HTML5 | Balisage sémantique |
| Frontend — Style | CSS3 + Bootstrap 5 | Design responsive, composants UI |
| Frontend — Comportement | JavaScript (Vanilla) | Horloge temps réel, AJAX, validations, QR code |
| Frontend — Graphiques | Chart.js (CDN) | Graphiques du tableau de bord |
| Backend — Logique | PHP 8.x | Traitement, calculs, sécurité, sessions |
| Backend — BDD | MySQL 8.x | Stockage de toutes les données |
| IA | API Anthropic Claude (Sonnet) | Assistant congé, chatbot RH |
| QR Code | qrcode.js (intégration locale) | Génération QR badge visiteur |
| PDF | TCPDF ou DOMPDF (PHP) | Génération des rapports |

### 24.2 Contraintes techniques

- L'application est une application web monopage classique (pas de framework SPA). Navigation via liens PHP avec rechargement de page complet, sauf pour les fonctionnalités AJAX (chatbot, assistant IA, actualisation vue temps réel).
- Toutes les requêtes SQL utilisent des requêtes préparées (PDO avec paramètres liés) pour prévenir les injections SQL.
- La protection CSRF est implémentée sur tous les formulaires POST via un token de session.
- Les fichiers uploadés sont validés côté serveur (type MIME réel, pas seulement l'extension, et taille maximale).
- L'application doit fonctionner correctement sur les navigateurs modernes Chrome, Firefox et Edge.
- Le design est responsive (Bootstrap 5) et doit s'afficher correctement sur écran d'ordinateur (résolution minimale 1280x768) et sur tablette.
- La clé API Anthropic est stockée dans `config_systeme` en base de données (non dans un fichier `.env` pour simplifier le déploiement), accessible uniquement par les scripts PHP backend.

---

## 25. JOURNAL D'AUDIT (AUDIT LOG)

### 25.1 Événements journalisés

| Code événement | Description |
|---|---|
| CONNEXION | Connexion réussie d'un utilisateur |
| ECHEC_CONNEXION | Tentative de connexion échouée |
| DECONNEXION | Déconnexion d'un utilisateur |
| CREATION_EMPLOYE | Création d'une fiche employé |
| MODIFICATION_EMPLOYE | Modification de données employé (avec avant/après) |
| DESACTIVATION_EMPLOYE | Désactivation d'un compte employé |
| POINTAGE | Enregistrement d'un pointage (kiosque) |
| CORRECTION_PRESENCE | Correction manuelle d'un pointage par l'ADMIN |
| SOUMISSION_CONGE | Soumission d'une demande de congé |
| TRAITEMENT_CONGE | Approbation ou refus d'une demande |
| ANNULATION_CONGE | Annulation d'un congé approuvé |
| MODIF_SOLDE_CONGE | Modification manuelle d'un solde |
| ENREGISTREMENT_VISITEUR | Enregistrement d'un nouveau visiteur |
| SORTIE_VISITEUR | Enregistrement de la sortie d'un visiteur |
| UPLOAD_DOCUMENT | Upload d'un document RH |
| SUPPRESSION_DOCUMENT | Suppression d'un document RH |
| GENERATION_RAPPORT | Génération et téléchargement d'un rapport |
| MODIF_CONFIG | Modification de la configuration système |
| MODIF_JOURS_FERIES | Modification du calendrier des jours fériés |
| ECHEC_PIN_KIOSQUE | Tentative de PIN incorrect (kiosque) |
| ACCES_NON_AUTORISE | Tentative d'accès à une ressource non permise |

### 25.2 Interface du journal d'audit

**Accès :** ADMIN uniquement, depuis menu "Journal d'audit"

**Affichage :**
- Tableau paginé (25 lignes par page) avec colonnes : Date/Heure, Utilisateur, Type d'événement, Description, IP, Détails (bouton)
- Filtres : type d'événement, utilisateur, plage de dates
- Le bouton "Détails" ouvre une modale affichant les données avant/après pour les événements de modification
- Export CSV disponible

### 25.3 Conservation

Les entrées de l'audit log sont conservées de façon permanente et ne peuvent être ni modifiées ni supprimées, même par l'ADMIN. C'est une garantie d'intégrité des traces.

---

*Fin du document de spécifications fonctionnelles — Version 1.0 — Mars 2026*
*Document produit pour le projet Ô Canada — Douala, Cameroun*
*Toute modification de ce document doit faire l'objet d'une nouvelle version numérotée.*
