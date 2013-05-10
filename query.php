<?

$DisplayCardID = $_POST['input'];

$DatabaseHost 		= 'localhost';
$DatabaseName 		= 'echips_v2';
$DatabaseUser		= 'root';
$DatabasePass		= 'megacool';
$CounterStart 		= 0;
$CounterDisplay		= 0;

$Database = new PDO('mysql:host='.$DatabaseHost.';dbname='.$DatabaseName.'',$DatabaseUser,$DatabasePass) or die("Oh no, I can't connect to the database!");

$FirstQuery = $Database->query("SELECT customers.cardid, customers.lastname, customers.firstname, 
								jobdetails.jobnumber, jobitems.qtycharged, jobitems.quotedprice, jobdetails.title, jobitems.notes, jobitems.qtysupplied, jobitems.quotedprice
								FROM jobdetails, customers, jobitems
								WHERE jobdetails.customerid = customers.cardid
								AND jobdetails.jobnumber = jobitems.jobnumber
								AND jobdetails.datetimesheet IS NOT NULL AND jobdetails.invoicenumber IS NULL
								AND customers.cardid=$DisplayCardID");
							
							
foreach($FirstQuery as $row) {
	$CardID[$CounterStart]		= $row['cardid'];
	$JobNumber[$CounterStart] 	= $row['jobnumber'];
	$JobTitle[$CounterStart] 	= $row['title'];
	$JobNotes[$CounterStart]	= $row['notes'];
	$CounterStart++;
}

// $JobNumberArray = array_unique($JobNumber);

// foreach($JobNumberArray as $value) {
	// echo '<b>'. $JobNumber[$CounterDisplay] .'</b> - ';
	// echo $JobTitle[$CounterDisplay];
	// echo '<br>';
	// $CounterStart++;
// }

print_r(array_unique($JobNumber));
echo '<p>';
print_r(array_unique($JobTitle));
echo '<p>';
print_r(array_unique($JobNotes));

?>