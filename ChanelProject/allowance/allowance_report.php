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

function get_allowance_report($con,$date1,$date2,$depts,$staffs){
	
	/*$Sql = "SELECT u.badgenumber, u.name, u.lastname, u.ParentName, u.DeptName, s.allowance_name, COUNT(s.allowance_id) AS allowance_id FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, name, lastname, DeptID, DeptName, ParentName FROM userinfo ui
				LEFT JOIN (SELECT DeptID, DeptName, ParentName FROM departments s
					LEFT JOIN (SELECT DeptID AS did, Deptname AS ParentName FROM departments) ds ON s.supdeptid = ds.did
				) d ON d.DeptID = ui.defaultdeptid
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT allowance_id, schid, allowance_name FROM con_allowance_sch cs
				LEFT JOIN (SELECT id, NAME AS allowance_name FROM con_allowance) ca ON cs.allowance_id = ca.id
			) s ON s.schid = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND allowance_name IS NOT NULL AND a.Symbol LIKE '%P%' AND u.DeptID IN (".$depts.") GROUP BY u.badgenumber, s.allowance_id ORDER BY u.badgenumber, s.allowance_id ASC";*/
    
	$Sql = "SELECT u.userid,u.badgenumber, u.name, u.lastname, u.ParentName, u.DeptName, s.allowance_name, COUNT(s.allowance_id) AS allowance_id FROM attshifts a  
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
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND allowance_name IS NOT NULL AND a.Symbol LIKE '%P%' AND u.DeptID IN (".$depts.") AND u.userid IN (".$staffs.") GROUP BY u.badgenumber, s.allowance_id ORDER BY u.badgenumber, s.allowance_id ASC";
	
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function get_pages_allowance_report($rows,$limit){
	$page = $rows / $limit;
	$p = $page > 1 && ($page - floor($page) == 0) ? $page : floor($page) + 1 ; 
	return $p;
}

function get_rows_allowance_report($con,$date1,$date2,$depts,$limit){
	
	$Sql = "SELECT u.userid FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, defaultdeptid AS DeptID FROM userinfo ui
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT allowance_id, schid, allowance_name FROM con_allowance_sch cs
				LEFT JOIN (SELECT id, NAME AS allowance_name FROM con_allowance) ca ON cs.allowance_id = ca.id
			) s ON s.schid = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND allowance_name IS NOT NULL AND a.Symbol LIKE '%P%' AND u.DeptID IN (".$depts.") GROUP BY u.badgenumber ORDER BY u.badgenumber, s.allowance_id ASC";
	
    $result = mysqli_query($con, $Sql);  

    if (($rows = mysqli_num_rows($result)) > 0) {
		return $rows;
    } 
	return 0;
}

function get_rows_data_allowance_report($con,$date1,$date2,$depts,$p,$limit){
	
	$Sql = "SELECT u.userid FROM attshifts a  
			LEFT JOIN (SELECT userid, badgenumber, defaultdeptid AS DeptID FROM userinfo ui
			) u ON u.userid = a.userid 
			LEFT JOIN (SELECT allowance_id, schid, allowance_name FROM con_allowance_sch cs
				LEFT JOIN (SELECT id, NAME AS allowance_name FROM con_allowance) ca ON cs.allowance_id = ca.id
			) s ON s.schid = a.SchId
			WHERE a.AttDate >= '".$date1."%' AND a.AttDate <= '".$date2."%' AND allowance_name IS NOT NULL AND a.Symbol LIKE '%P%' AND u.DeptID IN (".$depts.") GROUP BY u.badgenumber ORDER BY u.badgenumber, s.allowance_id ASC LIMIT ".($p * $limit - $limit).",".$limit."";
	
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
session_start();
$con = getdb();
$limit = isset($_POST['rp']) ? $_POST['rp'] : 20;
$p = isset($_POST['p']) ? $_POST['p'] : 1;
if(isset($_GET['c'])){$_SESSION['c'] = $_GET['c'];}
if(isset($_GET['e'])){$_SESSION['e'] = $_GET['e'];}
if(isset($_GET['d'])){empty($_GET['d'])?$_SESSION['d']=0:$_SESSION['d']=$_GET['d'];}
$rows = get_rows_allowance_report($con,$_SESSION['c'],$_SESSION['e'],$_SESSION['d'], $limit);
$pages = get_pages_allowance_report($rows, $limit);
$r_data = get_rows_data_allowance_report($con,$_SESSION['c'],$_SESSION['e'],$_SESSION['d'], $p, $limit);
$r ="";
foreach($r_data as $row){
	$r .= '"'.$row['userid'].'",';
}
$r = rtrim($r,",");
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
    mywindow.document.write('</head><body>');
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
	
	$("#table-1").scroll(function() { 
		$('#header-fixed > table').width($('#table-1 > table').width());
		var v = 1;
		$('#table-1 > table > thead > tr > th').each(function(i, obj) {
			$('#header-fixed > table  > thead > tr:nth-child(1) > th:nth-child('+v+')').width($(this).width());			
			v++;
		});

		var offset = $(this).scrollTop();
		if (offset >= eTop) {
			$fixedHeader.show();
			$('#table-1 > thead').hide();
			$('#table-1').height('317px');
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
						<form action="allowance_excel.php" method="post">
							<input type="hidden" name="start" value="<?= $_SESSION['c']?>" />
							<input type="hidden" name="end" value="<?= $_SESSION['e']?>" />
							<input type="hidden" name="depts" value="<?= $_SESSION['d']?>" />
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
						<th rowspan='2'>Personnel No</th>
						<th rowspan='2'>First Name</th>
						<th rowspan='2'>Last Name</th>
						<th rowspan='2'>Company</th>
						<th rowspan='2'>Department</th>
						<?php if($allowances_name = get_allowance_name($con)) { $count = 0;foreach($allowances_name as $a) { $count++;echo '<th colspan="2">'.$a['name'].'</th>';}}else{ echo '<td>Allowances - NONE</td>';}?>
					</tr>
					<tr>
						<?php foreach($allowances_name as $a) {echo'<th>Non-CA</th><th>CA</th>';}?>
					</tr>
				</thead>
				<tbody>
				<tr>
				<?php if($allowances = get_allowance_report($con,$_SESSION['c'],$_SESSION['e'],$_SESSION['d'],$r)) { 
					$id = null; $c=0; $last = count($allowances); $count_row=0;
					foreach($allowances as $all) { 
						if($id != $all['badgenumber'] || $id == null){
							if($c!=$count && $id!=null){
								for($c=$c+1;$c<=$count;$c++){
									echo '<td>0</td><td>0</td>';
									//echo '<td>0</td>';
								}
							}
							$ca_exist = check_ca($con,$all['userid']);
							echo '</tr><tr><td>'.$all['badgenumber'].'</td>
								<td>'.$all['name'].'</td>
								<td>'.$all['lastname'].'</td>
								<td>'.$all['ParentName'].'</td>
								<td>'.$all['DeptName'].'</td>';
							$c=0;
							foreach($allowances_name as $a){
								if($a['name']==$all['allowance_name']){
									echo $ca_exist ? '<td>0</td><td>'.$all['allowance_id'].'</td>':'<td>'.$all['allowance_id'].'</td><td>0</td>';
									//echo '<td>'.$all['allowance_id'].'</td>';

									$c++;
									break;
								}else{
									echo '<td>0</td><td>0</td>';
									//echo '<td>0</td>';
								}
								$c++;
							}
								
						}else{
							for($c;$c<=$count;$c++){
								if($allowances_name[$c]['name']==$all['allowance_name']){
									echo $ca_exist ? '<td>0</td><td>'.$all['allowance_id'].'</td>':'<td>'.$all['allowance_id'].'</td><td>0</td>';
									//echo '<td>'.$all['allowance_id'].'</td>';
									$c++;
									break;
								}else{
									echo '<td>0</td><td>0</td>';
									//echo '<td>0</td>';
								}
							}
						}
						
						$id = $all['badgenumber'];
						$count_row++;
						if($last==$count_row){
							if($c!=$count){
								for($c=$c+1;$c<=$count;$c++){
									echo '<td>0</td><td>0</td>';
									//echo '<td>0</td>';
								}	
							}
						}
					}
				}?>
				</tr>
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
					<div id="pReload" class="pReload pButton" title="Refresh" onclick='location.reload();'><span></span></div>
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