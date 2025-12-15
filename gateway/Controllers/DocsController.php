<?php

namespace Gateway\Controllers;

use Gateway\Core\Response;

/**
 * Documentation Controller
 * Provides API documentation
 */
class DocsController
{
    /**
     * Show API documentation
     */
    public function index()
    {
        $docs = [
            'name' => 'M-Pesa API Gateway',
            'version' => '1.0.0',
            'description' => 'RESTful API Gateway for M-Pesa integration',
            'base_url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
            'authentication' => [
                'type' => 'API Key',
                'methods' => [
                    'Bearer Token in Authorization header',
                    'X-API-Key header'
                ],
                'example' => 'Authorization: Bearer your_api_key_here'
            ],
            'endpoints' => [
                'health' => [
                    'method' => 'GET',
                    'path' => '/api/v1/health',
                    'description' => 'Check API health status',
                    'auth_required' => false
                ],
                'stkpush' => [
                    'initiate' => [
                        'method' => 'POST',
                        'path' => '/api/v1/stkpush',
                        'description' => 'Initiate STK Push (Lipa Na M-Pesa Online)',
                        'auth_required' => true,
                        'parameters' => [
                            'phone_number' => 'required|string|Phone number (254XXXXXXXXX)',
                            'amount' => 'required|numeric|Amount to charge',
                            'account_reference' => 'required|string|Account reference',
                            'transaction_desc' => 'required|string|Transaction description',
                            'callback_url' => 'optional|string|Custom callback URL',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ],
                    'query' => [
                        'method' => 'POST',
                        'path' => '/api/v1/stkpush/query',
                        'description' => 'Query STK Push status',
                        'auth_required' => true,
                        'parameters' => [
                            'checkout_request_id' => 'required|string|Checkout Request ID from initiate response',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ]
                ],
                'c2b' => [
                    'register' => [
                        'method' => 'POST',
                        'path' => '/api/v1/c2b/register',
                        'description' => 'Register C2B URLs',
                        'auth_required' => true,
                        'parameters' => [
                            'short_code' => 'optional|string|Override shortcode',
                            'confirmation_url' => 'optional|string|Custom confirmation URL',
                            'validation_url' => 'optional|string|Custom validation URL',
                            'response_type' => 'optional|string|Response type (Completed/Cancelled)',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ],
                    'simulate' => [
                        'method' => 'POST',
                        'path' => '/api/v1/c2b/simulate',
                        'description' => 'Simulate C2B transaction (sandbox only)',
                        'auth_required' => true,
                        'parameters' => [
                            'phone_number' => 'required|string|Phone number',
                            'amount' => 'required|numeric|Amount',
                            'bill_ref_number' => 'required|string|Bill reference number',
                            'command_id' => 'optional|string|Command ID (default: CustomerPayBillOnline)',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ]
                ],
                'b2c' => [
                    'payment' => [
                        'method' => 'POST',
                        'path' => '/api/v1/b2c/payment',
                        'description' => 'Initiate B2C payment',
                        'auth_required' => true,
                        'parameters' => [
                            'phone_number' => 'required|string|Recipient phone number',
                            'amount' => 'required|numeric|Amount to send',
                            'remarks' => 'optional|string|Payment remarks',
                            'occasion' => 'optional|string|Occasion',
                            'command_id' => 'optional|string|Command ID',
                            'result_url' => 'optional|string|Custom result URL',
                            'timeout_url' => 'optional|string|Custom timeout URL',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ]
                ],
                'b2b' => [
                    'payment' => [
                        'method' => 'POST',
                        'path' => '/api/v1/b2b/payment',
                        'description' => 'Initiate B2B payment',
                        'auth_required' => true,
                        'parameters' => [
                            'receiver_shortcode' => 'required|string|Receiver shortcode',
                            'amount' => 'required|numeric|Amount to send',
                            'remarks' => 'optional|string|Payment remarks',
                            'account_reference' => 'optional|string|Account reference',
                            'command_id' => 'optional|string|Command ID',
                            'sender_identifier_type' => 'optional|string|Sender identifier type',
                            'receiver_identifier_type' => 'optional|string|Receiver identifier type',
                            'result_url' => 'optional|string|Custom result URL',
                            'timeout_url' => 'optional|string|Custom timeout URL',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ]
                ],
                'account' => [
                    'balance' => [
                        'method' => 'POST',
                        'path' => '/api/v1/account/balance',
                        'description' => 'Query account balance',
                        'auth_required' => true,
                        'parameters' => [
                            'remarks' => 'optional|string|Query remarks',
                            'party_a' => 'optional|string|Party A',
                            'identifier_type' => 'optional|string|Identifier type',
                            'result_url' => 'optional|string|Custom result URL',
                            'timeout_url' => 'optional|string|Custom timeout URL',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ]
                ],
                'transaction' => [
                    'status' => [
                        'method' => 'POST',
                        'path' => '/api/v1/transaction/status',
                        'description' => 'Query transaction status',
                        'auth_required' => true,
                        'parameters' => [
                            'transaction_id' => 'required|string|Transaction ID',
                            'remarks' => 'optional|string|Query remarks',
                            'occasion' => 'optional|string|Occasion',
                            'identifier_type' => 'optional|string|Identifier type',
                            'result_url' => 'optional|string|Custom result URL',
                            'timeout_url' => 'optional|string|Custom timeout URL',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ],
                    'reversal' => [
                        'method' => 'POST',
                        'path' => '/api/v1/transaction/reversal',
                        'description' => 'Reverse a transaction',
                        'auth_required' => true,
                        'parameters' => [
                            'transaction_id' => 'required|string|Transaction ID to reverse',
                            'amount' => 'required|numeric|Amount to reverse',
                            'remarks' => 'optional|string|Reversal remarks',
                            'occasion' => 'optional|string|Occasion',
                            'receiver_party' => 'optional|string|Receiver party',
                            'receiver_identifier_type' => 'optional|string|Receiver identifier type',
                            'result_url' => 'optional|string|Custom result URL',
                            'timeout_url' => 'optional|string|Custom timeout URL',
                            'app_name' => 'optional|string|App name (default: "default")'
                        ]
                    ],
                    'history' => [
                        'method' => 'GET',
                        'path' => '/api/v1/transactions',
                        'description' => 'Get transaction history',
                        'auth_required' => true,
                        'parameters' => [
                            'type' => 'optional|string|Filter by transaction type',
                            'page' => 'optional|integer|Page number (default: 1)',
                            'per_page' => 'optional|integer|Items per page (default: 20)'
                        ]
                    ],
                    'show' => [
                        'method' => 'GET',
                        'path' => '/api/v1/transactions/{id}',
                        'description' => 'Get specific transaction details',
                        'auth_required' => true,
                        'parameters' => [
                            'id' => 'required|string|Transaction ID (in URL path)'
                        ]
                    ]
                ]
            ],
            'response_format' => [
                'success' => [
                    'success' => true,
                    'message' => 'Success message',
                    'data' => 'Response data',
                    'timestamp' => 'ISO 8601 timestamp'
                ],
                'error' => [
                    'success' => false,
                    'message' => 'Error message',
                    'errors' => 'Optional error details',
                    'timestamp' => 'ISO 8601 timestamp'
                ]
            ],
            'callback_urls' => [
                'stkpush' => '/api/v1/callbacks/stkpush',
                'c2b_validation' => '/api/v1/callbacks/c2b/validation',
                'c2b_confirmation' => '/api/v1/callbacks/c2b/confirmation',
                'b2c_result' => '/api/v1/callbacks/b2c/result',
                'b2c_timeout' => '/api/v1/callbacks/b2c/timeout',
                'b2b_result' => '/api/v1/callbacks/b2b/result',
                'b2b_timeout' => '/api/v1/callbacks/b2b/timeout',
                'balance_result' => '/api/v1/callbacks/balance/result',
                'balance_timeout' => '/api/v1/callbacks/balance/timeout',
                'reversal_result' => '/api/v1/callbacks/reversal/result',
                'reversal_timeout' => '/api/v1/callbacks/reversal/timeout',
                'status_result' => '/api/v1/callbacks/status/result',
                'status_timeout' => '/api/v1/callbacks/status/timeout'
            ]
        ];

        Response::success($docs, 'API Documentation');
    }
}
