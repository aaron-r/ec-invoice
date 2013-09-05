<html>
<head>
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/jquery.svg.js"></script>

</head>
<title>Efficient Chips</title>
<body>

<?

$DatabaseHost 		= '10.10.0.5';	// 10.10.0.5
$DatabaseName 		= 'echips_v2';
$DatabaseUser		= 'trevorp';
$DatabasePass		= 'megacool';
$CounterStart 		= 0;
$CounterDisplay		= 0;

try {
	$Database = new PDO('mysql:host='.$DatabaseHost.';dbname='.$DatabaseName.'',$DatabaseUser,$DatabasePass);
} catch (Exception $e) {
	die("Unable to connect to V2: " . $e->getMessage());
}

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
var SQLArray = [];
var JobNumberArray = [];
var IsFunctionValid = "TRUE";

// Highlight selected customer
$(document).ready(function() {

	$('tr#JobList').click(function() {
		$('tr').removeClass("HighlightJob");
		$(this).addClass("HighlightJob");
	});
	
	$('#ClientDetail').html('<div id="MainPrompt"> <img id="main-prompt" class="svg" src="img/ModernUI/book-empty.svg" /> <p id="main-prompt-text">Select a client to begin.</span> <div>');
	
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
	
		// Basic error checking
	if (typeof MYOBDeliveryStatus === 'undefined') {
		alert("You must select to either PRINT or EMAIL your invoice!");
		return false;
	}
	
	$('#ClientDetail').ready(function() {
		
		$('input[type=checkbox]:checked').each(function() {
			var CheckPONumber = $(this).closest('tr').find("input[id^=JobPONumber]").val();
			
			if (CheckPONumber !== "") {
				$(this).attr('checked', false);
				$(this).closest("table").addClass(CheckPONumber);
			}
		});
		
		if (!$('[id^="JobPONumber"]').val()) {
			DoesPONumberExist = "No";
		} else {
			DoesPONumberExist = "Yes";
		}
		
		$('input[type=checkbox]:checked').each(function() {
			CurrentJob = $(this).val();
			CompileJobs(CurrentJob, "", MYOBQuantity, MYOBItemNumber, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID);
		});
		
		// If no PO numbers are inputted, skip straight to submitting the invoice!
		if (DoesPONumberExist == "No") {
			EndJobTransaction(SQLString,"First")
			SubmitToMYOB(SQLArray);
			return;
		} else {
			EndJobTransaction(SQLString,"First")
			SQLArray.push("END TRANSACTION");
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
}

function EndJobTransaction(SQLString,JobStatus) {

	SQLString = SQLString.slice(0, - 1);
	SQLString += ")";
	
	if (JobStatus == "First") {
	
		SQLArray.push(SQLString);
		window.SQLString = "INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES (";
		
	} else if (JobStatus == "Next") {
	
		SQLArray.push(SQLString);
		SQLArray.push("END TRANSACTION");
		window.SQLString = "INSERT INTO Import_Item_Sales (CustomersNumber, ItemNumber, DeliveryStatus, Quantity, Description, ExTaxTotal, IncTaxTotal, CardID) VALUES (";
		
	} else if (JobStatus == "Last") {

		SQLArray.push(SQLString);
		SubmitToMYOB(SQLArray);
	}

}

function SubmitToMYOB(SQLArray) {
	
	$.ajax({
		url: "Invoice_Submit.php",
		type: "POST",
		data: { 'SQLArray' 			: SQLArray,
				'JobNumberArray' 	: JobNumberArray },
		success: function(data) {
			console.log(data);
			JSONResponse = JSON.parse(data);
			console.log(JSONResponse.MYOBResponse);
			
			WasInvoiceSubmitted(JSONResponse.MYOBResponse);
		}
	});
	
	window.SQLArray = [];
	window.JobNumberArray = [];
}

function WasInvoiceSubmitted(MYOBResponse) {
	
	if (MYOBResponse == "Total does not match; recalculated." || MYOBResponse == "Invalid or blank Payment is Due; default substituted.") {
		$('#ClientDetail').html('<div id="InvoiceSuccess"> <img id="main-prompt" class="svg" src="img/ModernUI/smiley-happy.svg" /> <p id="main-prompt-text">Success, the invoice was submitted!</span> <div>');
	} else {
		$('#ClientDetail').html('<div id="InvoiceFailure"> <img id="main-prompt" class="svg" src="img/ModernUI/smiley-frown.svg" /> <p id="main-prompt-text">Uh oh, something went wrong while submitting the invoice. <br> <p id="invoice-failure-reason">' + MYOBResponse + '</span> </span> <div>');
	}
	
	$('#ClientLoadContainer').load('Invoice_Summary.php .ClientSummary');
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
				
				if (MYOBExTaxTotal == "0.00") {
			
					var x=confirm("Invoice #" + CurrentJob + " contains an empty total. Continue?");
					
					if (x==true) {
						// Do nothing
					} else {
						return;
					}
				}
			}
			
			if (Description[n] != "") {
				MYOBDescription = Description[n].replace(/'/g,"''");
			}
			
			AppendToSQLString(MYOBPONumber,MYOBItemNumber,MYOBDeliveryStatus,MYOBQuantity,MYOBDescription,MYOBExTaxTotal,MYOBIncTaxTotal,MYOBCardID);
			
		}
	});
	
	// Add blank-line between jobs
	AppendToSQLString(MYOBPONumber,"misc",MYOBDeliveryStatus,"1","-","0","0",MYOBCardID);
	
	JobNumberArray.push(CurrentJob);
}

var AppendToSQLString = function (MYOBPONumber, MYOBItemNumber, MYOBDeliveryStatus, MYOBQuantity, MYOBDescription, MYOBExTaxTotal, MYOBIncTaxTotal, MYOBCardID) {

	SQLString += "('"+ MYOBPONumber +"','"+ MYOBItemNumber +"','"+ MYOBDeliveryStatus +"','"+ MYOBQuantity +"','"+ MYOBDescription +"','"+ MYOBExTaxTotal +"','"+ MYOBIncTaxTotal +"','"+ MYOBCardID +"'),";
}

</script>

<div id="ClientLoadContainer">
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
