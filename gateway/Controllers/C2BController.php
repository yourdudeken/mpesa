<?php

namespace Gateway\Controllers;

use Gateway\Core\Request;
use Gateway\Core\Response;

/**
 * C2B Controller
 * Handles Customer to Business transactions
 */
class C2BController extends BaseController
{
    /**
     * Register C2B URLs
     */
    public function register()
    {
        $request = new Request();

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [];

            // Optional parameters - allow override
            if ($request->has('short_code')) {
                $params['ShortCode'] = $request->input('short_code');
            }
            if ($request->has('confirmation_url')) {
                $params['ConfirmationURL'] = $request->input('confirmation_url');
            }
            if ($request->has('validation_url')) {
                $params['ValidationURL'] = $request->input('validation_url');
            }
            if ($request->has('response_type')) {
                $params['ResponseType'] = $request->input('response_type');
            }

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->C2BRegister($params, $appName);

            $this->logTransaction('C2B_REGISTER', $params, $response);

            Response::success($response, 'C2B URLs registered successfully');

        } catch (\Exception $e) {
            $this->logError('C2B_REGISTER', $e);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Simulate C2B transaction (sandbox only)
     */
    public function simulate()
    {
        $request = new Request();

        // Validate request
        $request->validate([
            'phone_number' => 'required|numeric',
            'amount' => 'required|numeric',
            'bill_ref_number' => 'required'
        ]);

        try {
            // Check if in sandbox mode
            if ($_ENV['MPESA_ENV'] !== 'sandbox') {
                Response::error('C2B simulation is only available in sandbox mode', 400);
            }

            $mpesa = $this->getMpesaInstance();

            $params = [
                'Msisdn' => $this->formatPhoneNumber($request->input('phone_number')),
                'Amount' => $request->input('amount'),
                'BillRefNumber' => $request->input('bill_ref_number'),
                'CommandID' => $request->input('command_id', 'CustomerPayBillOnline')
            ];

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->C2BSimulate($params, $appName);

            $this->logTransaction('C2B_SIMULATE', $params, $response);

            Response::success($response, 'C2B transaction simulated successfully');

        } catch (\Exception $e) {
            $this->logError('C2B_SIMULATE', $e);
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Format phone number to required format (254XXXXXXXXX)
     */
    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[\s\-\+]/', '', $phone);

        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }

        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }

        return $phone;
    }
}
