<?php

namespace Gateway\Controllers;

use Gateway\Core\Request;
use Gateway\Core\Response;
use Yourdudeken\Mpesa\Init;

/**
 * STK Push Controller
 * Handles Lipa Na M-Pesa Online requests
 */
class STKPushController extends BaseController
{
    /**
     * Initiate STK Push
     */
    public function initiate()
    {
        $request = new Request();

        // Validate request
        $request->validate([
            'phone_number' => 'required|numeric',
            'amount' => 'required|numeric',
            'account_reference' => 'required',
            'transaction_desc' => 'required'
        ]);

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [
                'PhoneNumber' => $this->formatPhoneNumber($request->input('phone_number')),
                'Amount' => $request->input('amount'),
                'AccountReference' => $request->input('account_reference'),
                'TransactionDesc' => $request->input('transaction_desc'),
            ];

            // Optional parameters
            if ($request->has('callback_url')) {
                $params['CallBackURL'] = $request->input('callback_url');
            }

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->STKPush($params, $appName);

            // Log the transaction
            $this->logTransaction('STK_PUSH', $params, $response);

            Response::success($response, 'STK Push initiated successfully');

        } catch (\Exception $e) {
            $this->logError('STK_PUSH', $e);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Query STK Push status
     */
    public function query()
    {
        $request = new Request();

        // Validate request
        $request->validate([
            'checkout_request_id' => 'required'
        ]);

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [
                'CheckoutRequestID' => $request->input('checkout_request_id')
            ];

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->STKStatus($params, $appName);

            Response::success($response, 'STK Push status retrieved successfully');

        } catch (\Exception $e) {
            $this->logError('STK_QUERY', $e);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Format phone number to required format (254XXXXXXXXX)
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any spaces, dashes, or plus signs
        $phone = preg_replace('/[\s\-\+]/', '', $phone);

        // If starts with 0, replace with 254
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }

        // If doesn't start with 254, add it
        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }

        return $phone;
    }
}
