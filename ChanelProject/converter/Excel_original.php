<?php
require_once "Excel/PHPExcel.php";

/****************************************Database Queries*********************************************/
function getdb(){
    //$host = $_SERVER['REMOTE_ADDR'] == '211.24.110.3:81' ? '192.168.0.58:81' : '211.24.110.3:81'; // for local machine
    //$host = '211.24.110.3:81';// working in cpanel
    $host = 'locahost:81';
	$username = "zax";
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

function getTotalTimeCards($con,$area,$date){
	
    /*$Sql = "SELECT a.userid, u.badgenumber, u.name, u.lastname, a.AttDate,s.SchName, a.Symbol, a.Late, a.Early,a.break_time_real, a.break_late, a.StartTime, a.EndTime, a.ClockInTime, a.ClockOutTime, a.total_work_time FROM attshifts a  
            LEFT JOIN (SELECT userid, badgenumber, NAME, lastname, area_id FROM userinfo ui
            LEFT JOIN (SELECT employee_id, area_id FROM userinfo_attarea ) ua ON ua.employee_id = ui.userid 
            ) u ON u.userid = a.userid 
            LEFT JOIN (SELECT SchClassID, SchName FROM schclass) s ON s.SchClassID = a.SchId
            WHERE a.AttDate LIKE '2018-02-09%' AND u.area_id LIKE '".$area."' AND u.badgenumber NOT LIKE '100132828' ORDER BY u.badgenumber ASC";*/
    
    $Sql = "SELECT a.userid, u.badgenumber, u.name, u.lastname, a.AttDate,s.SchName, a.Symbol, a.Late, a.Early,a.break_time_real, a.break_late, a.StartTime, a.EndTime, a.ClockInTime, a.ClockOutTime, a.total_work_time, m.data_index, m.in_time, m.out_time FROM attshifts a  
            LEFT JOIN (SELECT userid, badgenumber, NAME, lastname, area_id FROM userinfo ui
            LEFT JOIN (SELECT employee_id, area_id FROM userinfo_attarea ) ua ON ua.employee_id = ui.userid 
            ) u ON u.userid = a.userid 
            left join (SELECT emp_id, att_date, data_index, in_time, out_time FROM att_multipletransaction WHERE data_type LIKE '2' AND att_date LIKE '".$date."%') m ON m.emp_id = u.userid 
            LEFT JOIN (SELECT SchClassID, SchName FROM schclass) s ON s.SchClassID = a.SchId
            WHERE a.AttDate LIKE '".$date."%' AND u.area_id LIKE '".$area."' ORDER BY s.SchName,u.badgenumber ASC";
            
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


/****************************************Excel Design Layout *********************************************/
$header= array(
	'font' => array(
		'name' => 'Calibri Light',
		'size' => 20
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	),
	'fill' => array(
	    'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array(
			'argb' => 'FFC9C9C9',
		)
	),
);
$small_header = array(
	'font' => array(
		'bold' => true,
		'name' => 'Calibri',
		'size' => 14,
		'color' => array(
			'argb' => 'FF2F75B5',
		)
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	),
);

$sub_header = array(
	'font' => array(
		'name' => 'Calibri Light',
		'size' => 20
	),
	'borders' => array(
	    'top' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	    'bottom' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	),
);

$small_sub_header = array(
	'font' => array(
		'name' => 'Times New Roman',
		'size' => 14,
		'color' => array(
			'argb' => 'FFF2F2F2',
		)
	),
	'borders' => array(
	    'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	),
	'fill' => array(
	    'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array(
			'argb' => 'FF7B7B7B',
		)
	),
);

$body = array(
	'font' => array(
		'name' => 'Times New Roman',
		'size' => 14,
	),
	'borders' => array(
	    'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap' => true
	),
);
$body_box = array(
	'font' => array(
		'name' => 'Calibri',
		'size' => 14,
	),
	'borders' => array(
	    'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	),
);
$body_left_right = array(
	'font' => array(
		'name' => 'Calibri',
		'size' => 14,
	),
	'borders' => array(
	    'left' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
		'right' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	),
);
$body_bottom = array(
	'font' => array(
		'name' => 'Calibri',
		'size' => 14,
	),
	'borders' => array(
	    'bottom' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	),
);
$red_box = array(
	'fill' => array(
	    'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array(
			'argb' => PHPExcel_Style_Color::COLOR_RED,
		)
	),
);
$side_title = array('A'=>'Personnel No.','B'=>'Name','C'=>'Date','D'=>'Timetable','E'=>'Daily Summary Report','F'=>'DAILY SUMMARY REPORT FOR');
$attendance_title = array('ATTENDANCE','E'=>'Check-In Time', 'F'=>'Check-Out Time', 'G'=>'Remarks', 'H'=>'Total Time Worked');
$break_title = array('ACTUAL BREAK TIME', 'Break Out Time', 'Break In Time', 'Total Actual Break Time', 'Break Late');
/*****************************************************************************************************************************************************/

/******** Operation Starts Here ********/
$objPHPExcel = new PHPExcel();

$con = getdb();
if(date('Y-m-d H:i:s') >= date('Y-m-d 05:00:00') && date('Y-m-d H:i:s') <= date('Y-m-d 18:00:00')){
    $b = 1;
}else{
    $b = 0;
}
$x = 0;
for($a=0; $a <= $b; $a++){
    $areas = getAreas($con);
    
    //2 dates for morning session
    if($a == 1){
        $date = date('Y-m-d',strtotime(date('Y-m-d').'-1 day'));
    }else{
        $date = date('Y-m-d');
    }
    
    foreach ($areas as $area => $v){
        $objPHPExcel->setActiveSheetIndex($x);
    
        strpos(strtolower(trim($v['areaname'])),'kulim') !== false ? $j = 'M' : $j = 'O';  
        $objPHPExcel->getActiveSheet()
        ->freezePane('C7')
        ->setTitle(date('d M',strtotime($date)).' '.$v['areaname']) // Area Name for different worksheet
        ->mergeCells('A1:'.$j.'1')
        ->setShowGridlines(false)
        ->setCellValue('A1', $side_title['E'])
        ->getStyle('A1:'.$j.'1')->applyFromArray($header);
    
        $objPHPExcel->getActiveSheet()->setCellValue('A3', $side_title['F'].' '.strtoupper(date('dMY',strtotime($date))))
            ->getStyle('A3')->applyFromArray($small_header);
        
        $objPHPExcel->getActiveSheet()
            ->mergeCells('A4:'.$j.'4')
            ->setCellValue('A4', $v['areaname'])
            ->getStyle('A4:'.$j.'4')->applyFromArray($sub_header);
        
        for($i='A';$i < 'E'; $i++){
            $objPHPExcel->getActiveSheet()
                ->mergeCells($i.'5:'.$i.'6')
                ->setCellValue($i.'5', $side_title[$i])
                ->getStyle($i.'5:'.$i.'6')->applyFromArray($small_sub_header);
        }
        
        $objPHPExcel->getActiveSheet()
            ->mergeCells('E5:H5')
            ->setCellValue('E5', $attendance_title[0])
            ->getStyle('E5')->applyFromArray($small_sub_header);
            
        for($i='E';$i < 'I'; $i++){
            $objPHPExcel->getActiveSheet()
                ->setCellValue($i.'6', $attendance_title[$i])
                ->getStyle($i.'6')->applyFromArray($small_sub_header);
        }
        
        for($i='I';$i <= $j; $i++){
            if($i == 'I'){
                $objPHPExcel->getActiveSheet()
                ->mergeCells('I5:'.$j.'5')
                ->setCellValue('I5', $break_title[0])
                ->getStyle('I5')->applyFromArray($small_sub_header);
            }
            if($i == 'J'|| $i == 'L'){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($i.'6', $break_title[2])
                    ->getStyle($i.'6')->applyFromArray($small_sub_header);        
            }elseif($i == 'O' || ($i == 'M' && $j == 'M')){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($i.'6', $break_title[3])
                    ->getStyle($i.'6')->applyFromArray($small_sub_header); 
            }else{
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($i.'6', $break_title[1])
                    ->getStyle($i.'6')->applyFromArray($small_sub_header);   
            }
        
        }
        
        /***************************User Data****************************************/
        $row = 7;
        $countDays = 0;
        $countNights = 0;
        $countOffs = 0; 
        $countOffPs = 0; 
        $countRests = 0; 
        $countRestPs = 0; 
        $countDayLate = 0;
        $countNightLate = 0;
        $countDayAbsent = 0;
        $countNightAbsent = 0;
        
        if($user_data = getTotalTimeCards($con,$v['id'],$date)){
            $break_col = 'I';
            $filter = '';
            foreach ($user_data as $data => $value){
                if($filter != $value['badgenumber']){
                   
                
                    $attDate = explode(' ', $value['AttDate']);
                    $startTime = explode(' ', $value['StartTime']);
                    $endTime = explode(' ', $value['EndTime']);
                    
                    $break_out = explode(' ', $value['in_time']);
                    $break_in = explode(' ', $value['out_time']);
                    empty($break_out[1]) ? $break_out[1] = '': $break_out[1];
                    empty($break_in[1]) ? $break_in[1] = '': $break_in[1];
                    
                    
                    $off = strpos(strtolower(trim($value['SchName'])),':off') !== false ? 'OD' : '';
                    $rest = strpos(strtolower(trim($value['SchName'])),':rest') !== false ? 'RD' : '';
                    $absent = strpos(strtolower(trim($value['Symbol'])),'a') !== false ? 'A' : 'P';
                    if($off == 'OD'){
                        $remark = $off == 'OD' && $absent == 'A' && $absent ? 'OD'  : $off.','.$absent;
                    }elseif($rest == 'RD'){
                        $remark = $rest == 'RD' && $absent == 'A' ? 'RD'  : $rest.','.$absent;
                    }elseif(strpos(strtolower(trim($value['Symbol'])),'v') !== false){
                        $remark = 'Leave';
                    }else{
                        $remark = $absent;
                    }

                    $countDayAbsent = strpos(strtolower(trim($value['SchName'])),':day') !== false &&  $absent == 'A' ? $countDayAbsent+1 : $countDayAbsent;
                    $countNightAbsent = strpos(strtolower(trim($value['SchName'])),':night') !== false &&  $absent == 'A' ? $countNightAbsent+1 : $countNightAbsent;
        
                    $countDays = strpos(strtolower(trim($value['SchName'])),':day') !== false && $remark != 'Leave' ? $countDays+1 : $countDays;
                    $countNights = strpos(strtolower(trim($value['SchName'])),':night') !== false && $remark != 'Leave' ? $countNights+1 : $countNights;
                    $countOffs = strpos(strtolower(trim($value['SchName'])),':off') !== false ? $countOffs+1 : $countOffs; 
                    $countOffPs = strpos(strtolower(trim($value['SchName'])),':off') !== false && $absent == 'P' ? $countOffPs+1 : $countOffPs; 
                    $countRests = strpos(strtolower(trim($value['SchName'])),':rest') !== false ? $countRests+1 : $countRests; 
                    $countRestPs = strpos(strtolower(trim($value['SchName'])),':rest') !== false && $absent == 'P' ? $countRestPs+1 : $countRestPs; 
                    
                    if($value['total_work_time'] > 0){
                        $hour = ($value['total_work_time']/60);
                        $min = $value['total_work_time']-(floor($hour)*60);
                        $work_time = (strlen(floor($hour)) == 1 ? '0'.floor($hour) : floor($hour)).':'.(strlen($min) == 1 ? '0'.$min : $min);
                    }else{
                        $work_time = '';
                    }
                    
                    if($value['break_time_real'] > 0){
                        $bhour = $value['break_time_real']/60;
                        $bmin = $value['break_time_real']-(floor($bhour)*60);
                        $break_time = (strlen(floor($bhour)) == 1 ? '0'.floor($bhour) : floor($bhour)).':'.(strlen($bmin) == 1 ? '0'.$bmin : $bmin);
                        if($value['break_time_real'] > 90){
                            $objPHPExcel->getActiveSheet()->getStyle(($j == 'O' ? 'O' : 'M').''.$row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                        }
                    }else{
                        $break_time = '';
                    }
                    
                    if($value['break_late'] > 0){
                        $blhour = $value['break_late']/60;
                        $blmin = $value['break_late']-(floor($blhour)*60);
                        $break_late = (strlen(floor($blhour)) == 1 ? '0'.floor($blhour) : floor($blhour)).':'.(strlen($blmin) == 1 ? '0'.$blmin : $blmin);
                    }else{
                        $break_late = '';
                    }
                    
                    $objPHPExcel->getActiveSheet()
                            ->setCellValue('A'.$row, $value['badgenumber'])
                            ->setCellValue('B'.$row, ucwords(strtolower($value['name']).' '.strtolower($value['lastname'])))
                            ->setCellValue('C'.$row, $attDate[0])
                            ->setCellValue('D'.$row, $value['SchName'])
                            ->setCellValue('E'.$row, strpos(strtolower(trim($value['StartTime'])),'1900') !== false ? '' : $startTime[1])
                            ->setCellValue('F'.$row, strpos(strtolower(trim($value['EndTime'])),'1900') !== false ? '' : $endTime[1])
                            ->setCellValue('G'.$row, ltrim($remark, ','))
                            ->setCellValue('H'.$row, $work_time)
                            ->setCellValue($j.''.$row, $break_time)
                            ->setCellValue('I'.$row, strpos(strtolower(trim($value['in_time'])),'1900') !== false ? '' : $break_out[1]);
                     
                    if(strpos(strtolower(trim($value['StartTime'])),'1900') !== false && $off != 'OD' && $rest != 'RD'){
                            $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->applyFromArray($red_box);
                    }
                    if(strpos(strtolower(trim($value['EndTime'])),'1900') !== false && $off != 'OD' && $rest != 'RD'){
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->applyFromArray($red_box);
                    }
                    
                    if(strpos(strtolower(trim($value['in_time'])),'1900') !== false){
                        $objPHPExcel->getActiveSheet()->getStyle('I'.$row)->applyFromArray($red_box);
                    }
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$row, strpos(strtolower(trim($value['out_time'])),'1900') !== false ? '' : $break_in[1]);
                    if(strpos(strtolower(trim($value['out_time'])),'1900') !== false){
                        $objPHPExcel->getActiveSheet()->getStyle('J'.$row)->applyFromArray($red_box);
                    }
                    
                    $objPHPExcel->getActiveSheet()->getStyle('A7:'.$j.''.$row)->applyFromArray($body);
                    
                    if($value['StartTime'] > date('Y-m-d H:i:s',strtotime($value['ClockInTime'].'+30 minutes')) && $off != 'OD' && $rest != 'RD'){
                        $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                        
                        $countDayLate = strpos(strtolower(trim($value['SchName'])),':day') !== false ? $countDayLate+1 : $countDayLate;
                        $countNightLate = strpos(strtolower(trim($value['SchName'])),':night') !== false ?$countNightLate+1 : $countNightLate;
        
                    }
                    
                    if($value['EndTime'] < $value['ClockOutTime'] && $off != 'OD' && $rest != 'RD'){
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                    }
                    
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$row.':F'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$row.':F'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $row++;
                }else{
                    $break_col = $value['data_index'] == 2 ? 'K' : ($value['data_index'] == 3 ? 'M' : 'K');
                    
                    $break_out = explode(' ', $value['in_time']);
                    $break_in = explode(' ', $value['out_time']);
                    empty($break_out) ? $break_out = '': $break_out;
                    empty($break_in) ? $break_in = '': $break_in;
                    
                    if(strpos(strtolower(trim($value['in_time'])),'1900') !== false){
                        $objPHPExcel->getActiveSheet()->getStyle($break_col.''.($row-1))->applyFromArray($red_box);
                    }
                    $objPHPExcel->getActiveSheet()
                            ->setCellValue($break_col++.''.($row-1), strpos(strtolower(trim($value['in_time'])),'1900') !== false ? '' : $break_out[1]);
                    
                    if(strpos(strtolower(trim($value['out_time'])),'1900') !== false){
                        $objPHPExcel->getActiveSheet()->getStyle($break_col.''.($row-1))->applyFromArray($red_box);
                    }
                    $objPHPExcel->getActiveSheet()
                            ->setCellValue($break_col++.''.($row-1), strpos(strtolower(trim($value['out_time'])),'1900') !== false ? '' : $break_in[1]);
                    
                }
                $filter = $value['badgenumber'];
            }
            
        }
        //$objPHPExcel->getActiveSheet()->getStyle('I7:'.($j == 'P' ? 'N' : 'L').''.$row)->applyFromArray($red_box);   
        /*******************Lower Body*******************************************************************/
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$row, 'Morning Shift')
            ->setCellValue('C'.$row, 'Night Shift')
            ->setCellValue('E'.$row, 'Off Day')
            ->setCellValue('G'.$row, 'Rest Day')
            ->setCellValue('A'.($row+1), 'Attend')
            ->setCellValue('B'.($row+1), ($countDays-$countDayAbsent))
            ->setCellValue('C'.($row+1), 'Attend')
            ->setCellValue('D'.($row+1), ($countNights-$countNightAbsent))
            ->setCellValue('E'.($row+1), 'OD')
            ->setCellValue('F'.($row+1), ($countOffs-$countOffPs))
            ->setCellValue('G'.($row+1), 'RD')
            ->setCellValue('H'.($row+1), ($countRests-$countRestPs))
            ->setCellValue('A'.($row+2), 'Absent')
            ->setCellValue('B'.($row+2), $countDayAbsent)
            ->setCellValue('C'.($row+2), 'Absent')
            ->setCellValue('D'.($row+2), $countNightAbsent)
            ->setCellValue('E'.($row+2), 'OD,P')
            ->setCellValue('F'.($row+2), $countOffPs)
            ->setCellValue('G'.($row+2), 'RD,P')
            ->setCellValue('H'.($row+2), $countRestPs)
            ->setCellValue('A'.($row+3), 'Late')
            ->setCellValue('B'.($row+3), $countDayLate)
            ->setCellValue('C'.($row+3), 'Late')
            ->setCellValue('D'.($row+3), $countNightLate)
            ->setCellValue('A'.($row+4), 'Total')
            ->setCellValue('B'.($row+4), $countDays)
            ->setCellValue('C'.($row+4), 'Total')
            ->setCellValue('D'.($row+4), $countNights)
            ->setCellValue('E'.($row+4), 'Total')
            ->setCellValue('F'.($row+4), $countOffs)
            ->setCellValue('G'.($row+4), 'Total')
            ->setCellValue('H'.($row+4), $countRests);
            
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->applyFromArray($body_box);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->applyFromArray($body_left_right);
        $objPHPExcel->getActiveSheet()->getStyle('C'.$row)->applyFromArray($body_box);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->applyFromArray($body_left_right);
        $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->applyFromArray($body_box);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->applyFromArray($body_left_right);
        $objPHPExcel->getActiveSheet()->getStyle('G'.$row)->applyFromArray($body_box);
        $objPHPExcel->getActiveSheet()->getStyle('H'.$row)->applyFromArray($body_left_right);
        
        for($i='A';$i <= 'H'; $i++){
            $objPHPExcel->getActiveSheet()->getStyle($i.''.($row+1))->applyFromArray($body_left_right);
            $objPHPExcel->getActiveSheet()->getStyle($i.''.($row+2))->applyFromArray($body_left_right);
            $objPHPExcel->getActiveSheet()->getStyle($i.''.($row+3))->applyFromArray($body_left_right);
        }
    
        $objPHPExcel->getActiveSheet()->getStyle('A'.($row+4).':H'.($row+4))->applyFromArray($body_box);
        $objPHPExcel->getActiveSheet()->getStyle('G'.($row+4).':'.$j.''.($row+4))->applyFromArray($body_bottom);
        /*******************Footer*****************************************************************/
        $row = $row+7;
        $objPHPExcel->getActiveSheet()->getStyle('C'.($row))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$row, '*Notes: Everyday clocking records capture range')
            ->setCellValue('C'.$row, '**Red: Late & early out')
            ->setCellValue('A'.($row++), 'a) Day Shift')
            ->setCellValue('A'.($row++), 'Check-in time from 06:30am to 07:45am')
            ->setCellValue('A'.($row++), 'Check-out time from 06:30pm to 08:00pm');
    
        $pgDay = 1;
        $kulimDay = 1;
        $ksDay = 1;
    
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$row, $v['areaname'].':');
    
        if(strpos(strtolower($v['areaname']),'penang') !== false){
            $breaks = getBreakTimes($con,'pg(7am-7pm)');
            foreach($breaks as $break => $key){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('B'.$row++, 'Break '.$pgDay.' time from '.date('h:ia',strtotime($key['start_time'])).' to '.date('h:ia',strtotime($key['end_time'])));
                $pgDay++;
            }
        }elseif(strpos(strtolower($v['areaname']),'kulim') !== false){
            $breaks = getBreakTimes($con,'kulim day');
            foreach($breaks as $break => $key){
                $objPHPExcel->getActiveSheet()
                ->setCellValue('B'.$row++, 'Break '.$kulimDay.' time from '.date('h:ia',strtotime($key['start_time'])).' to '.date('h:ia',strtotime($key['end_time'])));
                $kulimDay++;
            }
        }elseif(strpos(strtolower($v['areaname']),'keysight') !== false){
            $breaks = getBreakTimes($con,'ks:day');
            foreach($breaks as $break => $key){
                $objPHPExcel->getActiveSheet()
                ->setCellValue('B'.$row++, 'Break '.$ksDay.' time from '.date('h:ia',strtotime($key['start_time'])).' to '.date('h:ia',strtotime($key['end_time'])));
                $ksDay++;
            }
        }
    
        $row++;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.($row++), 'a) Night Shift')
            ->setCellValue('A'.($row++), 'Check-in time from 06:30pm to 08:00pm')
            ->setCellValue('A'.($row++), 'Check-out time from 06:30am to 08:00am');    
        
        $pgDay = 1;
        $kulimDay = 1;
        $ksDay = 1;
    
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$row, $v['areaname'].':');
    
        if(strpos(strtolower($v['areaname']),'penang') !== false){
            $breaks = getBreakTimes($con,'pg(7pm-7am)');
            foreach($breaks as $break => $key){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('B'.$row++, 'Break '.$pgDay.' time from '.date('h:ia',strtotime($key['start_time'])).' to '.date('h:ia',strtotime($key['end_time'])));
                $pgDay++;
            }
        }elseif(strpos(strtolower($v['areaname']),'kulim') !== false){
            $breaks = getBreakTimes($con,'kulim night');
            foreach($breaks as $break => $key){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('B'.$row++, 'Break '.$kulimDay.' time from '.date('h:ia',strtotime($key['start_time'])).' to '.date('h:ia',strtotime($key['end_time'])));
                $kulimDay++;
            }
        }elseif(strpos(strtolower($v['areaname']),'keysight') !== false){
            $breaks = getBreakTimes($con,'ks:night');
            foreach($breaks as $break => $key){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('B'.$row++, 'Break '.$ksDay.' time from '.date('h:ia',strtotime($key['start_time'])).' to '.date('h:ia',strtotime($key['end_time'])));
                $ksDay++;
            }
        }
            
        $row++;$row++;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.($row++), 'System will not be able to capture IN and OUT time if associate late for too much') 
            ->setCellValue('A'.($row++), 'Every clocking records can be view after login to TimeTracX system, at Attendance >Transaction');  
        /*******************************************************************************************/
        $objPHPExcel->getActiveSheet()
            ->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()
            ->getColumnDimension('B')->setWidth(35);    
            
        for($i='C';$i < 'Z'; $i++){
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension($i)->setAutoSize(true);
        }
        $objPHPExcel->createSheet();
        $x++;
    }
}

$objPHPExcel->removeSheetByIndex($x);

$objPHPExcel->setActiveSheetIndex(0);
$filename = $side_title['E'];

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
return $objWriter->save('php://output');

db_close($con);
?>