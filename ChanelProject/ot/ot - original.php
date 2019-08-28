<?php
/****************************************Database Queries*********************************************/
$hosting = strpos($_SERVER['REMOTE_ADDR'],'192.168.0.') !== false ? '192.168.0.59:83' : '211.24.110.3:83';
function getdb(){
    //$host = $_SERVER['REMOTE_ADDR'] == '211.24.110.3:81' ? '192.168.0.58:81' : '211.24.110.3:81'; // for local machine
    //$host = '211.24.110.3:81';// working in cpanel
    $host = 'localhost';
	$username = "root";
    $password = "N3xpro2900";
    $db = "timetracx";

    $conn = mysqli_connect($host , $username, $password, $db);
    if (!$conn){
        die(mysqli_connect_error()." in ".$host);
    }
    return $conn;
}

function db_close($con){
    return mysqli_close($con);
}

function update_ot($con,$id,$code,$name,$ot1,$ot2,$ot3,$rate){
	$Sql = "UPDATE con_ot_name SET code='".$code."',name='".$name."',normal_ot='".$ot1."',weekend_ot='".$ot2."',holiday_ot='".$ot3."',rate='".$rate."' WHERE id LIKE '".$id."' ";

    if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function insert_ot($con,$code,$name,$ot1,$ot2,$ot3,$rate){
	$Sql = "INSERT INTO con_ot_name (code,name,normal_ot,weekend_ot,holiday_ot,8_hours,rate) VALUES ('".$code."','".$name."','".$ot1."','".$ot2."','".$ot3."','".$rate."') ";

    if (mysqli_query($con, $Sql)) {
		$id = mysqli_insert_id($con);
		return $id;
    } 
	return FALSE;
}

function insert_ot_user($con, $ot_id, $data){
	$Sql = "INSERT INTO con_ot_user (ot_id, userid) VALUES ";
	
	$values = array();
    foreach($data as $row){
        $userid = $row['userid'];
        $values[] = "('".$ot_id."', '".$usesrid."')";
    }

    $Sql .= implode(',', $values);
	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function insert_ot_user1($con, $ot_id, $userid){
	$Sql = "INSERT INTO con_ot_user (ot_id, userid) VALUES ('".$ot_id."','".$userid."')";
	
	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function delete_ot($con, $id){
	$Sql = "DELETE FROM con_ot_name WHERE id LIKE '".$id."'";
	
	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function delete_ot_user($con, $id){
	$Sql = "DELETE FROM con_ot_user WHERE ot_id LIKE '".$id."' ";

	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function delete_ot_user1($con, $id, $userid){
	$Sql = "DELETE FROM con_ot_user WHERE ot_id LIKE '".$id."' AND userid LIKE '".$userid."' ";

	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function check_ot_user($con, $id){
	$Sql = "SELECT * FROM con_ot_user
			WHERE ot_id LIKE '".$id."'";
            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
		return TRUE;
    } 
	return FALSE;
}

function check_ot($con, $id){
	$Sql = "SELECT code FROM con_ot_name
			WHERE code LIKE '".$id."'";
            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
		return TRUE;
    } 
	return FALSE;
}
/*************************************************************************************************************/

session_start();
$con = getdb();
/******** Operation Starts Here ********/
if(isset($_POST['save'])){
	switch ($_POST['ot_type']){
		case 'holiday':
			$ot1 = 0;
			$ot2 = 0;
			$ot3 = 1;
			break;
		case 'weekend':
			$ot1 = 0;
			$ot2 = 1;
			$ot3 = 0;
			break;
		default:
			$ot1 = 1;
			$ot2 = 0;
			$ot3 = 0;
			break;
	}
	if(!check_ot($con,$_POST['code']) && $id = insert_ot($con,$_POST['code'],$_POST['name'],$ot1,$ot2,$ot3,$_POST['rate'])){
		$_SESSION['msg'] = 'OT: '.$_POST['name'].' successfully created!';
	}else{
		check_ot($con,$_POST['code']) !== false ? $_SESSION['msg'] = 'Error: Cannot reuse existing code!' : $_SESSION['msg'] = 'Error inserting into database!';
	}
}else if(isset($_POST['edit'])){
	switch ($_POST['ot_type']){
				case 'holiday':
			$ot1 = 0;
			$ot2 = 0;
			$ot3 = 1;
			break;
		case 'weekend':
			$ot1 = 0;
			$ot2 = 1;
			$ot3 = 0;
			break;
		default:
			$ot1 = 1;
			$ot2 = 0;
			$ot3 = 0;
			break;
	}
	if(update_ot($con,$_POST['id'],$_POST['code'],$_POST['name'],$ot1,$ot2,$ot3,$_POST['rate'])){
		$_SESSION['msg'] = 'OT: '.$_POST['name'].' successfully updated!';
	}else{
		$_SESSION['msg'] = 'Error to update the OT: '.$_POST['name'].' details!';
	}
}else if(isset($_POST['delete'])){	
	if(check_ot_user($con,$_POST['id'])){
		$_SESSION['msg'] = 'Error: This OT is tied up with another user, please remove from all users!';
	}else{
		if(delete_ot($con,$_POST['id']) && delete_ot_user($con,$_POST['id'])){
			$_SESSION['msg'] = 'OT successfully deleted!';
		}else{
			$_SESSION['msg'] = 'Error: Unable to delete!';
		}
	}
}else if(isset($_POST['ot_id']) && insert_ot_user1($con,$_POST['id'], $_POST['userid'])){
}else if(!isset($_POST['ot_id']) && delete_ot_user1($con,$_POST['id'], $_POST['userid'])){
}
db_close($con);
header("Location:http://".$hosting."/ot");
?>