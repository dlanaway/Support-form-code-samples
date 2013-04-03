<?php



$selectCases = array();
$selectIds = array();
$selectBits = array();



// rewrite changeIssue file section

function rewriteChangeIssue(){

	$startOfFile = "function checkCookieForOption() {" . PHP_EOL . "var value = getCookieValues();" . PHP_EOL . "if (value == 'on'){" . PHP_EOL . "showHints();" . PHP_EOL . "} else if (value == 'off'){" . PHP_EOL . "hideHints();" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function changeOption(){" . PHP_EOL . PHP_EOL . "var chosenOption  = document.getElementById('issue').value;" . PHP_EOL . PHP_EOL . "var newHTML;" . PHP_EOL . "switch(chosenOption){" . PHP_EOL . "case '0':" . PHP_EOL . "newHTML = \"\";" . PHP_EOL . "document.getElementById('saveForm').disabled = true;" . PHP_EOL . "document.getElementById('buttons').style.left = '330px';" . PHP_EOL . "break;" . PHP_EOL . PHP_EOL;
	$endOfFile = "changeBorderHeight();" . PHP_EOL . "checkCookieForOption();" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function changeBorderHeight(){" . PHP_EOL . "var divh = document.getElementById(\"form_container\").offsetHeight;" . PHP_EOL . "parent.document.getElementById('zenbox_iframe').style.height = (divh + 100) + \"px\";" . PHP_EOL . "parent.document.getElementById('zenbox_screen').style.height = (divh + 240) + \"px\";" . PHP_EOL . "document.getElementById(\"border_left\").style.height = divh + \"px\";" . PHP_EOL . "document.getElementById(\"border_right\").style.height = divh + \"px\";" . PHP_EOL . PHP_EOL . "}";



	$fileCode = $startOfFile;

	//get issue list
	$issueList = getIssueDetails();
	//$i = 2;
	foreach($issueList as $row){
		global $submitable;
		$submitable = false;
		$issueCode = "case '" . $row['ID'] . "':" . PHP_EOL . "newHTML = \"<table><tbody>";
		if($row['subissue'] == 0){

			$issueCode .= createQuestions($row['ID']);
			$issueCode .= "\";" . PHP_EOL;
			//check for priority, upload, probdesc
			if ($row['priority'] == 1){
				$issueCode .= "newHTML = addPriority(newHTML);" . PHP_EOL;
				global $selectBits;
				$selectBit = array('case' => $row['ID'], 'id' => 'priority');
				array_push($selectBits, $selectBit);
			}
			if ($row['upload'] == 1){
				$issueCode .= "newHTML = addUpload(newHTML);" . PHP_EOL;
				$submitable = true;
			}
			if ($row['probdesc'] == 1){
				$issueCode .= "newHTML = addProbDesc(newHTML);" . PHP_EOL;
				$submitable = true;
			}
			$issueCode .= "newHTML = newHTML + \"</tbody></table>\";" . PHP_EOL;

		}else{
			$issueCode .= createSubissueList($row['ID'], $row['subissue']);
			$issueCode .= "</tbody></table><div id='subissue" . $row['ID'] . "'></div>\";" . PHP_EOL;

		}
		if ($submitable == false){
			$issueCode .= "document.getElementById('saveForm').disabled = true;" . PHP_EOL . "document.getElementById('buttons').style.left = '330px';" . PHP_EOL;
		}else{
			$issueCode .= "document.getElementById('saveForm').disabled = false;" . PHP_EOL . "document.getElementById('buttons').style.left = '280px';" . PHP_EOL;
		}
		$issueCode .= "break;" . PHP_EOL . PHP_EOL;
		//$i++;
		$fileCode .= $issueCode;

	}

	$fileCode .= "}" . PHP_EOL . PHP_EOL . "document.getElementById('options').innerHTML = newHTML;" . PHP_EOL . PHP_EOL;

	$selectStyleCode = styleSelect($selectBits);

	$fileCode .= $selectStyleCode;
	$fileCode .= $endOfFile;


	$filename = 'js/changeIssue.js';
	writeFile($filename, $fileCode);

}

function createQuestions($id){
	$fullQuestionCode = '';
	$questions = getQuestionDetails($id);

	foreach($questions as $item){
		switch($item['type']){
			case "plain text";
				$code = createPlain($item);
				break;
			case "email";
				$code = createText($item);
				break;
			case "numeric";
				$code = createNumeric($item);
				break;
			case "input text";
				$code = createText($item);
				break;
			case "select";
				$code = createSelect($item, $id, "");
				//global $selectCases;
				//array_push($selectCases, $id);
				break;
			case "radio";
				$code = createRadio($item);
				break;
			case "textarea";
				$code = createTextArea($item);
				break;
			case "checkbox";
				$code = createCheckbox($item);
				break;
		}
		
		$fullQuestionCode .= $code;

	}
	return $fullQuestionCode;
}

function createSubissueList($id, $subissue){

	$subissueInfo = getSubissueInfo($id);

	$subissueList = getSubissues($subissue);


	global $selectBits;

	$selectBit = array('case' => $id, 'id' => $id);
	array_push($selectBits, $selectBit);

	$label = $subissueInfo[0]['selectText'];

	$listCode = "<tr><td class='labelText'><label class='description' for='issuetype' id='problemSelect'>" . $label . "</label></td><td><div>";
	//$name = $subissueList[''];

	$listCode .= "<select name='" . $id . "' class='element select large' id='" . $id . "' onChange='changeSubIssue" . $subissueInfo[0]['subissue'] . "();'><option value='0' selected='selected'>&nbsp;</option>";


	foreach($subissueList as $listItem) {
		$listCode .= "<option value='s" . $listItem['ID'] . "' >" . $listItem['name'] . "</option>";
	
	}
	
	return $listCode;

}

function getSubissueInfo($id){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from issues where id = :id");
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		$subissueInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $subissueInfo;
}



// rewrite changeSubIssue file section

function rewriteChangeSubIssue(){

	$startOfFile = "";


	$endOfFile = "function addPriority(newHTML){" . PHP_EOL . "newHTML = newHTML + \"<tr><td class='labelText'><label class='description' for='priority'>Priority: </label></td><td><div><select name='priority' class='element select large' id='priority'><option value='1' selected='selected'>Low</option><option value='2' >Normal</option><option value='4' >Urgent</option></select></div><p class='instruct hint' id='instruct10'><small>Low = This can wait<br>Normal = I need it fixed soon<br>Urgent = If this is not fixed I can't do any work</small></p></td></tr>\";" . PHP_EOL . "return newHTML;" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function addUpload(newHTML){" . PHP_EOL . "newHTML = newHTML + \"<tr><td class='labelText'><label class='description' for='description'>Upload a screen shot:</label></td><td><input type='file' name='file' id='saveFrm' class='appnitro'/><br /><br /><a href='#'>How to take a screen shot</a><br />Allowed File Extensions: bmp, jpg, jpeg, gif, png, <br>doc, docx, zip, xls, xlsx, pdf, txt</td></tr>\";" . PHP_EOL . "return newHTML;" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function addProbDesc(newHTML){" . PHP_EOL . "newHTML = newHTML + \"<tr><td class='labelText'><label class='description' for='description'>Problem Description: *</label></td><td class='inputSide'><div><textarea id='description' name='description' class='element textarea medium'></textarea></div></td></tr>'\";" . PHP_EOL . "return newHTML;" . PHP_EOL . "}" . PHP_EOL;


	$startFunctnCode = "function changeSubIssue";

	$startFunctnCodePt2 = "(){" . PHP_EOL . PHP_EOL . "var chosenOption  = document.getElementById('";

	$nextFunctnCode = "').value;" . PHP_EOL . PHP_EOL . "var newHTML;" . PHP_EOL . "switch(chosenOption){" . PHP_EOL . "case '0':" . PHP_EOL . "newHTML = \"\";" . PHP_EOL . "document.getElementById('saveForm').disabled = true;" . PHP_EOL . "document.getElementById('buttons').style.left = '330px';" . PHP_EOL . "break;" . PHP_EOL . PHP_EOL;


	$fileCode = $startOfFile;

	$subissueNums = getSubIssueNums();


	foreach ($subissueNums as $subiss){


		$selectBits = array();

		$functnCode = "";
		$functnCode = $startFunctnCode . $subiss['subissue'] . $startFunctnCodePt2 . $subiss['ID'] . $nextFunctnCode;

		$subissueList = getSubissues($subiss['subissue']);


		foreach($subissueList as $row){
			global $submitable;
			$submitable = false;
			$issueCode = "case 's" . $row['ID'] . "':" . PHP_EOL . "newHTML = \"<table><tbody>";

			$issueCode .= createSubissueQuestions($row['ID']);
			$issueCode .= '";' . PHP_EOL;
			//check for priority, upload, probdesc
			if ($row['priority'] == 1){
				$issueCode .= "newHTML = addPriority(newHTML);" . PHP_EOL;

				global $selectBits;


				$selectBit = array('case' => 's' . $row['ID'], 'id' => 'priority');
				array_push($selectBits, $selectBit);
			}
			if ($row['upload'] == 1){
				$issueCode .= "newHTML = addUpload(newHTML);" . PHP_EOL;
				$submitable = true;
			}
			if ($row['probdesc'] == 1){
				$issueCode .= "newHTML = addProbDesc(newHTML);" . PHP_EOL;
				$submitable = true;
			}
			$issueCode .= "newHTML = newHTML + \"</tbody></table>\";" . PHP_EOL;
			if ($submitable == false){
				$issueCode .= "document.getElementById('saveForm').disabled = true;" . PHP_EOL . "document.getElementById('buttons').style.left = '330px';" . PHP_EOL;
			}else{
				$issueCode .= "document.getElementById('saveForm').disabled = false;" . PHP_EOL . "document.getElementById('buttons').style.left = '280px';" . PHP_EOL;
			}	
			$issueCode .= "break;" . PHP_EOL;
			//$i++;
			$functnCode .= $issueCode . PHP_EOL;


		}
		$selectStyleCode = styleSelect($selectBits);


		$functnCode .= "}" . PHP_EOL . "document.getElementById('subissue" . $subiss['ID'] . "').innerHTML = newHTML;" . PHP_EOL . $selectStyleCode . PHP_EOL . "changeBorderHeight();" . PHP_EOL . "checkCookieForOption();" . PHP_EOL;

		$fileCode .= $functnCode . "}" . PHP_EOL . PHP_EOL;

	}
	$fileCode .= $endOfFile;


	$filename = 'js/changeSubIssue.js';
	writeFile($filename, $fileCode);

}

function getSubissueNums(){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from issues where subissue > 0 order by subissue asc");
		$stmt->bindParam(':subissue', $subissue);
		$stmt->execute();

		$subissues = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $subissues;
}

function createSubissueQuestions($id){
	$fullQuestionCode = '';
	$questions = getSubissueQuestionDetails($id);

	foreach($questions as $item){
		switch($item['type']){
			case "plain text";
				$code = createPlain($item);
				break;
			case "email";
				$code = createText($item);
				break;
			case "numeric";
				$code = createNumeric($item);
				break;
			case "input text";
				$code = createText($item);
				break;
			case "select";
				$code = createSelect($item, $id, "s");
				break;
			case "radio";
				$code = createRadio($item);
				break;
			case "textarea";
				$code = createTextArea($item);
				break;
			case "checkbox";
				$code = createCheckbox($item);
				break;
		}
		
		$fullQuestionCode .= $code;

	}
	return $fullQuestionCode;
}

function getSubissueQuestionDetails($id){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from questions where subissue = :id order by questionorder asc");
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $questions;


}




// rewrite validation file section

function rewriteValidation(){

	$requiredQuestions = getRequiredQuestionDetails();

	$fileCode = "var errors = new Array()" . PHP_EOL . "errors[\"name\"] = \"Please enter your name.\";" . PHP_EOL . "errors[\"email\"] = \"Please enter a valid email address.\";" . PHP_EOL . "errors[\"description\"] = \"Please enter a problem description.\";" . PHP_EOL;

	$endCode = "function checkEmail(id){" . PHP_EOL . "var invalidChars = \" /:,;\";" . PHP_EOL . "var emailOK = true;" . PHP_EOL . "var badChar;" . PHP_EOL . "var atPos;" . PHP_EOL . "var periodPos;" . PHP_EOL . PHP_EOL . "var textVal = document.getElementById(id).value;" . PHP_EOL . "if (textVal == \"\"){" . PHP_EOL . "alertToProblem(id);" . PHP_EOL . "return false;" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "// check for invalid characters" . PHP_EOL . "for (var counter=0; counter<invalidChars.length; counter++) {" . PHP_EOL . "badChar = invalidChars.charAt(counter);" . PHP_EOL . "if (textVal.indexOf(badChar,0) != -1){" . PHP_EOL . "emailOK = false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "// check the for @ sign" . PHP_EOL . "atPos = textVal.indexOf(\"@\",1);" . PHP_EOL . "if (atPos == -1) {" . PHP_EOL . "emailOK = false;" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "if (id == 'email'){" . PHP_EOL . "var isDogshome = textVal.search(\"@dogshome.com\");" . PHP_EOL . "var isPetregister = textVal.search(\"@petregister.com.au\");" . PHP_EOL . "if ((isDogshome == -1) && (isPetregister == -1)){" . PHP_EOL . "emailOK = false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . "if (emailOK == false){" . PHP_EOL . "alertToProblem(id);" . PHP_EOL . "return false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function checkNumeric(id){" . PHP_EOL . "var inputVal = document.getElementById(id).value;" . PHP_EOL . "var isNumber = !isNaN(parseFloat(inputVal)) && isFinite(inputVal);" . PHP_EOL . "if (isNumber == false){" . PHP_EOL . "alertToProblem(id);" . PHP_EOL . "return false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function checkDate(id){" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function checkText(id) {" . PHP_EOL . "var textVal = document.getElementById(id).value;" . PHP_EOL . "if (textVal == \"\"){" . PHP_EOL . "alertToProblem(id);" . PHP_EOL . "return false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function checkRadio(id) {" . PHP_EOL . "var radioInput = document.getElementsByName(id +\"Choice\");" . PHP_EOL . "var radioSelected = false;" . PHP_EOL . "for(var i=0; i<radioInput.length; i++){" . PHP_EOL . "if (radioInput[i].checked) radioSelected = true;" . PHP_EOL . "}" . PHP_EOL . "if (radioSelected == false){" . PHP_EOL . "alertToProblem(id);" . PHP_EOL . "return false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function checkDropDown(id){" . PHP_EOL . "var selectedVal = document.getElementById(id).value;" . PHP_EOL . "if (selectedVal == \"0\"){" . PHP_EOL . "alertToProblem(id);" . PHP_EOL . "return false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function checkCheckboxes(id) {" . PHP_EOL . "var anyChecked = false;" . PHP_EOL . "var checkElements = document.getElementsByName(id + '[]');" . PHP_EOL . "for(var i = 0; i < checkElements.length; i++){" . PHP_EOL . "if(checkElements[i].checked) {" . PHP_EOL . "anyChecked = true;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . "var otherVal = document.getElementById(id + 'other').value;" . PHP_EOL . "if(otherVal != ''){" . PHP_EOL . "anyChecked = true;" . PHP_EOL . "}" . PHP_EOL . "if (anyChecked == false){" . PHP_EOL . "alertToProblem(id);" . PHP_EOL . "return false;" . PHP_EOL . "}" . PHP_EOL . "}" . PHP_EOL . PHP_EOL . "function alertToProblem(id) {" . PHP_EOL . "var errorMsg = errors[id];" . PHP_EOL . "alert(errorMsg);" . PHP_EOL . "}" . PHP_EOL;

	$errorArray = '';

	foreach ($requiredQuestions as $row){
		$errmsg = redoAposts($row['errmsg']);
		$errorArray .= 'errors["q' . $row['ID'] . '"] = "' . $errmsg . '";' . PHP_EOL;
	}

	$fileCode .= $errorArray . PHP_EOL;

	$fileCode .= "function checkForm(form) {" . PHP_EOL . PHP_EOL . "if (checkText('name') == false) return false;" . PHP_EOL . "if (checkEmail('email') == false) return false;" . PHP_EOL . PHP_EOL . "//get value of issue list" . PHP_EOL . "var issueNo = document.getElementById('issue').value;" . PHP_EOL . PHP_EOL;

	$bigSwitchCode = "switch(issueNo){" . PHP_EOL;
	$issueDetails = getIssueDetails();
	foreach($issueDetails as $issue){
		if ($issue['subissue'] == "0"){
			$caseCode = 'case "' . $issue['ID'] . '":' . PHP_EOL;
			$issueQuestions = getQuestions($issue['ID']);

			foreach ($issueQuestions as $question){
				$nextLine = createValidationCall($question['ID'], $question['type']);
				$caseCode .= $nextLine;
			}
			$caseCode .= "break;" . PHP_EOL;

			$bigSwitchCode .= $caseCode;

		} else {
			$caseCode = 'case "' . $issue['ID'] . '":' . PHP_EOL;
			$caseCode .= "var subIssueNo = document.getElementById('" . $issue['ID'] . "').value;" . PHP_EOL;
			$caseCode .= "switch(subIssueNo){" . PHP_EOL;
			$subIssues = getSubissues($issue['subissue']);
			$caseEcho = 'no';
			foreach ($subIssues as $subissue){
			
				$subCaseCode = 'case "s' . $subissue['ID'] . '":' . PHP_EOL;
				$subQuestions = getSubQuestions($subissue['ID']);
				$validationLines = "";
				foreach ($subQuestions as $subQuestion){
					$nextLine = createValidationCall($subQuestion['ID'], $subQuestion['type']);				
					$validationLines .= $nextLine;
				}
				if ($subissue['probdesc'] == "1") {
					$validationLines .= "if (checkText('description') == false) return false;" . PHP_EOL;
				}
				
				$subCaseCode .= $validationLines . "break;" . PHP_EOL;
				if ($validationLines != ""){
					$caseCode .= $subCaseCode;
					$caseEcho = 'yes';
				}
			}
			$caseCode .= "}" . PHP_EOL . "break;" . PHP_EOL;
			if ($caseEcho == 'yes'){
				$bigSwitchCode .= $caseCode;
			}
		}
	

	}
	$bigSwitchCode .= "}" . PHP_EOL;

	$fileCode .= $bigSwitchCode . "}" . PHP_EOL;
	
	$fileCode .= $endCode;


	$filename = 'js/validation.js';
	writeFile($filename, $fileCode);

}

function createValidationCall($id, $type){

	switch($type){
		case 'input text';
			$line = "if (checkText('q" . $id . "') == false) return false;" . PHP_EOL;
			break;
		case 'radio';
			$line = "if (checkRadio('q" . $id . "') == false) return false;" . PHP_EOL;
			break;
		case 'numeric';
			$line = "if (checkNumeric('q" . $id . "') == false) return false;" . PHP_EOL;
			break;
		case 'email';
			$line = "if (checkEmail('q" . $id . "') == false) return false;" . PHP_EOL;
			break;
		case 'textarea';
			$line = "if (checkText('q" . $id . "') == false) return false;" . PHP_EOL;
			break;
		case 'select';
			$line = "if (checkDropDown('q" . $id . "') == false) return false;" . PHP_EOL;
			break;
		case 'checkbox';
			$line = "if (checkCheckboxes('q" . $id . "') == false) return false;" . PHP_EOL;
			break;
	}
	return($line);

}

function getQuestions($id){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from questions where forissue = :id and required = 1");
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $questions;


}

function getSubQuestions($id){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from questions where subissue = :id and required = 1");
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $questions;


}

function getRequiredQuestionDetails(){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from questions where required = 1 order by questionorder asc");
		$stmt->execute();

		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $questions;


}




//shared functions

function createPlain($item){

	$label = redoAposts($item['label']);
	$code = "<tr><td class='labelText'></td><td><div><label class='description' for='description'>" . $label . "</label></div></td></tr>";

	return $code;
}

function createText($item){

	$label = redoAposts($item['label']);
	$code = "<tr><td class='labelText'><label class='description'>" . $label;
	if ($item['required'] == "1"){
		$code .= " *";
	}
	$code .= "</label></td><td><div><input id='q" . $item['ID'] . "' name='q" . $item['ID'] . "' class='element text large' type='text' maxlength='255'/></div>";

	if ($item['hint'] == "1"){
		$hintText = redoAposts($item['hinttext']);
		$hintCode = addHint($hintText);
		$code .= $hintCode;
	}
	$code .= "</td></tr>";
	global $submitable;
	$submitable = true;
	return $code;
}

function createTextArea($item){

	$label = redoAposts($item['label']);
	$code = "<tr><td class='labelText'><label class='description'>" . $label;
	if ($item['required'] == "1"){
		$code .= " *";
	}
	$code .= "</label></td><td><div><textarea id='q" . $item['ID'] . "' name='q" . $item['ID'] . "' class='element textarea medium'/></textarea></div>";

	if ($item['hint'] == "1"){
		$hintText = redoAposts($item['hinttext']);
		$hintCode = addHint($hintText);
		$code .= $hintCode;
	}
	$code .= "</td></tr>";
	global $submitable;
	$submitable = true;
	return $code;
}


function createNumeric($item){

	$label = redoAposts($item['label']);
	$code = "<tr><td class='labelText'><label class='description'>" . $label;
	if ($item['required'] == "1"){
		$code .= " *";
	}
	$code .= "</label></td><td><div><input id='q" . $item['ID'] . "' name='q" . $item['ID'] . "' class='element text small' type='text' maxlength='255'/></div>";

	if ($item['hint'] == "1"){
		$hintText = redoAposts($item['hinttext']);
		$hintCode = addHint($hintText);
		$code .= $hintCode;
	}
	$code .= "</td></tr>";
	global $submitable;
	$submitable = true;
	return $code;
}

function createSelect($item, $id, $prefix){

	global $selectBits;

	$selectBit = array('case' => $prefix . $id, 'id' => "q" . $item["ID"]);
	array_push($selectBits, $selectBit);
	$options = getOptions($item['ID']);
	$label = redoAposts($item['label']);
	$code = "<tr><td class='labelText'><label class='description'>" . $label;
	if ($item['required'] == "1"){
		$code .= " *";
	}
	$code .= "</label></td><td><div><select name='q" . $item['ID'] . "' class='element select large' id='q" . $item['ID'] . /*"' onchange='" . $function .*/ "'><option selected='selected' value='0'>&nbsp;</option>";


	foreach ($options as $optionItem){
		$code .= "<option value='" . $optionItem['ID'] . "'>" . $optionItem['text'] . "</option>";
	}
	$code .= "</div>";

	if ($item['hint'] == "1"){
		$hintText = redoAposts($item['hinttext']);
		$hintCode = addHint($hintText);
		$code .= $hintCode;
	}
	$code .= "</td></tr>";
	global $submitable;
	$submitable = true;
	return $code;
}

function createRadio($item){

	$label = redoAposts($item['label']);
	$options = getOptions($item['ID']);

	$code = "<tr><td class='labelText'><label class='description'>" . $label;
	if ($item['required'] == "1"){
		$code .= " *";
	}
	$code .= "</label></td><td><div id='" . $item['ID'] . "'>";
	$i = 1;
	foreach ($options as $optionItem){
		$code .= "<input name='q" . $item['ID'] . "Choice' id='q" . $item['ID'] . "-" . $i . "' class='field radio' value='" . $optionItem['text'] . "' type='radio'> " . $optionItem['text'];
	$i++;
	}

	$code .= "</div>";
	if ($item['hint'] == "1"){
		$hintText = redoAposts($item['hinttext']);
		$hintCode = addHint($hintText);
		$code .= $hintCode;
	}
	$code .= "</td></tr>";
	global $submitable;
	$submitable = true;
	return $code;
}

function createCheckbox($item){
	$options = getOptions($item['ID']);

	$label = redoAposts($item['label']);
	$code = "<tr><td class='labelText'><label class='description'>" . $label;
	if ($item['required'] == "1"){
		$code .= " *";
	}

	$code .= "</label></td><td><div>";
	$counter = 1;


	foreach ($options as $optionItem){
		if ($counter == "1"){
			//create checkbox
			$code .= "<label><input type='checkbox' name='q" . $item['ID'] . "[]' value='" . $optionItem['text'] . "'  />" . $optionItem['text'] . "</label>";

			if (strlen($optionItem['text']) > 25){
				$code .= "<br />";
				$counter = 1;
			} else {
				$counter = 2;
			}
		} else {
			if (strlen($optionItem['text']) <= 25){
				$code .= "<label class='secondBox'><input type='checkbox' name='q" . $item['ID'] . "[]' value='" . $optionItem['text'] . "'  />" . $optionItem['text'] . "</label><br />";
				$counter = 1;
			} else {
				$code .= "<br /><label><input type='checkbox' name='q" . $item['ID'] . "[]' value='" . $optionItem['text'] . "'  />" . $optionItem['text'] . "</label><br />\n";
				$counter = 1;
			}
		}
	}
	if($counter == "2"){
		$code .= "<br />";
	}
	$code .= "<label>Other:</label><br><input id='q" . $item['ID'] . "other' name='q" . $item['ID'] . "other' class='element text large' type='text' maxlength='255' value=''/>";
	$code .= "</div>";

	if ($item['hint'] == "1"){
		$hintText = redoAposts($item['hinttext']);
		$hintCode = addHint($hintText);
		$code .= $hintCode;
	}
	$code .= "</td></tr>";
	global $submitable;
	$submitable = true;
	return $code;
}

function addHint($hintText){

	$hintCode = "<p class='instruct hint' id='instruct10'><small>" . $hintText . "</small></p>";
	return $hintCode;
}

function styleSelect($selectBits){
	if (!(empty($selectBits))){

		usort($selectBits, 'compare_case'); 

		$selectStyleCode = "switch(chosenOption){" . PHP_EOL;
		$currentCase = "";
		$i = 1;
		foreach($selectBits as $row){
			if($row['case'] == $currentCase){
				$selectStyleCode .= "jQuery('#" . $row['id'] . "').selectbox();" . PHP_EOL;
			} else {
				if($i != "1"){
					$selectStyleCode .= "break;" . PHP_EOL;
				}
				$selectStyleCode .= "case'" . $row['case'] . "':" . PHP_EOL;
				$selectStyleCode .= "jQuery('#" . $row['id'] . "').selectbox();" . PHP_EOL;
				$currentCase = $row['case'];
			}
		$i++;
		}

	$selectStyleCode .= "break;" . PHP_EOL . "}" . PHP_EOL . PHP_EOL;

	return $selectStyleCode;

	}

}

function compare_case($a, $b) {
	return strnatcmp($a['case'], $b['case']);
} 





function getSubissues($subissue){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from subissues where forissue = :subissue order by listorder asc");
		$stmt->bindParam(':subissue', $subissue);
		$stmt->execute();

		$subissues = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $subissues;
}

function getQuestionDetails($id){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from questions where forissue = :id order by questionorder asc");
		$stmt->bindParam(':id', $id);
		$stmt->execute();

		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $questions;


}

function getOptions($id){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from options where forquestion = :id order by optionorder asc");
		$stmt->bindParam(':id', $id);

		$stmt->execute();

		$options = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $options;


}

function getIssueDetails(){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from issues order by listorder asc");
		$stmt->execute();

		$issueDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $issueDetails;


}

function writeFile($filename, $fileCode){
	$path = $filename;
	$handle = fopen($path, 'w') or exit('could not read file');
	$bytes = fwrite($handle, $fileCode) or exit('could not write to file');
	//echo $bytes . ' written to ' . $path; 
	fclose($handle);
}

function redoAposts($input){

	$newInput = str_replace("&#39;", "'", $input);
	$newInput = str_replace("&quot;", "'", $newInput);

	return $newInput;
}


?>
