<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="plugins/jquery.min.js"></script>
<script src="plugins/jquery-ui.js"></script>
<script src="plugins/jquery.editable.min.js"></script>
</head>

<body>

<?

error_reporting(0);

// TO-DO LIST
// ----------
// . Output Grand Total for customer
// . Finalise CSS design (rounded borders on top of tables)

// . Delete specific line from table - prompt
// . Add specific line to table

// . Auto-update totals for Qty, Unit Price, Line Total etc...

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


$(document).ready(function() {
	$('#ClientDetail').animate({ scrollTop: 0 }, 'medium');		// Auto-scroll to top of page
	$("td[class^='EditRow']").fadeOut(1000);
});

// Edit specific job table
$('img#EditJobHeader').click(function() {
	
	var EditJobNumber = $(this).attr('class');
	var IsJobEditable = $(this).closest('#JobHeader').attr('class');

	$(this).closest('tr').toggleClass("IsEditable");
	$(this).closest('tr').animate({"backgroundColor":"rgb(245,100,100)"}, 600);
	$('.EditRow_' + EditJobNumber).fadeIn(1000);
	
	$('#JobTable_' + EditJobNumber + ' td.EditJob').editable({
		lineBreaks: false,
	});
	
	if (IsJobEditable == "IsEditable") {
		$('#JobTable_' + EditJobNumber + ' td.EditJob').editable('destroy');
		$(this).closest('tr').animate({"backgroundColor":"rgb(224,224,255)"}, 400);
		$('.EditRow_' + EditJobNumber).fadeOut(1000);
	} 

});

// Add new line - ***example***
/* $('img#EditJob').click(function() {
	var EditJobNumber = $(this).attr('class');
	
	$("#JobTable_" + EditJobNumber).last().append('<tr id="JobBorder"><td class="JobTech"></td><td id="JobQty"></td><td id="JobCode"></td><td id="JobNotes">New line goes here!!!</td><td id="JobUnitPrice"></td><td id="JobLinePrice"></td></tr>');
	
}); */

</script>

<?

foreach($JobNumber as $value) {


	if ($value != $JobNumber[$CounterDisplay - 1]) {
	
		$LineTotal = number_format(($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay]), 2);
		$JobTotal = number_format(($JobTotal + $LineTotal), 2);
		
		echo '<table id="JobTable_'. $value .'">';
		
		echo '<tr id="JobHeader" class="">';
		echo '<td colspan=5> <input type=checkbox checked value='.$value.'> <b>&nbsp;Job #'.$JobNumber[$CounterDisplay] .' - '. $JobTitle[$CounterDisplay]. '</b> </td>';
		echo '<td> <img id="EditJobHeader" src="img/EditIcon.png" class='.$value.'> </td>';
		echo '</tr>';
		
		echo '<tr id="JobBorder">
			  <td id="JobTech">&nbsp;</td>
			  <td id="JobQty">Qty</td>
			  <td id="JobCode">Code</td>
			  <td>Notes</td>
			  <td>Unit Price</td>
			  <td>Line Total</td>
			  <td class="EditRow_'. $value .'"> <img src="img/AddRow.png"> </td>
			  </tr>';

		echo '<tr id="JobBorder">';
		echo '<td class="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td id="JobQty_'. $value .'" class="EditJob"> '. $JobQty[$CounterDisplay] .'</td>';
		echo '<td id="JobCode_'. $value .'" class="EditJob">'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td id="JobNotes" class="EditJob">'. nl2br($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td id="JobUnitPrice_'. $value .'" class="EditJob"> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td id="JobLinePrice_'. $value .'" class="EditJob"> $'. $LineTotal .'</td>';
		echo '<td class="EditRow_'. $value .'"> <img src="img/DeleteRow.png"> </td>';
		echo '</tr>';
		
		$CounterDisplay++;

	} 
	
	else {
		
		$LineTotal = number_format(($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay]), 2);
		$JobTotal = number_format(($JobTotal + $LineTotal), 2);
	
		//echo '<tbody>';
		echo '<tr id="JobBorder">';
		echo '<td class="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td id="JobQty_'. $value .'" class="EditJob">'. $JobQty[$CounterDisplay] .'</td>';
		echo '<td id="JobCode_'. $value .'" class="EditJob">'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td id="JobNotes_'. $value .'" class="EditJob">'. nl2br($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td id="JobUnitPrice'. $value .'" class="EditJob"> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td id="JobLinePrice_'. $value .'" class="EditJob"> $'. $LineTotal .'</td>';
		echo '<td class="EditRow_'. $value .'"> <img src="img/DeleteRow.png"> </td>';
		echo '</tr>';
		
		$CounterDisplay++;
	}
	
	if ($value != $JobNumber[$CounterDisplay]) {
	
		echo '</table>';
		echo '<div id="DisplayJobTotal">Total: <b>$'. $JobTotal .' </b> </div>';
		
		$JobTotal = 0;
	}

}

?>

</body>
</html>