Total order amount: MYR 5.00
GST %: 6


$cost + 0.06*$cost = 5
1.06*$cost = 5
$cost = 5/1.06
$cost = 4.71


GST Amount: (4.71*0.06) = 0.28

*** 
added function to calculate GST in Application/app/Http/Controllers/OrderController.php function "calculateGST"

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
***