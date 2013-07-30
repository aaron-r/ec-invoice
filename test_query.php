<?

try {
	$db = new PDO("odbc:Driver={MYOAU1001}; Database=MYOBDevelopment; ");
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	echo "Connected to MYOB successfully.\n";
} catch (Exception $e) {
	die("Unable to connect to MYOB: " . $e->getMessage());
}

$sql = "SELECT TOP 3 * FROM Sales ORDER BY InvoiceNumber DESC";

$result = $db->query($sql);

foreach($result as $row) {
	echo $row['InvoiceNumber'];
	printf("\n");
}

?>