-- =============================================================================
-- Réparation IDs / doublons — ELITE TCF CANADA
-- À exécuter dans phpMyAdmin (onglet SQL) sur la base de production.
-- Sauvegardez d'abord la base (Export).
-- =============================================================================

SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_AUTO_VALUE_ON_ZERO', '');

-- 1) Supprimer les lignes fantômes id = 0 (cause classique des "mêmes IDs")
DELETE FROM activities WHERE id = 0;
DELETE FROM analytics WHERE id = 0;
DELETE FROM notifications WHERE id = 0;
DELETE FROM payments WHERE id = 0;
DELETE FROM playlist_videos WHERE id = 0;
DELETE FROM channel_playlists WHERE id = 0;
DELETE FROM channel_posts WHERE id = 0;
DELETE FROM channel_post_comments WHERE id = 0;
DELETE FROM chat_messages WHERE id = 0;
DELETE FROM community_messages WHERE id = 0;
DELETE FROM videos WHERE id = 0;
DELETE FROM users WHERE id = 0;

-- 2) Restaurer AUTO_INCREMENT (tables critiques)
ALTER TABLE users MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE videos MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE video_comments MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE notifications MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE activities MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE analytics MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE payments MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE playlist_videos MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE channel_playlists MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE channel_posts MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE channel_post_comments MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE chat_messages MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE community_messages MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

-- Tables examens / chat (si présentes)
ALTER TABLE site_visit_logs MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
-- PK site_visit_logs si absente :
-- ALTER TABLE site_visit_logs ADD PRIMARY KEY (id);

ALTER TABLE subscription_payments MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE subscription_plan_catalog MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_ce_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_co_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_ee_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_eo_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_ce_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_co_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_ee_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_eo_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_chat_messages MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_chat_thread_members MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_chat_threads MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_eo_exams MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_eo_parts MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE tcf_eo_subjects MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE topics MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE trainers MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE user_email_codes MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

-- 3) Recaler les compteurs AUTO_INCREMENT
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE videos AUTO_INCREMENT = 1;
-- (MySQL ajustera au MAX(id)+1 automatiquement au prochain INSERT)

-- 4) Fusionner les doublons email (garde le plus petit id ; à adapter si besoin)
-- Exemple pour un email précis (décommentez et remplacez) :
-- UPDATE video_likes SET user_id = 12 WHERE user_id = 45;
-- UPDATE video_comments SET user_id = 12 WHERE user_id = 45;
-- UPDATE notifications SET user_id = 12 WHERE user_id = 45;
-- DELETE FROM users WHERE id = 45;

-- 5) Empêcher les futurs doublons email
-- (échoue s'il reste des doublons — nettoyez-les d'abord ou utilisez repair_database.php)
ALTER TABLE users ADD UNIQUE KEY uq_users_email (email);

-- 6) Vidéos : visibilité invalide → public
UPDATE videos
SET visibility = 'public'
WHERE visibility IS NULL
   OR TRIM(visibility) = ''
   OR visibility NOT IN ('public', 'private', 'premium');

-- 7) Vérifications
SELECT 'users' AS t, COUNT(*) AS n, MAX(id) AS max_id FROM users
UNION ALL
SELECT 'videos', COUNT(*), MAX(id) FROM videos
UNION ALL
SELECT 'videos_visibles', COUNT(*), MAX(id) FROM videos WHERE visibility IN ('public','premium');

SELECT email, COUNT(*) AS c FROM users GROUP BY email HAVING c > 1;
SHOW COLUMNS FROM users LIKE 'id';
SHOW COLUMNS FROM videos LIKE 'id';
