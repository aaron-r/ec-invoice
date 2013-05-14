<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="plugins/jquery-1.9.1.js"></script>
</head>

<body>

<?

// TO-DO LIST
// ----------
// . Generate grand-total of ALL invoices
// . Change selected customer to GREEN (jQuery)
// . Jump to top (ClientDetail) when new customer is selected
// . Add prompt to select client when blank page

// . ----------------------------------------------------------

// . Submit one job to MYOB - return invoice number
// . Submit multiple jobs to MYOB - return invoice number
// . Error-check: if item does not exist in MYOB
// . Error check: if line exceeds 255 characters
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

function GetJobDetails(cardid) {
	
	$.ajax({
		url: "query.php",
		type: "POST",
		data: {input : cardid},
		success: function(data) {
			$('#DisplayJobDetails').html(data);
		}
	});
	
	$('tr').click(function() {
		$("tr").removeClass("HighlightJob");
		$(this).addClass("HighlightJob");
	});

	
};

</script>

<div class="ClientSummary">
<table class="ClientTable" cellspacing="0" >
<tr>
<td style="width:350px"><b>Customer</b></td>
<td style="width:30px"></td>	<!-- Jobs Unbilled -->
<td><b>Value</b></td>
</tr>

<?

foreach($CardID as $value) {
	
	echo '
	<tr>
	<td><a href="" onclick="GetJobDetails('.$CardID[$CounterDisplay].'); return false;">'. $CustomerLastName[$CounterDisplay] .' '. $CustomerFirstName[$CounterDisplay] .'</a></td>
	<td><b>'. $UnbilledJobs[$CounterDisplay] .'</b></td>
	<td> $'. $JobTotalValue[$CounterDisplay] .'</td>
	</tr>';
	$CounterDisplay++;
}

?>

</table>
</div>

<div class="ClientDetail">

<span id="DisplayJobDetails"></span>

</div>

</body>
</html>