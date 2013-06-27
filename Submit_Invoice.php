<?

$Database = new PDO("odbc:Driver={MYOAU1001}; Database=MYOBDevelopment; ");
	
$SQLString = 'INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES ( ';

$PONumber				= $_POST['PONumber'];
$ItemNumberArray 		= $_POST['ItemNumber'];
$DeliveryStatus		 	= $_POST['DeliveryStatus'];
$QuantityArray 			= $_POST['Quantity'];
$DescriptionArray 		= $_POST['Description'];
$ExTaxTotalArray 		= $_POST['ExTaxTotal'];
$IncTaxTotalArray 		= $_POST['IncTaxTotal'];
$CardID					= $_POST['CardID'];

foreach($QuantityArray as $key => $value) {
	$SQLString .= "('". $PONumber ."','". $ItemNumberArray[$key] ."','". $DeliveryStatus ."','". $QuantityArray[$key] 
	."','". $DescriptionArray[$key] ."','". $ExTaxTotalArray[$key] ."','". $IncTaxTotalArray[$key] ."','". $CardID ."') ,";
}

$SQLString = rtrim($SQLString, ",");
$SQLString .= ')';

$SQLStatement = $Database -> prepare ($SQLString);
$SQLStatement->execute();

echo file_get_contents('C:\Users\aaron.r\AppData\Local\Temp\MYOBODBCError.txt', true);

?>
