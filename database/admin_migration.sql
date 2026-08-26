USE `inclucity_db`;

ALTER TABLE `usuarios`
  ADD COLUMN IF NOT EXISTS `tipo_usuario` enum('usuario','admin') NOT NULL DEFAULT 'usuario' AFTER `senha`;

-- No ambiente local, torna a primeira conta cadastrada administradora.
UPDATE `usuarios`
SET `tipo_usuario` = 'admin'
WHERE `id` = (SELECT primeiro_id FROM (SELECT MIN(`id`) AS primeiro_id FROM `usuarios`) AS primeira_conta);
