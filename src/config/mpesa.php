<?php

/**
 * M-Pesa Package Internal Configuration
 * 
 * This file contains package defaults including certificate paths,
 * and API endpoints. 
 * 
 * Everything else is provided by the user via the constructor 
 * or environment variables.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | API Domains
    |--------------------------------------------------------------------------
    */
    'apiUrl' => 'https://sandbox.safaricom.co.ke/',
    
    // Fallback URLs
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
    | Transaction Defaults (Command IDs & Metadata)
    |--------------------------------------------------------------------------
    | These ensure the package works out-of-the-box for standard use cases.
    */
    'lnmo' => [
        'default_transaction_type' => 'CustomerPayBillOnline',
        'transaction_desc'         => 'Transaction',
        'account_reference'        => 'Transaction',
    ],
    'c2b' => [
        'default_command_id' => 'CustomerPayBillOnline',
        'response_type'      => 'Completed',
        'remarks'            => 'Transaction',
    ],
    'b2c' => [
        'default_command_id' => 'BusinessPayment',
        'remarks'            => 'Business Payment',
        'occasion'           => 'Payment',
    ],
    'b2b' => [
        'default_command_id'       => 'BusinessPayBill',
        'sender_identifier_type'   => 4,
        'reciever_identifier_type' => 4,
        'remarks'                  => 'Business Payment',
        'account_reference'        => 'Transaction',
    ],
    'account_balance' => [
        'default_command_id' => 'AccountBalance',
        'identifier_type'    => 4,
        'remarks'            => 'Balance Query',
    ],
    'transaction_status' => [
        'default_command_id' => 'TransactionStatusQuery',
        'identifier_type'    => 4,
        'remarks'            => 'Status Query',
        'occasion'           => 'Query',
    ],
    'reversal' => [
        'default_command_id'       => 'TransactionReversal',
        'reciever_identifier_type' => 11,
        'remarks'                  => 'Reversal Request',
        'occasion'                 => 'Reversal',
    ],
    'b2pochi' => [
        'default_command_id' => 'BusinessPayToPochi',
        'remarks'            => 'Pochi Payment',
    ],
];
