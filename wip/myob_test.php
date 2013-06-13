<?

//$CustomersNumber = "1234";
$ItemNumber = $_POST['ItemNumber'];				
//$DeliveryStatus = "P";
$Quantity = $_POST['Quantity'];							
$Description = $_POST['Description'];
//$ExTaxTotal = 95.00;
$IncTaxTotal = $_POST['IncTaxTotal'];						
//$CardID = "CUS000001";

echo $Quantity .'<br>';
echo $ItemNumber .'<br>';
echo $Description .'<br>';
echo $IncTaxTotal .'<br>';

?>