USE `inclucity_db`;

-- Atualiza bancos criados pelas primeiras versoes sem apagar os dados existentes.
ALTER TABLE `usuarios`
  MODIFY `celular` varchar(20) NULL,
  MODIFY `cpf` varchar(14) NULL,
  MODIFY `senha` varchar(255) NULL,
  ADD COLUMN IF NOT EXISTS `oauth_provider` varchar(20) NULL AFTER `senha`,
  ADD COLUMN IF NOT EXISTS `oauth_subject` varchar(255) NULL AFTER `oauth_provider`,
  ADD COLUMN IF NOT EXISTS `tipo_usuario` enum('usuario','admin') NOT NULL DEFAULT 'usuario' AFTER `oauth_subject`,
  ADD UNIQUE INDEX IF NOT EXISTS `oauth_identity` (`oauth_provider`, `oauth_subject`);

ALTER TABLE `locais`
  MODIFY `usuario_id` int(11) NULL,
  ADD COLUMN IF NOT EXISTS `numero` varchar(20) NOT NULL DEFAULT 'S/N' AFTER `endereco`,
  ADD COLUMN IF NOT EXISTS `complemento` varchar(100) NULL AFTER `numero`,
  ADD COLUMN IF NOT EXISTS `bairro` varchar(100) NOT NULL DEFAULT 'Nao informado' AFTER `complemento`,
  ADD COLUMN IF NOT EXISTS `cidade` varchar(100) NOT NULL DEFAULT 'Sao Jose dos Campos' AFTER `bairro`,
  ADD COLUMN IF NOT EXISTS `estado` char(2) NOT NULL DEFAULT 'SP' AFTER `cidade`,
  ADD COLUMN IF NOT EXISTS `cep` char(8) NOT NULL DEFAULT '00000000' AFTER `estado`,
  ADD COLUMN IF NOT EXISTS `categorias` longtext NULL AFTER `longitude`,
  ADD COLUMN IF NOT EXISTS `deficiencias` longtext NULL AFTER `categorias`,
  ADD COLUMN IF NOT EXISTS `outra_categoria` varchar(100) NULL AFTER `deficiencias`,
  ADD COLUMN IF NOT EXISTS `recursos` longtext NULL AFTER `outra_categoria`,
  ADD COLUMN IF NOT EXISTS `outro_recurso` varchar(150) NULL AFTER `recursos`,
  ADD COLUMN IF NOT EXISTS `observacoes` varchar(2000) NULL AFTER `outro_recurso`,
  ADD COLUMN IF NOT EXISTS `site` varchar(255) NULL AFTER `observacoes`,
  ADD COLUMN IF NOT EXISTS `instagram` varchar(100) NULL AFTER `site`,
  ADD COLUMN IF NOT EXISTS `telefone` varchar(30) NULL AFTER `instagram`,
  ADD COLUMN IF NOT EXISTS `horario_funcionamento` varchar(255) NULL AFTER `telefone`;

-- Migra colunas antigas somente quando elas realmente existirem.
SET @possui_tipo_antigo = (
  SELECT COUNT(*) FROM `information_schema`.`COLUMNS`
  WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'locais' AND `COLUMN_NAME` = 'tipo'
);
SET @migrar_tipo_antigo = IF(
  @possui_tipo_antigo > 0,
  'UPDATE `locais` SET `categorias` = JSON_ARRAY(`tipo`) WHERE (`categorias` IS NULL OR `categorias` = '''') AND `tipo` IS NOT NULL AND `tipo` <> ''''',
  'SELECT 1'
);
PREPARE `migracao_tipo` FROM @migrar_tipo_antigo;
EXECUTE `migracao_tipo`;
DEALLOCATE PREPARE `migracao_tipo`;

SET @possui_deficiencia_antiga = (
  SELECT COUNT(*) FROM `information_schema`.`COLUMNS`
  WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'locais' AND `COLUMN_NAME` = 'deficiencia'
);
SET @migrar_deficiencia_antiga = IF(
  @possui_deficiencia_antiga > 0,
  'UPDATE `locais` SET `deficiencias` = JSON_ARRAY(`deficiencia`) WHERE (`deficiencias` IS NULL OR `deficiencias` = '''') AND `deficiencia` IS NOT NULL AND `deficiencia` <> ''''',
  'SELECT 1'
);
PREPARE `migracao_deficiencia` FROM @migrar_deficiencia_antiga;
EXECUTE `migracao_deficiencia`;
DEALLOCATE PREPARE `migracao_deficiencia`;

SET @possui_comentario_antigo = (
  SELECT COUNT(*) FROM `information_schema`.`COLUMNS`
  WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'locais' AND `COLUMN_NAME` = 'comentario'
);
SET @migrar_comentario_antigo = IF(
  @possui_comentario_antigo > 0,
  'UPDATE `locais` SET `observacoes` = `comentario` WHERE (`observacoes` IS NULL OR `observacoes` = '''') AND `comentario` IS NOT NULL AND `comentario` <> ''''',
  'SELECT 1'
);
PREPARE `migracao_comentario` FROM @migrar_comentario_antigo;
EXECUTE `migracao_comentario`;
DEALLOCATE PREPARE `migracao_comentario`;

UPDATE `locais` SET `categorias` = '[]'
WHERE `categorias` IS NULL OR `categorias` = '' OR JSON_VALID(`categorias`) = 0;
UPDATE `locais` SET `categorias` = '[]' WHERE JSON_TYPE(`categorias`) <> 'ARRAY';

UPDATE `locais` SET `deficiencias` = '[]'
WHERE `deficiencias` IS NULL OR `deficiencias` = '' OR JSON_VALID(`deficiencias`) = 0;
UPDATE `locais` SET `deficiencias` = '[]' WHERE JSON_TYPE(`deficiencias`) <> 'ARRAY';

UPDATE `locais` SET `recursos` = '[]'
WHERE `recursos` IS NULL OR `recursos` = '' OR JSON_VALID(`recursos`) = 0;
UPDATE `locais` SET `recursos` = '[]' WHERE JSON_TYPE(`recursos`) <> 'ARRAY';

ALTER TABLE `locais`
  MODIFY `categorias` longtext NOT NULL,
  MODIFY `deficiencias` longtext NOT NULL DEFAULT '[]',
  MODIFY `recursos` longtext NOT NULL,
  MODIFY `status` enum('pendente','em_analise','aprovado','reprovado','recusado','mais_informacoes') NOT NULL DEFAULT 'pendente';

UPDATE `locais` SET `status` = 'reprovado' WHERE `status` = 'recusado';

ALTER TABLE `locais`
  MODIFY `status` enum('pendente','em_analise','aprovado','reprovado','mais_informacoes') NOT NULL DEFAULT 'pendente';

CREATE TABLE IF NOT EXISTS `local_fotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_id` int(11) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fotos_local` (`local_id`),
  CONSTRAINT `fk_fotos_local` FOREIGN KEY (`local_id`) REFERENCES `locais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `local_fotos`
  MODIFY `local_id` int(11) NOT NULL,
  MODIFY `arquivo` varchar(255) NOT NULL,
  ADD INDEX IF NOT EXISTS `idx_fotos_local` (`local_id`);

DELETE `f`
FROM `local_fotos` AS `f`
LEFT JOIN `locais` AS `l` ON `l`.`id` = `f`.`local_id`
WHERE `l`.`id` IS NULL;

SET @possui_fk_fotos_local = (
  SELECT COUNT(*) FROM `information_schema`.`REFERENTIAL_CONSTRAINTS`
  WHERE `CONSTRAINT_SCHEMA` = DATABASE() AND `CONSTRAINT_NAME` = 'fk_fotos_local'
);
SET @criar_fk_fotos_local = IF(
  @possui_fk_fotos_local = 0,
  'ALTER TABLE `local_fotos` ADD CONSTRAINT `fk_fotos_local` FOREIGN KEY (`local_id`) REFERENCES `locais` (`id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE `criacao_fk_fotos_local` FROM @criar_fk_fotos_local;
EXECUTE `criacao_fk_fotos_local`;
DEALLOCATE PREPARE `criacao_fk_fotos_local`;

-- Garante os índices e relacionamentos esperados pelo esquema atual.
ALTER TABLE `locais`
  ADD INDEX IF NOT EXISTS `idx_locais_usuario` (`usuario_id`);

UPDATE `locais` AS `l`
LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `l`.`usuario_id`
SET `l`.`usuario_id` = NULL
WHERE `l`.`usuario_id` IS NOT NULL AND `u`.`id` IS NULL;

SET @possui_fk_locais_usuario = (
  SELECT COUNT(*) FROM `information_schema`.`REFERENTIAL_CONSTRAINTS`
  WHERE `CONSTRAINT_SCHEMA` = DATABASE() AND `CONSTRAINT_NAME` = 'fk_locais_usuario'
);
SET @criar_fk_locais_usuario = IF(
  @possui_fk_locais_usuario = 0,
  'ALTER TABLE `locais` ADD CONSTRAINT `fk_locais_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE `criacao_fk_locais_usuario` FROM @criar_fk_locais_usuario;
EXECUTE `criacao_fk_locais_usuario`;
DEALLOCATE PREPARE `criacao_fk_locais_usuario`;

-- Habilita notas e comentários nos bancos atualizados a partir de versões antigas.
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

-- A criação do primeiro administrador fica na migração específica admin_migration.sql.
