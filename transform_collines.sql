-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 30 sep. 2026 à 20:56
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `transform_collines`
--

-- --------------------------------------------------------

--
-- Structure de la table `besoins`
--

CREATE TABLE `besoins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unite_id` bigint(20) UNSIGNED NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `quantite_recherchee` decimal(10,2) NOT NULL COMMENT 'En kg ou tonnes — doit être > 0 (RG-001)',
  `delai` date NOT NULL COMMENT 'Date limite — doit être postérieure à la publication (RG-002)',
  `taux_couverture` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Pourcentage 0-100, recalculé automatiquement (F-07, RG-012)',
  `seuils_notifies` varchar(50) DEFAULT NULL,
  `statut` enum('brouillon','actif','partiellement_couvert','couvert','cloture','expire') NOT NULL DEFAULT 'brouillon' COMMENT 'Cycle de vie — cf. §4.1 des specs fonctionnelles',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Déchargement des données de la table `besoins`
--

INSERT INTO `besoins` (`id`, `unite_id`, `produit_id`, `quantite_recherchee`, `delai`, `taux_couverture`, `seuils_notifies`, `statut`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 20.00, '2026-10-10', 100.00, NULL, 'couvert', '2026-09-30 08:30:05', '2026-09-30 08:30:06'),
(2, 3, 2, 15.00, '2026-10-07', 100.00, NULL, 'couvert', '2026-09-30 08:30:05', '2026-09-30 08:30:06'),
(3, 4, 3, 8.00, '2026-10-15', 100.00, NULL, 'couvert', '2026-09-30 08:30:05', '2026-09-30 08:30:06'),
(4, 2, 1, 10.00, '2026-10-03', 40.00, NULL, 'partiellement_couvert', '2026-09-30 08:30:05', '2026-09-30 12:14:27'),
(5, 3, 1, 5.00, '2026-10-03', 40.00, '25', 'partiellement_couvert', '2026-09-30 08:30:05', '2026-09-30 17:47:27'),
(6, 2, 1, 20.00, '2026-10-02', 100.00, NULL, 'couvert', '2026-09-30 11:51:36', '2026-09-30 12:05:17'),
(7, 2, 1, 10000.00, '2026-10-18', 100.00, '25,50,100', 'couvert', '2026-09-30 12:22:21', '2026-09-30 12:24:01');

-- --------------------------------------------------------

--
-- Structure de la table `besoin_communes`
--

CREATE TABLE `besoin_communes` (
  `besoin_id` bigint(20) UNSIGNED NOT NULL,
  `commune_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Zone de collecte préférée d un besoin, exprimée en liste de communes (pivot)';

--
-- Déchargement des données de la table `besoin_communes`
--

INSERT INTO `besoin_communes` (`besoin_id`, `commune_id`) VALUES
(1, 1),
(1, 2),
(1, 4),
(2, 3),
(2, 5),
(3, 2),
(3, 6),
(4, 1),
(5, 2),
(5, 5),
(7, 1),
(7, 4);

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `communes`
--

CREATE TABLE `communes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(50) NOT NULL COMMENT 'Nom de la commune des Collines',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Les six communes du département des Collines';

--
-- Déchargement des données de la table `communes`
--

INSERT INTO `communes` (`id`, `nom`, `created_at`, `updated_at`) VALUES
(1, 'Dassa-Zoumé', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(2, 'Glazoué', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(3, 'Ouèssè', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(4, 'Savalou', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(5, 'Savè', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(6, 'Bantè', '2026-09-30 08:35:58', '2026-09-30 08:35:58');

-- --------------------------------------------------------

--
-- Structure de la table `disponibilites`
--

CREATE TABLE `disponibilites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producteur_id` bigint(20) UNSIGNED NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `besoin_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL = déclaration libre, non associée à un besoin précis (RG-009)',
  `quantite` decimal(10,2) NOT NULL COMMENT 'Quantité déclarée — doit être > 0 (RG-008)',
  `quantite_livree` decimal(10,2) DEFAULT NULL COMMENT 'Renseignée à la confirmation de réception (F-10) — peut différer de la quantité promise',
  `date_disponibilite` date NOT NULL,
  `statut` enum('declaree','associee','confirmee','expiree') NOT NULL DEFAULT 'declaree' COMMENT 'Cycle de vie — cf. §4.2 des specs fonctionnelles',
  `date_expiration` date DEFAULT NULL COMMENT 'Calculée à la création : date_disponibilite + 15 jours par défaut (RG-011)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Déchargement des données de la table `disponibilites`
--

INSERT INTO `disponibilites` (`id`, `producteur_id`, `produit_id`, `besoin_id`, `quantite`, `quantite_livree`, `date_disponibilite`, `statut`, `date_expiration`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 1, 8000.00, NULL, '2026-10-05', 'associee', '2026-10-20', '2026-09-30 08:30:05', '2026-09-30 08:30:05'),
(2, 6, 1, 1, 4000.00, NULL, '2026-10-06', 'associee', '2026-10-21', '2026-09-30 08:30:05', '2026-09-30 08:30:05'),
(3, 7, 2, 2, 6000.00, NULL, '2026-10-04', 'associee', '2026-10-19', '2026-09-30 08:30:05', '2026-09-30 08:30:05'),
(4, 8, 3, 3, 3000.00, NULL, '2026-10-08', 'associee', '2026-10-23', '2026-09-30 08:30:06', '2026-09-30 08:30:06'),
(5, 6, 2, NULL, 2500.00, NULL, '2026-10-10', 'declaree', '2026-10-25', '2026-09-30 08:30:06', '2026-09-30 08:30:06'),
(6, 5, 1, NULL, 3000.00, NULL, '2026-10-12', 'declaree', '2026-10-27', '2026-09-30 08:30:06', '2026-09-30 08:30:06'),
(7, 5, 1, 6, 20.00, 20.00, '2026-09-30', 'confirmee', '2026-10-15', '2026-09-30 12:05:17', '2026-09-30 17:22:35'),
(8, 5, 1, 7, 30000.00, NULL, '2026-09-30', 'associee', '2026-10-15', '2026-09-30 12:24:01', '2026-09-30 12:24:01'),
(9, 5, 1, NULL, 500.00, NULL, '2026-09-30', 'declaree', '2026-10-15', '2026-09-30 12:58:26', '2026-09-30 12:58:26'),
(10, 5, 1, 5, 2.00, NULL, '2026-10-03', 'associee', '2026-10-15', '2026-09-30 17:47:27', '2026-09-30 17:47:27');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique_fiabilite`
--

CREATE TABLE `historique_fiabilite` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type_acteur` enum('producteur','unite_transformation') NOT NULL,
  `producteur_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Renseigné si type_acteur = producteur',
  `unite_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Renseigné si type_acteur = unite_transformation',
  `disponibilite_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type_evenement` enum('livraison','paiement') NOT NULL,
  `resultat` enum('tenu','partiellement_tenu','non_tenu','a_temps','en_retard') NOT NULL,
  `commentaire` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historique bilatéral alimentant le score de fiabilité (RG-016, RG-017)';

--
-- Déchargement des données de la table `historique_fiabilite`
--

INSERT INTO `historique_fiabilite` (`id`, `type_acteur`, `producteur_id`, `unite_id`, `disponibilite_id`, `type_evenement`, `resultat`, `commentaire`, `created_at`) VALUES
(1, 'producteur', 5, NULL, 7, 'livraison', 'tenu', 'Promis : 20 kg, livré : 20 kg', NULL),
(2, 'producteur', 5, NULL, 7, 'livraison', 'tenu', 'Qualité : bon', NULL),
(3, 'unite_transformation', NULL, 2, 7, 'paiement', 'a_temps', 'Paiement : a_temps', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_30_131848_add_seuils_notifies_to_besoins_table', 2),
(5, '2026_09_30_133400_add_coordonnees_to_utilisateurs_table', 3);

-- --------------------------------------------------------

--
-- Structure de la table `notations`
--

CREATE TABLE `notations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `disponibilite_id` bigint(20) UNSIGNED NOT NULL,
  `note_qualite` enum('bon','moyen','faible') DEFAULT NULL COMMENT 'Saisie par l unité, sur le lot reçu',
  `note_paiement` enum('a_temps','en_retard') DEFAULT NULL COMMENT 'Saisie par le producteur, sur le paiement reçu',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Notation bilatérale qualité / paiement (F-11)';

--
-- Déchargement des données de la table `notations`
--

INSERT INTO `notations` (`id`, `disponibilite_id`, `note_qualite`, `note_paiement`, `created_at`) VALUES
(1, 7, 'bon', 'a_temps', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `code` enum('N-01','N-02','N-03','N-04','N-05') NOT NULL COMMENT 'Cf. tableau des notifications du cahier des charges',
  `canal` enum('web','sms') NOT NULL,
  `contenu` varchar(255) NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Notifications envoyées (F-08, F-09, N-01 à N-05)';

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `utilisateur_id`, `code`, `canal`, `contenu`, `lu`, `created_at`, `updated_at`) VALUES
(1, 14, 'N-03', 'web', 'Besoin urgent en Manioc à Dassa-Zoumé — quantité manquante : 10 kg. [besoin #4]', 0, NULL, NULL),
(2, 17, 'N-02', 'web', 'Votre besoin en Manioc est couvert à 25 %.', 0, NULL, NULL),
(3, 17, 'N-02', 'web', 'Votre besoin en Manioc est couvert à 50 %.', 0, NULL, NULL),
(4, 17, 'N-02', 'web', 'Votre besoin en Manioc est couvert à 100 %.', 0, NULL, NULL),
(5, 18, 'N-02', 'web', 'Votre besoin en Manioc est couvert à 25 %.', 0, '2026-09-30 17:47:27', NULL),
(6, 18, 'N-02', 'web', 'Fifamè AGBODJAN a rejoint la coopérative virtuelle pour votre besoin en Manioc (+2 kg).', 0, '2026-09-30 17:47:27', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `producteurs`
--

CREATE TABLE `producteurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `est_cooperative` tinyint(1) NOT NULL DEFAULT 0,
  `nombre_membres_approx` int(10) UNSIGNED DEFAULT NULL COMMENT 'Renseigné uniquement si est_cooperative = true',
  `score_fiabilite` enum('nouveau','a_surveiller','fiable') NOT NULL DEFAULT 'nouveau' COMMENT 'Calculé automatiquement — jamais modifiable manuellement (RG-018)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Profil producteur / coopérative (F-01)';

--
-- Déchargement des données de la table `producteurs`
--

INSERT INTO `producteurs` (`id`, `utilisateur_id`, `est_cooperative`, `nombre_membres_approx`, `score_fiabilite`, `created_at`, `updated_at`) VALUES
(5, 13, 1, 25, 'nouveau', '2026-09-30 08:30:01', '2026-09-30 17:22:35'),
(6, 14, 0, NULL, 'nouveau', '2026-09-30 08:30:02', '2026-09-30 08:30:02'),
(7, 15, 1, 12, 'a_surveiller', '2026-09-30 08:30:02', '2026-09-30 08:30:02'),
(8, 16, 0, NULL, 'fiable', '2026-09-30 08:30:03', '2026-09-30 08:30:03');

-- --------------------------------------------------------

--
-- Structure de la table `producteur_produits`
--

CREATE TABLE `producteur_produits` (
  `producteur_id` bigint(20) UNSIGNED NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Produits habituellement cultivés par un producteur (pivot)';

--
-- Déchargement des données de la table `producteur_produits`
--

INSERT INTO `producteur_produits` (`producteur_id`, `produit_id`) VALUES
(5, 1),
(6, 1),
(6, 2),
(7, 2),
(8, 3),
(8, 4);

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(50) NOT NULL COMMENT 'Nom du produit agricole (manioc, soja, anacarde, karité, autre)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Liste fermée des produits gérés par la plateforme';

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `created_at`, `updated_at`) VALUES
(1, 'Manioc', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(2, 'Soja', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(3, 'Anacarde', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(4, 'Karité', '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(5, 'Autre', '2026-09-30 08:35:58', '2026-09-30 08:35:58');

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('G1uaQ5g1aWyVTyLPJQ1BQHH0HS1tShzddFCc6K6l', 22996000001, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiYWVjV2V6T3NFazFGZlpwdEpHd1ZBeTkyYmZYdVRzVjJSSkNoQnBVZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY2FydGUvZGF0YSI7czo1OiJyb3V0ZSI7czoxMDoiY2FydGUuZGF0YSI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjEyOiIrMjI5OTYwMDAwMDEiO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiI2ZGVlZDY4OTZkN2JjN2RkNGM1Y2I0YmE3NTdiNDg4NGJiOTQ2MTkyYzlkMTMxOTVlYmY4MzRkMmM4M2QyOGU1Ijt9', 1790787020),
('pkrx5GXsKj5fAuD8mxJl3LEIsMLQoSltSlrfNZx5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSVluYlliNjFGWmxXWXRhQUVMNkNGV2VaSWtzMkZmaHJzZEZoU3BPMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9fQ==', 1790794568);

-- --------------------------------------------------------

--
-- Structure de la table `signalements_sms`
--

CREATE TABLE `signalements_sms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `message_brut` varchar(160) NOT NULL COMMENT 'Contenu brut du SMS reçu',
  `produit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantite` decimal(10,2) DEFAULT NULL,
  `commune_id` bigint(20) UNSIGNED DEFAULT NULL,
  `disponibilite_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Renseigné si le SMS a été correctement interprété (F-13)',
  `statut_traitement` enum('traite','erreur_format') NOT NULL DEFAULT 'erreur_format',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Journal des signalements reçus par SMS/USSD (F-13, RG-019)';

--
-- Déchargement des données de la table `signalements_sms`
--

INSERT INTO `signalements_sms` (`id`, `telephone`, `message_brut`, `produit_id`, `quantite`, `commune_id`, `disponibilite_id`, `statut_traitement`, `created_at`) VALUES
(1, '+22997000001', 'MANIOC 500 SAVALOU', 1, 500.00, 4, 9, 'traite', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `unites_transformation`
--

CREATE TABLE `unites_transformation` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `capacite_traitement_approx` decimal(10,2) DEFAULT NULL COMMENT 'Capacité de traitement habituelle, en tonnes',
  `zone_collecte_rayon_km` decimal(6,2) DEFAULT NULL COMMENT 'Rayon de collecte préféré, alternative aux communes ciblées',
  `score_fiabilite` enum('nouveau','a_surveiller','fiable') NOT NULL DEFAULT 'nouveau' COMMENT 'Basé sur l historique de paiement (RG-017, RG-018)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Profil unité de transformation (F-02)';

--
-- Déchargement des données de la table `unites_transformation`
--

INSERT INTO `unites_transformation` (`id`, `utilisateur_id`, `capacite_traitement_approx`, `zone_collecte_rayon_km`, `score_fiabilite`, `created_at`, `updated_at`) VALUES
(2, 17, 50.00, 60.00, 'nouveau', '2026-09-30 08:30:03', '2026-09-30 17:25:44'),
(3, 18, 30.00, 45.00, 'nouveau', '2026-09-30 08:30:04', '2026-09-30 08:30:04'),
(4, 19, 20.00, 50.00, 'nouveau', '2026-09-30 08:30:05', '2026-09-30 08:30:05');

-- --------------------------------------------------------

--
-- Structure de la table `unite_produits`
--

CREATE TABLE `unite_produits` (
  `unite_id` bigint(20) UNSIGNED NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Produits habituellement transformés par une unité (pivot)';

--
-- Déchargement des données de la table `unite_produits`
--

INSERT INTO `unite_produits` (`unite_id`, `produit_id`) VALUES
(2, 1),
(3, 2),
(4, 3);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL COMMENT 'Format béninois +229XXXXXXXX — identifiant unique du compte',
  `type_compte` enum('producteur','unite_transformation','institution','admin') NOT NULL,
  `commune_id` bigint(20) UNSIGNED NOT NULL,
  `village` varchar(150) DEFAULT NULL COMMENT 'Localisation précise (village), pour les producteurs surtout',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `statut_compte` enum('nouveau','actif','suspendu') NOT NULL DEFAULT 'nouveau',
  `mot_de_passe` varchar(255) DEFAULT NULL COMMENT 'Hash du mot de passe (nullable si connexion uniquement par SMS/OTP)',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Compte générique, quel que soit le rôle (RG-003, RG-018)';

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `telephone`, `type_compte`, `commune_id`, `village`, `latitude`, `longitude`, `statut_compte`, `mot_de_passe`, `remember_token`, `created_at`, `updated_at`) VALUES
(11, 'Administrateur Plateforme', '+22990000000', 'admin', 1, NULL, 7.7847000, 2.1882000, 'actif', '$2y$12$qF6wl.SbSYKjheipqJBw6.fJULpyv/XcW/Te9bS/ZxmDtuuWWsRb2', NULL, '2026-09-30 08:30:00', '2026-09-30 12:37:58'),
(12, 'Agent MAEP Collines', '+22991000000', 'institution', 1, NULL, 7.7802000, 2.1797000, 'actif', '$2y$12$7CsY/3YLTLL4gTuyj65cyuF88jg8JTl0uHQ6DpRwmeutlqDIXHXzy', 'InBtnY7CSFCFNHuQ6VIQjmtwU4ANz88ppsCrlTtZWXlIszhwyy3RScjWnjst', '2026-09-30 08:30:01', '2026-09-30 12:37:58'),
(13, 'Fifamè AGBODJAN', '+22997000001', 'producteur', 4, 'Savalou-Agbado', 7.9314000, 1.9611000, 'actif', '$2y$12$MLR2vud/zBvaxK/EueJlD.VyeD8fs.8CYHwVc6PNnCuSuqzebCW8W', 'AeLiOhMMd5QT4PinxOa8RFdnzrb2mxHjk2NcAJ282azzcSgZb6uvvo3V0bD0', '2026-09-30 08:30:01', '2026-09-30 12:37:58'),
(14, 'Jean HOUNKPATIN', '+22997000002', 'producteur', 1, 'Paouignan', 7.7836000, 2.1898000, 'actif', '$2y$12$NacDYv1hO2lOH.eZ3Cf9HO3Lr6jPHvU3x52PHsO1dQ8JfRkEed.ia', NULL, '2026-09-30 08:30:02', '2026-09-30 12:37:58'),
(15, 'Marie DOSSOU', '+22997000003', 'producteur', 5, 'Savè Centre', 8.0382000, 2.4900000, 'actif', '$2y$12$/R3UwIW7GurCZ.u3XcLX5uyHhGldPVNZcRmPSdBhglUDwKgnb8HyC', NULL, '2026-09-30 08:30:02', '2026-09-30 12:37:58'),
(16, 'Pierre ADJOVI', '+22997000004', 'producteur', 2, 'Glazoué', 7.9625000, 2.2331000, 'actif', '$2y$12$aWYiae4QIw2vgqP.Oi.hIu25rq6cFCGe1ldL.9nAvUSqwnviN5C8W', NULL, '2026-09-30 08:30:03', '2026-09-30 12:37:58'),
(17, 'Unité de Transformation de Paouignan', '+22996000001', 'unite_transformation', 1, 'Paouignan', 7.7803000, 2.1807000, 'actif', '$2y$12$WvQt301opXWYWec.DbWUSeDjDXNl5H0W8VdIzYdA9jPhJMVkrnOAW', 'Tb1hkWRMHKTAIuNsSxxg5Tn4ph4nPsL25JiOcSsoT6nW8u9DrFTjQey1at76', '2026-09-30 08:30:03', '2026-09-30 12:37:58'),
(18, 'Coopérative de Transformation de Savè', '+22996000002', 'unite_transformation', 5, 'Savè', 8.0375000, 2.4854000, 'actif', '$2y$12$B8tSk4pTYOq6WeyxgBlQxONcjFzdgCLZ/F1XAQ49uEmvMcyt19j/G', NULL, '2026-09-30 08:30:04', '2026-09-30 12:37:58'),
(19, 'Unité Anacarde Glazoué', '+22996000003', 'unite_transformation', 2, 'Glazoué', 7.9603000, 2.2406000, 'actif', '$2y$12$XagRapIXTTIRyIBI2oo7q.OQwCT0Do1ijkyZq2KD9Yo/T04/5vwnm', NULL, '2026-09-30 08:30:04', '2026-09-30 12:37:58');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `besoins`
--
ALTER TABLE `besoins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_besoins_unite` (`unite_id`),
  ADD KEY `idx_besoins_statut` (`statut`),
  ADD KEY `idx_besoins_produit` (`produit_id`);

--
-- Index pour la table `besoin_communes`
--
ALTER TABLE `besoin_communes`
  ADD PRIMARY KEY (`besoin_id`,`commune_id`),
  ADD KEY `fk_bc_commune` (`commune_id`);

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `communes`
--
ALTER TABLE `communes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `disponibilites`
--
ALTER TABLE `disponibilites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dispo_producteur` (`producteur_id`),
  ADD KEY `fk_dispo_produit` (`produit_id`),
  ADD KEY `idx_dispo_statut` (`statut`),
  ADD KEY `idx_dispo_besoin` (`besoin_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `historique_fiabilite`
--
ALTER TABLE `historique_fiabilite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hist_disponibilite` (`disponibilite_id`),
  ADD KEY `idx_hist_producteur` (`producteur_id`),
  ADD KEY `idx_hist_unite` (`unite_id`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `notations`
--
ALTER TABLE `notations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notations_disponibilite` (`disponibilite_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_utilisateur` (`utilisateur_id`,`lu`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `producteurs`
--
ALTER TABLE `producteurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `producteur_produits`
--
ALTER TABLE `producteur_produits`
  ADD PRIMARY KEY (`producteur_id`,`produit_id`),
  ADD KEY `fk_pp_produit` (`produit_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `signalements_sms`
--
ALTER TABLE `signalements_sms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sms_produit` (`produit_id`),
  ADD KEY `fk_sms_commune` (`commune_id`),
  ADD KEY `fk_sms_disponibilite` (`disponibilite_id`);

--
-- Index pour la table `unites_transformation`
--
ALTER TABLE `unites_transformation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `unite_produits`
--
ALTER TABLE `unite_produits`
  ADD PRIMARY KEY (`unite_id`,`produit_id`),
  ADD KEY `fk_up_produit` (`produit_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telephone` (`telephone`),
  ADD KEY `fk_utilisateurs_commune` (`commune_id`),
  ADD KEY `idx_utilisateurs_type` (`type_compte`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `besoins`
--
ALTER TABLE `besoins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `communes`
--
ALTER TABLE `communes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `disponibilites`
--
ALTER TABLE `disponibilites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `historique_fiabilite`
--
ALTER TABLE `historique_fiabilite`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `notations`
--
ALTER TABLE `notations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `producteurs`
--
ALTER TABLE `producteurs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `signalements_sms`
--
ALTER TABLE `signalements_sms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `unites_transformation`
--
ALTER TABLE `unites_transformation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `besoins`
--
ALTER TABLE `besoins`
  ADD CONSTRAINT `fk_besoins_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  ADD CONSTRAINT `fk_besoins_unite` FOREIGN KEY (`unite_id`) REFERENCES `unites_transformation` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `besoin_communes`
--
ALTER TABLE `besoin_communes`
  ADD CONSTRAINT `fk_bc_besoin` FOREIGN KEY (`besoin_id`) REFERENCES `besoins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bc_commune` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `disponibilites`
--
ALTER TABLE `disponibilites`
  ADD CONSTRAINT `fk_dispo_besoin` FOREIGN KEY (`besoin_id`) REFERENCES `besoins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dispo_producteur` FOREIGN KEY (`producteur_id`) REFERENCES `producteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dispo_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

--
-- Contraintes pour la table `historique_fiabilite`
--
ALTER TABLE `historique_fiabilite`
  ADD CONSTRAINT `fk_hist_disponibilite` FOREIGN KEY (`disponibilite_id`) REFERENCES `disponibilites` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hist_producteur` FOREIGN KEY (`producteur_id`) REFERENCES `producteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hist_unite` FOREIGN KEY (`unite_id`) REFERENCES `unites_transformation` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notations`
--
ALTER TABLE `notations`
  ADD CONSTRAINT `fk_notations_disponibilite` FOREIGN KEY (`disponibilite_id`) REFERENCES `disponibilites` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `producteurs`
--
ALTER TABLE `producteurs`
  ADD CONSTRAINT `fk_producteurs_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `producteur_produits`
--
ALTER TABLE `producteur_produits`
  ADD CONSTRAINT `fk_pp_producteur` FOREIGN KEY (`producteur_id`) REFERENCES `producteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pp_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `signalements_sms`
--
ALTER TABLE `signalements_sms`
  ADD CONSTRAINT `fk_sms_commune` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sms_disponibilite` FOREIGN KEY (`disponibilite_id`) REFERENCES `disponibilites` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sms_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `unites_transformation`
--
ALTER TABLE `unites_transformation`
  ADD CONSTRAINT `fk_unites_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `unite_produits`
--
ALTER TABLE `unite_produits`
  ADD CONSTRAINT `fk_up_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_up_unite` FOREIGN KEY (`unite_id`) REFERENCES `unites_transformation` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_utilisateurs_commune` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
