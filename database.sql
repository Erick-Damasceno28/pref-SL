-- ============================================================
--  Sistema de Cadastro Imobiliário
--  Prefeitura Municipal de São Leopoldo
--  database.sql  —  execute este arquivo no phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS cadastro_imoveis
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cadastro_imoveis;

CREATE TABLE IF NOT EXISTS usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(100)  NOT NULL,
  login      VARCHAR(50)   NOT NULL UNIQUE,
  senha      VARCHAR(255)  NOT NULL,
  criado_em  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Senha padrão: password
INSERT INTO usuarios (nome, login, senha) VALUES
('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE TABLE IF NOT EXISTS pessoas (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  nome              VARCHAR(150) NOT NULL,
  data_nascimento   DATE         NOT NULL,
  cpf               VARCHAR(14)  NOT NULL UNIQUE,
  sexo              ENUM('M','F','O') NOT NULL,
  telefone          VARCHAR(20)  NULL,
  email             VARCHAR(150) NULL,
  criado_em         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS imoveis (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  logradouro   VARCHAR(200) NOT NULL,
  numero       VARCHAR(20)  NOT NULL,
  bairro       VARCHAR(100) NOT NULL,
  complemento  VARCHAR(100) NULL,
  pessoa_id    INT          NOT NULL,
  criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_imovel_pessoa
    FOREIGN KEY (pessoa_id) REFERENCES pessoas(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_pessoas_nome      ON pessoas(nome);
CREATE INDEX idx_pessoas_cpf       ON pessoas(cpf);
CREATE INDEX idx_imoveis_logr      ON imoveis(logradouro);
CREATE INDEX idx_imoveis_bairro    ON imoveis(bairro);
CREATE INDEX idx_imoveis_pessoa    ON imoveis(pessoa_id);

INSERT INTO pessoas (nome, data_nascimento, cpf, sexo, telefone, email) VALUES
('João Silva Santos',       '1985-03-15', '123.456.789-00', 'M', '(51) 98765-4321', 'joao.silva@email.com'),
('Maria Oliveira Costa',    '1990-07-22', '987.654.321-00', 'F', '(51) 91234-5678', 'maria.oliveira@email.com'),
('Carlos Eduardo Pereira',  '1975-11-08', '456.789.123-00', 'M', NULL, NULL);

INSERT INTO imoveis (logradouro, numero, bairro, complemento, pessoa_id) VALUES
('Rua Independência', '452',  'Centro',     NULL,       1),
('Av. João Corrêa',   '1200', 'Rio Branco', 'Apto 304', 2),
('Rua Independência', '89',   'Centro',     NULL,       3);
