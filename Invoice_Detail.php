<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/jquery.editable.min.js"></script>
<script src="js/jquery.numeric.js"></script>
</head>
<title>Efficient Chips</title>
<body>

<?

// TO-DO LIST
// ----------
// . Ensure new rows are dynamically updated too.
// . Create area for PO Number.
// . Detect if it's a PART (ie: NOT onsite or inshop) and restrict editing that field to ONE LINE.

date_default_timezone_set('Australia/Perth');

$DisplayCardID = $_POST['input'];

$DatabaseHost 		= 'localhost';
$DatabaseName 		= 'echips_v2';
$DatabaseUser		= 'root';
$DatabasePass		= 'megacool';
$CounterStart 		= 0;
$CounterDisplay		= 0;

$Database = new PDO('mysql:host='.$DatabaseHost.';dbname='.$DatabaseName.'',$DatabaseUser,$DatabasePass) or die("Oh no, I can't connect to the database!");

$FirstQuery = $Database->query("SELECT customers.cardid, customers.lastname, customers.firstname, 
								jobdetails.jobnumber, jobitems.qtycharged, jobitems.quotedprice, jobitems.myobcode, jobdetails.title, jobdetails.appointmentdate,
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
	$JobDate[$CounterStart]		= $row['appointmentdate'];
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
	$('[class^="EditRow"]').fadeOut(1000);
	$('[id^="JobQty"]').numeric();
	$('[id^="JobUnitPrice"]').numeric();
	$('[id^="JobLineTotal"]').numeric();
	

});

// Edit specific job table
$('img#EditJobHeader').click(function() {

	var EditJobNumber = $(this).attr('class');
	var IsJobEditable = $(this).closest('#JobHeader').attr('class');

	$(this).closest('tr').toggleClass("IsEditable");
	$(this).closest('tr').animate({"backgroundColor":"rgb(245,100,100)"}, 600);
	
	$(".EditRow_" + EditJobNumber).css('visibility', 'visible');
	$(".EditRow_" + EditJobNumber).fadeIn(1000);

	$('td#JobNotes_' + EditJobNumber).editable({
		lineBreaks: false,
	});

	$('input#JobQty_' + EditJobNumber).prop("disabled", false);
	$('input#JobCode_' + EditJobNumber).prop("disabled", false);
	$('input#JobUnitPrice_' + EditJobNumber).prop("disabled", false);
	$('input#JobLineTotal_' + EditJobNumber).prop("disabled", false);
	
	if (IsJobEditable == "IsEditable") {
		$('td#JobNotes_' + EditJobNumber).editable('destroy');
		$(this).closest('tr').animate({"backgroundColor":"rgb(224,224,255)"}, 400);
		$('input#JobQty_' + EditJobNumber).prop("disabled", true);
		$('input#JobCode_' + EditJobNumber).prop("disabled", true);
		$('input#JobUnitPrice_' + EditJobNumber).prop("disabled", true);
		$('input#JobLineTotal_' + EditJobNumber).prop("disabled", true);
		
	$(".EditRow_" + EditJobNumber).fadeOut(1000);
	
	}

});

// Add new blank row to table
function AddRow(EditJobNumber) {

	$(".EditRow_" + EditJobNumber).css('display', 'table-cell');
	$(".EditRow_" + EditJobNumber).css('visibility', 'visible');


	var AddRowString = 
	'  <tr id="JobBorder"> '
	+' <td class="JobTech"> </td>'
	+' <td> <input id="JobQty_'+EditJobNumber+'" class="EditJobContents"> </td>'
	+' <td> <input id="JobCode_'+EditJobNumber+'" class="EditJobContents"> </td>'
	+' <td id="JobNotes_'+EditJobNumber+'"></td>'
	+' <td> <input id="JobUnitPrice_'+EditJobNumber+'" class="EditJobContents" value="0.00"> </td>'
	+' <td> <input id="JobLineTotal_'+EditJobNumber+'" class="EditJobContents" value="0.00"> </td>'
	+' <td class="EditRow_'+EditJobNumber+'"> <img src="img/DeleteRow.png" class="DeleteRow" onclick="DeleteRow()"> </td>'
	+' </tr>';
	
	$("#JobTable_" + EditJobNumber).last().append(AddRowString);
	
	$('td#JobNotes_' + EditJobNumber).editable({
		lineBreaks: false,
	});
	
	$('[id^="JobQty"]').numeric();
	$('[id^="JobUnitPrice"]').numeric();
	$('[id^="JobLineTotal"]').numeric();
	
};

// Prompt and delete specific row
function DeleteRow() {
	
	if (confirm("Delete row?")) {
	
		var EditJobNumber = $(event.target).parent().attr('class');
		EditJobNumber = EditJobNumber.replace(/\D/g, '');	// Strip all non-numerical characters from string
		var EditLineTotal = $(event.target).closest('tr').find("input[id^=JobLineTotal]").val();
		
		var EditJobTotal = $('span#EditTotal_' + EditJobNumber).html();
		console.log(EditJobTotal);

		EditJobTotal = parseInt(EditJobTotal) - parseInt(EditLineTotal);
		$('#EditTotal_' + EditJobNumber).html(EditJobTotal.toFixed(2));
		
		$(event.target).closest('tr').remove();
	} 

}


// Auto-calculate totals when fields are changed
$('.EditJobContents').keyup(function() {
	
	var FinalJobTotal = 0;

	var EditJobNumber = $(this).attr('id');
	EditJobNumber = EditJobNumber.replace(/\D/g,'');	// Strip all non-numerical characters from string
	
	var EditJobQty = $(this).closest('tr').find("input[id^=JobQty]").val();
	var EditUnitPrice = $(this).closest('tr').find("input[id^=JobUnitPrice]").val();
	var EditUnitPrice = EditUnitPrice.replace("$", "");
	var EditLineTotal = (EditJobQty * EditUnitPrice);
	
	$(this).closest('tr').find("input[id^=JobLineTotal]").val(EditLineTotal.toFixed(2));
	
	$('input#JobLineTotal_' + EditJobNumber).each(function() {
		FinalJobTotal = parseInt(FinalJobTotal) + parseInt($(this).val());
	});
	
	$('#EditTotal_' + EditJobNumber).html(FinalJobTotal.toFixed(2));
	
});

</script>

<?

foreach($JobNumber as $value) {


	if ($value != $JobNumber[$CounterDisplay - 1]) {
	
		$LineTotal = number_format($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay], 2);
		$JobTotal = number_format( (str_replace(",", "", $JobTotal) + str_replace(",", "", $LineTotal)), 2);
		
		if ($JobDate[$CounterDisplay] != NULL) {
			$DisplayJobDate = new DateTime($JobDate[$CounterDisplay]);
			$DisplayJobDate = " (" . $DisplayJobDate->format('M jS') . ")";
		} else {
			$DisplayJobDate = "";
		}
		
		// Initial header/table constructed
		echo '<table id="JobTable_'. $value .'">';
		
		echo '<tr id="JobHeader" class="">';
		echo '<td colspan=5> <input id="JobChecked_'. $value .'" type=checkbox checked value='.$value.'> 
		<span id="JobTitle_'. $value .'">Job #'.$JobNumber[$CounterDisplay] .' - '. $JobTitle[$CounterDisplay] . $DisplayJobDate .'</span> </td>';
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
		echo '<td> <input id="JobQty_'. $value .'" value='. $JobQty[$CounterDisplay] .' class="EditJobContents" disabled=true> </td>';
		echo '<td> <input id="JobCode_'. $value .'" value='. $JobCode[$CounterDisplay] .' class="EditJobContents" disabled=true> </td>';
		echo '<td id="JobNotes_'. $value .'">'. $JobNotes[$CounterDisplay] .'</td>';
		echo '<td> <input id="JobUnitPrice_'. $value .'" value='. number_format($JobPrice[$CounterDisplay], 2) .' class="EditJobContents" disabled=true> </td>';
		echo '<td> <input id="JobLineTotal_'. $value .'" value='. $LineTotal .' class="EditJobContents" disabled=true> </td>';
		echo '<td class="EditRow_'. $value .'"> <img src="img/DeleteRow.png" class="DeleteRow" onclick="DeleteRow()"> </td>';
		echo '</tr>';
		
		$CounterDisplay++;

	} 
	
	else {
		
		$LineTotal = number_format($JobQty[$CounterDisplay] * $JobPrice[$CounterDisplay], 2);
		$JobTotal = number_format( (str_replace(",", "", $JobTotal) + str_replace(",", "", $LineTotal)), 2);
	
		// Remaining job notes displayed; loops until end
		echo '<tr id="JobBorder">';
		echo '<td class="JobTech" bgcolor='.$TechColour[$CounterDisplay].'>'. $Tech[$CounterDisplay] .'</td>';
		echo '<td> <input id="JobQty_'. $value .'" value='. $JobQty[$CounterDisplay] .' class="EditJobContents" disabled=true> </td>';
		echo '<td> <input id="JobCode_'. $value .'" value='. $JobCode[$CounterDisplay] .' class="EditJobContents" disabled=true> </td>';
		echo '<td id="JobNotes_'. $value .'" class="EditJobContents"> '. $JobNotes[$CounterDisplay] .' </td>';
		echo '<td> <input id="JobUnitPrice_'. $value .'" value='. number_format($JobPrice[$CounterDisplay], 2) .' class="EditJobContents" disabled=true> </td>';
		echo '<td> <input id="JobLineTotal_'. $value .'" value='. $LineTotal .' class="EditJobContents" disabled=true> </td>';
		echo '<td class="EditRow_'. $value .'"> <img src="img/DeleteRow.png" class="DeleteRow" onclick="DeleteRow()"> </td>';
		echo '</tr>';
		
		$CounterDisplay++;
	}
	
	if ($value != $JobNumber[$CounterDisplay]) {
	
		echo '</table>';
		
		echo '	<div id="DisplayJobWrapper">
					<div id="DisplayJobLabel"> Total: </div> 
					<div id="DisplayJobTotal"> <span id="EditTotal_'. $value .'"> '. $JobTotal .' </span> </div>
				</div>';

		$JobTotal = 0;
		
	}

}

?>

</body>
</html>
