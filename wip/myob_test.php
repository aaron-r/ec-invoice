<?

//$CustomersNumber = "1234";
$ItemNumber = $_POST['ItemNumber'];				
//$DeliveryStatus = "P";
$Quantity = $_POST['Quantity'];							
$Description = $_POST['Description'];
$ExTaxTotal = $_POST['ExTaxTotal'];
$IncTaxTotal = $_POST['IncTaxTotal'];						
//$CardID = "CUS000001";

echo '['. $Quantity . '] - ' . $ItemNumber . ' - <i> ' . $Description . ' </i> - (' . $ExTaxTotal . ') (' . $IncTaxTotal . ') <br>';
echo '<p>';

?>