-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Tempo de geração: 30-Set-2025 às 20:56
-- Versão do servidor: 5.7.24
-- versão do PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `autolist`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `registros_clientes`
--

CREATE TABLE `registros_clientes` (
  `id` int(11) NOT NULL,
  `nome_cliente` varchar(255) NOT NULL,
  `endereco_cliente` text NOT NULL,
  `telefone_cliente` varchar(20) DEFAULT NULL,
  `fipe_valor` varchar(50) NOT NULL,
  `fipe_marca` varchar(100) NOT NULL,
  `fipe_modelo` varchar(255) NOT NULL,
  `fipe_ano_modelo` int(11) NOT NULL,
  `fipe_combustivel` varchar(50) NOT NULL,
  `fipe_codigo` varchar(50) NOT NULL,
  `fipe_mes_referencia` varchar(50) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `registros_clientes`
--

INSERT INTO `registros_clientes` (`id`, `nome_cliente`, `endereco_cliente`, `telefone_cliente`, `fipe_valor`, `fipe_marca`, `fipe_modelo`, `fipe_ano_modelo`, `fipe_combustivel`, `fipe_codigo`, `fipe_mes_referencia`, `data_cadastro`) VALUES
(1, 'Cirlene Neves de Lima', 'Av Norte N35', '81996933694', 'R$ 78.031,00', 'BMW', '116iA 1.6 TB 16V 136cv 5p', 2015, 'Gasolina', '009171-5', 'setembro de 2025', '2025-09-30 13:09:34'),
(3, 'José Da Silva', 'Av correntes N208', '81993782830', 'R$ 87.075,00', 'Alfa Romeo', '166 3.0 V6 24V', 2002, 'Gasolina', '006011-9', 'setembro de 2025', '2025-09-30 15:17:40');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `registros_clientes`
--
ALTER TABLE `registros_clientes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `registros_clientes`
--
ALTER TABLE `registros_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
