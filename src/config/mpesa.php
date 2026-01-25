<?php
/**
 * M-Pesa Package Internal Configuration
 * 
 * This file ONLY contains certificate paths.
 * All other configuration must be provided externally via:
 * - config/mpesa.php in your project root
 * - Passing config array to Mpesa constructor
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Certificate Paths (Internal Only)
    |--------------------------------------------------------------------------
    |
    | These paths point to the SSL certificates included with the package.
    | Users should NOT modify these paths.
    |
    */
    'certificate_path_sandbox' => __DIR__ . '/SandboxCertificate.cer',
    'certificate_path_production' => __DIR__ . '/ProductionCertificate.cer',
];
