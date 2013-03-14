<?php

$token = hash ( 'sha1', microtime () );
$_SESSION [ 'token' ] = $token;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript" src="templates/js/jquery.min.js" type="text/javascript"></script>
<title>Login - &copy; Proshut Sitemap Creator</title>
<style>
html {
	font-family: tahoma, verdana; font-size: 11px
}

#wrapper {
	width: 300px; height: 200px; background: #CCC; position: relative; border: 1px solid #999;
}

#formElement {
	list-style-type: none; padding: 0px
}

#formElement li {
	padding: 10px
}

#formElement li span {
	margin-left: 20px
}

strong {
	color: red;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$('#wrapper').css( 'left', ($(document).width()/2)-($('#wrapper').width()/2) );
	$('#wrapper').css( 'top', ($(document).height()/2)-($('#wrapper').height()/2) );
	$("input").keyup(function(){
		var username = $("input[name='username']").val();
		var password = $("input[name='password']").val();
		if( username.length > 3 && password.length > 3  ){
			$("input[type='submit']").removeAttr("disabled");
		}
		})
	})
</script>
</head>
<body>
<div id="wrapper">
<div style="background: #333; padding: 10px; color: #FFF; font-weight: bold;">Administrator Login <?php
if ( isset ( $_SESSION [ 'message' ] ) ) {
	echo ' - <strong>' . $_SESSION [ 'message' ] . '</strong>';
	unset ( $_SESSION [ 'message' ] );
}
?></div>
<div>
<form action="index.php" method="post">
<ul id="formElement">
	<li>Username : <span><input type="text" name="username" /></span></li>
	<li>Password : <span><input type="password" name="password" /></span></li>
	<li><input type="submit" name="" value="Login" disabled="disabled" /><input type="hidden" name="token" value="<?php
	echo $token;
	?>" /></li>
	<li>Proshut Sitemap Creator - &copy; 2011</li>
</ul>
</form>
</div>
</div>
</body>
</html>