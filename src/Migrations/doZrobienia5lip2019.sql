ALTER TABLE abstr_member ADD begin_date DATE DEFAULT NULL;
UPDATE `db_aptekarze`.`abstr_member` SET `begin_date`='2018-01-01 00:00:00' WHERE `id` > '1';
ALTER TABLE abstr_member ADD initial_account DOUBLE PRECISION DEFAULT NULL;