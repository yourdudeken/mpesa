<?php

namespace Gateway\Controllers;

use Gateway\Core\Request;
use Gateway\Core\Response;

/**
 * Callback Controller
 * Handles M-Pesa callback requests
 */
class CallbackController extends BaseController
{
    /**
     * Handle STK Push callback
     */
    public function stkpush()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('STK_PUSH_CALLBACK', $data);

        // Process the callback data
        // You can save to database, trigger events, send notifications, etc.

        // M-Pesa expects a success response
        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle C2B validation callback
     */
    public function c2bValidation()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('C2B_VALIDATION', $data);

        // Perform validation logic here
        // Return ResultCode 0 to accept, or non-zero to reject

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }

    /**
     * Handle C2B confirmation callback
     */
    public function c2bConfirmation()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('C2B_CONFIRMATION', $data);

        // Process confirmed transaction
        // Save to database, update records, etc.

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle B2C result callback
     */
    public function b2cResult()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('B2C_RESULT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle B2C timeout callback
     */
    public function b2cTimeout()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('B2C_TIMEOUT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle B2B result callback
     */
    public function b2bResult()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('B2B_RESULT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle B2B timeout callback
     */
    public function b2bTimeout()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('B2B_TIMEOUT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle balance result callback
     */
    public function balanceResult()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('BALANCE_RESULT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle balance timeout callback
     */
    public function balanceTimeout()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('BALANCE_TIMEOUT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle reversal result callback
     */
    public function reversalResult()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('REVERSAL_RESULT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle reversal timeout callback
     */
    public function reversalTimeout()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('REVERSAL_TIMEOUT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle status result callback
     */
    public function statusResult()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('STATUS_RESULT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }

    /**
     * Handle status timeout callback
     */
    public function statusTimeout()
    {
        $request = new Request();
        $data = $request->all();

        $this->logCallback('STATUS_TIMEOUT', $data);

        Response::json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success'
        ]);
    }
}
