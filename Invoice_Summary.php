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

// . Display error if any totals EQUAL $0 - "Do you want to proceed?"
// . Show error message if invoice isn't submitted properly
// . Show success message if invoice IS submitted OK
// . Animate the above messages

$DatabaseHost 		= 'localhost';
$DatabaseName 		= 'echips_v2';
$DatabaseUser		= 'root';
$DatabasePass		= 'megacool';
$CounterStart 		= 0;
$CounterDisplay		= 0;

$Database = new PDO('mysql:host='.$DatabaseHost.';dbname='.$DatabaseName.'',$DatabaseUser,$DatabasePass) or die("Oh no, I can't connect to V2!");

$FirstQuery = $Database->query("SELECT DISTINCT(customers.lastname), customers.firstname, COUNT(DISTINCT jobitems.jobnumber) AS UnbilledJobs, 
								customers.CardID, ROUND(SUM(qtycharged * quotedprice), 2) as SubTotal
								FROM customers, jobdetails, jobitems
								WHERE jobdetails.customerid = customers.CardID
								AND jobdetails.jobnumber = jobitems.jobnumber
								AND jobdetails.datetimesheet IS NOT NULL AND jobdetails.invoicenumber IS NULL
								GROUP BY customers.lastname
								HAVING UnbilledJobs > 0");

foreach($FirstQuery as $row) {
	$CardID[$CounterStart] 				= $row['CardID'];
	$CustomerLastName[$CounterStart] 	= $row['lastname'];
	$CustomerFirstName[$CounterStart] 	= $row['firstname'];
	$UnbilledJobs[$CounterStart] 		= $row['UnbilledJobs'];
	$JobTotalValue[$CounterStart]		= $row['SubTotal'];
	$CounterStart++;
}

?>

<script>

var MYOBDeliveryStatus;
var MYOBCardID;
var SQLString;

// Highlight selected customer
$(document).ready(function() {

	$('tr#JobList').click(function() {
		$('tr').removeClass("HighlightJob");
		$(this).addClass("HighlightJob");
	});
	
	$('#ClientDetail').html('<img id="first-prompt" class="svg" src="img/ModernUI/book-empty.svg" /> <p id="first-prompt-text">Select a client to begin.</span>');
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
function GetJobDetails(CardID) {

	$.ajax({
		url: "Invoice_Detail.php",
		type: "POST",
		data: {input : CardID},
		success: function(data) {
			$('#ClientDetail').html(data);
		}
	});

	MYOBCardID = CardID;
	$("#FooterIcons").css('visibility', 'visible');
};

function SubmitInvoice() {

	var PONumberObject = [];
	var MYOBQuantity;
	var MYOBItemNumber;
	var MYOBDescription;
	var MYOBExTaxTotal;
	var MYOBIncTaxTotal;
	var n = 0;
	
	SQLString = "INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES (";
	
	$('#ClientDetail').ready(function() {
		
		$('input[type=checkbox]:checked').each(function() {
			var CheckPONumber = $(this).closest('tr').find("input[id^=JobPONumber]").val();
			if (CheckPONumber !== "") {
			
				$(this).attr('checked', false);
				$(this).closest("table").addClass(CheckPONumber);
				DoesPONumberExist = "Yes";
			} else {
				DoesPONumberExist = "No";
			}
		});
		
		$('input[type=checkbox]:checked').each(function() {
			CurrentJob = $(this).val();
			CompileJobs(CurrentJob, "", MYOBQuantity, MYOBItemNumber, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID);
		});
		
		// Check if any PO Numbers are being submitted. If none exist, skip the rest of the routine.
		if (DoesPONumberExist == "No") {
			console.log(SQLString);
			EndJobTransaction(SQLString,"First")
			EndJobTransaction("","Last");
			return;
		} else {
			EndJobTransaction(SQLString,"First")
			SubmitToMYOB("END TRANSACTION","Next")
		}

		$('input[type=checkbox]').not(":checked").each(function() {
			MYOBPONumber = $(this).closest('table').attr('class');
			CurrentJobNumber = $(this).closest('table').attr('id').replace(/\D/g,'');
			
			PONumberObject.push({'MYOBPONumber':MYOBPONumber,'CurrentJobNumber':CurrentJobNumber});
		});
		
		function SortByPO(a,b) {
			return a.MYOBPONumber > b.MYOBPONumber ? 1 : -1;
		}
		
		PONumberObject.sort(SortByPO);
		
		for (var i = 0; i < PONumberObject.length; i++) {

			var MYOBPONumber = PONumberObject[i].MYOBPONumber;
			var CurrentJobNumber = PONumberObject[i].CurrentJobNumber;
		
			if (i == 0) {
				// First PO Number
				var PreviousPONumber = "";
				CompileJobs(CurrentJobNumber, MYOBPONumber, MYOBQuantity, MYOBItemNumber, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID);
				continue;
			} else {
				var PreviousPONumber = PONumberObject[i - 1].MYOBPONumber;
			}
			
			if (MYOBPONumber == PreviousPONumber) {
				CompileJobs(CurrentJobNumber, MYOBPONumber, MYOBQuantity, MYOBItemNumber, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID);
			} else {
				EndJobTransaction(SQLString,"Next")
				CompileJobs(CurrentJobNumber, MYOBPONumber, MYOBQuantity, MYOBItemNumber, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID);
			}
		}
		
		EndJobTransaction(SQLString,"Last")
		
	});
	
	// Basic error checking
	if (typeof MYOBDeliveryStatus === 'undefined') {
		alert("You must select to either PRINT or EMAIL your invoice!");
		return;
	}
}

function EndJobTransaction(SQLString,JobStatus) {
		
		SQLString = SQLString.slice(0, - 1);
		
		if (JobStatus == "First") {
			SubmitToMYOB(SQLString,JobStatus);
			
		} else if (JobStatus == "Next") {
			SubmitToMYOB(SQLString,JobStatus);
			SubmitToMYOB("END TRANSACTION",JobStatus);
			
		} else if (JobStatus == "Last") {
			SubmitToMYOB(SQLString,JobStatus);
		}
}

function CompileJobs(CurrentJob, MYOBPONumber, MYOBQuantity, MYOBItemNumber, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID) {

	// Add initial job title
	MYOBDescription = $('span#JobTitle_' + CurrentJob).html();
	AppendToSQLString(MYOBPONumber,"misc",MYOBDeliveryStatus,"1",MYOBDescription,"0","0",MYOBCardID);

	$('td#JobNotes_' + CurrentJob).each(function() {
		
		var WholeText = $(this).html();
		var Description = WholeText.split('\n');
		
		var n;
		for (n = 0; n < Description.length; n++) {
		
			if (Description[n].length > 255) {
				$('td#JobNotes_' + CurrentJob).addClass("LengthExceeded");
				alert("Line exceeds 255 characters! Please shorten this line.");
			}

			var CheckJobCode = $(this).closest('tr').find("input[id^=JobCode]").val();
			
			if (n != 0) {
				if (CheckJobCode.substring(0, 6) == "onsite" || CheckJobCode.substring(0, 6) == "inshop") {
					MYOBQuantity = "1";
					MYOBItemNumber = "Service";
					MYOBExTaxTotal = "0";
					MYOBIncTaxTotal = "0";
				}
			} else {
				MYOBQuantity = $(this).closest('tr').find("input[id^=JobQty]").val();
				MYOBItemNumber = $(this).closest('tr').find("input[id^=JobCode]").val();
				MYOBExTaxTotal = $(this).closest('tr').find("input[id^=JobLineTotal]").val();
				MYOBIncTaxTotal = "0";
			}
			
			if (Description[n] != "") {
				MYOBDescription = Description[n].replace(/'/g,"''");
			}
			
			AppendToSQLString(MYOBPONumber,MYOBItemNumber,MYOBDeliveryStatus,MYOBQuantity,MYOBDescription,MYOBExTaxTotal,MYOBIncTaxTotal,MYOBCardID);
		}
	});
	
	// Add blank-line between jobs
	AppendToSQLString(MYOBPONumber,"misc",MYOBDeliveryStatus,"1","-","0","0",MYOBCardID);
}

var AppendToSQLString = function (MYOBPONumber, MYOBItemNumber, MYOBDeliveryStatus, MYOBQuantity, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID) {

	SQLString += "('"+ MYOBPONumber +"','"+ MYOBItemNumber +"','"+ MYOBDeliveryStatus +"','"+ MYOBQuantity +"','"+ MYOBDescription +"','"+ MYOBExTaxTotal +"','"+ MYOBIncTaxTotal +"','"+ MYOBCardID +"') ,";
}

function SubmitToMYOB(SQLString,JobStatus) {

	// Need counter for how many unique invoices submitted - to return invoice numbers
	
	$.ajax({

		url: "Invoice_Submit.php",
		type: "POST",
		data: { 'SQLString' : SQLString,
				'JobStatus'	: JobStatus},
		success: function(data) {
			console.log(data);
		}
	});

	window.SQLString = "INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES (";
}

</script>

<div class="ClientSummary">
<table class="ClientTable">

<?

// Display summarised list of clients who have outstanding invoices
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
