<?php
require_once "Excel/PHPExcel.php";

/****************************************Database Queries*********************************************/
$hosting = strpos($_SERVER['REMOTE_ADDR'],'192.168.0.') !== false ? '192.168.0.58:81' : '211.24.110.3:81';
function getdb(){
    //$host = $_SERVER['REMOTE_ADDR'] == '211.24.110.3:81' ? '192.168.0.58:81' : '211.24.110.3:81'; // for local machine
    //$host = '211.24.110.3:81';// working in cpanel
    // $host = 'localhost';
	// $username = "root";
    // $password = "N3xpro2900";
	// $db = "timetracx";
	
	$host = 'localhost';
	$username = "sa";
    $password = "sapassword24";
    $db = "zkteco_biotime";	

    $conn = mysqli_connect($host , $username, $password, $db);
    if (!$conn){
        die(mysqli_connect_error()." in ".$host);
    }
    return $conn;
}

function db_close($con){
    return mysqli_close($con);
}

function getAreas($con){
	
    $Sql = "SELECT id,areaname FROM personnel_area WHERE areaname NOT LIKE 'Total'";
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function getTotalTimeCards($con, $area, $date1, $date2){
	   
    /*$Sql = "SELECT a.userid, u.badgenumber, u.name, u.lastname, a.AttDate,s.SchName, a.Symbol, a.Late, a.Early,a.break_time_real, a.break_late, a.StartTime, a.EndTime, a.ClockInTime, a.ClockOutTime, a.total_work_time, m.data_index, m.in_time, m.out_time FROM attshifts a  
            LEFT JOIN (SELECT userid, badgenumber, NAME, lastname, area_id FROM userinfo ui
            LEFT JOIN (SELECT employee_id, area_id FROM userinfo_attarea ) ua ON ua.employee_id = ui.userid 
            ) u ON u.userid = a.userid 
            left join (SELECT emp_id, att_date, data_index, in_time, out_time FROM att_multipletransaction WHERE data_type LIKE '2' AND att_date LIKE '".$date."%') m ON m.emp_id = u.userid 
            LEFT JOIN (SELECT SchClassID, SchName FROM schclass) s ON s.SchClassID = a.SchId
            WHERE a.AttDate LIKE '".$date."%' AND u.area_id LIKE '".$area."' ORDER BY s.SchName,u.badgenumber ASC";*/

	$Sql = "SELECT a.userid, u.badgenumber, u.email, SUM(a.o_normal_ot) AS o_normal_ot, SUM(a.o_weekend_ot) AS o_weekend_ot, SUM(a.o_holiday_ot) AS o_holiday_ot, COUNT(s.allowance_id) AS allowance_id  FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, email FROM userinfo) u ON u.userid = a.userid 
			LEFT JOIN (SELECT allowance_id, schid FROM con_allowance_sch) s ON s.schid = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND  a.Symbol LIKE '%P%' AND allowance_id > 0
			GROUP BY u.badgenumber ORDER BY u.badgenumber ASC";
            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function getTotalTimeCards_ot($con, $area, $date1, $date2){

	$Sql = "SELECT DISTINCT a.userid, u.badgenumber, u.email, a.AttDate, a.o_normal_ot, a.o_weekend_ot, a.o_holiday_ot FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, email FROM userinfo) u ON u.userid = a.userid 
            WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND  (a.o_normal_ot > 0 OR a.o_weekend_ot > 0 OR a.o_holiday_ot > 0) ORDER BY u.badgenumber, a.AttDate ASC";
            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function getTotalTimeCards_leave($con, $area, $date1, $date2){
	$Sql = "SELECT DISTINCT a.userid, u.badgenumber, a.StartSpecDay, a.EndSpecDay, l.LeaveName, l.ReportSymbol, l.MinUnit FROM user_speday a  
			LEFT JOIN (SELECT userid, badgenumber FROM userinfo) u ON u.userid = a.userid 
			LEFT JOIN (SELECT LeaveID, LeaveName, ReportSymbol, MinUnit FROM leaveclass) l ON l.LeaveID = a.`DateID` 
			WHERE a.StartSpecDay >= '".$date1."%' AND a.StartSpecDay <= '".$date2."%' AND a.EndSpecDay >= '".$date1."%' AND a.EndSpecDay <= '".$date2."%'  AND a.audit_status = '2' ORDER BY u.badgenumber, a.StartSpecDay ASC";
					
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function getTotalTimeCards_wages($con, $area, $date1, $date2){
	$Sql = "SELECT a.userid, u.badgenumber, a.AttDate, a.total_work_time FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, area_id, areaname FROM userinfo ui
				LEFT JOIN (SELECT employee_id, area_id, areaname FROM userinfo_attarea att
					LEFT JOIN (SELECT id,areaname FROM personnel_area) p ON p.id = att.area_id
				) ua ON ua.employee_id = ui.userid 
			) u ON u.userid = a.userid
            WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND u.areaname LIKE '%APP%' AND a.Symbol LIKE '%P%' ORDER BY u.badgenumber, a.AttDate ASC";
					
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function getTotalTimeCards_deduction($con, $area, $date1, $date2){
	// this one only usable for total time card to fix error in total time card in total work hour
	/*$Sql = "SELECT DISTINCT a.userid, u.badgenumber, a.AttDate, a.Late, a.break_late, s.is_consider_early FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber FROM userinfo) u ON u.userid = a.userid 
			LEFT JOIN (SELECT schclass_id, breaktime_id, is_consider_early FROM schclass_break_times sb
			LEFT JOIN (SELECT break_code, is_consider_early FROM att_breaktime) ab ON ab.break_code = sb.breaktime_id
			) s ON s.schclass_id = a.SchId
            WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND a.Late > 0  ORDER BY u.badgenumber, a.AttDate ASC";
    */
	$Sql = "SELECT DISTINCT a.userid, u.badgenumber, a.AttDate, a.Late, a.break_late FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber FROM userinfo) u ON u.userid = a.userid 
            WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND a.Late > 0  ORDER BY u.badgenumber, a.AttDate ASC";
    
	
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function getBreakTimes($con,$condition){
	
    $Sql = "SELECT break_name, start_time, end_time FROM `att_breaktime` WHERE break_name LIKE '%".$condition."%' ORDER BY break_name ASC";

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



/******** Operation Starts Here ********/
if($post_type = $_POST['import']){

	session_start();
							
	$title = array('Employee Email','Salary','Overtime Hours(1.5X)','Overtime Hours(2.0X)','Overtime Hours(3.0X)','Allowance General','Allowance Meal','Allowance Transport','Allowance Phone','Claims','Commission','Bonus','Allowance Petro','Allowance Parking','Allowance Incentive','Allowance Laudry','Allowance Cash Handling','Wage Arrears','Allowance Others');
	$con = getdb();

	$area = null; // default no area
	$date1 = date('Y-m-d',strtotime($_POST['date1']));
	$date2 = date('Y-m-d',strtotime($_POST['date2']));
	/***************************User Data****************************************/
	if($user_data = getTotalTimeCards($con,$area,$date1,$date2)){
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()
				->setTitle('Imported Data');
		$objPHPExcel->getActiveSheet()
                            ->setCellValue('A1', $title[0])
                            ->setCellValue('B1', $title[1])
                            ->setCellValue('C1', $title[2])
                            ->setCellValue('D1', $title[3])
                            ->setCellValue('E1', $title[4])
                            ->setCellValue('F1', $title[5])
                            ->setCellValue('G1', $title[6])
                            ->setCellValue('H1', $title[7])
                            ->setCellValue('I1', $title[8])
							->setCellValue('J1', $title[9])
                            ->setCellValue('K1', $title[10])
                            ->setCellValue('L1', $title[11])
                            ->setCellValue('M1', $title[12])
                            ->setCellValue('N1', $title[13])
                            ->setCellValue('O1', $title[14])
                            ->setCellValue('P1', $title[15])
                            ->setCellValue('Q1', $title[16])
                            ->setCellValue('R1', $title[17])
							->setCellValue('S1', $title[18]);
		$row = 2;
		foreach ($user_data as $data => $value){
			$objPHPExcel->getActiveSheet()
				->setCellValue('A'.$row, $value['email'])
				->setCellValue('B'.$row, '0')
				->setCellValue('C'.$row, ($value['o_normal_ot'] > 0 ? round($value['o_normal_ot']/60/60, 2): 0))
				->setCellValue('D'.$row, ($value['o_weekend_ot'] > 0 ? round($value['o_weekend_ot']/60/60, 2): 0))
				->setCellValue('E'.$row, ($value['o_holiday_ot'] > 0 ? round($value['o_holiday_ot']/60/60, 2): 0))
				->setCellValue('F'.$row, '0')
				// this still in COUNT format, need to get the rate for their allowance only can convert into currency value
				->setCellValue('G'.$row, $value['allowance_id']); 
			for($col=7;$col<=18;$col++){
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, '0');
			}
			$row++;
		}    
		session_destroy();
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="Kakitangan_'.$post_type.'_'.date('Y-m-d').'.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		return $objWriter->save('php://output');
	}else{
		$_SESSION['msg'] = 'Kakitangan Error: No Data exists in between '.$date1.' and '.$date2.'!';
		header("Location:http://".$hosting."/converter#kakitangan");
	}
	db_close($con);
}else{
	header("Location:http://".$hosting."/converter");
}
?>