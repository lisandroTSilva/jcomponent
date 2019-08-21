CREATE TABLE IF NOT EXISTS `#__patrimonio_patrimonio` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL ,
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL ,
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`catid` int(11) NOT NULL DEFAULT 0,
`nome` VARCHAR(255)  NOT NULL ,
`descricao` TEXT NOT NULL ,
`local` VARCHAR(255)  NOT NULL ,
`fornecedor` VARCHAR(255)  NOT NULL ,
`documento` VARCHAR(255)  NOT NULL ,
`dataaquisicao` DATETIME NOT NULL ,
`valor` REAL NOT NULL ,
`garantia` DATETIME NOT NULL ,
`ativo` TINYINT(1)  NOT NULL ,
`data_baixa` DATETIME NOT NULL ,
`patrimonio` VARCHAR(255)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

