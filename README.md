Reward credit process is implemented in laravel 8 project under "Application" folder

## Kunyo Test Solution

Answers are listed under "answers" folder.
- For Question 1, under "Ans 1" folder, 
	database schema "kunyo-db-schema.sql", diagram "kunyo-db-schema-diagram.png", reward flow chart "kunyo-reward-flowchart.png" and "reward functions.php" files are listed.
	
	Reward credit process is implemented in laravel project under "Application" folder

	Process: 

	1. Customer finalizes the order and submit.
	2. Order Request is handled by "App\Http\Controllers\OrderController.php" function "save()".
	3. System validates and processes the request.
	4. If order status is complete or reward points are used, Class "App\Http\Library\Reward.php" function "updateRewardPoints()" is triggered to add reward points or deduct used points.
	5. In case of complete order status, customer reward will be added by calculating reward points(converting to USD currency if diffrent currency is used) and deducting reward point if any used.
	6. Order request is complete and customer is displayed success confirmation message.

- For Question 2, "answer2.php" file contains the sql and result of query.
- For Question 3, "answer3.php" file contains the calculation and result of GST.


## About Laravel
Laravel is accessible, yet powerful, providing tools needed for large, robust applications. A superb combination of simplicity, elegance, and innovation give you tools you need to build any application with which you are tasked.