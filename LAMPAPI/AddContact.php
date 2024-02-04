<?php 
	error_reporting(E_ALL);
	$inData = getRequestInfo();

	// Error catching
	try {
	    $conn = new mysqli("localhost", "EatSand", "yurt", "COP4331");
	
	    if ($conn->connect_error) {
	        throw new Exception('Failed to connect to the database: ' . $conn->connect_error);
	    }
	
	    // ... (other database-related code)
	
	    // Close the database connection when done
	    $conn->close();
	} catch (Exception $e) {
	    // Log the exception details to the error log
	    error_log('Caught exception: ' . $e->getMessage());
	
	    // Return an appropriate error response to the client
	    returnWithError('An error occurred');
	}

	$FirstName = $inData["FirstName"];
	$LastName = $inData["LastName"];
	$Phone = $inData["Phone"];
	$Email = $inData["Email"];
	$UserId = $inData["UserId"];

	$searchCount = 0;

	$conn = new mysqli("localhost", "EatSand", "yurt", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$stmt = $conn->prepare("select * from Contacts where (FirstName like ? AND LastName like ?) and UserID=?");
		$stmt->bind_param("sss", $FirstName, $LastName, $UserId);
		$stmt->execute();
		
		$result = $stmt->get_result();

		while ($row = $result->fetch_assoc())
		{
			$searchCount++;
		}
		
		if ($searchCount == 0)
		{
			$stmt = $conn->prepare("INSERT into Contacts (FirstName, LastName, Phone, Email, UserId) VALUES(?,?,?,?,?)");
			$stmt->bind_param("sssss", $FirstName, $LastName, $Phone, $Email, $UserId);
			
			if ($stmt->execute())
			{
				returnWithInfo( $inData['FirstName'], $inData['LastName'], $inData["Phone"], $inData["Email"], $inData["UserId"] );
			}
			else
			{
				returnWithError("Failed to add contact");
			}
		}
		else
		{
			returnWithError("Contact already exists");
		}

		$stmt->close();
		$conn->close();
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

	function returnWithInfo( $firstName, $lastName, $phone, $email, $userId)
	{
		$retValue = '{"UserId":"' . $userId . '","FirstName":"' . $firstName . '","LastName":"' . $lastName . '","Phone":"' . $phone . '","Email":"' . $email . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
