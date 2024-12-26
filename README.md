## À propos de ce projet

Ce projet est une application web construite avec le framework Laravel pour la gestion des analyses de laboratoire. Il est conçu pour optimiser les flux de travail, améliorer l'efficacité et fournir des rapports détaillés aux professionnels du laboratoire. Les principales fonctionnalités incluent :

-   Gestion des patients et de leurs analyses.
-   Suivi en temps réel de l'avancement des analyses.
-   Rapports détaillés des revenus et activités du laboratoire.
-   Contrôle d'accès basé sur les rôles pour les administrateurs, biologistes et autres membres du personnel.
-   Intégration avec des composants UI modernes pour une interface propre et professionnelle.

## Fonctionnalités

-   **Gestion des patients :**

    -   Ajouter, visualiser et gérer les informations des patients.
    -   Assigner des analyses aux patients.

-   **Gestion des analyses :**

    -   Catégoriser et gérer différents types d'analyses (hématologie, biochimie, etc.).
    -   Suivre les états (en attente, terminé).

-   **Suivi des revenus :**

    -   Surveiller les revenus quotidiens, hebdomadaires et mensuels.

-   **Journaux d'activités :**

    -   Suivre les activités du laboratoire telles que la collecte d'échantillons, la complétion des analyses et la génération de rapports.

-   **Rapports :**
    -   Exporter des rapports détaillés sur les patients, les analyses et les revenus dans divers formats (PDF, Excel).

## Stack technologique

Ce projet utilise les technologies suivantes :

-   **Backend :** Framework Laravel - Livewire
-   **Frontend :** Templates Blade et Bootstrap pour un design réactif
-   **Base de données :** MySQL
-   **Bibliothèque de graphiques :** ApexCharts pour des visualisations interactives

## Installation

Pour configurer ce projet en local, suivez ces étapes :

1. Clonez le dépôt :

```bash
git clone https://github.com/GasyCoder/labo-lareference
```

2. Naviguez dans le répertoire du projet :

```bash
cd votre-repertoire
```

3. Installez les dépendances :

```bash
composer install
npm install
```

4. Configurez votre environnement :

-   Dupliquez le fichier `.env.example` et renommez-le en `.env`.
-   Mettez à jour les informations de connexion à la base de données et les autres variables d'environnement dans le fichier `.env`.

5. Lancez les migrations :

```bash
php artisan migrate
```

6. Démarrez le serveur de développement :

```bash
php artisan serve
```

## Utilisation

-   Accédez à l'application dans votre navigateur à l'adresse `http://localhost:8000`.
-   Connectez-vous avec les identifiants admin par défaut (définis dans le seeder ou configurés manuellement).
-   Explorez le tableau de bord, gérez les patients, les analyses et les revenus.

## Contribution

Ce projet est privé et sous la propriété intellectuelle de son propriétaire. Toute contribution est soumise à autorisation préalable. Si vous êtes un collaborateur autorisé, pour contribuer :

1. Forkez le dépôt.
2. Créez une nouvelle branche pour votre fonctionnalité ou correction de bug.
3. Soumettez une pull request avec une description détaillée de vos modifications.

## Vulnérabilités de sécurité

Si vous découvrez des problèmes de sécurité, veuillez les signaler immédiatement aux responsables du projet.

## Licence

Ce projet est un logiciel propriétaire et n'est pas sous licence open-source. Tous droits réservés au propriétaire du projet.
