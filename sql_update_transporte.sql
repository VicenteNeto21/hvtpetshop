-- Script para atualizar o campo transporte na tabela agendamentos
-- Execute este script no seu banco de dados MySQL/MariaDB

-- 1. Primeiro, altera o tipo do campo para VARCHAR para poder armazenar as novas opções
ALTER TABLE `agendamentos` 
MODIFY COLUMN `transporte` VARCHAR(50) DEFAULT 'Petshop busca e entrega';

-- 2. Atualiza os registros existentes para os novos valores
-- 'Sim' → 'Petshop busca e entrega' (era usado quando petshop fazia transporte)
-- 'Não' → 'Tutor leva e retira' (era usado quando tutor trazia e levava)
UPDATE `agendamentos` SET `transporte` = 'Petshop busca e entrega' WHERE `transporte` = 'Sim';
UPDATE `agendamentos` SET `transporte` = 'Tutor leva e retira' WHERE `transporte` = 'Não';

-- 3. Opcionalmente, pode alterar para ENUM novamente com os novos valores (recomendado)
ALTER TABLE `agendamentos` 
MODIFY COLUMN `transporte` ENUM(
    'Petshop busca e entrega',
    'Petshop busca, tutor retira',
    'Tutor leva, Petshop entrega',
    'Tutor leva e retira'
) DEFAULT 'Petshop busca e entrega';
 