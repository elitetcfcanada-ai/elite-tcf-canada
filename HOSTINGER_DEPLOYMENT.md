# Déploiement Hostinger & travail en local

## Travail en local (XAMPP) sans casser la prod

1. Démarrez **Apache** et **MySQL** dans le panneau XAMPP.
2. Créez la base `TCF` dans phpMyAdmin (`http://localhost/phpmyadmin`).
3. Importez `database/tcf.sql` (**structure seule** — pas les sujets EE/EO).
4. Importez les sujets Expression écrite / orale (au choix) :
   - **phpMyAdmin** : importer aussi `database/seeds_ee_eo_data.sql`
   - **ou CLI** : `php scripts/seed_all_ee_eo.php`
   - **ou navigateur** : `…/scripts/seed_all_ee_eo.php?key=REPAIR_TCF_2026`

5. Vérifiez que `includes/config.local.php` existe (non versionné) avec :

```php
$host = 'localhost';
$dbname = 'TCF';
$username = 'root';
$password = '';
$port = '';
```

6. Ouvrez `http://localhost/elite-TCFCanada/` (HTTP, pas HTTPS).
7. Développez et testez **en local**. Ne poussez (`git push`) que lorsque c’est validé.

> `database/tcf.sql` ne contient pas les sujets. Sans l’étape `seed_all_ee_eo.php`, Expression écrite / orale restent vides.

> `config.local.php` a priorité sur `config.hostinger.php`. Ainsi le local ne pointe plus vers la prod.

## Déploiement / mise à jour production

- Un `git push` met à jour le code en ligne si le dépôt est relié à Hostinger.
- Les fichiers uploadés (`uploads/`) ne sont en général **pas** dans Git : republiez les vidéos depuis l’admin si besoin.
- Ne committez jamais `includes/config.local.php` (déjà dans `.gitignore`).

## Réparer les IDs / doublons (production)

Si les inscriptions créent des comptes avec le même id, ou si les vidéos n’apparaissent pas :

1. **Sauvegardez** la base (Export phpMyAdmin).
2. Ouvrez une fois :
   `https://VOTRE-DOMAINE/scripts/repair_database.php?key=REPAIR_TCF_2026`
3. Ou exécutez `database/fix_ids_and_duplicates.sql` dans phpMyAdmin.
4. **Supprimez** `scripts/repair_database.php` après usage.
5. Testez inscription + page Vidéos (visibilité `public` ou `premium`).

## Checklist Hostinger

- PHP 8.1+
- Dossiers `uploads/` en écriture
- Callbacks paiement en HTTPS
- Importer / réparer le schéma si la base est ancienne
