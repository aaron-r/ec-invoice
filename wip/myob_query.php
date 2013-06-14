<?php

//$db = new PDO("odbc:Driver={MYOAU1001}; Type=MYOB; Database=SystemTest2; Access_Type=READ_WRITE; SQL_Attr_Autocommit=1; Suppress_Warnings=FALSE; ");

$db = new PDO("odbc:Driver={MYOAU1001}; Database=MYOBDevelopment; Access_Type=READ_WRITE; ");

//Successful query
$sql = "SELECT * FROM Employees";
   foreach ($db->query($sql) as $row)
       {
       print $row['Name'] .' - '. $row['CardIdentification'] . '<br />';
       }

?>