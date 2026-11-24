-- =====================================================================
-- Base de datos y usuarios con PRIVILEGIOS MINIMOS (sin usar root)
-- Ejecutar UNA vez como administrador:  mysql -u root -p < sql/database.sql
-- =====================================================================
CREATE DATABASE IF NOT EXISTS company_info CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE company_info;

-- ---------------------------- TABLA 1 --------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Nombre         VARCHAR(60)  NOT NULL,
  Apellido       VARCHAR(60)  NOT NULL,
  Usuario        VARCHAR(30)  NOT NULL,
  Correo         VARCHAR(120) NOT NULL,
  HashMagic      VARCHAR(255) NOT NULL,
  Sexo           CHAR(1)      NOT NULL,
  secret_2fa     VARCHAR(64)  NULL DEFAULT NULL,
  fecha_registro TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario (Usuario),
  UNIQUE KEY uq_correo  (Correo)
) ENGINE=InnoDB;

-- ---------------------------- TABLA 2 --------------------------------
CREATE TABLE IF NOT EXISTS intentos_login (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Usuario            VARCHAR(30)  NOT NULL,
  ipRemoto           VARCHAR(45)  NOT NULL,
  deteccion_anomalia TINYINT      NOT NULL DEFAULT 0,
  fecha              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ------------------------ USUARIOS DE BD -----------------------------
-- (cambie las contrasenas y igualelas en clases/config.php)
CREATE USER IF NOT EXISTS 'app_login'@'localhost'   IDENTIFIED BY 'Cambiar_Esta_Clave_1!';
CREATE USER IF NOT EXISTS 'app_lectura'@'localhost' IDENTIFIED BY 'Cambiar_Esta_Clave_2!';

-- app_login: lo que necesita la aplicacion (nada de DELETE, DROP, ALTER, etc.)
GRANT SELECT, INSERT, UPDATE ON company_info.usuarios        TO 'app_login'@'localhost';
GRANT SELECT, INSERT         ON company_info.intentos_login  TO 'app_login'@'localhost';

-- app_lectura: solo consulta
GRANT SELECT ON company_info.usuarios       TO 'app_lectura'@'localhost';
GRANT SELECT ON company_info.intentos_login TO 'app_lectura'@'localhost';

FLUSH PRIVILEGES;

-- Comando para ver los privilegios concedidos:
SHOW GRANTS FOR 'app_login'@'localhost';
SHOW GRANTS FOR 'app_lectura'@'localhost';

-- Si ya tenia la tabla usuarios anterior, migre asi (en vez del CREATE TABLE):
--   ALTER TABLE usuarios ADD COLUMN Correo VARCHAR(120) NOT NULL AFTER Usuario,
--     ADD COLUMN secret_2fa VARCHAR(64) NULL, ADD UNIQUE KEY uq_usuario (Usuario), ADD UNIQUE KEY uq_correo (Correo);
