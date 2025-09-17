-- -----------------------------------------------------
-- MySQL schema for QR Attendance & Exams MVP
-- -----------------------------------------------------

-- Recommended session settings (safe defaults)
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

-- Create and select database (optional; adjust name if needed)
CREATE DATABASE IF NOT EXISTS `school_mvp`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `school_mvp`;

-- -----------------------------------------------------
-- Table: students
-- Each student has a stable UUID used for QR codes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `class_name` VARCHAR(100) NULL,
    `email` VARCHAR(191) NULL,
    `phone` VARCHAR(50) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_students_uuid` (`uuid`),
    KEY `idx_students_last_first` (`last_name`, `first_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: courses
-- Minimal course definition + optional weekly schedule hints
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `courses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `class_name` VARCHAR(100) NULL,
    `day_of_week` TINYINT UNSIGNED NULL COMMENT '1=Mon..7=Sun',
    `start_time` TIME NULL,
    `end_time` TIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_courses_code` (`code`),
    KEY `idx_courses_class_day` (`class_name`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: exams
-- Standalone exams (often tied to a course)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `exams` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(191) NOT NULL,
    `exam_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_exams_course` (`course_id`),
    CONSTRAINT `fk_exams_course`
        FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: course_sessions
-- Concrete instances of a course in time (useful to compute presence/late)
-- Can be regular classes or linked to an exam
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `course_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` BIGINT UNSIGNED NOT NULL,
    `session_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NULL,
    `is_exam` TINYINT(1) NOT NULL DEFAULT 0,
    `exam_id` BIGINT UNSIGNED NULL,
    `late_after_minutes` INT UNSIGNED NULL DEFAULT 10,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_session` (`course_id`, `session_date`, `start_time`),
    KEY `idx_sessions_exam` (`exam_id`),
    CONSTRAINT `fk_sessions_course`
        FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_sessions_exam`
        FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: payments
-- Tracks tuition/exam/other payments for students
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `exam_id` BIGINT UNSIGNED NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` CHAR(3) NULL,
    `type` ENUM('tuition','exam','other') NOT NULL,
    `status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'paid',
    `paid_at` TIMESTAMP NULL,
    `reference` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_payments_student` (`student_id`),
    KEY `idx_payments_exam` (`exam_id`),
    UNIQUE KEY `uniq_payments_reference` (`reference`),
    CONSTRAINT `fk_payments_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_payments_exam`
        FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: exam_authorizations
-- Explicit authorization to sit an exam (derived from payments and rules)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `exam_authorizations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `exam_id` BIGINT UNSIGNED NOT NULL,
    `allowed` TINYINT(1) NOT NULL DEFAULT 0,
    `allowed_at` TIMESTAMP NULL,
    `checked_by` VARCHAR(100) NULL,
    `notes` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_exam_auth` (`student_id`, `exam_id`),
    KEY `idx_exam_auth_exam` (`exam_id`),
    CONSTRAINT `fk_exam_auth_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_exam_auth_exam`
        FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: attendance
-- Records QR scans and computed presence status per session
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `session_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('present','late','absent','excused') NOT NULL DEFAULT 'present',
    `scanned_at` TIMESTAMP NULL,
    `notes` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_attendance_student_session` (`student_id`, `session_id`),
    KEY `idx_attendance_session` (`session_id`),
    CONSTRAINT `fk_attendance_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_attendance_session`
        FOREIGN KEY (`session_id`) REFERENCES `course_sessions` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Helpful views (optional)
-- -----------------------------------------------------
DROP VIEW IF EXISTS `v_attendance_today`;
CREATE VIEW `v_attendance_today` AS
SELECT 
    a.id AS attendance_id,
    s.uuid AS student_uuid,
    s.first_name,
    s.last_name,
    cs.session_date,
    cs.start_time,
    c.name AS course_name,
    a.status,
    a.scanned_at
FROM attendance a
JOIN students s ON s.id = a.student_id
JOIN course_sessions cs ON cs.id = a.session_id
JOIN courses c ON c.id = cs.course_id
WHERE cs.session_date = CURRENT_DATE();

-- -----------------------------------------------------
-- Seed examples (comment out if not needed)
-- -----------------------------------------------------
-- INSERT INTO students (`uuid`, `first_name`, `last_name`, `class_name`)
-- VALUES ('00000000-0000-0000-0000-000000000001', 'Alice', 'Dupont', '3A');

