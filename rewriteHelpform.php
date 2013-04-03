<?php


function rewriteHelpform(){
	$nameList = getListDetails();
	$path = 'helpform.html';


	//read file into array using file command, split using explode on \n

	$file = file($path) or exit('Could not read file');


	//remove select list options
	foreach ($file as $key => $value){
		$isOption = strpos($value, 'option value');

		if ($isOption !== false){
			unset($file[$key]);
		}
	}

	$file = implode("", $file); 

	$handle = fopen($path, 'w') or exit('could not read file');
	$bytes = fwrite($handle, $file) or exit('could not write to file');
	//echo $bytes . ' written to ' . $path; 
	fclose($handle);


	//add in options from database

	$file = file($path) or exit('Could not read file');
	$f=fopen($path,"w") or die("couldn't open $file");

	foreach ($file as $key => $value){
		$isSelect = strpos($value, 'select name');
		if ($isSelect !== false){

			fwrite($f,$value);
			fwrite($f, '<option value="0" selected="selected">&nbsp;</option>' . PHP_EOL);
			foreach($nameList as $row){
				$option = '<option value="' . $row['ID'] . '" ';

				$option .= '>';
				$option .= $row['name'] . '</option>' . PHP_EOL;

				fwrite($f,$option);
			}
		} else {

		fwrite($f,$value);
		}
	}
	fclose($f);
}



function getListDetails(){
	try{
		$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->prepare("select ID, name from issues order by listorder asc");
		$stmt->execute();

		$issue = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$pdo = null;

	}
	catch(PDOException $e){
       		echo $e;
      	}

	return $issue;


}



?>