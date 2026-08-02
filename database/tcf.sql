-- =============================================================================
-- ELITE TCF CANADA — schéma consolidé (une seule source de vérité)
-- Fichier unique : database/tcf.sql
-- =============================================================================
-- Tables :
--   users, notifications, annonces, statistiques, videos,
--   abonnements, historique_abonnements, partenaires, temoignages,
--   activites, sujets, comprehension_ecrite, comprehension_orale,
--   expression_ecrite, expression_orale, visiteurs, parametres
-- =============================================================================

SET NAMES utf8mb4;
SET SQL_MODE = '';
SET time_zone = '+00:00';

-- -----------------------------------------------------------------------------
-- users : comptes + rôles + abonnement courant + OTP / remember
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user',
  `subscription_type` ENUM('free','monthly','annual','plan_1w','plan_2w','plan_1m','plan_2m') NOT NULL DEFAULT 'free',
  `subscription_expires_at` DATETIME DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `permissions` TEXT DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `avatar_data` MEDIUMBLOB DEFAULT NULL,
  `avatar_mime` VARCHAR(80) DEFAULT NULL,
  `otp_code` VARCHAR(12) DEFAULT NULL,
  `otp_purpose` VARCHAR(40) DEFAULT NULL,
  `otp_expires_at` DATETIME DEFAULT NULL,
  `remember_token` VARCHAR(128) DEFAULT NULL,
  `remember_expires_at` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `last_activity` DATETIME DEFAULT NULL,
  `reg_country_code` VARCHAR(2) DEFAULT NULL,
  `reg_country_name` VARCHAR(120) DEFAULT NULL,
  `reg_traffic_source` VARCHAR(32) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_sub` (`subscription_type`, `subscription_expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- notifications
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(40) NOT NULL DEFAULT 'update',
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `deep_link` VARCHAR(500) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- annonces (posts communauté + messages diffusés)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `annonces` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` ENUM('post','message') NOT NULL DEFAULT 'post',
  `body` TEXT NOT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `image_data` MEDIUMBLOB DEFAULT NULL,
  `image_mime` VARCHAR(80) DEFAULT NULL,
  `link_url` VARCHAR(1000) DEFAULT NULL,
  `visibility` ENUM('visitors','registered','premium','gratuit') NOT NULL DEFAULT 'registered',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `likes_json` LONGTEXT DEFAULT NULL,
  `views_json` LONGTEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_annonces_pub` (`is_published`, `kind`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- statistiques (événements analytics / métriques)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `statistiques` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` VARCHAR(40) NOT NULL DEFAULT 'event',
  `ref_type` VARCHAR(40) DEFAULT NULL,
  `ref_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(40) DEFAULT NULL,
  `value_num` INT DEFAULT NULL,
  `meta_json` LONGTEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stats_kind` (`kind`, `created_at`),
  KEY `idx_stats_ref` (`ref_type`, `ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- videos (métadonnées + binaire vidéo/miniature en base)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `videos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `thumbnail_mime` VARCHAR(80) DEFAULT NULL,
  `video_url` VARCHAR(500) DEFAULT NULL,
  `video_data` LONGBLOB DEFAULT NULL,
  `video_mime` VARCHAR(80) DEFAULT NULL,
  `visibility` ENUM('public','private','premium') NOT NULL DEFAULT 'public',
  `duration` VARCHAR(32) DEFAULT NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `likes` INT UNSIGNED NOT NULL DEFAULT 0,
  `likes_json` LONGTEXT DEFAULT NULL,
  `comments_json` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_videos_vis` (`visibility`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- abonnements (catalogue des plans)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `abonnements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_key` VARCHAR(40) NOT NULL,
  `tier` VARCHAR(40) NOT NULL DEFAULT '',
  `badge` VARCHAR(120) NOT NULL DEFAULT '',
  `title` VARCHAR(160) NOT NULL DEFAULT '',
  `price_label` VARCHAR(80) NOT NULL DEFAULT '',
  `price_xaf` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `duration_days` INT UNSIGNED NOT NULL DEFAULT 30,
  `features_json` LONGTEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_abo_plan_key` (`plan_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- historique_abonnements (paiements + pending)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `historique_abonnements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `plan_key` VARCHAR(40) DEFAULT NULL,
  `amount` INT DEFAULT NULL,
  `currency` VARCHAR(10) DEFAULT 'XAF',
  `status` VARCHAR(40) NOT NULL DEFAULT 'pending',
  `provider` VARCHAR(40) DEFAULT 'notchpay',
  `reference` VARCHAR(120) DEFAULT NULL,
  `provider_ref` VARCHAR(160) DEFAULT NULL,
  `meta_json` LONGTEXT DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_habo_user` (`user_id`),
  KEY `idx_habo_status` (`status`),
  KEY `idx_habo_ref` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- partenaires
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `partenaires` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(160) NOT NULL,
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `logo_data` MEDIUMBLOB DEFAULT NULL,
  `logo_mime` VARCHAR(80) DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_part_pub` (`is_published`, `sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- temoignages
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `temoignages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `author_name` VARCHAR(120) NOT NULL DEFAULT '',
  `content` TEXT NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tem_pub` (`is_published`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- activites (journal admin + jours d'activité utilisateur)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` ENUM('log','day') NOT NULL DEFAULT 'log',
  `user_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(40) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `activity_date` DATE DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_act_user` (`user_id`, `kind`),
  KEY `idx_act_day` (`user_id`, `activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- sujets : passerelle catalogue CE / CO / EE / EO
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sujets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('ce','co','ee','eo') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(160) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `visibility` VARCHAR(20) NOT NULL DEFAULT 'gratuit',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 3600,
  `published_at` DATETIME DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sujets_type_slug` (`type`, `slug`),
  KEY `idx_sujets_type_pub` (`type`, `is_published`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- comprehension_ecrite (examens + consignes en JSON)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comprehension_ecrite` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sujet_id` INT UNSIGNED DEFAULT NULL,
  `kind` ENUM('exam','consigne') NOT NULL DEFAULT 'exam',
  `slug` VARCHAR(160) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `intro_html` TEXT DEFAULT NULL,
  `section_key` VARCHAR(40) DEFAULT NULL,
  `visibility` VARCHAR(20) NOT NULL DEFAULT 'gratuit',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 3600,
  `content_json` LONGTEXT DEFAULT NULL,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `legacy_exam_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ce_sujet` (`sujet_id`),
  KEY `idx_ce_kind` (`kind`, `is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- comprehension_orale
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comprehension_orale` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sujet_id` INT UNSIGNED DEFAULT NULL,
  `kind` ENUM('exam','consigne') NOT NULL DEFAULT 'exam',
  `slug` VARCHAR(160) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `intro_html` TEXT DEFAULT NULL,
  `section_key` VARCHAR(40) DEFAULT NULL,
  `visibility` VARCHAR(20) NOT NULL DEFAULT 'gratuit',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 2100,
  `content_json` LONGTEXT DEFAULT NULL,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `legacy_exam_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_co_sujet` (`sujet_id`),
  KEY `idx_co_kind` (`kind`, `is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- expression_ecrite
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expression_ecrite` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sujet_id` INT UNSIGNED DEFAULT NULL,
  `kind` ENUM('exam','consigne') NOT NULL DEFAULT 'exam',
  `slug` VARCHAR(160) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `intro_html` TEXT DEFAULT NULL,
  `section_key` VARCHAR(40) DEFAULT NULL,
  `visibility` VARCHAR(20) NOT NULL DEFAULT 'gratuit',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 3600,
  `content_json` LONGTEXT DEFAULT NULL,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `legacy_exam_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ee_sujet` (`sujet_id`),
  KEY `idx_ee_kind` (`kind`, `is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- expression_orale
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expression_orale` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sujet_id` INT UNSIGNED DEFAULT NULL,
  `kind` ENUM('exam','consigne') NOT NULL DEFAULT 'exam',
  `slug` VARCHAR(160) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `intro_html` TEXT DEFAULT NULL,
  `section_key` VARCHAR(40) DEFAULT NULL,
  `visibility` VARCHAR(20) NOT NULL DEFAULT 'gratuit',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 3600,
  `content_json` LONGTEXT DEFAULT NULL,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `legacy_exam_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eo_sujet` (`sujet_id`),
  KEY `idx_eo_kind` (`kind`, `is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- visiteurs (visites site + vues d’épreuves)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `visiteurs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` VARCHAR(40) NOT NULL DEFAULT 'site',
  `visitor_key` VARCHAR(80) DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `ref_type` VARCHAR(40) DEFAULT NULL,
  `ref_id` INT UNSIGNED DEFAULT NULL,
  `path` VARCHAR(500) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `country_code` VARCHAR(2) DEFAULT NULL,
  `country_name` VARCHAR(120) DEFAULT NULL,
  `meta_json` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vis_kind` (`kind`, `created_at`),
  KEY `idx_vis_ref` (`ref_type`, `ref_id`),
  KEY `idx_vis_visitor` (`visitor_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- parametres (réglages plateforme / branding)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parametres` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` LONGTEXT DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_param_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
