-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Erstellungszeit: 12. Mrz 2025 um 12:52
-- Server-Version: 5.7.39
-- PHP-Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `v-001-compare`
--

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `vcompare_franke_v`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `vcompare_franke_v` (
`owner` varchar(60)
,`objId` varchar(255)
,`name` varchar(255)
,`designtime_FCL_P_version` varchar(80)
,`designtime_FCL_P_status` varchar(20)
,`runtime_FCL_P_version` varchar(80)
,`runtime_FCL_P_status` varchar(40)
,`designtime_FCL_Q_version` varchar(80)
,`designtime_FCL_Q_status` varchar(20)
,`runtime_FCL_Q_version` varchar(80)
,`runtime_FCL_Q_status` varchar(40)
);

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `vcompare_galenica_v`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `vcompare_galenica_v` (
`owner` varchar(60)
,`objId` varchar(255)
,`name` varchar(255)
,`designtime_OC_Development_version` varchar(80)
,`designtime_OC_Development_status` varchar(20)
,`runtime_OC_Development_version` varchar(80)
,`runtime_OC_Development_status` varchar(40)
,`designtime_OC_Production_version` varchar(80)
,`designtime_OC_Production_status` varchar(20)
,`runtime_OC_Production_version` varchar(80)
,`runtime_OC_Production_status` varchar(40)
,`designtime_OC_QualityAssurance_version` varchar(80)
,`designtime_OC_QualityAssurance_status` varchar(20)
,`runtime_OC_QualityAssurance_version` varchar(80)
,`runtime_OC_QualityAssurance_status` varchar(40)
);

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `vcompare_global_it_v`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `vcompare_global_it_v` (
`owner` varchar(60)
,`objId` varchar(255)
,`name` varchar(255)
,`designtime_DEV_version` varchar(80)
,`designtime_DEV_status` varchar(20)
,`runtime_DEV_version` varchar(80)
,`runtime_DEV_status` varchar(40)
,`designtime_PROD_version` varchar(80)
,`designtime_PROD_status` varchar(20)
,`runtime_PROD_version` varchar(80)
,`runtime_PROD_status` varchar(40)
,`designtime_QAS_version` varchar(80)
,`designtime_QAS_status` varchar(20)
,`runtime_QAS_version` varchar(80)
,`runtime_QAS_status` varchar(40)
);

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `vcompare_owners`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `vcompare_owners` (
`id` int(11)
,`value` varchar(80)
);

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `vcompare_tenant_owner_v`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `vcompare_tenant_owner_v` (
`id` int(11)
,`owner` varchar(80)
,`name` varchar(80)
,`tier` varchar(1)
,`host` varchar(400)
,`client` varchar(1024)
,`secret` varchar(1024)
,`tokenurl` varchar(512)
);

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `vcompare_tenant_v`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `vcompare_tenant_v` (
`id` int(11)
,`owner` varchar(80)
,`name` varchar(80)
,`tier` varchar(1)
);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `v_IntegrationDesigntimeArtifacts`
--

CREATE TABLE `v_IntegrationDesigntimeArtifacts` (
  `id` int(11) NOT NULL,
  `owner` varchar(60) NOT NULL,
  `system` varchar(60) NOT NULL,
  `objId` varchar(255) NOT NULL,
  `Name` varchar(120) DEFAULT NULL,
  `Version` varchar(80) NOT NULL,
  `PackageId` varchar(80) NOT NULL,
  `Type` varchar(80) DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `v_IntegrationPackages`
--

CREATE TABLE `v_IntegrationPackages` (
  `id` int(11) NOT NULL,
  `owner` varchar(80) NOT NULL,
  `system` varchar(60) NOT NULL,
  `objId` varchar(255) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `ShortText` varchar(800) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `v_IntegrationRuntimeArtifacts`
--

CREATE TABLE `v_IntegrationRuntimeArtifacts` (
  `id` int(11) NOT NULL,
  `owner` varchar(60) NOT NULL,
  `system` varchar(60) NOT NULL,
  `objId` varchar(255) NOT NULL,
  `Version` varchar(80) DEFAULT NULL,
  `Name` varchar(255) NOT NULL,
  `Type` varchar(60) NOT NULL,
  `DeployedBy` varchar(80) NOT NULL,
  `DeployedOn` timestamp NULL DEFAULT NULL,
  `Status` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `v_Owner`
--

CREATE TABLE `v_Owner` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `info` varchar(400) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `v_Tenant`
--

CREATE TABLE `v_Tenant` (
  `id` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `tier` varchar(1) NOT NULL,
  `host` varchar(400) NOT NULL,
  `client` varchar(1024) NOT NULL,
  `secret` varchar(1024) NOT NULL,
  `tokenurl` varchar(512) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur des Views `vcompare_franke_v`
--
DROP TABLE IF EXISTS `vcompare_franke_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vcompare_franke_v`  AS SELECT `artifacts`.`owner` AS `owner`, `artifacts`.`objId` AS `objId`, `artifacts`.`name` AS `name`, max((case when (`artifacts`.`system` = 'FCL_P') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_FCL_P_version`, max((case when (`artifacts`.`system` = 'FCL_P') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_FCL_P_status`, max((case when (`artifacts`.`system` = 'FCL_P') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_FCL_P_version`, max((case when (`artifacts`.`system` = 'FCL_P') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_FCL_P_status`, max((case when (`artifacts`.`system` = 'FCL_Q') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_FCL_Q_version`, max((case when (`artifacts`.`system` = 'FCL_Q') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_FCL_Q_status`, max((case when (`artifacts`.`system` = 'FCL_Q') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_FCL_Q_version`, max((case when (`artifacts`.`system` = 'FCL_Q') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_FCL_Q_status` FROM (select `v_integrationdesigntimeartifacts`.`owner` AS `owner`,`v_integrationdesigntimeartifacts`.`system` AS `system`,`v_integrationdesigntimeartifacts`.`objId` AS `objId`,`v_integrationdesigntimeartifacts`.`Name` AS `name`,`v_integrationdesigntimeartifacts`.`Version` AS `designtime_version`,`v_integrationdesigntimeartifacts`.`Status` AS `designtime_status`,NULL AS `runtime_version`,NULL AS `runtime_status` from `v_integrationdesigntimeartifacts` where (`v_integrationdesigntimeartifacts`.`owner` = 'Franke') union all select `v_integrationruntimeartifacts`.`owner` AS `owner`,`v_integrationruntimeartifacts`.`system` AS `system`,`v_integrationruntimeartifacts`.`objId` AS `objId`,`v_integrationruntimeartifacts`.`Name` AS `name`,NULL AS `designtime_version`,NULL AS `designtime_status`,`v_integrationruntimeartifacts`.`Version` AS `runtime_version`,`v_integrationruntimeartifacts`.`Status` AS `runtime_status` from `v_integrationruntimeartifacts` where (`v_integrationruntimeartifacts`.`owner` = 'Franke')) AS `artifacts` GROUP BY `artifacts`.`owner`, `artifacts`.`objId`, `artifacts`.`name``name`  ;

-- --------------------------------------------------------

--
-- Struktur des Views `vcompare_galenica_v`
--
DROP TABLE IF EXISTS `vcompare_galenica_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vcompare_galenica_v`  AS SELECT `artifacts`.`owner` AS `owner`, `artifacts`.`objId` AS `objId`, `artifacts`.`name` AS `name`, max((case when (`artifacts`.`system` = 'OC Development') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_OC_Development_version`, max((case when (`artifacts`.`system` = 'OC Development') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_OC_Development_status`, max((case when (`artifacts`.`system` = 'OC Development') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_OC_Development_version`, max((case when (`artifacts`.`system` = 'OC Development') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_OC_Development_status`, max((case when (`artifacts`.`system` = 'OC Production') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_OC_Production_version`, max((case when (`artifacts`.`system` = 'OC Production') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_OC_Production_status`, max((case when (`artifacts`.`system` = 'OC Production') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_OC_Production_version`, max((case when (`artifacts`.`system` = 'OC Production') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_OC_Production_status`, max((case when (`artifacts`.`system` = 'OC QualityAssurance') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_OC_QualityAssurance_version`, max((case when (`artifacts`.`system` = 'OC QualityAssurance') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_OC_QualityAssurance_status`, max((case when (`artifacts`.`system` = 'OC QualityAssurance') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_OC_QualityAssurance_version`, max((case when (`artifacts`.`system` = 'OC QualityAssurance') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_OC_QualityAssurance_status` FROM (select `v_integrationdesigntimeartifacts`.`owner` AS `owner`,`v_integrationdesigntimeartifacts`.`system` AS `system`,`v_integrationdesigntimeartifacts`.`objId` AS `objId`,`v_integrationdesigntimeartifacts`.`Name` AS `name`,`v_integrationdesigntimeartifacts`.`Version` AS `designtime_version`,`v_integrationdesigntimeartifacts`.`Status` AS `designtime_status`,NULL AS `runtime_version`,NULL AS `runtime_status` from `v_integrationdesigntimeartifacts` where (`v_integrationdesigntimeartifacts`.`owner` = 'Galenica') union all select `v_integrationruntimeartifacts`.`owner` AS `owner`,`v_integrationruntimeartifacts`.`system` AS `system`,`v_integrationruntimeartifacts`.`objId` AS `objId`,`v_integrationruntimeartifacts`.`Name` AS `name`,NULL AS `designtime_version`,NULL AS `designtime_status`,`v_integrationruntimeartifacts`.`Version` AS `runtime_version`,`v_integrationruntimeartifacts`.`Status` AS `runtime_status` from `v_integrationruntimeartifacts` where (`v_integrationruntimeartifacts`.`owner` = 'Galenica')) AS `artifacts` GROUP BY `artifacts`.`owner`, `artifacts`.`objId`, `artifacts`.`name``name`  ;

-- --------------------------------------------------------

--
-- Struktur des Views `vcompare_global_it_v`
--
DROP TABLE IF EXISTS `vcompare_global_it_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vcompare_global_it_v`  AS SELECT `artifacts`.`owner` AS `owner`, `artifacts`.`objId` AS `objId`, `artifacts`.`name` AS `name`, max((case when (`artifacts`.`system` = 'DEV') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_DEV_version`, max((case when (`artifacts`.`system` = 'DEV') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_DEV_status`, max((case when (`artifacts`.`system` = 'DEV') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_DEV_version`, max((case when (`artifacts`.`system` = 'DEV') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_DEV_status`, max((case when (`artifacts`.`system` = 'PROD') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_PROD_version`, max((case when (`artifacts`.`system` = 'PROD') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_PROD_status`, max((case when (`artifacts`.`system` = 'PROD') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_PROD_version`, max((case when (`artifacts`.`system` = 'PROD') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_PROD_status`, max((case when (`artifacts`.`system` = 'QAS') then `artifacts`.`designtime_version` else NULL end)) AS `designtime_QAS_version`, max((case when (`artifacts`.`system` = 'QAS') then `artifacts`.`designtime_status` else NULL end)) AS `designtime_QAS_status`, max((case when (`artifacts`.`system` = 'QAS') then `artifacts`.`runtime_version` else NULL end)) AS `runtime_QAS_version`, max((case when (`artifacts`.`system` = 'QAS') then `artifacts`.`runtime_status` else NULL end)) AS `runtime_QAS_status` FROM (select `v_integrationdesigntimeartifacts`.`owner` AS `owner`,`v_integrationdesigntimeartifacts`.`system` AS `system`,`v_integrationdesigntimeartifacts`.`objId` AS `objId`,`v_integrationdesigntimeartifacts`.`Name` AS `name`,`v_integrationdesigntimeartifacts`.`Version` AS `designtime_version`,`v_integrationdesigntimeartifacts`.`Status` AS `designtime_status`,NULL AS `runtime_version`,NULL AS `runtime_status` from `v_integrationdesigntimeartifacts` where (`v_integrationdesigntimeartifacts`.`owner` = 'global it') union all select `v_integrationruntimeartifacts`.`owner` AS `owner`,`v_integrationruntimeartifacts`.`system` AS `system`,`v_integrationruntimeartifacts`.`objId` AS `objId`,`v_integrationruntimeartifacts`.`Name` AS `name`,NULL AS `designtime_version`,NULL AS `designtime_status`,`v_integrationruntimeartifacts`.`Version` AS `runtime_version`,`v_integrationruntimeartifacts`.`Status` AS `runtime_status` from `v_integrationruntimeartifacts` where (`v_integrationruntimeartifacts`.`owner` = 'global it')) AS `artifacts` GROUP BY `artifacts`.`owner`, `artifacts`.`objId`, `artifacts`.`name``name`  ;

-- --------------------------------------------------------

--
-- Struktur des Views `vcompare_owners`
--
DROP TABLE IF EXISTS `vcompare_owners`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vcompare_owners`  AS SELECT `v_owner`.`id` AS `id`, `v_owner`.`name` AS `value` FROM `v_owner``v_owner`  ;

-- --------------------------------------------------------

--
-- Struktur des Views `vcompare_tenant_owner_v`
--
DROP TABLE IF EXISTS `vcompare_tenant_owner_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vcompare_tenant_owner_v`  AS SELECT `t`.`id` AS `id`, `o`.`name` AS `owner`, `t`.`name` AS `name`, `t`.`tier` AS `tier`, `t`.`host` AS `host`, `t`.`client` AS `client`, `t`.`secret` AS `secret`, `t`.`tokenurl` AS `tokenurl` FROM (`v_tenant` `t` join `v_owner` `o` on((`t`.`owner` = `o`.`id`)))  ;

-- --------------------------------------------------------

--
-- Struktur des Views `vcompare_tenant_v`
--
DROP TABLE IF EXISTS `vcompare_tenant_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vcompare_tenant_v`  AS SELECT `t`.`id` AS `id`, `o`.`name` AS `owner`, `t`.`name` AS `name`, `t`.`tier` AS `tier` FROM (`v_tenant` `t` left join `v_owner` `o` on((`t`.`owner` = `o`.`id`)))  ;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `v_IntegrationDesigntimeArtifacts`
--
ALTER TABLE `v_IntegrationDesigntimeArtifacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `owner` (`owner`,`system`,`objId`);

--
-- Indizes für die Tabelle `v_IntegrationPackages`
--
ALTER TABLE `v_IntegrationPackages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `owner` (`owner`,`system`,`objId`);

--
-- Indizes für die Tabelle `v_IntegrationRuntimeArtifacts`
--
ALTER TABLE `v_IntegrationRuntimeArtifacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `owner` (`owner`,`system`,`objId`);

--
-- Indizes für die Tabelle `v_Owner`
--
ALTER TABLE `v_Owner`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `v_Tenant`
--
ALTER TABLE `v_Tenant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tenant_owner` (`owner`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `v_IntegrationDesigntimeArtifacts`
--
ALTER TABLE `v_IntegrationDesigntimeArtifacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `v_IntegrationPackages`
--
ALTER TABLE `v_IntegrationPackages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `v_IntegrationRuntimeArtifacts`
--
ALTER TABLE `v_IntegrationRuntimeArtifacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `v_Owner`
--
ALTER TABLE `v_Owner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `v_Tenant`
--
ALTER TABLE `v_Tenant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `v_Tenant`
--
ALTER TABLE `v_Tenant`
  ADD CONSTRAINT `fk_tenant_owner` FOREIGN KEY (`owner`) REFERENCES `v_Owner` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
