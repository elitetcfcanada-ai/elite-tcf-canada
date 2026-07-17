# Guide de Dépannage Hostinger - Elite TCF Canada

## Problèmes Rencontrés sur Hostinger

### 1. Problème d'Inscription
**Symptômes:** Impossible de finaliser l'inscription, erreur lors de la création de compte.

**Causes possibles:**
- **Base de données non importée:** Les tables nécessaires n'existent pas
- **Permissions incorrectes:** Le script ne peut pas écrire dans la base
- **Configuration PHP:** Extensions manquantes (pdo_mysql, mbstring)
- **Sessions PHP:** Le dossier de sessions n'est pas accessible en écriture

**Solutions:**
1. Importez `database/tcf.sql` via phpMyAdmin sur Hostinger
2. Vérifiez que les identifiants dans `includes/config.hostinger.php` sont corrects
3. Activez les extensions PHP requises dans le panneau Hostinger
4. Vérifiez les permissions du dossier de sessions PHP

### 2. Problème de Connexion
**Symptômes:** Connexion impossible, session non maintenue, redirection incorrecte.

**Causes possibles:**
- **Sessions PHP mal configurées:** Cookie params incorrects
- **HTTPS/HTTP mixte:** Problème de sécurité des cookies
- **Base de données déconnectée:** Mauvais identifiants après import
- **Chemin DOCUMENT_ROOT incorrect:** Calcul des URLs faux

**Solutions:**
1. Configurez SSL/HTTPS sur Hostinger (obligatoire pour les sessions sécurisées)
2. Vérifiez que `session.save_path` est accessible en écriture
3. Testez la connexion base de données via le script diagnostic
4. Vérifiez que DOCUMENT_ROOT pointe vers le bon dossier

### 3. Problème d'Affichage des Vidéos
**Symptômes:** Vidéos non affichées, thumbnails manquants, erreurs de chargement.

**Causes possibles:**
- **Permissions dossiers uploads:** Dossiers non accessibles en lecture/écriture
- **Chemins fichiers incorrects:** URLs mal calculées pour Hostinger
- **Fichiers manquants:** Vidéos/thumbnails non uploadés sur le serveur
- **Base de données vide:** Aucune vidéo dans la table videos
- **Configuration chemins:** Fonctions site_href/site_url mal adaptées

**Solutions:**
1. **Permissions critiques:**
   - Configurez tous les dossiers `uploads/` et sous-dossiers à 755 ou 777
   - Vérifiez via FTP/File Manager que les permissions sont correctes

2. **Upload des fichiers médias:**
   - Uploadez manuellement les fichiers vidéos et thumbnails dans les dossiers appropriés
   - Vérifiez que les chemins dans la base de données correspondent aux fichiers réels

3. **Configuration des chemins:**
   - Le script `hostinger_debug.php` vérifiera les calculs de chemins
   - Ajustez `DOCUMENT_ROOT` si nécessaire dans la configuration Hostinger

4. **Base de données:**
   - Vérifiez que la table `videos` contient des enregistrements
   - Insérez des vidéos de test si nécessaire

## Étapes de Dépannage Complètes

### Étape 1: Diagnostic Initial
1. Uploadez `hostinger_debug.php` sur votre serveur Hostinger
2. Accédez à `https://votre-domaine.com/hostinger_debug.php`
3. Analysez les résultats et corrigez les erreurs identifiées
4. Supprimez le fichier après diagnostic (sécurité)

### Étape 2: Configuration Base de Données
1. Créez la base de données `u648716817_tcf_canada` sur Hostinger
2. Importez `database/tcf.sql` via phpMyAdmin
3. Vérifiez que toutes les tables sont créées (51 tables)
4. Testez la connexion avec les identifiants fournis

### Étape 3: Permissions Fichiers
Via FTP ou File Manager Hostinger:
```bash
chmod 755 uploads/
chmod 755 uploads/avatars/
chmod 755 uploads/channel/
chmod 755 uploads/channel_posts/
chmod 755 uploads/co_media/
chmod 755 uploads/trainers/
```

Si les permissions 755 ne fonctionnent pas, essayez 777 (moins sécurisé mais parfois nécessaire):

### Étape 4: Configuration PHP
Dans le panneau Hostinger:
1. Activez les extensions: pdo_mysql, mbstring, json, curl, gd, session
2. Configurez php.ini avec les valeurs de `php.ini` du projet
3. Vérifiez que upload_max_filesize >= 64M
4. Vérifiez que post_max_size >= 64M

### Étape 5: Upload des Médias
1. Uploadez les fichiers vidéos dans `uploads/` ou sous-dossiers appropriés
2. Uploadez les thumbnails dans `uploads/` 
3. Mettez à jour la base de données avec les chemins corrects si nécessaire
4. Vérifiez que les fichiers sont accessibles via URL

### Étape 6: Test Final
1. Testez l'inscription avec un compte de test
2. Testez la connexion avec le compte créé
3. Testez l'affichage des vidéos sur la page videos.php
4. Vérifiez que les thumbnails s'affichent correctement

## Différences Local vs Hostinger

### Environnement Local (XAMPP)
- **DOCUMENT_ROOT:** Pointe vers `htdocs/`
- **Permissions:** Automatiques, pas de restrictions
- **Sessions:** Fonctionnent immédiatement
- **Chemins:** Calculs basés sur structure locale

### Environnement Hostinger
- **DOCUMENT_ROOT:** Peut pointer vers `public_html/` ou autre
- **Permissions:** Doivent être configurées manuellement
- **Sessions:** Configuration spécifique requise
- **Chemins:** Calculs doivent s'adapter à la structure Hostinger

## Scripts Utilitaires

### hostinger_debug.php
Script de diagnostic complet qui teste:
- Connexion base de données
- Configuration sessions PHP
- Permissions dossiers uploads
- Configuration chemins et URLs
- Configuration PHP
- Données vidéos
- Simulation inscription

### hostinger_diagnostic.php
Script de diagnostic simplifié pour vérifications rapides.

## Support Technique

Si les problèmes persistent après ces étapes:
1. Exécutez `hostinger_debug.php` et partagez les résultats
2. Vérifiez les logs d'erreur Hostinger
3. Contactez le support Hostinger pour les problèmes serveur
4. Vérifiez que votre plan Hostinger supporte les fonctionnalités requises

## Notes Importantes

- **Sécurité:** Supprimez toujours les fichiers de diagnostic après utilisation
- **Backups:** Faites des backups avant de modifier la base de données
- **SSL:** HTTPS est fortement recommandé pour les sessions sécurisées
- **Performance:** Les fichiers vidéos volumineux peuvent nécessiter une configuration spécifique
