# Déploiement sur Hostinger

## 1. Préparer les fichiers
- Uploader tout le contenu du projet à la racine du site Hostinger (public_html) ou dans un sous-dossier si nécessaire.
- Ne pas uploader le dossier .git.

## 2. Base de données
- Créer une base MySQL sur Hostinger.
- Importer le fichier [database/tcf.sql](database/tcf.sql).
- Copier [includes/config.local.php.example](includes/config.local.php.example) vers [includes/config.local.php](includes/config.local.php) et renseigner les identifiants.

## 3. Configuration PHP
- Utiliser PHP 8.1 ou plus récent.
- Vérifier que les dossiers d’upload sont accessibles en écriture.

## 4. Paiement / callbacks
- Définir l’URL publique de votre site dans la configuration de paiement si nécessaire.
- Si votre site est en HTTPS, l’URL de callback doit être HTTPS.

## 5. Vérification rapide
- Ouvrir votre domaine pour vérifier l’accueil.
- Tester l’admin et la connexion.
