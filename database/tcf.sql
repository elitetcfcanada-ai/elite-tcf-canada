-- phpMyAdmin SQL Dump
-- Structure des tables uniquement (sans données)
-- Généré automatiquement

SET SQL_MODE = "";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Structure de la table `activities`
--

CREATE TABLE IF NOT EXISTS `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_act_user` (`user_id`),
  KEY `idx_act_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `analytics`
--

CREATE TABLE IF NOT EXISTS `analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('view','watch','like','share') NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `video_id` (`video_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `channel_branding`
--

CREATE TABLE IF NOT EXISTS `channel_branding` (
  `id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL DEFAULT '',
  `tagline` varchar(800) NOT NULL DEFAULT '',
  `logo_url` varchar(512) DEFAULT NULL,
  `banner_url` varchar(512) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `channel_playlists`
--

CREATE TABLE IF NOT EXISTS `channel_playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `visibility` enum('public','private') NOT NULL DEFAULT 'public',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chpl_created_by` (`created_by`),
  KEY `idx_chpl_visibility` (`visibility`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `channel_post_comments`
--

CREATE TABLE IF NOT EXISTS `channel_post_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cpc_post` (`post_id`),
  KEY `idx_cpc_parent` (`parent_id`),
  KEY `idx_cpc_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `channel_post_likes`
--

CREATE TABLE IF NOT EXISTS `channel_post_likes` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`post_id`,`user_id`),
  KEY `idx_cpl_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `channel_post_poll_votes`
--

CREATE TABLE IF NOT EXISTS `channel_post_poll_votes` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `option_index` smallint(6) NOT NULL,
  `voted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`post_id`,`user_id`),
  KEY `idx_cppv_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `channel_posts`
--

CREATE TABLE IF NOT EXISTS `channel_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_user_id` int(11) NOT NULL,
  `post_type` enum('text','image','poll') NOT NULL DEFAULT 'text',
  `title` varchar(255) DEFAULT NULL,
  `body` text NOT NULL DEFAULT '',
  `image_url` varchar(512) DEFAULT NULL,
  `poll_options_json` text DEFAULT NULL,
  `video_id` int(11) DEFAULT NULL,
  `visibility` enum('public','private') NOT NULL DEFAULT 'public',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chpost_author` (`author_user_id`),
  KEY `idx_chpost_video` (`video_id`),
  KEY `idx_chpost_vis` (`visibility`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `channel_subscribers`
--

CREATE TABLE IF NOT EXISTS `channel_subscribers` (
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `community_messages`
--

CREATE TABLE IF NOT EXISTS `community_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `recipients` enum('all','active','premium','new','admins') DEFAULT 'all',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `deep_link` varchar(512) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL,
  `plan_type` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `playlist_videos`
--

CREATE TABLE IF NOT EXISTS `playlist_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_playlist_video` (`playlist_id`,`video_id`),
  KEY `idx_pv_video` (`video_id`),
  KEY `idx_pv_playlist_order` (`playlist_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `site_visit_logs`
--

CREATE TABLE IF NOT EXISTS `site_visit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `page_path` varchar(512) DEFAULT '/',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(512) DEFAULT NULL,
  `referrer` varchar(1024) DEFAULT NULL,
  `traffic_source` varchar(32) NOT NULL DEFAULT 'other',
  `utm_source` varchar(128) DEFAULT NULL,
  `utm_medium` varchar(128) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `country_name` varchar(120) DEFAULT NULL,
  `region_name` varchar(120) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `subscription_payment_pending`
--

CREATE TABLE IF NOT EXISTS `subscription_payment_pending` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_key` varchar(32) NOT NULL,
  `notch_reference` varchar(80) NOT NULL,
  `amount_xaf` int(11) NOT NULL DEFAULT 100,
  `channel` varchar(32) DEFAULT NULL,
  `status` varchar(24) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_notch_ref` (`notch_reference`),
  KEY `idx_pending_user` (`user_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `subscription_payments`
--

CREATE TABLE IF NOT EXISTS `subscription_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_key` varchar(32) NOT NULL,
  `plan_label` varchar(160) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(8) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(32) NOT NULL DEFAULT 'demo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_subpay_user_created` (`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `subscription_plan_catalog`
--

CREATE TABLE IF NOT EXISTS `subscription_plan_catalog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plan_key` varchar(32) NOT NULL,
  `tier` varchar(64) NOT NULL DEFAULT '',
  `badge` varchar(160) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(8) NOT NULL DEFAULT '$',
  `duration_days` int(10) unsigned NOT NULL DEFAULT 7,
  `features_json` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `tcf_ce_answers`
--

CREATE TABLE IF NOT EXISTS `tcf_ce_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `answer_key` varchar(8) NOT NULL,
  `answer_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1877 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ce_consignes`
--

CREATE TABLE IF NOT EXISTS `tcf_ce_consignes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Consignes Compréhension Écrite',
  `body` longtext NOT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ce_exam_views`
--

CREATE TABLE IF NOT EXISTS `tcf_ce_exam_views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `visitor_id` varchar(64) NOT NULL DEFAULT '',
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `uq_ce_exam_viewer` (`exam_id`,`user_id`,`visitor_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ce_exams`
--

CREATE TABLE IF NOT EXISTS `tcf_ce_exams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(140) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `intro_html` text DEFAULT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `duration_seconds` int(10) unsigned NOT NULL DEFAULT 3600,
  `published_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ce_questions`
--

CREATE TABLE IF NOT EXISTS `tcf_ce_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `situation` text DEFAULT NULL,
  `question_text` text NOT NULL,
  `points` int(11) NOT NULL DEFAULT 3,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_co_answers`
--

CREATE TABLE IF NOT EXISTS `tcf_co_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `answer_key` varchar(8) NOT NULL,
  `answer_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_co_consignes`
--

CREATE TABLE IF NOT EXISTS `tcf_co_consignes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Consignes Compréhension Orale',
  `body` longtext NOT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_co_exam_views`
--

CREATE TABLE IF NOT EXISTS `tcf_co_exam_views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `visitor_id` varchar(64) NOT NULL DEFAULT '',
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `uq_co_exam_viewer` (`exam_id`,`user_id`,`visitor_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_co_exams`
--

CREATE TABLE IF NOT EXISTS `tcf_co_exams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(140) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `intro_html` text DEFAULT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `duration_seconds` int(10) unsigned NOT NULL DEFAULT 1800,
  `published_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_co_questions`
--

CREATE TABLE IF NOT EXISTS `tcf_co_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `question_text` text NOT NULL,
  `points` int(11) NOT NULL DEFAULT 1,
  `image_src` text DEFAULT NULL,
  `audio_src` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ee_combinations`
--

CREATE TABLE IF NOT EXISTS `tcf_ee_combinations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `combo_number` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=639 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ee_consignes`
--

CREATE TABLE IF NOT EXISTS `tcf_ee_consignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `task_key` varchar(20) NOT NULL DEFAULT 'general',
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ee_exam_views`
--

CREATE TABLE IF NOT EXISTS `tcf_ee_exam_views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `visitor_id` varchar(64) NOT NULL DEFAULT '',
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `uq_ee_exam_viewer` (`exam_id`,`user_id`,`visitor_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ee_exams`
--

CREATE TABLE IF NOT EXISTS `tcf_ee_exams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(140) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ee_task_documents`
--

CREATE TABLE IF NOT EXISTS `tcf_ee_task_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `doc_number` tinyint(3) unsigned NOT NULL,
  `title` varchar(180) DEFAULT NULL,
  `content` longtext NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1303 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_ee_tasks`
--

CREATE TABLE IF NOT EXISTS `tcf_ee_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `combination_id` int(10) unsigned NOT NULL,
  `task_number` tinyint(3) unsigned NOT NULL,
  `prompt` text NOT NULL,
  `correction` longtext DEFAULT NULL,
  `word_min` int(10) unsigned DEFAULT NULL,
  `word_max` int(10) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1962 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_eo_consignes`
--

CREATE TABLE IF NOT EXISTS `tcf_eo_consignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `task_key` varchar(20) NOT NULL DEFAULT 'general',
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_eo_exam_views`
--

CREATE TABLE IF NOT EXISTS `tcf_eo_exam_views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `visitor_id` varchar(64) NOT NULL DEFAULT '',
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `uq_eo_exam_viewer` (`exam_id`,`user_id`,`visitor_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_eo_exams`
--

CREATE TABLE IF NOT EXISTS `tcf_eo_exams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(140) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'gratuit',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_eo_parts`
--

CREATE TABLE IF NOT EXISTS `tcf_eo_parts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `task_key` varchar(20) NOT NULL DEFAULT 'tache2',
  `part_number` int(11) NOT NULL DEFAULT 1,
  `part_title` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_eo_subjects`
--

CREATE TABLE IF NOT EXISTS `tcf_eo_subjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `part_id` int(10) unsigned NOT NULL,
  `subject_number` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `prompt` text NOT NULL,
  `correction` mediumtext DEFAULT NULL,
  `role_label` varchar(255) DEFAULT NULL,
  `icon_class` varchar(80) DEFAULT 'bx bx-message-detail',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tcf_platform_settings`
--

CREATE TABLE IF NOT EXISTS `tcf_platform_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `testimonials`
--

CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_name` varchar(120) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` enum('Compréhension Écrite','Compréhension Orale','Expression Écrite','Expression Orale') NOT NULL,
  `visibility` enum('gratuit','premium') DEFAULT 'gratuit',
  `json_file` varchar(255) NOT NULL,
  `uses` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `trainers`
--

CREATE TABLE IF NOT EXISTS `trainers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `role_title` varchar(160) NOT NULL DEFAULT '',
  `photo_url` varchar(512) DEFAULT NULL,
  `social_links_json` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `user_activity_days`
--

CREATE TABLE IF NOT EXISTS `user_activity_days` (
  `user_id` int(11) NOT NULL,
  `activity_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `user_email_codes`
--

CREATE TABLE IF NOT EXISTS `user_email_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `purpose` varchar(32) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','super_admin') DEFAULT 'user',
  `subscription_type` enum('free','monthly','annual','plan_1w','plan_2w','plan_1m','plan_2m') NOT NULL DEFAULT 'free',
  `subscription_expires_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `permissions` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `reg_country_code` varchar(2) DEFAULT NULL,
  `reg_country_name` varchar(120) DEFAULT NULL,
  `reg_traffic_source` varchar(32) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `uq_users_email` (`email`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `video_comments`
--

CREATE TABLE IF NOT EXISTS `video_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `body` varchar(2000) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vc_video` (`video_id`),
  KEY `idx_vc_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `video_likes`
--

CREATE TABLE IF NOT EXISTS `video_likes` (
  `video_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`video_id`,`user_id`),
  KEY `idx_vl_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `videos`
--

CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `visibility` enum('public','private','premium') DEFAULT 'public',
  `duration` time(3) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `likes` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
