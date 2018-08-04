DELIMITER $$  
  
DROP FUNCTION IF EXISTS `url_decode` $$  
CREATE DEFINER=`root`@`%` FUNCTION `url_decode`(original_text TEXT) RETURNS TEXT CHARSET utf8  
BEGIN  
    DECLARE new_text TEXT DEFAULT NULL;  
    DECLARE pointer INT DEFAULT 1;  
    DECLARE end_pointer INT DEFAULT 1;  
    DECLARE encoded_text TEXT DEFAULT NULL;  
    DECLARE result_text TEXT DEFAULT NULL;  
   
    SET new_text = REPLACE(original_text,'+',' ');  
    SET new_text = REPLACE(new_text,'%0A','\r\n');  
   
    SET pointer = LOCATE("%", new_text);  
    while pointer <> 0 && pointer < (CHAR_LENGTH(new_text) - 2) DO  
        SET end_pointer = pointer + 3;  
        while MID(new_text, end_pointer, 1) = "%" DO  
            SET end_pointer = end_pointer+3;  
        END while;  
   
        SET encoded_text = MID(new_text, pointer, end_pointer - pointer);  
        SET result_text = CONVERT(UNHEX(REPLACE(encoded_text, "%", "")) USING utf8);  
        SET new_text = REPLACE(new_text, encoded_text, result_text);  
        SET pointer = LOCATE("%", new_text, pointer + CHAR_LENGTH(result_text));  
    END while;  
   
    return new_text;  
  
END $$  
  
DELIMITER ;  