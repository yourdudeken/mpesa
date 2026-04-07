<?php

namespace Yourdudeken\Mpesa\Exceptions;

use Exception;

class MpesaException extends Exception
{
    public function __construct(string $message, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function invalidTransactionType(string $type): self
    {
        return new self("Invalid transaction type: {$type}. Use Mpesa::PAYBILL or Mpesa::TILL.");
    }

    public static function missingAccountReference(): self
    {
        return new self('An Account Reference is required for All transactions.');
    }

    public static function missingTillNumber(): self
    {
        return new self('Till number is required for Buy Goods transactions.');
    }

    public static function missingCallbackUrl(string $key): self
    {
        return new self("Ensure you have set the {$key} in the config or passed as a parameter");
    }

    public static function missingB2BAccountNumber(): self
    {
        return new self('Account Number is required for BusinessPayBill CommandID');
    }

    public static function authenticationFailed(string $message = 'Authentication failed'): self
    {
        return new self($message, 401);
    }

    public static function apiError(string $message, int $code = 0): self
    {
        return new self($message, $code);
    }
}
