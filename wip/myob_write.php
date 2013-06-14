<?php

try {
//$db = new PDO("odbc:Driver={MYOAU1001}; Type=MYOB; Database=SystemTest2; Access_Type=READ_WRITE; SQL_Attr_Autocommit=1; Suppress_Warnings=FALSE; ");

$db = new PDO("odbc:Driver={MYOAU1001}; Database=MYOBDevelopment; Access_Type=READ_WRITE; ");

$db->beginTransaction();

// $qry = $db->prepare('INSERT INTO Import_Items (ItemNumber, ItemName, Buy, Sell, Inventory, AssetAccount, IncomeAccount, ExpenseAccount) VALUES (?,?,?,?,?,?,?,?)');
// $qry->execute(array('z002', 'CuntFuck2', 'Y', 'Y', 'Y', '10000', '40000', '60000'));

$qry = $db->prepare( 'INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ');
$qry->execute(array('001', 'z001', 'P', '1', 'Butts', '95.00', '95.00', 'CUS000001'));

$db->commit();

} catch (PDOException $e) {
   echo 'Connection failed: ' . $e->getMessage();
}

echo file_get_contents('C:\Users\aaron.r\AppData\Local\Temp\MYOBODBCError.txt', true);

//Successful query
// $sql = "SELECT * FROM Employees";
   // foreach ($db->query($sql) as $row)
       // {
       // print $row['Name'] .' - '. $row['CardIdentification'] . '<br />';
       // }

?>