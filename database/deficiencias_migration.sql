USE `inclucity_db`;

ALTER TABLE `locais`
  ADD COLUMN IF NOT EXISTS `deficiencias` longtext NULL AFTER `categorias`;

-- Preserva o valor da coluna singular usada pelas primeiras versões, caso ela exista.
SET @possui_deficiencia_antiga = (
  SELECT COUNT(*)
  FROM `information_schema`.`COLUMNS`
  WHERE `TABLE_SCHEMA` = DATABASE()
    AND `TABLE_NAME` = 'locais'
    AND `COLUMN_NAME` = 'deficiencia'
);

SET @migrar_deficiencia_antiga = IF(
  @possui_deficiencia_antiga > 0,
  'UPDATE `locais` SET `deficiencias` = JSON_ARRAY(`deficiencia`) WHERE (`deficiencias` IS NULL OR `deficiencias` = '''') AND `deficiencia` IS NOT NULL AND `deficiencia` <> ''''',
  'SELECT 1'
);

PREPARE `migracao_deficiencia` FROM @migrar_deficiencia_antiga;
EXECUTE `migracao_deficiencia`;
DEALLOCATE PREPARE `migracao_deficiencia`;

-- Substitui valores ausentes ou JSON inválido por um array vazio.
UPDATE `locais`
SET `deficiencias` = '[]'
WHERE `deficiencias` IS NULL
   OR `deficiencias` = ''
   OR JSON_VALID(`deficiencias`) = 0;

-- A aplicação trabalha exclusivamente com arrays JSON.
UPDATE `locais`
SET `deficiencias` = '[]'
WHERE JSON_TYPE(`deficiencias`) <> 'ARRAY';

ALTER TABLE `locais`
  MODIFY `deficiencias` longtext NOT NULL DEFAULT '[]';
