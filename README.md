# 📚 Mangarake

Application de gestion et de réservation de mangas en boutique physique, permettant aux clients de parcourir un catalogue, réserver des tomes disponibles et suivre leurs commandes, et à un administrateur de gérer le catalogue et les commandes.

## 📋 Description

Mangarake est une application web full-stack développée dans le cadre du Bachelor Concepteur Développeur d'Applications (CDA) à l'IPSSI.

L'application permet de :

- Parcourir un catalogue de mangas et consulter le détail de chaque tome (stock, prix, synopsis)
- Trier le catalogue par popularité (nombre de réservations)
- Réserver des tomes disponibles et les ajouter à un panier
- Soumettre une commande et suivre son statut (en attente, soumise, confirmée, expirée)
- Consulter son historique de commandes
- Administrer le catalogue : recherche et import de mangas depuis une API externe, gestion des tomes, des utilisateurs et des commandes

Stack technique :

- 🔵 **Back-end** : Symfony (PHP) — API REST
- 🔴 **Front-end** : Angular (TypeScript) — SPA, servie en production via Nginx
- 🟢 **Base de données** : MySQL, via Doctrine ORM
- 🔐 **Authentification** : JWT (LexikJWTAuthenticationBundle)
- 📖 **Données mangas** : API Tenrai (MyAnimeList)
- 🌍 **Traduction** : API DeepL (traduction des synopsis)
- 🐳 **Déploiement** : Docker
- ⚙️ **CI** : GitHub Actions (tests backend, build frontend, analyse statique CodeQL + Semgrep)

## 🚀 Installation et lancement

### Prérequis

- Docker Desktop installé et lancé
- Une clé API [DeepL](https://www.deepl.com/pro-api) (gratuite) pour la traduction des synopsis

### Étapes

**1. Cloner le projet**

```bash
git clone https://github.com/raniazerr/Projet-FilRouge.git
cd Projet-FilRouge/backend
```

**2. Configurer les variables d'environnement**

Créer un fichier `.env.local` à côté du `.env` existant :

```
JWT_PASSPHRASE=votre_passphrase_jwt
DEEPL_API_KEY=votre_cle_api_deepl
```

**3. Lancer Docker**

```bash
docker compose up -d
```

Au premier démarrage, les images sont construites automatiquement, les clés JWT sont générées et le schéma de base de données est créé.

**4. Accéder à l'application**

| Service | URL |
|---|---|
| 🌐 Application Angular | http://localhost:4200 |
| 🔧 API Symfony | http://localhost:8000 |

### 🔄 Lancer l'application après le premier build

```bash
docker compose up -d
```

### 🛑 Arrêter l'application

```bash
docker compose down
```

> Pour supprimer les données de la base : `docker compose down -v`

## 🗂️ Structure du projet

```
Projet-FilRouge/
├── .github/
│   └── workflows/
│       └── ci.yml                # Pipeline CI GitHub Actions
├── backend/                      # API Symfony
│   ├── assets/
│   ├── bin/                      # Console Symfony
│   ├── config/                   # Configuration (security, CORS, JWT...)
│   ├── migrations/               # Historique des migrations Doctrine
│   ├── public/                   # Front controller (index.php)
│   ├── src/
│   │   ├── Controller/           # Contrôleurs API REST
│   │   ├── Entity/               # Entités Doctrine
│   │   ├── Repository/           # Repositories
│   │   └── Service/              # Services (API externe, traduction)
│   ├── tests/                    # Tests PHPUnit
│   ├── translations/
│   ├── .env / .env.dev / .env.test
│   ├── docker-compose.yml
│   ├── docker-entrypoint.sh
│   └── Dockerfile
└── frontend/                     # SPA Angular
    ├── public/
    ├── src/app/
    │   ├── guards/                # Guards de navigation (auth, admin)
    │   ├── interceptors/          # Intercepteur JWT
    │   └── services/
    ├── Dockerfile
    └── nginx.conf                # Configuration Nginx (build de production)
```

> ℹ️ Le dossier `migrations/` retrace l'évolution du schéma de base tout au long du développement (utilisé en local avec la base MySQL/phpMyAdmin). Le conteneur Docker applique quant à lui le schéma final directement depuis les entités via `doctrine:schema:update`, plus simple à maintenir pour un environnement de démonstration qui n'a pas besoin de rejouer tout l'historique.

## 🔐 Créer un compte administrateur

1. S'inscrire via l'interface
2. Se connecter à la base de données MySQL Docker :
   ```bash
   docker compose exec db mysql -uroot -proot mangarake
   ```
3. Exécuter la requête suivante en remplaçant l'email :
   ```sql
   UPDATE user SET roles = '["ROLE_ADMIN"]' WHERE email = 'email@exemple.com';
   ```

## ✅ Lancer les tests unitaires

```bash
docker compose exec backend php bin/phpunit
```

## ⚙️ Intégration continue

À chaque push ou pull request sur `main`/`dev`, GitHub Actions exécute quatre jobs :

- **Backend (Symfony)** : installation des dépendances Composer, `composer audit` (scan de vulnérabilités des dépendances), génération des clés JWT, création de la base de test, exécution des tests PHPUnit
- **Frontend (Angular)** : installation des dépendances npm, build Angular (vérification de compilation)
- **SAST — CodeQL** : analyse statique du code JavaScript/TypeScript, résultats publiés dans l'onglet *Security* du dépôt
- **SAST — Semgrep** : analyse statique du code PHP avec un jeu de règles de sécurité générique (`p/security-audit`)

## 📡 Principales routes API

| Méthode | Route | Description | Auth |
|---|---|---|---|
| POST | `/api/register` | Inscription | ❌ |
| POST | `/api/login` | Connexion (retourne JWT) | ❌ |
| GET | `/manga/index` | Liste des mangas (+ top réservés) | ❌ |
| GET | `/manga/{id}` | Détail d'un manga + ses tomes | ❌ |
| GET | `/manga/search?q=` | Rechercher un manga sur l'API externe | 👑 Admin |
| POST | `/manga/new` | Importer un manga depuis l'API externe | 👑 Admin |
| PUT/PATCH | `/manga/{id}/edit` | Modifier un manga | 👑 Admin |
| DELETE | `/manga/{id}` | Supprimer un manga | 👑 Admin |
| GET | `/tome/manga/{id}` | Tomes d'un manga | ❌ |
| GET | `/tome/{id}` | Détail d'un tome | ❌ |
| POST | `/tome` | Créer un tome | 👑 Admin |
| PUT/PATCH | `/tome/{id}` | Modifier un tome | 👑 Admin |
| DELETE | `/tome/{id}` | Supprimer un tome | 👑 Admin |
| GET | `/api/commandes` | Mes commandes | ✅ |
| POST | `/api/commandes/ajouter` | Ajouter un tome au panier | ✅ |
| GET | `/api/commandes/historique` | Historique des commandes | ✅ |
| DELETE | `/api/commandes/reservation/{id}` | Retirer un tome du panier | ✅ |
| DELETE | `/api/commandes/{id}` | Annuler une commande | ✅ |
| PATCH | `/api/commandes/{id}/soumettre` | Soumettre sa commande | ✅ |
| GET | `/api/commandes/admin` | Toutes les commandes | 👑 Admin |
| PATCH | `/api/commandes/admin/{id}/statut` | Confirmer/expirer une commande | 👑 Admin |
| GET | `/user` | Liste des utilisateurs | 👑 Admin |
| POST | `/user/new` | Créer un utilisateur | 👑 Admin |
| DELETE | `/user/me` | Supprimer son propre compte | ✅ |
| GET/PUT/PATCH/DELETE | `/user/{id}` | Détail/édition/suppression d'un utilisateur | 👑 Admin |

## 🛡️ Sécurité

- Authentification JWT stateless, avec protection des routes par rôle (`ROLE_ADMIN`, `IS_AUTHENTICATED_FULLY`)
- Rate limiting sur le login (5 tentatives / 15 minutes)
- Hachage des mots de passe (bcrypt/Argon2 via `auto`)
- Protection CSRF sur les formulaires sensibles
- Protection injection SQL via Doctrine ORM (requêtes paramétrées)
- CORS restreint à l'origine du front (`NelmioCorsBundle`)
- Verrouillage pessimiste en base lors de la réservation d'un tome, pour éviter la sur-réservation en cas d'accès concurrent
- Validation systématique des entrées utilisateur côté contrôleurs
- Audit automatique des dépendances (`composer audit`) et analyse statique du code (CodeQL, Semgrep) à chaque exécution de la CI

## 🔑 Variables d'environnement requises

| Variable | Description |
|---|---|
| `JWT_PASSPHRASE` | Passphrase pour les clés JWT |
| `DEEPL_API_KEY` | Clé API DeepL pour la traduction des synopsis |

## 👤 Auteur

ZERAMDINI Rania - Projet réalisé individuellement dans le cadre du cahier des charges technique CDA - formation IPSSI.
