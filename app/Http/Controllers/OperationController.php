<?php

namespace App\Http\Controllers;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Admin\CoinsController;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationController extends Controller
{

    public function index(Request $request)
    {

        try {
            $input = $this->_decryptRequest($request);

            switch ($input['type']) {
                case EnumOperationType::CHECK_AUTHENTICITY:
                    $result = $this->_checkTransaction($input['data']);
                    break;

                case EnumOperationType::NOTIFY_WALLET:
                    $result = Notify\BTCController::notify($input['data']);
                    break;

                case EnumOperationType::CORE_WITHDRAWAL_LIMIT:
                    $result = CoinsController::coreLimitWithdrawal($input['data']);
                    break;

                default:
                    return response(['message' => trans('messages.general.invalid_data')], 422);
            }

            return $this->_encryptResponse($result);
        } catch (\Exception $ex) {
            return response(['message' => $ex->getMessage()], 422);
        }
    }

    private function _decryptRequest(Request $request)
    {
        $input = $request->all();
        return decrypt($input[0]);
    }

    private function _encryptResponse($response)
    {
        return encrypt($response);
    }

    private function _checkTransaction($data)
    {

        $query = "
            SELECT transactions.*, user_wallets.address FROM transactions 
            INNER JOIN user_wallets ON user_wallets.id = transactions.wallet_id 
            WHERE transactions.toAddress = '" . $data['toAddress'] . "' 
            AND transactions.fee = '" . $data['fee'] . "' 
            AND transactions.status IN (" . EnumTransactionsStatus::AUTHORIZED . " ) 
            AND transactions.type = " . EnumTransactionType::OUT . " 
            AND user_wallets.address = '" . $data['fromAddress'] . "' 
            AND transactions.amount = '" . $data['amount'] . "'";

        $result = DB::select(DB::raw($query));
        if (is_null($result)) {
            throw new \Exception(trans('messages.transaction.invalid'));
        }
        return $result;
    }

    public function fees()
    {
        try {
            $coins = Coin::where('is_crypto', true)->where('is_wallet', true)->where('is_active', true)->get();

            $newCoins = [];

            foreach ($coins as $coin) {
                $fee_1 = OffScreenController::post(EnumOperationType::ESTIMATE_SMART_FEE, 1, $coin->abbr);
                $fee_3 = OffScreenController::post(EnumOperationType::ESTIMATE_SMART_FEE, 3, $coin->abbr);
                $fee_6 = OffScreenController::post(EnumOperationType::ESTIMATE_SMART_FEE, 6, $coin->abbr);
                $coin->fee_low = is_numeric($fee_6) ? $fee_6 : 0.00001;
                $coin->fee_avg = is_numeric($fee_3) ? $fee_3 : 0.00001;
                $coin->fee_high = is_numeric($fee_1) ? $fee_1 : 0.00001;
                $coin->save();

                array_push($newCoins, $coin);
            }

            return $newCoins;

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

}
