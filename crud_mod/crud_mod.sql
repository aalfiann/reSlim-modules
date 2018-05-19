SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crud_mod
-- ----------------------------
DROP TABLE IF EXISTS `crud_mod`;
CREATE TABLE `crud_mod` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Fullname` varchar(50) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Telp` varchar(15) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Website` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET FOREIGN_KEY_CHECKS=1;