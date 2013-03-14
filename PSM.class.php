<?php
/**
 * Project:     Proshut Site Map Creator: the PHP compiling template engine
 * File:        PSM.class.php
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * @copyright 2011 - Proshut
 * @author Hamid Seyyedi <hamid.udc at gmail dot com> 
 * @package ProshutSiteMapper
 * @version S03.20.11.BT
 */
set_time_limit ( 0 );
class proshutSiteMapper {
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $host;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $protocol = 'http';
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $MACID;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $firstPage;
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $links = array ();
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $images = array ();
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $extrnalLinks = array ();
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $damagedLinks = array ();
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $index = array ();
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $allowExtensions = 'php|asp|aspx|html|htm';
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $matchs = "href|src";
	
	/**
	 * Enter description here ...
	 * @var integer
	 */
	public $startTime;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $time = '../sitemap/';
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $rootPath = '../sitemap/';
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $lastModified = array ();
	
	/**
	 * Enter description here ...
	 * @var integer
	 */
	public $offset = 0;
	
	/**
	 * Enter description here ...
	 * @var integer
	 */
	public $timer = 1;
	
	/**
	 * Enter description here ...
	 * @var integer
	 */
	public $redirectTimer = 1;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $content;
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $outputs = array ('UrlList', 'ImageXML', 'SiteXML', 'MobileXML', 'HTMLMap' );
	
	/**
	 * Enter description here ...
	 * @var integr
	 */
	public $linksPerFile = 1000;
	
	/**
	 * Enter description here ...
	 * @var integer
	 */
	public $RSSlinksPerFile = 100;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $RSSTitle;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $RSSDescription;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $XMLStyelSheetPath;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $HTMLStyleSheet = 'html.css';
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $XMLchangeFrequncy = 'daily';
	
	/**
	 * Enter description here ...
	 * @var integer
	 */
	public $HTMLlinksPerFile = 100;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $htmlTitlePrefix = '';
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $htmlTitleSuffix = '';
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $lostTitle = array ();
	
	/**
	 * Enter description here ...
	 * @var array
	 */
	public $HTMLAllowMeta = TRUE;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $company;
	
	/**
	 * Enter description here ...
	 * @var integer
	 */
	public $period = 24;
	
	/**
	 * Enter description here ...
	 * @var string
	 */
	public $HTMLFooter;
	
	public static $DBConnection;
	
	/**
	 * Enter description here ...
	 * @param unknown_type $url
	 */
	public function __construct($url) {
		
		try{
		new loadConfig ( 'sitemap.ini', $this, FALSE );
		}
		catch( Exception $e ){ die($e->getMessage()); }
		
		proshutSiteMapper::$DBConnection = new mysqlConnect ();
		$urlParts = proshutSiteMapper::analyzeUrl ( urldecode ( $url ) );
		$url = proshutSiteMapper::rebuildURL ( $urlParts );
		$this->startTime = time ();
		$this->redirectTimer = isset ( $_SESSION ['redirectTimer'] ) ? $_SESSION ['redirectTimer'] : 1;
		$this->host = $urlParts ['host'];
		$this->XMLStyelSheetPath = $this->rootPath . 'sitemap.xsl';
		$this->protocol = $urlParts ['protocol'];
		$this->MACID = sha1 ( $this->host );
		proshutSiteMapper::createTables ( proshutSiteMapper::$DBConnection->getLink (), $this->MACID );
		$this->checkPreviuos ();
		$this->time = file_exists ( $this->rootPath . "log" ) ? filectime ( $this->rootPath . "log" ) : time ();
		$this->reportPrevious ();
		if (@$_GET ['crawl']) {
			$this->checkStatus ();
			$this->loadLog ();
			$this->setDir ();
			$this->createLogFile ();
			proshutSiteMapper::createFramework ( $url );
			if (! isset ( $_SESSION ['startTime'] ))
				$this->crawlingUrl ( array ($url ) );
			$this->doCrawlLinks ();
		}
	
	}
	
	public function __call($method, $args) {
		
		if (! method_exists ( $this, $method )) {
			$tmpproperty = str_replace ( 'set_', '', $method );
			$this->$tmpproperty = $args [0];
		}
	}
	
	public function set_HTMLFooter($data) {
		$this->HTMLFooter = base64_decode ( $data );
	}
	
	public function createLogFile() {
		
		$file = $this->rootPath . 'log';
		if (! file_exists ( $file ))
			$fp = fopen ( $this->rootPath . 'log', 'w+' );
	
	}
	
	/**
	 * + Status Conditions :
	 * |--------- LT : Lost Titles
	 * |--------- NL : New Entries 
	 * |--------- PA : Process Again
	 */
	public function checkStatus() {
		
		switch ($_GET ['status']) {
			
			case "LT" :
				if (! $result = mysql_query ( "UPDATE sm_tmp_link_{$this->MACID} link LEFT JOIN sm_tmp_{$this->MACID} page ON page.title='' SET link.view = 0 WHERE link.url = page.url  " ))
					die ( mysql_error () );
				if (! $result = mysql_query ( "DELETE FROM `sm_tmp_{$this->MACID}` WHERE `title`=''" ))
					die ( mysql_error () );
				
				break;
			case "NL" :
				if (! $result = mysql_query ( "UPDATE sm_tmp_link_{$this->MACID} SET `view` = 0 WHERE `url` = '{$this->host}'  " ))
					die ( mysql_error () );
				break;
			case "PA" :
				if (! $result = mysql_query ( "DROP TABLE sm_tmp_{$this->MACID}  " ))
					die ( mysql_error () );
				if (! $result = mysql_query ( "DROP TABLE sm_tmp_link_{$this->MACID}  " ))
					die ( mysql_error () );
				@unlink ( $this->rootPath . 'log' );
				proshutSiteMapper::createTables ( proshutSiteMapper::$DBConnection->getLink (), $this->MACID );
				break;
		}
	
	}
	
	static public function createTables($db, $tbl) {
		
		$sql = "CREATE TABLE IF NOT EXISTS `sm_tmp_{$tbl}` (
				   `id` int(11) NOT NULL AUTO_INCREMENT,
	               `url` varchar(255) NOT NULL,
	               `title` varchar(255) NOT NULL,
	               `keyword` varchar(255) NOT NULL,
	               `description` text NOT NULL,
	               `abstract` text NOT NULL,
	               `lastMod` varchar(255) NOT NULL,
	               `images` text NOT NULL,
	               PRIMARY KEY (`id`),
                   UNIQUE KEY `url` (`url`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if (! mysql_query ( $sql, $db ))
			die ( mysql_error () );
		
		$sql = "CREATE TABLE IF NOT EXISTS `sm_tmp_link_{$tbl}` (
				   `id` int(11) NOT NULL AUTO_INCREMENT,
	               `url` varchar(255) NOT NULL,
	               PRIMARY KEY (`id`),
                   UNIQUE KEY `url` (`url`),
                   `view` tinyint(1) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if (! mysql_query ( $sql, $db ))
			die ( mysql_error () );
	}
	
	/**
	 * Enter description here ...
	 * @param string $urls
	 * @return Ambigous <multitype:, boolean, string>
	 */
	public function getRemoteFile($urls) {
		
		$mh = curl_multi_init ();
		$handles = array ();
		
		foreach ( $urls as $url ) {
			$handles [$url] = curl_init ( $url );
			
			curl_setopt ( $handles [$url], CURLOPT_TIMEOUT, 3 );
			curl_setopt ( $handles [$url], CURLOPT_AUTOREFERER, true );
			curl_setopt ( $handles [$url], CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $handles [$url], CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt ( $handles [$url], CURLOPT_SSL_VERIFYPEER, false );
			
			curl_multi_add_handle ( $mh, $handles [$url] );
		}
		
		$running = null;
		
		do {
			curl_multi_exec ( $mh, $running );
		} while ( $running > 0 );
		
		foreach ( $handles as $key => $value ) {
			$handles [$key] = false;
			
			if (curl_errno ( $value ) === 0) {
				$handles [$key] = curl_multi_getcontent ( $value );
			} else {
				$this->setDamagedLinks ( $key );
				mysql_query ( "DELETE FROM sm_tmp_link_{$this->MACID} WHERE `url`='{$key}' ", proshutSiteMapper::$DBConnection->getLink () );
			}
			
			curl_multi_remove_handle ( $mh, $value );
			curl_close ( $value );
		}
		
		curl_multi_close ( $mh );
		
		return $handles;
	}
	
	/**
	 * Enter description here ...
	 * @param array $links
	 */
	public function crawlingUrl(array $links) {
		
		foreach ( $links as $link ) {
			$redirctPage = $this->isRedirect ( $link );
			if ($redirctPage === FALSE)
				$url [] = $link;
			elseif ($redirctPage === NULL)
				continue;
			else
				$url [] = $redirctPage;
		
		}
		if (! isset ( $url ))
			return;
		$contents = $this->getRemoteFile ( $url );
		if ($contents) {
			$i = 0;
			foreach ( $contents as $key => $data ) {
				$meta = proshutSiteMapper::getMetaTags ( $data );
				
				$target = $map = array ();
				proshutSiteMapper::findLinks ( $data, $target, $map, $this->matchs );
				$urlParts = proshutSiteMapper::analyzeUrl ( $key );
				foreach ( $target as $links ) {
					if ( strstr ( strtolower ( $links ['linkcode'] ), 'rel="nofollow"' ))
						continue;
					if (strlen ( $links ['link_raw'] ) < 3)
						continue;
					$address = proshutSiteMapper::rebuildURL ( proshutSiteMapper::buildURL ( $links ['link_raw'], $urlParts ) );
					
					if (FALSE === proshutSiteMapper::isExternalLinks ( $address )) {
						if (proshutSiteMapper::isImage ( $address )) {
							if (! in_array ( $address, $this->images ? $this->images : array () )) {
								$this->images [] = $address;
								$pageImages [] = array ($address, proshutSiteMapper::getCaption ( $links ['linkcode'] ) );
							}
						} else if (! in_array ( $address, $this->damagedLinks ? $this->damagedLinks : array () ))
							$validAddress [] = $address;
					} else {
						if (! in_array ( $address, $this->extrnalLinks ? $this->extrnalLinks : array () ))
							$this->extrnalLinks [] = $address;
					
					}
				}
				$this->addToLinks ( $validAddress );
				$collectData [$i] ['key'] = $key;
				$collectData [$i] ['keyword'] = isset ( $meta ['keywords'] ) ? $meta ['keywords'] : '';
				$collectData [$i] ['description'] = isset ( $meta ['description'] ) ? $meta ['description'] : '';
				$collectData [$i] ['abstract'] = isset ( $meta ['abstract'] ) ? $meta ['abstract'] : '';
				$collectData [$i] ['title'] = proshutSiteMapper::getTitle ( $data );
				$collectData [$i] ['lastModified'] = @$this->lastModified [$key];
				$collectData [$i] ['images'] = isset ( $pageImages ) ? $pageImages : '';
				proshutSiteMapper::writeOnFile ( $collectData, $this->MACID );
				$i ++;
			}
		}
	}
	
	/**
	 * Enter description here ...
	 */
	public function doCrawlLinks() {
		
		$diff = time () - $this->startTime;
		$duration = 120;
		if ($diff > 200)
			$duration = 180;
		
		if ($diff > ($duration * $this->redirectTimer)) {
			$this->redirectTimer ++;
			$this->updateLogFile ();
			sleep ( 1 );
			$_SESSION ['startTime'] = $this->startTime;
			$_SESSION ['redirectTimer'] = $this->redirectTimer;
			$_SESSION ['endTime'] = time ();
			echo "<script type=\"text/javascript\">window.location='" . $_SERVER ['PHP_SELF'] . "?crawl=1'</script>";
			flush ();
		}
		
		if (! $result = mysql_query ( "SELECT `id`,`url` FROM `sm_tmp_link_{$this->MACID}` WHERE `view` = 0 ORDER BY `id` ASC LIMIT 5 " ))
			die ( mysql_error () );
		
		if (mysql_num_rows ( $result ) < 1) {
			
			$this->factory ();
		
		} else {
			
			while ( $row = mysql_fetch_object ( $result ) ) {
				$urls [] = $row->url;
				$marked [] = $row->id;
			}
			$this->crawlingUrl ( $urls, TRUE );
		}
		mysql_free_result ( $result );
		$this->flushOutput ();
		if (! $result = mysql_query ( "UPDATE `sm_tmp_link_{$this->MACID}` SET `view` = 1 WHERE `id` IN (" . join ( ",", $marked ) . ") " ))
			die ( mysql_error () );
		$this->doCrawlLinks ();
	
	}
	
	public function addToLinks($addresses) {
		
		if (! $addresses)
			return;
		$sql = "INSERT IGNORE INTO `sm_tmp_link_{$this->MACID}` ( `url` ) VALUES ";
		for($i = 0; $i < sizeof ( $addresses ); $i ++) {
			$sqls [] = " ( '" . $addresses [$i] . "'  ) ";
		}
		
		mysql_query ( $sql . join ( ",", $sqls ) . ";" );
		
	
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $url
	 * @return NULL|Ambigous <string, boolean>
	 */
	public function isRedirect($url) {
		
		$urlParts = proshutSiteMapper::analyzeUrl ( $url );
		if (strstr ( $urlParts ['file'], '.' )) {
			list ( $name, $extention ) = split ( "\.", $urlParts ['file'] );
			if (trim ( $name ) == '' || trim ( $extention ) == '')
				return NULL;
		}
		
		$opts = array ('http' => array ('max_redirects' => 1, 'ignore_errors' => 1, 'timeout' => 10 ) );
		stream_context_get_default ( $opts );
		$headers = get_headers ( $url, true );
		if (! $headers) {
			mysql_query ( "DELETE FROM sm_tmp_link_{$this->MACID} WHERE `url`='{$url}' ", proshutSiteMapper::$DBConnection->getLink () );
			return NULL;
		}
		
		$status = $headers [0];
		list ( $protocol, $code, $message ) = split ( ' ', $status, 3 );
		if (! $headers)
			$this->setDamagedLinks ( $url );

		if (! strstr ( $headers ['Content-Type'], 'text/html' ) || $code > 400) {
			$this->setDamagedLinks ( $url );
			mysql_query ( "DELETE FROM sm_tmp_link_{$this->MACID} WHERE `url`='{$url}' ", proshutSiteMapper::$DBConnection->getLink () );
			return NULL;
		}
		$this->lastModified [$url] = ( isset ( $headers ['Last-Modified'] ) &&  $headers ['Last-Modified'] ) ? date ( 'Y-m-d\TH:i:s', strtotime ( $headers ['Last-Modified'] ) ) . '+00:00' : date ( 'Y-m-d\TH:i:s', time () ) . '+00:00';
		$opts = array ('http' => array ('max_redirects' => 20, 'ignore_errors' => 0 ) );
		stream_context_get_default ( $opts );
		if ($code >= 300 && $code < 400) {
			if (FALSE === proshutSiteMapper::isExternalLinks ( $headers ['Location'] )) {
				$tmpheader = get_headers ( $headers ['Location'], true );
				list ( , $tmpcode, ) = split ( ' ', $tmpheader [0], 3 );
				if ($tmpcode == 200)
					return $headers ['Location'];
				else {
					mysql_query ( "DELETE FROM sm_tmp_link_{$this->MACID} WHERE `url`='{$url}' ", proshutSiteMapper::$DBConnection->getLink () );
					return NULL;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $source
	 * @param unknown_type $target_array
	 * @param unknown_type $map_array
	 * @param unknown_type $match_part
	 */
	static public function findLinks(&$source, &$target_array, &$map_array, $match_part) {
		
		$all_links = array ();
		preg_match_all ( "/<[ ]{0,}a[ \n\r][^<>]{0,}(?<= |\n|\r)(?:" . $match_part . ")[ \n\r]{0,}=[ \n\r]{0,}[\"|']{0,1}([^\"'>< ]{0,})[^<>]{0,}>((?:(?!<[ \n\r]*\/a[ \n\r]*>).)*)<[ \n\r]*\/a[ \n\r]*>/ is", $source, $regs );
		
		for($x = 0; $x < count ( $regs [1] ); $x ++) {
			$tmp_array ["link_raw"] = trim ( $regs [1] [$x] );
			$tmp_array ["linktext"] = $regs [2] [$x];
			$tmp_array ["linkcode"] = trim ( $regs [0] [$x] );
			$map_key = $tmp_array ["link_raw"];
			if (! isset ( $map_array [$map_key] )) {
				$target_array [] = $tmp_array;
				$map_array [$map_key] = true;
			}
		}
		$pregs [] = "/<[^<>]{0,}[ \n\r](?:" . $match_part . ")[ \n\r]{0,}=[ \n\r]{0,}[\"|']{0,1}([^\"'>< ]{0,})[^<>]{0,}>/ is";
		$pregs [] = "/[ \.:;](?:" . $match_part . ")[ \n\r]{0,}[=|\(][ \n\r]{0,}[\"|']{0,1}([^\"'>< ;]{0,})['\"<> ;]/ is";
		
		for($x = 0; $x < count ( $pregs ); $x ++) {
			unset ( $regs );
			preg_match_all ( $pregs [$x], $source, $regs );
			for($y = 0; $y < count ( $regs [1] ); $y ++) {
				unset ( $tmp_array );
				$tmp_array ["link_raw"] = trim ( $regs [1] [$y] );
				$tmp_array ["linkcode"] = trim ( $regs [0] [$y] );
				$tmp_array ["linktext"] = "";
				$map_key = $tmp_array ["link_raw"];
				if (! isset ( $map_array [$map_key] )) {
					$target_array [] = $tmp_array;
					$map_array [$map_key] = true;
				}
			}
		}
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $url
	 * @return number
	 */
	static function analyzeUrl(&$url) {
		
		preg_match ( '/^.{0,10}:\/\//', $url, $match );
		isset ( $match [0] ) ? $protocol = $match [0] : $protocol = '';
		
		$url_tmp = substr ( $url, strlen ( $protocol ) );
		preg_match ( '/(^[^\/\?#]{1,})/', $url_tmp, $match );
		isset ( $match [1] ) ? $host_complete = $match [1] : $host_complete = '';
		
		$url_tmp = substr ( $url_tmp, strlen ( $host_complete ) );
		preg_match ( '#^[^?\#]{0,}/#', $url_tmp, $match );
		isset ( $match [0] ) ? $path = $match [0] : $path = '';
		
		$url_tmp = substr ( $url_tmp, strlen ( $path ) );
		preg_match ( '#^[^?\#]*#', $url_tmp, $match );
		isset ( $match [0] ) ? $file = $match [0] : $file = '';
		
		$url_tmp = substr ( $url_tmp, strlen ( $file ) );
		preg_match ( '/^\?[^#]*/', $url_tmp, $match );
		isset ( $match [0] ) ? $query = $match [0] : 

		$query = '';
		preg_match ( "#^.*@#", $host_complete, $match );
		if (isset ( $match [0] ))
			$auth_login = $match [0];
		if (isset ( $auth_login ))
			$host_complete = substr ( $host_complete, strlen ( $auth_login ) );
		preg_match ( "#[^:]*#", $host_complete, $match );
		isset ( $match [0] ) ? $host = $match [0] : $host = "";
		preg_match ( "#:([^:]*$)#", $host_complete, $match );
		if (isset ( $match [1] ))
			$port = ( int ) $match [1];
		$parts = explode ( ".", $host );
		if (count ( $parts ) <= 2)
			$domain = $host;
		else {
			$pos = strpos ( $host, "." );
			$domain = substr ( $host, $pos + 1 );
		}
		if ($protocol == "")
			$protocol = "http://";
		if (! isset ( $port )) {
			if ($protocol == "http://")
				$port = 80;
			if ($protocol == "https://")
				$port = 443;
		}
		if ($path == "")
			$path = "/";
		$urlParts ["protocol"] = $protocol;
		$urlParts ["host"] = $host;
		$urlParts ["path"] = $path;
		$urlParts ["file"] = $file;
		$urlParts ["query"] = $query;
		$urlParts ["domain"] = $domain;
		$urlParts ["port"] = $port;
		
		return $urlParts;
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $url
	 * @return Ambigous <string, unknown>
	 */
	static public function normalizeURL($url) {
		
		$urlParts = proshutSiteMapper::normalizeURL ( $url );
		if ($urlParts ["protocol"] == "http://" && $urlParts ["port"] == 80 || $urlParts ["protocol"] == "https://" && $urlParts ["port"] == 443)
			$urlRebuild = $urlParts ["protocol"] . $urlParts ["host"] . $urlParts ["path"] . $urlParts ["file"] . $urlParts ["query"];
		else
			$urlRebuild = $url;
		return $urlRebuild;
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $urlParts
	 * @return string
	 */
	static function rebuildURL($urlParts) {
		
		if (! isset ( $urlParts ["path"] ))
			$urlParts ["path"] = "";
		if (! isset ( $urlParts ["file"] ))
			$urlParts ["file"] = "";
		if (! isset ( $urlParts ["query"] ))
			$urlParts ["query"] = "";
		$url = $urlParts ["protocol"] . $urlParts ["host"] . $urlParts ["path"] . $urlParts ["file"] . $urlParts ["query"];
		return $url;
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $link
	 * @param unknown_type $urlPartsActual
	 * @return number
	 */
	static public function buildURL($link, $urlPartsActual) {
		
		$entities = array ("'&(quot|#34);'i", "'&(amp|#38);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i", "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i", "'&(copy|#169);'i" );
		$replace = array ("\"", "&", "<", ">", " ", chr ( 161 ), chr ( 162 ), chr ( 163 ), chr ( 169 ) );
		$link = preg_replace ( "/^(.{1,})#.{0,}$/", "\\1", $link );
		if (substr ( $link, 0, 2 ) == "//") {
			$link = "http:" . $link;
			$link = proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $link ) );
		} elseif (substr ( $link, 0, 1 ) == "/")
			$link = $urlPartsActual ["protocol"] . $urlPartsActual ["host"] . ":" . $urlPartsActual ["port"] . $link;
		elseif (substr ( $link, 0, 2 ) == "./")
			$link = $urlPartsActual ["protocol"] . $urlPartsActual ["host"] . ":" . $urlPartsActual ["port"] . $urlPartsActual ["path"] . substr ( $link, 2 );
		elseif (preg_match ( '/^[^\/]{1,}(:\/\/)/', $link )) {
			if (substr ( $link, 0, 7 ) == "http://" || substr ( $link, 0, 8 ) == "https://")
				$link = proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $link ) );
			else
				$link = "";
		} elseif (preg_match ( '/^[a-zA-Z]{0,}:[^\/]{0,1}/', $link ))
			$link = "";
		elseif (substr ( $link, 0, 3 ) == "../") {
			$new_path = $urlPartsActual ["path"];
			while ( substr ( $link, 0, 3 ) == "../" ) {
				$new_path = preg_replace ( '/\/[^\/]{0,}\/$/', "/", $new_path );
				$link = substr ( $link, 3 );
			}
			$link = $urlPartsActual ["protocol"] . $urlPartsActual ["host"] . ":" . $urlPartsActual ["port"] . $new_path . $link;
		} elseif (substr ( $link, 0, 1 ) == "#")
			$link = "";
		else
			$link = $urlPartsActual ["protocol"] . $urlPartsActual ["host"] . ":" . $urlPartsActual ["port"] . $urlPartsActual ["path"] . $link;
		$link = preg_replace ( $entities, $replace, $link );
		$link = proshutSiteMapper::analyzeUrl ( $link );
		
		return $link;
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $link
	 * @return number
	 */
	static public function isImage($link) {
		
		return preg_match ( '/^.*\.(jpg|jpeg|png|gif)$/i', $link );
	}
	
	static public function getCaption($tag) {
		
		preg_match_all ( '/alt="([^"]*)"/', $tag, $img );
		if (isset ( $img [1] [0] ))
			return $img [1] [0];
		else
			return '';
	}
	
	public function setDamagedLinks($link) {
		
		$fp = fopen ( $this->rootPath . 'damagedLinks.tmp', 'a+' );
		fwrite ( $fp, $link . "\r\n" );
		fclose ( $fp );
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $link
	 * @param unknown_type $actualUrl
	 * @return boolean
	 */
	function isExternalLinks($link) {
		
		$urlPartsLink = proshutSiteMapper::analyzeUrl ( $link );
		$hostLink = preg_replace ( '/^www\./', '', $urlPartsLink ["host"] );
		$hostActual = preg_replace ( '/^www\./', '', $this->host );
		if ($hostActual != $hostLink)
			return TRUE;
		else
			return FALSE;
	
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $url
	 */
	static public function createFramework($url) {
		
		echo "
		
		<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\" style=\"font-family:tahoma;font-size:11px\">
		  <tr>
		    <td width=\"15%\" style=\"border-bottom:1px solid #CCC\"><b>Host :</b></td>
		    <td width=\"85%\" style=\"border-bottom:1px solid #CCC\"><div id=\"host\">" . $url . "</div></td>
		  </tr>  
		  <tr>
		    <td style=\"border-bottom:1px solid #CCC\"><b>Index Page Number :</b></td>
		    <td style=\"border-bottom:1px solid #CCC\"><div id=\"pageIndex\">Loading ...</div></td>
		  </tr>
		  <tr>
		    <td style=\"border-bottom:1px solid #CCC\"><b>Index Image Number :</b></td>
		    <td style=\"border-bottom:1px solid #CCC\"><div id=\"imageIndex\">Loading ...</div></td>
		  </tr>
		  <tr>
		    <td style=\"border-bottom:1px solid #CCC\"><b>Crawled Page Number :</b></td>
		    <td style=\"border-bottom:1px solid #CCC\"><div id=\"crawledPage\">Loading ...</div></td>
		  </tr>
		  <tr>
		    <td style=\"border-bottom:1px solid #CCC\"><b>Invalid Address :</b></td>
		    <td style=\"border-bottom:1px solid #CCC\"><div id=\"invalidAddress\">Loading ...</div></td>
		  </tr>	  
		  <tr>
		    <td style=\"border-bottom:1px solid #CCC\"><b>Time Left :</b></td>
		    <td style=\"border-bottom:1px solid #CCC\"><div id=\"timeLeft\"></div></td>
		  </tr>
		  <tr>
		    <td style=\"border-bottom:1px solid #CCC\"><b>Status : </b></td>
		    <td style=\"border-bottom:1px solid #CCC\"><div id=\"status\">Crawling ... </div></td>
		  </tr>
		</table>";
	
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $integer
	 * @return string
	 */
	static public function timeLeft($integer) {
		
		$seconds = $integer;
		if ($seconds / 60 >= 1) {
			$minutes = floor ( $seconds / 60 );
			if ($minutes / 60 >= 1) { # Hours
				$hours = floor ( $minutes / 60 );
				if ($hours / 24 >= 1) { #days
					$days = floor ( $hours / 24 );
					if ($days / 7 >= 1) { #weeks
						$weeks = floor ( $days / 7 );
						if ($weeks >= 2)
							$return = "$weeks Weeks";
						else
							$return = "$weeks Week";
					} #end of weeks
					$days = $days - (floor ( $days / 7 )) * 7;
					if (@$weeks >= 1 && $days >= 1)
						$return = @$return . ", ";
					if ($days >= 2)
						$return = @$return . "$days days";
					if ($days == 1)
						$return = @$return . " $days day";
				} #end of days
				$hours = $hours - (floor ( $hours / 24 )) * 24;
				if ($days >= 1 && $hours >= 1)
					$return = @$return . ", ";
				if ($hours >= 2)
					$return = @$return . "$hours hours";
				if ($hours == 1)
					$return = @$return . " $hours hour";
			} #end of Hours
			$minutes = $minutes - (floor ( $minutes / 60 )) * 60;
			if (@$hours >= 1 && $minutes >= 1)
				$return = @$return . ", ";
			if ($minutes >= 2)
				$return = @$return . "$minutes minutes";
			if ($minutes == 1)
				$return = @$return . "$minutes minute";
		} #end of minutes
		$seconds = $integer - (floor ( $integer / 60 )) * 60;
		if (@$minutes >= 1 && $seconds >= 1)
			$return = @$return . ", ";
		if ($seconds >= 2)
			$return = @$return . " $seconds seconds";
		if ($seconds == 1)
			$return = @$return . "$seconds second";
		return $return;
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $content
	 * @return Ambigous <string, multitype:>
	 */
	static public function getMetaTags($content) {
		
		$content = strtolower ( $content );
		$content = preg_replace ( "'<style[^>]*>.*</style>'siU", '', $content ); // strip js 
		$content = preg_replace ( "'<script[^>]*>.*</script>'siU", '', $content ); // strip css 
		preg_match_all ( "/<meta[^>]+(http\-equiv|name)=\"([^\"]*)\"[^>]" . "+content=\"([^\"]*)\"[^>]*>/i", $content, $out );
		
		if (isset ( $out ) && $out)
			for($i = 0; $i < count ( $out [2] ); $i ++)
				$meta [$out [2] [$i]] = strstr ( $out [3] [$i], ';' ) ? explode ( ';', $out [3] [$i] ) : $out [3] [$i];
		return (isset ( $meta ) ? $meta : '');
	}
	
	/**
	 * Enter description here ...
	 */
	public function callRegisteredShutdown() {
		
		if ($this->links)
			$this->doCrawlLinks ();
	}
	
	/**
	 * Enter description here ...
	 */
	public function updateLogFile() {
		
		file_put_contents ( $this->rootPath . "{$this->MACID}_LOG_IMAGE.tmp", join ( "\r\n", $this->images ) );
		file_put_contents ( $this->rootPath . "{$this->MACID}_LOG_ELINK.tmp", join ( "\r\n", $this->extrnalLinks ) );
		file_put_contents ( $this->rootPath . "{$this->MACID}_LOG_DLINK.tmp", join ( "\r\n", $this->damagedLinks ) );
	
	}
	
	/**
	 * Enter description here ...
	 */
	public function loadLog() {
		
		if (file_exists ( $this->rootPath . "{$this->MACID}_LOG_INDEX.tmp" )) {
			$this->images = explode ( "\r\n", file_get_contents ( $this->rootPath . "{$this->MACID}_LOG_IMAGE.tmp" ) );
			$this->extrnalLinks = explode ( "\r\n", file_get_contents ( $this->rootPath . "{$this->MACID}_LOG_ELINK.tmp" ) );
			$this->damagedLinks = explode ( "\r\n", file_get_contents ( $this->rootPath . "{$this->MACID}_LOG_DLINK.tmp" ) );
			$this->flushOutput ();
		
		}
	}
	
	private function clearPreviuosVersion() {
		
		if (file_exists ( $path = $_SERVER ['DOCUMENT_ROOT'] . '/sitemap.xml' )) {
			unlink ( $path );
			$directory = scandir ( $this->rootPath );
			foreach ( $directory as $file )
				if ($file != '.' && $file != '..')
					unlink ( $this->rootPath . $file );
		}
	
	}
	
	/**
	 * Enter description here ...
	 */
	public function clearLogs() {
		
		$files = glob ( $this->rootPath . "{$this->MACID}_LOG_*.tmp" );
		if ($files) {
			foreach ( $files as $file )
				unlink ( $file );
		}
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $event
	 */
	public function flushOutput($event = 'Crawling ...') {
		
		if (! $result = mysql_query ( "SELECT ( SELECT COUNT(`id`) FROM `sm_tmp_link_{$this->MACID}` ) as total , (  SELECT COUNT(`id`) FROM `sm_tmp_link_{$this->MACID}` WHERE `view` = 1 ) as craweled " ))
			die ( mysql_error () );
		
		$row = mysql_fetch_object ( $result );
		mysql_free_result ( $result );
		echo "<script type=\"text/javascript\">document.getElementById('pageIndex').innerHTML = \"" . $row->total . "\";
											       document.getElementById('crawledPage').innerHTML = \"" . $row->craweled . "\";
											       document.getElementById('imageIndex').innerHTML = \"" . count ( @$this->images ) . "\";
											       document.getElementById('status').innerHTML = \"" . $event . "\";
											       document.getElementById('timeLeft').innerHTML = \"" . proshutSiteMapper::timeLeft ( time () - $this->time ) . "\";
											       document.getElementById('invalidAddress').innerHTML = \"" . count ( $this->damagedLinks ) . "\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $data
	 * @return unknown|string
	 */
	static public function getTitle($data) {
		
		$parts = preg_match ( "#<title>(.+)<\/title>#iU", $data, $matches );
		if (isset ( $matches [1] )){
			return $matches [1];
		}else
			return '';
	}
	
	/**
	 * Enter description here ...
	 */
	public function setDir() {
		
		if (! is_dir ( $this->rootPath ))
			mkdir ( $this->rootPath );
	
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $url
	 * @param unknown_type $title
	 * @param unknown_type $keywords
	 * @param unknown_type $description
	 * @param unknown_type $abstract
	 * @param unknown_type $lm
	 * @param unknown_type $images
	 */
	public function writeOnFile($data, $ID) {
		
		$sql = "INSERT IGNORE INTO `sm_tmp_$ID` ( `url`, `title`, `keyword`, `description`, `abstract`, `lastMod`, `images` ) VALUES ";
		for($i = 0; $i < sizeof ( $data ); $i ++) {
			if ($data [$i] ['images']) {
				foreach ( $data [$i] ['images'] as $link )
					$images = $link [0] . chr ( 22 ) . $link [1] . "\r\n";
			}
			$sqls [] = " ( '" . $data [$i] ['key'] . "' ,'" . $data [$i] ['title'] . "' ,'" . $data [$i] ['keyword'] . "' ,
						   '" . $data [$i] ['description'] . "' ,'" . $data [$i] ['abstract'] . "' ,'" . $data [$i] ['lastModified'] . "' ,'" . @$images . "'  ) ";
		}
		mysql_query ( $sql . join ( ",", $sqls ) . ";" );
	
	}
	
	/**
	 * Enter description here ...
	 */
	public function parseLogFile() {
		
		echo "<script type=\"text/javascript\">document.getElementById('status').innerHTML = \"Parsing Temprary Files ...\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
		
		$this->createLostTitles ();
		
		echo "<script type=\"text/javascript\"> document.getElementById('status').innerHTML = \"Filtering Lost Titles ...\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
		
		$sql = "SELECT COUNT(`url`) as total FROM `sm_tmp_{$this->MACID}` WHERE `title`!='' ";
		if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
			die ( mysql_error () );
		} else {
			$row = mysql_fetch_object ( $result );
			$this->content = $row->total;
		
		}
		mysql_free_result ( $result );
		if ($this->lostTitle)
			echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:12px;font-weight:bold;color:#900;border-bottom:1px solid #CCC">' . count ( $this->lostTitle ) . ' Link(s) are lost titles - Index Page Refreshed - [ <a href="' . $this->rootPath . 'lostTitle.tmp" >View</a> ] </div>';
		
		echo "<script type=\"text/javascript\">document.getElementById('pageIndex').innerHTML = \"" . ($this->content) . "\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
		
		foreach ( $this->outputs as $method )
			call_user_method ( 'create' . ucfirst ( $method ), $this );
		
		if (file_exists ( $this->rootPath . 'damagedLinks.tmp' )) {
			echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:12px;font-weight:bold;color:#900;border-bottom:1px solid #CCC"> Damaged Links - [ <a href="' . $this->rootPath . 'damagedLinks.tmp" >View</a> ] </div>';
			echo str_pad ( " ", 4096 );
			flush ();
		
		}
	
	}
	
	public function createLostTitles() {
		
		$sql = "SELECT `url` FROM `sm_tmp_{$this->MACID}` WHERE `title`='' ";
		if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
			die ( mysql_error () );
		} else {
			while ( $row = mysql_fetch_object ( $result ) ) {
				$this->lostTitle [] = $row->url;
			}
		
		}
		mysql_free_result ( $result );
		if (! $this->lostTitle)
			return;
		$fp = fopen ( $this->rootPath . 'lostTitle.tmp', 'w' );
		fwrite ( $fp, join ( "\r\n", $this->lostTitle ) );
		fclose ( $fp );
	
	}
	
	/**
	 * Enter description here ...
	 */
	public function createUrlList() {
		
		$total = ceil ( $this->content / $this->linksPerFile );
		
		for($i = 0; $i < $total; $i ++) {
			
			$startNo = $i * $this->linksPerFile;
			
			$sql = "SELECT `url` FROM `sm_tmp_{$this->MACID}` WHERE `title` !='' LIMIT {$startNo},{$this->linksPerFile} ";
			if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
				die ( mysql_error () );
			} else {
				while ( $row = mysql_fetch_object ( $result ) ) {
					$content [] = $row->url;
				}
			
			}
			mysql_free_result ( $result );
			if ($total > 1)
				$fp = fopen ( $this->rootPath . (! $i ? 'url.txt' : 'url_' . $i . '.txt'), 'w' );
			else
				$fp = fopen ( $this->rootPath . (! $i ? 'url.txt' : 'url.txt'), 'w' );
			fwrite ( $fp, join ( "\r\n", $content ) );
			fclose ( $fp );
			$content = array();
		
		}
		echo "<script type=\"text/javascript\"> document.getElementById('status').innerHTML = \"URL created\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	}
	
	/**
	 * Enter description here ...
	 */
	
	public function imageNode($content, $i = FALSE) {
		
		$data = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
		if ($this->XMLStyelSheetPath)
			$data .= '<?xml-stylesheet type="text/xsl" href="../../' . $this->XMLStyelSheetPath . '"?>' . "\r\n";
		$data .= '<urlset' . "\r\n";
		$data .= '    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\r\n";
		$data .= '    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\r\n";
		$data .= '    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' . "\r\n";
		$data .= '    xsi:schemaLocation="' . "\r\n";
		$data .= '        http://www.sitemaps.org/schemas/sitemap/0.9' . "\r\n";
		$data .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\r\n";
		
		foreach ( $content as $url ) {
			$data .= '    <url>' . "\r\n";
			$data .= '    <loc>' . str_replace ( array ('&', '\'', '"', '>', '<' ), array ('&amp;', '&apos;', '&quot;', '&gt;', '&lt;' ), $url ['loc'] ) . '</loc>' . "\r\n";
			$data .= '    <lastmod>' . $url ['lastMod'] . '</lastmod>' . "\r\n";
			$data .= '    <changefreq>' . $url ['changefreq'] . '</changefreq>' . "\r\n";
			$data .= '    <priority>' . $url ['priority'] . '</priority>' . "\r\n";
			if (isset ( $url ['images'] )) {
				foreach ( $url ['images'] as $image ) {
					$data .= '    <image:image>' . "\r\n";
					$data .= '        <image:loc>' . str_replace ( array ('&', '\'', '"', '>', '<' ), array ('&amp;', '&apos;', '&quot;', '&gt;', '&lt;' ), $image ['loc'] ) . '</image:loc>' . "\r\n";
					$data .= '        <image:caption>' . str_replace ( array ('&', '\'', '"', '>', '<' ), array ('&amp;', '&apos;', '&quot;', '&gt;', '&lt;' ), $image ['caption'] ) . '</image:caption>' . "\r\n";
					$data .= '    </image:image>' . "\r\n";
				}
			}
			$data .= '    </url>' . "\r\n";
		}
		$data .= '</urlset>' . "\r\n";
		
		$fp = fopen ( $this->rootPath . (FALSE === $i ? 'img.xml' : 'img_' . $i . '.xml'), 'w' );
		fwrite ( $fp, $data );
		fclose ( $fp );
	}
	
	public function createImageXML() {
		
		$sql = "SELECT COUNT(`url`) as total FROM `sm_tmp_{$this->MACID}` WHERE `images` !=''";
		if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
			die ( mysql_error () );
		} else {
			$row = mysql_fetch_object ( $result );
			$rowsCount = $row->total;
		
		}
		mysql_free_result ( $result );
		$total = ceil ( $rowsCount / $this->linksPerFile );
		
		for($i = 0; $i < $total; $i ++) {
			
			$startNo = $i * $this->linksPerFile;
			$sql = "SELECT `url`,`images`,`lastMod` FROM `sm_tmp_{$this->MACID}` WHERE `images` !='' LIMIT {$startNo},{$this->linksPerFile} ";
			if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
				die ( mysql_error () );
			} else {
				while ( $row = mysql_fetch_object ( $result ) ) {
					$content [$row->url] = array( $row->images , $row->lastMod );
				}
			
			}
			mysql_free_result ( $result );
		}
		$t = 0;
		foreach ( $content as $url => $imgs ) {
			$tmpArr = explode ( "\r\n", $imgs[0] );
			foreach ( $tmpArr as $key => $address ) {
				if ( $address ) {
					$images [ $t ] [ 'url' ] = $url;
					$images [ $t ] [ 'image' ] = $address;
					$images [ $t ] [ 'lastMod' ] = $imgs [ 1 ];
				}
				$t ++;
			}
		}
		$i = 1;
		$currentFileSuffix = 0;
		foreach ( $images as $item ) {
			if ($i % $this->linksPerFile == 0)
				$currentFileSuffix ++;
			$urls [$i] ['loc'] = $item ['url'];
			$urls [$i] ['lastMod'] = $item ['lastMod'];
			$urls [$i] ['changefreq'] = $this->XMLchangeFrequncy;
			$i == 0 ? $urls [$i] ['priority'] = '1' : $urls [$i] ['priority'] = '0.5';
			$img = explode ( chr ( 22 ), $item ['image'] );
			$urls [$i] ['images'] [$i] ['loc'] = $img [0];
			$urls [$i] ['images'] [$i] ['caption'] = @$img [1];
			$i ++;
		}
		if (! $urls)
			return;
			
		if ($currentFileSuffix != 0) {
			$chunks = @array_chunk ( $urls, $this->linksPerFile );
			
			$i = 0;
			foreach ( $chunks as $content ) {
				$this->imageNode ( $content, $i );
				$i ++;
			}
		
		} else
			$this->imageNode ( $urls );
		
		echo "<script type=\"text/javascript\"> document.getElementById('status').innerHTML = \"IMAGE created\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	}
	
	public function siteNode($content, $i = FALSE, $filename = FALSE) {
		
		$data = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
		if ($this->XMLStyelSheetPath)
			$data .= '<?xml-stylesheet type="text/xsl" href="../../' . $this->XMLStyelSheetPath . '"?>' . "\r\n";
		$data .= '<urlset' . "\r\n";
		$data .= '    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\r\n";
		$data .= '    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\r\n";
		$data .= '    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' . "\r\n";
		$data .= '    xsi:schemaLocation="' . "\r\n";
		$data .= '        http://www.sitemaps.org/schemas/sitemap/0.9' . "\r\n";
		$data .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\r\n";
		foreach ( $content as $url ) {
			$data .= '    <url>' . "\r\n";
			$data .= '    <loc>' . str_replace ( array ('&', '\'', '"', '>', '<' ), array ('&amp;', '&apos;', '&quot;', '&gt;', '&lt;' ), $url ['loc'] ) . '</loc>' . "\r\n";
			$data .= '    <lastmod>' . $url ['lastmod'] . '</lastmod>' . "\r\n";
			$data .= '    <changefreq>' . $url ['changefreq'] . '</changefreq>' . "\r\n";
			$data .= '    <priority>' . $url ['priority'] . '</priority>' . "\r\n";
			$data .= '    </url>' . "\r\n";
		}
		$data .= '</urlset>' . "\r\n";
		if (FALSE === $filename)
			$path = $this->rootPath . (FALSE === $i ? 'sit.xml' : 'sit_' . $i . '.xml');
		else
			$path = $filename;
		$fp = fopen ( $path, 'w' );
		fwrite ( $fp, $data );
		fclose ( $fp );
	}
	
	/**
	 * Enter description here ...
	 */
	public function createSiteXML() {
		
		$total = ceil ( $this->content / $this->linksPerFile );
		
		for($i = 0; $i < $total; $i ++) {
			
			$startNo = $i * $this->linksPerFile;
			$sql = "SELECT `url`,`lastMod` FROM `sm_tmp_{$this->MACID}` WHERE `title` !='' LIMIT {$startNo},{$this->linksPerFile} ";
			if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
				die ( $sql );
			} else {
				$t = 0;
				while ( $row = mysql_fetch_object ( $result ) ) {
					$urls [$t] ['loc'] = $row->url;
					$urls [$t] ['lastmod'] = $row->lastMod;
					$urls [$t] ['changefreq'] = $this->XMLchangeFrequncy;
					$t == 0 ? $urls [$t] ['priority'] = '1' : $urls [$t] ['priority'] = '0.5';
					$t ++;
				}
			
			}
			mysql_free_result ( $result );
			if ($total > 1)
				$this->siteNode ( $urls, $i );
			else
				$this->siteNode ( $urls );
		}
		
		echo "<script type=\"text/javascript\"document.getElementById('status').innerHTML = \"SITE created\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	}
	
	public function mobileNode($content, $i = FALSE) {
		
		$data = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
		if ($this->XMLStyelSheetPath)
			$data .= '<?xml-stylesheet type="text/xsl" href="../../' . $this->XMLStyelSheetPath . '"?>' . "\r\n";
		$data .= '<urlset' . "\r\n";
		$data .= '    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\r\n";
		$data .= '    xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0">' . "\r\n";
		foreach ( $content as $url ) {
			$data .= '    <url>' . "\r\n";
			$data .= '    <loc>' . str_replace ( array ('&', '\'', '"', '>', '<' ), array ('&amp;', '&apos;', '&quot;', '&gt;', '&lt;' ), $url ['loc'] ) . '</loc>' . "\r\n";
			$data .= '    <mobile:mobile/>' . "\r\n";
			$data .= '    </url>' . "\r\n";
		}
		$data .= '</urlset>' . "\r\n";
		
		$fp = fopen ( $this->rootPath . (FALSE === $i ? 'mob.xml' : 'mob_' . $i . '.xml'), 'w' );
		fwrite ( $fp, $data );
		fclose ( $fp );
	}
	
	/**
	 * Enter description here ...
	 */
	public function createMobileXML() {
		
		$total = ceil ( $this->content / $this->linksPerFile );
		
		for($i = 0; $i < $total; $i ++) {
			
			$startNo = $i * $this->linksPerFile;
			$sql = "SELECT `url` FROM `sm_tmp_{$this->MACID}` WHERE `title` !='' LIMIT {$startNo},{$this->linksPerFile} ";
			if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
				die ( mysql_error () );
			} else {
				$t = 0;
				while ( $row = mysql_fetch_object ( $result ) ) {
					$urls [$t] ['loc'] = $row->url;
					$t ++;
				}
			
			}
			mysql_free_result ( $result );
			if ($total > 1)
				$this->mobileNode ( $urls, $i );
			else
				$this->mobileNode ( $urls );
		}
		
		echo "<script type=\"text/javascript\">document.getElementById('status').innerHTML = \"MOBILE created\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	}
	
	/**
	 * Enter description here ...
	 */
	public function createRSSXML() {
	
		$total = ceil ( $this->content / $this->RSSlinksPerFile );
		
		for($i = 0; $i < $total; $i ++) {
			
			$startNo = $i * $this->RSSlinksPerFile;
	
			$sql = "SELECT `id`,count('id') as total FROM `sm_tmp_{$this->MACID}` WHERE `title`!='' group by title having total > 1";
			if ( ! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () ) ) {
				die ( mysql_error () );
			}
			while ( $row = mysql_fetch_object ( $result ) ) {
				$ids[] = $row->id;
			}
			mysql_free_result ( $result );
			$sql = "SELECT `id`,count('id') as total FROM `sm_tmp_{$this->MACID}` WHERE `description`!='' group by description having total > 1";
			if ( ! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () ) ) {
				die ( mysql_error () );
			}
			while ( $row = mysql_fetch_object ( $result ) ) {
				$ids [] = $row->id;
			}
						
			array_shift ( $ids );
			$sql = "SELECT `url`,`title`,`description` 
					FROM `sm_tmp_{$this->MACID}` 
					WHERE 
						`id` NOT IN ( ". join ( ",", $ids ) . " ) 
						LIMIT {$startNo},{$this->RSSlinksPerFile}";

			if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
				die ( mysql_error () );
			} else {
				$t = 0;
				while ( $row = mysql_fetch_object ( $result ) ) {
					$urls [$t] ['link'] = $row->url;
					$urls [$t] ['title'] = $row->title;
					$urls [$t] ['description'] = $row->description ? $row->description : $row->title;
					$t ++;
				}
			
			}
		}
		mysql_free_result ( $result );
		$data = '<?xml version="1.0" encoding="utf-8"?>' . "\r\n";
		$data .= '<rss version="2.0">' . "\r\n";
		$data .= '    <channel>' . "\r\n";
		$data .= '        <title>' . $this->RSSTitle . '</title>' . "\r\n";
		$data .= '        <link>' . proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . '</link>' . "\r\n";
		$data .= '        <description></description>' . "\r\n";
		$data .= '        <language>en-us</language>' . "\r\n";
		$data .= '        <author>Websepanta Co.</author>' . "\r\n";
		if (! $urls)
			return;
		foreach ( $urls as $url ) {
			$data .= '    <item>' . "\r\n";
			$data .= '        <title>' . str_replace ( array ('&', '\'', '"', '>', '<' ), array ('&amp;', '&apos;', '&quot;', '&gt;', '&lt;' ), $url ['title'] ) . '</title>' . "\r\n";
			$data .= '        <link>' . str_replace ( array ('&', '\'', '"', '>', '<' ), array ('&amp;', '&apos;', '&quot;', '&gt;', '&lt;' ), $url ['link'] ) . '</link>' . "\r\n";
			$data .= '        <description>' . $url ['description'] . '</description>' . "\r\n";
			$data .= '    </item>' . "\r\n";
		}
		$data .= '    </channel>' . "\r\n";
		$data .= '</rss>' . "\r\n";
		
		$fp = fopen ( $this->rootPath . 'rss.xml', 'w' );
		fwrite ( $fp, $data );
		fclose ( $fp );
		
		echo "<script type=\"text/javascript\">document.getElementById('status').innerHTML = \"RSS XML created\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	}
	
	public function HTMLNode($content, $i = FALSE, $totalRecords = FALSE, $pages = FALSE) {
		
		$data = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\r\n";
		$data .= '<html xmlns="http://www.w3.org/1999/xhtml">' . "\r\n";
		$data .= '<head>' . "\r\n";
		$data .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\r\n";
		$data .= '<title>' . ($this->htmlTitlePrefix . ' -' . $this->host . '-' . $this->htmlTitleSuffix . (FALSE === $i ? '' : $i)) . ' </title>' . "\r\n";
		$data .= '<link rel="stylesheet" href="' . $this->HTMLStyleSheet . '"  />' . "\r\n";
		$data .= '</head>' . "\r\n";
		$data .= '<body><div id="count">' . "\r\n";
		$data .= '<h1>' . $this->company . ' Site Map</h1>' . "\r\n";
		$data .= '<h3><a href="' . proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . '">Homepage</a>' . "\r\n";
		$data .= 'Last updated: ' . date ( "l, d.F Y", time () ) . '<br />Total pages: ' . $totalRecords . ' </h3>' . "\r\n";
		if (FALSE !== $pages) {
			$data .= '  <div class="pager">' . "\r\n";
			for($x = 0; $x < $pages; $x ++) {
				if ($i != $x)
					$pg [] = '<a href="htm_' . $x . '.html">' . ($x + 1) . '</a>' . "\r\n";
				else
					$pg [] = '<span> [' . ($x + 1) . ']</span>' . "\r\n";
			}
			$data .= join ( " - ", $pg );
			$data .= '  </div>' . "\r\n";
		}
		$data .= '  <table cellpadding="0" cellspacing="0" border="0" width="800px" align="center">' . "\r\n";
		foreach ( $content as $url ) {
			$data .= '  <tr>' . "\r\n";
			$data .= '    <td class="lpage"><a href="' . $url ['link'] . '" title="' . $url ['title'] . '">' . $url ['title'] . '</a>' . "\r\n";
			if ($this->HTMLAllowMeta)
				$data .= '        <br />' . $url ['description'] . '</td>' . "\r\n";
			$data .= '  </td>' . "\r\n";
			$data .= '  </tr>' . "\r\n";
		}
		$data .= '  </table>' . "\r\n";
		$data .= $this->HTMLFooter;
		$data .= '</div></body>' . "\r\n";
		$data .= '</html>' . "\r\n";
		$fp = fopen ( $this->rootPath . (FALSE === $i ? 'htm.html' : 'htm_' . $i . '.html'), 'w' );
		fwrite ( $fp, $data );
		fclose ( $fp );
	
	}
	
	public function createHTMLMap() {
		
		$total = ceil ( $this->content / $this->HTMLlinksPerFile );
		
		for($i = 0; $i < $total; $i ++) {
			
			$startNo = $i * $this->HTMLlinksPerFile;
			$sql = "SELECT `url`,`title`,`description` FROM `sm_tmp_{$this->MACID}` WHERE `title` !='' LIMIT {$startNo},{$this->HTMLlinksPerFile} ";
			if (! $result = mysql_query ( $sql, proshutSiteMapper::$DBConnection->getLink () )) {
				die ( mysql_error () );
			} else {
				$t = 0;
				while ( $row = mysql_fetch_object ( $result ) ) {
					$urls [$t] ['link'] = $row->url;
					$urls [$t] ['title'] = $row->title;
					$urls [$t] ['description'] = $row->description ? $row->description : $row->title;
					$t ++;
				}
			
			}
			mysql_free_result ( $result );
			if ($total > 1)
				$this->HTMLNode ( $urls, $i );
			else
				$this->HTMLNode ( $urls );
		}
		
		echo "<script type=\"text/javascript\">document.getElementById('status').innerHTML = \"HTML created\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	
	}
	
	public function createXSL() {
		
		$data = '<?xml version="1.0" encoding="UTF-8"?>
				 <xsl:stylesheet 
					version="1.0"
					xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
					xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
					xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
					xmlns:fo="http://www.w3.org/1999/XSL/Format"
					xmlns="http://www.w3.org/1999/xhtml">
					<xsl:output method="html" indent="yes" encoding="UTF-8"/>
					<xsl:template match="/">
					<html>
					<head>
					<title>' . $this->company . ' XML Sitemap</title>
					<style>
						body {background-color: #DDD;font: normal 80% "Tahoma", "Helvetica", sans-serif;margin: 0;text-align: center;}
						#cont {margin: auto;width: 800px;text-align: left;}
						a:link {color: #0180AF;text-decoration: underline;}
						a:hover {color: #666;}
						h1 {background-color: #fff;padding: 20px;color: #00AEEF;text-align: left;font-size: 32px;margin: 0px;}
						h3 {font-size: 12px;background-color: #B8DCE9;margin: 0px;padding: 10px;}
						h3 a {float: right;font-weight: normal;display: block;}
						th {text-align: center;background-color: #00AEEF;color: #fff;padding: 4px;font-weight: normal;font-size: 12px;}
						td {font-size: 12px;padding: 2px;text-align: left;}
						tr {background: #fff}
						tr:nth-child(odd) {background: #f0f0f0}
						#footer {background-color: #B8DCE9;padding: 10px;}
					</style>
					</head>
						<body>
							<div id="cont">
							<h1><xsl:if test="sm:urlset/sm:url/sm:mobile">Mobile </xsl:if>' . ucwords ( $this->compnay ) . '<xsl:if test="sm:sitemapindex"> Index</xsl:if></h1>
							<h3>
							<a href="' . proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . '">' . $this->host . '</a>
							<xsl:choose>
							<xsl:when  test="sm:sitemapindex"> 
							' . $this->host . ' Links : <xsl:value-of select="count(sm:sitemapindex/sm:sitemap)"/>
							</xsl:when>
							<xsl:otherwise> 
							Link numbers : <xsl:value-of select="count(sm:urlset/sm:url)"/>
							</xsl:otherwise>
							</xsl:choose>
							</h3>
							<xsl:apply-templates />
							<div id="footer" style="font-size:9px;" align="center" dir="rtl"> 
							<a href="http://www.websepanta.com/services/search-engine-optimization.html">Google XML sitemap maker</a> | 
							<a href="http://www.websepanta.com/services/web-design.html">Web Design</a>  2005-2011 
							<a href="' . proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . '">' . ucwords ( $this->compnay ) . '</a>
							</div></div>
						</body>
					</html>
					</xsl:template>
					<xsl:template match="sm:sitemapindex">
						<table cellpadding="0" cellspacing="0" width="100%">
							<tr>
							<th></th>
							<th>URL</th>
							<th>Last Modified</th>
							</tr>
						<xsl:for-each select="sm:sitemap">
							<tr> 
							<xsl:variable name="loc"><xsl:value-of select="sm:loc"/></xsl:variable>
							<xsl:variable name="pno"><xsl:value-of select="position()"/></xsl:variable>
							<td><xsl:value-of select="$pno"/></td>
							<td><a href="{$loc}"><xsl:value-of select="sm:loc"/></a></td>
							<xsl:apply-templates/> 
							</tr>
						</xsl:for-each>
						</table>
					  </xsl:template>
					  <xsl:template match="sm:urlset">
						<table cellpadding="0" cellspacing="0" width="100%">
							<tr>
							<th></th>
							<th>URL</th>
							<xsl:if test="sm:url/sm:lastmod"><th>Last Modified</th></xsl:if>
							<xsl:if test="sm:url/sm:changefreq"><th>Change Frequency</th></xsl:if>
							<xsl:if test="sm:url/sm:priority"><th>Priority</th></xsl:if>
							</tr>
						<xsl:for-each select="sm:url">
							<tr> 
							<xsl:variable name="loc"><xsl:value-of select="sm:loc"/></xsl:variable>
							<xsl:variable name="pno"><xsl:value-of select="position()"/></xsl:variable>
							<td><xsl:value-of select="$pno"/></td>
							<td><a href="{$loc}"><xsl:value-of select="sm:loc"/></a></td>
							<xsl:apply-templates/> 
							</tr>
						</xsl:for-each>
						</table>
					</xsl:template>
					<xsl:template match="sm:loc|image:image">
					</xsl:template>
					<xsl:template match="sm:lastmod|sm:changefreq|sm:priority">
					<td><xsl:apply-templates/></td>
					</xsl:template>
					</xsl:stylesheet>';
		$fp = fopen ( $this->XMLStyelSheetPath, 'w+' );
		fwrite ( $fp, $data );
		fclose ( $fp );
	
	}
	
	static public function clearDirectory($root, $MACID) {
		
		$files = glob ( $root . "{$MACID}_SAVE_*.tmp" );
		if ($files)
			foreach ( $files as $file )
				unlink ( $file );
	}
	
	public function collectSitemaps() {
		
		$dir = opendir ( $this->rootPath );
		while ( $files = readdir ( $dir ) ) {
			if ($files == '.' || $files == '..')
				continue;
			list ( $name, $ext ) = split ( "\.", $files );
			if (! in_array ( $ext, array ('html', 'xml', 'txt' ) ))
				continue;
			
			$file [$name] = $files;
			ksort ( $file );
		}
		foreach ( $file as $key => $maps ) {
			$path = proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . 'sitemap/' . $maps;
			$node [$key] ['loc'] = $path;
			$node [$key] ['lastmod'] = date ( "Y-m-d\TH:i:s+00:00", filemtime ( $this->rootPath . $maps ) );
			$node [$key] ['changefreq'] = 'daily';
			$node [$key] ['priority'] = '0.9';
		}
		
		$this->siteNode ( $node, FALSE, $_SERVER ['DOCUMENT_ROOT'] . '/sitemap.xml' );
	
	}
	
	public function ping() {
		
		echo "<script type=\"text/javascript\">document.getElementById('status').innerHTML = \"Pinging Sitemap ...\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
		
		$pingTo = array ('http://www.google.com/webmasters/tools/ping?sitemap=', 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=YahooDemo&amp;url=', 'http://www.bing.com/webmaster/ping.aspx?siteMap=', 'http://submissions.ask.com/ping?sitemap=' );
		
		$address = proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . 'sitemap.xml';
		$this->getRemoteFile ( array_map ( create_function ( '&$item,$address', 'return $item.$address;' ), $pingTo, array_fill ( 0, count ( $pingTo ), $address ) ) );
		
		echo "<script type=\"text/javascript\">document.getElementById('status').innerHTML = \"Pinging Finished\";</script>";
		echo str_pad ( " ", 4096 );
		flush ();
	
	}
	
	/**
	 * Enter description here ...
	 */
	public function factory() {
		
		$this->flushOutput ();
		@session_destroy ();
		$this->clearLogs ();
		unset ( $this->index, $this->links, $this->extrnalLinks, $this->images );
		$this->clearPreviuosVersion ();
		$this->createXSL ();
		$this->parseLogFile ();
		proshutSiteMapper::clearDirectory ( $this->rootPath, $this->MACID );
		$this->collectSitemaps ();
		$this->ping ();
		sleep ( 3 );
		echo "<script type=\"text/javascript\">window.location='" . $_SERVER ['PHP_SELF'] . "';</script>";
		echo str_pad ( " ", 4096 );
		flush ();
		die ();
	}
	
	public function checkPreviuos() {
		
		if (file_exists ( $_SERVER ['DOCUMENT_ROOT'] . '/sitemap.xml' )) {
			$status = 'generate';
			if ((time () - $this->period * 3600) < filemtime ( $_SERVER ['DOCUMENT_ROOT'] . '/sitemap.xml' )) {
				$status = 'regenrate';
				/*echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:12px;font-weight:bold;color:#900;border-bottom:1px solid #CCC"> Time limit to regenrate sitemap : ' . @proshutSiteMapper::timeLeft ( ($this->period * 3600) - (time () - filemtime ( $_SERVER ['DOCUMENT_ROOT'] . '/sitemap.xml' )) ) . '   </div>';
				$this->reportPrevious ();
				die ();
			*/}
		}
		if (! isset ( $_GET ['crawl'] ))
			echo '
			<form style="font-family:tahoma;font-size:12px;font-weight:bold;color:#900;" action="' . $_SERVER ['PHP_SELF'] . '" method="get" />Do you want to run site map ' . @$status . ' ? 
			<div style="color:#333;font-weight:normal"><br />
				<div><input type="radio" name="status" value="LT" />Check back lost titles. </div>
				<div><input type="radio" name="status" value="NL" />Check new entries. </div>
				<div><input type="radio" name="status" value="PA" checked="checked"/>The process again. </div>
			</div>
			<input type="submit" name="crawl" value=" Accept " />
			</form> ';
	}
	
	public function reportPrevious() {
		
		if (file_exists ( $_SERVER ['DOCUMENT_ROOT'] . '/sitemap.xml' )) {
			
			$dir = opendir ( $this->rootPath );
			while ( $files = readdir ( $dir ) ) {
				if ($files == '.' || $files == '..')
					continue;
				list ( $name, $ext ) = split ( "\.", $files );
				if (! in_array ( $ext, array ('html', 'xml', 'txt' ) ))
					continue;
				
				$file [$name] = $files;
				ksort ( $file );
			}
		
		}
		if (! isset ( $_GET ['crawl'] )) {
			echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:12px;font-weight:bold"> Latest Sitemap Report   </div>';
			$i = 1;
			if (isset ( $file )) {
				foreach ( $file as $key => $map ) {
					$link = proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . 'sitemap/' . $map;
					echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#666"> ' . $i . ' ) <a href="' . $link . '" target="_blank"  style="color:#333">' . $link . '<a/> - ' . date ( " d F Y G:i", filemtime ( $this->rootPath . $map ) ) . ' </div>';
					$i ++;
				}
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#666"> ' . $i . ' ) <a href="' . proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . 'sitemap.xml"  target="_blank" style="color:#333">' . proshutSiteMapper::rebuildURL ( proshutSiteMapper::analyzeUrl ( $this->host ) ) . 'sitemap.xml<a/> - ' . date ( " d F Y G:i", filemtime ( $this->rootPath . $map ) ) . ' </div>';
			} else
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#666;float:left"> No Result Found </div>';
			if (file_exists ( $this->rootPath . 'lostTitle.tmp' ))
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#900;border-bottom:1px solid #CCC;float:left"><a href="' . $this->rootPath . 'lostTitle.tmp"  style="color:#900" target="_blank" >Lost Titles</a> </div>';
			else
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#900;border-bottom:1px solid #CCC;float:left"><span style="color:#666">Lost Titles </span></div>';
			if (file_exists ( $this->rootPath . 'damagedLinks.tmp' ))
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#900;border-bottom:1px solid #CCC;float:left"> |  <a href="' . $this->rootPath . 'damagedLinks.tmp"  style="color:#900" target="_blank" >Damaged Links</a> </div>';
			else
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#900;border-bottom:1px solid #CCC;float:left"> | <span style="color:#666">Damaged Links </div>';
			if (file_exists ( $_SERVER ['DOCUMENT_ROOT'] . 'robots.txt' ))
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#900;border-bottom:1px solid #CCC;float:left"> |  <a href="' . $_SERVER ['DOCUMENT_ROOT'] . 'robots.txt"  style="color:#900" target="_blank" >Robots</a> </div>';
			else
				echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:11px;font-weight:bold;color:#900;border-bottom:1px solid #CCC;float:left"> | <span style="color:#666">No Robots File Exists</span> </div>';
		
		} else
			echo '<div style="padding:4px 0 4px 0;font-family:tahoma;font-size:12px;font-weight:bold"> Processing on the go ...  </div>';
	
	}

}

class mysqlConnect extends Exception {
	
	private $host;
	
	private $username;
	
	private $password;
	
	private $connection;
	
	private $database;
	
	public static $instance;
	
	public function __construct() {
		
		new loadConfig ( 'db.ini', $this, false, '	', '|' );
		$this->database = convert_uudecode ( strrev ( base64_decode ( $this->database ) ) );
		$this->username = convert_uudecode ( strrev ( base64_decode ( $this->username ) ) );
		$this->password = convert_uudecode ( strrev ( base64_decode ( $this->password ) ) );
		
		if (mysqlConnect::$instance == NULL) {
			if (! $this->connection = @mysql_connect ( $this->host, $this->username, $this->password )) {
				throw new Exception ( mysql_error () );
			}
			mysqlConnect::$instance = $this->connection;
		
		} else {
			$this->connection = mysqlConnect::$instance;
		
		}
		
		if (! mysql_select_db ( $this->database, $this->connection )) {
			throw new Exception ( mysql_error () );
		
		}
		
		
	
	}
	
	public function __destruct() {
	
	}
	
	public function getLink() {
		
		return $this->connection;
	
	}
	
	public function getDatabase() {
		
		return $this->database;
	
	}
	
	public function createResult($sql) {
		
		$result = new mysqlResultSet ( $sql, $this->database, $this->getLink () );
		
		return $result;
	
	}
	
	public function getVerNumber() {
		
		return mysql_get_server_info ();
	
	}
	
	private function closeConnection() {
		
		mysqlConnect::$instance = NULL;
		
		if (is_resource ( $this->connection )) {
			mysql_close ( $this->getLink () );
			unset ( $this->connection );
		
		}
	
	}
	
	public function __call($method, $args) {
		
		if (! method_exists ( $this, $method )) {
			$tmpproperty = str_replace ( 'set_', '', $method );
			$this->$tmpproperty = @$args [0];
		
		}
	
	}

}

class loadConfig extends Exception {
	
	public function __construct($file, $obj, $decode = true, $delimiter = ';', $separator = ':') {
		if (file_exists ( $file )) {
			$tmpContent = $decode ? self::decode ( file_get_contents ( $file ) ) : file_get_contents ( $file );
			$lines = explode ( $delimiter, $tmpContent );
			if (! $lines) {
				throw new Exception ( fileIsEmpty );
			} else {
				foreach ( $lines as $tmpLine ) {
					@list ( $var, $val ) = explode ( $separator, $tmpLine );
					if (strstr ( $val, ',' )) {
						$val = explode ( ',', $val );
					}
					$func = 'set_' . trim ( $var );
					$obj->$func ( $val );
				}
			}
		
		} else {
			throw new Exception ( invalidConfigurationFile );
		
		}
	
	}
	
	static public function readConfig($file, $decode = true, $delimiter = ';', $seprator = ':') {
		
		if (file_exists ( $file )) {
			$tmpContent = $decode ? self::decode ( file_get_contents ( $file ) ) : file_get_contents ( $file );
			$lines = explode ( $delimiter, $tmpContent );
			if (! $lines) {
				throw new Exception ( fileIsEmpty );
			} else {
				foreach ( $lines as $tmpLine ) {
					@list ( $var, $val ) = explode ( $seprator, $tmpLine );
					$rettemp [trim ( $var )] = $val;
				}
			}
		
		} else {
			throw new Exception ( invalidConfigurationFile );
		
		}
		
		return $rettemp;
	
	}
	
	static public function writeConfig($file, array $vars, array $vals, $encode = true, $delimiter = ';', $seprator = ':') {
		
		if (file_exists ( $file )) {
			$fp = fopen ( $file, 'w+' );
			for($i = 0; $i < sizeof ( $vars ); $i ++) {
				$tmpstr [] = trim ( $vars [$i] ) . $seprator . trim ( $vals [$i] );
			}
			$confstr = implode ( $delimiter, $tmpstr );
			fwrite ( $fp, $encode ? self::encode ( $confstr ) : $confstr );
			fclose ( $fp );
		
		} else {
			throw new Exception ( invalidConfigurationFile );
		
		}
		
		return true;
	
	}
	
	static public function decode($str) {
		
		return base64_decode ( convert_uudecode ( strrev ( $str ) ) );
	
	}
	
	static public function encode($str) {
		
		return strrev ( convert_uuencode ( base64_encode ( $str ) ) );
	
	}

}




new proshutSiteMapper ( $_SERVER ['HTTP_HOST'] );

?>
