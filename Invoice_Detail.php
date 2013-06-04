<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="plugins/jquery.min.js"></script>
<script src="plugins/jquery-ui.min.js"></script>
<script src="plugins/jquery.editable.min.js"></script>
</head>

<body>

<?

error_reporting(0);

// TO-DO LIST
// ----------	
// . Auto-update totals for: (ea. 'Line Total' = Qty * Unit Price) and (Invoice Total = sum of all 'Line Total')				
// . Display date along-side each job number and job title.
// . Deleting jobs: make sure you're unable to delete LAST <tr> - basically put in blank <tr> instead
// . Prompt before a row is deleted.
// . [CSS] - Completely hide (+/-) signs when page loads. (really needed?)

// . -------------------------MYOB---------------------------
// . Submit one job to MYOB - return invoice number
// . Submit multiple jobs to MYOB - return invoice number
// . Error-check: if item does not exist in MYOB
// . Error check: if line exceeds 255 characters
// . Auto e-mail from MYOB
// . Auto print from MYOB

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
	// CSS hide visible for EditRow here?
	$('[class^="EditRow"]').fadeOut(1000);
});

// Edit specific job table
$('img#EditJobHeader').click(function() {

	var EditJobNumber = $(this).attr('class');
	var IsJobEditable = $(this).closest('#JobHeader').attr('class');

	$(this).closest('tr').toggleClass("IsEditable");
	$(this).closest('tr').animate({"backgroundColor":"rgb(245,100,100)"}, 600);
	
	$(".EditRow_" + EditJobNumber).css('visibility', 'visible');
	$(".EditRow_" + EditJobNumber).fadeIn(1000);

	$('#JobTable_' + EditJobNumber + ' td.EditJob').editable({
		lineBreaks: true,
	});

	
	if (IsJobEditable == "IsEditable") {
		$('#JobTable_' + EditJobNumber + ' td.EditJob').editable('destroy');
		$(this).closest('tr').animate({"backgroundColor":"rgb(224,224,255)"}, 400);
		
	$(".EditRow_" + EditJobNumber).fadeOut(1000);
	
	} 

});

// Add new blank row to table
function AddRow(EditJobNumber) {

	$(".EditRow_" + EditJobNumber).css('visibility', 'visible');

	var AddRowString = '<tr id="JobBorder"> <td class="JobTech"> </td> <td id="JobQty" class="EditJob"> </td> <td id="JobCode" class="EditJob"> </td> <td id="JobNotes" class="EditJob">&nbsp;</td> <td id="JobUnitPrice" class="EditJob"> </td> <td id="JobLinePrice" class="EditJob"> </td> <td class="EditRow_'+EditJobNumber+'"> <img src="img/DeleteRow.png" class="DeleteRow"> </td>';
	$("#JobTable_" + EditJobNumber).last().append(AddRowString);
	
	$('#JobTable_' + EditJobNumber + ' td.EditJob').editable({
		lineBreaks: true,
	});
	
};

// Prompt and delete specific row
$('.DeleteRow').live('click', function() {
	
	var ConfirmDelete = confirm("Delete row?");
	
	if (ConfirmDelete==true) {
		$(this).closest('tr').remove();
	} else {
	}
	
	// If last row, just call AddRow for another?
});

// Auto-calculate totals when fields are changed
$('.EditJob').keyup(function() {
	
	// Detect for letters within Qty, Unit Price and Line Total
	// ... and ensure they CANT be inputted!

	var EditJobQty = $(this).closest('tr').children('[id^="JobQty"]').text();
	var EditUnitPrice = $(this).closest('tr').children('[id^="JobUnitPrice"]').text();
	var EditLineTotal = $(this).closest('tr').children('[id^="JobLinePrice"]').text();
		
	var EditUnitPrice = EditUnitPrice.replace("$", "");
	var EditLineTotal = (EditJobQty * EditUnitPrice);
	alert("Line Total is: " + EditJobQty + " [*] " + EditUnitPrice );

});

</script>

<?

foreach($JobNumber as $value) {


	if ($value != $JobNumber[$CounterDisplay - 1]) {
	
		$LineTotal = number_format($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay], 2);
		$JobTotal = number_format( (str_replace(",", "", $JobTotal) + str_replace(",", "", $LineTotal)), 2);
		
		// Initial header/table constructed
		echo '<table id="JobTable_'. $value .'">';
		
		echo '<tr id="JobHeader" class="">';
		echo '<td colspan=5> <input type=checkbox checked value='.$value.'> <b>&nbsp;Job #'.$JobNumber[$CounterDisplay] .' - '. $JobTitle[$CounterDisplay]. '</b> </td>';
		echo '<td> <img id="EditJobHeader" src="img/EditIcon.png" class='.$value.'> </td>';
		echo '</tr>';
		
		echo '<tr id="JobBorder">
			  <td id="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>&nbsp;</td>
			  <td id="JobQty">Qty</td>
			  <td id="JobCode">Code</td>
			  <td>Notes</td>
			  <td>Unit Price</td>
			  <td>Line Total</td>
			  <td class="EditRow_'. $value .'"> <img src="img/AddRow.png" class="AddRow" onclick="AddRow('. $value .')"> </td>
			  </tr>';

		// First job notes displayed
		echo '<tr id="JobBorder">';
		echo '<td class="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td id="JobQty_'. $value .'" class="EditJob">'. $JobQty[$CounterDisplay] .'</td>';
		echo '<td id="JobCode_'. $value .'" class="EditJob">'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td id="JobNotes" class="EditJob">'. htmlentities($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td id="JobUnitPrice_'. $value .'" class="EditJob"> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td id="JobLinePrice_'. $value .'" class="EditJob"> $'. $LineTotal .'</td>';
		echo '<td class="EditRow_'. $value .'"> <img src="img/DeleteRow.png" class="DeleteRow"> </td>';
		echo '</tr>';
		
		$CounterDisplay++;

	} 
	
	else {
		
		$LineTotal = number_format($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay], 2);
		$JobTotal = number_format( (str_replace(",", "", $JobTotal) + str_replace(",", "", $LineTotal)), 2);
	
		// Remaining job notes displayed; looped until end
		echo '<tr id="JobBorder">';
		echo '<td class="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td id="JobQty_'. $value .'" class="EditJob">'. $JobQty[$CounterDisplay] .'</td>';
		echo '<td id="JobCode_'. $value .'" class="EditJob">'. $JobCode[$CounterDisplay] .'</td>';
		echo '<td id="JobNotes_'. $value .'" class="EditJob">'. htmlentities($JobNotes[$CounterDisplay]) .'</td>';
		echo '<td id="JobUnitPrice_'. $value .'" class="EditJob"> $'. number_format($JobPrice[$CounterDisplay], 2) .'</td>';
		echo '<td id="JobLinePrice_'. $value .'" class="EditJob"> $'. $LineTotal .'</td>';
		echo '<td class="EditRow_'. $value .'"> <img src="img/DeleteRow.png" class="DeleteRow"> </td>';
		echo '</tr>';
		
		$CounterDisplay++;
	}
	
	if ($value != $JobNumber[$CounterDisplay]) {
	
		echo '</table>';
		
		echo '	<div id="DisplayJobWrapper">
					<div id="DisplayJobLabel"> Total: </div> 
					<div id="DisplayJobTotal"> <b>$'. $JobTotal .' </b> </div>
				</div>';
		
		$JobTotal = 0;
	}

}

?>

</body>
</html>