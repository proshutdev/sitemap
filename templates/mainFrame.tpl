<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript" src="templates/js/jquery.min.js" type="text/javascript"></script>
<title>&copy; Proshut Sitemap Creator</title>
<style>
html {
	font-family: tahoma, verdana; font-size: 11px
}
#menu{
	padding-left:5px;
	list-style-type:none;
	height:13px;
	background:#FFF;
	position:absolute;
	z-index:100;
}
#menu li{
	float:left;
	padding:5px;
	border:1px solid #CCC;
	margin-right:3px;
	background:#FFF;
	cursor:pointer
}
#message{
	background:#EFEFEF;
	border:1px solid #CCC;
	color:green;
	padding:5px;
	font-weight:bold;
	margin-bottom:5px
}
</style>
</head>
<body>
<ul id="menu">
<li id="runEngin" style="border-bottom:1px solid #FFF" >Run Engine</li>
<li id="update" style="background:#666;color:#FFF">Site Map Setting</li>
<li id="logout" style="background:#900;color:#FFF"><a href="index.php?do=logout" style="color:#FFF;text-decoration:none">Log out</a></li>

</ul>
<div style="border:1px solid #CCC;position:relative;top:35px;<?php if( !isset( $_SESSION['updated'] ) ) echo "display:none;" ?>padding:10px" id="updateForm">
<div id="message" style="display:none"><?php echo @$_SESSION['message']?></div>
<form name="license_update" action="index.php" method="post"  >
<table cellpadding="1" cellspacing="1" border="0" width="100%">
	<tr>
		<td width="15%">Outputs :</td>
		<td>
		<?php foreach ( array( 'UrlList'=>'URL List ','ImageXML'=>'Images ','SiteXML'=>'Sites','MobileXML'=>'Mobile Version','RSSXML'=>'RSS','HTMLMap'=>'HTML' ) as $key=>$item ){?>
		<input type="checkbox" name="outputs[]" value="<?php echo $key; ?>" <?php if(in_array($key,$setting['outputs'])) echo  'checked="checked"';  ?> /><?php echo $item  ?>
		<?php }?>
		</td>
	</tr>
	<tr>
		<td width="15%">Number of links in Files :</td>
		<td><input type="text" name="linksPerFile" value="<?php echo $setting['linksPerFile']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">Number of links in RSS :</td>
		<td><input type="text" name="RSSlinksPerFile" value="<?php echo $setting['RSSlinksPerFile']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">Number of links in HTML :</td>
		<td><input type="text" name="HTMLlinksPerFile"value="<?php echo $setting['HTMLlinksPerFile']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">RSS Title :</td>
		<td><input type="text" name="RSSTitle" value="<?php echo $setting['RSSTitle']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">RSS Description :</td>
		<td><textarea rows="2" cols="30" name="RSSDescription"><?php echo $setting['RSSDescription']; ?></textarea></td>
	</tr>
	<tr>
		<td width="15%">HTML Template CSS :</td>
		<td><input type="text" name="HTMLStyleSheet" value="<?php echo $setting['HTMLStyleSheet']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">XML Change Frequency :</td>
		<td><input type="text" name="XMLchangeFrequncy" value="<?php echo $setting['XMLchangeFrequncy']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">HTML Title Prefix :</td>
		<td><input type="text" name="htmlTitlePrefix" value="<?php echo $setting['htmlTitlePrefix']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">HTML Title Suffix :</td>
		<td><input type="text" name="htmlTitleSuffix" value="<?php echo $setting['htmlTitleSuffix']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">Allow Meta For HTML :</td>
		<td>
		<input type="radio" name="HTMLAllowMeta" value="1" <?php if($setting['HTMLAllowMeta']=='1') echo  'checked="checked"';  ?> />Enabled
		<input type="radio" name="HTMLAllowMeta" value="0" <?php if($setting['HTMLAllowMeta']=='0') echo  'checked="checked"';  ?>/>Disabled
		</tr>
	<tr>
		<td width="15%">Name of Company :</td>
		<td><input type="text" name="company" value="<?php echo $setting['company']; ?>" /></td>
	</tr>
	<tr>
		<td width="15%">Period :</td>
		<td><input type="text" name="period" value="<?php echo $setting['period']; ?>" style="width: 30px" />Days</td>
	</tr>
	<tr>
		<td width="15%">HTML Footer :</td>
		<td><textarea rows="2" cols="30" name="HTMLFooter"><?php echo $setting['HTMLFooter']; ?></textarea></td>
	</tr>
	<tr>
		<td colspan="2">
		<input type="hidden" name="do" value="update" /> 
		<input type="submit" class="bgButton" value="Update" /></td>
	</tr>
</table>
</form>
</div>
<div style="border:1px solid #CCC;position:relative;top:35px;<?php if( isset( $_SESSION['updated'] ) ) echo "display:none;" ?>" id="iFrame"><iframe src="PSM.class.php" width="99%" height="300" frameborder="0"></iframe></div>
</body>
<script type="text/javascript">
<?php if( isset( $_SESSION['updated'] ) ) { ?>
$(document).ready(function(){
	$('#update').css({
		'background':'#FFF','border-bottom':'1px solid #FFF','color':''
		})
	$('#runEngin').css({
		'background':'#666','border-bottom':'1px solid #CCC','color':'#FFF'
	})	
})
<?php }?>
<?php if( isset( $_SESSION['updated'] ) ) { ?>
$(document).ready(function(){
	$('#message').fadeIn().delay(1800).fadeOut();
})
<?php }?>

$('#update').click(function(){
	$('#iFrame').slideUp();
	$('#updateForm').slideDown();
	$(this).css({
	'background':'#FFF','border-bottom':'1px solid #FFF','color':''
	})
	$('#runEngin').css({
	'background':'#666','border-bottom':'1px solid #CCC','color':'#FFF'
	})
})
$('#runEngin').click(function(){
	$('#iFrame').slideDown();
	$('#updateForm').slideUp();
	$(this).css({
	'background':'#FFF','border-bottom':'1px solid #FFF','color':''
	})
	$('#update').css({
	'background':'#666','border-bottom':'1px solid #CCC','color':'#FFF'
	})
})
</script>
</html>
<?php unset( $_SESSION['updated'], $_SESSION['message'] )?>