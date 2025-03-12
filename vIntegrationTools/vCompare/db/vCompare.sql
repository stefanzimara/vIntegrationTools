--
-- Table structure for table `v_IntegrationDesigntimeArtifacts`
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
-- Table structure for table `v_IntegrationPackages`
--

CREATE TABLE `v_IntegrationPackages` (
  `id` int(11) NOT NULL,
  `owner` varchar(80) NOT NULL,
  `system` varchar(60) NOT NULL,
  `objId` varchar(255) NOT NULL,
  `Name` VARCHAR(255) NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `v_IntegrationRuntimeArtifacts`
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `v_IntegrationDesigntimeArtifacts`
--
ALTER TABLE `v_IntegrationDesigntimeArtifacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `owner` (`owner`,`system`,`objId`);

--
-- Indexes for table `v_IntegrationPackages`
--
ALTER TABLE `v_IntegrationPackages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `owner` (`owner`,`system`,`objId`);

--
-- Indexes for table `v_IntegrationRuntimeArtifacts`
--
ALTER TABLE `v_IntegrationRuntimeArtifacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `owner` (`owner`,`system`,`objId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `v_IntegrationDesigntimeArtifacts`
--
ALTER TABLE `v_IntegrationDesigntimeArtifacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `v_IntegrationPackages`
--
ALTER TABLE `v_IntegrationPackages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `v_IntegrationRuntimeArtifacts`
--
ALTER TABLE `v_IntegrationRuntimeArtifacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;


