-- Volcando estructura para tabla paywatch.all_notifications
CREATE TABLE IF NOT EXISTS `all_notifications` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `device_id` varchar(255) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `app_name` varchar(100) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `text` varchar(1000) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `big_text` text COLLATE utf8mb4_spanish2_ci,
  `sub_text` varchar(500) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `timestamp` bigint NOT NULL,
  `is_payment_app` tinyint(1) DEFAULT '0',
  `category` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT 'other',
  `synced` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_package_name` (`package_name`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_category` (`category`),
  KEY `idx_payment_app` (`is_payment_app`),
  KEY `idx_synced` (`synced`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_all_notifications_user_id` (`user_id`),
  CONSTRAINT `fk_all_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2269 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla paywatch.payment_notifications
CREATE TABLE IF NOT EXISTS `payment_notifications` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `device_id` varchar(255) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `app` varchar(100) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `title` varchar(500) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `text` varchar(1000) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `big_text` text COLLATE utf8mb4_spanish2_ci,
  `sub_text` varchar(500) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `original_message` text COLLATE utf8mb4_spanish2_ci,
  `timestamp` bigint NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `sender` varchar(255) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `confidence_level` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT 'MEDIUM',
  `raw_notification_text` text COLLATE utf8mb4_spanish2_ci,
  `migrated` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_app` (`app`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_confidence` (`confidence_level`),
  KEY `idx_migrated` (`migrated`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_payment_notifications_user_id` (`user_id`),
  CONSTRAINT `fk_payment_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla paywatch.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(150) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `password_hash` char(60) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `device_id` varchar(255) COLLATE utf8mb4_spanish2_ci NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_username` (`username`),
  KEY `idx_usuario_device_id` (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
