<?php
session_start ();
define ( 'PRSEXE', '1' );
if ( ( ! isset ( $_SESSION [ 'login' ] ) && ! isset ( $_POST [ 'token' ] ) ) || ( isset ( $_POST [ 'token' ] ) && $_SESSION [ 'token' ] != $_POST [ 'token' ] ) ) {
	include 'templates/login.tpl';
} elseif ( ! isset ( $_SESSION [ 'login' ] ) && isset ( $_POST [ 'token' ] ) && $_SESSION [ 'token' ] == $_POST [ 'token' ] ) {
	if ( false === file_exists ( 'license.ini' ) )
		die ( "Invalid License File" );
	$fp = file_get_contents ( 'license.ini' );
	$lines = explode ( "\r\n", $fp );
	if ( ! $lines )
		die ( "Invalid License File" );
	foreach ( $lines as $key => $element ) {
		if ( $key == 0 )
			list ( , $username ) = split ( "\=", $element );
		elseif ( $key == 1 )
			list ( , $password ) = split ( "\=", $element );
	}
	if ( ! $username || ! $password ) {
		die ( "Invalid License File" );
	}
	if ( $username == $_POST [ 'username' ] && $password == $_POST [ 'password' ] )
		$_SESSION [ 'login' ] = 1;
	else
		$_SESSION [ 'message' ] = 'Login Faild';
	header ( "Location:{$_SERVER['PHP_SELF']}" );
	exit ();
} elseif ( isset ( $_SESSION [ 'login' ] ) && @$_POST [ 'do' ] == 'update' ) {
	$fp = fopen ( 'sitemap.ini', 'w+' );
	foreach ( $_POST as $vars => $vals ) {
		if ( $vars == 'do' )
			continue;
		$tmpstr [] = trim ( $vars ) . '=' . trim ( is_array ( $vals ) ? join ( ",", $vals ) : $vals );
	}
	$confstr = join ( "\r\n", $tmpstr );
	fwrite ( $fp, $confstr );
	fclose ( $fp );
	header ( "Location:{$_SERVER['PHP_SELF']}" );
	$_SESSION [ 'updated' ] = 1;
	$_SESSION [ 'message' ] = 'Setting Updated';
	exit ();
} elseif ( isset ( $_SESSION [ 'login' ] ) && @$_GET [ 'do' ] == 'logout' ) {
	session_destroy ();
	header ( "Location:{$_SERVER['PHP_SELF']}" );
	exit ();
} elseif ( isset ( $_SESSION [ 'login' ] ) ) {
	if ( false === file_exists ( 'sitemap.ini' ) )
		die ( "Invalid Config File" );
	$fp = file_get_contents ( 'sitemap.ini' );
	$lines = explode ( "\r\n", $fp );
	if ( ! $lines )
		die ( "Invalid Config File" );
	foreach ( $lines as $line ) {
		list ( $var, $val ) = split ( "\=", $line );
		$setting [ $var ] = ( strstr ( $val, "," ) ? explode ( ",", $val ) : $val );
	}
	include 'templates/mainFrame.tpl';
}

?>