-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 29 avr. 2026 à 20:12
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `yayascaffee`
--
CREATE DATABASE IF NOT EXISTS `yayascaffee` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `yayascaffee`;

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `replied_at` timestamp NULL DEFAULT NULL,
  `admin_reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`, `replied_at`, `admin_reply`) VALUES
(1, 'maram', 'Mimi@thebest.com', 'Feedback', 'so yummy all the food', 'read', '2026-04-23 13:57:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `custom_orders`
--

DROP TABLE IF EXISTS `custom_orders`;
CREATE TABLE `custom_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coffee_type` varchar(50) NOT NULL,
  `size` enum('S','M','L') NOT NULL,
  `milk_type` varchar(50) NOT NULL,
  `extras` text DEFAULT NULL,
  `base_price` decimal(5,2) NOT NULL,
  `total_price` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `custom_orders`
--

INSERT INTO `custom_orders` (`id`, `user_id`, `coffee_type`, `size`, `milk_type`, `extras`, `base_price`, `total_price`, `created_at`) VALUES
(1, 2, 'espresso', 'M', 'almond_milk', '[{\"value\":\"chocolate\",\"name\":\"Chocolate\",\"price\":1}]', 5.00, 8.50, '2026-04-24 19:09:15'),
(2, 2, 'cold_brew', 'M', 'almond_milk', '[{\"value\":\"honey\",\"name\":\"Honey\",\"price\":1.5}]', 9.00, 13.00, '2026-04-24 19:09:50'),
(3, 2, 'espresso', 'S', 'whole_milk', '[]', 5.00, 5.00, '2026-04-24 19:13:48'),
(4, 2, 'espresso', 'S', 'whole_milk', '[]', 5.00, 5.00, '2026-04-24 19:16:43'),
(5, 2, 'espresso', 'S', 'whole_milk', '[]', 5.00, 5.00, '2026-04-24 19:19:43'),
(6, 2, 'latte', 'M', 'oat_milk', '[{\"value\":\"chocolate\",\"name\":\"Chocolate\",\"price\":1}]', 8.00, 11.50, '2026-04-27 08:23:02'),
(7, 2, 'espresso', 'M', 'almond_milk', '[{\"value\":\"honey\",\"name\":\"Honey\",\"price\":1.5}]', 5.00, 9.00, '2026-04-27 09:36:39'),
(8, 2, 'cappuccino', 'M', 'whole_milk', '[{\"value\":\"honey\",\"name\":\"Honey\",\"price\":1.5}]', 7.00, 10.00, '2026-04-27 09:48:46');

-- --------------------------------------------------------

--
-- Structure de la table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('coffee','food') NOT NULL,
  `price` decimal(6,2) NOT NULL,
  `description` text DEFAULT NULL,
  `calories` int(11) DEFAULT 0,
  `protein` int(11) DEFAULT 0,
  `is_best_seller` tinyint(1) DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `category`, `price`, `description`, `calories`, `protein`, `is_best_seller`, `image_path`, `created_at`) VALUES
(1, 'Ramadan Edition', 'coffee', 5.50, 'Arabic coffee made with love and arabica beans.', 0, 0, 1, 'images/ramadan.jpg', '2026-04-22 19:36:01'),
(2, 'Latte', 'coffee', 8.00, 'Creamy latte with high quality beans.', 95, 10, 0, 'images/artlatte.jpg', '2026-04-22 19:36:01'),
(3, 'Spanish Iced Latte', 'coffee', 14.00, 'Cold brew with espresso and a touch of sweetness.', 210, 9, 0, 'images/spanishlatte.jpg', '2026-04-22 19:36:01'),
(4, 'High Protein Bowl', 'food', 18.00, 'Fresh avocado on sourdough with poached egg and microgreens.', 300, 28, 1, 'images/saltybowl.jpg', '2026-04-22 19:36:01'),
(5, 'Figue Salad', 'food', 22.00, 'Organic figue with roasted veggies and tahini dressing.', 420, 22, 0, 'images/figuesalad.jpg', '2026-04-22 19:36:01'),
(6, 'Greek Yogurt Bowl', 'food', 16.00, 'Thick yogurt with berries, granola, and honey.', 310, 20, 0, 'images/food1.jpg', '2026-04-22 19:36:01');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(8,2) NOT NULL,
  `status` enum('pending','confirmed','ready','delivered','cancelled') DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL COMMENT 'Email du client',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Téléphone du client',
  `delivery_address` text DEFAULT NULL COMMENT 'Adresse de livraison',
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données articles (JSON)' CHECK (json_valid(`items_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `note`, `created_at`, `email`, `phone`, `delivery_address`, `items_json`) VALUES
(1, 2, 5.50, 'ready', NULL, '2026-04-24 18:51:26', 'Mimi@thebest.com', '54101059', 'hi', '[{\"id\":1,\"name\":\"Ramadan Edition\",\"quantity\":1,\"price\":5.5,\"type\":\"menu\",\"customization\":null}]'),
(2, 2, 8.00, 'pending', NULL, '2026-04-24 19:08:56', 'Mimi@thebest.com', '54101059', 'hola', '[{\"id\":2,\"name\":\"Latte\",\"quantity\":1,\"price\":8,\"type\":\"menu\",\"customization\":null}]'),
(3, 2, 42.00, 'pending', NULL, '2026-04-25 14:20:22', 'Mimi@thebest.com', '54101059', 'mmmm', '[{\"id\":\"custom_1777057755370\",\"name\":\"Custom Espresso\",\"quantity\":1,\"price\":8.5,\"type\":\"custom\",\"customization\":\"Size: Medium, Milk: Almond Milk, Extras: Chocolate\",\"details\":{\"coffee_type\":\"espresso\",\"size\":\"M\",\"milk_type\":\"almond_milk\",\"extras\":[{\"value\":\"chocolate\",\"name\":\"Chocolate\",\"price\":1}]}},{\"id\":\"custom_1777057790433\",\"name\":\"Custom Cold Brew\",\"quantity\":1,\"price\":13,\"type\":\"custom\",\"customization\":\"Size: Medium, Milk: Almond Milk, Extras: Honey\",\"details\":{\"coffee_type\":\"cold_brew\",\"size\":\"M\",\"milk_type\":\"almond_milk\",\"extras\":[{\"value\":\"honey\",\"name\":\"Honey\",\"price\":1.5}]}},{\"id\":\"custom_1777058028860\",\"name\":\"Custom Espresso\",\"quantity\":1,\"price\":5,\"type\":\"custom\",\"customization\":\"Size: Small, Milk: Whole Milk\",\"details\":{\"coffee_type\":\"espresso\",\"size\":\"S\",\"milk_type\":\"whole_milk\",\"extras\":[]}},{\"id\":\"custom_1777058203976\",\"name\":\"Custom Espresso\",\"quantity\":1,\"price\":5,\"type\":\"custom\",\"customization\":\"Size: Small, Milk: Whole Milk\",\"details\":{\"coffee_type\":\"espresso\",\"size\":\"S\",\"milk_type\":\"whole_milk\",\"extras\":[]}},{\"id\":\"custom_1777058383751\",\"name\":\"Custom Espresso\",\"quantity\":1,\"price\":5,\"type\":\"custom\",\"customization\":\"Size: Small, Milk: Whole Milk\",\"details\":{\"coffee_type\":\"espresso\",\"size\":\"S\",\"milk_type\":\"whole_milk\",\"extras\":[]}},{\"id\":1,\"name\":\"Ramadan Edition\",\"quantity\":1,\"price\":5.5,\"type\":\"menu\",\"customization\":null}]'),
(4, 3, 8.00, 'pending', NULL, '2026-04-25 14:22:32', 'mahaa@gmail.com', '54111555', 'mookj', '[{\"id\":2,\"name\":\"Latte\",\"quantity\":1,\"price\":8,\"type\":\"menu\",\"customization\":null}]'),
(5, 2, 19.50, 'pending', NULL, '2026-04-27 08:23:51', 'Mimi@thebest.com', '54101059', 'mmmm', '[{\"id\":2,\"name\":\"Latte\",\"quantity\":1,\"price\":8,\"type\":\"menu\",\"customization\":null},{\"id\":\"custom_1777278182573\",\"name\":\"Custom Latte\",\"quantity\":1,\"price\":11.5,\"type\":\"custom\",\"customization\":\"Size: Medium, Milk: Oat Milk, Extras: Chocolate\",\"details\":{\"coffee_type\":\"latte\",\"size\":\"M\",\"milk_type\":\"oat_milk\",\"extras\":[{\"value\":\"chocolate\",\"name\":\"Chocolate\",\"price\":1}]}}]'),
(6, 2, 41.00, 'pending', NULL, '2026-04-27 09:38:08', 'Mimi@thebest.com', '7777', 'rue-77', '[{\"id\":4,\"name\":\"High Protein Bowl\",\"quantity\":1,\"price\":18,\"type\":\"menu\",\"customization\":null},{\"id\":3,\"name\":\"Spanish Iced Latte\",\"quantity\":1,\"price\":14,\"type\":\"menu\",\"customization\":null},{\"id\":\"custom_1777282599024\",\"name\":\"Custom Espresso\",\"quantity\":1,\"price\":9,\"type\":\"custom\",\"customization\":\"Size: Medium, Milk: Almond Milk, Extras: Honey\",\"details\":{\"coffee_type\":\"espresso\",\"size\":\"M\",\"milk_type\":\"almond_milk\",\"extras\":[{\"value\":\"honey\",\"name\":\"Honey\",\"price\":1.5}]}}]'),
(7, 5, 24.00, 'pending', NULL, '2026-04-27 09:52:33', 'elhakmohamedyoussef@gmail.com', '94382419', 'mourouj 5', '[{\"id\":2,\"name\":\"Latte\",\"quantity\":1,\"price\":8,\"type\":\"menu\",\"customization\":null},{\"id\":6,\"name\":\"Greek Yogurt Bowl\",\"quantity\":1,\"price\":16,\"type\":\"menu\",\"customization\":null}]');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'Yaya', 'admin@yaya.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-22 19:36:00'),
(2, 'Maram', 'Chaouch', 'Mimi@thebest.com', '$2y$10$W21YVH08xlRkXHqhLzypDuHlucJFVxIsxBPoeDDbebbvVLoDreqsu', 'user', '2026-04-23 05:35:20'),
(3, 'Guest', 'User', 'mahaa@gmail.com', '$2y$10$h9i2SMGb65zXZenVWftdduhfXd7C11r.OoF53xbKFv8Ws.lhPo046', 'user', '2026-04-25 14:22:32'),
(4, 'eya', 'Touati', 'eya11@gmail.com', '$2y$10$4PFjic.WPrslu.ehq52AKO9fiQrAT4qSHPdAMPmX1UHxVJuuoakyS', 'user', '2026-04-27 09:32:35'),
(5, 'youssef', 'elhak', 'elhakmohamedyoussef@gmail.com', '$2y$10$skUsnMNr8D4b9oeSQprvdO0fJTwM8p7fl2rA4m/tclSz6KxKBbMce', 'user', '2026-04-27 09:50:56');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `custom_orders`
--
ALTER TABLE `custom_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD CONSTRAINT `custom_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
