<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery.min.js"></script>
</head>
<title>Efficient Chips</title>
<body>

<?

// TO-DO LIST
// ----------
// . Submit button is invisible until a client is selected

// . -------------------------MYOB---------------------------
// . Submit one job to MYOB - return invoice number
// . Submit multiple jobs to MYOB - return invoice number

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
	
	$('#ClientDetail').html('<img src="http://erroraccessdenied.com/files/images/iamnotgoodwithcomputer.jpeg" alt="Placeholder for instructions">');
	
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

};

function SubmitInvoice() {

var MYOBQuantity = [];
var MYOBItemNumber = [];
var MYOBDescription = [];
var MYOBExTaxTotal = [];
var MYOBIncTaxTotal = [];
	
	$('#ClientDetail').ready(function() {
		
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
						MYOBExTaxTotal.push("0");
						MYOBIncTaxTotal.push( $(this).closest('tr').find("input[id^=JobLineTotal]").val() );
					}
					
					if (Description[n] != "") {
						MYOBDescription.push(Description[n]);
					}	
				}
				
			});

			// Add blank-line between jobs
			MYOBQuantity.push("1");
			MYOBItemNumber.push("misc");
			MYOBDescription.push("-----");
			MYOBExTaxTotal.push("0");
			MYOBIncTaxTotal.push("0");
			
			console.log(MYOBQuantity);
			console.log(MYOBItemNumber);
			console.log(MYOBDescription);
		});
		
	});
	
	// Submit array to be parsed for MYOB
	$.ajax({
	
		url: "Submit_Invoice.php",
		type: "POST",
		data: {
		'PONumber' : '123456',
		'Quantity' : MYOBQuantity,
		'ItemNumber' : MYOBItemNumber,
		'DeliveryStatus' : 'P',			// 'P' (print) or 'E' (e-mail)
		'Description' : MYOBDescription,
		'ExTaxTotal' : MYOBExTaxTotal,
		'IncTaxTotal' : MYOBIncTaxTotal,
		'CardID' : 'CUS000001'
		},
		success: function(data) {
		$('#ClientFooter').html(data);
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

<h2 onclick="SubmitInvoice();"><u>Submit Invoices!</u></h2>

<div id="ClientFooter">
<b>AJAX results go here...</b><br>
</div>

</body>
</html>
