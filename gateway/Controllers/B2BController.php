<?php

namespace Gateway\Controllers;

use Gateway\Core\Request;
use Gateway\Core\Response;

/**
 * B2B Controller
 * Handles Business to Business payments
 */
class B2BController extends BaseController
{
    /**
     * Initiate B2B payment
     */
    public function payment()
    {
        $request = new Request();

        // Validate request
        $request->validate([
            'receiver_shortcode' => 'required|numeric',
            'amount' => 'required|numeric'
        ]);

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [
                'PartyB' => $request->input('receiver_shortcode'),
                'Amount' => $request->input('amount'),
                'Remarks' => $request->input('remarks', 'B2B Payment'),
                'AccountReference' => $request->input('account_reference', 'Account')
            ];

            // Optional parameters
            if ($request->has('command_id')) {
                $params['CommandID'] = $request->input('command_id');
            }
            if ($request->has('sender_identifier_type')) {
                $params['SenderIdentifierType'] = $request->input('sender_identifier_type');
            }
            if ($request->has('receiver_identifier_type')) {
                $params['RecieverIdentifierType'] = $request->input('receiver_identifier_type');
            }
            if ($request->has('result_url')) {
                $params['ResultURL'] = $request->input('result_url');
            }
            if ($request->has('timeout_url')) {
                $params['QueueTimeOutURL'] = $request->input('timeout_url');
            }

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->B2B($params, $appName);

            $this->logTransaction('B2B_PAYMENT', $params, $response);

            Response::success($response, 'B2B payment initiated successfully');

        } catch (\Exception $e) {
            $this->logError('B2B_PAYMENT', $e);
            Response::error($e->getMessage(), 500);
        }
    }
}
