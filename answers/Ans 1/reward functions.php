Reward credit process is implemented in laravel project under "Application" folder

Process: 

1. Customer finalizes the order and submit.
2. Order Request is processed by "App\Http\Controllers\OrderController.php" function "save()". Impact of rewards on db tables are described as well.
 		TABLE "orders" columns:
 		`user_id` int,
 		`order_date` datetime,
 		`sales_type` varchar,
 		`total` double,
 		`points_used` double,
 		`net_total` double,
 		`status` varchar,

	public function save(Request $request)
    {
        // validate Order request like (authenticated user?, requested vs available product quantity and other input validations)
        // ....
        
        if($requestValidated){
            try{
                $customer = Auth::user(); // customer needs to be registered as user
                $currency = Request::get('currency', 'USD');
                $totalAmount = Request::get('total_amount', 0);
                $usedRewardPoints = Request::get('used_reward_points', 0);

                $order = new Order();
                // $order->....

                /*** REWARDS IMPACT ON DB LEVEL
                 * points used are stored in "orders" table
                 * total and net amount after deducting points as well
                 ***/

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

3. System validates and processes the request.
4. If order status is complete or reward points are used, "App\Http\Library\Reward.php" function "updateRewardPoints()" is triggered to add reward points or deduct used points.
5. In case of complete order status, customer reward will be added by calculating reward points(converting to USD currency if diffrent currency is used) and deducting reward point if any used.

	TABLE "customer_rewards" columns:
	`user_id`,
	`points`,
	`used_points`,
	`remaining_points`,
	`expiry_date`,

	public function updateRewardPoints()
	{
		$customerReward = CustomerReward::firstOrNew(['user_id' => $this->customer->id]);
		
		if($this->currency != 'USD'){
			// get total amount in USD
			$this->totalAmount = $this->getTotalAmountInUSD();
		}

		$reward = $this->totalAmount >= 1?$this->totalAmount:0;

		/*
		* reward greater than or equals 1 is only elligible for customer reward
		* OR
		* user used reward points which means user must already have been rewarded
		*/
		if($reward >= 1 || ($this->usedRewardPoints > 0 && !empty($customerReward->id))){

			/*** REWARDS IMPACT ON DB LEVEL
			 * columns in "customer_rewards" table, "points", "used_points", "remaining_points" and "expiry_date"
			 * In case of first purchase, this condition will add reward points in "customer_rewards" table by calculating and converting(if required) order amount. Columns "points" and "remaining_points" and "expiry_date" will be updated.
			 * In case of existing customer reward, new points will be added to remaining points and deducted if any reward points used. Columns "points", "used_points", "remaining_points" and "expiry_date" will be updated.
			 ***/

			$customerReward->user_id = $this->customer->id;
			$customerReward->points = (!empty($customerReward->points) && is_numeric($customerReward->points))?((double)$customerReward->points + (double)$reward):$reward;
			$customerReward->used_points = (!empty($customerReward->used_points) && is_numeric($customerReward->used_points))?((double)$customerReward->used_points + (double)$this->usedRewardPoints):$this->usedRewardPoints;

			$remainingPoints = ((double)$reward - (double)$this->usedRewardPoints);
			$customerReward->remaining_points = (!empty($customerReward->remaining_points) && is_numeric($customerReward->remaining_points))?((double)$customerReward->remaining_points + (double)$reward - (double)$this->usedRewardPoints):($remainingPoints < 0?0:$remainingPoints);
			if($reward < 1){
				$customerReward->expiry_date =  Carbon::now()->addYear();
			}
			$customerReward->save();
		}

		return $customerReward;
	}

6. Order request is complete and customer is displayed success confirmation message.