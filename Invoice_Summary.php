<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery.min.js"></script>
</head>

<body>

<?

// TO-DO LIST
// ----------
// . Add prompt to select client when first opened. Quick static tutorial.
// . Left Footer: [Customer Name - X invoices worth Z amount] > [E-mail | Print] / Submit		- Live status bar. Fade in/out for new customer selected.
// . Centre Footer: This client doesn't have an e-mail address. Prompt to add one via ODBC.
// . Combine above two in to one? Does it really need dynamic price update???

// . -------------------------MYOB---------------------------
// . Submit one job to MYOB - return invoice number
// . Submit multiple jobs to MYOB - return invoice number
// . Auto e-mail from MYOB
// . Auto print from MYOB

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
	
	$('#ClientDetail').ready(function() {
		
		$('input[type=checkbox]:checked').each(function() {
		
			var JobNumber = $(this).val();
			var MYOBJobTitle = $('span#JobTitle_' + JobNumber).html();
			
			// Submit Initial Job Number & Title
			$.ajax({
					url: "wip/myob_test.php",
					type: "POST",
					data: {
					'Quantity' : '1',
					'ItemNumber' : 'title',
					'Description' : '<b>' + MYOBJobTitle + '</b>',
					'IncTaxTotal' : ''
					},
					success: function(data) {
					$('#ClientFooter').append(data);
				}
				});
			
			$('td#JobNotes_' + JobNumber).each(function() {

				var MYOBQuantity 	= $('input#JobQty_' + JobNumber).val();
				var MYOBItemNumber 	= $('input#JobCode_' + JobNumber).val();
				var MYOBIncTaxTotal = $('input#JobLineTotal_' + JobNumber).val();
				
				var WholeText = $(this).html();
				var MYOBDescription = WholeText.split('\n');
				
				var n;
				for (n = 0; n < MYOBDescription.length; n++) {
				
					if (MYOBDescription[n].length > 255) {
						$('td#JobNotes_' + JobNumber).addClass("LengthExceeded");
					}
					
				var CheckJobCode = $('input#JobCode_' + JobNumber).val();
					
				// if STARTS WITH onsite or inshop *AND* if (n != 0) do:
				if (n != 0) {
					MYOBQuantity	= "1";
					MYOBItemNumber	= "Service";
					MYOBIncTaxTotal = "";
				} else {
					var MYOBQuantity 	= $('input#JobQty_' + JobNumber).val();
					var MYOBItemNumber 	= $('input#JobCode_' + JobNumber).val();
					var MYOBIncTaxTotal = $('input#JobLineTotal_' + JobNumber).val();
				}
				
				if (MYOBDescription[n] != "") {

					// Loop through all Job Notes
					$.ajax({
						url: "wip/myob_test.php",
						type: "POST",
						data: {
						'Quantity' : MYOBQuantity,
						'ItemNumber' : MYOBItemNumber,
						'Description' : MYOBDescription[n],
						'ExTaxTotal' : MYOBIncTaxTotal,
						'IncTaxTotal' : n
						},
						success: function(data) {
						$('#ClientFooter').append(data);
					}
					});
				
				 }
				// Get final total here?
				
				}
				
			});
			
		});
		
		// 1. Get Job Title *
		// 2. Get Qty, Code, Notes (each line), Line Total *
		// 3. If same code and new line, change code to 'Service'
		// 4. Get Final Total
		
		// 1a. Initial Stage 						<-----
		// 2a. Loop through rest of stages.				 '
		// Has to repeat this for EACH table/checkbox ----
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
<b>AJAX results go here:</b><br>
</div>

</body>
</html>