<?php
/**
 * Transaction Model
 * Handles all transaction-related database operations
 */


class Transaction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new transaction record
     */
    public function create($data) {
        $sql = "INSERT INTO transactions (
            checkout_request_id, merchant_request_id, phone_number, 
            amount, account_reference, transaction_desc, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($sql, [
            $data['checkout_request_id'] ?? null,
            $data['merchant_request_id'] ?? null,
            $data['phone_number'],
            $data['amount'],
            $data['account_reference'],
            $data['transaction_desc'] ?? '',
            $data['status'] ?? 'pending'
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update transaction with callback data
     */
    public function updateFromCallback($checkoutRequestId, $callbackData) {
        $sql = "UPDATE transactions SET 
            mpesa_receipt_number = ?,
            transaction_date = ?,
            status = ?,
            result_code = ?,
            result_desc = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE checkout_request_id = ?";

        $this->db->query($sql, [
            $callbackData['mpesa_receipt_number'] ?? null,
            $callbackData['transaction_date'] ?? null,
            $callbackData['status'],
            $callbackData['result_code'],
            $callbackData['result_desc'],
            $checkoutRequestId
        ]);
    }

    /**
     * Get transaction by checkout request ID
     */
    public function getByCheckoutRequestId($checkoutRequestId) {
        $sql = "SELECT * FROM transactions WHERE checkout_request_id = ?";
        return $this->db->fetchOne($sql, [$checkoutRequestId]);
    }

    /**
     * Get all transactions with pagination
     */
    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT * FROM transactions ORDER BY created_at DESC LIMIT ? OFFSET ?";
        return $this->db->fetchAll($sql, [$limit, $offset]);
    }

    /**
     * Get transactions by phone number
     */
    public function getByPhone($phoneNumber, $limit = 20) {
        $sql = "SELECT * FROM transactions WHERE phone_number = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$phoneNumber, $limit]);
    }

    /**
     * Get transactions by status
     */
    public function getByStatus($status, $limit = 50) {
        $sql = "SELECT * FROM transactions WHERE status = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$status, $limit]);
    }

    /**
     * Get transaction statistics
     */
    public function getStats() {
        $sql = "SELECT 
            COUNT(*) as total_transactions,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount
        FROM transactions";
        
        return $this->db->fetchOne($sql);
    }

    /**
     * Get recent transactions
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT * FROM transactions ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
}
