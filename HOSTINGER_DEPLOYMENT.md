# Déploiement Hostinger & travail en local

## Travail en local (XAMPP) sans casser la prod

1. Démarrez **Apache** et **MySQL** dans le panneau XAMPP.
2. Créez la base `TCF` dans phpMyAdmin (`http://localhost/phpmyadmin`).
3. Importez `database/tcf.sql` (**structure seule** — pas les sujets EE/EO).
4. Importez les sujets Expression écrite / orale (design mois, comme avant) :
   - **Recommandé** : `php scripts/restore_epreuve_ee_eo.php`  
     (lit `database/epreuve.sql` s’il est présent, sinon `database/seeds_ee_eo_data.sql`)
   - **phpMyAdmin** : importer `database/seeds_ee_eo_data.sql` après `tcf.sql`
   - **Navigateur** : `…/scripts/restore_epreuve_ee_eo.php?key=REPAIR_TCF_2026`

> Ne pas réutiliser `import_exp_ecrite_to_db.php` seul : il crée des cartes « Part1 / Data ».

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

## Déploiement / mise à jour production (push → Hostinger)

### Nom du commit (juillet)
`MET première mise à jour cuisson juillet`

### Guide push (local → GitHub → Hostinger)

1. Ouvrez PowerShell dans le dossier du projet :
   `cd C:\xampp\htdocs\elite-TCFCanada`
2. Vérifiez l’état :
   `git status`
3. Ajoutez les fichiers :
   `git add -A`
4. Créez le commit :
   `git commit -m "MET première mise à jour cuisson juillet"`
5. Poussez vers GitHub (Hostinger se met à jour si le dépôt est relié) :
   `git push origin main`
6. Attendez 1–2 minutes, puis rechargez le site en **Ctrl+F5**.

### Après le push — à faire une fois sur Hostinger

| Action | Détail |
|--------|--------|
| Clé Gemini | Créer `includes/gemini_key.php` avec `return 'VOTRE_CLE';` (non versionné) |
| Webhook paiement | Dashboard Notch Pay → `https://elitetcfcanada.online/payment_webhook.php` |
| Base chat | Les tables messagerie sont déjà à supprimer en prod si encore présentes |
| Cache | Vider le cache Hostinger / navigateur si l’ancien JS s’affiche |

### Important

- Ne committez **jamais** `includes/config.local.php` ni `includes/gemini_key.php`.
- Les `uploads/` (vidéos, images) ne partent en général **pas** avec Git.
- Un `git push` met à jour le code en ligne si le dépôt est relié à Hostinger.

## Travail en local (XAMPP) sans casser la prod


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
