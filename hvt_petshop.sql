-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01/07/2025 às 19:41
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `hvt_petshop`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_hora` datetime NOT NULL,
  `servico_id` int(11) NOT NULL,
  `transporte` enum('Sim','Não') DEFAULT 'Não',
  `status` enum('Pendente','Em Atendimento','Finalizado','Cancelado') DEFAULT 'Pendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `pet_id`, `usuario_id`, `data_hora`, `servico_id`, `transporte`, `status`, `criado_em`, `observacoes`, `atualizado_em`) VALUES
(15, 3, 1, '2025-07-02 15:30:00', 4, 'Sim', 'Pendente', '2025-07-01 17:31:34', '', NULL),
(16, 3, 1, '2025-07-02 15:30:00', 7, 'Sim', 'Pendente', '2025-07-01 17:31:34', '', NULL),
(17, 3, 1, '2025-07-02 15:30:00', 9, 'Sim', 'Pendente', '2025-07-01 17:31:34', '', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fichas_petshop`
--

CREATE TABLE `fichas_petshop` (
  `id` int(11) NOT NULL,
  `agendamento_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `data_preenchimento` datetime NOT NULL DEFAULT current_timestamp(),
  `altura_pelos` varchar(100) DEFAULT NULL,
  `doenca_pre_existente` varchar(255) DEFAULT NULL,
  `doenca_ouvido` varchar(255) DEFAULT NULL,
  `doenca_pele` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ficha_observacoes`
--

CREATE TABLE `ficha_observacoes` (
  `ficha_id` int(11) NOT NULL,
  `observacao_id` int(11) NOT NULL,
  `outros_detalhes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ficha_servicos_realizados`
--

CREATE TABLE `ficha_servicos_realizados` (
  `ficha_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `outros_detalhes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_servicos`
--

CREATE TABLE `historico_servicos` (
  `id` int(11) NOT NULL,
  `agendamento_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `realizado_por` int(11) NOT NULL,
  `data_realizacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_status_agendamento`
--

CREATE TABLE `historico_status_agendamento` (
  `id` int(11) NOT NULL,
  `agendamento_id` int(11) NOT NULL,
  `status_antigo` enum('Pendente','Em Atendimento','Finalizado','Cancelado') NOT NULL,
  `status_novo` enum('Pendente','Em Atendimento','Finalizado','Cancelado') NOT NULL,
  `alterado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `alterado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `observacoes_visuais`
--

CREATE TABLE `observacoes_visuais` (
  `id` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `observacoes_visuais`
--

INSERT INTO `observacoes_visuais` (`id`, `descricao`) VALUES
(1, 'Pulgas/Carrapatos'),
(2, 'Pele'),
(3, 'Secreções Genitais'),
(4, 'Olhos'),
(5, 'Ouvidos'),
(6, 'Problemas nos Dentes'),
(7, 'Outros');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `especie` varchar(50) DEFAULT NULL,
  `raca` varchar(50) DEFAULT NULL,
  `idade` varchar(20) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `sexo` enum('Macho','Fêmea') DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `pelagem` varchar(50) DEFAULT NULL,
  `tutor_id` int(11) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pets`
--

INSERT INTO `pets` (`id`, `nome`, `especie`, `raca`, `idade`, `nascimento`, `sexo`, `peso`, `pelagem`, `tutor_id`, `observacoes`, `criado_em`, `atualizado_em`) VALUES
(2, 'Luck', 'Canina', 'Maltes', '2', NULL, 'Macho', 20.00, 'Branca', 7, 'Ele precisa de transporte', '2025-06-30 21:23:55', '2025-06-30 21:36:50'),
(3, 'Carlos Daniel', 'Canina', 'SRD', '10', NULL, 'Macho', 5.00, ' Branca', 8, 'Nada', '2025-07-01 17:30:40', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE `servicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `servicos`
--

INSERT INTO `servicos` (`id`, `nome`, `descricao`, `preco`) VALUES
(4, 'Banho', 'Banho completo com produtos adequados', 50.00),
(5, 'Hidratação', 'Hidratação da pelagem', 30.00),
(6, 'Tosa', 'Tosa completa', 60.00),
(7, 'Tosa Higiênica', 'Tosa na região íntima', 40.00),
(8, 'Tosa Padrão', 'Tosa seguindo padrão da raça', 70.00),
(9, 'Unhas', 'Corte de unhas', 20.00),
(10, 'Escovar Dentes', 'Escovação dentária', 25.00),
(11, 'Desembolo', 'Remoção de nós e embaraços', 35.00),
(99, 'Outros', NULL, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `sexo_pet`
--

CREATE TABLE `sexo_pet` (
  `id` char(1) NOT NULL,
  `descricao` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `sexo_pet`
--

INSERT INTO `sexo_pet` (`id`, `descricao`) VALUES
('F', 'Fêmea'),
('M', 'Macho');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tutores`
--

CREATE TABLE `tutores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `cep` varchar(9) DEFAULT NULL,
  `rua` varchar(100) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `bairro` varchar(60) DEFAULT NULL,
  `cidade` varchar(60) DEFAULT NULL,
  `uf` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tutores`
--

INSERT INTO `tutores` (`id`, `nome`, `email`, `telefone`, `atualizado_em`, `cep`, `rua`, `numero`, `bairro`, `cidade`, `uf`) VALUES
(7, 'Vicente Neto', 'vneto500@gmail.com', '(88) 99227-4307', '2025-07-01 00:19:19', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Marcelo', '1@gmail.com', '(88) 99546-8541', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','funcionario') DEFAULT 'funcionario',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`, `atualizado_em`) VALUES
(1, 'Vicente Neto', 'vneto500@gmail.com', '$2y$10$psg0OT5ZUsAoVPbvRuARXucDm2bKgHVRScFkFH0Z9QMlc8PMZqoM.', 'funcionario', '2025-06-19 12:20:09', NULL),
(2, 'teste', 'teste@teste.com', '$2y$10$uLSxE6ugtaNWz8xzYbYn/OsMsV7xDwEBd4I0CuBmIO7u6RFOCFQym', 'funcionario', '2025-06-20 12:49:17', NULL);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `view_agendamentos_completos`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `view_agendamentos_completos` (
`agendamento_id` int(11)
,`data_hora` datetime
,`status_agendamento` enum('Pendente','Em Atendimento','Finalizado','Cancelado')
,`transporte` enum('Sim','Não')
,`obs_agendamento` text
,`pet_nome` varchar(100)
,`tutor_nome` varchar(100)
,`servico_nome` varchar(255)
,`servico_preco` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `view_historico_servicos`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `view_historico_servicos` (
`historico_id` int(11)
,`data_realizacao` datetime
,`agendamento_id` int(11)
,`pet_nome` varchar(100)
,`servico_nome` varchar(255)
,`realizado_por` varchar(100)
);

-- --------------------------------------------------------

--
-- Estrutura para view `view_agendamentos_completos`
--
DROP TABLE IF EXISTS `view_agendamentos_completos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_agendamentos_completos`  AS SELECT `a`.`id` AS `agendamento_id`, `a`.`data_hora` AS `data_hora`, `a`.`status` AS `status_agendamento`, `a`.`transporte` AS `transporte`, `a`.`observacoes` AS `obs_agendamento`, `p`.`nome` AS `pet_nome`, `t`.`nome` AS `tutor_nome`, `s`.`nome` AS `servico_nome`, `s`.`preco` AS `servico_preco` FROM (((`agendamentos` `a` join `pets` `p` on(`a`.`pet_id` = `p`.`id`)) join `tutores` `t` on(`p`.`tutor_id` = `t`.`id`)) join `servicos` `s` on(`a`.`servico_id` = `s`.`id`)) ;

-- --------------------------------------------------------

--
-- Estrutura para view `view_historico_servicos`
--
DROP TABLE IF EXISTS `view_historico_servicos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_historico_servicos`  AS SELECT `h`.`id` AS `historico_id`, `h`.`data_realizacao` AS `data_realizacao`, `h`.`agendamento_id` AS `agendamento_id`, `p`.`nome` AS `pet_nome`, `s`.`nome` AS `servico_nome`, `u`.`nome` AS `realizado_por` FROM (((`historico_servicos` `h` join `pets` `p` on(`h`.`pet_id` = `p`.`id`)) join `servicos` `s` on(`h`.`servico_id` = `s`.`id`)) join `usuarios` `u` on(`h`.`realizado_por` = `u`.`id`)) ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `servico_id` (`servico_id`),
  ADD KEY `idx_agendamentos_data_hora` (`data_hora`),
  ADD KEY `idx_agendamentos_status` (`status`);

--
-- Índices de tabela `fichas_petshop`
--
ALTER TABLE `fichas_petshop`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agendamento_id` (`agendamento_id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices de tabela `ficha_observacoes`
--
ALTER TABLE `ficha_observacoes`
  ADD PRIMARY KEY (`ficha_id`,`observacao_id`),
  ADD KEY `observacao_id` (`observacao_id`);

--
-- Índices de tabela `ficha_servicos_realizados`
--
ALTER TABLE `ficha_servicos_realizados`
  ADD PRIMARY KEY (`ficha_id`,`servico_id`),
  ADD KEY `servico_id` (`servico_id`);

--
-- Índices de tabela `historico_servicos`
--
ALTER TABLE `historico_servicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agendamento_id` (`agendamento_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `servico_id` (`servico_id`),
  ADD KEY `realizado_por` (`realizado_por`),
  ADD KEY `idx_historico_servicos_data` (`data_realizacao`);

--
-- Índices de tabela `historico_status_agendamento`
--
ALTER TABLE `historico_status_agendamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agendamento_id` (`agendamento_id`),
  ADD KEY `alterado_por` (`alterado_por`);

--
-- Índices de tabela `observacoes_visuais`
--
ALTER TABLE `observacoes_visuais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `idx_nome` (`nome`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nome` (`nome`);

--
-- Índices de tabela `sexo_pet`
--
ALTER TABLE `sexo_pet`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tutores`
--
ALTER TABLE `tutores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_nome` (`nome`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `fichas_petshop`
--
ALTER TABLE `fichas_petshop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `historico_servicos`
--
ALTER TABLE `historico_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_status_agendamento`
--
ALTER TABLE `historico_status_agendamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `observacoes_visuais`
--
ALTER TABLE `observacoes_visuais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT de tabela `tutores`
--
ALTER TABLE `tutores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_3` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fichas_petshop`
--
ALTER TABLE `fichas_petshop`
  ADD CONSTRAINT `fichas_petshop_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fichas_petshop_ibfk_2` FOREIGN KEY (`funcionario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ficha_observacoes`
--
ALTER TABLE `ficha_observacoes`
  ADD CONSTRAINT `ficha_observacoes_ibfk_1` FOREIGN KEY (`ficha_id`) REFERENCES `fichas_petshop` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ficha_observacoes_ibfk_2` FOREIGN KEY (`observacao_id`) REFERENCES `observacoes_visuais` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ficha_servicos_realizados`
--
ALTER TABLE `ficha_servicos_realizados`
  ADD CONSTRAINT `ficha_servicos_realizados_ibfk_1` FOREIGN KEY (`ficha_id`) REFERENCES `fichas_petshop` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ficha_servicos_realizados_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_servicos`
--
ALTER TABLE `historico_servicos`
  ADD CONSTRAINT `historico_servicos_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_servicos_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_servicos_ibfk_3` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_servicos_ibfk_4` FOREIGN KEY (`realizado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_status_agendamento`
--
ALTER TABLE `historico_status_agendamento`
  ADD CONSTRAINT `historico_status_agendamento_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_status_agendamento_ibfk_2` FOREIGN KEY (`alterado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
