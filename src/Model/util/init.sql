CREATE DATABASE IF NOT EXISTS `zerox45`;
USE `zerox45`;

DROP TRIGGER IF EXISTS `trg_thread_after_delete`;
DROP TABLE IF EXISTS `affinity`;
DROP TABLE IF EXISTS `endorse`;
DROP TABLE IF EXISTS `log`;
DROP TABLE IF EXISTS `thread`;
DROP TABLE IF EXISTS `post`;
DROP TABLE IF EXISTS `topic`;
DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
    `id`       BIGINT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL,
    `passwd`   VARCHAR(255) NOT NULL,
    `super`    TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topic` (
    `id`         BIGINT NOT NULL AUTO_INCREMENT,
    `creator_id` BIGINT DEFAULT NULL,
    `name`       VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_topic_name` (`name`),
    CONSTRAINT `fk_topic_creator` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `post` (
    `id`          BIGINT NOT NULL AUTO_INCREMENT,
    `parent_id`   BIGINT DEFAULT NULL,
    `title`       VARCHAR(255) DEFAULT NULL,
    `content`     TEXT NOT NULL,
    `creator_key` VARCHAR(255) NOT NULL,
    `deleted`     TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_post_parent` FOREIGN KEY (`parent_id`) REFERENCES `post` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `thread` (
    `id`        BIGINT NOT NULL AUTO_INCREMENT,
    `topic_id`  BIGINT NOT NULL,
    `anchor_id` BIGINT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_anchor_id` (`anchor_id`),
    CONSTRAINT `fk_thread_topic`  FOREIGN KEY (`topic_id`)  REFERENCES `topic` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_thread_anchor` FOREIGN KEY (`anchor_id`) REFERENCES `post`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `log` (
    `id`        BIGINT NOT NULL AUTO_INCREMENT,
    `action`    ENUM('post_created', 'post_patched', 'post_deleted', 'post_seen') NOT NULL,
    `post_id`   BIGINT NOT NULL,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_log_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `endorse` (
    `id_post`   BIGINT NOT NULL,
    `voter_key` VARCHAR(255) NOT NULL,
    `vote`      TINYINT(1) NOT NULL,
    PRIMARY KEY (`id_post`, `voter_key`),
    CONSTRAINT `fk_endorse_post` FOREIGN KEY (`id_post`) REFERENCES `post` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `affinity` (
    `id_user`  BIGINT NOT NULL,
    `id_topic` BIGINT NOT NULL,
    PRIMARY KEY (`id_user`, `id_topic`),
    CONSTRAINT `fk_affinity_user`  FOREIGN KEY (`id_user`)  REFERENCES `user`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_affinity_topic` FOREIGN KEY (`id_topic`) REFERENCES `topic` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER //
CREATE TRIGGER `trg_thread_after_delete`
AFTER DELETE ON `thread`
FOR EACH ROW
BEGIN
    DELETE FROM `post` WHERE `id` = OLD.anchor_id;
END//
DELIMITER ;
