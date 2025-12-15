<?php

namespace Gateway\Controllers;

use Gateway\Core\Request;
use Gateway\Core\Response;

/**
 * Transaction Controller
 * Handles transaction status queries, reversals, and history
 */
class TransactionController extends BaseController
{
    /**
     * Query transaction status
     */
    public function status()
    {
        $request = new Request();

        // Validate request
        $request->validate([
            'transaction_id' => 'required'
        ]);

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [
                'TransactionID' => $request->input('transaction_id'),
                'Remarks' => $request->input('remarks', 'Transaction Status Query')
            ];

            // Optional parameters
            if ($request->has('occasion')) {
                $params['Occasion'] = $request->input('occasion');
            }
            if ($request->has('identifier_type')) {
                $params['IdentifierType'] = $request->input('identifier_type');
            }
            if ($request->has('result_url')) {
                $params['ResultURL'] = $request->input('result_url');
            }
            if ($request->has('timeout_url')) {
                $params['QueueTimeOutURL'] = $request->input('timeout_url');
            }

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->transactionStatus($params, $appName);

            $this->logTransaction('TRANSACTION_STATUS', $params, $response);

            Response::success($response, 'Transaction status query initiated successfully');

        } catch (\Exception $e) {
            $this->logError('TRANSACTION_STATUS', $e);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Reverse a transaction
     */
    public function reversal()
    {
        $request = new Request();

        // Validate request
        $request->validate([
            'transaction_id' => 'required',
            'amount' => 'required|numeric'
        ]);

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [
                'TransactionID' => $request->input('transaction_id'),
                'Amount' => $request->input('amount'),
                'Remarks' => $request->input('remarks', 'Transaction Reversal'),
                'Occasion' => $request->input('occasion', '')
            ];

            // Optional parameters
            if ($request->has('receiver_party')) {
                $params['ReceiverParty'] = $request->input('receiver_party');
            }
            if ($request->has('receiver_identifier_type')) {
                $params['RecieverIdentifierType'] = $request->input('receiver_identifier_type');
            }
            if ($request->has('result_url')) {
                $params['ResultURL'] = $request->input('result_url');
            }
            if ($request->has('timeout_url')) {
                $params['QueueTimeOutURL'] = $request->input('timeout_url');
            }

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->reversal($params, $appName);

            $this->logTransaction('REVERSAL', $params, $response);

            Response::success($response, 'Transaction reversal initiated successfully');

        } catch (\Exception $e) {
            $this->logError('REVERSAL', $e);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get transaction history
     */
    public function history()
    {
        $request = new Request();

        try {
            // Read transaction logs
            $logFile = $this->logDir . '/transactions.log';
            
            if (!file_exists($logFile)) {
                Response::success([], 'No transactions found');
                return;
            }

            $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $transactions = [];

            foreach ($logs as $log) {
                $transaction = json_decode($log, true);
                if ($transaction) {
                    $transactions[] = $transaction;
                }
            }

            // Apply filters
            $type = $request->input('type');
            if ($type) {
                $transactions = array_filter($transactions, function($t) use ($type) {
                    return $t['type'] === strtoupper($type);
                });
            }

            // Pagination
            $page = (int) $request->input('page', 1);
            $perPage = (int) $request->input('per_page', 20);
            $offset = ($page - 1) * $perPage;

            $total = count($transactions);
            $transactions = array_slice(array_reverse($transactions), $offset, $perPage);

            Response::success([
                'transactions' => $transactions,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($total / $perPage)
                ]
            ], 'Transaction history retrieved successfully');

        } catch (\Exception $e) {
            $this->logError('TRANSACTION_HISTORY', $e);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Get a specific transaction
     */
    public function show($params)
    {
        try {
            $id = $params['id'] ?? null;
            
            if (!$id) {
                Response::error('Transaction ID is required', 400);
            }

            // Read transaction logs
            $logFile = $this->logDir . '/transactions.log';
            
            if (!file_exists($logFile)) {
                Response::notFound('Transaction not found');
                return;
            }

            $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($logs as $log) {
                $transaction = json_decode($log, true);
                if ($transaction && isset($transaction['response'])) {
                    // Check various ID fields
                    $responseId = $transaction['response']->ConversationID ?? 
                                 $transaction['response']->CheckoutRequestID ?? 
                                 $transaction['response']->OriginatorConversationID ?? 
                                 null;
                    
                    if ($responseId === $id) {
                        Response::success($transaction, 'Transaction found');
                        return;
                    }
                }
            }

            Response::notFound('Transaction not found');

        } catch (\Exception $e) {
            $this->logError('TRANSACTION_SHOW', $e);
            Response::error($e->getMessage(), 500);
        }
    }
}
