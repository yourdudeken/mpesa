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
];
