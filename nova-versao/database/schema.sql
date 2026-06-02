USE sql10829082;
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

CREATE TABLE IF NOT EXISTS `operadoras` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nome`          VARCHAR(100)  NOT NULL,
    `slug`          VARCHAR(100)  NOT NULL UNIQUE,
    `logo_url`      VARCHAR(255)  DEFAULT NULL,
    `descricao`     TEXT          DEFAULT NULL,
    `ativo`         TINYINT(1)    NOT NULL DEFAULT 1,
    `criado_em`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `planos` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `operadora_id`    INT UNSIGNED  NOT NULL,
    `nome`            VARCHAR(100)  NOT NULL,
    `categoria`       ENUM('SEM_COPARTICIPACAO','COM_COPARTICIPACAO') NOT NULL DEFAULT 'SEM_COPARTICIPACAO',
    `cobertura`       VARCHAR(100)  DEFAULT NULL,
    `faixa_0_18`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `faixa_19_28`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `faixa_29_43`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `faixa_44_58`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `faixa_59_plus`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `vigencia_inicio` DATE          DEFAULT NULL,
    `vigencia_fim`    DATE          DEFAULT NULL,
    `versao`          INT           NOT NULL DEFAULT 1,
    `ativo`           TINYINT(1)    NOT NULL DEFAULT 1,
    `criado_em`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`   DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_operadora` (`operadora_id`),
    INDEX `idx_ativo` (`ativo`),
    CONSTRAINT `fk_plano_operadora` FOREIGN KEY (`operadora_id`) REFERENCES `operadoras` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `empresas` (
    `id`                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `cnpj`                VARCHAR(18)   NOT NULL UNIQUE,
    `razao_social`        VARCHAR(255)  DEFAULT NULL,
    `email`               VARCHAR(191)  NOT NULL UNIQUE,
    `senha_hash`          VARCHAR(255)  NOT NULL,
    `telefone`            VARCHAR(20)   NOT NULL,
    `cep`                 VARCHAR(10)   DEFAULT NULL,
    `endereco`            VARCHAR(255)  DEFAULT NULL,
    `bairro`              VARCHAR(100)  DEFAULT NULL,
    `numero`              VARCHAR(20)   DEFAULT NULL,
    `complemento`         VARCHAR(100)  DEFAULT NULL,
    `cidade`              VARCHAR(100)  DEFAULT NULL,
    `uf`                  CHAR(2)       DEFAULT NULL,
    `contrato_social_url` VARCHAR(255)  DEFAULT NULL,
    `status`              ENUM('PENDENTE_PAGAMENTO','ATIVO','SUSPENSO','CANCELADO') NOT NULL DEFAULT 'PENDENTE_PAGAMENTO',
    `pix_status`          VARCHAR(50)   DEFAULT 'PENDENTE',
    `asaas_payment_id`    VARCHAR(100)  DEFAULT NULL,
    `criado_em`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`       DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendedores` (
    `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `empresa_id`       INT UNSIGNED  DEFAULT NULL,
    `nome_completo`    VARCHAR(255)  NOT NULL,
    `cpf`              VARCHAR(14)   NOT NULL UNIQUE,
    `rg`               VARCHAR(20)   DEFAULT NULL,
    `telefone`         VARCHAR(20)   NOT NULL UNIQUE,
    `email`            VARCHAR(255)  DEFAULT NULL,
    `senha_hash`       VARCHAR(255)  NOT NULL,
    `perfil`           ENUM('VENDEDOR','SUPERVISOR','GERENTE','ADMIN') NOT NULL DEFAULT 'VENDEDOR',
    `cep`              VARCHAR(10)   DEFAULT NULL,
    `endereco`         VARCHAR(255)  DEFAULT NULL,
    `bairro`           VARCHAR(100)  DEFAULT NULL,
    `numero`           VARCHAR(20)   DEFAULT NULL,
    `cidade`           VARCHAR(100)  DEFAULT NULL,
    `uf`               CHAR(2)       DEFAULT NULL,
    `doc_frente_url`   VARCHAR(255)  DEFAULT NULL,
    `doc_verso_url`    VARCHAR(255)  DEFAULT NULL,
    `status`           ENUM('PENDENTE_PAGAMENTO','ATIVO','SUSPENSO','CANCELADO') NOT NULL DEFAULT 'PENDENTE_PAGAMENTO',
    `pix_status`       VARCHAR(50)   DEFAULT 'PENDENTE',
    `asaas_payment_id` VARCHAR(100)  DEFAULT NULL,
    `ultimo_acesso`    DATETIME      DEFAULT NULL,
    `criado_em`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`    DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_empresa` (`empresa_id`),
    INDEX `idx_perfil` (`perfil`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_vendedor_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clientes` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nome_completo`   VARCHAR(255)  NOT NULL,
    `cpf`             VARCHAR(14)   NOT NULL,
    `rg`              VARCHAR(20)   DEFAULT NULL,
    `data_nascimento` DATE          DEFAULT NULL,
    `sexo`            ENUM('M','F','OUTRO') DEFAULT NULL,
    `estado_civil`    VARCHAR(30)   DEFAULT NULL,
    `nome_mae`        VARCHAR(255)  DEFAULT NULL,
    `email`           VARCHAR(255)  DEFAULT NULL,
    `telefone`        VARCHAR(20)   DEFAULT NULL,
    `cep`             VARCHAR(10)   DEFAULT NULL,
    `endereco`        VARCHAR(255)  DEFAULT NULL,
    `bairro`          VARCHAR(100)  DEFAULT NULL,
    `numero`          VARCHAR(20)   DEFAULT NULL,
    `complemento`     VARCHAR(100)  DEFAULT NULL,
    `cidade`          VARCHAR(100)  DEFAULT NULL,
    `uf`              CHAR(2)       DEFAULT NULL,
    `profissao`       VARCHAR(100)  DEFAULT NULL,
    `entidade`        VARCHAR(100)  DEFAULT NULL,
    `criado_em`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`   DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `propostas` (
    `id`                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `protocolo`            VARCHAR(30)   NOT NULL UNIQUE,
    `cliente_id`           INT UNSIGNED  NOT NULL,
    `vendedor_id`          INT UNSIGNED  NOT NULL,
    `empresa_id`           INT UNSIGNED  DEFAULT NULL,
    `operadora_id`         INT UNSIGNED  NOT NULL,
    `plano_id`             INT UNSIGNED  DEFAULT NULL,
    `categoria`            ENUM('SEM_COPARTICIPACAO','COM_COPARTICIPACAO') NOT NULL,
    `quantidade_vidas`     INT           NOT NULL DEFAULT 1,
    `valor_total`          DECIMAL(10,2) DEFAULT NULL,
    `status`               ENUM('RASCUNHO','PENDENTE_SUPERVISOR','APROVADO_SUPERVISOR','REPROVADO_SUPERVISOR','PENDENTE_GERENTE','APROVADO_GERENTE','RECUSADO_GERENTE','ENVIADO_OPERADORA','CONCLUIDO','CANCELADO') NOT NULL DEFAULT 'RASCUNHO',
    `decisao_supervisor`   VARCHAR(255)  DEFAULT NULL,
    `decisao_gerente`      VARCHAR(255)  DEFAULT NULL,
    `operadora_destino`    VARCHAR(100)  DEFAULT NULL,
    `observacoes`          TEXT          DEFAULT NULL,
    `doc_frente_url`       VARCHAR(255)  DEFAULT NULL,
    `doc_verso_url`        VARCHAR(255)  DEFAULT NULL,
    `doc_residencia_url`   VARCHAR(255)  DEFAULT NULL,
    `doc_contracheque_url` VARCHAR(255)  DEFAULT NULL,
    `gera_comissao`        TINYINT(1)    NOT NULL DEFAULT 1,
    `criado_em`            TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`        DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_protocolo` (`protocolo`),
    INDEX `idx_cliente`  (`cliente_id`),
    INDEX `idx_vendedor` (`vendedor_id`),
    INDEX `idx_empresa`  (`empresa_id`),
    INDEX `idx_status`   (`status`),
    CONSTRAINT `fk_proposta_cliente`   FOREIGN KEY (`cliente_id`)   REFERENCES `clientes` (`id`),
    CONSTRAINT `fk_proposta_vendedor`  FOREIGN KEY (`vendedor_id`)  REFERENCES `vendedores` (`id`),
    CONSTRAINT `fk_proposta_operadora` FOREIGN KEY (`operadora_id`) REFERENCES `operadoras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagamentos` (
    `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `entity_type`      ENUM('EMPRESA','VENDEDOR') NOT NULL,
    `entity_id`        INT UNSIGNED  NOT NULL,
    `valor`            DECIMAL(10,2) NOT NULL,
    `asaas_payment_id` VARCHAR(100)  DEFAULT NULL,
    `pix_qr_code`      TEXT          DEFAULT NULL,
    `pix_copia_cola`   TEXT          DEFAULT NULL,
    `status`           VARCHAR(50)   NOT NULL DEFAULT 'PENDING',
    `confirmado_em`    DATETIME      DEFAULT NULL,
    `criado_em`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`    DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_entity`   (`entity_type`, `entity_id`),
    INDEX `idx_asaas_id` (`asaas_payment_id`),
    INDEX `idx_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `acao`         VARCHAR(100)    NOT NULL,
    `nivel`        ENUM('INFO','WARNING','ERROR') NOT NULL DEFAULT 'INFO',
    `usuario_id`   INT UNSIGNED    DEFAULT NULL,
    `usuario_tipo` VARCHAR(20)     DEFAULT NULL,
    `ip`           VARCHAR(45)     DEFAULT NULL,
    `dados`        LONGTEXT        DEFAULT NULL,
    `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_acao`       (`acao`),
    INDEX `idx_usuario`    (`usuario_id`),
    INDEX `idx_criado_em`  (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `operadoras` (`nome`, `slug`, `descricao`, `ativo`) VALUES
('AMIL',        'amil',        'Planos completos com ampla rede credenciada nacional.',        1),
('UNIMED',      'unimed',      'Cooperativa medica com tradicao e excelencia em atendimento.', 1),
('KLINI SAUDE', 'klini-saude', 'Planos flexiveis com otimo custo-beneficio.',                  1),
('CEMERU',      'cemeru',      '50 anos cuidando da Zona Oeste do Rio de Janeiro.',            1),
('SAMOC',       'samoc',       'Planos regionais com excelente cobertura local.',               1),
('OPLAN SAUDE', 'oplan-saude', 'Planos flex com foco em acessibilidade.',                      1),
('ABRABDIR',    'abrabdir',    'Plano oficial para advogados e bachareis em direito.',          1),
('ABRACEM',     'abracem',     'Plano corporativo para consultores empresariais.',              1),
('ABA',         'aba',         'Planos para desenvolvimento social e ONGs.',                   1);

INSERT INTO `planos` (`operadora_id`, `nome`, `categoria`, `cobertura`, `faixa_0_18`, `faixa_19_28`, `faixa_29_43`, `faixa_44_58`, `faixa_59_plus`, `ativo`) VALUES
(1, 'AMIL Completo',     'SEM_COPARTICIPACAO', 'Nacional',   280.00, 320.00, 380.00, 450.00, 580.00, 1),
(1, 'AMIL Completo',     'COM_COPARTICIPACAO', 'Nacional',   220.00, 250.00, 300.00, 360.00, 460.00, 1),
(2, 'UNIMED Completo',   'SEM_COPARTICIPACAO', 'Nacional',   290.00, 330.00, 390.00, 460.00, 590.00, 1),
(2, 'UNIMED Completo',   'COM_COPARTICIPACAO', 'Nacional',   230.00, 260.00, 310.00, 370.00, 470.00, 1),
(3, 'KLINI Flex',        'SEM_COPARTICIPACAO', 'Regional',   260.00, 300.00, 360.00, 430.00, 550.00, 1),
(3, 'KLINI Flex',        'COM_COPARTICIPACAO', 'Regional',   200.00, 230.00, 280.00, 340.00, 440.00, 1),
(4, 'CEMERU Tradicional','SEM_COPARTICIPACAO', 'Zona Oeste', 270.00, 310.00, 370.00, 440.00, 560.00, 1),
(4, 'CEMERU Tradicional','COM_COPARTICIPACAO', 'Zona Oeste', 210.00, 240.00, 290.00, 350.00, 450.00, 1),
(5, 'SAMOC Regional',    'SEM_COPARTICIPACAO', 'Regional',   250.00, 290.00, 350.00, 420.00, 540.00, 1),
(5, 'SAMOC Regional',    'COM_COPARTICIPACAO', 'Regional',   190.00, 220.00, 270.00, 330.00, 430.00, 1),
(6, 'OPLAN Flex',        'SEM_COPARTICIPACAO', 'Regional',   240.00, 280.00, 340.00, 410.00, 530.00, 1),
(6, 'OPLAN Flex',        'COM_COPARTICIPACAO', 'Regional',   180.00, 210.00, 260.00, 320.00, 420.00, 1);

INSERT INTO `vendedores` (`nome_completo`, `cpf`, `telefone`, `email`, `senha_hash`, `perfil`, `status`, `pix_status`) VALUES
('Administrador Master', '000.000.000-00', '21000000000', 'admin@concessionariabem.com.br',
 '$2b$12$R1rasFZT3OcOnWuEynX4/.CTrAiAvK4U31AjTBDiGydFGL6einwDO', 'ADMIN', 'ATIVO', 'CONFIRMED');

SET FOREIGN_KEY_CHECKS = 1;
