

SET FOREIGN_KEY_CHECKS=0;

--
-- Add data for table `user_role`
--

INSERT IGNORE INTO `user_role` (`RoleID`, `Role`) VALUES
(6, 'master'),
(7, 'standart');

-- ----------------------------
-- Table structure for sys_company
-- ----------------------------
DROP TABLE IF EXISTS `sys_company`;
CREATE TABLE `sys_company` (
  `BranchID` varchar(10) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Phone` varchar(15) NOT NULL,
  `Fax` varchar(15) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Owner` varchar(50) DEFAULT NULL,
  `PIC` varchar(50) DEFAULT NULL,
  `TIN` varchar(50) DEFAULT NULL,
  `StatusID` int(11) NOT NULL,
  `Created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Username` varchar(50) NOT NULL,
  `Updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Updated_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`BranchID`),
  KEY `BranchID` (`BranchID`),
  KEY `StatusID` (`StatusID`),
  KEY `Name` (`Name`),
  KEY `Phone` (`Phone`),
  KEY `Username` (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for sys_user
-- ----------------------------
DROP TABLE IF EXISTS `sys_user`;
CREATE TABLE `sys_user` (
  `Username` varchar(50) NOT NULL,
  `BranchID` varchar(10) NOT NULL,
  `StatusID` int(11) NOT NULL,
  `Created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Created_by` varchar(50) NOT NULL,
  `Updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Updated_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET FOREIGN_KEY_CHECKS=1;
