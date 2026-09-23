-- Banco: fn_agropecuaria
--
-- Todas as tabelas usadas pelo sistema. Rodar esse arquivo não é
-- obrigatório: cada classe em classes/ já cria a própria tabela
-- sozinha (CREATE TABLE IF NOT EXISTS) na primeira vez que a tela
-- correspondente é acessada. Esse arquivo existe só pra quem preferir
-- montar o banco na mão pelo phpMyAdmin antes de subir o sistema.

CREATE TABLE IF NOT EXISTS equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL DEFAULT 'Trator',
    modelo VARCHAR(100),
    marca VARCHAR(100),
    ano INT,
    identificacao VARCHAR(50),
    horimetro DECIMAL(10,2),
    potencia VARCHAR(50),
    status ENUM('Ativo','Operando','Em manutenção','Inativo') NOT NULL DEFAULT 'Ativo',
    manutencao_preventiva_data DATE NULL,
    observacoes VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS safras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    cultura VARCHAR(100),
    area DECIMAL(10,2),
    talhao VARCHAR(100),
    data_plantio DATE NULL,
    previsao_colheita DATE NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS producao_colheitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cultura VARCHAR(100),
    area_colhida DECIMAL(10,2),
    quantidade_colhida DECIMAL(10,2),
    produtividade DECIMAL(10,2),
    data_colheita DATE NULL,
    destino VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS financeiro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Receita','Despesa') NOT NULL,
    categoria VARCHAR(100) NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantidade DECIMAL(10,2) NULL,
    data_lancamento DATE NULL,
    talhao_id INT NULL,
    forma_pagamento VARCHAR(50) NULL,
    observacoes VARCHAR(255) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
