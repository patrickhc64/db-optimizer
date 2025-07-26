CREATE DATABASE IF NOT EXISTS query_db;
USE query_db;

CREATE TABLE performance_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query_text TEXT NOT NULL,
    execution_time FLOAT NOT NULL
);

INSERT INTO performance_log (query_text, execution_time) VALUES
('SELECT * FROM users WHERE email="test@example.com"', 0.85),
('SELECT * FROM orders WHERE user_id IN (SELECT id FROM users WHERE status=1)', 1.23),
('SELECT COUNT(*) FROM products', 0.42);