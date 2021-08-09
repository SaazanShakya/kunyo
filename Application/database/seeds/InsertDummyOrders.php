<?php

use Illuminate\Database\Seeder;

class InsertDummyOrders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orders = [
            ['id' => 1001, 'order_date' => '2007-05-01 12:10:10', 'sales_type' => 'Normal'],
            ['id' => 1002, 'order_date' => '2007-05-07 05:28:55', 'sales_type' => 'Normal'],
            ['id' => 1003, 'order_date' => '2007-05-19 17:17:00', 'sales_type' => 'Promotion'],
            ['id' => 1004, 'order_date' => '2007-05-22 22:47:16', 'sales_type' => 'Promotion'],
            ['id' => 1005, 'order_date' => '2007-05-27 08:15:07', 'sales_type' => 'Promotion'],
            ['id' => 1006, 'order_date' => '2007-06-01 06:35:59', 'sales_type' => 'Normal']

        ];

        $orderProducts = [
            ['id' => 2000, 'order_id' => 1001, 'item_name' => 'Radio', 'normal_price' => 800.00, 'promotion_price' => 712.99], 
            ['id' => 2001, 'order_id' => 1002, 'item_name' => 'Portable Audio', 'normal_price' => 16.00, 'promotion_price' => 15.00], 
            ['id' => 2002, 'order_id' => 1002, 'item_name' => 'THE SIMS', 'normal_price' => 9.99, 'promotion_price' => 8.79], 
            ['id' => 2003, 'order_id' => 1003, 'item_name' => 'Radio', 'normal_price' => 800.00, 'promotion_price' => 712.99], 
            ['id' => 2004, 'order_id' => 1004, 'item_name' => 'Scanner', 'normal_price' => 124.00, 'promotion_price' => 120.00], 
            ['id' => 2005, 'order_id' => 1005, 'item_name' => 'Portable Audio', 'normal_price' => 16.00, 'promotion_price' => 15.00], 
            ['id' => 2006, 'order_id' => 1005, 'item_name' => 'Radio', 'normal_price' => 800.00, 'promotion_price' => 712.99], 
            ['id' => 2007, 'order_id' => 1006, 'item_name' => 'Camcorders', 'normal_price' => 359.00, 'promotion_price' => 303.00], 
            ['id' => 2008, 'order_id' => 1006, 'item_name' => 'Radio', 'normal_price' => 800.00, 'promotion_price' => 712.99]
        ];

        \DB::transaction(function() use($orders, $orderProducts){
            \DB::table('test_orders')->insert($orders);
            \DB::table('test_order_products')->insert($orderProducts);
        });
    }
}
