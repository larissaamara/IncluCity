-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/08/2026 às 20:11
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `inclucity_db`
--

CREATE DATABASE IF NOT EXISTS `inclucity_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `inclucity_db`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `oauth_provider` varchar(20) DEFAULT NULL,
  `oauth_subject` varchar(255) DEFAULT NULL,
  `tipo_usuario` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `oauth_identity` (`oauth_provider`, `oauth_subject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `locais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` char(2) NOT NULL,
  `cep` char(8) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `categorias` longtext NOT NULL,
  `deficiencias` longtext NOT NULL DEFAULT '[]',
  `outra_categoria` varchar(100) DEFAULT NULL,
  `recursos` longtext NOT NULL,
  `outro_recurso` varchar(150) DEFAULT NULL,
  `observacoes` varchar(2000) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `horario_funcionamento` varchar(255) DEFAULT NULL,
  `status` enum('pendente','em_analise','aprovado','reprovado','mais_informacoes') NOT NULL DEFAULT 'pendente',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_locais_usuario` (`usuario_id`),
  KEY `idx_locais_status_data` (`status`, `data_cadastro`),
  CONSTRAINT `chk_locais_estado` CHECK (`estado` REGEXP '^[A-Z]{2}$'),
  CONSTRAINT `chk_locais_cep` CHECK (`cep` REGEXP '^[0-9]{8}$'),
  CONSTRAINT `chk_locais_latitude` CHECK (`latitude` BETWEEN -90 AND 90),
  CONSTRAINT `chk_locais_longitude` CHECK (`longitude` BETWEEN -180 AND 180),
  CONSTRAINT `chk_locais_categorias` CHECK (JSON_VALID(`categorias`) AND JSON_TYPE(`categorias`) = 'ARRAY'),
  CONSTRAINT `chk_locais_deficiencias` CHECK (JSON_VALID(`deficiencias`) AND JSON_TYPE(`deficiencias`) = 'ARRAY'),
  CONSTRAINT `chk_locais_recursos` CHECK (JSON_VALID(`recursos`) AND JSON_TYPE(`recursos`) = 'ARRAY'),
  CONSTRAINT `fk_locais_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `local_fotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_id` int(11) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fotos_local` (`local_id`),
  CONSTRAINT `fk_fotos_local` FOREIGN KEY (`local_id`) REFERENCES `locais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Avaliações públicas dos locais. Cada usuário pode avaliar um local uma vez
-- e atualizar sua própria nota ou comentário posteriormente.
CREATE TABLE IF NOT EXISTS `avaliacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nota` tinyint(3) unsigned NOT NULL,
  `comentario` varchar(1500) NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `avaliacao_usuario_local` (`local_id`, `usuario_id`),
  KEY `idx_avaliacoes_usuario` (`usuario_id`),
  CONSTRAINT `fk_avaliacoes_local` FOREIGN KEY (`local_id`) REFERENCES `locais` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_avaliacoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_avaliacoes_nota` CHECK (`nota` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
