-- ============================================================
-- Task Table for Student To-Do List System
-- Database: user_system
-- ============================================================

USE `user_system`;

-- --------------------------------------------------------
-- Table structure for `tasks`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id`     INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11)      NOT NULL,
  `title`       VARCHAR(150) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `due_date`    DATE         DEFAULT NULL,
  `category`    ENUM('Homework','Project','Exam','Assignment','Personal','Other') NOT NULL DEFAULT 'Other',
  `priority`    ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
  `status`      ENUM('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `fk_tasks_user` (`user_id`),
  CONSTRAINT `fk_tasks_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
