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

function get_ot($con){
	$Sql = "SELECT c.id, c.code, c.name, c.normal_ot, c.weekend_ot, c.holiday_ot, c.rate, ca.userid FROM con_ot_name c
			LEFT JOIN (SELECT * FROM con_ot_user) ca ON ca.ot_id = c.id
			GROUP BY id ORDER BY c.id,FIELD(ca.userid, '".$_SESSION['userid']."') DESC";  
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
if(isset($_GET['id'])){$_SESSION['userid'] = $_GET['id'];}
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
			<li class="active"><a href="#index" data-toggle="tab">OT lists</a></li>
			<li><a href="#add" data-toggle="tab">Add OT</a></li>
		</ul>
        <div class="tab-content">
			<div id="add" class="tab-pane fade">
				<br>
				<form class="form-horizontal" action="ot.php" method="post">
					<div class="container">
						<div class="form-group">
							<label >OT Code <span style="color:red;">*</span></label>
							<input class="form-control" placeholder="No Spacing allowed, e.g. ot_1" pattern="[^\s]+" type="text" name="code" required/>
							<br>
							<label >OT Name <span style="color:red;">*</span></label>
							<input class="form-control" type="text" name="name" required/>
							<br>
							<label >OT Rate <span style="color:red;">*</span></label>
							<input class="form-control" type="number" min="0"  placeholder="0.0" step="0.1" pattern="[0-9]+\.[0-9]{1}"  name="rate" required/>
							<br>
							<label >OT Type <span style="color:red;">*</span></label>
							<select name="ot_type" class="form-control">
								<option value="normal" selected>Normal OT</option>
								<option value="weekend">Weekend OT</option>
								<option value="holiday">Holiday OT</option>
							</select>
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
							<thead>
								<tr>
									<th>Box</th>
									<th>Code</th>
									<th>Name</th>
									<th>OT Type</th>
									<th>Rate</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php $value=''; if($timetable = get_ot($con)) { foreach($timetable as $t) { if($value != $t['id']){?>
								<script type="text/javascript">
									$(document).ready(function(){
										$("#edit<?= $t['id']; ?>").click(function(){
											$("#edit<?= $t['id']; ?>").addClass("hide");
											$("#delete<?= $t['id']; ?>").addClass("hide");
											$("#submit<?= $t['id']; ?>").removeClass("hide").val("Edit").attr("name","edit");
											$("#close<?= $t['id']; ?>").removeClass("hide").val("Close");
											$("#name<?= $t['id']; ?>").removeClass("hide").val("<?= $t['name'] ?>");
											$("#code<?= $t['id']; ?>").removeClass("hide").val("<?= $t['code'] ?>");
											$("#rate<?= $t['id']; ?>").removeClass("hide").val("<?= $t['rate'] ?>");
											$("#ot<?= $t['id']; ?>").removeClass("hide");
											$("#label1<?= $t['id']; ?>").addClass("hide");
											$("#label2<?= $t['id']; ?>").addClass("hide");
											$("#label3<?= $t['id']; ?>").addClass("hide");
											$("#label4<?= $t['id']; ?>").addClass("hide");
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
											$("#code<?= $t['id']; ?>").addClass("hide");
											$("#rate<?= $t['id']; ?>").addClass("hide");
											$("#ot<?= $t['id']; ?>").addClass("hide");
											$("#label1<?= $t['id']; ?>").removeClass("hide");
											$("#label2<?= $t['id']; ?>").removeClass("hide");
											$("#label3<?= $t['id']; ?>").removeClass("hide");
											$("#label4<?= $t['id']; ?>").removeClass("hide");
										});
									});
								</script>
								<tr>
									<form class="form-horizontal" action="ot.php" method="post">
									<td>
										<input type="checkbox" onChange="this.form.submit()" name="ot_id" <?= ($_SESSION['userid'] == $t['userid']) ? 'value="1" checked' : 'value="0"';  ?>/>
										<input type="hidden" name="id" value="<?= $t['id']; ?>">
										<input type="hidden" name="userid" value="<?= $_SESSION['userid']; ?>">
									</td>
									</form>
									<form class="form-horizontal" action="ot.php" method="post">
									<td>
										<label id="label1<?php echo $t['id']; ?>"><?= $t['code'] ?></label>
										<input class="form-control hide" placeholder="No Spacing allowed, e.g. ot_1" pattern="[^\s]+" id="code<?= $t['id']; ?>" type="text" name="code" required"/>
									</td>
									<td>
										<label id="label2<?php echo $t['id']; ?>"><?= $t['name'] ?></label>
										<input class="form-control hide" id="name<?= $t['id']; ?>" type="text" name="name" required"/>
									</td>
									<td>
										<label id="label3<?php echo $t['id']; ?>"><?php if($t['holiday_ot'] == 1) {echo 'Holiday OT';}elseif($t['weekend_ot'] == 1){ echo 'Weekend OT';}else{ echo 'Normal OT';} ?></label>
										<select id="ot<?= $t['id']; ?>" name="ot_type" class="form-control hide">
											<option value="normal" <?= $t['normal_ot'] == 1 ? 'selected' : ''; ?>>Normal OT</option>
											<option value="weekend" <?= $t['weekend_ot'] == 1 ? 'selected' : ''; ?>>Weekend OT</option>
											<option value="holiday" <?= $t['holiday_ot'] == 1 ? 'selected' : ''; ?>>Holiday OT</option>
										</select>									</td>
									<td>
										<label id="label4<?php echo $t['id']; ?>"><?= $t['rate'] ?></label>
										<input class="form-control hide" min="0"  placeholder="0.0" step="0.1" pattern="[0-9]+\.[0-9]{1}" id="rate<?= $t['id']; ?>" type="number" name="rate" required"/>
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
							</tbody>
							</table>
						</div>
					</div>
				
			</div>
		</div>
    </div>
</body>
<?php $_SESSION['msg'] = null; db_close($con); ?>
</html>
