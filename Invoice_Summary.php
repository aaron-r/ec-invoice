<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery.min.js"></script>
</head>

<body>

<?

error_reporting(0);

// TO-DO LIST
// ----------
// . Add prompt to select client when first opened. Quick static tutorial.
// . Left Footer: [Customer Name - X invoices worth Z amount] > [E-mail | Print] / Submit		- Live status bar. Fade in/out for new customer selected.
// . Centre Footer: This client doesn't have an e-mail address. Prompt to add one via ODBC.

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
		//alert('foobar');
		//var tmp2 = $('td#JobNotes_21877').html();
		//alert(tmp2);
		
		$('input[type=checkbox]:checked').each(function() {
			var JobNumber = $(this).val();
			
			$('td#JobNotes_' + JobNumber).each(function() {
				// split each line
				// check each line for character length
				
				var text = $(this).html();
				var eachLine = text.split('<br>');
				
				//alert( eachLine[1].text() );
				alert(eachLine[0]);
				alert('Lines found: '+ eachLine.length);
			});
			
		});
		
		// for each checkbox that IS checked - serialize?
		// get job notes, check for 255 characters and newline
		// also get qty,code,unit price,line total
		// finally get total of current job
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
<h2 onclick="SubmitInvoice();">Submit Invoices!</h2>
</div>

</body>
</html>