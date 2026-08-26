ALTER TABLE `usuarios`
  MODIFY `celular` varchar(20) NULL,
  MODIFY `cpf` varchar(14) NULL,
  MODIFY `senha` varchar(255) NULL,
  ADD COLUMN `oauth_provider` varchar(20) NULL AFTER `senha`,
  ADD COLUMN `oauth_subject` varchar(255) NULL AFTER `oauth_provider`,
  ADD UNIQUE KEY `oauth_identity` (`oauth_provider`, `oauth_subject`);
