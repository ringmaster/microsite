<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="MicroSite">
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
  </head>
  <body>

	<form method="POST" action="">
	
	<table style="border:1px solid #eeeeee;">
	<tbody>
	<?php 
	foreach($view->vars as $k => $v) {
		if(in_array($k, array('_self'))) {
			continue;
		}
		echo '<tr><th>' . $k . '</th><td style="border-left:1px solid #eeeeee;"><textarea name="vars['.$k.']">' . htmlspecialchars($v) . '</textarea></td></tr>';
	}
	?>
	<tr><th><input type="text" name="newfield"></th><td><textarea name="newvalue"></textarea></td></tr>
	</tbody>
	<tfoot>
	<tr><td colspan="2"><a href="/<?php echo $path; ?>">View Page</a> <input type="submit" value="Submit"></td></tr>
	</tfoot>
	</table>
	
	</form>
	
  </body>
</html>
