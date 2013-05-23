<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="plugins/jquery-1.9.1.js"></script>
<script src="plugins/jquery.editable.min.js"></script>
</head>

<body>

<?

error_reporting(0);

// TO-DO LIST
// ----------
// . Output Grand Total for customer
// . Create a toggle 'edit' button (change bg-colour to red and have editable text)
// . Ability to 'add' or 'delete' a new row for a job
// . Auto-update totals for Qty, Unit Price, Line Total etc...

// . Finalise CSS design (rounded borders?)

// JTable or SlickGrid - look in to!

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



<script>

// Auto-scroll to top of page
$(document).ready(function() {
	$('#ClientDetail').animate({ scrollTop: 0 }, 'medium');
});

// Edit specific job
$('img#EditJob').click(function() {
	
	var EditJobNumber = $(this).attr('class');

	$(this).closest('tr').addClass("EditJob");
	
	$("#JobNotes_" + EditJobNumber).editable({
		lineBreaks: false,
	});
	
});

</script>

<?

foreach($JobNumber as $value) {

echo '<table id="JobTable_'. $value .'" cellspacing="0" border="0">';

	if ($value != $JobNumber[$CounterDisplay - 1]) {
	
		$LineTotal = number_format(($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay]), 2);
		$JobTotal = number_format(($JobTotal + $LineTotal), 2);
		
		echo '<tr bgcolor=E0E0FF>';
		echo '<td colspan=5> <input type=checkbox checked value='.$value.'> <b>&nbsp;Job #'.$JobNumber[$CounterDisplay] .' - '. $JobTitle[$CounterDisplay]. '</b> </td>';
		echo '<td> <img id="EditJob" src="img/EditIcon.png" class='.$value.' style="float:right; padding-right: 5px" > </td>';
		echo '</tr>';
		
		echo '<tr id="JobBorder">
			  <td id="JobTech">&nbsp;</td>
			  <td id="JobQty">Qty</td>
			  <td id="JobCode">Code</td>
			  <td>Notes</td>
			  <td>Unit Price</td>
			  <td>Line Total</td>
			  </tr>';
		
		echo '<tr id="JobBorder">';
		echo '<td id="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td id="JobQty_'. $value .'" class="Edit"> '. $JobQty[$CounterDisplay] .'</td>';
		echo '<td id="JobCode_'. $value .'" class="Edit">'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td id="JobNotes_'. $value .'" class="Edit">'. nl2br($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td id="JobUnitPrice'. $value .'" class="Edit"> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td id="JobLinePrice_'. $value .'" class="Edit"> $'. $LineTotal .'</td>';
		echo '</tr>';
		
		$CounterDisplay++;

	} 
	else {
		
		$LineTotal = number_format(($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay]), 2);
		$JobTotal = number_format(($JobTotal + $LineTotal), 2);
	
		echo '<tr id="JobBorder">';
		echo '<td id="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td id="JobQty_'. $value .'" class="Edit">'. $JobQty[$CounterDisplay] .'</td>';
		echo '<td id="JobCode_'. $value .'" class="Edit">'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td id="JobNotes_'. $value .'" class="Edit">'. nl2br($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td id="JobUnitPrice'. $value .'" class="Edit"> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td id="JobLinePrice_'. $value .'" class="Edit"> $'. $LineTotal .'</td>';
		echo '</tr>';
		
		$CounterDisplay++;
	}
	
	if ($value != $JobNumber[$CounterDisplay]) {
		
		echo '<tr>
			  <td colspan=4>&nbsp;</td>
			  <td bgcolor="#FFEBCD">Total: </td>';
		echo '<td id='.$value.'_Total bgcolor="#FFEBCD"> <b>$'. $JobTotal .' </b></td>';
		echo '</tr>';
		
		echo '<tr>
		<td colspan=2">&nbsp;</td>
		</tr>';
		
		$JobTotal = 0;
	}
	
echo '</table>';

}

?>

</body>
</html>