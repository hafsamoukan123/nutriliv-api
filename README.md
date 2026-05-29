# 🥗 NutriLiv — Plateforme de Livraison de Repas Sains

> Projet de fin d'études — Plateforme complète de commande et livraison de repas équilibrés

![NutriLiv](https://img.shields.io/badge/Laravel-12-red) ![React](https://img.shields.io/badge/React-18-blue) ![License](https://img.shields.io/badge/license-MIT-green)

## 📋 Description

NutriLiv est une plateforme web complète permettant la commande et la livraison de repas sains. Elle connecte 4 types d'utilisateurs : clients, restaurants/vendeurs, livreurs et administrateurs.

## ✨ Fonctionnalités

- 🔐 Authentification avec rôles (Client, Vendeur, Livreur, Admin)
- 🛒 Panier et commandes en ligne
- 🚴 Suivi GPS en temps réel du livreur
- 💰 Paiement à la livraison (COD)
- 📊 Dashboard analytics pour vendeurs et admins
- 🔔 Système de notifications en temps réel
- 💸 Gestion des virements bancaires

## 🛠️ Technologies

| Backend | Frontend |
|---------|----------|
| Laravel 12 | React 18 + Vite |
| MySQL | TanStack Query |
| Laravel Sanctum | Zustand |
| RESTful API | Tailwind CSS |

## 🚀 Installation

### Backend (Laravel)
```bash
git clone https://github.com/username/nutriliv-api
cd nutriliv-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

### Frontend (React)
```bash
git clone https://github.com/username/nutriliv-front
cd nutriliv-front
npm install
npm run dev
```

## 👥 Comptes de test

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Admin | admin@test.com | password |
| Vendeur | vendeur@test.com | password |
| Client | client@test.com | password |
| Livreur | livreur@test.com | password |

## 📱 Captures d'écran
_(à ajouter après déploiement)_

## 👨‍💻 Auteur
Hafsa Moukan — Projet de fin d'études 2026
