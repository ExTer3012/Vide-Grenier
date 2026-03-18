# Vide Grenier en Ligne

Application web de dépôt et consultation d'annonces gratuites, développée en PHP (architecture MVC maison) avec Twig, PDO et MySQL. Conteneurisée avec Docker.

---

## Prérequis

- [Docker](https://www.docker.com/) >= 24
- [Docker Compose](https://docs.docker.com/compose/) >= 2
- Git

---

## Environnements disponibles

| Environnement | Fichier compose | Port app | Port DB | Image Docker |
|---|---|---|---|---|
| **dev** | `docker-compose.dev.yml` | `8080` | `3307` | Non (volume monté) |
| **staging** | `docker-compose.staging.yml` | `8081` | — | `vgl_app:staging` |
| **prod** | `docker-compose.prod.yml` | `80` | — | `vgl_app:latest` |

En **dev**, le code source est monté en volume : toute modification est immédiatement visible sans rebuild.
En **staging** et **prod**, une image Docker est buildée et taguée.

---

## Installation et démarrage

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd vide-grenier-en-ligne
```

### 2. Configurer les variables d'environnement

```bash
# Copier le template et adapter les valeurs
cp .env.example .env
```

Les variables disponibles sont décrites dans `.env.example`. Les fichiers `.env.dev`, `.env.staging` et `.env.prod` ne sont **jamais commités**.

### 3. Démarrer un environnement

**Développement**
```bash
docker compose -f docker-compose.dev.yml up --build
# Application disponible sur http://localhost:8080
```

**Staging**
```bash
docker compose -f docker-compose.staging.yml up --build
# Application disponible sur http://localhost:8081
```

**Production**
```bash
docker compose -f docker-compose.prod.yml up --build -d
# Application disponible sur http://localhost:80
```

### 4. Arrêter et supprimer un environnement

```bash
# Arrêter sans supprimer les volumes (données conservées)
docker compose -f docker-compose.dev.yml down

# Arrêter ET supprimer les volumes (repart de zéro)
docker compose -f docker-compose.dev.yml down -v
```

---

## Base de données

Les scripts SQL sont dans le dossier `sql/` et sont **chargés automatiquement** au démarrage du conteneur MySQL :

| Fichier | Contenu |
|---|---|
| `sql/schema.sql` | Structure des tables (idempotent — `IF NOT EXISTS`) |
| `sql/data.sql` | Données initiales (idempotent — `ON DUPLICATE KEY UPDATE`) |

Les deux scripts peuvent être exécutés plusieurs fois sans erreur et sans dupliquer les données.

---

## Lancer les tests

```bash
# Dans le conteneur app en dev
docker compose -f docker-compose.dev.yml exec app composer test

# Ou localement si PHP et Composer sont installés
composer install
composer test
```

---

## Structure du projet

```
├── App/
│   ├── Controllers/     # Contrôleurs (User, Product, Api, Home)
│   ├── Models/          # Modèles PDO (Articles, User, Cities)
│   ├── Utility/         # Hash (Argon2id), Csrf
│   ├── Views/           # Templates Twig
│   └── Config.php       # Configuration (lit les variables d'env)
├── Core/                # Router, Controller, Model, View, Error
├── bootstrap/
│   └── env.php          # Chargeur de variables d'environnement
├── docs/
│   └── api.md           # Documentation API
├── public/              # Document root Apache (index.php, assets)
├── sql/
│   ├── schema.sql       # Structure BDD
│   └── data.sql         # Données initiales
├── tests/
│   └── Unit/            # Tests PHPUnit
├── docker-compose.dev.yml
├── docker-compose.staging.yml
├── docker-compose.prod.yml
├── Dockerfile
├── .env.example
└── phpunit.xml
```

---

## Architecture technique

**Routing** — Le [Router](Core/Router.php) traduit les URLs via la méthode `add` :

```php
$router->add('product/{id:\d+}', ['controller' => 'Product', 'action' => 'show']);
```

**Vues** — Rendues avec [Twig](https://twig.symfony.com/), dans `App/Views/` :

```php
View::renderTemplate('Home/index.html', ['articles' => $articles]);
```

**Modèles** — Héritent de `Core\Model`, utilisent PDO :

```php
$db = static::getDB();
```

**Sécurité** — Mots de passe hashés avec Argon2id, protection CSRF sur tous les formulaires POST, sessions `httponly/samesite`.

**API villes** — L'autocomplétion utilise l'API publique [adresse.data.gouv.fr](https://adresse.data.gouv.fr/) (plus de table `villes_france` en base).

---

## Documentation API

Voir [`docs/api.md`](docs/api.md) pour le détail de tous les endpoints.

---

## Compilation des assets (optionnel sans Docker)

```bash
npm install
npm run watch   # Compile les fichiers SCSS en continu
```