<?php
//error_reporting(6135);
include "config/config.php";

if (!isset($_GET['issue'])){
	$issue = 'undefined';
} else {
	$issue = $_GET['issue'];
}


if ((!(is_Numeric($issue))) || (!($issue >= 2))){
	$issue = 0;
}

if ($issue == 2){
	editIssues();	
}
else if ($issue >= 4){
	$subissueNum = $issue - 3;
	editSubissues($subissueNum);

}
echo "<br /><div id='delList'></div><li id='buttons'><input id='removeButton' type='button' value='Remove Item(s)' onClick='removeItems()'>\n<input id='addButton' type='button' value='Add Item' onClick='addItem()'>\n<input id='updateListButton' type='submit' value='Update List' ></li>";

function editIssues(){

	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from issues order by listorder asc");

		$stmt->execute();

		$issueList = $stmt->fetchAll(PDO::FETCH_ASSOC);

		displayList($issueList);


		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}
}

function editSubissues($subissueNum){

	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select * from subissues where forissue = :subissueNum order by listorder asc");
		$stmt->bindParam(':subissueNum', $subissueNum);

		$stmt->execute();

		$issueList = $stmt->fetchAll(PDO::FETCH_ASSOC);

		displayList($issueList);


		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}
}


function displayList($issueList){
$i=1;
	echo "<br><div><div id='del' class='heading'>Remove</div><div id='lab' class='heading'>Label</div>";

	global $issue;
	if ($issue == 2){

		echo "<div id='subiss' class='heading'>SubIssue</div><div id='subissQ' class='heading'>Subissue Question</div></div>";
	}

	echo "<ul id='sortable'>";	
	foreach($issueList as $row){

		$id=$row['ID'];
		$name=$row['name'];
		$num=$row['ID'];

		echo "<li class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><div class='item' id='item" . $i . "'><input type='hidden' name='id" . $i . "' value='" . $id . "'><input type='checkbox' name='del" . $i . "' id='del" . $i . "' value='" . $id . "'><input id='" . $i . "' type='text' size='25' value='" . $name . "' name='" . $i . "'/>";

		//global $issue;
		if ($issue == 2){
			$subissue = $row['subissue'];
			$questionText=$row['selectText'];
			$checkId = "sub" . $i;
			echo "<input type='checkbox' onClick='addRemoveQuestion(\"" . $i . "\")' name='" . $checkId . "' id='" . $checkId . "' value='";

 
			if ($subissue != 0){
				echo $subissue . "' checked='checked'";
			} else {
				echo "update'";
			}

			echo ">";
			if ($subissue != 0){
				$qstnId = "qstn" . $i;
				echo "<input type='text' name='" . $qstnId . "' id='" . $qstnId . "' value='" . $questionText . "'>";
			}
		}

	echo "</div></li>";
	$i++;
	}
	echo "</ul>";
	echo "<input type='hidden' value='" . ($i-1) . "' name='origNumIssues'>";
}



?>