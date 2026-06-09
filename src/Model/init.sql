CREATE TABLE `user` (
    `id`       BIGINT NOT NULL AUTO_INCREMENT,
    `username` TEXT NOT NULL,
    `passwd`   TEXT NOT NULL, --FOR THE LOVE OF GOD, DONT FORGET TO BCRYPT THIS YOU BAFOON
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_username` (`username`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topic` (
    `id`         BIGINT NOT NULL AUTO_INCREMENT,
    `creator_id` BIGINT NOT NULL,
    `name`       TEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_topic_name` (`name`(255)),
    CONSTRAINT `fk_topic_creator` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `post` (
    `id`         BIGINT NOT NULL AUTO_INCREMENT,
    `parent_id`  BIGINT DEFAULT NULL,
    `title`      TEXT NOT NULL,
    `content`    TEXT NOT NULL,
    `creator_id` BIGINT DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_post_parent`  FOREIGN KEY (`parent_id`)  REFERENCES `post` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_post_creator` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `thread` (
    `id`        BIGINT NOT NULL AUTO_INCREMENT,
    `topic_id`  BIGINT NOT NULL,
    `anchor_id` BIGINT NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_thread_topic`  FOREIGN KEY (`topic_id`)  REFERENCES `topic` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_thread_anchor` FOREIGN KEY (`anchor_id`) REFERENCES `post`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `log` (
    `id`       BIGINT NOT NULL AUTO_INCREMENT,
    `action`   ENUM('topic_created', 'post_created', 'post_edited', 'post_deleted') NOT NULL,
    `topic_id` BIGINT NOT NULL,
    `post_id`  BIGINT DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_log_topic` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_log_post`  FOREIGN KEY (`post_id`)  REFERENCES `post`  (`id`) ON DELETE CASCADE,
    -- post_id is required for all post-level actions; topic_created logs only need topic_id
    CONSTRAINT `chk_log_post_required` CHECK (`action` = 'topic_created' OR `post_id` IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
