<?
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Module includes
include "./moduleFunctions.php" ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$higherEducationApplicationInstitutionID=$_GET["higherEducationApplicationInstitutionID"] ;

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/applications_track_edit.php&higherEducationApplicationInstitutionID=$higherEducationApplicationInstitutionID" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/applications_track_edit.php")==FALSE) {
	//Fail 0
	$URL=$URL . "&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Check for student enrolment
	if (studentEnrolment($_SESSION[$guid]["gibbonPersonID"], $connection2)==FALSE) {
		//Fail 0
		$URL=$URL . "&addReturn=fail0" ;
		header("Location: {$URL}");
	}
	else {
		//Check for application record
		try {
			$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
			$sql="SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			//Fail 2
			$URL=$URL . "&addReturn=fail2" ;
			header("Location: {$URL}");
			break ;
		}
		
		if ($result->rowCount()!=1) {
			//Fail 2
			$URL=$URL . "&addReturn=fail2" ;
			header("Location: {$URL}");
		}
		else {
			$row=$result->fetch() ;
	
			//Check if application specified
			if ($higherEducationApplicationInstitutionID=="") {
				//Fail 1
				$URL=$URL . "&addReturn=fail1" ;
				header("Location: {$URL}");
			}
			else {
				try {
					$data=array("higherEducationApplicationInstitutionID"=>$higherEducationApplicationInstitutionID); 
					$sql="SELECT * FROM higherEducationApplicationInstitution WHERE higherEducationApplicationInstitutionID=:higherEducationApplicationInstitutionID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) { 
					//Fail 2
					$URL=$URL . "&addReturn=fail2" ;
					header("Location: {$URL}");
				}
				
				if ($result->rowCount()!=1) {
					//Fail 2
					$URL=$URL . "&addReturn=fail2" ;
					header("Location: {$URL}");
				}
				else {
					$row=$result->fetch() ;
			
					//Validate Inputs
					$higherEducationApplicationID=$row["higherEducationApplicationID"] ;
					$higherEducationInstitutionID=$_POST["higherEducationInstitutionID"] ;
					$higherEducationMajorID=$_POST["higherEducationMajorID"] ;
					$scholarship=$_POST["scholarship"] ;
					$applicationNumber=$_POST["applicationNumber"] ;
					$rank=$_POST["rank"] ;
					$rating=$_POST["rating"] ;
					$status=$_POST["status"] ;
					$question=$_POST["question"] ;
					$answer=$_POST["answer"] ;
					$offer=$_POST["offer"] ;
					$offerDetails=$_POST["offerDetails"] ;
					
					if ($higherEducationApplicationID=="" OR $higherEducationInstitutionID=="" OR $higherEducationMajorID=="") {
						//Fail 3
						$URL=$URL . "&updateReturn=fail3" ;
						header("Location: {$URL}");
					}
					else {
						//Write to database
						try {
							$data=array("higherEducationApplicationID"=>$higherEducationApplicationID, "higherEducationInstitutionID"=>$higherEducationInstitutionID, "higherEducationMajorID"=>$higherEducationMajorID, "scholarship"=>$scholarship, "applicationNumber"=>$applicationNumber, "rank"=>$rank, "rating"=>$rating, "status"=>$status, "question"=>$question, "answer"=>$answer, "offer"=>$offer, "offerDetails"=>$offerDetails, "higherEducationApplicationInstitutionID"=>$higherEducationApplicationInstitutionID); 
							$sql="UPDATE higherEducationApplicationInstitution SET higherEducationApplicationID=:higherEducationApplicationID, higherEducationInstitutionID=:higherEducationInstitutionID, higherEducationMajorID=:higherEducationMajorID, scholarship=:scholarship, applicationNumber=:applicationNumber, rank=:rank, rating=:rating, status=:status, question=:question, answer=:answer, offer=:offer, offerDetails=:offerDetails WHERE higherEducationApplicationInstitutionID=:higherEducationApplicationInstitutionID" ;
							$result=$connection2->prepare($sql);
							$result->execute($data);
						}
						catch(PDOException $e) { 
							//Fail 2
							$URL=$URL . "&updateReturn=fail2" ;
							header("Location: {$URL}");
							break ;
						}
						
						//Success 0
						$URL=$URL . "&updateReturn=success0" ;
						header("Location: {$URL}");
					}	
				}
			}
		}
	}
}
?>