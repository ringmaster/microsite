<!DOCTYPE html>
<html id="<?php echo $page_id; ?>">
  <head>
	  <title><?php echo $title; ?></title>
		<style type="text/css">
		@import url(/css/microsite.css);
		</style>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
  </head>
  <body>
		<?php echo $content; ?>
		<script type="text/javascript">
document.onkeydown = function getcode(ev) {
	value = ev.keyCode;
	if (value == 113) { location.href = location.href + '?edit'; }
}
		</script>
  </body>
</html>