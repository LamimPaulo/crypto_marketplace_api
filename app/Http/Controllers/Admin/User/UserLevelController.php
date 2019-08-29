<?php

namespace App\Http\Controllers\Admin\User;

use App\Enum\EnumUserLevelType;
use App\Http\Controllers\Controller;
use App\Http\Requests\LevelRequest;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\TaxCoin;
use App\Models\User\UserLevel;
use App\Models\User\UserLevelLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class UserLevelController extends Controller
{
    public function index()
    {
        try {
            $levels = UserLevel::with([
                'tax_brl',
                'tax_crypto',
                'product',
                'limits' => function ($limits) {
                    return $limits->with('coin');
                }
            ])->get();
            return response([
                'message' => trans('messages.general.success'),
                'levels' => $levels
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function enumTypes()
    {
        return [
            'calc_types' => [
                1 => 'Porcentagem',
                2 => 'Decimal'
            ],
            'operation_types' => [
                3 => 'Envio',
                5 => 'Saque',
            ],
            'tax_types' => [
                1 => 'TED',
                2 => 'Operação',
            ],
            'coins' => Coin::select('id', 'shortname')
                ->where([
                    'is_crypto' => true,
                    'is_wallet' => true,
                    'is_active' => true,
                ])->get()
        ];
    }

    public function store(LevelRequest $request)
    {
        try {
            DB::beginTransaction();
            $level = UserLevel::create($request->all());

            foreach ($request->tax_crypto as $tax) {
                TaxCoin::create([
                    'coin_id' => 1,
                    'user_level_id' => $level->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            foreach ($request->tax_brl as $tax) {
                TaxCoin::create([
                    'coin_id' => 2,
                    'user_level_id' => $level->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Keycode Criado com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(LevelRequest $request)
    {
        $request->validate([
            'tax_crypto' => 'nullable|array',
            'tax_brl' => 'nullable|array',
            'tax_brl.*.user_level_id' => 'required_with:tax_brl|exists:user_levels,id',
            'tax_crypto.*.user_level_id' => 'required_with:tax_crypto|exists:user_levels,id'
        ], [
            'tax_brl.*.user_level_id.required_with' => 'O nível de usuário deve ser informado corretamente para a atualização. (BRL)',
            'tax_brl.*.user_level_id.exists' => 'O nível de usuário informado é inválido. (BRL)',
            'tax_crypto.*.user_level_id.required_with' => 'O nível de usuário deve ser informado corretamente para a atualização. (Crypto)',
            'tax_crypto.*.user_level_id.exists' => 'O nível de usuário informado é inválido. (Crypto)'
        ]);

        try {
            DB::beginTransaction();
            $level = UserLevel::findOrFail($request->id);
            $level->update($request->all());

            $product = Product::findOrFail($request->product_id);
            $product->value = $request->product['value'];
            $product->value_lqx = $request->product['value_lqx'];
            $product->bonus_percent = $request->product['bonus_percent'];
            $product->save();

            //tax coins update
            foreach ($level->taxes as $tax_) {
                $tax_->delete();
            }

            foreach ($request->tax_crypto as $tax) {
                TaxCoin::create([
                    'coin_id' => 1,
                    'user_level_id' => $request->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            foreach ($request->tax_brl as $tax) {
                TaxCoin::create([
                    'coin_id' => 2,
                    'user_level_id' => $request->id,
                    'coin_tax_type' => $tax['coin_tax_type'],
                    'value' => $tax['value'],
                    'operation' => $tax['operation'],
                    'calc_type' => $tax['calc_type']
                ]);
            }

            foreach ($request->limits as $limit) {
                $level_limit = UserLevelLimit::find($limit['id']);
                $level_limit->limit = $limit['limit'];
                $level_limit->limit_auto = $limit['limit_auto'];
                $level_limit->save();
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Keycode Atualizado com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function soldList(request $request)
    {   
        $today = Carbon::now()->format('Y-m-d H:i:s');
        $sevenDays =  Carbon::now()->subweek()->format('Y-m-d H:i:s');
        $fourteenDays = Carbon::now()->subWeek(2)->format('Y-m-d H:i:s');
        $thirteenDays =  Carbon::now()->subMonth(1)->format('Y-m-d H:i:s');
        $sixMonths =  Carbon::now()->subMonth(6)->format('Y-m-d H:i:s');
        try {
            $sold = Transaction::with([
                'user'
            ])
            ->where('category', 12)
            ->orderBy('created_at', 'DESC');
            if (!empty($request->name)) {
                $sold->whereHas('user', function ($user) use ($request) {
                    $user->where('name', 'LIKE', "%{$request->name}%")->orWhere('username', 'LIKE', "%{$request->name}%");
                });
            }
            if (!empty($request->product)){
                switch($request->product) {
                    case 'Free':
                        $sold->where('product_id', 1);
                        break;
                    case 'Basic':
                        $sold->where('product_id', 2);
                        break;
                    case 'Pro':
                        $sold->where('product_id', 3);
                        break;
                    case 'Gold':
                        $sold->where('product_id', 4);
                        break;
                    case 'Infinity':
                        $sold->where('product_id', 5);
                        break;
                    case 'CAD':
                        $sold->where('product_id', 6);
                        break;
                    case 'Free internacional':
                        $sold->where('product_id', 7);
                        break;
                    case 'Basic internacional':
                        $sold->where('product_id', 8);
                        break;
                    case 'Pro internacional':
                        $sold->where('product_id', 9);
                        break;
                    case 'Gold internacional':
                        $sold->where('product_id', 10);
                        break;
                    case 'Infinity internacional':
                        $sold->where('product_id', 11);
                        break;
                    case 'CAD internacional':
                        $sold->where('product_id', 12);
                        break;
                    }
                }
            if(!empty($request->period)) {
                switch($request->period) {
                    case 'Ultimos 7 Dias':
                        $sold->where('created_at', '>=', $sevenDays);
                        break;

                    case 'Ultimos 14 Dias':
                        $sold->where('created_at', '>=', $fourteenDays);
                        break;
                    case 'Ultimos 30 dias':
                        $sold->where('created_at', '>=', $thirteenDays);
                        break;
                    case 'Ultimos 6 meses':
                        $sold->where('created_at', '>=', $sixMonths);
                        break;
                }
            }
                return response([
                'status' => 'success',
                'data' => $sold->paginate(10),
            ], Response::HTTP_OK);

            } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function soldReport(Request $request)
    {
        $product_id = [1,2,3,4,5,6,7,8,9,10,11,12];
        $keycode = [
            '1' => '0', '2' => '0','3' => '0',
            '4' => '0','5' => '0','6' => '0',
            '7' => '0','8' => '0','9' => '0',
            '10' => '0','11' => '0','12' => '0',
        ];
        $fix_amount = [
            '0' => ['1' => '0'],'1' => ['2' => '0'],'2' => ['3' => '0'],
            '3' => ['4' => '0'],'4' => ['5' => '0'],'5' => ['6' => '0'],
            '6' => ['7' => '0'],'7' => ['8' => '0'],'8' => ['9' => '0'],
            '9' => ['10' => '0'],'10' => ['11' => '0'],'11' => ['12' => '0'],
        ];
            try {
                $keycodes_fiat = Transaction::getQuery()
                ->where('coin_id', 2)
                ->select('product_id', DB::raw('count(*) as total'))
                ->where('category', 12)
                ->when($request->period == 'Ultimos 7 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subweek()->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 14 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subweek(2)->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 30 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subDays(30)->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 6 meses',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
                    })
                ->groupBy('product_id')
                ->pluck('total','product_id')->all();

                
                $keycodes_crypto = Transaction::getQuery()
                ->select('product_id', DB::raw('count(*) as total'))
                ->when($request->period == 'Ultimos 7 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subweek()->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 14 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subweek(2)->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 30 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subDays(30)->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 6 meses',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
                    })
                ->where('category', 12)
                ->where('coin_id', 1)
                ->groupBy('product_id')
                ->pluck('total','product_id')->all();
                
                $keycodes_total = Transaction::getQuery()
                ->select('product_id', DB::raw('count(*) as total'))
                ->when($request->period == 'Ultimos 7 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subweek()->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 14 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subweek(2)->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 30 Dias',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subDays(30)->format('Y-m-d H:i:s'));
                    })
                ->when($request->period == 'Ultimos 6 meses',
                    function($q){
                        $q->where('created_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
                    })
                ->where('category', 12)
                ->groupBy('product_id')
                ->pluck('total','product_id')->all();
                
                foreach ($product_id as $id) {
                    $coin_brl[] = Transaction::getQuery()
                    ->select('amount', 'product_id', DB::raw('SUM(amount) as total'))
                    ->when($request->period == 'Ultimos 7 Dias',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subweek()->format('Y-m-d H:i:s'));
                        })
                    ->when($request->period == 'Ultimos 14 Dias',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subweek(2)->format('Y-m-d H:i:s'));
                        })
                    ->when($request->period == 'Ultimos 30 Dias',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subDays(30)->format('Y-m-d H:i:s'));
                        })
                    ->when($request->period == 'Ultimos 6 meses',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
                        })
                    ->where('product_id', $id)
                    ->where('coin_id', 2)
                    ->groupBy('product_id')
                    ->pluck('total', 'product_id')
                    ->all();

                    $coin_crypto[] = Transaction::getQuery()
                    ->select('amount', 'product_id', DB::raw('SUM(amount) as total'))
                    ->when($request->period == 'Ultimos 7 Dias',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subweek()->format('Y-m-d H:i:s'));
                        })
                    ->when($request->period == 'Ultimos 14 Dias',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subweek(2)->format('Y-m-d H:i:s'));
                        })
                    ->when($request->period == 'Ultimos 30 Dias',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subDays(30)->format('Y-m-d H:i:s'));
                        })
                    ->when($request->period == 'Ultimos 6 meses',
                        function($q){
                            $q->where('created_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
                        })
                    ->where('product_id', $id)
                    ->where('coin_id', 1)
                    ->groupBy('product_id')
                    ->pluck('total', 'product_id')
                    ->all();
                }
                
                $fix_coin_brl = array_filter($coin_brl); 
                $amount_brl = $fix_coin_brl + $fix_amount;
                
                $fix_coin_crypto = array_filter($coin_crypto);
                $amount_crypto = $fix_coin_crypto + $fix_amount;

                $count_fiat = $keycodes_fiat + $keycode;
                $count_crypto = $keycodes_crypto + $keycode;
                $count_total = $keycodes_total + $keycode;

                return response([
                'free_Qtd_money' => $count_fiat['1'],
                'free_Qtd_crypto' => $count_crypto['1'],
                'free_Qtd_total' => $count_total['1'],
                'free_money' => 'R$ ' . number_format($amount_brl['0']['1'], 2, ',', '.'),
                'free_crypto' => sprintf('%.8f', $amount_crypto['0']['1']),

                'basic_Qtd_money' => $count_fiat['2'],
                'basic_Qtd_crypto' => $count_crypto['2'],
                'basic_Qtd_total' => $count_total['2'],
                'basic_money' => 'R$ ' . number_format($amount_brl['1']['2'], 2, ',', '.'),
                'basic_crypto' => sprintf('%.8f', $amount_crypto['1']['2']),
                
                'pro_Qtd_money' => $count_fiat['3'],
                'pro_Qtd_crypto' => $count_crypto['3'],
                'pro_Qtd_total' => $count_total['3'],
                'pro_money' => 'R$ ' . number_format($amount_brl['2']['3'], 2, ',', '.'),
                'pro_crypto' => sprintf('%.8f', $amount_crypto['2']['3']),
                
                'gold_Qtd_money' => $count_fiat['4'],
                'gold_Qtd_crypto' => $count_crypto['4'],
                'gold_Qtd_total' => $count_total['4'],
                'gold_money' => 'R$ ' . number_format($amount_brl['3']['4'], 2, ',', '.'),
                'gold_crypto' => sprintf('%.8f', $amount_crypto['3']['4']),
                
                'infinity_Qtd_money' => $count_fiat['5'],
                'infinity_Qtd_crypto' => $count_crypto['5'],
                'infinity_Qtd_total' => $count_total['5'],
                'infinity_money' => 'R$ ' . number_format($amount_brl['4']['5'], 2, ',', '.'),
                'infinity_crypto' => sprintf('%.8f', $amount_crypto['0']['1']),
                
                'cad_Qtd_money' => $count_fiat['6'],
                'cad_Qtd_crypto' => $count_crypto['6'],
                'cad_Qtd_total' => $count_total['6'],
                'cad_money' => 'R$ ' . number_format($amount_brl['5']['6'], 2, ',', '.'),
                'cad_crypto' => sprintf('%.8f', $amount_crypto['5']['6']),
                
                'freeInt_Qtd_money' => $count_fiat['7'],
                'freeInt_Qtd_crypto' => $count_crypto['7'],
                'freeInt_Qtd_total' => $count_total['7'],
                'freeInt_money' => 'R$ ' . number_format($amount_brl['6']['7'], 2, ',', '.'),
                'freeInt_crypto' => sprintf('%.8f', $amount_crypto['6']['7']),
                
                'basicInt_Qtd_money' => $count_fiat['8'],
                'basicInt_Qtd_crypto' => $count_crypto['8'],
                'basicInt_Qtd_total' => $count_total['8'],
                'basicInt_money' => 'R$ ' . number_format($amount_brl['7']['8'], 2, ',', '.'),
                'basicInt_crypto' => sprintf('%.8f', $amount_crypto['7']['8']),
                
                'proInt_Qtd_money' => $count_fiat['9'],
                'proInt_Qtd_crypto' => $count_crypto['9'],
                'proInt_Qtd_total' => $count_total['9'],
                'proInt_money' => 'R$ ' . number_format($amount_brl['8']['9'], 2, ',', '.'),
                'proInt_crypto' => sprintf('%.8f', $amount_crypto['8']['9']),
                
                'goldInt_Qtd_money' => $count_fiat['10'],
                'goldInt_Qtd_crypto' => $count_crypto['10'],
                'goldInt_Qtd_total' => $count_total['10'],
                'goldInt_money' => 'R$ ' . number_format($amount_brl['9']['10'], 2, ',', '.'),
                'goldInt_crypto' => sprintf('%.8f', $amount_crypto['9']['10']),
                
                'infinityInt_Qtd_money' => $count_fiat['11'],
                'infinityInt_Qtd_crypto' => $count_crypto['11'],
                'infinityInt_Qtd_total' => $count_total['11'],
                'infinityInt_money' => 'R$ ' . number_format($amount_brl['10']['11'], 2, ',', '.'),
                'infinityInt_crypto' => sprintf('%.8f', $amount_crypto['10']['11']),
                
                'cadInt_Qtd_money' => $count_fiat['12'],
                'cadInt_Qtd_crypto' => $count_crypto['12'],
                'cadInt_Qtd_total' => $count_total['12'],
                'cadInt_money' => 'R$ ' . number_format($amount_brl['11']['12'], 2, ',', '.'),
                'cadInt_crypto' => sprintf('%.8f', $amount_crypto['11']['12'])

            ], Response::HTTP_OK);

            } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
