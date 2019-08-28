<?php
/****************************************Database Queries*********************************************/
$hosting = strpos($_SERVER['REMOTE_ADDR'],'192.168.0.') !== false ? '192.168.0.58:81' : '211.24.110.3:83';
function getdb(){
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

function get_ot_report($con,$date1,$date2,$users){
	
	$Sql = "SELECT u.userid, u.badgenumber, u.name as name, u.lastname, u.ParentName, u.DeptName, DATE_FORMAT(a.AttDate, '%Y-%m-%d') AS AttDate, s.SchName, DATE_FORMAT(a.StartTime,'%H:%i') AS ClockInTime, DATE_FORMAT(a.EndTime,'%H:%i') AS ClockOutTime, TIME_FORMAT(SEC_TO_TIME(a.total_work_time*60),'%H:%i') AS total_work_time, TIME_FORMAT(SEC_TO_TIME(a.o_normal_ot),'%H:%i') AS o_normal_ot, TIME_FORMAT(SEC_TO_TIME(a.o_weekend_ot),'%H:%i') AS o_weekend_ot , TIME_FORMAT(SEC_TO_TIME(a.o_holiday_ot),'%H:%i') AS o_holiday_ot FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, NAME, lastname, DeptID, DeptName, ParentName FROM userinfo ui
				LEFT JOIN (SELECT t1.DeptID AS DeptID, t1.DeptName AS DeptName, (CASE 
				WHEN t4.DeptName IS NOT NULL THEN t4.DeptName 
				WHEN t3.DeptName IS NOT NULL THEN t3.DeptName 
				WHEN t2.DeptName IS NOT NULL THEN t2.DeptName 
				ELSE t1.DeptName END ) AS ParentName
					FROM departments AS t1
					LEFT JOIN departments AS t2 ON t2.DeptID = t1.supdeptid
					LEFT JOIN departments AS t3 ON t3.DeptID = t2.supdeptid
					LEFT JOIN departments AS t4 ON t4.DeptID = t3.supdeptid
				) d ON d.DeptID = ui.defaultdeptid
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT SchClassID, SchName FROM schclass) s ON s.SchClassID = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND u.userid IN (".$users.") AND (a.o_normal_ot > 0 OR a.o_weekend_ot > 0 OR a.o_holiday_ot > 0) ORDER BY u.badgenumber, a.AttDate ASC";

            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function get_ot_approver($con, $date, $id){
	$Sql = "SELECT emp_id FROM att_overtime 
			WHERE emp_id LIKE '".$id."' AND starttime LIKE '".$date."%' AND audit_status = '2'";  
    
	$result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
		return TRUE;
    } 
	return FALSE;
}


/*************************************************************************************************************/



/******** Operation Starts Here ********/
if(isset($_POST['csv'])){
	$con = getdb();
	$ot_title = array('Personnel No','First Name','Last Name','Company','Department','Date','Timetable Name','Check In Time','Check out Time','Total Time Worked','Normal OT','Weekend OT','Holiday OT','Approve Status');

	$date1 = date('Y-m-d',strtotime($_POST['start']));
	$date2 = date('Y-m-d',strtotime($_POST['end']));
	$users = $_POST['users'];

	header('Content-Type: text/csv; charset=utf-8');  
	header('Content-Disposition: attachment; filename=Overtime_'.$date1.'_'.$date2.'.csv');
	$output = fopen("php://output", "w");  
	
	/***************************User Data****************************************/

	if($ots = get_ot_report($con,$date1,$date2,$users)){
		fputs($output, implode($ot_title, ',')."\n");
		foreach($ots as $all) { 
			$approver = get_ot_approver($con,$all['AttDate'],$all['userid']) ? 'Approved' : 'Auto OT';
			$parentName = ucwords(strtolower($all['ParentName']));
			$words = explode(" ", $parentName);
			$parentCode = "";

			foreach ($words as $w) {
				$parentCode .= $w[0];
			}
					
			$normal_GPSB_Rest_OT = $parentCode == 'GPSB' && $all['o_weekend_ot'] >= 12 ? '14:00' : $all['o_weekend_ot'];
					
			$row = array();
			array_push($row,$all['badgenumber'],$all['name'],$all['lastname'],$all['ParentName'],$all['DeptName'],$all['AttDate'],$all['SchName'],$all['ClockInTime'],$all['ClockOutTime'],$all['total_work_time'],$all['o_normal_ot'],$normal_GPSB_Rest_OT,$all['o_holiday_ot'],$approver);
			$row = str_replace('"', '', $row);
			fputs($output, implode($row, ',')."\n");
		}
	}else{
		header("Location:http://".$hosting."/ot/ot_report.php");
	}
	fclose($output);
	db_close($con);
}else{
	header("Location:http://".$hosting."/ot/ot_report.php");
}
?>