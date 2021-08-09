<?php

namespace App\Http\Controllers;

use App\Http\Library\Reward;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    private $pointRewardAmount = 0.01;

    /**
     * validate and save order request 
     * @param  Request $request Illuminate\Http\Request
     * @return mixed response
     */
    public function save(Request $request)
    {
        // validate Order request like (authenticated user?, requested vs available product quantity and other input validations like available reward points considering the expiry date)
        // Separate Request Validation class can also be used for validation. 
        // ....
        
        if($requestValidated){
            try{
                $customer = Auth::user(); // customer needs to be registered as user
                $currency = Request::get('currency', 'USD');
                $totalAmount = Request::get('total_amount', 0);
                $usedRewardPoints = Request::get('used_reward_points', 0);

                $order = new Order();
                // $order->....
                $order->total = $totalAmount;
                $order->points_used = $usedRewardPoints;
                $order->net_total = $this->calculateNetTotal($totalAmount, $usedRewardPoints);
                /**
                 * total amount/price of order and product price will have to processed from UI according to the use of customer's available reward points before final checkout step
                 * process order request and save
                 * and save ordered products
                **/
                $orderProducts = []; // array of ordered products
                $order->products()->saveMany($orderProducts);
                
                // if sales order is in "Complete" status or reward points are used in order, trigger reward system to credit or deduct reward
                if(@$order->status == 'complete' || $usedRewardPoints > 0){
                    $reward = new Reward($customer, $order, $currency, $usedRewardPoints);
                    $reward->updateRewardPoints();
                }
                
                // return success response;
            }catch (Exception $e){
                // add error log
                // return error response;
            }
        }else{
            // return validation error response
        }
    }

    /**
     * calculate net total of the order request after deducting points amount
     * @param  integer $total  total amount of order
     * @param  integer $points points
     * @return numeric response
     */
    private function calculateNetTotal($total = 0, $points = 0)
    {
        if($total > 0 && $points > 0){
            $pointReward = $points * $this->pointRewardAmount;

            return (double)$total - (double)$pointReward;
        }

        return 0;
    }

    /**
     * returns GST of an amount by amount type
     * @param  Request $request Illuminate\Http\Request
     * @return numeric response
     */
    public function calculateGST(Request $request)
    {
        $amount = Request::get('amount');
        $gstRate = Request::get('gst_rate', 6);
        $type = Request::get('amount_type', 'cost');
        $gstAmount = 'N/A';

        switch ($type) {
            case 'cost':
                $gstAmount = $amount*($gstRate/100);
                break;
            
            default:
                $gstRate = $gstRate/100;
                $costAmount = $amount/(1+$gstRate);
                $gstAmount = $costAmount*$gstRate;
                break;
        }

        return $gstAmount;
    }
}
