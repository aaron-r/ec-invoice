<?php
// INSERT INTO Import_Item_Sales (A, B, C) VALUES (?, ?, ?),(?, ?, ?),(?, ?, ?)

$db = new PDO("odbc:Driver={MYOAU1001}; Database=MYOBDevelopment; ");

// Initial "inshop"/"on-site" + other statics set here.
$CustomersNumber = "12345";
$ItemNumber = "100";						// Changes
$DeliveryStatus = "P";
$Quantity = 1;								// Changes
$Description = "Workshop Service Rate";		// Changes
$ExTaxTotal = 95.00;
$IncTaxTotal = 0.00;						// Changes
$CardID = "CUS000001";

$qAmmount = 2;

$db->beginTransaction();

$query = "INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES (";
$data = array($CustomersNumber,$ItemNumber,$DeliveryStatus,$Quantity,$Description,$ExTaxTotal,$IncTaxTotal,$CardID);

// Clean-up SQL query
$qPart = array_fill(0, $qAmmount, "(?, ?, ?, ?, ?, ?, ?, ?)");
$query .= implode(", ",$qPart);
$query .= ")";

$desc = array("Found fake anti virus.","Ran Virus Scanner, removed 13 bad entries","Updates all OK!");

$i = 1;
$a = 1;

echo $query.'<p>';
$stmt = $db -> prepare($query);

while ($a <= 3) {

	foreach ($data as $item) {
		// Loop through & write to query here
		
		echo $i ." - ". $item . "<br>";
		$stmt->bindParam($i++, $item);
		
		if ($i == 9) {
			echo '<p>';
			$i = 1;
		}
	}

	// Change variables here
	$Quantity = 1;
	$ItemNumber = "100";
	$Description = $desc[$a];		// Error Check to see if enough values in array!
	$IncTaxTotal = 0.00;
	$a++;
	// Re-set $data array
	$data = array($CustomersNumber,$ItemNumber,$DeliveryStatus,$Quantity,$Description,$ExTaxTotal,$IncTaxTotal,$CardID);
}

$stmt -> execute();
$db->commit();

echo file_get_contents('C:\Users\aaron.r\AppData\Local\Temp\MYOBODBCError.txt', true);

?>
