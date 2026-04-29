# ☕ Yaya's Caffee — Application Web Full Stack

Une application web complète pour un café, développée avec 
PHP, MySQL et JavaScript.

![PHP](https://img.shields.io/badge/PHP-8.0-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-yellow)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📸 Screenshots

> (ajoute des screenshots ici après)

---

## ✨ Fonctionnalités

### Côté Client
- 🏠 Page d'accueil avec best sellers dynamiques
- 📋 Menu complet avec recherche en temps réel
- ☕ Builder de café personnalisé avec calcul de prix dynamique
- 🛒 Panier persistant (localStorage)
- 📦 Système de commandes complet
- 📬 Formulaire de contact
- 👤 Authentification (inscription/connexion/déconnexion)

### Côté Admin
- 📊 Dashboard avec statistiques en temps réel
- 🍽️ CRUD complet du menu avec upload d'images
- 📦 Gestion des commandes avec suivi de statut
- 👥 Gestion des utilisateurs
- 💬 Gestion des messages de contact

---

## 🔒 Sécurité

- Hashage des mots de passe avec **BCrypt**
- Protection contre les **injections SQL** (PDO + requêtes préparées)
- Protection contre les attaques **XSS** (htmlspecialchars)
- **Session fixation** prevention (session_regenerate_id)
- Validation double couche **JavaScript + PHP**
- Protection des routes admin (**requireAdmin()**)

---

## 🛠️ Technologies utilisées

| Technologie | Usage |
|---|---|
| PHP 8 | Backend, logique serveur |
| MySQL | Base de données |
| PDO | Connexion DB sécurisée |
| JavaScript ES6 | Frontend, interactions |
| HTML5 / CSS3 | Structure et design |
| Font Awesome | Icônes |
| Google Fonts | Typographie |
| XAMPP | Serveur local |

---

## 🗄️ Structure de la base de données
users         → comptes utilisateurs + rôles
menu_items    → produits du menu
orders        → commandes clients
contacts      → messages de contact
custom_orders → commandes café personnalisé
---

## 📁 Structure du projet
projet web/
├── index.php              ← Page d'accueil
├── menu.php               ← Menu complet
├── cart.php               ← Panier + commande
├── contact.php            ← Contact
├── customize.html         ← Builder café
├── signin.html            ← Connexion
├── signup.html            ← Inscription
├── dream.html             ← Notre histoire
├── style.css              ← CSS principal
│
├── includes/
│   ├── db.php             ← Connexion PDO
│   └── auth.php           ← Sessions + sécurité
│
├── api/
│   ├── signin.php         ← API connexion
│   ├── signup.php         ← API inscription
│   ├── signout.php        ← Déconnexion
│   ├── contact.php        ← API contact
│   ├── order_create.php   ← API commandes
│   └── customize.php      ← API café custom
│
├── admin/
│   ├── dashboard.php      ← Panel admin
│   └── actions/
│       ├── menu_add.php
│       ├── menu_edit.php
│       ├── menu_delete.php
│       ├── order_status.php
│       └── contact_status.php
│
├── auth/
│   └── check_session.php  ← Vérif session JSON
│
├── js/
│   └── customize.js       ← Logique builder
│
└── images/
└── (images du site)