-- GESTOR AGRO - ATUALIZAÇÃO INCREMENTAL
-- Execute somente este arquivo no banco atual, pelo phpMyAdmin.
-- Ele não apaga nem recria as tabelas já existentes.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    usuario VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('Administrador','Auxiliar Administrativo','Operador') NOT NULL DEFAULT 'Operador',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Padroniza os três perfis usados pelo controle de acesso.
UPDATE usuarios SET nivel = 'Administrador' WHERE LOWER(nivel) IN ('admin', 'administrador');
UPDATE usuarios SET nivel = 'Auxiliar Administrativo' WHERE LOWER(nivel) IN ('auxiliar', 'aux administrativo', 'auxiliar administrativo');
UPDATE usuarios SET nivel = 'Operador'
WHERE nivel IS NULL OR TRIM(nivel) = '' OR nivel NOT IN ('Administrador', 'Auxiliar Administrativo', 'Operador');

ALTER TABLE usuarios
    MODIFY COLUMN nivel ENUM('Administrador','Auxiliar Administrativo','Operador') NOT NULL DEFAULT 'Operador',
    MODIFY COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1;

CREATE TABLE IF NOT EXISTS colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    cpf VARCHAR(14) NULL,
    telefone VARCHAR(20) NULL,
    cargo VARCHAR(100) NULL,
    forma_pagamento ENUM('Diária','CLT','Quinzenal','Hora') NOT NULL,
    valor_pagamento DECIMAL(12,2) NULL,
    equipamento_id INT NULL,
    data_admissao DATE NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    observacoes VARCHAR(500) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colaborador_ativo (ativo),
    INDEX idx_colaborador_equipamento (equipamento_id),
    CONSTRAINT fk_colaborador_equipamento
        FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS operacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipamento_id INT NOT NULL,
    operador_id INT NOT NULL,
    usuario_id INT NULL,
    data_operacao DATE NOT NULL,
    consumo_medio DECIMAL(10,2) NULL COMMENT 'Litros por hora',
    hora_inicio TIME NOT NULL,
    hora_termino TIME NULL,
    status ENUM('Planejada','Em andamento','Concluída','Cancelada') NOT NULL DEFAULT 'Planejada',
    descricao VARCHAR(500) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_operacao_data (data_operacao),
    INDEX idx_operacao_status (status),
    INDEX idx_operacao_usuario (usuario_id),
    CONSTRAINT fk_operacao_equipamento
        FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_operacao_operador
        FOREIGN KEY (operador_id) REFERENCES colaboradores(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS manutencoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipamento_id INT NOT NULL,
    usuario_id INT NULL,
    tipo ENUM('Preventiva','Corretiva','Preditiva') NOT NULL,
    descricao VARCHAR(500) NOT NULL,
    data_abertura DATE NOT NULL,
    data_conclusao DATE NULL,
    status ENUM('Aberta','Em andamento','Concluída','Cancelada') NOT NULL DEFAULT 'Aberta',
    custo DECIMAL(12,2) NULL,
    responsavel VARCHAR(120) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_manutencao_status (status),
    INDEX idx_manutencao_equipamento (equipamento_id),
    INDEX idx_manutencao_usuario (usuario_id),
    CONSTRAINT fk_manutencao_equipamento
        FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fazenda_config (
    id TINYINT UNSIGNED PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    area_total_ha DECIMAL(12,2) NOT NULL DEFAULT 0,
    area_produtiva_ha DECIMAL(12,2) NOT NULL DEFAULT 0,
    municipio VARCHAR(100) NULL,
    estado CHAR(2) NULL,
    cultura_principal VARCHAR(100) NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO fazenda_config
    (id, nome, area_total_ha, area_produtiva_ha, municipio, estado, cultura_principal)
VALUES
    (1, 'Minha Fazenda', 0, 0, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE id = id;
