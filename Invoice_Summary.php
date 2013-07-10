<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery.min.js"></script>
<script src="js/jquery.svg.js"></script>

</head>
<title>Efficient Chips</title>
<body>

<?

// TO-DO LIST
// ----------
// . Return invoice number.
// . Set invoice number against database and close job.

// . Grab PO Number - error check so that only ONE can be submitted at once.

$DatabaseHost 		= 'localhost';
$DatabaseName 		= 'echips_v2';
$DatabaseUser		= 'root';
$DatabasePass		= 'megacool';
$CounterStart 		= 0;
$CounterDisplay		= 0;

$Database = new PDO('mysql:host='.$DatabaseHost.';dbname='.$DatabaseName.'',$DatabaseUser,$DatabasePass) or die("Oh no, I can't connect to the database!");

$FirstQuery = $Database->query("SELECT DISTINCT(customers.lastname), customers.firstname, COUNT(DISTINCT jobitems.jobnumber) AS UnbilledJobs, 
								customers.cardid, ROUND(SUM(qtycharged * quotedprice), 2) as SubTotal
								FROM customers, jobdetails, jobitems
								WHERE jobdetails.customerid = customers.cardid
								AND jobdetails.jobnumber = jobitems.jobnumber
								AND jobdetails.datetimesheet IS NOT NULL AND jobdetails.invoicenumber IS NULL
								GROUP BY customers.lastname
								HAVING UnbilledJobs > 0");

foreach($FirstQuery as $row) {
	$CardID[$CounterStart] 				= $row['cardid'];
	$CustomerLastName[$CounterStart] 	= $row['lastname'];
	$CustomerFirstName[$CounterStart] 	= $row['firstname'];
	$UnbilledJobs[$CounterStart] 		= $row['UnbilledJobs'];
	$JobTotalValue[$CounterStart]		= $row['SubTotal'];
	$CounterStart++;
}

?>

<script>

// Highlight selected customer
$(document).ready(function() {

	$('tr#JobList').click(function() {
		$('tr').removeClass("HighlightJob");
		$(this).addClass("HighlightJob");
	});
	
	$('#ClientDetail').html('<img id="first-prompt" class="svg" src="img/ModernUI/book-empty.svg" /> <p id="first-prompt-text">Select a client to begin.</span>');
	
	var MYOBDeliveryStatus;
	var MYOBCardID;
	
});

$('body').on('click', 'svg#option-print', function() {
	$('svg#option-print path').css('fill', '#FFFFFF');
	$('svg#option-email path').css('fill', '#7F8285');
	MYOBDeliveryStatus = "P";
});

$('body').on('click', 'svg#option-email', function() {
	$('svg#option-email path').css('fill', '#FFFFFF');
	$('svg#option-print path').css('fill', '#7F8285');
	MYOBDeliveryStatus = "E";
});

// Display selected customer's invoices
function GetJobDetails(cardid) {

	$.ajax({
		url: "Invoice_Detail.php",
		type: "POST",
		data: {input : cardid},
		success: function(data) {
			$('#ClientDetail').html(data);
		}
	});

	$("#FooterIcons").css('visibility', 'visible');
	MYOBCardID = cardid;
	
	console.log(MYOBCardID);
	
};

function SubmitInvoice() {

var MYOBPONumber = [];
var MYOBQuantity = [];
var MYOBItemNumber = [];
var MYOBDescription = [];
var MYOBExTaxTotal = [];
var MYOBIncTaxTotal = [];
var n = 0;
	
	$('#ClientDetail').ready(function() {
		
		$('input[type=checkbox]:checked').each(function() {
		
			var CheckPONumber = $(this).closest('tr').find("input[id^=JobPONumber]").val();

			if (CheckPONumber !== "") {
				$(this).attr('checked', false);
			}
			
		});
		
		$('input[type=checkbox]:checked').each(function() {
		
			var JobNumber = $(this).val();
			MYOBDescription.push( $('span#JobTitle_' + JobNumber).html() );
			MYOBQuantity.push("1");
			MYOBItemNumber.push("misc");
			MYOBExTaxTotal.push("0");
			MYOBIncTaxTotal.push("0");

			$('td#JobNotes_' + JobNumber).each(function() {
				
				var WholeText = $(this).html();
				var Description = WholeText.split('\n');
				
				var n;
				for (n = 0; n < Description.length; n++) {
				
					if (Description[n].length > 255) {
						$('td#JobNotes_' + JobNumber).addClass("LengthExceeded");
					}

					var CheckJobCode = $(this).closest('tr').find("input[id^=JobCode]").val();
					
					if (n != 0) {
						if (CheckJobCode.substring(0, 6) == "onsite" || CheckJobCode.substring(0, 6) == "inshop") {
							MYOBQuantity.push("1");
							MYOBItemNumber.push("Service");
							MYOBExTaxTotal.push("0");
							MYOBIncTaxTotal.push("0");
						}
					} else {
						MYOBQuantity.push( $(this).closest('tr').find("input[id^=JobQty]").val() );
						MYOBItemNumber.push( $(this).closest('tr').find("input[id^=JobCode]").val() );
						MYOBExTaxTotal.push( $(this).closest('tr').find("input[id^=JobLineTotal]").val() );
						MYOBIncTaxTotal.push("0");
					}
					
					if (Description[n] != "") {
						MYOBDescription.push( Description[n].replace(/'/g,"''") );
					}	
				}
			
			});
	
			// Add blank-line between jobs
			MYOBQuantity.push("1");
			MYOBItemNumber.push("misc");
			MYOBDescription.push("-");
			MYOBExTaxTotal.push("0");
			MYOBIncTaxTotal.push("0");
		});
		
	});
	// Basic error checking
	if (typeof MYOBDeliveryStatus === 'undefined') {
		alert("You must select to either PRINT or EMAIL your invoice!");
		return;
	}
	
	// Submit array to be parsed for MYOB
	MYOBSubmit(MYOBPONumber, MYOBQuantity, MYOBItemNumber, MYOBDeliveryStatus, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID);
	
}

function MYOBSubmit(MYOBPONumber, MYOBQuantity, MYOBItemNumber, MYOBDeliveryStatus, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID) {

		$.ajax({

		url: "Submit_Invoice.php",
		type: "POST",
		data: {
			'PONumber' 			: MYOBPONumber,
			'Quantity' 			: MYOBQuantity,
			'ItemNumber' 		: MYOBItemNumber,
			'DeliveryStatus' 	: MYOBDeliveryStatus,
			'Description' 		: MYOBDescription,
			'ExTaxTotal' 		: MYOBExTaxTotal,
			'IncTaxTotal' 		: MYOBIncTaxTotal,
			'CardID' 			: MYOBCardID
		},
		success: function(data) {
			console.log(data);
		}

	});

}

</script>

<div class="ClientSummary">
<table class="ClientTable">

<?

// Display summary list of all clients with outstanding invoices
foreach($CardID as $value) {
	
	echo '
	<tr id="JobList" onclick="GetJobDetails('.$CardID[$CounterDisplay].');">
	<td class="CustomerName">'. $CustomerLastName[$CounterDisplay] .' '. $CustomerFirstName[$CounterDisplay] .'</td>
	<td class="UnbilledJobs"><b>'. $UnbilledJobs[$CounterDisplay] .'</b></td>
	<td class="JobTotalValue"> $'. $JobTotalValue[$CounterDisplay] .'</td>
	</tr>';
	$CounterDisplay++;
}

?>

</table>

</div>

<div id="ClientDetail"></div>

<div id="ClientFooter">

<div id="FooterIcons">
<img id="option-print" class="svg footer-options" src="img/ModernUI/printer.svg" />
<img id="option-email" class="svg footer-options" src="img/ModernUI/email.svg" />
<span onclick="SubmitInvoice();" id="SubmitButton">SUBMIT INVOICES</button>
</div>

</div>

</body>
</html>
