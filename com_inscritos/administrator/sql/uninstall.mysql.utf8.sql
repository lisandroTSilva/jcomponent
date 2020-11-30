DROP TABLE IF EXISTS `#__inscritos`;
DELETE FROM `#__content_types`
WHERE (type_alias LIKE 'com_inscritos.%');