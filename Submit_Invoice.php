<?

$Database = new PDO("odbc:Driver={MYOAU1001}; Database=MYOBDevelopment; ");

$SQLStatement = $Database->prepare ('INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');

$PONumberArray			= $_POST['PONumber'];
$ItemNumberArray 		= $_POST['ItemNumber'];
$DeliveryStatusArray 	= $_POST['DeliveryStatus'];
$QuantityArray 			= $_POST['Quantity'];
$DescriptionArray 		= $_POST['Description'];
$ExTaxTotalArray 		= $_POST['ExTaxTotal'];
$IncTaxTotalArray 		= $_POST['IncTaxTotal'];
$CardIDArray 			= $_POST['CardID'];

// $SQLStatement->bindParam(1, $PONumberArray);
// $SQLStatement->bindParam(2, $ItemNumberArray[0]);
// $SQLStatement->bindParam(3, $DeliveryStatusArray);
// $SQLStatement->bindParam(4, $QuantityArray[0]);
// $SQLStatement->bindParam(5, $DescriptionArray[0]);
// $SQLStatement->bindParam(6, $ExTaxTotalArray[0]);
// $SQLStatement->bindParam(7, $IncTaxTotalArray[0]);
// $SQLStatement->bindParam(8, $CardIDArray);

// $SQLStatement->execute();

$SQLStatement->execute(array($PONumberArray,$ItemNumberArray,$DeliveryStatusArray,$QuantityArray,$DescriptionArray,$ExTaxTotalArray,$IncTaxTotalArray,$CardIDArray));

echo file_get_contents('C:\Users\aaron.r\AppData\Local\Temp\MYOBODBCError.txt', true);

// maybe implode array? send as one string with commas?

?>
