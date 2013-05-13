<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>

<?

// TO-DO LIST
// ----------
// . Make sure 'Unit Price' and 'Line Total' stay same width on large lines (See: Alliance Contracting)
// . Create EDITable fields
// . CSS and finalize display output, text align

$DisplayCardID = $_POST['input'];

$DatabaseHost 		= 'localhost';
$DatabaseName 		= 'echips_v2';
$DatabaseUser		= 'root';
$DatabasePass		= 'megacool';
$CounterStart 		= 0;
$CounterDisplay		= 0;

$Database = new PDO('mysql:host='.$DatabaseHost.';dbname='.$DatabaseName.'',$DatabaseUser,$DatabasePass) or die("Oh no, I can't connect to the database!");

$FirstQuery = $Database->query("SELECT customers.cardid, customers.lastname, customers.firstname, 
								jobdetails.jobnumber, jobitems.qtycharged, jobitems.quotedprice, jobitems.myobcode, jobdetails.title, 
								jobitems.qtysupplied, jobitems.quotedprice, jobitems.notes, jobitems.staffid, staff.shortname, staff.staffcolor
								FROM jobdetails, customers, jobitems, staff
								WHERE jobdetails.customerid = customers.cardid
								AND jobdetails.jobnumber = jobitems.jobnumber
								AND jobitems.staffid = staff.id
								AND jobdetails.datetimesheet IS NOT NULL AND jobdetails.invoicenumber IS NULL
								AND customers.cardid=$DisplayCardID");
							
							
foreach($FirstQuery as $row) {
	$CardID[$CounterStart]		= $row['cardid'];
	$JobNumber[$CounterStart] 	= $row['jobnumber'];
	$JobTitle[$CounterStart] 	= $row['title'];
	$JobNotes[$CounterStart]	= $row['notes'];
	$JobQty[$CounterStart]		= $row['qtysupplied'];
	$JobCode[$CounterStart]		= $row['myobcode'];
	$JobPrice[$CounterStart]	= $row['quotedprice'];
	$Tech[$CounterStart]		= $row['shortname'];
	$TechColour[$CounterStart] 	= $row['staffcolor'];
	$CounterStart++;
}
?>

<table class="JobTable" border=1>

<?

foreach($JobNumber as $value) {

	if ($value != $JobNumber[$CounterDisplay - 1]) {
	
		$LineTotal = number_format(($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay]), 2);
		$JobTotal = number_format(($JobTotal + $LineTotal), 2);
		
		echo '<tr bgcolor=E0E0FF>';
		echo '<td> <input type=checkbox checked value='.$value.'> </td>';
		echo '<td colspan=5> <b>&nbsp;Job #'.$JobNumber[$CounterDisplay] .' - '. $JobTitle[$CounterDisplay]. '</b> </td>';
		echo '</tr>';
		
		// TO-DO: Make sure 'Unit Price' and 'Line Total' stay same width on large lines (See: Alliance Contracting)
		echo '<tr>
			  <td>&nbsp;</td>
			  <td>Qty</td>
			  <td>Code</td>
			  <td>Notes</td>
			  <td>Unit Price</td>
			  <td>Line Total</td>
			  </tr>';
		
		echo '<tr>';
		echo '<td bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td>'. $JobQty[$CounterDisplay] .'</td>';
		echo '<td>'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td>'. nl2br($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td> $'. $LineTotal .'</td>';
		echo '</tr>';
		
		$CounterDisplay++;

	} 
	else {
		
		$LineTotal = number_format(($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay]), 2);
		$JobTotal = number_format(($JobTotal + $LineTotal), 2);
	
		echo '<tr>';
		echo '<td bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td>'. $JobQty[$CounterDisplay] .'</td>';
		echo '<td>'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td>'. nl2br($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td> $'. $LineTotal .'</td>';
		echo '</tr>';
		
		$CounterDisplay++;
	}
	
	if ($value != $JobNumber[$CounterDisplay]) {
		
		echo '<tr>
			  <td colspan=4>&nbsp;</td>
			  <td bgcolor="#FFEBCD">Total: </td>';
		echo '<td class='.$value.'_Total bgcolor="#FFEBCD"> $'. $JobTotal .'</td>';
		echo '</tr>';
		
		echo '<tr>
		<td colspan=5">&nbsp;</td>
		</tr>';
		
		$JobTotal = 0;
	}

}

?>

</table>
	
</body>
</html>