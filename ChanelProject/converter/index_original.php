<head>
<link rel="shortcut icon" href="assets/logo_Tracx.png" />
<link rel="stylesheet" href="assets/theme.css">
<script src="assets/jquery.min.js"></script>
<script src="assets/moment-with-locales.min.js"></script>
<script src="assets/bootstrap.min.js"></script>
<script src="assets/bootstrap-datetimepicker.min.js"></script>
<?php session_start(); if(!isset($_SESSION['msg'])){$_SESSION['msg'] = null;} ?>
<script type="text/javascript">
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip(); 
		if(window.location.href.search('#sql') > 0){
			$('a[href="#sql"]').tab('show'); 
		}else if(window.location.href.search('#kakitangan') > 0){
			$('a[href="#kakitangan"]').tab('show');
		}
		$("#alert").fadeTo(5000, 500).slideUp(500, function(){
			$("#alert").slideUp(500);
		});
	});
	$(function () {
		var d = new Date();
		var year = d.getFullYear();
		var month = d.getMonth() + 1;
		var date = d.getDate();
		
		$('#datetimepicker1').datetimepicker({
			icons: {
                    time: 'fa fa-clock-o',
                    up: 'fa fa-sort-asc',
                    down: 'fa fa-sort-desc',
					date: 'fa fa-calendar',
					previous: '	fa fa-caret-square-o-left',
					next: '	fa fa-caret-square-o-right',
					clear: 'fa fa-trash-o',
					close: 'fa fa-close'
                },
			format: 'YYYY-MM-DD',
			defaultDate: year+"-"+month+"-01",
		});
		$('#datetimepicker2').datetimepicker({
			icons: {
                    time: 'fa fa-clock-o',
                    up: 'fa fa-sort-asc',
                    down: 'fa fa-sort-desc',
					date: 'fa fa-calendar',
					previous: '	fa fa-caret-square-o-left',
					next: '	fa fa-caret-square-o-right',
					clear: 'fa fa-trash-o',
					close: 'fa fa-close'
                },
			format: 'YYYY-MM-DD',
			defaultDate: year+"-"+month+"-"+date,
			useCurrent: false //Important! See issue #1075
		});
		
		$("#datetimepicker1").on("dp.change", function (e) {
			$('#datetimepicker2').data("DateTimePicker").minDate(e.date);
		});
		
		$("#datetimepicker2").on("dp.change", function (e) {
			$('#datetimepicker1').data("DateTimePicker").maxDate(e.date);
		});
		
		$('#datetimepicker3').datetimepicker({
			icons: {
                    time: 'fa fa-clock-o',
                    up: 'fa fa-sort-asc',
                    down: 'fa fa-sort-desc',
					date: 'fa fa-calendar',
					previous: '	fa fa-caret-square-o-left',
					next: '	fa fa-caret-square-o-right',
					clear: 'fa fa-trash-o',
					close: 'fa fa-close'
                },
			format: 'YYYY-MM-DD',
			defaultDate: year+"-"+month+"-01",
		});
		$('#datetimepicker4').datetimepicker({
			icons: {
                    time: 'fa fa-clock-o',
                    up: 'fa fa-sort-asc',
                    down: 'fa fa-sort-desc',
					date: 'fa fa-calendar',
					previous: '	fa fa-caret-square-o-left',
					next: '	fa fa-caret-square-o-right',
					clear: 'fa fa-trash-o',
					close: 'fa fa-close'
                },
			format: 'YYYY-MM-DD',
			defaultDate: year+"-"+month+"-"+date,
			useCurrent: false //Important! See issue #1075
		});
		
		$("#datetimepicker3").on("dp.change", function (e) {
			$('#datetimepicker4').data("DateTimePicker").minDate(e.date);
		});
		
		$("#datetimepicker4").on("dp.change", function (e) {
			$('#datetimepicker3').data("DateTimePicker").maxDate(e.date);
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
	color: #FC6B0A;
}
</style>
</head>

<br>
<body>
    <div id="wrap">
        <div class="container">
            <div class="row">
			    <div class="form-group">
				<a href="/converter/"><img src="assets/logo.png" alt="TimeTracX" style="float:left;"><i style="font-size:55px;"> - Payroll Integrator <sup style="color:red;font-size:28px;">Beta</sup></i></a>
				</div>
				<ul class="nav nav-tabs">
					<!--<li class="active"><a href="#index" data-toggle="tab">Converter <sup>Legacy</sup></a></li>-->
					<li class="active"><a href="#sql" data-toggle="tab">SQL Payroll</a></li>
					<li><a href="#kakitangan" data-toggle="tab">Kakitangan</a></li>
				</ul>
                <div class="tab-content">
					<div id="sql" class="tab-pane fade in active">
						<br>
						<?php if(strpos($_SESSION['msg'],'SQL') !== false) { ?>
						<div id="alert" class="alert alert-danger" ><?= $_SESSION['msg']; ?></div>
						<?php } ?>
						<form class="form-horizontal" action="sql_payroll.php" method="post" name="upload_excel" enctype="multipart/form-data">
							<label class="control-label" for="filebutton">Exported file is in <i style="color:green;">CSV-based Format</i></label>
							<div class="control-label">
								
								<div class="container">
									<div class="row">
										<div class='col-sm-6'>
											<label class="pull-left">Start Date:</label>
											<input type='text' name="date1" class="form-control" id='datetimepicker1'/>
										</div>
										<div class='col-sm-6'>
											<label class="pull-left">End Date:</label>
											<input type='text' name="date2" class="form-control" id='datetimepicker2'/>
										</div>
									</div>
								</div>
								
								<br>
								<div class="pull-left">
									<label >Export:</label> &nbsp;&nbsp;
									<input type="submit" name="export" class="btn btn-default" value="Overtime"/> &nbsp;&nbsp;
									<input type="submit" name="export" class="btn btn-default" value="Leave"/> &nbsp;&nbsp;
									<input type="submit" name="export" class="btn btn-default" value="Wages"/> &nbsp;&nbsp;
								
									<input type="submit" name="export" class="btn btn-default" value="Deduction"/> &nbsp;&nbsp;
									<input type="submit" name="export" class="btn btn-default" value="Allowance"/> &nbsp;&nbsp;
								</div>
							</div>
						</form>
					</div>
						
					<div id="kakitangan" class="tab-pane fade">
						<br>
						<?php if(strpos($_SESSION['msg'],'Kakitangan') !== false) { ?>
						<div id="alert" class="alert alert-danger" ><?= $_SESSION['msg']; ?></div>
						<?php } ?>
						<form class="form-horizontal" action="kakitangan.php" method="post" name="upload_excel" enctype="multipart/form-data">
							<label class="control-label" for="filebutton">Exported file is in <i style="color:green;">CSV-based Format</i></label>
							<div class="control-label">
								<div class="container">
									<div class="row">
										<div class='col-sm-6'>
											<label class="pull-left">Start Date:</label>
											<input type='text' name="date1" class="form-control" id='datetimepicker3'/>
										</div>
										<div class='col-sm-6'>
											<label class="pull-left">End Date:</label>
											<input type='text' name="date2" class="form-control" id='datetimepicker4'/>
										</div>
									</div>
								</div>
								<br>
								<div class="pull-left">
									<label >Export:</label> &nbsp;&nbsp;
									<input type="submit" name="import" class="btn btn-default" value="Monthly Payroll" data-placement="bottom"/> &nbsp;&nbsp;
								</div>
							</div>
						</form>
					</div>
					
					<div id="index" class="tab-pane fade">
						<br>
						<form class="form-horizontal" action="info.php" method="post" name="upload_excel" enctype="multipart/form-data">
							<div class="form-group">
								<label class="col-xs-4 col-sm-4 col-md-4 control-label" for="filebutton">Select <i style="color:green;">TotalTimeCard.CSV</i> File</label>
								<div class="col-md-4 col-sm-4 col-xs-4">
									<input type="file" name="file" id="file" class="input-large btn btn-default">
								</div>
								<div class="col-md-4 col-sm-4 col-xs-4">
									<input type="submit" name="convert" class="btn btn-success" value="Convert"/>
								</div>
							</div>
						</form>
					</div>
				</div>
            </div>
        </div>

    </div>
</body>
<hr>
<footer style="background-color:black">
    <div id="wrap">
        <div class="container">
            <div class="row">
				<h4 class="text-center" style="color:white;font-size:12px;">&copy; <?php echo date('Y'); ?> TimeTracX</h4>
            </div>
        </div>    
    </div>
</footer>
<?php session_destroy(); ?>
</html>
