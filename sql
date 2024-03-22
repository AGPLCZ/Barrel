CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_string VARCHAR(255) NOT NULL,
    total_amount_czk DECIMAL(10, 2) DEFAULT 0.00,
    btc_address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount_btc DECIMAL(16, 8),
    transaction_fee DECIMAL(16, 8),
    coinmate_transaction_id INT,
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);



INSERT INTO users (user_string, total_amount_czk, btc_address) VALUES
('randomString1', 1000.00, '1BitcoinAdresaXyz'),
('randomString2', 1500.50, '1BitcoinAdresaAbc'),
('randomString3', 750.25, '1BitcoinAdresaDef');




INSERT INTO transactions (user_id, amount_btc, transaction_fee, coinmate_transaction_id, status) VALUES
(1, 0.0010, 0.0001, 123456, 'NEW'),
(2, 0.0015, 0.0001, 123457, 'SENT'),
(3, 0.00075, 0.0001, 123458, 'WAITING');