<?php

use Illuminate\Database\Seeder;
use App\Models\Transaction;

class UpdateTransactionsProductId extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transactions = Transaction::get('info', 'product_id');

        foreach ($transactions as $transaction) {
            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Free',
                'Compra de Keycode: Free',
                'Compra de Nível: Free'
            ))
            ->update(['product_id' =>  1 ]);

            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Basic',
                'Compra de Keycode: Basic',
                'Compra de Nível: Basic'
            ))
            ->update(['product_id' =>  2 ]);
            
            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Pro',
                'Compra de Keycode: Pro',
                'Compra de Nivel: Pro'
            ))
            ->update(['product_id' =>  3 ]);

            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Gold',
                'Compra de Keycode: Gold',
                'Compra de Nivel: Gold'
            ))
            ->update(['product_id' =>  4 ]);

            
            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Infinity',
                'Compra de Keycode: Infinity',
                'Compra de Nivel: Infinity'
            ))
            ->update(['product_id' =>  5 ]);


            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Licença CAD',
                'Compra de Keycode: Licença CAD',
                'Compra de Nivel: Licença CAD'
            ))
            ->update(['product_id' =>  6 ]);
            
            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Free (Outwards)',
                'Compra de Keycode - Free International',
                'Compra de Keycode: Free International',
                'Keycode Purchase: Free International'
            ))
            ->update(['product_id' =>  7 ]);

            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Basic (Outwards)',
                'Compra de Keycode - Basic International',
                'Compra de Keycode: Basic International',
                'info', 'Keycode Purchase: Basic International'
            ))
            ->update(['product_id' =>  8 ]);
            
            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Pro (Outwards)',
                'Compra de Keycode: Pro International',
                'Compra de Nivel: Pro International',
                'info', 'Keycode Purchase: Pró International'
            ))
            ->update(['product_id' =>  9 ]);

            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Gold (Outwards)',
                'Compra de Keycode - Gold International',
                'Compra de Keycode: Gold International',
                'Compra de Nivel: Gold International',
                'Keycode Purchase: Gold International'
            ))
            ->update(['product_id' =>  10 ]);

            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Infinity (Outwards)',
                'Compra de Keycode: Infinity International',
                'Compra de Nivel: Infinity International',
                'Keycode Purchase: Infinity International'
            ))
            ->update(['product_id' =>  11 ]);

            Transaction::where('category', 12)
            ->whereIn('info', array(
                'Compra de Keycode - Licença CAD International',
                'Compra de Keycode: Licença CAD International',
                'Compra de Nivel: Licença CAD International',
                'Keycode Purchase: CAD International'
            ))                                    
            ->update(['product_id' =>  12 ]);
        }
    }
}
