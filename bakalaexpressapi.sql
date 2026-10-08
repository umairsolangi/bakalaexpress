-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 08, 2026 at 01:09 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakalaexpressapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catalog_categories`
--

CREATE TABLE `catalog_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by_admin_id` bigint UNSIGNED DEFAULT NULL,
  `updated_by_admin_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalog_categories`
--

INSERT INTO `catalog_categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`, `created_by_admin_id`, `updated_by_admin_id`) VALUES
(1, 'General Store', 'general-store', 'Daily essentials, pantry staples, snacks, and household items.', 1, '2026-06-17 05:02:43', '2026-06-17 05:02:43', NULL, NULL),
(2, 'Dairy & Bakery', 'dairy-bakery', 'Milk, yogurt, eggs, bread, and other fresh breakfast essentials.', 1, '2026-06-17 05:02:43', '2026-06-17 05:02:43', NULL, NULL),
(3, 'Fruits & Vegetables', 'fruits-vegetables', 'Fresh produce and seasonal grocery items.', 1, '2026-06-17 05:02:43', '2026-06-17 05:02:43', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED DEFAULT NULL,
  `shop_product_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED NOT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL DEFAULT '5',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `moderated_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `global_products`
--

CREATE TABLE `global_products` (
  `id` bigint UNSIGNED NOT NULL,
  `catalog_category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `base_price` decimal(10,2) NOT NULL,
  `unit_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by_admin_id` bigint UNSIGNED DEFAULT NULL,
  `updated_by_admin_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `global_products`
--

INSERT INTO `global_products` (`id`, `catalog_category_id`, `name`, `description`, `base_price`, `unit_type`, `default_image`, `is_active`, `created_at`, `updated_at`, `deleted_at`, `created_by_admin_id`, `updated_by_admin_id`) VALUES
(1, 1, 'Olpers Milk 1L', 'Fresh milk carton for daily home use.', 280.00, 'Carton', NULL, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44', NULL, NULL, NULL),
(2, 1, 'Dawn Bread Large', 'Fresh bread loaf for breakfast and snacks.', 150.00, 'Loaf', NULL, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44', NULL, NULL, NULL),
(3, 1, 'Farm Fresh Eggs', 'A dozen fresh eggs.', 400.00, 'Dozen', NULL, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44', NULL, NULL, NULL),
(4, 1, 'Lays Masala', 'Popular snack packet.', 80.00, 'Packet', NULL, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44', NULL, NULL, NULL),
(5, 1, 'Coca Cola 1.5L', 'Soft drink bottle.', 180.00, 'Bottle', NULL, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `sender_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2024_06_14_000000_create_personal_access_tokens_table', 1),
(4, '2024_12_12_180538_create_sellers_table', 1),
(5, '2024_12_12_180539_create_services_table', 1),
(6, '2024_12_14_132911_create_users_table', 1),
(7, '2024_12_16_140213_create_user_profile_updates_table', 1),
(8, '2024_12_16_180740_create_sessions_table', 1),
(9, '2024_12_18_172704_create_notifications_table', 1),
(10, '2024_12_27_184305_create_orders_table', 1),
(11, '2024_12_27_184336_create_order_items_table', 1),
(12, '2025_03_09_205809_create_messages_table', 1),
(13, '2025_03_25_152156_create_feedback_table', 1),
(14, '2025_04_07_094536_create_seller_verifications_table', 1),
(15, '2025_04_24_224639_add_remember_token_to_sellers_table', 1),
(16, '2025_12_18_094203_rename_services_to_products_and_update_columns', 1),
(17, '2025_12_18_094206_create_riders_table', 1),
(18, '2025_12_18_094211_add_rider_id_and_update_status_to_orders_table', 1),
(19, '2025_12_18_094607_rename_service_id_to_product_id_in_order_items_table', 1),
(20, '2025_12_18_105000_add_completed_to_order_status_enum', 1),
(21, '2026_01_06_082753_add_details_to_riders_table', 1),
(22, '2026_04_28_110900_add_otp_expires_at_to_users_table', 1),
(23, '2026_04_28_113300_add_location_fields_to_sellers_table', 1),
(24, '2026_04_28_145000_create_catalog_categories_table', 1),
(25, '2026_04_28_145100_add_catalog_category_id_to_sellers_table', 1),
(26, '2026_04_28_145200_create_global_products_table', 1),
(27, '2026_04_28_145300_create_shop_product_table', 1),
(28, '2026_04_28_145400_add_master_catalog_fields_to_order_items_table', 1),
(29, '2026_05_04_072346_add_governance_fields_to_catalog_tables', 1),
(30, '2026_05_04_130000_add_stock_quantity_to_shop_product_table', 1),
(31, '2026_05_04_130100_add_inventory_reserved_at_to_orders_table', 1),
(32, '2026_05_04_140000_add_ux_feature_fields', 1),
(33, '2026_06_17_095457_add_delivery_charges_to_orders_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED NOT NULL,
  `rider_id` bigint UNSIGNED DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','confirmed_by_seller','preparing','ready_for_pickup','assigned_to_rider','picked_up','delivered','cancelled','rejected','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_charges` decimal(8,2) NOT NULL DEFAULT '0.00',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `promo_code_id` bigint UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `inventory_reserved_at` timestamp NULL DEFAULT NULL,
  `estimated_delivery_at` timestamp NULL DEFAULT NULL,
  `delivery_proof_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `shop_product_id` bigint UNSIGNED DEFAULT NULL,
  `global_product_id` bigint UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `unit_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `seller_city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seller_area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seller_contact_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_order_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `maximum_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int UNSIGNED DEFAULT NULL,
  `used_count` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnic_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnic_front` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnic_back` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_book` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_lat` decimal(10,7) DEFAULT NULL,
  `current_long` decimal(10,7) DEFAULT NULL,
  `status` enum('online','offline','busy') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riders`
--

INSERT INTO `riders` (`id`, `name`, `email`, `address`, `phone`, `cnic_number`, `cnic_front`, `cnic_back`, `password`, `profile_image`, `vehicle_type`, `vehicle_number`, `license_image`, `vehicle_image`, `registration_book`, `current_lat`, `current_long`, `status`, `is_approved`, `is_verified`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Demo Rider', 'rider@bakalaexpress.com', 'Street 10, Baldia Town, Karachi', '03111234567', '4210112345671', NULL, NULL, '$2y$12$1kjVWi1XSxq52FA.AKx9PewQEJYPTcsRey60A2KdyRPPWDUHk99Rm', NULL, 'bike', 'KHI-1234', NULL, NULL, NULL, 24.9491000, 66.9385000, 'online', 1, 1, NULL, '2026-06-17 05:02:44', '2026-06-17 05:02:44');

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to seller profile image',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'City where seller operates',
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Area where seller operates',
  `sector` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `near_areas` json DEFAULT NULL,
  `full_address` text COLLATE utf8mb4_unicode_ci,
  `opens_at` time DEFAULT NULL,
  `closes_at` time DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `catalog_category_id` bigint UNSIGNED DEFAULT NULL,
  `accountIsApproved` int UNSIGNED NOT NULL COMMENT '1 for yes, 0 for no',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `remember_token`, `profile_image`, `city`, `area`, `sector`, `near_areas`, `full_address`, `opens_at`, `closes_at`, `is_open`, `catalog_category_id`, `accountIsApproved`, `is_deleted`, `created_at`, `updated_at`) VALUES
(3, 'Demo Seller', 'seller@bakalaexpress.com', '$2y$12$4655HVIFrSFVmDbtGPM4YOR7BtFFnSxKIVAcPLP9iRF4WsgRk3xk.', NULL, NULL, 'Karachi', 'Baldia Town', '4B', '[\"Ali Chowk\", \"Lahori Hotel\"]', 'Shop #5, Sector 4B, near Lahori Hotel, Baldia Town', NULL, NULL, 1, 1, 1, 0, '2026-06-17 05:02:43', '2026-06-17 05:02:44');

-- --------------------------------------------------------

--
-- Table structure for table `seller_verifications`
--

CREATE TABLE `seller_verifications` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `documents` json DEFAULT NULL COMMENT 'Paths to verification documents',
  `business_description` text COLLATE utf8mb4_unicode_ci,
  `reason_for_verification` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_product`
--

CREATE TABLE `shop_product` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED NOT NULL,
  `global_product_id` bigint UNSIGNED NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shop_product`
--

INSERT INTO `shop_product` (`id`, `seller_id`, `global_product_id`, `custom_price`, `stock_quantity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, 0, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44'),
(2, 3, 2, NULL, 0, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44'),
(3, 3, 3, NULL, 0, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44'),
(4, 3, 4, NULL, 0, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44'),
(5, 3, 5, NULL, 0, 1, '2026-06-17 05:02:44', '2026-06-17 05:02:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `sellerType` tinyint NOT NULL DEFAULT '2' COMMENT '1 for admin, 2 for buyer, 3 for seller',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mobile number of the user',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Primary address line',
  `address2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Secondary address line (optional)',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'City of residence',
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'State of residence',
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Postal code',
  `pickup_time` enum('morning','afternoon','evening') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Preferred pickup time',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indicates if the user has verified their email',
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'OTP for email verification',
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `sellerType`, `name`, `email`, `email_verified_at`, `password`, `mobile`, `address`, `address2`, `city`, `state`, `zip`, `pickup_time`, `is_verified`, `otp`, `otp_expires_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Demo Admin', 'admin@bakala.pk', '2026-06-17 05:02:43', '$2y$12$Qdl3yS/mTrWYG4JOAMq9DOZLp3JODEnlQY5tNBmRJdkI/2GyfYUle', '03001234567', 'Office #1, Baldia Town', NULL, 'Karachi', 'Sindh', '75760', 'morning', 1, NULL, NULL, NULL, '2026-06-17 05:02:43', '2026-06-17 05:02:43'),
(2, 2, 'Demo User', 'user@bakalaexpress.com', '2026-06-17 05:02:43', '$2y$12$jgBfBq4H8YeJqqRKo9IDweyjIsy9ZajSowiCsWgw4oltshxq2hEWa', '03007654321', 'House #12, Sector 4B, Baldia Town', NULL, 'Karachi', 'Sindh', '75760', 'evening', 1, NULL, NULL, NULL, '2026-06-17 05:02:43', '2026-06-17 05:02:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_profile_updates`
--

CREATE TABLE `user_profile_updates` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `catalog_categories`
--
ALTER TABLE `catalog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `catalog_categories_name_unique` (`name`),
  ADD UNIQUE KEY `catalog_categories_slug_unique` (`slug`),
  ADD KEY `catalog_categories_created_by_admin_id_foreign` (`created_by_admin_id`),
  ADD KEY `catalog_categories_updated_by_admin_id_foreign` (`updated_by_admin_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `favorites_user_id_seller_id_unique` (`user_id`,`seller_id`),
  ADD UNIQUE KEY `favorites_user_id_shop_product_id_unique` (`user_id`,`shop_product_id`),
  ADD KEY `favorites_seller_id_foreign` (`seller_id`),
  ADD KEY `favorites_shop_product_id_foreign` (`shop_product_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_order_id_foreign` (`order_id`),
  ADD KEY `feedback_user_id_foreign` (`user_id`),
  ADD KEY `feedback_seller_id_foreign` (`seller_id`),
  ADD KEY `feedback_moderated_by_foreign` (`moderated_by`);

--
-- Indexes for table `global_products`
--
ALTER TABLE `global_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `global_products_catalog_category_id_is_active_index` (`catalog_category_id`,`is_active`),
  ADD KEY `global_products_created_by_admin_id_foreign` (`created_by_admin_id`),
  ADD KEY `global_products_updated_by_admin_id_foreign` (`updated_by_admin_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_order_id_foreign` (`order_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_seller_id_foreign` (`seller_id`),
  ADD KEY `orders_rider_id_foreign` (`rider_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_service_id_foreign` (`product_id`),
  ADD KEY `order_items_shop_product_id_foreign` (`shop_product_id`),
  ADD KEY `order_items_global_product_id_foreign` (`global_product_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `services_seller_id_foreign` (`seller_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promo_codes_code_unique` (`code`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `riders_email_unique` (`email`),
  ADD UNIQUE KEY `riders_cnic_number_unique` (`cnic_number`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sellers_email_unique` (`email`),
  ADD KEY `sellers_catalog_category_id_foreign` (`catalog_category_id`);

--
-- Indexes for table `seller_verifications`
--
ALTER TABLE `seller_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_verifications_seller_id_foreign` (`seller_id`),
  ADD KEY `seller_verifications_reviewed_by_foreign` (`reviewed_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shop_product`
--
ALTER TABLE `shop_product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_product_seller_id_global_product_id_unique` (`seller_id`,`global_product_id`),
  ADD KEY `shop_product_global_product_id_foreign` (`global_product_id`),
  ADD KEY `shop_product_seller_id_is_active_index` (`seller_id`,`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_profile_updates`
--
ALTER TABLE `user_profile_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_profile_updates_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `catalog_categories`
--
ALTER TABLE `catalog_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `global_products`
--
ALTER TABLE `global_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `seller_verifications`
--
ALTER TABLE `seller_verifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shop_product`
--
ALTER TABLE `shop_product`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_profile_updates`
--
ALTER TABLE `user_profile_updates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `catalog_categories`
--
ALTER TABLE `catalog_categories`
  ADD CONSTRAINT `catalog_categories_created_by_admin_id_foreign` FOREIGN KEY (`created_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `catalog_categories_updated_by_admin_id_foreign` FOREIGN KEY (`updated_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_shop_product_id_foreign` FOREIGN KEY (`shop_product_id`) REFERENCES `shop_product` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_moderated_by_foreign` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `global_products`
--
ALTER TABLE `global_products`
  ADD CONSTRAINT `global_products_catalog_category_id_foreign` FOREIGN KEY (`catalog_category_id`) REFERENCES `catalog_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `global_products_created_by_admin_id_foreign` FOREIGN KEY (`created_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `global_products_updated_by_admin_id_foreign` FOREIGN KEY (`updated_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_global_product_id_foreign` FOREIGN KEY (`global_product_id`) REFERENCES `global_products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_service_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_shop_product_id_foreign` FOREIGN KEY (`shop_product_id`) REFERENCES `shop_product` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `services_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sellers`
--
ALTER TABLE `sellers`
  ADD CONSTRAINT `sellers_catalog_category_id_foreign` FOREIGN KEY (`catalog_category_id`) REFERENCES `catalog_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `seller_verifications`
--
ALTER TABLE `seller_verifications`
  ADD CONSTRAINT `seller_verifications_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seller_verifications_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_product`
--
ALTER TABLE `shop_product`
  ADD CONSTRAINT `shop_product_global_product_id_foreign` FOREIGN KEY (`global_product_id`) REFERENCES `global_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_product_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profile_updates`
--
ALTER TABLE `user_profile_updates`
  ADD CONSTRAINT `user_profile_updates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
