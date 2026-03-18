# Documentation API — Vide Grenier en Ligne

## Informations générales

| Propriété | Valeur |
|---|---|
| Format des réponses | `application/json` |
| Authentification | Session PHP (cookie `PHPSESSID`) |
| Base URL dev | `http://localhost:8080` |
| Base URL staging | `http://localhost:8081` |
| Base URL prod | `https://votre-domaine.fr` |

Les endpoints préfixés `/api/` retournent du JSON.  
Les autres routes retournent des pages HTML.

---

## Endpoints API

### GET `/api/products`

Retourne la liste de tous les articles, avec les informations de l'auteur.

**Paramètres query**

| Paramètre | Type | Obligatoire | Valeurs acceptées | Défaut |
|---|---|---|---|---|
| `sort` | string | Non | `views`, `date`, `` | `` |

**Exemple de requête**
```
GET /api/products?sort=views
```

**Réponse 200 — Succès**
```json
[
  {
    "id": 3,
    "name": "Jeu Harry Potter",
    "description": "Harry Potter et la coupe de feu. Très bon état",
    "published_date": "2018-05-28",
    "views": 65,
    "picture": "3.jpeg",
    "user_id": 1,
    "username": "John Doe"
  }
]
```

**Réponse 400 — Paramètre invalide**
```json
{
  "error": "Paramètre sort invalide. Valeurs acceptées : views, date."
}
```

**Réponse 500 — Erreur serveur**
```json
{
  "error": "Erreur serveur."
}
```

---

### GET `/api/cities`

Recherche des communes françaises via l'API [adresse.data.gouv.fr](https://adresse.data.gouv.fr/).  
Utilisé pour l'autocomplétion du champ ville lors de l'ajout d'un article.

**Paramètres query**

| Paramètre | Type | Obligatoire | Contrainte |
|---|---|---|---|
| `query` | string | Oui | Minimum 2 caractères |

**Exemple de requête**
```
GET /api/cities?query=Par
```

**Réponse 200 — Succès**
```json
[
  {
    "label": "Paris (75000)",
    "postcode": "75000",
    "city": "Paris"
  },
  {
    "label": "Parly (89240)",
    "postcode": "89240",
    "city": "Parly"
  }
]
```

> Retourne `[]` si la requête fait moins de 2 caractères ou si aucune commune ne correspond.

**Réponse 500 — Erreur serveur**
```json
{
  "error": "Erreur serveur."
}
```

---

## Endpoints HTML (pages)

### Authentification

#### GET `/login`
Affiche le formulaire de connexion.

#### POST `/login`
Authentifie un utilisateur et crée une session.  
Redirige vers `/account` en cas de succès, réaffiche le formulaire avec un message d'erreur sinon.

**Corps de la requête** `application/x-www-form-urlencoded`

| Champ | Type | Obligatoire | Description |
|---|---|---|---|
| `email` | string (email) | Oui | Adresse email du compte |
| `password` | string | Oui | Mot de passe |
| `csrf_token` | string | Oui | Token CSRF de la session courante |
| `submit` | string | Oui | Présence du bouton de soumission |

**Codes de réponse**

| Code | Situation |
|---|---|
| `302 → /account` | Connexion réussie |
| `200` | Formulaire avec message d'erreur |

---

#### GET `/register`
Affiche le formulaire d'inscription.

#### POST `/register`
Crée un compte utilisateur, hache le mot de passe avec **Argon2id**, puis connecte automatiquement.

**Corps de la requête** `application/x-www-form-urlencoded`

| Champ | Type | Obligatoire | Contrainte |
|---|---|---|---|
| `username` | string | Oui | Minimum 2 caractères |
| `email` | string (email) | Oui | Format email valide, non déjà utilisé |
| `password` | string | Oui | Minimum 8 caractères |
| `password-check` | string | Oui | Doit être identique à `password` |
| `csrf_token` | string | Oui | Token CSRF de la session courante |
| `submit` | string | Oui | Présence du bouton de soumission |

**Codes de réponse**

| Code | Situation |
|---|---|
| `302 → /account` | Inscription réussie |
| `200` | Formulaire avec erreurs de validation |

---

#### GET `/logout`
Détruit la session et redirige vers l'accueil.  
**Requiert d'être connecté** (route privée).

| Code | Situation |
|---|---|
| `302 → /` | Déconnexion réussie |
| `302 → /login` | Non connecté |

---

### Articles

#### GET `/account`
Affiche les articles de l'utilisateur connecté.  
**Requiert d'être connecté** (route privée).

---

#### GET `/product`
Affiche le formulaire d'ajout d'article.  
**Requiert d'être connecté** (route privée).

#### POST `/product`
Crée un nouvel article.  
**Requiert d'être connecté** (route privée).

**Corps de la requête** `multipart/form-data`

| Champ | Type | Obligatoire | Contrainte |
|---|---|---|---|
| `name` | string | Oui | Minimum 3 caractères |
| `description` | string | Oui | Non vide |
| `picture` | file | Non | Image de l'article |
| `csrf_token` | string | Oui | Token CSRF de la session courante |
| `submit` | string | Oui | Présence du bouton de soumission |

**Codes de réponse**

| Code | Situation |
|---|---|
| `302 → /product/{id}` | Article créé avec succès |
| `200` | Formulaire avec erreurs de validation |
| `302 → /login` | Non connecté |

---

#### GET `/product/{id}`
Affiche la page d'un article.

**Paramètres de chemin**

| Paramètre | Type | Description |
|---|---|---|
| `id` | integer | Identifiant numérique de l'article |

**Codes de réponse**

| Code | Situation |
|---|---|
| `200` | Page de l'article |
| `404` | Article introuvable |
| `500` | Erreur serveur |

---

## Schémas de données

### Article
```json
{
  "id": 1,
  "name": "Mappemonde à gratter",
  "description": "Carte du monde à gratter. Neuve dans son emballage.",
  "published_date": "2018-05-28",
  "views": 4,
  "picture": "1.jpeg",
  "user_id": 1,
  "username": "John Doe"
}
```

### City
```json
{
  "label": "Paris (75000)",
  "postcode": "75000",
  "city": "Paris"
}
```

### Error
```json
{
  "error": "Message d'erreur."
}
```

---

## Sécurité

### CSRF
Tous les formulaires POST intègrent un champ caché `csrf_token` généré côté serveur et stocké en session.  
Toute requête POST sans token valide est rejetée avec un log `WARNING`.

### Sessions
Les sessions sont configurées avec les options suivantes :

| Option | Valeur |
|---|---|
| `httponly` | `true` — inaccessible depuis JavaScript |
| `samesite` | `Lax` — protège contre le CSRF cross-site |
| `secure` | `true` en staging/prod, `false` en dev |
| `lifetime` | Configurable via `SESSION_LIFETIME` (défaut : 3600s) |

### Mots de passe
Les mots de passe sont hashés avec **Argon2id** via `password_hash()` (PHP natif).  
Aucun salt n'est stocké en base — il est intégré dans le hash Argon2id.  
Un mécanisme de rehash automatique est en place si les paramètres de coût évoluent.