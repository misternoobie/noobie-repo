<!--
This is the main logic of the system. This is where the filters are executed and where the data logic is gathered.
-->

<?php 
include_once('Constants.php');
include_once(ABS_PATH .'/classes/ticketInfo.php');
include_once(ABS_PATH .'/classes/variables.php');
include_once(ABS_PATH .'/classes/staff.php');

function changeView($viewType){
	global $displayView;
	if (!strcmp($viewType,"priority")){
		$displayView = "priority";
	}
	if (!strcmp($viewType,"component")){
		$displayView = "component";	
	}	
	if (!strcmp($viewType,"key")){
		$displayView = "key";
	}
	if (!strcmp($viewType,"version")){
		$displayView = "version";
	}
	if (!strcmp($viewType,"project")){
		$displayView = "project";
	}	
}

//check if name exist in the staff list
function nameExist($name, $nameList){
	for($x = 0; $x < count($nameList); $x++){
		if(!strcmp($name,$nameList[$x]->name)){	
			return true;
		}
	}
	return false;
}

//check if member is part of the team. If there is a new member, add in the teamMembers in variables.php
function isTeamMember($name){
	global $teamMembers;
	foreach ($teamMembers as $member) {
	   if(strpos($name,$member) !== false){
		   return true;
	   }
	}
	return false;
}

// field color code
function colorCode($singleTicket){
	$htmlCode = "";
	if(!strcmp($singleTicket->issueType,"Bug")){
		if(strpos($singleTicket->reportedByCustomer,'true') !== false){
			if(!strcmp($singleTicket->priority,"P1")){
				$htmlCode .= "background-color:#CC3300";
			}	
			else if(!strcmp($singleTicket->priority,"P3") || !strcmp($singleTicket->priority,"P4")){
				$htmlCode .= "background-color:#33FF00";
			}
			else if(!strcmp($singleTicket->priority,"P2")){
				$htmlCode .= "background-color:#FFFF33";
			}		
		} else{
			return "background-color:#66FFCC";
		}
	} else { 
		//if issueType is resolution
		if(strpos($singleTicket->richText,'External') !== false || strpos($singleTicket->richText,'external') !== false){
			if(!strcmp($singleTicket->priority,"P1")){
				$htmlCode .= "background-color:#CC3300";
			}	
			else if(!strcmp($singleTicket->priority,"P3") || !strcmp($singleTicket->priority,"P4")){
				$htmlCode .= "background-color:#33FF00";
			}
			else if(!strcmp($singleTicket->priority,"P2")){
				$htmlCode .= "background-color:#FFFF33";
			}		
		} else{
			$htmlCode .= "background-color:#66FFCC";
		}
	}
	if(!strcmp($singleTicket->status,"Disputed")){
		$htmlCode = "    animation-name: example;
		animation-duration: 0.8s;
		animation-timing-function: linear;
		animation-iteration-count: infinite; ";
	}
	return $htmlCode;
}

// field color code
function priorityHover($singleTicket){
	return "content:".$singleTicket->priority;
}

// display all the ticket, parameter is staff
function displayDone($assignee){
	global $displayView; // display settings	
	foreach ($assignee->tickets as $singleTicket) {
		if(!strcmp($singleTicket->issueType,"Resolution") && !strcmp($singleTicket->status,"Resolved")){
		echo "<a href='" . $singleTicket->link ."' style='".colorCode($singleTicket)."' ";
		echo "title='". $singleTicket->title ."' target='_blank'>";
		echo $singleTicket->$displayView;
		echo "</a><br>";
		}
	}
}

// set the status of the ticket for the kanban board
function categorizeTicket($singleTicket){
	if (!strcmp($singleTicket->issueType,"Bug") 
		&& (!strcmp($singleTicket->status,"Open") || !strcmp($singleTicket->status,"Awaiting Reply")
		|| !strcmp($singleTicket->status,"Disputed"))){
			// kanban 1st status
			$singleTicket->kanbanStatus = KANBAN_STATUS_1;
	} else if (!strcmp($singleTicket->issueType,"Bug") 
		&& !strcmp($singleTicket->status,"Accepted") 
		&& (strcmp($singleTicket->statusWhiteBoard,"On Going") 
		&& strcmp($singleTicket->statusWhiteBoard,"On going"))){
			// kanban 2nd status
			$singleTicket->kanbanStatus = KANBAN_STATUS_2;
	} else if (!strcmp($singleTicket->issueType,"Bug") 
		&& !strcmp($singleTicket->status,"Accepted") 
		&& (strripos($singleTicket->statusWhiteBoard,'going') !== false)){
			// kanban 3rd status
			$singleTicket->kanbanStatus = KANBAN_STATUS_3;
	} else if (!strcmp($singleTicket->issueType,"Resolution") 
		&& !strcmp($singleTicket->status,"Open") 
		&& empty($singleTicket->statusWhiteBoard)){
			// kanban 4th status
			$singleTicket->kanbanStatus = KANBAN_STATUS_4;
	} else if (!strcmp($singleTicket->issueType,"Resolution") 
		&& !strcmp($singleTicket->status,"Accepted") 
		&& (strcmp($singleTicket->statusWhiteBoard,"On Going") 
		&& strcmp($singleTicket->statusWhiteBoard,"On going"))){
			// kanban 5th status
			$singleTicket->kanbanStatus = KANBAN_STATUS_5;
	} else if (!strcmp($singleTicket->issueType,"Resolution") 
		&& !strcmp($singleTicket->status,"Accepted")
		&& (strripos($singleTicket->statusWhiteBoard,'going') !== false)){
			// kanban 6th status
			$singleTicket->kanbanStatus = KANBAN_STATUS_6;
	} else if (!strcmp($singleTicket->issueType,"Resolution") 
		&& !strcmp($singleTicket->status,"Development Complete")
		&& (strcmp($singleTicket->statusWhiteBoard,"On Going") 
		&& strcmp($singleTicket->statusWhiteBoard,"On going"))){
			// kanban 7th status
			$singleTicket->kanbanStatus = KANBAN_STATUS_7;
	} else if (!strcmp($singleTicket->issueType,"Resolution") 
		&& !strcmp($singleTicket->status,"Development Complete")
		&& (strripos($singleTicket->statusWhiteBoard,'going') !== false)){
			// kanban 8th status
			$singleTicket->kanbanStatus = KANBAN_STATUS_8; 
	} else if (!strcmp($singleTicket->issueType,"Resolution") 
		&& !strcmp($singleTicket->status,"Awaiting Verification")){
			// kanban 9th status
			$singleTicket->kanbanStatus = KANBAN_STATUS_9;
	}	
}

//gather all the tickets for a specific staff
function collectTicket($assignee){
	global $xml;
	foreach($xml->children()->children() as $xmlTicket) {
		if(!strcmp($xmlTicket->assignee,$assignee->name)){ // traverse the xml to find the ticket of the assignee
			$singleTicket = new ticketInfo(); 
			$singleTicket->issueType = $xmlTicket->type;
			$singleTicket->key = $xmlTicket->key;
			$singleTicket->title  = $xmlTicket->title;
			$singleTicket->link = $xmlTicket->link;
			$singleTicket->status = $xmlTicket->status;
			$singleTicket->component  = $xmlTicket->component;	
			$singleTicket->version  = $xmlTicket->version;
			$singleTicket->project  = $xmlTicket->project;
			$singleTicket->due  = $xmlTicket->due;			
			if ($xmlTicket->labels){
			   $singleTicket->labels = $xmlTicket->labels->children();
		    }			
			//reported priority
			if ($xmlTicket->customfields){		
				foreach($xmlTicket->customfields->customfield as $customfield) {
					if(!strcmp($customfield['id'],"customfield_10021"))
						if(!strcmp($customfield->customfieldname,"Reported Priority"))
							foreach($customfield->customfieldvalues->customfieldvalue as $customfieldvalue)
								if(!strcmp($customfieldvalue,"1 - Show-Stopper"))
									$singleTicket->priority = "P1";
								else if(!strcmp($customfieldvalue,"2 - Critical"))
									$singleTicket->priority = "P2";
								else if(!strcmp($customfieldvalue,"3 - Major"))
									$singleTicket->priority = "P3";
								else if(!strcmp($customfieldvalue,"4 - Minor"))
									$singleTicket->priority = "P4";								
				} 
		    }			
			// status whiteboard
			if ($xmlTicket->customfields){		
				foreach($xmlTicket->customfields->customfield as $customfield) {
					if(!strcmp($customfield['id'],"customfield_10130"))
						if(!strcmp($customfield->customfieldname,"Status Whiteboard"))
							foreach($customfield->customfieldvalues->customfieldvalue as $customfieldvalue)
								if(!strcmp($customfieldvalue,"On Going") || !strcmp($customfieldvalue,"On going") || !strcmp($customfieldvalue,"on going"))
									$singleTicket->statusWhiteBoard = $customfieldvalue;
				} 
		    }		
			// richText, determine if it is external
			if ($xmlTicket->customfields){		
				foreach($xmlTicket->customfields->customfield as $customfield) {
					if(!strcmp($customfield['id'],"customfield_10110"))
						if(!strcmp($customfield->customfieldname,"Rich Text Comment"))
							foreach($customfield->customfieldvalues->customfieldvalue as $customfieldvalue)
								if(strpos($customfieldvalue,'External') !== false || strpos($customfieldvalue,'external') !== false)
									$singleTicket->richText = $customfieldvalue;
				} 
		    }	
			// Reported By Customer, determine if bug external
			if ($xmlTicket->customfields){		
				foreach($xmlTicket->customfields->customfield as $customfield) {
					if(!strcmp($customfield['id'],"customfield_10190"))
						if(!strcmp($customfield->customfieldname,"Reported By Customer"))
							foreach($customfield->customfieldvalues->customfieldvalue as $customfieldvalue)
								if(strpos($customfieldvalue,'true') !== false || strpos($customfieldvalue,'false') !== false)
									$singleTicket->reportedByCustomer = $customfieldvalue;
				} 
		    }				
			categorizeTicket($singleTicket);
			$assignee->addTicket($singleTicket);
		}
	} 
}

// store all staff in a single array
function collectStaff(){
	//gather all the staff and store them inside an array
	global $xml;
	global $staffList;
	foreach($xml->children()->children() as $resourcePerson) {
		if (!empty($resourcePerson->assignee) && isTeamMember($resourcePerson->assignee)){
			$staff = new staff;
			$staff->setName($resourcePerson->assignee);			
			if(!nameExist($resourcePerson->assignee, $staffList)){
				array_push($staffList, $staff);
			}
		}
	} 
}

// store all assignee in a single array
function collectAssignee(){
	global $teamMembers;
	global $staffList;
	foreach($teamMembers as $resourcePerson) {
		if (!empty($resourcePerson)){
			$staff = new staff;
			$staff->setName($resourcePerson);			
			if(!nameExist($resourcePerson, $staffList)){
				array_push($staffList, $staff);
			}
		}
	} 
}


function displayTickets($staff){
	/*$dom = new DOMDocument('1.0');//Create new document with specified version number

	$tr = $dom->createElement('tr');

	
	$td = $dom->createElement('td', 'Buffer');
	$domAttribute = $dom->createAttribute('class');
	$domAttribute->value = 'assignee';
	$td = $dom->createElement('td', $staff->name);
	$tr->appendChild($td);*/
	
	echo "<tr>";
	echo "<td class='assignee'>". $staff->name ."</td>";
	echo "<td class='open'>". $staff->displayTicket(KANBAN_STATUS_1) ."". displayTotal($staff,KANBAN_STATUS_1) ."</td>";
	echo "<td class='buffer_accept'>". $staff->displayTicket(KANBAN_STATUS_2)."". displayTotal($staff,KANBAN_STATUS_2) ."</td>";
	echo "<td class='onGoing_accept'>". $staff->displayTicket(KANBAN_STATUS_3)."". displayTotal($staff,KANBAN_STATUS_3)."</td>";
	echo "<td class='forReview'>". $staff->displayTicket(KANBAN_STATUS_4) ."". displayTotal($staff,KANBAN_STATUS_4)."</td>";
	echo "<td class='buffer_dev'>". $staff->displayTicket(KANBAN_STATUS_5) ."". displayTotal($staff,KANBAN_STATUS_5)."</td>";
	echo "<td class='onGoing_dev'>". $staff->displayTicket(KANBAN_STATUS_6) ."". displayTotal($staff,KANBAN_STATUS_6)."</td>";
	echo "<td class='buffer_test'>". $staff->displayTicket(KANBAN_STATUS_7) ."". displayTotal($staff,KANBAN_STATUS_7)."</td>";
	echo "<td class='onGoing_test'>". $staff->displayTicket(KANBAN_STATUS_8) ."". displayTotal($staff,KANBAN_STATUS_8)."</td>";
	echo "<td class='forReview'>". $staff->displayTicket(KANBAN_STATUS_9) ."". displayTotal($staff,KANBAN_STATUS_9)."</td>";
	echo "</tr>";
	/*
	$tr->appendChild($domAttribute);
	$dom->appendChild($tr);
	echo $dom->saveHTML();        //Outputs the generated source code*/
}

function displayTotal($staff , $kanban_status){
	
	$totalTicket = 0;
	foreach ($staff->tickets as $singleTicket) {
		if(!strcmp($singleTicket->kanbanStatus,$kanban_status))
			$totalTicket++;
	}
	if($totalTicket > 0)
		return $totalTicket;
}

//MAIN PROCESS
collectAssignee(); // collect assignee
foreach ($staffList as $staff){
	collectTicket($staff); //collect ticket list for each assignee
}

?>
