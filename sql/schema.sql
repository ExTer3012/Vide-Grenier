-- schema.sql — Structure de la base de données
-- Vide Grenier en Ligne
-- Peut être exécuté plusieurs fois sans erreurs

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Table `users`
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(100) NOT NULL,
    `email`      VARCHAR(254) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `is_admin`   TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table `articles`
CREATE TABLE IF NOT EXISTS `articles` (
    `id`             INT(11)      NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(200) NOT NULL,
    `description`    TEXT         NOT NULL,
    `published_date` DATE         DEFAULT NULL,
    `user_id`        INT(11)      NOT NULL,
    `views`          INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `picture`        VARCHAR(200) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_articles_picture` (`picture`),
    KEY `fk_articles_user` (`user_id`),
    CONSTRAINT `fk_articles_user`
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;