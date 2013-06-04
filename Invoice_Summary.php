<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="plugins/jquery.min.js"></script>
</head>

<body>

<?

error_reporting(0);

// TO-DO LIST
// ----------
// . Add prompt to select client when first opened. Quick static tutorial.
// . Footer: [Customer Name - X invoices worth Z amount] > [E-mail | Print] / Submit		- Live status bar. Fade in/out for new customer selected.

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
	
	$('#DisplayJobDetails').html('<img src="http://erroraccessdenied.com/files/images/iamnotgoodwithcomputer.jpeg" alt="Placeholder for instructions">');
	
});

// Display selected customer's invoices
function GetJobDetails(cardid) {

	$.ajax({
		url: "Invoice_Detail.php",
		type: "POST",
		data: {input : cardid},
		success: function(data) {
			$('#DisplayJobDetails').html(data);
		}
	});

};

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

<div id="ClientDetail">
<span id="DisplayJobDetails"></span>
</div>

<div id="ClientFooter">

</div>

</body>
</html>