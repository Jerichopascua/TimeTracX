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

function get_allowance_report($con,$date1,$date2,$depts){
	/*$Sql = "SELECT u.badgenumber, u.name, u.lastname, u.ParentName, u.DeptName, s.allowance_name, COUNT(s.allowance_id) AS allowance_id FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, name, lastname, DeptID, DeptName, ParentName FROM userinfo ui
				LEFT JOIN (SELECT DeptID, DeptName, ParentName FROM departments s
					LEFT JOIN (SELECT DeptID AS did, Deptname AS ParentName FROM departments) ds ON s.supdeptid = ds.did
				) d ON d.DeptID = ui.defaultdeptid
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT allowance_id, schid, allowance_name FROM con_allowance_sch cs
				LEFT JOIN (SELECT id, NAME AS allowance_name FROM con_allowance) ca ON cs.allowance_id = ca.id
			) s ON s.schid = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND a.Symbol LIKE '%P%' AND u.DeptID IN (".$depts.") GROUP BY u.badgenumber, s.allowance_id ORDER BY u.badgenumber, s.allowance_id ASC";*/
			
	$Sql = "SELECT u.userid, u.badgenumber, u.name, u.lastname, u.ParentName, u.DeptName, s.allowance_name, COUNT(s.allowance_id) AS allowance_id FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, name, lastname, DeptID, DeptName, ParentName FROM userinfo ui
				LEFT JOIN (SELECT t1.DeptID AS DeptID, t1.DeptName AS DeptName, (CASE 
					WHEN t5.DeptName IS NOT NULL THEN t5.DeptName
					WHEN t4.DeptName IS NOT NULL THEN t4.DeptName 
					WHEN t3.DeptName IS NOT NULL THEN t3.DeptName 
					WHEN t2.DeptName IS NOT NULL THEN t2.DeptName 
					ELSE t1.DeptName END ) AS ParentName
					FROM departments AS t1
						LEFT JOIN departments AS t2 ON t2.DeptID = t1.supdeptid
						LEFT JOIN departments AS t3 ON t3.DeptID = t2.supdeptid
						LEFT JOIN departments AS t4 ON t4.DeptID = t3.supdeptid
						LEFT JOIN departments AS t5 ON t5.DeptID = t4.supdeptid
				) d ON d.DeptID = ui.defaultdeptid
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT allowance_id, schid, allowance_name FROM con_allowance_sch cs
				LEFT JOIN (SELECT id, NAME AS allowance_name FROM con_allowance) ca ON cs.allowance_id = ca.id
			) s ON s.schid = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND allowance_name IS NOT NULL AND a.Symbol LIKE '%P%' AND u.DeptID IN (".$depts.") GROUP BY u.badgenumber, s.allowance_id ORDER BY u.badgenumber, s.allowance_id ASC";
	
	
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function get_allowance_name($con){
	$Sql = "SELECT c.id, c.name FROM con_allowance  c
			ORDER BY c.id ASC";  
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function check_ca($con,$id){
	$Sql = "SELECT ot_id, code FROM con_ot_user ou
			LEFT JOIN (SELECT id, code FROM con_ot_name WHERE code LIKE '%CA%' OR code LIKE '%ca%') ot ON ot.id = ou.ot_id
			WHERE userid LIKE '".$id."'";  
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
	$allowance_title = array('Personnel No','First Name','Last Name','Company','Department');

	$date1 = date('Y-m-d',strtotime($_POST['start']));
	$date2 = date('Y-m-d',strtotime($_POST['end']));
	$depts = $_POST['depts'];
	$count = 0;
	if($allowances_name = get_allowance_name($con)){
		foreach($allowances_name as $a){
			$count++;
			array_push($allowance_title,$a['name']);
			$ca = explode('_',$a['name']);
			$allowance_ca_name = $ca[0].'_'.$ca[1].'(CA)_'.$ca[2];
			array_push($allowance_title,$allowance_ca_name);
		}
	}
	$allowance_title = str_replace('"', '', $allowance_title);

	header('Content-Type: text/csv; charset=utf-8');  
	header('Content-Disposition: attachment; filename=Allowances_'.$date1.'_'.$date2.'.csv');
	$output = fopen("php://output", "w");  
	
	/***************************User Data****************************************/

	if($allowances = get_allowance_report($con,$date1,$date2,$depts)){
		fputs($output, implode($allowance_title, ',')."\n");
		$id = null; $c=0; $last = count($allowances); $count_row=0; $row = array();
		foreach($allowances as $all) { 
			if($id != $all['badgenumber'] || $id == null){
				if($c!=$count && $id!=null){
					for($c=$c+1;$c<=$count;$c++){
						array_push($row,'0');
						array_push($row,'0');
					}		
				}
				
				$ca_exist = check_ca($con,$all['userid']);
				if($id!=null){
					$row = str_replace('"', '', $row);
					fputs($output, implode($row, ',')."\n");
				}
				$c=0;
				$row = array();
				array_push($row,$all['badgenumber'],$all['name'],$all['lastname'],$all['ParentName'],$all['DeptName']);
				foreach($allowances_name as $a){
					if($a['name']==$all['allowance_name']){
						$ca_exist ? array_push($row,'0') && array_push($row,$all['allowance_id']):array_push($row,$all['allowance_id'])&&array_push($row,'0');
						$c++;
						break;
					}else{
						array_push($row,'0');
						array_push($row,'0');
					}
					$c++;
				}
			}else{
				for($c;$c<=$count;$c++){
					if($allowances_name[$c]['name']==$all['allowance_name']){
						$ca_exist ? array_push($row,'0') && array_push($row,$all['allowance_id']):array_push($row,$all['allowance_id'])&&array_push($row,'0');
						$c++;
						break;
					}else{
						array_push($row,'0');
						array_push($row,'0');
					}
				}
			}
			$id = $all['badgenumber'];
			$count_row++;
			if($last==$count_row){
				if($c!=$count){
					for($c=$c+1;$c<=$count;$c++){
						array_push($row,'0');
						array_push($row,'0');
					}	
				}
				$row = str_replace('"', '', $row);
				fputs($output, implode($row, ',')."\n");
			}
		}
	}else{
		header("Location:http://".$hosting."/allowance/allowance_report.php");
	}
	fclose($output);
	db_close($con);
}else{
	header("Location:http://".$hosting."/allowance/allowance_report.php");
}
?>