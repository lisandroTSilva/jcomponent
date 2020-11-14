DROP TABLE IF EXISTS `#__inscritos`;

ALTER TABLE `#__inscritos` DROP INDEX `nome_email_celular_evento`;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_inscritos.%');