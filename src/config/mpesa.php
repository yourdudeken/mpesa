<?php

/**
 * M-Pesa Package Internal Configuration
 * 
 * This file contains package defaults including certificate paths,
 * API endpoints, and default CommandIDs for various transaction types.
 * 
 * Users provide their specific credentials via their project's config/mpesa.php
 */

return [
    /*
    |--------------------------------------------------------------------------
    | API Domain 
    |--------------------------------------------------------------------------
    */
    'apiUrl' => 'https://sandbox.safaricom.co.ke/',
    
    // Fallback URLs for sandboxes
    'apiUrlSandbox' => 'https://sandbox.safaricom.co.ke/',
    'apiUrlLive'    => 'https://api.safaricom.co.ke/',

    /*
    |--------------------------------------------------------------------------
    | Certificate Paths
    |--------------------------------------------------------------------------
    */
    'certificate_path_sandbox' => __DIR__ . '/SandboxCertificate.cer',
    'certificate_path_production' => __DIR__ . '/ProductionCertificate.cer',

    /*
    |--------------------------------------------------------------------------
    | Transaction Defaults (Command IDs)
    |--------------------------------------------------------------------------
    | These are the standard M-Pesa CommandIDs.
    */
    'lnmo' => [
        'short_code'               => null,
        'passkey'                  => null,
        'callback'                 => null,
        'default_transaction_type' => 'CustomerPayBillOnline',
        'transaction_desc'         => 'Transaction',
        'account_reference'        => 'Transaction',
    ],
    'c2b' => [
        'short_code'         => null,
        'default_command_id' => 'CustomerPayBillOnline',
        'response_type'      => 'Completed',
        'remarks'            => 'Transaction',
        'confirmation_url'   => null,
        'validation_url'     => null,
    ],
    'b2c' => [
        'short_code'         => null,
        'initiator_name'     => null,
        'initiator_password' => null,
        'default_command_id' => 'BusinessPayment',
        'remarks'            => 'Business Payment',
        'occasion'           => 'Payment',
        'result_url'         => null,
        'timeout_url'        => null,
    ],
    'b2b' => [
        'short_code'               => null,
        'initiator_name'           => null,
        'initiator_password'       => null,
        'default_command_id'       => 'BusinessPayBill',
        'sender_identifier_type'   => 4,
        'reciever_identifier_type' => 4,
        'remarks'                  => 'Business Payment',
        'account_reference'        => 'Transaction',
        'result_url'               => null,
        'timeout_url'              => null,
    ],
    'account_balance' => [
        'short_code'         => null,
        'initiator_name'     => null,
        'initiator_password' => null,
        'default_command_id' => 'AccountBalance',
        'identifier_type'    => 4,
        'remarks'            => 'Balance Query',
        'result_url'         => null,
        'timeout_url'        => null,
    ],
    'transaction_status' => [
        'short_code'         => null,
        'initiator_name'     => null,
        'initiator_password' => null,
        'default_command_id' => 'TransactionStatusQuery',
        'identifier_type'    => 4,
        'remarks'            => 'Status Query',
        'occasion'           => 'Query',
        'result_url'         => null,
        'timeout_url'        => null,
    ],
    'reversal' => [
        'short_code'               => null,
        'initiator_name'           => null,
        'initiator_password'       => null,
        'default_command_id'       => 'TransactionReversal',
        'reciever_identifier_type' => 11,
        'remarks'                  => 'Reversal Request',
        'occasion'                 => 'Reversal',
        'result_url'               => null,
        'timeout_url'              => null,
    ],
    'b2pochi' => [
        'short_code'         => null,
        'initiator_name'     => null,
        'initiator_password' => null,
        'default_command_id' => 'BusinessPayToPochi',
        'remarks'            => 'Pochi Payment',
        'result_url'         => null,
        'timeout_url'        => null,
    ],
];
