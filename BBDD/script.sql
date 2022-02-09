-- Create procedure to update enddates on customerpricelists and trazabilidades
DELIMITER $$
CREATE PROCEDURE caducar()
BEGIN
  DECLARE param1 MEDIUMINT;
  DECLARE param2 MEDIUMINT;

  SELECT COUNT(codcustomerpricelist) INTO param1 FROM customerpricelists where fechafin < CURRENT_DATE;
  SELECT COUNT(codtrazabilidad) INTO param2 FROM trazabilidades where fechacaducidad < CURRENT_DATE;

  IF (param1 > 0) THEN
    update customerpricelists set estado='Caducado' WHERE fechafin < CURRENT_DATE;
  END IF;

  IF (param2 > 0) THEN
    update trazabilidades set estado='Caducado' WHERE fechacaducidad < CURRENT_DATE;
  END IF;
END
$$


create event evento_caducar
on schedule every 1 day starts (TIMESTAMP(CURRENT_DATE) + INTERVAL 1 DAY + INTERVAL 5 MINUTE)
on completion preserve
do call caducar;

