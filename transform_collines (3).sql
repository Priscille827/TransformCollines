-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 16 oct. 2026 à 14:13
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
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catégories de produits du marketplace';

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `code`, `nom`, `icone`, `ordre`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'brut', 'Produits bruts', 'sprout', 1, 1, '2026-10-14 11:25:09', '2026-10-14 11:25:09'),
(2, 'transforme', 'Produits transformés', 'package', 2, 1, '2026-10-14 11:25:09', '2026-10-14 11:25:09'),
(3, 'intrant', 'Intrants agricoles', 'beaker', 3, 1, '2026-10-14 11:25:09', '2026-10-14 11:25:09'),
(4, 'materiel', 'Matériel et outils', 'wrench', 4, 1, '2026-10-14 11:25:09', '2026-10-14 11:25:09');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(20) NOT NULL COMMENT 'Ex: CMD-2026-0001',
  `acheteur_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Population, producteur ou unité',
  `producteur_id` bigint(20) UNSIGNED NOT NULL,
  `montant_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `statut` enum('en_attente','acceptee','refusee','en_preparation','expediee','disponible_retrait','livree','terminee','annulee') NOT NULL DEFAULT 'en_attente',
  `mode_reception` enum('retrait','livraison') NOT NULL DEFAULT 'retrait',
  `adresse_livraison` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `motif_annulation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Commandes passées sur le marketplace';

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `reference`, `acheteur_id`, `producteur_id`, `montant_total`, `statut`, `mode_reception`, `adresse_livraison`, `notes`, `motif_annulation`, `created_at`, `updated_at`) VALUES
(1, 'CMD-2026-0001', 23, 5, 10000.00, 'terminee', 'retrait', NULL, NULL, NULL, '2026-10-15 13:09:39', '2026-10-15 13:17:56');

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
  `unite_mesure_id` bigint(20) UNSIGNED DEFAULT NULL,
  `poids_unitaire_kg` decimal(10,2) DEFAULT NULL,
  `unite_reference_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Unité de référence du poids (kg, g, L, ml)',
  `besoin_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL = déclaration libre, non associée à un besoin précis (RG-009)',
  `quantite` decimal(10,2) NOT NULL COMMENT 'Quantité déclarée — doit être > 0 (RG-008)',
  `quantite_kg_equivalent` decimal(12,2) DEFAULT NULL,
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

INSERT INTO `disponibilites` (`id`, `producteur_id`, `produit_id`, `unite_mesure_id`, `poids_unitaire_kg`, `unite_reference_id`, `besoin_id`, `quantite`, `quantite_kg_equivalent`, `quantite_livree`, `date_disponibilite`, `statut`, `date_expiration`, `created_at`, `updated_at`) VALUES
(1, 5, 1, NULL, NULL, NULL, 1, 8000.00, NULL, NULL, '2026-10-05', 'associee', '2026-10-20', '2026-09-30 08:30:05', '2026-09-30 08:30:05'),
(2, 6, 1, NULL, NULL, NULL, 1, 4000.00, NULL, NULL, '2026-10-06', 'associee', '2026-10-21', '2026-09-30 08:30:05', '2026-09-30 08:30:05'),
(3, 7, 2, NULL, NULL, NULL, 2, 6000.00, NULL, NULL, '2026-10-04', 'associee', '2026-10-19', '2026-09-30 08:30:05', '2026-09-30 08:30:05'),
(4, 8, 3, NULL, NULL, NULL, 3, 3000.00, NULL, NULL, '2026-10-08', 'associee', '2026-10-23', '2026-09-30 08:30:06', '2026-09-30 08:30:06'),
(5, 6, 2, NULL, NULL, NULL, NULL, 2500.00, NULL, NULL, '2026-10-10', 'declaree', '2026-10-25', '2026-09-30 08:30:06', '2026-09-30 08:30:06'),
(6, 5, 1, NULL, NULL, NULL, NULL, 3000.00, NULL, NULL, '2026-10-12', 'declaree', '2026-10-27', '2026-09-30 08:30:06', '2026-09-30 08:30:06'),
(7, 5, 1, NULL, NULL, NULL, 6, 20.00, NULL, 20.00, '2026-09-30', 'confirmee', '2026-10-15', '2026-09-30 12:05:17', '2026-09-30 17:22:35'),
(8, 5, 1, NULL, NULL, NULL, 7, 30000.00, NULL, NULL, '2026-09-30', 'associee', '2026-10-15', '2026-09-30 12:24:01', '2026-09-30 12:24:01'),
(9, 5, 1, NULL, NULL, NULL, NULL, 500.00, NULL, NULL, '2026-09-30', 'declaree', '2026-10-15', '2026-09-30 12:58:26', '2026-09-30 12:58:26'),
(10, 5, 1, NULL, NULL, NULL, 5, 2.00, NULL, NULL, '2026-10-03', 'associee', '2026-10-15', '2026-09-30 17:47:27', '2026-09-30 17:47:27'),
(11, 11, 3, NULL, NULL, NULL, NULL, 500.00, NULL, NULL, '2026-10-14', 'declaree', '2026-10-29', '2026-10-14 17:22:11', '2026-10-14 17:22:11');

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
-- Structure de la table `ligne_commandes`
--

CREATE TABLE `ligne_commandes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `commande_id` bigint(20) UNSIGNED NOT NULL,
  `offre_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Peut être NULL si l offre est supprimée',
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `unite_mesure_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` decimal(12,2) NOT NULL,
  `prix_unitaire` decimal(12,2) NOT NULL,
  `sous_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lignes d une commande';

--
-- Déchargement des données de la table `ligne_commandes`
--

INSERT INTO `ligne_commandes` (`id`, `commande_id`, `offre_id`, `produit_id`, `unite_mesure_id`, `quantite`, `prix_unitaire`, `sous_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 5, 2.00, 5000.00, 10000.00, '2026-10-15 13:09:39', '2026-10-15 13:09:39');

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
-- Structure de la table `mouvements_stock`
--

CREATE TABLE `mouvements_stock` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('entree','sortie','ajustement') NOT NULL,
  `quantite` decimal(12,2) NOT NULL COMMENT 'Positif pour entrée, négatif pour sortie',
  `motif` varchar(255) DEFAULT NULL COMMENT 'Récolte, vente, perte, ajustement...',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'Classe du modèle lié (Commande, Offre...)',
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cree_par` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historique des entrées et sorties de stock';

--
-- Déchargement des données de la table `mouvements_stock`
--

INSERT INTO `mouvements_stock` (`id`, `stock_id`, `type`, `quantite`, `motif`, `reference_type`, `reference_id`, `cree_par`, `created_at`, `updated_at`) VALUES
(1, 1, 'entree', 10.00, 'Récolte', NULL, NULL, 13, '2026-10-15 08:10:56', '2026-10-15 08:10:56'),
(2, 1, 'sortie', -2.00, 'Commande CMD-2026-0001', 'App\\Models\\Commande', 1, 13, '2026-10-15 13:17:01', '2026-10-15 13:17:01'),
(3, 2, 'entree', 50.00, 'Ajout de stock', NULL, NULL, 13, '2026-10-16 07:08:28', '2026-10-16 07:08:28'),
(4, 3, 'entree', 10.00, 'Ajout de stock', NULL, NULL, 13, '2026-10-16 07:12:43', '2026-10-16 07:12:43'),
(5, 4, 'entree', 20.00, 'Ajout de stock', NULL, NULL, 17, '2026-10-16 07:17:49', '2026-10-16 07:17:49'),
(6, 5, 'entree', 20.00, 'Ajout de stock', NULL, NULL, 17, '2026-10-16 07:19:21', '2026-10-16 07:19:21'),
(7, 4, 'entree', 5.00, 'Ajout de stock', NULL, NULL, 17, '2026-10-16 09:27:00', '2026-10-16 09:27:00');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `code` enum('N-01','N-02','N-03','N-04','N-05') NOT NULL COMMENT 'Cf. tableau des notifications du cahier des charges',
  `titre` varchar(150) DEFAULT NULL,
  `canal` enum('web','sms') NOT NULL,
  `contenu` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT 0,
  `lu_le` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Notifications envoyées (F-08, F-09, N-01 à N-05)';

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `utilisateur_id`, `code`, `titre`, `canal`, `contenu`, `url`, `lu`, `lu_le`, `created_at`, `updated_at`) VALUES
(1, 14, 'N-03', NULL, 'web', 'Besoin urgent en Manioc à Dassa-Zoumé — quantité manquante : 10 kg. [besoin #4]', NULL, 0, NULL, NULL, NULL),
(2, 17, 'N-02', NULL, 'web', 'Votre besoin en Manioc est couvert à 25 %.', NULL, 0, NULL, NULL, NULL),
(3, 17, 'N-02', NULL, 'web', 'Votre besoin en Manioc est couvert à 50 %.', NULL, 0, NULL, NULL, NULL),
(4, 17, 'N-02', NULL, 'web', 'Votre besoin en Manioc est couvert à 100 %.', NULL, 0, NULL, NULL, NULL),
(5, 18, 'N-02', NULL, 'web', 'Votre besoin en Manioc est couvert à 25 %.', NULL, 0, NULL, '2026-09-30 17:47:27', NULL),
(6, 18, 'N-02', NULL, 'web', 'Fifamè AGBODJAN a rejoint la coopérative virtuelle pour votre besoin en Manioc (+2 kg).', NULL, 0, NULL, '2026-09-30 17:47:27', NULL),
(7, 11, 'N-01', NULL, 'web', 'Nouveau compte producteur : Yissegnon', NULL, 0, NULL, '2026-10-14 15:52:59', NULL),
(8, 12, 'N-01', NULL, 'web', 'Nouveau compte producteur : Yissegnon', NULL, 0, NULL, '2026-10-14 15:52:59', NULL),
(9, 11, 'N-01', NULL, 'web', 'Nouveau compte Producteur : KINDOHO Josué', NULL, 0, NULL, '2026-10-14 17:00:22', NULL),
(10, 12, 'N-01', NULL, 'web', 'Nouveau compte Producteur : KINDOHO Josué', NULL, 0, NULL, '2026-10-14 17:00:22', NULL),
(11, 11, 'N-01', NULL, 'web', 'Nouveau compte Producteur : KINDOHO Josué', NULL, 0, NULL, '2026-10-14 17:04:54', NULL),
(12, 12, 'N-01', NULL, 'web', 'Nouveau compte Producteur : KINDOHO Josué', NULL, 0, NULL, '2026-10-14 17:04:54', NULL),
(13, 11, 'N-01', NULL, 'web', 'KINDOHO Josué propose le produit « Fruits »', NULL, 0, NULL, '2026-10-14 17:20:09', NULL),
(14, 12, 'N-01', NULL, 'web', 'KINDOHO Josué propose le produit « Fruits »', NULL, 0, NULL, '2026-10-14 17:20:09', NULL),
(15, 22, 'N-04', NULL, 'web', 'Votre proposition « Fruits » a été validée sous le nom « Fruits ».', NULL, 0, NULL, '2026-10-15 06:19:30', NULL),
(16, 13, 'N-05', NULL, 'web', 'Nouvelle commande CMD-2026-0001 de KOKOU', NULL, 1, NULL, '2026-10-15 13:09:39', NULL),
(17, 23, 'N-05', NULL, 'web', 'Votre commande CMD-2026-0001 a été acceptée.', NULL, 0, NULL, '2026-10-15 13:17:01', NULL),
(18, 23, 'N-05', NULL, 'web', 'Votre commande CMD-2026-0001 est maintenant en préparation.', NULL, 0, NULL, '2026-10-15 13:17:08', NULL),
(19, 23, 'N-05', NULL, 'web', 'Votre commande CMD-2026-0001 est maintenant expédiée.', NULL, 0, NULL, '2026-10-15 13:17:27', NULL),
(20, 23, 'N-05', NULL, 'web', 'Votre commande CMD-2026-0001 est maintenant livrée.', NULL, 0, NULL, '2026-10-15 13:17:43', NULL),
(21, 23, 'N-05', NULL, 'web', 'Votre commande CMD-2026-0001 est maintenant terminée.', NULL, 0, NULL, '2026-10-15 13:17:56', NULL),
(22, 11, 'N-01', NULL, 'web', 'Nouveau compte Producteur : Varencia TOGO', NULL, 0, NULL, '2026-10-16 10:23:00', NULL),
(23, 12, 'N-01', NULL, 'web', 'Nouveau compte Producteur : Varencia TOGO', NULL, 0, NULL, '2026-10-16 10:23:00', NULL),
(24, 11, 'N-01', NULL, 'web', 'Varencia TOGO a fourni son document (CIP)', NULL, 0, NULL, '2026-10-16 10:26:23', NULL),
(25, 12, 'N-01', NULL, 'web', 'Varencia TOGO a fourni son document (CIP)', NULL, 0, NULL, '2026-10-16 10:26:23', NULL),
(26, 24, 'N-04', NULL, 'web', 'Votre compte a été validé. Vous pouvez maintenant utiliser toutes les fonctionnalités.', NULL, 0, NULL, '2026-10-16 10:28:00', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `offres`
--

CREATE TABLE `offres` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producteur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unite_id` bigint(20) UNSIGNED DEFAULT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `categorie_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `unite_mesure_id` bigint(20) UNSIGNED NOT NULL,
  `poids_unitaire_kg` decimal(10,2) DEFAULT NULL,
  `unite_reference_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Unité de référence du poids (kg, g, L, ml)',
  `quantite_disponible` decimal(12,2) NOT NULL,
  `quantite_kg_equivalent` decimal(12,2) DEFAULT NULL,
  `prix_detail` decimal(12,2) DEFAULT NULL COMMENT 'Prix à l unité (détail)',
  `prix_gros` decimal(12,2) DEFAULT NULL COMMENT 'Prix à l unité (gros)',
  `seuil_gros` decimal(12,2) DEFAULT NULL COMMENT 'Quantité minimum pour bénéficier du prix de gros',
  `localisation` varchar(255) DEFAULT NULL,
  `conditions_retrait` text DEFAULT NULL,
  `statut` enum('brouillon','active','epuisee','archivee') NOT NULL DEFAULT 'active',
  `photo_principale` varchar(255) DEFAULT NULL,
  `vues` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Déchargement des données de la table `offres`
--

INSERT INTO `offres` (`id`, `producteur_id`, `unite_id`, `produit_id`, `categorie_id`, `titre`, `description`, `unite_mesure_id`, `poids_unitaire_kg`, `unite_reference_id`, `quantite_disponible`, `quantite_kg_equivalent`, `prix_detail`, `prix_gros`, `seuil_gros`, `localisation`, `conditions_retrait`, `statut`, `photo_principale`, `vues`, `created_at`, `updated_at`) VALUES
(1, 5, NULL, 1, 1, 'Manioc frais', 'Bien gros', 5, 25.00, NULL, 3.00, 125.00, 5000.00, 4500.00, 5.00, 'Savalou rue 12', 'Livraison possible', 'active', 'offres/1/DpOoZwxHvTS51U1c2CEF4x1gEoZ0KY7dAHmMjffD.jpg', 4, '2026-10-15 08:57:06', '2026-10-15 13:17:01'),
(2, 5, NULL, 2, 1, 'Soja Local', 'soja local frais récolté', 3, 25.00, NULL, 8.00, 200.00, 3500.00, 2500.00, 5.00, 'Savalou rue 12', 'Livraison possible à partir de 5 sac', 'active', 'offres/2/DI4uTAbbBvhdpN920NHX2yYQ3GXizcIHn1xTOjlT.jpg', 1, '2026-10-16 07:14:29', '2026-10-16 07:14:59'),
(3, NULL, 2, 6, 2, 'Huile de palme de qualité', 'Huile de palme de qualité', 11, 1.00, NULL, 10.00, 10.00, 2500.00, 1500.00, 5.00, 'Savalou rue 12', 'Retrait surplace', 'active', 'offres/3/9XDHYZQek1q78y1UpWhvuik1st2h369xaRaK0Aoh.jpg', 0, '2026-10-16 09:00:03', '2026-10-16 09:02:46');

-- --------------------------------------------------------

--
-- Structure de la table `offre_photos`
--

CREATE TABLE `offre_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `offre_id` bigint(20) UNSIGNED NOT NULL,
  `chemin` varchar(255) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Photos associées à une offre';

--
-- Déchargement des données de la table `offre_photos`
--

INSERT INTO `offre_photos` (`id`, `offre_id`, `chemin`, `ordre`, `created_at`, `updated_at`) VALUES
(2, 1, 'offres/1/DpOoZwxHvTS51U1c2CEF4x1gEoZ0KY7dAHmMjffD.jpg', 0, '2026-10-15 09:17:30', '2026-10-15 09:17:30'),
(3, 2, 'offres/2/DI4uTAbbBvhdpN920NHX2yYQ3GXizcIHn1xTOjlT.jpg', 0, '2026-10-16 07:14:51', '2026-10-16 07:14:51'),
(4, 3, 'offres/3/9XDHYZQek1q78y1UpWhvuik1st2h369xaRaK0Aoh.jpg', 0, '2026-10-16 09:02:46', '2026-10-16 09:02:46');

-- --------------------------------------------------------

--
-- Structure de la table `panier_items`
--

CREATE TABLE `panier_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `offre_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Articles du panier de chaque utilisateur';

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
  `score_fiabilite` enum('nouveau','a_surveiller','fiable') NOT NULL DEFAULT 'nouveau' COMMENT 'Calculé automatiquement — jamais modifiable manuellement (RG-018)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Profil producteur / coopérative (F-01)';

--
-- Déchargement des données de la table `producteurs`
--

INSERT INTO `producteurs` (`id`, `utilisateur_id`, `score_fiabilite`, `created_at`, `updated_at`) VALUES
(5, 13, 'nouveau', '2026-09-30 08:30:01', '2026-09-30 17:22:35'),
(6, 14, 'nouveau', '2026-09-30 08:30:02', '2026-09-30 08:30:02'),
(7, 15, 'a_surveiller', '2026-09-30 08:30:02', '2026-09-30 08:30:02'),
(8, 16, 'fiable', '2026-09-30 08:30:03', '2026-09-30 08:30:03'),
(9, 20, 'nouveau', '2026-10-14 15:52:58', '2026-10-14 15:52:58'),
(11, 22, 'nouveau', '2026-10-14 17:04:54', '2026-10-14 17:04:54'),
(12, 24, 'nouveau', '2026-10-16 10:23:00', '2026-10-16 10:23:00');

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
(5, 2),
(5, 14),
(6, 1),
(6, 2),
(7, 2),
(8, 3),
(8, 4),
(11, 2),
(11, 3),
(12, 3),
(12, 4);

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(50) NOT NULL COMMENT 'Nom du produit agricole (manioc, soja, anacarde, karité, autre)',
  `categorie_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type_produit` enum('brut','transforme','intrant','materiel') NOT NULL DEFAULT 'brut',
  `unite_mesure_defaut_id` bigint(20) UNSIGNED DEFAULT NULL,
  `poids_kg_reference` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `statut` enum('actif','en_attente','refuse') NOT NULL DEFAULT 'actif' COMMENT 'Statut de validation du produit',
  `cree_par` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Liste fermée des produits gérés par la plateforme';

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `categorie_id`, `type_produit`, `unite_mesure_defaut_id`, `poids_kg_reference`, `description`, `actif`, `statut`, `cree_par`, `created_at`, `updated_at`) VALUES
(1, 'Manioc', 1, 'brut', NULL, NULL, NULL, 1, 'actif', NULL, '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(2, 'Soja', 1, 'brut', NULL, NULL, NULL, 1, 'actif', NULL, '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(3, 'Anacarde', 1, 'brut', NULL, NULL, NULL, 1, 'actif', NULL, '2026-09-30 08:35:58', '2026-10-15 06:18:25'),
(4, 'Karité', 1, 'brut', NULL, NULL, NULL, 1, 'actif', NULL, '2026-09-30 08:35:58', '2026-09-30 08:35:58'),
(6, 'Huile de palme', 2, 'transforme', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(7, 'Farine de manioc', 2, 'transforme', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(8, 'Savon de karité', 2, 'transforme', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(9, 'Jus d\'anacarde', 2, 'transforme', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(10, 'Semences', 3, 'intrant', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(11, 'Engrais', 3, 'intrant', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(12, 'Machette', 4, 'materiel', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(13, 'Houe', 4, 'materiel', NULL, NULL, NULL, 1, 'actif', NULL, '2026-10-14 11:25:14', '2026-10-14 11:25:14'),
(14, 'Sorgho', 1, 'brut', NULL, NULL, NULL, 1, 'actif', 11, '2026-10-15 06:19:11', '2026-10-15 06:19:11');

-- --------------------------------------------------------

--
-- Structure de la table `propositions_produits`
--

CREATE TABLE `propositions_produits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom_saisi` varchar(100) NOT NULL COMMENT 'Nom tel que saisi par l utilisateur',
  `type_produit` enum('brut','transforme','intrant','materiel') NOT NULL DEFAULT 'brut' COMMENT 'Type du produit proposé',
  `propose_par` bigint(20) UNSIGNED NOT NULL,
  `propose_par_role` enum('producteur','unite_transformation') DEFAULT NULL COMMENT 'Rôle du proposeur',
  `statut` enum('a_verifier','valide','refuse') NOT NULL DEFAULT 'a_verifier',
  `nom_corrige` varchar(100) DEFAULT NULL COMMENT 'Nom corrigé par l admin',
  `produit_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Produit final créé si validé',
  `motif_refus` text DEFAULT NULL,
  `traite_par` bigint(20) UNSIGNED DEFAULT NULL,
  `traite_le` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Propositions de produits à valider par le Super admin';

--
-- Déchargement des données de la table `propositions_produits`
--

INSERT INTO `propositions_produits` (`id`, `nom_saisi`, `type_produit`, `propose_par`, `propose_par_role`, `statut`, `nom_corrige`, `produit_id`, `motif_refus`, `traite_par`, `traite_le`, `created_at`, `updated_at`) VALUES
(1, 'Fruits', 'brut', 22, 'producteur', 'valide', 'Fruits', NULL, NULL, 11, '2026-10-15 06:19:30', '2026-10-14 17:20:09', '2026-10-15 06:19:30');

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
('nF3ctlBid5gJakjEbDjZ6uzBXAAFK0cE84KW0ESt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ2pLOG5VdGRNSnBYQ1BYRGN0WGxDdWlWQ2ZrR1h6aDQzSEdNUDBtayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9fQ==', 1792152679),
('rg9LvuKJ0nvM180neVbH8BnklKCBbmYtq1RSdgfF', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNFZCaHVPd2Z4M0JZWDV0eDlYWmFPS2tqMklqeXZZdDhUQ0wwTDU3diI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9jb21wdGVzLzI0IjtzOjU6InJvdXRlIjtzOjE4OiJhZG1pbi5jb21wdGVzLnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiNDhhZjc3YmQ5MWYzNmRlODUwMmJiM2VlOWRiZDllMDJkNjBkOGNjYmQ2MjAyY2ExYzFjMDgwZDViNDZjZjYyYSI7fQ==', 1792152679);

-- --------------------------------------------------------

--
-- Structure de la table `stocks`
--

CREATE TABLE `stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producteur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unite_id` bigint(20) UNSIGNED DEFAULT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `unite_mesure_id` bigint(20) UNSIGNED NOT NULL,
  `poids_unitaire_kg` decimal(10,2) DEFAULT NULL COMMENT 'Valeur d''un contenant dans l''unité de référence (kg ou litre)',
  `unite_reference_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Unité de référence du poids (kg, g, L, ml)',
  `quantite_disponible` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantite_reservee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `seuil_alerte` decimal(12,2) DEFAULT NULL COMMENT 'Alerte si stock <= seuil',
  `prix_unitaire` decimal(12,2) DEFAULT NULL COMMENT 'Prix par unité de mesure',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Déchargement des données de la table `stocks`
--

INSERT INTO `stocks` (`id`, `producteur_id`, `unite_id`, `produit_id`, `unite_mesure_id`, `poids_unitaire_kg`, `unite_reference_id`, `quantite_disponible`, `quantite_reservee`, `seuil_alerte`, `prix_unitaire`, `created_at`, `updated_at`) VALUES
(1, 5, NULL, 1, 5, 25.00, NULL, 8.00, 5.00, 2.00, 5000.00, '2026-10-15 08:10:56', '2026-10-15 13:17:01'),
(2, 5, NULL, 2, 3, 25.00, NULL, 50.00, 8.00, 5.00, 3000.00, '2026-10-16 07:08:28', '2026-10-16 07:14:29'),
(3, 5, NULL, 14, 3, 5.00, NULL, 10.00, 0.00, 5.00, 2000.00, '2026-10-16 07:12:43', '2026-10-16 07:12:43'),
(4, NULL, 2, 6, 11, 1.00, 6, 25.00, 10.00, 5.00, 1600.00, '2026-10-16 07:17:49', '2026-10-16 09:27:00'),
(5, NULL, 2, 7, 14, 10.00, NULL, 20.00, 0.00, 5.00, 1000.00, '2026-10-16 07:19:21', '2026-10-16 07:19:21');

-- --------------------------------------------------------

--
-- Structure de la table `unites_mesure`
--

CREATE TABLE `unites_mesure` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` enum('mesure','contenant') NOT NULL DEFAULT 'mesure' COMMENT 'mesure = kg/tonne/litre, contenant = sac/panier/bidon',
  `nom` varchar(50) NOT NULL,
  `symbole` varchar(10) DEFAULT NULL,
  `est_reference` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true pour kg (unité de référence SI)',
  `unite_reference_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Unité de mesure de référence (Litre pour bouteille, kg pour sac)',
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Unités de mesure configurables (kg, tonne, sac, panier, bassine...)';

--
-- Déchargement des données de la table `unites_mesure`
--

INSERT INTO `unites_mesure` (`id`, `code`, `type`, `nom`, `symbole`, `est_reference`, `unite_reference_id`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'kg', 'mesure', 'Kilogramme', 'kg', 1, NULL, 1, '2026-10-14 10:10:27', '2026-10-14 10:10:27'),
(2, 'tonne', 'mesure', 'Tonne', 'T', 0, NULL, 1, '2026-10-14 10:10:27', '2026-10-14 10:10:27'),
(3, 'sac', 'contenant', 'Sac', 'sac', 0, 1, 1, '2026-10-14 10:10:27', '2026-10-14 10:10:27'),
(4, 'panier', 'contenant', 'Panier', 'pan', 0, 1, 1, '2026-10-14 10:10:27', '2026-10-14 10:10:27'),
(5, 'bassine', 'contenant', 'Bassine', 'bas', 0, 1, 1, '2026-10-14 10:10:27', '2026-10-14 10:10:27'),
(6, 'litre', 'mesure', 'Litre', 'L', 0, NULL, 1, '2026-10-14 10:10:27', '2026-10-14 10:10:27'),
(7, 'tas', 'mesure', 'Tas', 'tas', 0, NULL, 1, '2026-10-15 07:24:55', '2026-10-15 07:24:55'),
(8, 'gramme', 'mesure', 'Gramme', 'g', 0, NULL, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08'),
(9, 'millilitre', 'mesure', 'Millilitre', 'ml', 0, NULL, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08'),
(10, 'm3', 'mesure', 'Mètre cube', 'm³', 0, NULL, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08'),
(11, 'bouteille', 'contenant', 'Bouteille', 'bout.', 0, 6, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08'),
(12, 'bidon', 'contenant', 'Bidon', 'bid.', 0, 6, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08'),
(13, 'sachet', 'contenant', 'Sachet', 'sach.', 0, 6, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08'),
(14, 'carton', 'contenant', 'Carton', 'cart.', 0, 1, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08'),
(15, 'caisse', 'contenant', 'Caisse', 'cais.', 0, 1, 1, '2026-10-15 11:34:08', '2026-10-15 11:34:08');

-- --------------------------------------------------------

--
-- Structure de la table `unites_transformation`
--

CREATE TABLE `unites_transformation` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `zone_collecte_rayon_km` decimal(6,2) DEFAULT NULL COMMENT 'Rayon de collecte préféré, alternative aux communes ciblées',
  `score_fiabilite` enum('nouveau','a_surveiller','fiable') NOT NULL DEFAULT 'nouveau' COMMENT 'Basé sur l historique de paiement (RG-017, RG-018)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Profil unité de transformation (F-02)';

--
-- Déchargement des données de la table `unites_transformation`
--

INSERT INTO `unites_transformation` (`id`, `utilisateur_id`, `zone_collecte_rayon_km`, `score_fiabilite`, `created_at`, `updated_at`) VALUES
(2, 17, 60.00, 'nouveau', '2026-09-30 08:30:03', '2026-09-30 17:25:44'),
(3, 18, 45.00, 'nouveau', '2026-09-30 08:30:04', '2026-09-30 08:30:04'),
(4, 19, 50.00, 'nouveau', '2026-09-30 08:30:05', '2026-09-30 08:30:05');

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
(2, 6),
(2, 7),
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
  `email` varchar(150) DEFAULT NULL,
  `nom_responsable` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) NOT NULL COMMENT 'Format béninois +229XXXXXXXX — identifiant unique du compte',
  `whatsapp` varchar(20) DEFAULT NULL,
  `npi` varchar(20) DEFAULT NULL,
  `rccm` varchar(50) DEFAULT NULL,
  `ifu` varchar(50) DEFAULT NULL,
  `document_type` enum('cip','passeport','ifu') DEFAULT NULL,
  `document_chemin` varchar(255) DEFAULT NULL,
  `document_verifie` tinyint(1) NOT NULL DEFAULT 0,
  `type_compte` enum('producteur','unite_transformation','population','admin') NOT NULL,
  `commune_id` bigint(20) UNSIGNED NOT NULL,
  `village` varchar(150) DEFAULT NULL COMMENT 'Localisation précise (village), pour les producteurs surtout',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `statut_compte` enum('nouveau','actif','suspendu') NOT NULL DEFAULT 'nouveau',
  `statut_validation` enum('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
  `motif_refus` text DEFAULT NULL,
  `valide_par` bigint(20) UNSIGNED DEFAULT NULL,
  `valide_le` timestamp NULL DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL COMMENT 'Hash du mot de passe (nullable si connexion uniquement par SMS/OTP)',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Compte générique, quel que soit le rôle (RG-003, RG-018)';

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `nom_responsable`, `telephone`, `whatsapp`, `npi`, `rccm`, `ifu`, `document_type`, `document_chemin`, `document_verifie`, `type_compte`, `commune_id`, `village`, `latitude`, `longitude`, `statut_compte`, `statut_validation`, `motif_refus`, `valide_par`, `valide_le`, `mot_de_passe`, `remember_token`, `created_at`, `updated_at`) VALUES
(11, 'Administrateur Plateforme', NULL, NULL, '+22990000000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'admin', 1, NULL, 7.7847000, 2.1882000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$qF6wl.SbSYKjheipqJBw6.fJULpyv/XcW/Te9bS/ZxmDtuuWWsRb2', '0pcr3MjwCR2AOorcsbwWbe5HHMZj9ZJcsuJIzFctgAV3IqOP07UmcEjtHjFB', '2026-09-30 08:30:00', '2026-09-30 12:37:58'),
(12, 'Agent MAEP Collines', NULL, NULL, '+22991000000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'admin', 1, NULL, 7.7802000, 2.1797000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$7CsY/3YLTLL4gTuyj65cyuF88jg8JTl0uHQ6DpRwmeutlqDIXHXzy', 'ipin5419KHxEVEpNTOK8QaMZKwaoU8Oclo7JXgCxJaoVX8qr4gECbldO9wtS', '2026-09-30 08:30:01', '2026-09-30 12:37:58'),
(13, 'Fifamè AGBODJAN', NULL, NULL, '+22997000001', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'producteur', 4, 'Savalou-Agbado', 7.9314000, 1.9611000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$MLR2vud/zBvaxK/EueJlD.VyeD8fs.8CYHwVc6PNnCuSuqzebCW8W', 'HiYRDhhIdILtkutMks5TMAlviiBfbhCJxO3vwXN1f2jOkxWOa5mX1hn6MhwB', '2026-09-30 08:30:01', '2026-09-30 12:37:58'),
(14, 'Jean HOUNKPATIN', NULL, NULL, '+22997000002', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'producteur', 1, 'Paouignan', 7.7836000, 2.1898000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$NacDYv1hO2lOH.eZ3Cf9HO3Lr6jPHvU3x52PHsO1dQ8JfRkEed.ia', NULL, '2026-09-30 08:30:02', '2026-09-30 12:37:58'),
(15, 'Marie DOSSOU', NULL, NULL, '+22997000003', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'producteur', 5, 'Savè Centre', 8.0382000, 2.4900000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$/R3UwIW7GurCZ.u3XcLX5uyHhGldPVNZcRmPSdBhglUDwKgnb8HyC', NULL, '2026-09-30 08:30:02', '2026-09-30 12:37:58'),
(16, 'Pierre ADJOVI', NULL, NULL, '+22997000004', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'producteur', 2, 'Glazoué', 7.9625000, 2.2331000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$aWYiae4QIw2vgqP.Oi.hIu25rq6cFCGe1ldL.9nAvUSqwnviN5C8W', NULL, '2026-09-30 08:30:03', '2026-09-30 12:37:58'),
(17, 'Unité de Transformation de Paouignan', NULL, NULL, '+22996000001', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'unite_transformation', 1, 'Paouignan', 7.7803000, 2.1807000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$WvQt301opXWYWec.DbWUSeDjDXNl5H0W8VdIzYdA9jPhJMVkrnOAW', 'PYPE9brWnhiQd8CUN1h1PDwf8HeYciTSGA4H34VaNNkbT0GZJKMF8egrnpj5', '2026-09-30 08:30:03', '2026-09-30 12:37:58'),
(18, 'Coopérative de Transformation de Savè', NULL, NULL, '+22996000002', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'unite_transformation', 5, 'Savè', 8.0375000, 2.4854000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$B8tSk4pTYOq6WeyxgBlQxONcjFzdgCLZ/F1XAQ49uEmvMcyt19j/G', NULL, '2026-09-30 08:30:04', '2026-09-30 12:37:58'),
(19, 'Unité Anacarde Glazoué', NULL, NULL, '+22996000003', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'unite_transformation', 2, 'Glazoué', 7.9603000, 2.2406000, 'actif', 'valide', NULL, NULL, '2026-10-14 10:10:26', '$2y$12$XagRapIXTTIRyIBI2oo7q.OQwCT0Do1ijkyZq2KD9Yo/T04/5vwnm', NULL, '2026-09-30 08:30:04', '2026-09-30 12:37:58'),
(20, 'Yissegnon', NULL, NULL, '0162348404', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'producteur', 6, 'Togo', NULL, NULL, 'actif', 'en_attente', NULL, NULL, NULL, '$2y$12$1vRSKyApNlRGPqhzKotyHO9PQP80fNOA73pZts3k86K2LLSndVFI2', NULL, '2026-10-14 15:52:58', '2026-10-14 15:52:58'),
(22, 'KINDOHO Josué', NULL, NULL, '+2290154313454', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'producteur', 6, 'Djajo', NULL, NULL, 'actif', 'en_attente', NULL, NULL, NULL, '$2y$12$BkKkFrBLNSQb0D4Etg3qiOCsuznf532By0h8UNqjR.JMf9RtGyC6S', NULL, '2026-10-14 17:04:54', '2026-10-14 17:04:54'),
(23, 'KOKOU', 'kokou@gmail.com', NULL, '+22962348404', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'population', 1, NULL, NULL, NULL, 'actif', 'valide', NULL, NULL, '2026-10-15 12:51:56', '$2y$12$vE83MTwWnvK69IJoPbK76.mkcRUbw5mvo2eg2qYAjtWhkOQxK8V5y', NULL, '2026-10-15 12:51:56', '2026-10-15 12:51:56'),
(24, 'Varencia TOGO', NULL, NULL, '+2290160105619', NULL, NULL, NULL, NULL, 'cip', 'documents/24/0GxnB05xcG6iX0iI5Bv1rEhbZMIYpgV77yiAL4vS.png', 1, 'producteur', 5, 'KOKO', NULL, NULL, 'actif', 'valide', NULL, 11, '2026-10-16 10:28:00', '$2y$12$GC6bMGTT4eWScw347H/2fOqDY7DpsxjNuLksMQtL8cP.gyct1Roge', NULL, '2026-10-16 10:23:00', '2026-10-16 10:28:00');

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
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `idx_commandes_statut` (`statut`),
  ADD KEY `fk_commandes_acheteur` (`acheteur_id`),
  ADD KEY `fk_commandes_producteur` (`producteur_id`);

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
  ADD KEY `idx_dispo_besoin` (`besoin_id`),
  ADD KEY `fk_dispo_unite_reference` (`unite_reference_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Index pour la table `ligne_commandes`
--
ALTER TABLE `ligne_commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lignes_commande` (`commande_id`),
  ADD KEY `fk_lignes_offre` (`offre_id`),
  ADD KEY `fk_lignes_produit` (`produit_id`),
  ADD KEY `fk_lignes_unite` (`unite_mesure_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mouvements_stock`
--
ALTER TABLE `mouvements_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mouvements_stock` (`stock_id`),
  ADD KEY `idx_mouvements_reference` (`reference_type`,`reference_id`),
  ADD KEY `fk_mouvements_cree_par` (`cree_par`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_utilisateur` (`utilisateur_id`,`lu`);

--
-- Index pour la table `offres`
--
ALTER TABLE `offres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_offres_statut` (`statut`),
  ADD KEY `idx_offres_produit` (`produit_id`),
  ADD KEY `fk_offres_producteur` (`producteur_id`),
  ADD KEY `fk_offres_unite` (`unite_mesure_id`),
  ADD KEY `fk_offres_unite_vendeur` (`unite_id`),
  ADD KEY `fk_offres_categorie` (`categorie_id`),
  ADD KEY `fk_offres_unite_reference` (`unite_reference_id`);

--
-- Index pour la table `offre_photos`
--
ALTER TABLE `offre_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_offre_photos_offre` (`offre_id`);

--
-- Index pour la table `panier_items`
--
ALTER TABLE `panier_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_panier_user_offre` (`utilisateur_id`,`offre_id`),
  ADD KEY `fk_panier_offre` (`offre_id`);

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
  ADD UNIQUE KEY `nom` (`nom`),
  ADD KEY `fk_produits_categorie` (`categorie_id`),
  ADD KEY `fk_produits_unite_defaut` (`unite_mesure_defaut_id`),
  ADD KEY `fk_produits_cree_par` (`cree_par`),
  ADD KEY `idx_produits_statut` (`statut`);

--
-- Index pour la table `propositions_produits`
--
ALTER TABLE `propositions_produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_propositions_statut` (`statut`),
  ADD KEY `fk_propositions_propose_par` (`propose_par`),
  ADD KEY `fk_propositions_produit` (`produit_id`),
  ADD KEY `fk_propositions_traite_par` (`traite_par`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stocks_produit` (`produit_id`),
  ADD KEY `fk_stocks_unite` (`unite_mesure_id`),
  ADD KEY `idx_stocks_unite` (`unite_id`),
  ADD KEY `fk_stocks_producteur` (`producteur_id`),
  ADD KEY `fk_stocks_unite_reference` (`unite_reference_id`);

--
-- Index pour la table `unites_mesure`
--
ALTER TABLE `unites_mesure`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_unites_mesure_reference` (`unite_reference_id`);

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
  ADD UNIQUE KEY `uk_utilisateurs_npi` (`npi`),
  ADD UNIQUE KEY `uk_utilisateurs_email` (`email`),
  ADD KEY `fk_utilisateurs_commune` (`commune_id`),
  ADD KEY `idx_utilisateurs_type` (`type_compte`),
  ADD KEY `fk_utilisateurs_valide_par` (`valide_par`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `besoins`
--
ALTER TABLE `besoins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ligne_commandes`
--
ALTER TABLE `ligne_commandes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `mouvements_stock`
--
ALTER TABLE `mouvements_stock`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `offres`
--
ALTER TABLE `offres`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `offre_photos`
--
ALTER TABLE `offre_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `panier_items`
--
ALTER TABLE `panier_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `producteurs`
--
ALTER TABLE `producteurs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `propositions_produits`
--
ALTER TABLE `propositions_produits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `unites_mesure`
--
ALTER TABLE `unites_mesure`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `fk_commandes_acheteur` FOREIGN KEY (`acheteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_commandes_producteur` FOREIGN KEY (`producteur_id`) REFERENCES `producteurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `disponibilites`
--
ALTER TABLE `disponibilites`
  ADD CONSTRAINT `fk_dispo_besoin` FOREIGN KEY (`besoin_id`) REFERENCES `besoins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dispo_producteur` FOREIGN KEY (`producteur_id`) REFERENCES `producteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dispo_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  ADD CONSTRAINT `fk_dispo_unite_reference` FOREIGN KEY (`unite_reference_id`) REFERENCES `unites_mesure` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ligne_commandes`
--
ALTER TABLE `ligne_commandes`
  ADD CONSTRAINT `fk_lignes_commande` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lignes_offre` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_lignes_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lignes_unite` FOREIGN KEY (`unite_mesure_id`) REFERENCES `unites_mesure` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `mouvements_stock`
--
ALTER TABLE `mouvements_stock`
  ADD CONSTRAINT `fk_mouvements_cree_par` FOREIGN KEY (`cree_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mouvements_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offres`
--
ALTER TABLE `offres`
  ADD CONSTRAINT `fk_offres_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_offres_producteur` FOREIGN KEY (`producteur_id`) REFERENCES `producteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offres_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offres_unite` FOREIGN KEY (`unite_mesure_id`) REFERENCES `unites_mesure` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offres_unite_reference` FOREIGN KEY (`unite_reference_id`) REFERENCES `unites_mesure` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_offres_unite_vendeur` FOREIGN KEY (`unite_id`) REFERENCES `unites_transformation` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offre_photos`
--
ALTER TABLE `offre_photos`
  ADD CONSTRAINT `fk_offre_photos_offre` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `panier_items`
--
ALTER TABLE `panier_items`
  ADD CONSTRAINT `fk_panier_offre` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_panier_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

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
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `fk_produits_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_produits_cree_par` FOREIGN KEY (`cree_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_produits_unite_defaut` FOREIGN KEY (`unite_mesure_defaut_id`) REFERENCES `unites_mesure` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `propositions_produits`
--
ALTER TABLE `propositions_produits`
  ADD CONSTRAINT `fk_propositions_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_propositions_propose_par` FOREIGN KEY (`propose_par`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_propositions_traite_par` FOREIGN KEY (`traite_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `fk_stocks_producteur` FOREIGN KEY (`producteur_id`) REFERENCES `producteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stocks_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stocks_to_unite` FOREIGN KEY (`unite_id`) REFERENCES `unites_transformation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stocks_unite_mesure` FOREIGN KEY (`unite_mesure_id`) REFERENCES `unites_mesure` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stocks_unite_reference` FOREIGN KEY (`unite_reference_id`) REFERENCES `unites_mesure` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `unites_mesure`
--
ALTER TABLE `unites_mesure`
  ADD CONSTRAINT `fk_unites_mesure_reference` FOREIGN KEY (`unite_reference_id`) REFERENCES `unites_mesure` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `fk_utilisateurs_commune` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`),
  ADD CONSTRAINT `fk_utilisateurs_valide_par` FOREIGN KEY (`valide_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
