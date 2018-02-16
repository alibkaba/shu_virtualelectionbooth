<?php
//CLA DB connection
function CLA(){
	$CLAdsn = "mysql:host=localhost;dbname=djkabau1_CLA";
	$CLAu = "djkabau1_CLAuser";
	$CLAp = "(^QN;DQ*2%Hn";
	$CLAconn = new PDO($CLAdsn, $CLAu, $CLAp);
	try {
		$CLAconn = new PDO($CLAdsn, $CLAu, $CLAp);
		$CLAconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage() . "\n";
	}
	return $CLAconn;
}

//CTF DB connection
function CTF(){
	$CTFdsn = "mysql:host=localhost;dbname=djkabau1_CTF";
	$CTFu = "djkabau1_CTFuser";
	$CTFp = "0%[.s6Qq{@B.";
	$CTFconn = new PDO($CTFdsn, $CTFu, $CTFp);
	try {
		$CTFconn = new PDO($CTFdsn, $CTFu, $CTFp);
		$CTFconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		echo 'Error: ' . $e->getMessage() . "\n";
	}
	return $CTFconn;
}

//Symmetric setup for CLA_To_CTF_Encryption and CLA_To_CTF_Decryption
$key = "this is a secret key";
$cipher = MCRYPT_3DES;
$mode = MCRYPT_MODE_CBC;
$iv_size = mcrypt_get_iv_size($cipher, $mode);
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

//Validating incoming data from main.js
Validate_Ajax_Request();

function Validate_Ajax_Request() {
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
		Validate_action();
	}
}

//Validating action
function Validate_action(){
	if (isset($_POST["action"]) && !empty($_POST["action"])) {
		$action = $_POST["action"];
		Operation($action);
	}
}

function Operation($action){
	switch($action) {
		case "Reset": Reset_PHP();
			break;
		case "Voter_To_CLA": Voter_To_CLA();
			break;
		case "Results": Results();
			break;
	}
}

function Reset_PHP(){
	//Refreshing CLA's asymmetric keys
	//creates privates key
	$CLAPR = openssl_pkey_new(array(
		'private_key_bits' => 1024,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	));
	//save private key file
	openssl_pkey_export_to_file($CLAPR, 'clapr.key');
	//generate public key for private key
	$CLAkeyDetails = openssl_pkey_get_details($CLAPR);
	//save public key file
	file_put_contents('clapu.key', $CLAkeyDetails['key']);
	//free private key
	openssl_free_key($CLAPR);
	
	//Refreshing CTF's asymmetric keys
	$CTFPR = openssl_pkey_new(array(
		'private_key_bits' => 1024,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	));
	openssl_pkey_export_to_file($CTFPR, 'ctfpr.key');
	$CTFkeyDetails = openssl_pkey_get_details($CTFPR);
	file_put_contents('ctfpu.key', $CTFkeyDetails['key']);
	openssl_free_key($CTFPR);

	//Refreshing CLA DB & unit testing
	//Create
	$CLAconn = CLA();
	$Query = 'DROP TABLE IF EXISTS `djkabau1_CLA`.`CLA_LIST` ;
	CREATE TABLE IF NOT EXISTS `djkabau1_CLA`.`CLA_LIST` (
	`Voter_ID` INT NOT NULL,
	`Validation_Number` INT NOT NULL,
	PRIMARY KEY (`Voter_ID`))
	ENGINE = InnoDB;';
	$Statement = $CLAconn->prepare($Query);
	$Statement->execute();

	//Insert
	$Voter_ID = "1";
	$Validation_Number = "1";
	$Query = 'insert into CLA_LIST (Voter_ID, Validation_Number) VALUES (?,?)';
	$Statement = $CLAconn->prepare($Query);
	$Statement->bindParam(1, $Voter_ID, PDO::PARAM_INT);
	$Statement->bindParam(2, $Validation_Number, PDO::PARAM_INT);
	$Statement->execute();

	//Update
	$New_Validation_Number = "2";
	$Query = 'update CLA_LIST set Validation_Number = (?) where Validation_Number = (?)';
	$Statement = $CLAconn->prepare($Query);
	$Statement->bindParam(1, $New_Validation_Number, PDO::PARAM_INT);
	$Statement->bindParam(2, $Validation_Number, PDO::PARAM_INT);
	$Statement->execute();

	//Drop and create
	$Query = 'DROP TABLE IF EXISTS `djkabau1_CLA`.`CLA_LIST` ;
	CREATE TABLE IF NOT EXISTS `djkabau1_CLA`.`CLA_LIST` (
	`Voter_ID` INT NOT NULL,
	`Validation_Number` INT NOT NULL,
	PRIMARY KEY (`Voter_ID`))
	ENGINE = InnoDB;';
	$Statement = $CLAconn->prepare($Query);
	$Statement->execute();
	$CLAconn = null;

	//Refreshing CTF DB & unit testing
	$CTFconn = CTF();
	$Query = 'DROP TABLE IF EXISTS `djkabau1_CTF`.`CTF_LIST` ;
	CREATE TABLE IF NOT EXISTS `djkabau1_CTF`.`CTF_LIST` (
	`Validation_Number` INT NOT NULL,
	`Vote` VARCHAR(45) NULL,
	PRIMARY KEY (`Validation_Number`))
	ENGINE = InnoDB;';
	$Statement = $CTFconn->prepare($Query);
	$Statement->execute();

	$Voter_ID = "1";
	$Validation_Number = "1";
	$Vote = "Team Red";
	$Query = 'insert into CTF_LIST (Validation_Number, Vote) VALUES (?,?)';
	$Statement = $CTFconn->prepare($Query);
	$Statement->bindParam(1, $Validation_Number, PDO::PARAM_INT);
	$Statement->bindParam(2, $Vote, PDO::PARAM_STR, 45);
	$Statement->execute();

	$New_Vote = "Team Blue";
	$Validation_Number = "1";
	$Query = 'update CTF_LIST set Vote = (?) where Validation_Number = (?)';
	$Statement = $CTFconn->prepare($Query);
	$Statement->bindParam(1, $New_Vote, PDO::PARAM_STR, 45);
	$Statement->bindParam(2, $Validation_Number, PDO::PARAM_INT);
	$Statement->execute();

	$Query = 'DROP TABLE IF EXISTS `djkabau1_CTF`.`CTF_LIST` ;
	CREATE TABLE IF NOT EXISTS `djkabau1_CTF`.`CTF_LIST` (
	`Validation_Number` INT NOT NULL,
	`Vote` VARCHAR(45) NULL,
	PRIMARY KEY (`Validation_Number`))
	ENGINE = InnoDB;';
	$Statement = $CTFconn->prepare($Query);
	$Statement->execute();
	$CTFconn = null;

	echo "Reset complete";
}

function Voter_To_CLA(){
	//Creating voter's asymmetric keys 
	Create_V_Keys();
	//Retriving of voter_id value
	$Voter_ID = $_POST['Voter_ID'];
	//Generation a number
	$Signature = Generate_Number();
	//Combining voter_id and signature into 1 variable for compressing and encryption
	$Combined_Data = $Voter_ID . ' ' . $Signature;
	$Data = gzcompress($Combined_Data);
	$Data2 = gzcompress($Signature);
	$Data2 = VPR_Encryption($Data2);
	$Data = CLAPU_Encryption($Data);
	$Data = CLAPR_Decryption($Data);
	$Data2 = VPU_Decryption($Data2);
	$Signature2 = gzuncompress($Data2);
	$Combined_Data = gzuncompress($Data);
	//Decompressing
	list($Voter_ID, $Signature) = explode(' ', $Combined_Data);
	//Comparing numbers
	Compare_Numbers($Signature, $Signature2);
	//Grabs all data from CLA DB
	$CLA_Data = Grab_CLA_Data();
	//Validating to see if voter_id exists
	$Validation_Number = Voter_ID_Check($Voter_ID, $CLA_Data);
	if ($Validation_Number){
		//skips validationg number creation since voter_id already had validationg number
		goto Voter_ID_Exists;
	}
	//Generations validation number
	$Validation_Number = Generate_Validation_Number($CLA_Data);
	//Saves voter_id and validation number
	CLA_Insert($Voter_ID, $Validation_Number);
	//CLA sends validation number to CTF
	CLA_To_CTF($Validation_Number);
	Voter_ID_Exists:
	CLA_To_Voter($Validation_Number);
}

function CLA_To_Voter($Validation_Number){
	$Signature = Generate_Number();
	$Combined_Data = $Validation_Number . ' ' . $Signature;
	$Data = gzcompress($Combined_Data);
	$Data2 = gzcompress($Signature);
	$Data2 = CLAPR_Encryption($Data2);
	$Data = VPU_Encryption($Data);
	$Data = VPR_Decryption($Data);
	$Data2 = CLAPU_Decryption($Data2);
	$Signature2 = gzuncompress($Data2);
	$Combined_Data = gzuncompress($Data);
	list($Validation_Number, $Signature) = explode(' ', $Combined_Data);
	Compare_Numbers($Signature, $Signature2);
	Voter_To_CTF($Validation_Number);
}

function CLA_To_CTF($Validation_Number){
	$Data = gzcompress($Validation_Number);
	$Data = CLA_To_CTF_Encryption($Data);
	$Data = CLA_To_CTF_Decryption($Data);
	$Validation_Number = gzuncompress($Data);
	CTF_Insert($Validation_Number);
}

function Voter_To_CTF($Validation_Number){
	//Retriving of voter's vote value
	$Vote = Vote_Data();
	$Combined_Data = $Validation_Number . ' ' . $Vote . ' ' . $Signature;
	$Data = gzcompress($Combined_Data);
	$Data2 = gzcompress($Signature);
	$Data2 = VPR_Encryption($Data2);
	$Data = CTFPU_Encryption($Data);
	$Data = CTFPR_Decryption($Data);
	$Data2 = VPU_Decryption($Data2);
	$Signature2 = gzuncompress($Data2);
	$Combined_Data = gzuncompress($Data);
	list($Validation_Number, $Vote, $Signature) = explode(' ', $Combined_Data);
	Compare_Numbers($Signature, $Signature2);
	//Grabs all data from CTF DB
	$CTF_Data = Grab_CTF_Data();
	//Validating to see if validation voted
	$Vote_Results = Validation_Vote_Check($CTF_Data, $Validation_Number);
	if ($Vote_Results){
		echo "You've already voted \n";
		goto Voter_Voted;
	}
	//Updates CTF DB with vote
	CTF_Update($Validation_Number, $Vote);
	echo "Thank you for voting \n";
	Voter_Voted:
}

function Results(){
	$CTF_Data = Grab_CTF_Data();
	$Red = array();
	$Blue = array();
	foreach($CTF_Data as $result) {
		$DB_Vote = $result['Vote'];
		if ($DB_Vote == "Red"){
			$Red[] = $result['Validation_Number'];
		}
		else{
			$Blue[] = $result['Validation_Number'];
		}
	}
	echo "Team Red total vote count is " . sizeof($Red) . "\n";
	for ($i=0; $i < count($Red); ++$i){
		echo $Red[$i] . "\n";
	}

	echo "Team Blue total vote count is " . sizeof($Blue) . "\n";
	for ($i=0; $i < count($Blue); ++$i){
		echo $Blue[$i] . "\n";
	}
}

function Create_V_Keys(){
	$VPR = openssl_pkey_new(array(
		'private_key_bits' => 1024,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	));
	openssl_pkey_export_to_file($VPR, 'vpr.key');
	$VkeyDetails = openssl_pkey_get_details($VPR);
	file_put_contents('vpu.key', $VkeyDetails['key']);
	openssl_free_key($VPR);

}

function Vote_Data(){
	$Vote = $_POST['Vote'];
	return $Vote;
}

function Grab_CLA_Data(){
	$CLAconn = CLA();
    $Query = 'select * from CLA_LIST';
	$Statement = $CLAconn->prepare($Query);
	$Statement->execute();
	$Data = $Statement->fetchAll();
	return $Data;
	$CLAconn = null;
}

function Grab_CTF_Data(){
	$CTFconn = CTF();
    $Query = 'select * from CTF_LIST';
	$Statement = $CTFconn->prepare($Query);
	$Statement->execute();
	$Data = $Statement->fetchAll();
	return $Data;
	$CTFconn = null;
}

function Voter_ID_Check($Voter_ID, $CLA_Data){
	foreach($CLA_Data as $result) {
		$DB_Voter_ID = $result['Voter_ID'];
		if ($DB_Voter_ID == $Voter_ID){
			$Validation_Number = $result['Validation_Number'];
			return $Validation_Number;
			break;
		}
	}
}

function Validation_Vote_Check($CTF_Data, $Validation_Number){
	foreach($CTF_Data as $result) {
		$DB_Validation_Number = $result['Validation_Number'];
		if ($DB_Validation_Number == $Validation_Number){
			$Vote = $result['Vote'];
			return $Vote;
			break;
		}
	}
}

function Generate_Validation_Number($CLA_Data){
	loop:
	$Generated_Number = Generate_Number();
	foreach($CLA_Data as $result) {
		$Validation_Number = $result['Validation_Number'];
		if ($Validation_Number == $Generated_Number){
			goto loop;
		}
	}
	return $Generated_Number;
}

function Generate_Number(){
	$Data = rand(1, 2147483647);
	return $Data;
}

function Compare_Numbers($Signature, $Signature2){
	if ($Signature != $Signature2){
		throw new Exception("Your signature numbers arean't matching!");
	}
}

function CLA_Insert($Voter_ID, $Validation_Number){
	$CLAconn = CLA();
	$Query = 'insert into CLA_LIST (Voter_ID, Validation_Number) VALUES (?,?)';
	$Statement = $CLAconn->prepare($Query);
	$Statement->bindParam(1, $Voter_ID, PDO::PARAM_INT);
	$Statement->bindParam(2, $Validation_Number, PDO::PARAM_INT);
	$Statement->execute();
	$CLAconn = null;
}

function CTF_Insert($Validation_Number){
	$CTFconn = CTF();
	$Query = 'insert into CTF_LIST (Validation_Number) VALUES (?)';
	$Statement = $CTFconn->prepare($Query);
	$Statement->bindParam(1, $Validation_Number, PDO::PARAM_INT);
	$Statement->execute();
	$CTFconn = null;
}

function CTF_Update($Validation_Number, $Vote){
	$CTFconn = CTF();
	$Query = 'update CTF_LIST set Vote = (?) where Validation_Number = (?)';
	$Statement = $CTFconn->prepare($Query);
	$Statement->bindParam(1, $Vote, PDO::PARAM_STR, 45);
	$Statement->bindParam(2, $Validation_Number, PDO::PARAM_INT);
	$Statement->execute();
	$CTFconn = null;
}

//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////
//Encryptions
function VPR_Encryption($Data){
	if (!$VPRKey = openssl_pkey_get_private('file://vpr.key'))
	{
		die('cannot find vpr key');
	}
	if (!openssl_private_encrypt($Data, $New_Data, $VPRKey))
	{
		die('failed to encrypt with vpr key');
	}
	openssl_free_key($VPRKey);
	return $New_Data;
}

function CLAPR_Encryption($Data){
	if (!$CLAPRKey = openssl_pkey_get_private('file://clapr.key'))
	{
		die('cannot find clapr key');
	}
	if (!openssl_private_encrypt($Data, $New_Data, $CLAPRKey))
	{
		die('failed to encrypt with clapr key');
	}
	openssl_free_key($CLAPRKey);
	return $New_Data;
}

function CTFPR_Encryption($Data){
	if (!$CTFPRKey = openssl_pkey_get_private('file://ctfpr.key'))
	{
		die('cannot find ctfpr key');
	}
	if (!openssl_private_encrypt($Data, $New_Data, $CTFPRKey))
	{
		die('failed to encrypt with ctfpr key');
	}
	openssl_free_key($CTFPRKey);
	return $New_Data;
}

function VPU_Encryption($Data){
	if (!$VPUKey = openssl_pkey_get_public('file://vpu.key'))
	{
		die('cannot find vpu key');
	}
	if (!openssl_public_encrypt($Data, $New_Data, $VPUKey))
	{
		die('failed to encrypt with vpu key');
	}
	openssl_free_key($VPUKey);
	return $New_Data;
}

function CLAPU_Encryption($Data){
	if (!$CLAPUKey = openssl_pkey_get_public('file://clapu.key'))
	{
		die('cannot find clapu key');
	}
	if (!openssl_public_encrypt($Data, $New_Data, $CLAPUKey))
	{
		die('failed to encrypt with clapu key');
		while ($msg = openssl_error_string()){
			echo $msg . "\n";
		}
	}
	openssl_free_key($CLAPUKey);
	return $New_Data;
}

function CTFPU_Encryption($Data){
	if (!$CTFPUKey = openssl_pkey_get_public('file://ctfpu.key'))
	{
		die('cannot find ctfpu key');
	}
	if (!openssl_public_encrypt($Data, $New_Data, $CTFPUKey))
	{
		die('failed to encrypt with ctfpu key');
	}
	openssl_free_key($CTFPUKey);
	return $New_Data;
}

//Decryption
function VPR_Decryption($Data){
	if (!$VPRKey = openssl_pkey_get_private('file://vpr.key'))
	{
		die('cannot find vpr key');
	}
	if (!openssl_private_decrypt($Data, $New_Data, $VPRKey))
	{
		die('failed to decrypt with vpr key');
	}
	openssl_free_key($VPRKey);
	return $New_Data;
}

function CLAPR_Decryption($Data){
	if (!$CLAPRKey = openssl_pkey_get_private('file://clapr.key'))
	{
		die('cannot find clapr key');
	}
	if (!openssl_private_decrypt($Data, $New_Data, $CLAPRKey))
	{
		die('failed to decrypt with clapr key');
	}
	openssl_free_key($CLAPRKey);
	return $New_Data;
}

function CTFPR_Decryption($Data){
	if (!$CTFPRKey = openssl_pkey_get_private('file://ctfpr.key'))
	{
		die('cannot find ctfpr key');
	}
	if (!openssl_private_decrypt($Data, $New_Data, $CTFPRKey))
	{
		die('failed to decrypt with ctfpr key');
	}
	openssl_free_key($CTFPRKey);
	return $New_Data;
}

function VPU_Decryption($Data){
	if (!$VPUKey = openssl_pkey_get_public('file://vpu.key'))
	{
		die('cannot find vpu key');
	}
	if (!openssl_public_decrypt($Data, $New_Data, $VPUKey))
	{
		die('failed to decrypt with vpu key');
	}
	openssl_free_key($VPUKey);
	return $New_Data;
}

function CLAPU_Decryption($Data){
	if (!$CLAPUKey = openssl_pkey_get_public('file://clapu.key'))
	{
		die('cannot find clapu key');
	}
	if (!openssl_public_decrypt($Data, $New_Data, $CLAPUKey))
	{
		die('failed to decrypt with clapu key');
	}
	openssl_free_key($CLAPUKey);
	return $New_Data;
}

function CTFPU_Decryption($Data){
	if (!$CTFPUKey = openssl_pkey_get_public('file://ctfpu.key'))
	{
		die('cannot find ctfpu key');
	}
	if (!openssl_public_decrypt($Data, $New_Data, $CTFPUKey))
	{
		die('failed to decrypt with ctfpu key');
	}
	openssl_free_key($CTFPUKey);
	return $New_Data;
}

function CLA_To_CTF_Encryption($Data){
	global $key, $cipher, $mode, $iv_size, $iv;
	$New_Data = mcrypt_encrypt ($cipher, $key, $Data, $mode, $iv);
	$New_Data = $iv . $New_Data;
	$New_Data = base64_encode($New_Data);
	return $New_Data;
}

function CLA_To_CTF_Decryption($Data){
	global $key, $cipher, $mode, $iv_size, $iv;
	$Data = base64_decode($Data);
	$iv_dec = substr($Data, 0, $iv_size);
	$Data = substr($Data, $iv_size);
	$New_Data = mcrypt_decrypt ($cipher, $key, $Data, $mode, $iv_dec);
	$New_Data = rtrim($New_Data, "\0");
	return $New_Data;
}
?>
