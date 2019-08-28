<?php
/****************************************Database Queries*********************************************/
$hosting = strpos($_SERVER['REMOTE_ADDR'],'192.168.0.') !== false ? '192.168.0.58:81' : '211.24.110.3:81';
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

function get_ot_report($con,$date1,$date2,$users,$p,$limit){
	
	$Sql = "SELECT u.userid, u.badgenumber, u.name as name, u.lastname, u.ParentName, u.DeptName, DATE_FORMAT(a.AttDate, '%Y-%m-%d') AS AttDate, s.SchName, DATE_FORMAT(a.StartTime,'%H:%i') AS ClockInTime, DATE_FORMAT(a.EndTime,'%H:%i') AS ClockOutTime, TIME_FORMAT(SEC_TO_TIME(a.total_work_time*60),'%H:%i') AS total_work_time, TIME_FORMAT(SEC_TO_TIME(a.o_normal_ot),'%H:%i') AS o_normal_ot, TIME_FORMAT(SEC_TO_TIME(a.o_weekend_ot),'%H:%i') AS o_weekend_ot , TIME_FORMAT(SEC_TO_TIME(a.o_holiday_ot),'%H:%i') AS o_holiday_ot FROM attshifts a  
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
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND u.userid IN (".$users.") AND (a.o_normal_ot > 0 OR a.o_weekend_ot > 0 OR a.o_holiday_ot > 0) ORDER BY u.badgenumber, a.AttDate ASC LIMIT ".($p * $limit - $limit).",".$limit."";

            
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function get_pages_ot_report($rows,$limit){
	$page = $rows / $limit;
	$p = $page > 1 && ($page - floor($page) == 0) ? $page : floor($page) + 1 ; 
	return $p;
}

function get_rows_ot_report($con,$date1,$date2,$users,$limit){
	
	$Sql = "SELECT u.userid, u.badgenumber, u.name as name, u.lastname, u.ParentName, u.DeptName, DATE_FORMAT(a.AttDate, '%Y-%m-%d') AS AttDate, s.SchName, DATE_FORMAT(a.StartTime,'%H:%i') AS ClockInTime, DATE_FORMAT(a.EndTime,'%H:%i') AS ClockOutTime, TIME_FORMAT(SEC_TO_TIME(a.total_work_time*60),'%H:%i') AS total_work_time, TIME_FORMAT(SEC_TO_TIME(a.o_normal_ot),'%H:%i') AS o_normal_ot, TIME_FORMAT(SEC_TO_TIME(a.o_weekend_ot),'%H:%i') AS o_weekend_ot , TIME_FORMAT(SEC_TO_TIME(a.o_holiday_ot),'%H:%i') AS o_holiday_ot FROM attshifts a  
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

    if (($rows = mysqli_num_rows($result)) > 0) {
		return $rows;
    } 
	return 0;
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
$limit = isset($_POST['rp']) ? $_POST['rp'] : 20;
$p = isset($_POST['p']) ? $_POST['p'] : 1;
if(isset($_GET['c'])){$_SESSION['c'] = $_GET['c'];}
if(isset($_GET['e'])){$_SESSION['e'] = $_GET['e'];}
if(isset($_GET['d'])){empty($_GET['d'])?$_SESSION['d']=0:$_SESSION['d']=$_GET['d'];}
$rows = get_rows_ot_report($con,$_SESSION['c'],$_SESSION['e'],$_SESSION['d'], $limit);
$pages = get_pages_ot_report($rows, $limit);
?>
<head>
<link rel="shortcut icon" href="assets/logo_Tracx.png" />
<link rel="stylesheet" href="assets/theme.css"/>
<link rel="stylesheet" href="assets/flexigrid.pack.css"/>
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap.min.js"></script>
<script type="text/javascript">
function printPDF(id) {
    var contents = document.getElementById(id).innerHTML;
	var mywindow = window.open('', 'Print', 'height=600,width=800');

    mywindow.document.write('<html><head><title>Print</title><link rel="stylesheet" href="assets/theme.css"/>');
    mywindow.document.write('</head><body >');
    mywindow.document.write(contents);
    mywindow.document.write('</body></html>');
    mywindow.document.close();
    mywindow.focus();
    mywindow.print();
    return true;
}
	
$(function() {
	$('#pReload').click(function(){
		location.reload();
	});
	$('#rp').change(function(){
		$('#p').val('1');
		$('#pagination').submit();
	});
	
	$('#pFirst').click(function(){
		$('#p').val('1');
		$('#pagination').submit();
	});
	$('#pPrev').click(function(){
		if($('#p').val() <= 1 ){
			$('#p').val('1');
		}else{
			$('#p').val(parseInt($('#p').val()-1));
		}
		$('#pagination').submit();
	});
	$('#pNext').click(function(){
		if($('#p').val() >=  parseInt($('#pages').text())){
			$('#p').val($('#pages').text());
		}else{
			$('#p').val(parseInt($('#p').val())+1);
		}
		$('#pagination').submit();
	});
	$('#pLast').click(function(){
		$('#p').val(parseInt($('#pages').text()));
		$('#pagination').submit();
	});
	
	$('#p').change(function(){
		if($('#p').val() <= 0 || isNaN($('#p').val())){
			$('#p').val('1');
		}
		if($('#p').val() >  parseInt($('#pages').text())){
			$('#p').val($('#pages').text());
		}
		$('#pagination').submit();
	});

	$('#pagination').submit(function(){
		$("#id_page_load").show();
	});
	
	var eTop = $('#table-1').offset().top; 
	var $header = $("#table-1 > table > thead").clone();
	var $fixedHeader = $("#header-fixed > table ").append($header);

	$('#header-fixed > table').width($('#table-1 > table').width());
	$fixedHeader.hide();
	$("#id_page_load").hide();
	
	$("#table-1").scroll(function(){ 
		$('#header-fixed > table').width($('#table-1 > table').width());
		var v = 1;
		$('#table-1 > table > thead > tr > th').each(function(i, obj) {
			$('#header-fixed > table  > thead > tr > th:nth-child('+v+')').width($(this).width());
			v++;
		});

		var offset = $(this).scrollTop();
		if (offset >= eTop) {
			$fixedHeader.show();
			$('#table-1 > thead').hide();
			$('#table-1').height('345px');
			$('#header-fixed').css('overflow-y','auto');
		}
		else if (offset < eTop) {
			$fixedHeader.hide();
			$('#table-1 > thead').show();
			$('#table-1').height('373.5px');
			$('#header-fixed').css('overflow','hidden');
		}
		$('#header-fixed').scrollLeft($(this).scrollLeft());
	});
});
</script>
<style>
div#id_page_load {
    background-color: #dcdfe2;
    border-color: #7ac142;
	position: absolute;
    z-index: 999999;
    text-align: center;
    left: 45%;
    top: 45%;
    border-width: 5px;
    border-style: solid;
	font: 12px Verdana, Lucida, Arial, Helvetica, 宋体, sans-serif;
}
</style>
</head>
<body style="background: white; cursor: default;">
<div id="id_page_load">
		<div class="div_page_load">
			<div style="float: left; height: 40px;margin-right:6px">
				<img src="assets/loadpage.gif">
			</div>
			<div style="line-height: 40px; height: 40px; display: block;text-align:left; float: left; color:#32598A; font-size:14px;">Processing. Please wait...</div>
			<div class="clear displayN"></div>
		</div>
	<div class="clear displayN"></div>
</div>
<div id='id_main_div' style="margin:0px;">
	<div class="flexigrid">
		<div class="tDiv">
			<div class="tDiv2">
				<div class="fbutton">
					<div>
						<span class="export_pdf" style="padding-left: 20px;" onclick="printPDF('table-1');">Export PDF</span>
					</div>
				</div>
				<div class="fbutton">
					<div>
					<form action="ot_excel.php" method="post">
						<input type="hidden" name="start" value="<?= $_SESSION['c']?>" />
						<input type="hidden" name="end" value="<?= $_SESSION['e']?>" />
						<input type="hidden" name="users" value="<?= $_SESSION['d']?>" />
						<span><input type="submit" name="csv" style="padding-left: 20px; border:none;" class="export_csv" value="Export CSV" /></span>
					</form>
					</div>
				</div>
				
			</div>
			<div style="flow:left;"></div>
		</div>
		<div id="header-fixed" class="hDiv">
			<table class="table table-condensed" style='font-size:11px;margin-bottom: 0px;overflow:hidden;' cellpadding="0" cellspacing="0" border="0"></table>
		</div>
		<div id="table-1" class="hDiv bDiv" style="height: 373.5px;">
			<table class="table table-striped table-condensed" style='font-size:11px' cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr>
						<th>Personnel No</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Company</th>
						<th>Department</th>
						<th>Date</th>
						<th>Timetable Name</th>
						<th>Check-In Time</th>
						<th>Check-out Time</th>
						<th>Total Time Worked</th>
						<th>Normal OT</th>
						<th>Weekend OT</th>
						<th>Holiday OT</th>
						<th>Approve Status</th>
					</tr>
				</thead>
				<tbody>
				<?php if($ots = get_ot_report($con,$_SESSION['c'],$_SESSION['e'],$_SESSION['d'],$p,$limit)) { 
					foreach($ots as $all) { 
						$approver = get_ot_approver($con,$all['AttDate'],$all['userid']) ? 'Approved' : 'Auto OT';
						$parentName = ucwords(strtolower($all['ParentName']));
						$words = explode(" ", $parentName);
						$parentCode = "";

						foreach ($words as $w) {
						  $parentCode .= $w[0];
						}
						
						$normal_GPSB_Rest_OT = $parentCode == 'GPSB' && $all['o_weekend_ot'] >= 12 ? '14:00' : $all['o_weekend_ot'];
						echo '<tr><td>'.$all['badgenumber'].'</td>
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
			<div class="iDiv" style="display: none;"></div>
		</div>
		<div class="pDiv">
			<div class="pDiv2">
			<form id="pagination" method="post">
				<div class="pGroup">
					<select id="rp" name="rp">
						<option value="5" <?= $limit == 5 ? "selected" : ""; ?>>5&nbsp;&nbsp;</option>
						<option value="10" <?= $limit == 10 ? "selected" : ""; ?>>10&nbsp;&nbsp;</option>
						<option value="20" <?= $limit == 20 ? "selected" : ""; ?>>20&nbsp;&nbsp;</option>
						<option value="50" <?= $limit == 50 ? "selected" : ""; ?>>50&nbsp;&nbsp;</option>
						<option value="80" <?= $limit == 80 ? "selected" : ""; ?>>80&nbsp;&nbsp;</option>
						<option value="100" <?= $limit == 100 ? "selected" : ""; ?>>100&nbsp;&nbsp;</option>
					</select>
				</div>
				<div class="btnseparator"></div> 
				<div class="pGroup"> 
					<div id="pFirst" class="pFirst pButton" title="First"><span></span></div>
					<div id="pPrev" class="pPrev pButton" title="Previous"><span></span></div> 
				</div>
				<div class="btnseparator"></div>
				<div class="pGroup">
					<span class="pcontrol">No.<input id="p" type="text" size="4" name="p" value="<?= $p ?>">Page,Total <span id="pages"><?= $pages ?></span>Page</span></div>
				<div class="btnseparator"></div>
				<div class="pGroup">
					<div id="pNext" class="pNext pButton" title="Next"><span></span></div>
					<div id="pLast" class="pLast pButton" title="Last"><span></span></div>
				</div>
				<div class="btnseparator"></div>
				<div class="pGroup">
					<div id="pReload" class="pReload pButton" title="Refresh"><span></span></div>
				</div>
				<div class="btnseparator"></div>
				<div class="pGroup"><span class="pPageStat">Show <?= ($p * $limit - $limit + 1) ?> To <?= (($p * $limit) >= $rows) ? $rows : ($p * $limit); ?> ,Total <?= $rows ?> Records</span></div>
			</form>
			</div>
			<div style="flow:left;"></div>
		</div>
		<div class="vGrip"><span></span></div>
	</div>
</div>
</body>
<?php $_SESSION['msg'] = null; db_close($con); ?>
</html>