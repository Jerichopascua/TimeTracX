<?php
/****************************************Database Queries*********************************************/
$hosting = strpos($_SERVER['REMOTE_ADDR'],'192.168.0.') !== false ? '192.168.0.59:83' : '211.24.110.3:83';
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
	
	$Sql = "SELECT u.userid, u.badgenumber, u.name as name, u.lastname, u.ParentName, u.DeptName, DATE_FORMAT(a.AttDate, '%Y-%m-%d') AS AttDate, s.SchName, a.ClockInTime, a.ClockOutTime, TIME_FORMAT(SEC_TO_TIME(a.total_work_time*60),'%H:%i') AS total_work_time, TIME_FORMAT(SEC_TO_TIME(a.o_normal_ot),'%H:%i') AS o_normal_ot, TIME_FORMAT(SEC_TO_TIME(a.o_weekend_ot),'%H:%i') AS o_weekend_ot , TIME_FORMAT(SEC_TO_TIME(a.o_holiday_ot),'%H:%i') AS o_holiday_ot FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, NAME, lastname, DeptID, DeptName, ParentName FROM userinfo ui
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
session_start();
$con = getdb();
if(isset($_GET['c'])){$_SESSION['c'] = $_GET['c'];}
if(isset($_GET['e'])){$_SESSION['e'] = $_GET['e'];}
if(isset($_GET['d'])){empty($_GET['d'])?$_SESSION['d']=0:$_SESSION['d']=$_GET['d'];}
?>
<head>
<link rel="shortcut icon" href="assets/logo_Tracx.png" />
<link rel="stylesheet" href="assets/theme.css">
<script src="assets/jquery.min.js"></script>
<script src="assets/moment-with-locales.min.js"></script>
<script src="assets/bootstrap.min.js"></script>
<script src="assets/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#alert").fadeTo(5000, 500).slideUp(500, function(){
			$("#alert").slideUp(500);
		});
	});
</script>
<style type="text/css">
a:link, a:visited, a:hover, a:active    {
  text-decoration:  none;
  color:black;
  } 
.btn-default{
	background: 0 0;
}
tr{
	font-size:12px;
}
.table-hover tbody tr:hover td {
  background-color: #d0f7b3;
}
.tDiv {
    background-color: #dbdee1;
	font-size: 12px;
    position: relative;
    border: 1px solid #ccc;
    border-bottom: 0px;
}
.hDiv, .hDiv > tr > th {
    background-color: #b8bcb6;
    position: relative;
    border: 1px solid #ccc;
    border-bottom: 0px;
	font-weight: normal;
}
.table-fixed tbody {
height: 350px;
overflow-y: auto;
width: 100%;

}
.table-fixed thead,
.table-fixed tbody,
.table-fixed tr,
.table-fixed td,
.table-fixed th {
display: block;
}
.table-fixed tr:after {
content: "";
display: block;
visibility: hidden;
clear: both;
}
.table-fixed tbody td,
.table-fixed thead > tr > th {
float: left;
}
.tfoot{
	position: fixed;
	margin-top: -43px;
	width: 100%;
	background-color: #dbdee1;
	border: 1px solid #ccc;
    border-top: 0px;
    overflow: hidden;
    white-space: nowrap;
    position: relative;
}
</style>
</head>
<body>
	<div class="tDiv">
		<form action="ot_excel.php" method="post">
			<input type="hidden" name="start" value="<?= $_SESSION['c']?>" />
			<input type="hidden" name="end" value="<?= $_SESSION['e']?>" />
			<input type="hidden" name="users" value="<?= $_SESSION['d']?>" />
			<input type="submit" name="csv" style="font-size:11px;" class="btn" value="Export CSV" />
		</form>
	</div>
	<table class="table table-responsive table-hover table-condensed table-bordered table-striped table-fixed" style="margin-top:-15px;">
		<thead class="hDiv">
			<tr>
				<th>Personnel No</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Company</th>
				<th>Department</th>
				<th>Date</th>
				<th>Timetable Name</th>
				<th>Check In Time</th>
				<th>Check out Time</th>
				<th>Total Time Worked</th>
				<th>Normal OT</th>
				<th>Weekend OT</th>
				<th>Holiday OT</th>
				<th>Approve Status</th>
			</tr>
		</thead>
		<tbody>
		<tr>
			<?php if($ots = get_ot_report($con,$_SESSION['c'],$_SESSION['e'],$_SESSION['d'])) { 
				foreach($ots as $all) { 
					$approver = get_ot_approver($con,$all['AttDate'],$all['userid']) ? 'Approved' : 'Auto OT';
					$parentName = ucwords(strtolower($all['ParentName']));
					$words = explode(" ", $parentName);
					$parentCode = "";

					foreach ($words as $w) {
					  $parentCode .= $w[0];
					}
					
					$normal_GPSB_Rest_OT = $parentCode == 'GPSB' && $all['o_weekend_ot'] >= 12 ? '14:00' : $all['o_weekend_ot'];
					echo '<tr class="filterable-cell"><td>'.$all['badgenumber'].'</td>
						<td>'.$all['name'].'</td>
						<td>'.$all['lastname'].'</td>
						<td>'.$all['ParentName'].'</td>
						<td>'.$all['DeptName'].'</td>
						<td>'.$all['AttDate'].'</td>
						<td>'.$all['SchName'].'</td>
						<td>'.$all['ClockInTime'].'</td>
						<td>'.$all['ClockOutTime'].'</td>
						<td>'.$all['total_work_time'].'</td>
						<td>'.$all['o_normal_ot'].'</td>
						<td>'.$normal_GPSB_Rest_OT.'</td>
						<td>'.$all['o_holiday_ot'].'</td>
						<td>'.$approver.'</td></tr>';
				}
			}
			?>
		</tbody>
	</table>
	<div class="clearfix"></div>
	<div class="tfoot">
			<ul class="pagination pagination-sm">
				<li class="disabled">
					<span aria-hidden="true">&laquo;</span>
				</li>
				<li class="active"><span>1 <span class="sr-only">(current)</span></span></li>
				<li><a href="#">2</a></li>
				<li><a href="#">3</a></li>
				<li><a href="#">4</a></li>
				<li><a href="#">5</a></li>
				<li>
					<a href="#" aria-label="Next">
					<span aria-hidden="true">&raquo;</span>
				</a>
				</li>
			</ul>
	</div>
</body>
<?php $_SESSION['msg'] = null; db_close($con); ?>
</html>