<?php 
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

function get_norm_timetable($con){
	$Sql = "SELECT SchClassID, SchName FROM schclass s WHERE s.timetable_type LIKE '0' ORDER BY s.SchClassID ASC";  
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function get_flex_timetable($con){
	$Sql = "SELECT SchClassID, SchName FROM schclass s WHERE s.timetable_type LIKE '1' ORDER BY s.SchClassID ASC";  
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}

function get_allowance($con){
	$Sql = "SELECT c.id, c.name, c.code, ca.schid FROM con_allowance  c
			LEFT JOIN (SELECT * FROM con_allowance_sch) ca ON ca.allowance_id =  c.id
			ORDER BY c.id, FIELD(ca.schid, '".$_SESSION['schid']."') DESC";  
    $result = mysqli_query($con, $Sql);  

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
        }
		return $array;
    } 
	return FALSE;
}
$con = getdb();
session_start(); 
if(!isset($_SESSION['msg'])){$_SESSION['msg'] = null;}
if(isset($_GET['id'])){$_SESSION['schid'] = $_GET['id'];}
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
	color: #FC6B0A;
	background: 0 0;
	background-color: white !important;
}
.btn-default:hover{
	border-color: green;
	color: #FC6B0A ;
}
body,table,.form-control{
	font-size:12.5px;
}
</style>
</head>
<br>
<body>
     <div class="container">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#index" data-toggle="tab">Allowance lists</a></li>
			<li><a href="#add" data-toggle="tab">Add Allowance</a></li>
		</ul>
        <div class="tab-content">
			<div id="add" class="tab-pane fade">
				<br>
				<form class="form-horizontal" action="allowance.php" method="post">
					<div class="container">
						<div class="form-group">
							<label >Allowance Name <span style="color:red;">*</span></label>
							<input class="form-control" type="text" name="name" required/>
						</div>
						<br>
						<div class="form-group">
							<label >Allowance Tied up Normal Timetable</label>
							<table class="table table-hover">
								<?php $i = 0; if($normal_timetable = get_norm_timetable($con)) { foreach($normal_timetable as $t) { echo ($i == 0) ? '<tr>' : '';?>
									<td>
										<input type="checkbox" name="timetable[]" value="<?= $t['SchClassID'] ?>" /> <?= $t['SchName'] ?>
									</td>
								<?php  echo ($i == 2) ? '</tr>' : ''; (($i+1) > 2) ? $i = 0 : $i++;  }} ?>
							</table>
						</div>
						<div class="form-group">
							<label >Allowance Tied up Flexible Timetable</label>
							<table class="table table-hover">
								<?php $i = 0; if($flexible_timetable = get_flex_timetable($con)) { foreach($flexible_timetable as $t) { echo ($i == 0) ? '<tr>' : '';?>
									<td>
										<input type="checkbox" name="timetable[]" value="<?= $t['SchClassID'] ?>" /> <?= $t['SchName'] ?>
									</td>
								<?php  echo ($i == 2) ? '</tr>' : ''; (($i+1) > 2) ? $i = 0 : $i++;  }} ?>
							</table>
							
						</div>
						<div class="form-group">
							<input type="submit" name="save" class="btn btn-default orange pull-right" value="Save" /> &nbsp;&nbsp;
						</div>
					</div>
				</form>
			</div>
			
			<div id="index" class="tab-pane fade in active">
				<br>
				<?php if(($_SESSION['msg']) != null) { ?>
				<div id="alert" class="alert <?= (strpos($_SESSION['msg'],'Error') !== false) ? 'alert-danger' : 'alert-success' ?>" ><?= $_SESSION['msg']; ?></div>
				<?php } ?>
					<div class="container">
						<div class="form-group">
							<table class="table table-hover">
								<tr>
									<th>Box</th>
									<!--<th>Code</th>-->
									<th>Name</th>
									<th>Action</th>
								</tr>
								<?php $value=''; if($timetable = get_allowance($con)) { foreach($timetable as $t) { if($value != $t['id']){?>
								<script type="text/javascript">
									$(document).ready(function(){
										$("#edit<?= $t['id']; ?>").click(function(){
											$("#edit<?= $t['id']; ?>").addClass("hide");
											$("#delete<?= $t['id']; ?>").addClass("hide");
											$("#submit<?= $t['id']; ?>").removeClass("hide").val("Edit").attr("name","edit");
											$("#close<?= $t['id']; ?>").removeClass("hide").val("Close");
											$("#name<?= $t['id']; ?>").removeClass("hide").val("<?= $t['name'] ?>");
											$("#label<?= $t['id']; ?>").addClass("hide");
										});
										$("#delete<?= $t['id']; ?>").click(function(){
											$("#edit<?= $t['id']; ?>").addClass("hide");
											$("#delete<?= $t['id']; ?>").addClass("hide");
											$("#submit<?= $t['id']; ?>").removeClass("hide").val("Yes").attr("name","delete");
											$("#close<?= $t['id']; ?>").removeClass("hide").val("No");
										});
										$("#close<?= $t['id']; ?>").click(function(){
											$("#edit<?= $t['id']; ?>").removeClass("hide");
											$("#delete<?= $t['id']; ?>").removeClass("hide");
											$("#submit<?= $t['id']; ?>").addClass("hide");
											$("#close<?= $t['id']; ?>").addClass("hide");
											$("#name<?= $t['id']; ?>").addClass("hide");
											$("#label<?= $t['id']; ?>").removeClass("hide");
										});
									});
								</script>
								<tr>
									<form class="form-horizontal" action="allowance.php" method="post">
									<td>
										<input type="checkbox" onChange="this.form.submit()" name="timetable_id" <?= ($_SESSION['schid'] == $t['schid']) ? 'value="1" checked' : 'value="0"';  ?>/>
										<input type="hidden" name="id" value="<?= $t['id']; ?>">
										<input type="hidden" name="schid" value="<?= $_SESSION['schid']; ?>">
									</td>
									</form>
									<form class="form-horizontal" action="allowance.php" method="post">
									<!--<td>
										<label id="label<?php echo $t['id']; ?>"><?= $t['code'] ?></label>
										<input class="form-control hide" id="code<?= $t['id']; ?>" type="text" name="code" required"/>
									</td>-->
									<td>
										<label id="label<?php echo $t['id']; ?>"><?= $t['name'] ?></label>
										<input class="form-control hide" id="name<?= $t['id']; ?>" type="text" name="name" required"/>
									</td>
									<td>
										<input type="button" id="edit<?= $t['id']; ?>" class="btn btn-default" value="Edit" /> 
										<input type="button" id="delete<?= $t['id']; ?>" class="btn btn-default" value="Delete ?" /> 
										<input type="submit" id="submit<?= $t['id']; ?>" class="btn btn-success hide" /> 
										<input type="button" id="close<?= $t['id']; ?>" class="btn btn-danger hide" />
										<input type="hidden" name="id" value="<?= $t['id']; ?>">
									</td>
									</form>
								</tr>
								<?php $value = $t['id'];}}} ?>
							</table>
						</div>
					</div>
				
			</div>
		</div>
    </div>
</body>
<?php $_SESSION['msg'] = null; db_close($con); ?>
</html>
