USE `inclucity_db`;

ALTER TABLE `usuarios`
  ADD COLUMN IF NOT EXISTS `tipo_usuario` enum('usuario','admin') NOT NULL DEFAULT 'usuario' AFTER `senha`;

-- No ambiente local, garante um administrador sem alterar contas quando um já existe.
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
