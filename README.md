# ProStay Africa

ERP hotelier modulaire construit avec Laravel, Livewire et Breeze (authentification).

## Stack technique

- PHP 8.4+
- Laravel 13
- Livewire 3 + Volt
- Breeze (auth Livewire)
- Vite + Tailwind CSS
- SQLite (par defaut), compatible MySQL/PostgreSQL

## Fonctionnalites implementees (MVP)

- Authentification complete: login, register, reset password, verification email, profil
- Base ERP hoteliere:
	- gestion clients
	- commandes
	- facturation
	- paiements
	- POS quick sale
- Structure metier:
	- enums de domaine
	- services applicatifs (orders, billing, payment, pos)
	- migrations et seeders
	- composants Livewire par module

## Installation locale

1. Installer les dependances PHP:

```bash
composer install
```

2. Initialiser l'environnement:

```bash
cp .env.example .env
php artisan key:generate
```

3. Migrer et seeder la base:

```bash
php artisan migrate:fresh --seed
```

4. Installer les dependances front:

```bash
npm install
```

5. Lancer l'application:

```bash
php artisan serve
npm run dev
```

## Build production

```bash
npm run build
```

Les assets sont generes dans `public/build`.

## Routes principales

- `/` -> redirige vers `login` (invite) ou `dashboard` (auth)
- `/dashboard`
- `/customers`
- `/orders`
- `/billing/invoices`
- `/pos`
- `/profile`

## Utilisateur de test (seeder)

- Email: `test@example.com`
- Mot de passe: `password`

## Commandes utiles

```bash
# tests
php artisan test

# routes
php artisan route:list

# nettoyage cache
php artisan optimize:clear
```

## Depannage assets (Vite/Tailwind)

Si le style ne se charge pas:

1. Verifier qu'il n'y a pas de fichier `public/hot` residuel si vous n'utilisez pas `npm run dev`.
2. Regenerer les assets:

```bash
rm -f public/hot
npm run build
```

3. En dev, laisser `npm run dev` actif pendant la navigation.

## Documentation complementaire

- Roadmap MVP: `docs/mvp-erp-roadmap.md`

## Licence

Projet distribue sous licence MIT.
