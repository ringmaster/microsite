<!DOCTYPE html>
<html>
  <head>
	  <title>Microsite - Edit a Page</title>
		<style type="text/css">
		textarea {
			width: 400px;
			height: 4em;
		}
		tfoot td {
			text-align: right;
		}
		</style>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
	  </head>
  <body>

	<table style="border:1px solid #eeeeee;">
	<tbody>
	<?php 
foreach($view->vars as $k => $v) {
	if(in_array($k, array())) {
		continue;
	}
	echo '<tr><th>' . $k . '</th><td style="border-left:1px solid #eeeeee;">' . htmlspecialchars($v) . '</td></tr>';
}
?>
	</tbody>
	</table>
	
  </body>
	<script type="text/javascript">
document.onkeydown = function getcode(ev) {
	value = ev.keyCode;
	if (value == 113) { location.href = location.href + '?edit'; }
}
	</script>

</html>

