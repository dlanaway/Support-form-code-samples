<?php

include "config/config.php";
$issue = getGetData('issue');
$issueType = getGetData('type');

if ($issueType == "issues") {
	$subissueSelected = "0";
	$table = "issues";
	getIssueOptions($table, $issue);
} else {
	$subissueSelected = $issue;
	$issue= "0";
	$table = "subissues";
	getIssueOptions($table, $subissueSelected);
}


getQuestions($issue, $subissueSelected);
$nextId = nextId();


echo "<input type='hidden' id='nextId' name='nextId' value='" . $nextId . "'><br /><div id='delList'></div><div id='buttons'><input id='removeButton' type='button' value='Remove Item(s)' onClick='removeItems()'>\n<input id='addQuestionButton' type='button' value='Add Question' onClick='addQuestion()'>\n<input id='updateQuestionButton' type='submit' value='Update Questions' ></div><br><br>";


function getGetData($name){
	if (!isset($_GET[$name])){
		$getVal = 'undefined';
	} else {
		$getVal = $_GET[$name];
	}
	return $getVal;
}



function getIssueOptions($table, $issue){


	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from $table where ID = :issue");
		$stmt->bindParam(':issue', $issue);

		$stmt->execute();

		$issueOptionList = $stmt->fetchAll(PDO::FETCH_ASSOC);

		displayIssueOptions($issueOptionList, $table);


		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}
}




function getQuestions($issue, $subissueSelected){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from questions where forissue = :forissue and subissue = :subissueSelected order by questionorder asc");
		$stmt->bindParam(':forissue', $issue);
		$stmt->bindParam(':subissueSelected', $subissueSelected);

		$stmt->execute();

		$questionList = $stmt->fetchAll(PDO::FETCH_ASSOC);

		displayList($questionList);


		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}
}

function displayIssueOptions($issueOptionList, $table){
	$issueOptionText = array('Priority', 'File Upload', 'Problem Description');
	$issueValsText = array('priority', 'upload', 'probdesc');
	echo "<br><div id='boxes'>";	
	foreach($issueOptionList as $row){
		$id=$row['ID'];
		$priority=$row['priority'];
		$upload=$row['upload'];
		$probdesc=$row['probdesc'];
		$issueOptions = array($priority, $upload, $probdesc);
		
		for ($i=0;$i<3;$i++){
			echo $issueOptionText[$i] . " <input type='checkbox' name='" . $issueValsText[$i] . "' id='" . $issueValsText[$i] . "' value='" . $issueValsText[$i] . $id; 
			if ($issueOptions[$i] == 1){
				echo "' checked='checked'";
			}
		echo "'>";
		}
	}
	echo "</div><br><input type='hidden' name='issType' value='" . $table . "'>";
}


function displayList($questionList){
	$inputTypes = array('plain text', 'email', 'numeric', 'input text', 'textarea', 'select', 'radio', 'checkbox');
//display checkboxes for priority, upload, probdesc
	$i=1;
	echo "<div id='headings'><div id='del' class='heading'>Remove</div><div id='lab' class='heading'>Label</div><div id='type' class='heading'>Type</div><div id='opt' class='heading'>Options</div><div id='hint' class='heading'>Hint</div><div id='hnttxt' class='heading'>Hint Text</div><div id='reqd' class='heading'>Required</div><div id='errtxt' class='heading'>Error Text</div><div id='tcktxt' class='heading'>Ticket Text</div></div>";
	echo "<ul id='sortable'>";	
	foreach($questionList as $row){

		$id=$row['ID'];
		$label=$row['label'];
		$type=$row['type'];
		$hint=$row['hint'];
		$hintText=$row['hinttext'];
		$required=$row['required'];
		$errMessage=$row['errmsg'];
		$ticketdesc=$row['ticketdescription'];

		echo "<li class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><div class='question' id='item" . $i . "'><input type='hidden' name='id" . $i . "' value='" . $id . "'><input type='checkbox' name='del" . $i . "' id='del" . $i . "' value='" . $id . "'><input id='" . $i . "' type='text' size='30' value='" . $label . "' name='" . $i . "'/>";

		echo "<select name='type" . $i . "' class='element select large' id='type" . $i . "' onChange=\"checkType('" . $i . "')\">";
		for($j=0; $j<count($inputTypes); $j++){
			echo "<option value='" . $inputTypes[$j] . "'"; 
			if ($type == $inputTypes[$j]){
				echo " selected='selected'";
			}
			echo "'>" . $inputTypes[$j] . "</option>";
		}

		echo "</select><div class='editButton'><input id='edit" . $i . "' type='button' value='Edit' onClick=\"Options.render('" . $id . "');\"";

		if (($type == 'select') || ($type == 'radio') || ($type == 'checkbox')){
			echo " style='visibility:visible;'";
		} else {
			echo " style='visibility:hidden;'";
		}
		echo "></div><input type='checkbox' name='hint" . $i . "' id='hint" . $i . "' value='" . $id . "'"; 

		if ($hint == 1){
			echo " checked='checked'";
		}
		if ($type == 'plain text'){
			echo " disabled='true'";
		}
		echo "><input id='hinttext" . $i . "' type='text' size='30' value='" . $hintText . "' name='hinttext" . $i . "'";
		if ($type == 'plain text'){
			echo " disabled='true'";
		}
		
		echo "/>";

		echo "<input type='checkbox' name='required" . $i . "' id='required" . $i . "' value='" . $id . "'";
		if ($required == 1){
			echo " checked='checked'";
		}
		if ($type == 'plain text'){
			echo " disabled='true'";
		}
		echo "><input id='errMessage" . $i . "' type='text' size='30' value='" . $errMessage . "' name='errMessage" . $i . "'";
		if ($type == 'plain text'){
			echo " disabled='true'";
		}
		
		echo "/>";		
				
		echo "<input id='ticketdesc" . $i . "' type='text' size='30' value='" . $ticketdesc . "' name='ticketdesc" . $i . "'";
		if ($type == 'plain text'){
			echo " disabled='true'";
		}
		
		echo "/>";		
		
		echo "</div></li>";
		$i++;
	}
	echo "</ul>";
	echo "<input type='hidden' value='" . ($i-1) . "' name='origNumQuestions'>";
}


function nextId(){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select max(ID) from questions");

		$stmt->execute();

		$lastNum = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
		echo $e;
	}

	$nextId = $lastNum[0]['max(ID)'] + 1;
	return $nextId;
}
?>