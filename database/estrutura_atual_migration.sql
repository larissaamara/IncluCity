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

UPDATE `locais`
SET `categorias` = JSON_ARRAY(`tipo`)
WHERE (`categorias` IS NULL OR `categorias` = '') AND `tipo` IS NOT NULL AND `tipo` <> '';

UPDATE `locais`
SET `deficiencias` = JSON_ARRAY(`deficiencia`)
WHERE (`deficiencias` IS NULL OR `deficiencias` = '') AND `deficiencia` IS NOT NULL AND `deficiencia` <> '';

UPDATE `locais`
SET `recursos` = JSON_ARRAY(),
    `observacoes` = COALESCE(NULLIF(`observacoes`, ''), `comentario`)
WHERE `recursos` IS NULL OR `recursos` = '';

UPDATE `locais` SET `categorias` = JSON_ARRAY() WHERE `categorias` IS NULL OR `categorias` = '';
UPDATE `locais` SET `deficiencias` = JSON_ARRAY() WHERE `deficiencias` IS NULL OR `deficiencias` = '';

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

-- Garante que exista um administrador local sem criar senhas padrao.
UPDATE `usuarios`
SET `tipo_usuario` = 'admin'
WHERE `id` = (
  SELECT `primeiro_id`
  FROM (SELECT MIN(`id`) AS `primeiro_id` FROM `usuarios`) AS `primeira_conta`
)
AND NOT EXISTS (
  SELECT 1
  FROM (SELECT `tipo_usuario` FROM `usuarios`) AS `perfis`
  WHERE `tipo_usuario` = 'admin'
);
