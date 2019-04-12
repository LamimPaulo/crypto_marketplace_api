<?php

namespace App\Http\Controllers;

use App\Enum\EnumPaypalStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Models\Coin;
use App\Models\Transaction;
use App\PaypalAuth;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;

class PaymentProviderController extends Controller
{
    public function paypalConfirmations()
    {

        $access_token = $this->accessToken();

        $BRL = Coin::getByAbbr('BRL')->id;
        $USD = Coin::getByAbbr('USD')->id;

        $payments = Transaction::where([
            'status' => EnumTransactionsStatus::PENDING,
            'type' => EnumTransactionType::IN,
            'category' => EnumTransactionCategory::DEPOSIT,
            'payment_provider_id' => 2
        ])
            ->whereIn('coin_id', [$BRL, $USD])->orderBy('created_at')->get();

        if ($payments->count()) {
            $http = new \GuzzleHttp\Client();
            foreach ($payments as $p) {
                try {
                    DB::beginTransaction();
                    $response = $http->request('GET', config('paypal.url') . 'payments/payment/' . $p->tx, ['headers' => ['Authorization' => "Bearer {$access_token}"]]);
                    $contents = json_decode($response->getBody()->getContents());

                    if (!$contents->state) {
                        continue;
                    }

                    if ($contents->state === EnumPaypalStatus::approved) {
                        $this->balanceService::increments($p);
                    }

                    $paymentToUpdate = Transaction::where('tx', $p->tx)->first();
                    $paymentToUpdate->status = EnumPaypalStatus::STATUS[$contents->state];
                    $paymentToUpdate->save();
                    DB::commit();

                } catch (ClientException $e) {
                    DB::rollBack();

                    $paymentToUpdate = Transaction::where('tx', $p->tx)->first();
                    $paymentToUpdate->status = EnumPaypalStatus::failed;
                    $paymentToUpdate->error = $e->getMessage();
                    $paymentToUpdate->save();
                    continue;
                }
            }
        }

    }

    private function accessToken()
    {
        $token = PaypalAuth::where('expires_in', '>=', Carbon::now())->first();

        if (!$token) {
            $accessToken = $this->getAccessToken();
            if ($accessToken['status'] == 1) {
                $token = PaypalAuth::create([
                    'app_id' => $accessToken['json']['app_id'],
                    'access_token' => $accessToken['json']['access_token'],
                    'expires_in' => Carbon::now()->addSeconds($accessToken['json']['expires_in'])
                ]);
            }
        }

        return $token->access_token;
    }

    private function getAccessToken()
    {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, config("paypal.url") . "oauth2/token");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, config("paypal.client_id") . ":" . config("paypal.secret"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

            $result = curl_exec($ch);
            $err = curl_error($ch);

            if ($err) {
                throw new \Exception($err);
            }

            $json = json_decode($result);
            if (isset($json->error)) {
                throw new \Exception($json->error . ': ' . $json->error);
            }
            return ['status' => 1, 'json' => collect($json)];
        } catch (\Exception $e) {
            return ['status' => 0, 'json' => $e->getMessage()];
        }
    }
}
