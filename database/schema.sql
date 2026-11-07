-- ============================================================================
-- Decision Support System (DSS) - University Specialization Selection
-- Database Schema
-- ============================================================================
-- Run this file to create the database structure.
-- For seed data with proper password hashing, use: php database/seed.php
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `dss_university`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `dss_university`;

-- ============================================================================
-- Table: users
-- Stores authentication credentials and roles for system access.
-- ============================================================================
DROP TABLE IF EXISTS `evaluations`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `specializations`;
DROP TABLE IF EXISTS `criteria`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `username`   VARCHAR(50)  NOT NULL UNIQUE,
    `email`      VARCHAR(100) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
    `role`       ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='System users with authentication credentials';

-- ============================================================================
-- Table: students
-- Stores student profile information. Optionally linked to a user account.
-- ============================================================================
CREATE TABLE `students` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NULL COMMENT 'FK to users table; NULL if not linked to an account',
    `first_name` VARCHAR(50)  NOT NULL,
    `last_name`  VARCHAR(50)  NOT NULL,
    `email`      VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_students_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Student profiles for specialization evaluation';

-- ============================================================================
-- Table: specializations
-- Available university specializations (bilingual EN/FR).
-- ============================================================================
CREATE TABLE `specializations` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `code`           VARCHAR(10)  NOT NULL UNIQUE COMMENT 'Short code e.g. P1, P2',
    `name_en`        VARCHAR(100) NOT NULL,
    `name_fr`        VARCHAR(100) NOT NULL,
    `description_en` TEXT NULL,
    `description_fr` TEXT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='University specialization programs';

-- ============================================================================
-- Table: criteria
-- Decision criteria used to evaluate specializations.
-- ============================================================================
CREATE TABLE `criteria` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name_en`    VARCHAR(100) NOT NULL,
    `name_fr`    VARCHAR(100) NOT NULL,
    `type`       ENUM('maximize', 'minimize') NOT NULL DEFAULT 'maximize'
                 COMMENT 'maximize=higher is better, minimize=lower is better',
    `weight`     DECIMAL(4,2) NOT NULL DEFAULT 1.00
                 COMMENT 'Criterion weight for WSM calculation (0.00-1.00)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Decision criteria with optimization direction and weights';

-- ============================================================================
-- Table: evaluations
-- Student scores for each specialization against each criterion.
-- ============================================================================
CREATE TABLE `evaluations` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `student_id`        INT NOT NULL,
    `specialization_id` INT NOT NULL,
    `criterion_id`      INT NOT NULL,
    `score`             DECIMAL(4,2) NOT NULL
                        COMMENT 'Score between 1.00 and 10.00',
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Each student can only rate a specialization on a criterion once
    CONSTRAINT `uq_evaluation_entry`
        UNIQUE (`student_id`, `specialization_id`, `criterion_id`),

    -- Score must be between 1 and 10
    CONSTRAINT `chk_score_range`
        CHECK (`score` >= 1.00 AND `score` <= 10.00),

    CONSTRAINT `fk_evaluations_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `fk_evaluations_specialization`
        FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `fk_evaluations_criterion`
        FOREIGN KEY (`criterion_id`) REFERENCES `criteria` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Evaluation matrix: student scores per specialization per criterion';

-- ============================================================================
-- Indexes for performance
-- ============================================================================
CREATE INDEX `idx_evaluations_student` ON `evaluations` (`student_id`);
CREATE INDEX `idx_evaluations_specialization` ON `evaluations` (`specialization_id`);
CREATE INDEX `idx_evaluations_criterion` ON `evaluations` (`criterion_id`);
CREATE INDEX `idx_students_user` ON `students` (`user_id`);

-- ============================================================================
-- Default Seed Data (Out-of-the-box University Dataset)
-- Passwords: admin -> admin123, student -> student123
-- ============================================================================

-- Users
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'admin@dss-university.com', '$2y$10$wT/0D0jP3Ff.g5fJt6/Q/.lE6QxJ2uO/vYc6h5yKj5o8p5u1b9q5q', 'admin'),
(2, 'student', 'ahmed.benali@student.dss-university.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFjkn8WBG3ec6Le5H5wQjZk27l6G7kuy', 'student')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Students
INSERT INTO `students` (`id`, `user_id`, `first_name`, `last_name`, `email`) VALUES
(1, 2, 'Ahmed', 'Benali', 'ahmed.benali@student.dss-university.com')
ON DUPLICATE KEY UPDATE `first_name`=`first_name`;

-- Specializations (From Project Brief)
INSERT INTO `specializations` (`id`, `code`, `name_en`, `name_fr`, `description_en`, `description_fr`) VALUES
(1, 'P1', 'Artificial Intelligence', 'Intelligence Artificielle', 'Study of intelligent systems, machine learning, deep learning, natural language processing, and computer vision.', 'Étude des systèmes intelligents, apprentissage automatique, apprentissage profond, traitement du langage naturel et vision par ordinateur.'),
(2, 'P2', 'Cybersecurity', 'Cybersécurité', 'Protection of information systems, network security, cryptography, ethical hacking, and digital forensics.', 'Protection des systèmes d\'information, sécurité des réseaux, cryptographie, piratage éthique et criminalistique numérique.'),
(3, 'P3', 'Data Science', 'Science des Données', 'Data analysis, statistical modeling, big data technologies, data visualization, and predictive analytics.', 'Analyse de données, modélisation statistique, technologies big data, visualisation de données et analyse prédictive.'),
(4, 'P4', 'Information Systems and Software Engineering', 'Ingénierie des Systèmes d\'Information et du Logiciel', 'Software development methodologies, database design, enterprise systems, web technologies, and project management.', 'Méthodologies de développement logiciel, conception de bases de données, systèmes d\'entreprise, technologies web et gestion de projet.')
ON DUPLICATE KEY UPDATE `code`=`code`;

-- Criteria (From Project Brief with weights summing to 1.00)
INSERT INTO `criteria` (`id`, `name_en`, `name_fr`, `type`, `weight`) VALUES
(1, 'Programming Skills', 'Compétences en Programmation', 'maximize', 0.20),
(2, 'Mathematics', 'Mathématiques', 'maximize', 0.15),
(3, 'Competency Alignment', 'Alignement des Compétences', 'maximize', 0.20),
(4, 'Career Opportunities', 'Opportunités de Carrière', 'maximize', 0.20),
(5, 'Difficulty', 'Difficulté', 'minimize', 0.10),
(6, 'Personal Interest', 'Intérêt Personnel', 'maximize', 0.15)
ON DUPLICATE KEY UPDATE `name_en`=`name_en`;

-- Evaluations for Student #1 (Ahmed Benali)
INSERT INTO `evaluations` (`student_id`, `specialization_id`, `criterion_id`, `score`) VALUES
-- P1: Artificial Intelligence
(1, 1, 1, 8.50),
(1, 1, 2, 9.00),
(1, 1, 3, 8.00),
(1, 1, 4, 9.50),
(1, 1, 5, 8.00),
(1, 1, 6, 9.00),
-- P2: Cybersecurity
(1, 2, 1, 7.00),
(1, 2, 2, 5.50),
(1, 2, 3, 6.50),
(1, 2, 4, 8.50),
(1, 2, 5, 6.00),
(1, 2, 6, 7.00),
-- P3: Data Science
(1, 3, 1, 8.00),
(1, 3, 2, 8.50),
(1, 3, 3, 7.50),
(1, 3, 4, 9.00),
(1, 3, 5, 7.00),
(1, 3, 6, 8.50),
-- P4: ISSE
(1, 4, 1, 9.00),
(1, 4, 2, 6.00),
(1, 4, 3, 8.50),
(1, 4, 4, 7.50),
(1, 4, 5, 5.00),
(1, 4, 6, 7.00)
ON DUPLICATE KEY UPDATE `score`=VALUES(`score`);

