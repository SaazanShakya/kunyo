SQL to retrieve the total order and sales amount of 
all the orders with output displaying Number_Of_Order, Total_Sales_Amount

SELECT 
	count(DISTINCT `o`.`id`) AS Number_Of_Order, 
	SUM(
		CASE 
			WHEN `o`.`sales_type` = 'Promotion' THEN `op`.`promotion_price` 
			ELSE `op`.`normal_price` 
		END
		) AS Total_Sales_Amount 
FROM `orders` AS `o` 
LEFT JOIN `order_products` AS `op`
ON `o`.`id`=`op`.`order_id`


RESULT:

Number_Of_Order | Total_Sales_Amount
6					3545.97
