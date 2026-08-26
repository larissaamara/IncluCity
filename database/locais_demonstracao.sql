USE `inclucity_db`;

-- Locais fictícios para demonstrar o mapa e seus filtros.
-- O WHERE NOT EXISTS permite executar este arquivo mais de uma vez sem duplicar registros.

-- Usuários fictícios cadastrados para representar contribuições da comunidade.
-- A senha é um hash aleatório sem senha conhecida e não permite acesso às contas.
INSERT INTO `usuarios` (`nome`, `email`, `celular`, `cpf`, `senha`, `tipo_usuario`)
SELECT 'Mariana Costa', 'mariana.costa@example.com', '(12) 90000-1001', '900.000.001-00', '$2y$10$LAGeRO49Ofa16zB2tp607.rqHlFkiY80lWbtk.las9zBaDHiMRDz.', 'usuario'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `email` = 'mariana.costa@example.com');

INSERT INTO `usuarios` (`nome`, `email`, `celular`, `cpf`, `senha`, `tipo_usuario`)
SELECT 'Rafael Mendes', 'rafael.mendes@example.com', '(12) 90000-1002', '900.000.002-00', '$2y$10$LAGeRO49Ofa16zB2tp607.rqHlFkiY80lWbtk.las9zBaDHiMRDz.', 'usuario'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `email` = 'rafael.mendes@example.com');

INSERT INTO `usuarios` (`nome`, `email`, `celular`, `cpf`, `senha`, `tipo_usuario`)
SELECT 'Camila Nogueira', 'camila.nogueira@example.com', '(12) 90000-1003', '900.000.003-00', '$2y$10$LAGeRO49Ofa16zB2tp607.rqHlFkiY80lWbtk.las9zBaDHiMRDz.', 'usuario'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `email` = 'camila.nogueira@example.com');

SET @usuario_mariana = (SELECT `id` FROM `usuarios` WHERE `email` = 'mariana.costa@example.com' LIMIT 1);
SET @usuario_rafael = (SELECT `id` FROM `usuarios` WHERE `email` = 'rafael.mendes@example.com' LIMIT 1);
SET @usuario_camila = (SELECT `id` FROM `usuarios` WHERE `email` = 'camila.nogueira@example.com' LIMIT 1);

INSERT INTO `locais`
  (`usuario_id`, `nome`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `latitude`, `longitude`, `categorias`, `recursos`, `observacoes`, `status`)
SELECT @usuario_mariana, 'Café Inclusivo (Demonstração)', 'Avenida São João', '100', 'Jardim Esplanada', 'São José dos Campos', 'SP', '12242000', -23.1969000, -45.8958000,
  '["Restaurante"]',
  '["Rampa de acesso","Entrada acessível","Banheiro acessível","Braile","Cão-guia permitido"]',
  'Local fictício criado somente para demonstrar o funcionamento dos filtros do IncluCity.', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome` = 'Café Inclusivo (Demonstração)');

INSERT INTO `locais`
  (`usuario_id`, `nome`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `latitude`, `longitude`, `categorias`, `recursos`, `observacoes`, `status`)
SELECT @usuario_rafael, 'Shopping Acessível (Demonstração)', 'Avenida Andrômeda', '500', 'Jardim Satélite', 'São José dos Campos', 'SP', '12230000', -23.2246000, -45.8907000,
  '["Shopping","Comércio"]',
  '["Rampa de acesso","Elevador acessível","Banheiro acessível","Vaga acessível","Piso tátil","Espaço para cadeira de rodas"]',
  'Local fictício criado somente para demonstrar o funcionamento dos filtros do IncluCity.', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome` = 'Shopping Acessível (Demonstração)');

INSERT INTO `locais`
  (`usuario_id`, `nome`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `latitude`, `longitude`, `categorias`, `recursos`, `observacoes`, `status`)
SELECT @usuario_camila, 'Centro Cultural para Todos (Demonstração)', 'Praça Afonso Pena', '50', 'Centro', 'São José dos Campos', 'SP', '12210090', -23.1865000, -45.8841000,
  '["Espaço cultural","Instituição/serviço"]',
  '["Libras","Audiodescrição","Piso tátil","Comunicação acessível","Sala de conforto"]',
  'Local fictício criado somente para demonstrar o funcionamento dos filtros do IncluCity.', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome` = 'Centro Cultural para Todos (Demonstração)');

INSERT INTO `locais`
  (`usuario_id`, `nome`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `latitude`, `longitude`, `categorias`, `recursos`, `observacoes`, `status`)
SELECT @usuario_mariana, 'Igreja Comunidade Aberta (Demonstração)', 'Rua Paraibuna', '300', 'Jardim São Dimas', 'São José dos Campos', 'SP', '12245120', -23.2024000, -45.8898000,
  '["Igreja"]',
  '["Entrada acessível","Rampa de acesso","Libras","Atendimento prioritário","Vaga acessível"]',
  'Solicitação recusada porque não foram enviadas imagens que comprovem os recursos de acessibilidade informados.', 'reprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome` = 'Igreja Comunidade Aberta (Demonstração)');

INSERT INTO `locais`
  (`usuario_id`, `nome`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `latitude`, `longitude`, `categorias`, `recursos`, `observacoes`, `status`)
SELECT @usuario_rafael, 'Instituto Cidadania (Demonstração)', 'Avenida Cassiano Ricardo', '800', 'Parque Residencial Aquarius', 'São José dos Campos', 'SP', '12246000', -23.2137000, -45.9108000,
  '["Instituição/serviço","Órgão público"]',
  '["Balcão acessível","Sinalização acessível","Braile","Libras","Comunicação acessível","Atendimento prioritário"]',
  'Local fictício criado somente para demonstrar o funcionamento dos filtros do IncluCity.', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome` = 'Instituto Cidadania (Demonstração)');

UPDATE `locais`
SET `usuario_id` = CASE `nome`
  WHEN 'Café Inclusivo (Demonstração)' THEN @usuario_mariana
  WHEN 'Shopping Acessível (Demonstração)' THEN @usuario_rafael
  WHEN 'Centro Cultural para Todos (Demonstração)' THEN @usuario_camila
  WHEN 'Igreja Comunidade Aberta (Demonstração)' THEN @usuario_mariana
  WHEN 'Instituto Cidadania (Demonstração)' THEN @usuario_rafael
END
WHERE `nome` LIKE '%(Demonstração)';

-- Estados variados para demonstrar a moderação no painel administrativo.
UPDATE `locais`
SET `status` = CASE `nome`
  WHEN 'Shopping Acessível (Demonstração)' THEN 'pendente'
  WHEN 'Igreja Comunidade Aberta (Demonstração)' THEN 'reprovado'
  ELSE 'aprovado'
END
WHERE `nome` IN (
  'Café Inclusivo (Demonstração)',
  'Shopping Acessível (Demonstração)',
  'Centro Cultural para Todos (Demonstração)',
  'Igreja Comunidade Aberta (Demonstração)',
  'Instituto Cidadania (Demonstração)'
);

UPDATE `locais`
SET `observacoes` = 'Solicitação recusada porque não foram enviadas imagens que comprovem os recursos de acessibilidade informados.'
WHERE `nome` = 'Igreja Comunidade Aberta (Demonstração)';

-- Fotos demonstrativas correspondentes a cada local.
-- Cada SELECT localiza o local pelo nome e evita cadastrar a mesma foto novamente.

INSERT INTO `local_fotos` (`local_id`, `arquivo`)
SELECT l.id, 'assets/uploads/solicitacoes/demo-cafe-inclusivo.png'
FROM `locais` l
WHERE l.nome = 'Café Inclusivo (Demonstração)'
  AND NOT EXISTS (
    SELECT 1 FROM `local_fotos` f
    WHERE f.local_id = l.id AND f.arquivo = 'assets/uploads/solicitacoes/demo-cafe-inclusivo.png'
  );

INSERT INTO `local_fotos` (`local_id`, `arquivo`)
SELECT l.id, 'assets/uploads/solicitacoes/demo-shopping-acessivel.png'
FROM `locais` l
WHERE l.nome = 'Shopping Acessível (Demonstração)'
  AND NOT EXISTS (
    SELECT 1 FROM `local_fotos` f
    WHERE f.local_id = l.id AND f.arquivo = 'assets/uploads/solicitacoes/demo-shopping-acessivel.png'
  );

INSERT INTO `local_fotos` (`local_id`, `arquivo`)
SELECT l.id, 'assets/uploads/solicitacoes/demo-centro-cultural.png'
FROM `locais` l
WHERE l.nome = 'Centro Cultural para Todos (Demonstração)'
  AND NOT EXISTS (
    SELECT 1 FROM `local_fotos` f
    WHERE f.local_id = l.id AND f.arquivo = 'assets/uploads/solicitacoes/demo-centro-cultural.png'
  );

DELETE f FROM `local_fotos` f
INNER JOIN `locais` l ON l.id = f.local_id
WHERE l.nome = 'Igreja Comunidade Aberta (Demonstração)';

INSERT INTO `local_fotos` (`local_id`, `arquivo`)
SELECT l.id, 'assets/uploads/solicitacoes/demo-instituto-cidadania.png'
FROM `locais` l
WHERE l.nome = 'Instituto Cidadania (Demonstração)'
  AND NOT EXISTS (
    SELECT 1 FROM `local_fotos` f
    WHERE f.local_id = l.id AND f.arquivo = 'assets/uploads/solicitacoes/demo-instituto-cidadania.png'
  );

-- Segunda coleção de locais fictícios para ampliar a demonstração dos filtros.

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_mariana,'Mercado Bom Vizinho (Demonstração)','Avenida Cidade Jardim','1450','Bosque dos Eucaliptos','São José dos Campos','SP','12233002',-23.2381000,-45.8892000,'["Mercado","Comércio"]','["Entrada acessível","Rampa de acesso","Vaga acessível","Piso tátil","Atendimento prioritário"]','Mercado fictício com entrada nivelada e recursos para mobilidade e deficiência visual.','aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Mercado Bom Vizinho (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_rafael,'Clínica Vida Plena (Demonstração)','Rua das Piabas','220','Jardim Aquarius','São José dos Campos','SP','12246030',-23.2175000,-45.9140000,'["Clínica"]','["Rampa de acesso","Entrada acessível","Banheiro acessível","Piso tátil","Atendimento prioritário","Comunicação acessível"]','Clínica fictícia com circulação acessível e atendimento prioritário.','aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Clínica Vida Plena (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_camila,'Escola Horizonte Inclusivo (Demonstração)','Rua dos Lírios','315','Jardim Motorama','São José dos Campos','SP','12224010',-23.1896000,-45.8628000,'["Escola"]','["Rampa de acesso","Entrada acessível","Piso tátil","Libras","Sala de conforto","Comunicação acessível"]','Escola fictícia com recursos para estudantes com diferentes necessidades.','aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Escola Horizonte Inclusivo (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_mariana,'Parque das Araucárias (Demonstração)','Avenida Linneu de Moura','900','Urbanova','São José dos Campos','SP','12244080',-23.2013000,-45.9284000,'["Parque"]','["Entrada acessível","Piso tátil","Banheiro acessível","Espaço para cadeira de rodas","Sinalização acessível"]','Parque fictício com caminhos nivelados, sanitário acessível e áreas de descanso inclusivas.','aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Parque das Araucárias (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_rafael,'Hotel Acolher (Demonstração)','Rua Vilaça','180','Centro','São José dos Campos','SP','12210000',-23.1819000,-45.8876000,'["Hotel"]','["Entrada acessível","Balcão acessível","Elevador acessível","Banheiro acessível","Braile","Cão-guia permitido"]','Hotel fictício com recepção rebaixada, rota tátil e circulação ampla.','aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Hotel Acolher (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_camila,'Terminal Conexão Sul (Demonstração)','Avenida Perseu','70','Jardim Satélite','São José dos Campos','SP','12230040',-23.2268000,-45.8936000,'["Transporte público"]','["Elevador acessível","Piso tátil","Sinalização acessível","Espaço para cadeira de rodas","Comunicação acessível"]','Terminal fictício com embarque nivelado e rota acessível sinalizada.','aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Terminal Conexão Sul (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_mariana,'Farmácia Bem-Estar (Demonstração)','Avenida Rui Barbosa','640','Santana','São José dos Campos','SP','12211005',-23.1739000,-45.8927000,'["Farmácia","Comércio"]','["Rampa de acesso","Entrada acessível","Balcão acessível","Piso tátil","Atendimento prioritário"]','Farmácia fictícia com balcão rebaixado e entrada sem degraus.','aprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Farmácia Bem-Estar (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_rafael,'Faculdade Nova Era (Demonstração)','Avenida Shishima Hifumi','2100','Urbanova','São José dos Campos','SP','12244000',-23.2086000,-45.9520000,'["Faculdade"]','["Rampa de acesso","Elevador acessível","Piso tátil","Libras","Sala de conforto","Comunicação acessível"]','Solicitação fictícia aguardando conferência da equipe antes da publicação.','pendente'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Faculdade Nova Era (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_camila,'Praça das Flores (Demonstração)','Rua República do Líbano','55','Jardim Oswaldo Cruz','São José dos Campos','SP','12216060',-23.1941000,-45.8792000,'["Praça"]','["Entrada acessível","Piso tátil","Espaço para cadeira de rodas","Sinalização acessível"]','Solicitação recusada porque o endereço e as coordenadas enviados não correspondem ao local mostrado na evidência.','reprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Praça das Flores (Demonstração)');

INSERT INTO `locais` (`usuario_id`,`nome`,`endereco`,`numero`,`bairro`,`cidade`,`estado`,`cep`,`latitude`,`longitude`,`categorias`,`recursos`,`observacoes`,`status`)
SELECT @usuario_mariana,'Feira Comunidade Inclusiva (Demonstração)','Avenida Olivo Gomes','100','Santana','São José dos Campos','SP','12211000',-23.1668000,-45.9005000,'["Evento"]','["Rampa de acesso","Piso tátil","Libras","Espaço para cadeira de rodas","Comunicação acessível"]','Solicitação recusada porque o evento já havia terminado e os recursos informados não puderam ser verificados pela moderação.','reprovado'
WHERE NOT EXISTS (SELECT 1 FROM `locais` WHERE `nome`='Feira Comunidade Inclusiva (Demonstração)');

-- Garante os estados e justificativas mesmo quando o script é executado novamente.
UPDATE `locais` SET `status`='aprovado' WHERE `nome` IN ('Mercado Bom Vizinho (Demonstração)','Clínica Vida Plena (Demonstração)','Escola Horizonte Inclusivo (Demonstração)','Parque das Araucárias (Demonstração)','Hotel Acolher (Demonstração)','Terminal Conexão Sul (Demonstração)','Farmácia Bem-Estar (Demonstração)');
UPDATE `locais` SET `status`='pendente' WHERE `nome`='Faculdade Nova Era (Demonstração)';
UPDATE `locais` SET `status`='reprovado',`observacoes`='Solicitação recusada porque o endereço e as coordenadas enviados não correspondem ao local mostrado na evidência.' WHERE `nome`='Praça das Flores (Demonstração)';
UPDATE `locais` SET `status`='reprovado',`observacoes`='Solicitação recusada porque o evento já havia terminado e os recursos informados não puderam ser verificados pela moderação.' WHERE `nome`='Feira Comunidade Inclusiva (Demonstração)';

INSERT INTO `local_fotos` (`local_id`,`arquivo`)
SELECT l.id, CONCAT('assets/uploads/solicitacoes/', x.arquivo)
FROM `locais` l
INNER JOIN (
  SELECT 'Mercado Bom Vizinho (Demonstração)' nome,'demo-mercado-bom-vizinho.png' arquivo UNION ALL
  SELECT 'Clínica Vida Plena (Demonstração)','demo-clinica-vida-plena.png' UNION ALL
  SELECT 'Escola Horizonte Inclusivo (Demonstração)','demo-escola-horizonte.png' UNION ALL
  SELECT 'Parque das Araucárias (Demonstração)','demo-parque-araucarias.png' UNION ALL
  SELECT 'Hotel Acolher (Demonstração)','demo-hotel-acolher.png' UNION ALL
  SELECT 'Terminal Conexão Sul (Demonstração)','demo-terminal-conexao.png' UNION ALL
  SELECT 'Farmácia Bem-Estar (Demonstração)','demo-farmacia-bem-estar.png' UNION ALL
  SELECT 'Faculdade Nova Era (Demonstração)','demo-faculdade-nova-era.png' UNION ALL
  SELECT 'Praça das Flores (Demonstração)','demo-praca-das-flores.png' UNION ALL
  SELECT 'Feira Comunidade Inclusiva (Demonstração)','demo-feira-inclusiva.png'
) x ON x.nome=l.nome
WHERE NOT EXISTS (
  SELECT 1 FROM `local_fotos` f
  WHERE f.local_id=l.id AND f.arquivo=CONCAT('assets/uploads/solicitacoes/',x.arquivo)
);

-- Deficiências atendidas pelos locais demonstrativos, usadas diretamente pelos filtros do mapa.
UPDATE `locais`
SET `deficiencias` = CASE `nome`
  WHEN 'Café Inclusivo (Demonstração)' THEN '["fisica","visual"]'
  WHEN 'Shopping Acessível (Demonstração)' THEN '["fisica","visual"]'
  WHEN 'Centro Cultural para Todos (Demonstração)' THEN '["visual","auditiva","cognitiva"]'
  WHEN 'Igreja Comunidade Aberta (Demonstração)' THEN '["fisica","auditiva","cognitiva"]'
  WHEN 'Instituto Cidadania (Demonstração)' THEN '["fisica","visual","auditiva","cognitiva"]'
  WHEN 'Mercado Bom Vizinho (Demonstração)' THEN '["fisica","visual","cognitiva"]'
  WHEN 'Clínica Vida Plena (Demonstração)' THEN '["fisica","visual","cognitiva"]'
  WHEN 'Escola Horizonte Inclusivo (Demonstração)' THEN '["fisica","visual","auditiva","cognitiva"]'
  WHEN 'Parque das Araucárias (Demonstração)' THEN '["fisica","visual"]'
  WHEN 'Hotel Acolher (Demonstração)' THEN '["fisica","visual"]'
  WHEN 'Terminal Conexão Sul (Demonstração)' THEN '["fisica","visual","auditiva","cognitiva"]'
  WHEN 'Farmácia Bem-Estar (Demonstração)' THEN '["fisica","visual","cognitiva"]'
  WHEN 'Faculdade Nova Era (Demonstração)' THEN '["fisica","visual","auditiva","cognitiva"]'
  WHEN 'Praça das Flores (Demonstração)' THEN '["fisica","visual"]'
  WHEN 'Feira Comunidade Inclusiva (Demonstração)' THEN '["fisica","visual","auditiva","cognitiva"]'
  ELSE `deficiencias`
END
WHERE `nome` LIKE '%(Demonstração)';
