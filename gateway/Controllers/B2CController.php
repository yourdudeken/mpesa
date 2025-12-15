<?php

namespace Gateway\Controllers;

use Gateway\Core\Request;
use Gateway\Core\Response;

/**
 * B2C Controller
 * Handles Business to Customer payments
 */
class B2CController extends BaseController
{
    /**
     * Initiate B2C payment
     */
    public function payment()
    {
        $request = new Request();

        // Validate request
        $request->validate([
            'phone_number' => 'required|numeric',
            'amount' => 'required|numeric'
        ]);

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [
                'PartyB' => $this->formatPhoneNumber($request->input('phone_number')),
                'Amount' => $request->input('amount'),
                'Remarks' => $request->input('remarks', 'B2C Payment'),
                'Occasion' => $request->input('occasion', '')
            ];

            // Optional parameters
            if ($request->has('command_id')) {
                $params['CommandID'] = $request->input('command_id');
            }
            if ($request->has('result_url')) {
                $params['ResultURL'] = $request->input('result_url');
            }
            if ($request->has('timeout_url')) {
                $params['QueueTimeOutURL'] = $request->input('timeout_url');
            }

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->B2C($params, $appName);

            $this->logTransaction('B2C_PAYMENT', $params, $response);

            Response::success($response, 'B2C payment initiated successfully');

        } catch (\Exception $e) {
            $this->logError('B2C_PAYMENT', $e);
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
