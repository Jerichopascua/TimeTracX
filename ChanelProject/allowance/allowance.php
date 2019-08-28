<?php
/****************************************Database Queries*********************************************/
$hosting = strpos($_SERVER['REMOTE_ADDR'],'192.168.0.') !== false ? '192.168.0.58:81' : '211.24.110.3:81';
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

function update_allowance($con, $id, $name){
	$Sql = "UPDATE con_allowance SET name='".$name."' WHERE id LIKE '".$id."' ";

    if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}


function insert_allowance($con,$data){
	$Sql = "INSERT INTO con_allowance (name) VALUES ('".$data."') ";

    if (mysqli_query($con, $Sql)) {
		$id = mysqli_insert_id($con);
		return $id;
    } 
	return FALSE;
}

function insert_allowance_sch($con, $allowance_id, $data){
	$Sql = "INSERT INTO con_allowance_sch (allowance_id, schid) VALUES ";
	
	$values = array();
    foreach($data as $row){
        $schid = $row['timetable'];
        $values[] = "('".$allowance_id."', '".$schid."')";
    }

    $Sql .= implode(',', $values);
	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function insert_allowance_sch1($con, $allowance_id, $schid){
	$Sql = "INSERT INTO con_allowance_sch (allowance_id, schid) VALUES ('".$allowance_id."','".$schid."')";
	
	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function delete_allowance($con, $id){
	$Sql = "DELETE FROM con_allowance WHERE id LIKE '".$id."'";
	
	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function delete_allowance_sch($con, $id){
	$Sql = "DELETE FROM con_allowance_sch WHERE allowance_id LIKE '".$id."' ";

	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function delete_allowance_sch1($con, $id, $schid){
	$Sql = "DELETE FROM con_allowance_sch WHERE allowance_id LIKE '".$id."' AND schid LIKE '".$schid."' ";

	if (mysqli_query($con, $Sql)) {
		return TRUE;
    } 
	return FALSE;
}

function check_allowance_sch($con, $id){
	$Sql = "SELECT * FROM con_allowance_sch
			WHERE allowance_id LIKE '".$id."'";
            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
		return TRUE;
    } 
	return FALSE;
}

function get_allowance_report($data){
	$Sql = "SELECT a.userid, u.badgenumber, u.name, u.lastname, a.AttDate,s.SchName, a.Symbol, a.Late, a.Early,a.break_time_real, a.break_late, a.StartTime, a.EndTime, a.ClockInTime, a.ClockOutTime, a.total_work_time, a.o_normal_ot, a.o_weekend_ot, a.o_holiday_ot, u.area_id FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, NAME, lastname, area_id FROM userinfo ui
			LEFT JOIN (SELECT employee_id, area_id FROM userinfo_attarea ) ua ON ua.employee_id = ui.userid 
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT SchClassID, SchName FROM schclass) s ON s.SchClassID = a.SchId
            WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' ORDER BY s.SchName,u.badgenumber ASC";
            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}
/*************************************************************************************************************/

session_start();
$con = getdb();
/******** Operation Starts Here ********/
if(isset($_POST['save'])){
	if($id = insert_allowance($con,$_POST['name'])){
		if(!empty($_POST['timetable'])){
			if(!insert_allowance_sch($con, $id, $_POST['timetable'])){
				$_SESSION['msg'] = 'Error pointing to timetable!';
			}
		}
		$_SESSION['msg'] = 'Allowance successfully created!';
	}else{
		$_SESSION['msg'] = 'Error inserting into database!';
	}
}else if(isset($_POST['edit'])){
	if(update_allowance($con,$_POST['id'],$_POST['name'])){
		$_SESSION['msg'] = 'Allowance successfully updated!';
	}else{
		$_SESSION['msg'] = 'Error to update the allowance name!';
	}
}else if(isset($_POST['delete'])){	
	if(check_allowance_sch($con,$_POST['id'])){
		$_SESSION['msg'] = 'Error: This allowance is tied up with another timetable, please remove from all timetables!';
	}else{
		if(delete_allowance($con,$_POST['id']) && delete_allowance_sch($con,$_POST['id'])){
			$_SESSION['msg'] = 'Allowance successfully deleted!';
		}else{
			$_SESSION['msg'] = 'Error: Unable to delete!';
		}
	}
}else if(isset($_POST['timetable_id']) && insert_allowance_sch1($con,$_POST['id'], $_POST['schid'])){
}else if(!isset($_POST['timetable_id']) && delete_allowance_sch1($con,$_POST['id'], $_POST['schid'])){
}
db_close($con);
header("Location:http://".$hosting."/allowance");
?>