<?// Connect and submit invoice to MYOB$SQLString = $_POST['SQLString'];$JobStatus = $_POST['JobStatus'];echo $JobStatus;if ($JobStatus == "First") {	$Database = new PDO("odbc:Driver={MYOAU1001}; Database=MYOBDevelopment; ");	$Database->beginTransaction();	echo "Beginning transaction...";}	$SQLStatement = $Database -> prepare ($SQLString);if ($JobStatus == "Last") {	$SQLStatement->commit();	echo "Transaction finished!";}echo file_get_contents('C:\Users\aaron.r\AppData\Local\Temp\MYOBODBCError.txt', true);?>