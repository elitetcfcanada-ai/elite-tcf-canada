-- =============================================================================
-- Réparation IDs / doublons — ELITE TCF CANADA
-- Sauvegardez d'abord la base (Export).
--
-- Si une ligne échoue : continuez avec le reste, ou utilisez plutôt
-- scripts/repair_database.php?key=REPAIR_TCF_2026 (plus sûr).
-- =============================================================================

SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_AUTO_VALUE_ON_ZERO', '');

-- 1) Supprimer les lignes fantômes id = 0
DELETE FROM activities WHERE id = 0;
DELETE FROM analytics WHERE id = 0;
DELETE FROM notifications WHERE id = 0;
DELETE FROM payments WHERE id = 0;
DELETE FROM playlist_videos WHERE id = 0;
DELETE FROM channel_playlists WHERE id = 0;
DELETE FROM channel_posts WHERE id = 0;
DELETE FROM channel_post_comments WHERE id = 0;
DELETE FROM community_messages WHERE id = 0;
DELETE FROM videos WHERE id = 0;
DELETE FROM users WHERE id = 0;

-- 2) Tables critiques : PRIMARY KEY puis AUTO_INCREMENT
-- (MySQL exige une clé sur la colonne AUTO_INCREMENT — erreur #1075 sinon)

ALTER TABLE users MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE videos MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE video_comments MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

-- notifications / activities / etc. : ajouter PK si absente, puis AI
-- Si "Duplicate entry" : ignorez cette table et passez à la suivante.

ALTER TABLE notifications ADD PRIMARY KEY (id);
ALTER TABLE notifications MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE activities ADD PRIMARY KEY (id);
ALTER TABLE activities MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE analytics ADD PRIMARY KEY (id);
ALTER TABLE analytics MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE payments ADD PRIMARY KEY (id);
ALTER TABLE payments MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE playlist_videos ADD PRIMARY KEY (id);
ALTER TABLE playlist_videos MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE channel_playlists ADD PRIMARY KEY (id);
ALTER TABLE channel_playlists MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE channel_posts ADD PRIMARY KEY (id);
ALTER TABLE channel_posts MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE channel_post_comments ADD PRIMARY KEY (id);
ALTER TABLE channel_post_comments MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE community_messages ADD PRIMARY KEY (id);
ALTER TABLE community_messages MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

-- site_visit_logs : PK obligatoire AVANT AUTO_INCREMENT
-- Si doublons d'id → échoue : utilisez repair_database.php à la place.
ALTER TABLE site_visit_logs ADD PRIMARY KEY (id);
ALTER TABLE site_visit_logs MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE subscription_payments ADD PRIMARY KEY (id);
ALTER TABLE subscription_payments MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE subscription_plan_catalog ADD PRIMARY KEY (id);
ALTER TABLE subscription_plan_catalog MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE topics ADD PRIMARY KEY (id);
ALTER TABLE topics MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE trainers ADD PRIMARY KEY (id);
ALTER TABLE trainers MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE user_email_codes ADD PRIMARY KEY (id);
ALTER TABLE user_email_codes MODIFY id INT(11) NOT NULL AUTO_INCREMENT;

-- Examens (même règle : PK puis AI)
ALTER TABLE tcf_ce_consignes ADD PRIMARY KEY (id);
ALTER TABLE tcf_ce_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_co_consignes ADD PRIMARY KEY (id);
ALTER TABLE tcf_co_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_ee_consignes ADD PRIMARY KEY (id);
ALTER TABLE tcf_ee_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_eo_consignes ADD PRIMARY KEY (id);
ALTER TABLE tcf_eo_consignes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_ce_exam_views ADD PRIMARY KEY (id);
ALTER TABLE tcf_ce_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_co_exam_views ADD PRIMARY KEY (id);
ALTER TABLE tcf_co_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_ee_exam_views ADD PRIMARY KEY (id);
ALTER TABLE tcf_ee_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_eo_exam_views ADD PRIMARY KEY (id);
ALTER TABLE tcf_eo_exam_views MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_eo_exams ADD PRIMARY KEY (id);
ALTER TABLE tcf_eo_exams MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_eo_parts ADD PRIMARY KEY (id);
ALTER TABLE tcf_eo_parts MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tcf_eo_subjects ADD PRIMARY KEY (id);
ALTER TABLE tcf_eo_subjects MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- 3) UNIQUE email
ALTER TABLE users ADD UNIQUE KEY uq_users_email (email);

-- 4) Vidéos : visibilité invalide → public
UPDATE videos
SET visibility = 'public'
WHERE visibility IS NULL
   OR TRIM(visibility) = ''
   OR visibility NOT IN ('public', 'private', 'premium');

-- 5) Vérifications
SELECT 'users' AS t, COUNT(*) AS n, MAX(id) AS max_id FROM users
UNION ALL
SELECT 'videos', COUNT(*), MAX(id) FROM videos
UNION ALL
SELECT 'tcf_ee_exams', COUNT(*), MAX(id) FROM tcf_ee_exams
UNION ALL
SELECT 'tcf_eo_exams', COUNT(*), MAX(id) FROM tcf_eo_exams;

SHOW COLUMNS FROM users LIKE 'id';
SHOW COLUMNS FROM site_visit_logs LIKE 'id';
