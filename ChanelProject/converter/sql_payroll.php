<?php
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

function getTotalTimeCards_ot($con, $area, $date1, $date2){

	$Sql = "SELECT DISTINCT a.userid, u.badgenumber, a.AttDate, a.o_normal_ot, a.o_weekend_ot, a.o_holiday_ot FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber FROM userinfo ui
			LEFT JOIN (SELECT employee_id, area_id FROM userinfo_attarea ) ua ON ua.employee_id = ui.userid 
			) u ON u.userid = a.userid 
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
            WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND a.Late > 0 ORDER BY u.badgenumber, a.AttDate ASC";
    
	
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function getTotalTimeCards_allowance($con,$area,$date1,$date2){
	$Sql = "SELECT u.badgenumber, a.AttDate, COUNT(s.allowance_id) AS allowance_id FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, NAME, lastname FROM userinfo ui
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT allowance_id, schid FROM con_allowance_sch) s ON s.schid = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND a.`Symbol` LIKE '%P%' AND allowance_id > 0 GROUP BY u.badgenumber, a.AttDate ORDER BY u.badgenumber, a.AttDate ASC";
            
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
if($post_type = $_POST['export']){
	session_start();
	
	$ot_wages_title = array('Date','Employee','Code','Unit');
	$leave_title = array('TransDate','Employee','LeaveCode','Unit');
	$deduction_allowance_title = array('Date','Employee','Code','Unit');
	header('Content-Type: text/csv; charset=utf-8');  
	header('Content-Disposition: attachment; filename=SQL_'.$post_type.'_'.date('Y-m-d').'.csv');
	$output = fopen("php://output", "w");  
	$con = getdb();

	$area = null; // default no area
	$date1 = date('Y-m-d',strtotime($_POST['date1']));
	$date2 = date('Y-m-d',strtotime($_POST['date2']));
	/***************************User Data****************************************/
	switch ($post_type){
		case 'Overtime':
			if($user_data = getTotalTimeCards_ot($con,$area,$date1,$date2)){
				fputcsv($output, $ot_wages_title); 
				foreach ($user_data as $data => $value){					
					if($value['o_normal_ot'] > 0){
						$normal_unit = $value['o_normal_ot']/60/60;
						$row = array(date('d-m-Y',strtotime($value['AttDate'])),$value['badgenumber'],'HW15',round($normal_unit, 2));
						fputcsv($output, $row);
					}
					
					if($value['o_weekend_ot'] > 0){
						$weekend_unit = $value['o_weekend_ot']/60/60;
						$row = array(date('d-m-Y',strtotime($value['AttDate'])),$value['badgenumber'],'HW20',round($weekend_unit, 2));
						fputcsv($output, $row);
					}
					
					if($value['o_holiday_ot'] > 0){
						$holiday_unit = $value['o_holiday_ot']/60/60;
						$row = array(date('d-m-Y',strtotime($value['AttDate'])),$value['badgenumber'],'HW30',round($holiday_unit, 2));
						fputcsv($output, $row);
					}
				}    
				session_destroy();
			}else{
				$_SESSION['msg'] = 'SQL Error: No Overtime-related Data exists in between '.$date1.' and '.$date2.'!';
				header("Location:http://".$hosting."/converter#sql");
			}
			break;
		case 'Leave':
			if($user_data = getTotalTimeCards_leave($con,$area,$date1,$date2)){
				fputcsv($output, $leave_title); 
				foreach ($user_data as $data => $value){
					$leave_date_1 = strtotime($value['StartSpecDay']);
					$leave_date_2 = strtotime($value['EndSpecDay']);
					$totaltime = ($leave_date_2 - $leave_date_1); 
					$hours = intval($totaltime / 3600 / 24);   
					$leave_date = date('d-m-Y',strtotime($value['StartSpecDay']));
					if($hours < 1) {
						if(strpos(strtolower($value['LeaveName']),'annual') !== false || strpos(strtolower($value['ReportSymbol']),'al') !== false){
						$code = 'AL';
						$unit = '1';
						if(strpos(strtolower($value['LeaveName']),'half') !== false || strpos(strtolower($value['ReportSymbol']),'hal') !== false)
						{
							$unit = '0.5';
						}
						}else if(strpos(strtolower($value['LeaveName']),'sick') !== false || strpos(strtolower($value['ReportSymbol']),'mc') !== false){
							$code = 'MC';
							$unit = '1';
						}else if(strpos(strtolower($value['LeaveName']),'unpaid') !== false || strpos(strtolower($value['ReportSymbol']),'ul') !== false || strpos(strtolower($value['ReportSymbol']),'upl') !== false) {
							$code = 'UL';
							$unit = '1';
							if(strpos(strtolower($value['LeaveName']),'half') !== false || strpos(strtolower($value['ReportSymbol']),'hul') !== false)
							{
								$unit = '0.5';
							}
						}else {
							$code = $value['ReportSymbol'];
							$unit = $value['MinUnit'];
						}
						$row = array($leave_date,$value['badgenumber'],$code,$unit);
						fputcsv($output, $row);
					}else{
						for($i=1;$i<=$hours;$i++){
							if(strpos(strtolower($value['LeaveName']),'annual') !== false || strpos(strtolower($value['ReportSymbol']),'al') !== false){
							$code = 'AL';
							$unit = '1';
							if(strpos(strtolower($value['LeaveName']),'half') !== false || strpos(strtolower($value['ReportSymbol']),'hal') !== false)
							{
								$unit = '0.5';
							}
							}else if(strpos(strtolower($value['LeaveName']),'sick') !== false || strpos(strtolower($value['ReportSymbol']),'mc') !== false){
								$code = 'MC';
								$unit = '1';
							}else if(strpos(strtolower($value['LeaveName']),'unpaid') !== false || strpos(strtolower($value['ReportSymbol']),'ul') !== false || strpos(strtolower($value['ReportSymbol']),'upl') !== false) {
								$code = 'UL';
								$unit = '1';
								if(strpos(strtolower($value['LeaveName']),'half') !== false || strpos(strtolower($value['ReportSymbol']),'hul') !== false)
								{
									$unit = '0.5';
								}
							}else {
								$code = $value['ReportSymbol'];
								$unit = $value['MinUnit'];
							}
							$row = array($leave_date,$value['badgenumber'],$code,$unit);
							fputcsv($output, $row);
							$leave_date = date('d-m-Y',strtotime($value['StartSpecDay'].'+'.$i.' day'));
						}
					}
				}
				session_destroy();
			}else{
				$_SESSION['msg'] = 'SQL Error: No Leave-related Data exists in between '.$date1.' and '.$date2.'!';
				header("Location:http://".$hosting."/converter#sql");
			}
			break;
		case 'Wages':
			if($user_data = getTotalTimeCards_wages($con,$area,$date1,$date2)){
				fputcsv($output, $ot_wages_title); 
				foreach ($user_data as $data => $value){
					$total_work_unit = $value['total_work_time']/60;
					$row = array(date('d-m-Y',strtotime($value['AttDate'])),$value['badgenumber'],'WAGE',round($total_work_unit,2));
					fputcsv($output, $row);
				}
				session_destroy();
			}else{
				$_SESSION['msg'] = 'SQL Error: No Wages-related Data exists in between '.$date1.' and '.$date2.'!';
				header("Location:http://".$hosting."/converter#sql");
			}
			break;
			
		case 'Deduction':
			if($user_data = getTotalTimeCards_deduction($con,$area,$date1,$date2)){
				fputcsv($output, $deduction_allowance_title); 
				foreach ($user_data as $data => $value){					
					$value['Late'] > 0 ? $late = $value['Late']/60 : 0;
					$row = array(date('d-m-Y',strtotime($value['AttDate'])),$value['badgenumber'],'LATE',round($late,2));
					fputcsv($output, $row);
				}
				session_destroy();
			}else{
				$_SESSION['msg'] = 'SQL Error: No Deduction-related Data exists in between '.$date1.' and '.$date2.'!';
				header("Location:http://".$hosting."/converter#sql");
			}
			break;
			
		case 'Allowance':
			if($user_data = getTotalTimeCards_allowance($con,$area,$date1,$date2)){
				fputcsv($output, $deduction_allowance_title); 
				foreach ($user_data as $data => $value){					
					$row = array(date('d-m-Y',strtotime($value['AttDate'])),$value['badgenumber'],'MEAL',$value['allowance_id']);
					fputcsv($output, $row);
				}
				session_destroy();
			}else{
				$_SESSION['msg'] = 'SQL Error: No Allowance-related Data exists in between '.$date1.' and '.$date2.'!';
				header("Location:http://".$hosting."/converter#sql");
			}
			break;
			
		default:
			$_SESSION['msg'] = 'SQL Error: Unknown Function!!';
			header("Location:http://".$hosting."/converter#sql");
			break;
	}
	fclose($output);
	db_close($con);
}else{
	header("Location:http://".$hosting."/converter");
}
?>