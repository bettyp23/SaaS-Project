-- Todo Tracker SaaS Database Schema
-- MySQL Database Creation and Schema Setup

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `vibe_templates` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `vibe_templates`;

-- Users table with enhanced fields for SaaS
CREATE TABLE `users` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `timezone` varchar(255) DEFAULT 'UTC',
    `profile_picture` varchar(255) DEFAULT NULL,
    `two_factor_secret` text DEFAULT NULL,
    `two_factor_recovery_codes` text DEFAULT NULL,
    `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
    `failed_login_attempts` int(11) DEFAULT 0,
    `locked_until` timestamp NULL DEFAULT NULL,
    `remember_token` varchar(100) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_email_index` (`email`),
    KEY `users_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens
CREATE TABLE `password_reset_tokens` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    KEY `password_reset_tokens_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personal access tokens for API authentication
CREATE TABLE `personal_access_tokens` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `tokenable_type` varchar(255) NOT NULL,
    `tokenable_id` bigint(20) unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `token` varchar(64) NOT NULL,
    `abilities` text DEFAULT NULL,
    `last_used_at` timestamp NULL DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teams table for collaboration
CREATE TABLE `teams` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `owner_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `teams_owner_id_foreign` (`owner_id`),
    CONSTRAINT `teams_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team members with roles
CREATE TABLE `team_members` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `team_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `role` enum('owner','admin','member','viewer') NOT NULL DEFAULT 'member',
    `invited_by` bigint(20) unsigned DEFAULT NULL,
    `invited_at` timestamp NULL DEFAULT NULL,
    `joined_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `team_members_team_id_user_id_unique` (`team_id`,`user_id`),
    KEY `team_members_user_id_foreign` (`user_id`),
    KEY `team_members_invited_by_foreign` (`invited_by`),
    CONSTRAINT `team_members_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `team_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `team_members_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Todo lists/projects
CREATE TABLE `todo_lists` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `color` varchar(7) DEFAULT '#3B82F6',
    `user_id` bigint(20) unsigned NOT NULL,
    `team_id` bigint(20) unsigned DEFAULT NULL,
    `is_public` tinyint(1) DEFAULT 0,
    `sort_order` int(11) DEFAULT 0,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `todo_lists_user_id_foreign` (`user_id`),
    KEY `todo_lists_team_id_foreign` (`team_id`),
    KEY `todo_lists_sort_order_index` (`sort_order`),
    CONSTRAINT `todo_lists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `todo_lists_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags for categorization
CREATE TABLE `tags` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `color` varchar(7) DEFAULT '#6B7280',
    `user_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `tags_user_id_foreign` (`user_id`),
    KEY `tags_name_index` (`name`),
    CONSTRAINT `tags_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Main todos table
CREATE TABLE `todos` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
    `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `due_date` timestamp NULL DEFAULT NULL,
    `completed_at` timestamp NULL DEFAULT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `list_id` bigint(20) unsigned DEFAULT NULL,
    `parent_id` bigint(20) unsigned DEFAULT NULL,
    `sort_order` int(11) DEFAULT 0,
    `estimated_time` int(11) DEFAULT NULL COMMENT 'Estimated time in minutes',
    `actual_time` int(11) DEFAULT NULL COMMENT 'Actual time spent in minutes',
    `is_recurring` tinyint(1) DEFAULT 0,
    `recurring_pattern` varchar(50) DEFAULT NULL COMMENT 'daily, weekly, monthly, custom',
    `recurring_interval` int(11) DEFAULT NULL,
    `recurring_end_date` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `todos_user_id_foreign` (`user_id`),
    KEY `todos_list_id_foreign` (`list_id`),
    KEY `todos_parent_id_foreign` (`parent_id`),
    KEY `todos_status_index` (`status`),
    KEY `todos_priority_index` (`priority`),
    KEY `todos_due_date_index` (`due_date`),
    KEY `todos_sort_order_index` (`sort_order`),
    CONSTRAINT `todos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `todos_list_id_foreign` FOREIGN KEY (`list_id`) REFERENCES `todo_lists` (`id`) ON DELETE SET NULL,
    CONSTRAINT `todos_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `todos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Todo tags pivot table
CREATE TABLE `todo_tags` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `todo_id` bigint(20) unsigned NOT NULL,
    `tag_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `todo_tags_todo_id_tag_id_unique` (`todo_id`,`tag_id`),
    KEY `todo_tags_tag_id_foreign` (`tag_id`),
    CONSTRAINT `todo_tags_todo_id_foreign` FOREIGN KEY (`todo_id`) REFERENCES `todos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `todo_tags_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File attachments for todos
CREATE TABLE `todo_attachments` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `todo_id` bigint(20) unsigned NOT NULL,
    `filename` varchar(255) NOT NULL,
    `original_filename` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `file_size` bigint(20) unsigned NOT NULL,
    `mime_type` varchar(100) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `todo_attachments_todo_id_foreign` (`todo_id`),
    CONSTRAINT `todo_attachments_todo_id_foreign` FOREIGN KEY (`todo_id`) REFERENCES `todos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments on todos
CREATE TABLE `todo_comments` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `todo_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `content` text NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `todo_comments_todo_id_foreign` (`todo_id`),
    KEY `todo_comments_user_id_foreign` (`user_id`),
    CONSTRAINT `todo_comments_todo_id_foreign` FOREIGN KEY (`todo_id`) REFERENCES `todos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `todo_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE `notifications` (
    `id` char(36) NOT NULL,
    `type` varchar(255) NOT NULL,
    `notifiable_type` varchar(255) NOT NULL,
    `notifiable_id` bigint(20) unsigned NOT NULL,
    `data` text NOT NULL,
    `read_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User preferences
CREATE TABLE `user_preferences` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `key` varchar(255) NOT NULL,
    `value` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_preferences_user_id_key_unique` (`user_id`,`key`),
    CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription plans
CREATE TABLE `subscription_plans` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `price` decimal(10,2) NOT NULL,
    `currency` varchar(3) DEFAULT 'USD',
    `interval` enum('monthly','yearly') NOT NULL DEFAULT 'monthly',
    `trial_days` int(11) DEFAULT 0,
    `max_todos` int(11) DEFAULT NULL,
    `max_team_members` int(11) DEFAULT NULL,
    `features` json DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subscription_plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User subscriptions
CREATE TABLE `user_subscriptions` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `plan_id` bigint(20) unsigned NOT NULL,
    `stripe_subscription_id` varchar(255) DEFAULT NULL,
    `status` enum('active','cancelled','past_due','unpaid') NOT NULL DEFAULT 'active',
    `current_period_start` timestamp NULL DEFAULT NULL,
    `current_period_end` timestamp NULL DEFAULT NULL,
    `trial_ends_at` timestamp NULL DEFAULT NULL,
    `cancelled_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_subscriptions_user_id_foreign` (`user_id`),
    KEY `user_subscriptions_plan_id_foreign` (`plan_id`),
    CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `user_subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans
INSERT INTO `subscription_plans` (`name`, `slug`, `description`, `price`, `currency`, `interval`, `trial_days`, `max_todos`, `max_team_members`, `features`, `is_active`) VALUES
('Free', 'free', 'Basic features for personal use', 0.00, 'USD', 'monthly', 0, 50, 1, '["basic_todos", "one_list", "email_support"]', 1),
('Pro', 'pro', 'Advanced features for power users', 9.99, 'USD', 'monthly', 14, NULL, 5, '["unlimited_todos", "multiple_lists", "team_collaboration", "file_attachments", "priority_support"]', 1),
('Enterprise', 'enterprise', 'Full features for teams and organizations', 29.99, 'USD', 'monthly', 30, NULL, NULL, '["unlimited_todos", "unlimited_teams", "advanced_analytics", "api_access", "white_label", "dedicated_support"]', 1);

-- Create indexes for better performance
CREATE INDEX `idx_todos_user_status` ON `todos` (`user_id`, `status`);
CREATE INDEX `idx_todos_due_date_status` ON `todos` (`due_date`, `status`);
CREATE INDEX `idx_todos_priority_status` ON `todos` (`priority`, `status`);
CREATE INDEX `idx_todos_created_at` ON `todos` (`created_at`);
CREATE INDEX `idx_todos_updated_at` ON `todos` (`updated_at`);

-- Create full-text search index for todos
ALTER TABLE `todos` ADD FULLTEXT(`title`, `description`);

-- Create indexes for notifications
CREATE INDEX `idx_notifications_read_at` ON `notifications` (`read_at`);
CREATE INDEX `idx_notifications_created_at` ON `notifications` (`created_at`);
