-- M-Pesa Payment System Database Schema
-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    checkout_request_id VARCHAR(255) UNIQUE,
    merchant_request_id VARCHAR(255),
    phone_number VARCHAR(15) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    account_reference VARCHAR(255) NOT NULL,
    transaction_desc TEXT,
    mpesa_receipt_number VARCHAR(255),
    transaction_date DATETIME,
    status VARCHAR(50) DEFAULT 'pending',
    result_code INTEGER,
    result_desc TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Callbacks table for logging all M-Pesa callbacks
CREATE TABLE IF NOT EXISTS callbacks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    checkout_request_id VARCHAR(255),
    callback_type VARCHAR(50),
    payload TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    phone_number VARCHAR(15) UNIQUE NOT NULL,
    name VARCHAR(255),
    email VARCHAR(255),
    total_transactions INTEGER DEFAULT 0,
    total_amount DECIMAL(10, 2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_transactions_phone ON transactions(phone_number);
CREATE INDEX IF NOT EXISTS idx_transactions_status ON transactions(status);
CREATE INDEX IF NOT EXISTS idx_transactions_created ON transactions(created_at);
CREATE INDEX IF NOT EXISTS idx_callbacks_checkout ON callbacks(checkout_request_id);