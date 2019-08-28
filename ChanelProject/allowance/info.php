<?php
function getdb(){
    $servername = "localhost";
    $username = "root";
    $password = "N3xpro2900";
    $db = "timetracx";
     
    try {
        $conn = mysqli_connect($servername, $username, $password, $db);
    }catch(exception $e){
        echo "Connection failed: " . $e->getMessage();
    }
    return $conn;
}

function get_all_device_area(){
    $con = getdb();
	
    $Sql = "SELECT i.alias AS alias, a.id AS area_id FROM personnel_area a
			LEFT JOIN (SELECT alias, area_id FROM iclock )i ON a.areaid = i.area_id";
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			if($row['alias'] != NULL){
				$array[$row['alias']] = $row['area_id'];
			}
        }
		return $array;
    } 
	return FALSE;
}

function get_ot_area($id){
    $con = getdb();
	
    $Sql = "SELECT * FROM ot_converter WHERE id LIKE '".$id."'";
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
		return $row;
    } 
	return FALSE;
}

$hosting = strpos($_SERVER['REMOTE_ADDR'],'192.168.0.') !== false ? '192.168.0.58:81' : '211.24.110.3:81';
 if(isset($_POST["convert"])){
	$filename = $_FILES["file"]["tmp_name"];		

	$csv_mimetypes = array(
		'text/csv',
		'application/csv',
		'text/comma-separated-values',
		'application/excel',
		'application/vnd.ms-excel',
		'application/vnd.msexcel'
	);

		if($_FILES["file"]["size"] > 0 && in_array($_FILES['file']['type'],$csv_mimetypes))
		{
		  	$file = fopen($filename, "r");
			$header = array(
				'Personnel No.',
				'Date',
				'Normal OT',
				'Weekend OT',
				'Holiday OT'
			);
			$isFirstRow = true;
			$current_personnel = null;
			
			header('Content-Type: text/csv; charset=utf-8');  
			header('Content-Disposition: attachment; filename=OT_payroll_'.date('Y-m-d').'.csv');  
			$output = fopen("php://output", "w");  
			fputcsv($output, array('Trans Date', 'Post Date', 'Employee', 'Code', 'Description', 'Work Unit', 'Rate'));
	        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE){
				$numCols = count($getData);			
				if($isFirstRow){
					for($j=0;$j<=$numCols;$j++){
						if(in_array(trim($getData[$j]),$header)){
							$index[] = $j;	
						}else{
							continue;
						}
						$head[] = trim($getData[$j]);
						/*echo "<script type=\"text/javascript\">
						alert(\"Invalid File Format: Please Upload CSV File.\");
						window.location.href = 'http://211.24.110.3/converter';
					</script>";*/
					}
					foreach($header as $x => $v){
						foreach($head as $x1 => $v1){								
							if($v==$v1){										
								$realPicked[$x] = $index[$x1];
							}									
						}
					}
					$isFirstRow = false;
					continue;
					
				}else{
					foreach($realPicked as $z){
						$data_details[] = trim($getData[$z]);
					}
					//print_r($data_details);
					
					$personal_no = $data_details[0];
					$date = $data_details[1];
					//$exception = $data_details[2];
					//$timetable_name =  $data_details[3];
					//$device =  $data_details[4];
					//$check_in = $data_details[5];
					//$check_out = $data_details[6];
					//$break = $data_details[7];
					//$auto_break = $data_details[8];
					//$absent = $data_details[9];
					//$total_work = $data_details[10];
					//$total_work_no_break = $data_details[11];
					$ot_1 = $data_details[2];
					$ot_2 = $data_details[3];
					$ot_3 = $data_details[4];

					/*if($absent == 1 || $check_in == null || $check_out == null){
						$check_in = '00:00:00';
						$check_out = '00:00:00';
						$daytype = 'NPL';
					}
					
					if(strtolower(trim($exception)) == 'day off'){
						$daytype = 'OD';
					}elseif (strtolower(trim($exception)) == 'weekend') {
						$daytype = 'RD';
					}*/
					if($current_personnel ==  null){
						
						$optimizedWU = 0;
						$optimizedWU2 = 0;
						$optimizedWU3 = 0;
						$new_min = 0;
						$new_min2 = 0;
						$new_min3 = 0;
						$finaldate = null;
						
						//First Data
						//Normal OT
						if ($ot_1 != null) 
                        {
							$total_ot1 = explode(':',$ot_1);
                            if ($total_ot1[0] > 0)
                            {
                                if ($total_ot1[1] < 30)
                                {
                                    $new_min = 0;
                                }
                                else
                                {
                                    $new_min = 0.5;
                                }
                                $optimizedWU = $total_ot1[0] + $new_min;
                            }
                        }
                        else
                        {
                            $optimizedWU = 0;
                        }
						//Rest Day OT
						if ($ot_2 != null)
                        {
							$total_ot2 = explode(':',$ot_2);
                            if ($total_ot2[0] > 0)
                            {
                                if ($total_ot2[1] < 30)
                                {
                                    $new_min2 = 0;
                                }
                                else
                                {
                                    $new_min2 = 0.5;
                                }
                                $optimizedWU2 = ($total_ot2[0]-8) + $new_min2;
                            }
                        }
                        else
                        {
                            $optimizedWU2 = 0;
                        }
						//Public Holiday OT
						if ($ot_3 != null)
                        {
							$total_ot3 = explode(':',$ot_3);
                            if ($total_ot3[0] > 0)
                            {
                                if ($total_ot3[1] < 30)
                                {
                                    $new_min3 = 0;
                                }
                                else
                                {
                                    $new_min3 = 0.5;
                                }
                                $optimizedWU3 = ($total_ot3[0]-8) + $new_min3;
                            }
                        }
                        else
                        {
                            $optimizedWU3 = 0;
                        }
						$current_personnel = $personal_no;
					}elseif($current_personnel !=  null && $current_personnel != $personal_no){
						
						if($optimizedWU != 0){
							$row1 = array($finaldate, $finaldate, $current_personnel, 'HW15', 'Working Day', $optimizedWU, '1.5');
							fputcsv($output, $row1); 
						}
						if($optimizedWU2 != 0){
							$row2 = array($finaldate, $finaldate, $current_personnel, 'HW20', 'Weekend', $optimizedWU2, '2.0');
							fputcsv($output, $row2); 
						}
						if($optimizedWU3 != 0){
							$row3 = array($finaldate, $finaldate, $current_personnel, 'HW30', 'Public Holiday', $optimizedWU3, '3.0');
							fputcsv($output, $row3); 
						}

						$optimizedWU = 0;
						$optimizedWU2 = 0;
						$optimizedWU3 = 0;
						$new_min = 0;
						$new_min2 = 0;
						$new_min3 = 0;
						$finaldate = null;
						
						//First Data
						//Normal OT
						if ($ot_1 != null) 
                        {
							$total_ot1 = explode(':',$ot_1);
                            if ($total_ot1[0] > 0)
                            {
                                if ($total_ot1[1] < 30)
                                {
                                    $new_min = 0;
                                }
                                else
                                {
                                    $new_min = 0.5;
                                }
                                $optimizedWU = $total_ot1[0] + $new_min;
                            }
                        }
                        else
                        {
                            $optimizedWU = 0;
                        }
						//Rest Day OT
						if ($ot_2 != null)
                        {
							$total_ot2 = explode(':',$ot_2);
                            if ($total_ot2[0] > 0)
                            {
                                if ($total_ot2[1] < 30)
                                {
                                    $new_min2 = 0;
                                }
                                else
                                {
                                    $new_min2 = 0.5;
                                }
                                $optimizedWU2 = ($total_ot2[0]-8) + $new_min2;
                            }
                        }
                        else
                        {
                            $optimizedWU2 = 0;
                        }
						//Public Holiday OT
						if ($ot_3 != null)
                        {
							$total_ot3 = explode(':',$ot_3);
                            if ($total_ot3[0] > 0)
                            {
                                if ($total_ot3[1] < 30)
                                {
                                    $new_min3 = 0;
                                }
                                else
                                {
                                    $new_min3 = 0.5;
                                }
                                $optimizedWU3 = ($total_ot3[0]-8) + $new_min3;
                            }
                        }
                        else
                        {
                            $optimizedWU3 = 0;
                        }
						
						$current_personnel = $personal_no;
					}else{
						
						//Following Data
						//Normal OT
						if ($ot_1 != null) 
                        {
							$total_ot1 = explode(':',$ot_1);
                            if ($total_ot1[0] > 0)
                            {
                                if ($total_ot1[1] < 30)
                                {
                                    $new_min = 0;
                                }
                                else
                                {
                                    $new_min = 0.5;
                                }
                                $optimizedWU = $optimizedWU + $total_ot1[0] + $new_min;
                            }
                        }
                        else
                        {
                            $optimizedWU = $optimizedWU;
                        }
						//Rest Day OT
						if ($ot_2 != null)
                        {
							$total_ot2 = explode(':',$ot_2);
                            if ($total_ot2[0] > 0)
                            {
                                if ($total_ot2[1] < 30)
                                {
                                    $new_min2 = 0;
                                }
                                else
                                {
                                    $new_min2 = 0.5;
                                }
                                $optimizedWU2 = $optimizedWU2 + ($total_ot2[0]-8) + $new_min2;
                            }
                        }
                        else
                        {
                            $optimizedWU2 = $optimizedWU2;
                        }
						//Public Holiday OT
						if ($ot_3 != null)
                        {
							$total_ot3 = explode(':',$ot_3);
                            if ($total_ot3[0] > 0)
                            {
                                if ($total_ot3[1] < 30)
                                {
                                    $new_min3 = 0;
                                }
                                else
                                {
                                    $new_min3 = 0.5;
                                }
                                $optimizedWU3 = $optimizedWU3 + ($total_ot3[0]-8) + $new_min3;
                            }
                        }
                        else
                        {
                            $optimizedWU3 = $optimizedWU3;
                        }	
						$finaldate = $date;
					}
				}
				$data_details = null;								
			}	
			if($optimizedWU != 0){
					$row1 = array($finaldate, $finaldate, $current_personnel, 'HW15', 'Working Day', $optimizedWU, '1.5');
					fputcsv($output, $row1); 
				}
			if($optimizedWU2 != 0){
				$row2 = array($finaldate, $finaldate, $current_personnel, 'HW20', 'Weekend', $optimizedWU2, '2.0');
				fputcsv($output, $row2); 
			}
			if($optimizedWU3 != 0){
				$row3 = array($finaldate, $finaldate, $current_personnel, 'HW30', 'Public Holiday', $optimizedWU3, '3.0');
				fputcsv($output, $row3); 
			}
			fclose($output);
	        fclose($file);
			
		}else{
			echo "<script type=\"text/javascript\">
						alert(\"Invalid File Format: Please Upload CSV File.\");
						window.location.href = 'http://".$hosting."/converter';
					</script>";
		}
	}else{
			echo "<script type=\"text/javascript\">
						alert(\"Invalid File Format: Please Upload CSV File.\");
						window.location.href = 'http://".$hosting."/converter';
					</script>";
	}
	mysqli_close();
 ?>