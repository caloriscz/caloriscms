-- Add before deploying password-link code. Compatible with MySQL and MariaDB.
-- Re-runnable; leaves users, passwords and legacy activation values untouched.
SET @caloris_reset_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_token_hash') = 0,
    'ALTER TABLE users ADD COLUMN reset_token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
    'SELECT 1');
PREPARE caloris_reset_stmt FROM @caloris_reset_sql;
EXECUTE caloris_reset_stmt;
DEALLOCATE PREPARE caloris_reset_stmt;

SET @caloris_reset_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_expires_at') = 0,
    'ALTER TABLE users ADD COLUMN reset_expires_at DATETIME DEFAULT NULL',
    'SELECT 1');
PREPARE caloris_reset_stmt FROM @caloris_reset_sql;
EXECUTE caloris_reset_stmt;
DEALLOCATE PREPARE caloris_reset_stmt;
