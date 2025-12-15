<?php

namespace Gateway\Controllers;

use Gateway\Core\Request;
use Gateway\Core\Response;

/**
 * Account Controller
 * Handles account balance queries
 */
class AccountController extends BaseController
{
    /**
     * Query account balance
     */
    public function balance()
    {
        $request = new Request();

        try {
            $mpesa = $this->getMpesaInstance();

            $params = [
                'Remarks' => $request->input('remarks', 'Account Balance Query')
            ];

            // Optional parameters
            if ($request->has('party_a')) {
                $params['PartyA'] = $request->input('party_a');
            }
            if ($request->has('identifier_type')) {
                $params['IdentifierType'] = $request->input('identifier_type');
            }
            if ($request->has('result_url')) {
                $params['ResultURL'] = $request->input('result_url');
            }
            if ($request->has('timeout_url')) {
                $params['QueueTimeOutURL'] = $request->input('timeout_url');
            }

            $appName = $request->input('app_name', 'default');
            $response = $mpesa->accountBalance($params, $appName);

            $this->logTransaction('ACCOUNT_BALANCE', $params, $response);

            Response::success($response, 'Account balance query initiated successfully');

        } catch (\Exception $e) {
            $this->logError('ACCOUNT_BALANCE', $e);
            Response::error($e->getMessage(), 500);
        }
    }
}
