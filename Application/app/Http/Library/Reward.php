<?php 

namespace App\Http\Library;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\CustomerReward;

class Reward
{
	private $customer = NULL;
	private $order = NULL;
	private $totalAmount = 0;
	private $usedRewardPoints = 0;
	private $defaultCurrency = 'USD';
	private $currency = 'USD';
	private $currencyConverterAPI = 'http://free.currencyconverterapi.com/api/v5/convert';
	private $currencyConverterAPIKey = '';
	
	function __construct($customer, $order, $currency, $usedRewardPoints)
	{
		$this->customer = $customer;
		$this->order = $order;
		$this->totalAmount = $order->net_total;
		$this->usedRewardPoints = $usedRewardPoints;
		$this->currency = $currency;
		$this->currencyConverterAPIKey = env('CURRENCY_CONVERTER_API_KEY', '');
	}

	/**
	 * trigger reward process 
	 * @return mixed response
	 */
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

	/**
	 * returns total order amount in after converting to USD currency
	 * currency converter https://www.currencyconverterapi.com/ free version
	 * @return numeric response
	 */
	public function getTotalAmountInUSD()
	{
		if(strtolower($this->currency) != strtolower($this->defaultCurrency)){
			$fromCurrency = urlencode($this->currency);
			$defaultCurrency = urlencode($this->defaultCurrency);
			$queryParam =  $fromCurrency."_".$defaultCurrency;
			$converterParam = '?q='.$queryParam.'&compact=y&apiKey='.$this->currencyConverterAPIKey;
			$converterAPIURL = $this->currencyConverterAPI.$converterParam;
			$currencyConverter = Http::get($converterAPIURL);
			if($currencyConverter->successful()){
				$response = $currencyConverter->json();

				$convertedArray = json_decode($response, true);
				$ratio = floatval($convertedArray[$queryParam]);
				$total = $ratio * $this->totalAmount;

				return number_format($total, 2, '.', '');
			}

			// add error log of failed currency conversion with date 
			return 0;
		}

		return $this->totalAmount;
	}
}