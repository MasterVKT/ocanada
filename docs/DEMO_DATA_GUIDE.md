# Donnees de Demonstration - Guide d Utilisation

## Objectif
Ce guide explique comment exploiter le jeu de donnees de demonstration injecte dans la base de donnees de l application OCanada.

Le seeder utilise est :
- App\\Database\\Seeds\\DemoDataSeeder

Il a ete concu pour :
- rester coherent avec les specifications fonctionnelles (roles, planning, conges, presences, visiteurs, notifications) ;
- etre idempotent (relancable sans casser la base) ;
- etre compatible avec les variantes de schema presentes dans ce depot (legacy + compat) ;
- enrichir suffisamment la base pour une demonstration credible des dashboards, de la finance et des rapports multi-periodes.

## Commandes Utiles

## 1) Generer les donnees demo
```bash
php spark db:seed DemoDataSeeder
```

## 2) Rejouer les donnees demo
Relancer la meme commande. Les donnees sont majoritairement re-utilisees ou completees sans duplication inutile.

## Comptes de Connexion Demo
Mot de passe par defaut pour les comptes demo crees/maj par le seeder :
- Demo123!

Comptes principaux :
- Admin : admin.demo@ocanada.local
- Agent : agent.demo@ocanada.local
- Employe (exemple) : pauline.ngassa@ocanada.local

Comptes employes supplementaires (meme mot de passe) :
- emmanuel.kamga@ocanada.local
- chantal.eyenga@ocanada.local
- frederic.mboua@ocanada.local
- alice.tchoumi@ocanada.local
- vincent.fotso@ocanada.local
- marie.nji@ocanada.local
- didier.mbarga@ocanada.local

## Donnees Generees

## 1) Utilisateurs et employes
- Comptes demo + prise en compte des employes deja presents dans la base.
- Nettoyage defensif du cas Anemena Guy : une seule occurrence employee est conservee si un doublon apparait.
- Fiches employes coherentes, avec :
  - matricule prefixe EMP-DEMO-### ;
  - departement, poste, type de contrat ;
  - horaires standard ;
  - PIN kiosque hash ;
  - liaison utilisateur <-> employe lorsque le schema la supporte.

## 2) Planning / shifts
- 3 modeles de shift actifs :
  - Journee Standard ;
  - Matin Operationnel ;
  - Soir Service.
- Affectations actives employees -> shifts (repartition en rotation).

## 3) Soldes de conges
- Soldes pour l annee courante sur tous les employes actifs sans solde existant.
- Champs alimentes selon les colonnes disponibles (schema legacy ou moderne).

## 4) Demandes de conges
- Plusieurs demandes avec statuts differents :
  - en_attente ;
  - approuve(e) ;
  - refuse(e) ;
  - annule(e).
- Types representes : annuel, maladie, sans_solde, autre, maternite_paternite.
- Periodes reparties sur plusieurs mois pour mieux alimenter les vues admin et employe.

## 5) Presences
- Historique de pointages sur environ 6 mois de jours ouvres, jusqu a aujourd hui.
- Cas representes :
  - present ;
  - retard ;
  - absent ;
  - absences corrigees (selon colonnes disponibles).
- Cet historique alimente directement :
  - le tableau financier ;
  - les statistiques de presences ;
  - les rapports mensuels et comparatifs.

## 6) Visiteurs
- Entrees visiteurs reparties sur plusieurs mois avec badges prefixes VIS-DEMO-###.
- Cas present et departi/sorti selon le schema.
- Suffisant pour alimenter les vues statistiques visiteurs et les historiques.

## 7) Notifications
- Notifications internes sur differents types (conge, retard, document, finance, visiteurs).
- Repartition lue/non lue.

## 8) Documents RH
- Metadonnees de documents RH pour les employes demo et les comptes deja presents.
- Fichiers references de type demo (metadata only).

## 9) Audit log
- Evenements demo inseres pour illustrer la tracabilite (connexion, pointage, conge, visiteur, finance, documents).

## 10) Donnees financieres
- Il n existe pas de table financiere dediee : les indicateurs sont derives principalement de :
  - employes.salaire_base et/ou employes.salaire_journalier ;
  - presences.statut ;
  - presences.retard_minutes.
- Le seeder enrichit donc surtout les presences historiques et les salaires pour rendre le module finance parlant en demonstration.

## Conventions et Coherence
Pour faciliter les tests et le nettoyage, les donnees demo utilisent des marqueurs stables :
- Matricules employes : EMP-DEMO-###
- Badges visiteurs : VIS-DEMO-###
- Emails demo : *.demo@ocanada.local ou *.@ocanada.local definis dans le seeder
- Messages/descriptions contenant "demo" ou "demonstration"

## Scenarios Recommandes de Test

## 1) Espace employe
- Se connecter avec un compte employe demo.
- Verifier :
  - Mes conges (liste multi-statut) ;
  - Mon planning (semaine/mois) ;
  - Mes documents ;
  - Notifications.

## 2) Espace admin
- Se connecter en admin demo.
- Verifier :
  - vue conges ;
  - vue presences ;
  - visiteurs ;
  - rapports et tableaux de bord ;
  - finance (comparaison mensuelle, ventilation par departement, classement employes).

## 3) Espace agent
- Se connecter en agent demo.
- Verifier gestion des visiteurs actifs + historiques.

## Notes Importantes
- Les donnees sont prevues pour un environnement de developpement/test uniquement.
- Le mot de passe Demo123! est volontairement simple pour faciliter les tests.
- Ne pas utiliser ce seeder en production.
- Les donnees existantes sont preservees autant que possible ; le seeder privilegie l ajout ou l enrichissement plutot que l ecrasement.

## Fichiers Ajoutes
- Seeder : app/Database/Seeds/DemoDataSeeder.php
- Guide : docs/DEMO_DATA_GUIDE.md

## Option de Nettoyage (manuel)
Si vous souhaitez purger les donnees demo, utilisez vos outils SQL en supprimant par prefixes/indices :
- employes.matricule LIKE 'EMP-DEMO-%'
- visiteurs.badge_id LIKE 'VIS-DEMO-%' ou visiteurs.numero_badge LIKE 'VIS-DEMO-%'
- utilisateurs.email LIKE '%.demo@ocanada.local'

Toujours respecter l ordre de suppression selon les contraintes FK de votre schema.
