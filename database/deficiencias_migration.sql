USE `inclucity_db`;

ALTER TABLE `locais`
  ADD COLUMN IF NOT EXISTS `deficiencias` longtext NULL AFTER `categorias`;

UPDATE `locais`
SET `deficiencias` = '[]'
WHERE `deficiencias` IS NULL OR `deficiencias` = '';
