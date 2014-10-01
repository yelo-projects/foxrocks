<?php 
/**
 * ====================================================================================
 *                           PREMIUM URL SHORTENER (c) KBRmedia
 * ----------------------------------------------------------------------------------
 * @copyright This software is exclusively sold at CodeCanyon.net. If you have downloaded this
 *  from another site or received it from someone else than me, then you are engaged
 *  in an illegal activity. You must delete this software immediately or buy a proper
 *  license from http://gempixel.com/buy/short.
 *
 *  Thank you for your cooperation and don't hesitate to contact me if anything :)
 * ====================================================================================
 *
 * @author KBRmedia (http://gempixel.com)
 * @link http://gempixel.com 
 * @package Premium URL Shortener
 * @subpackage Application installer
 */
	if(!isset($_SESSION)) session_start();
	$error="";
	$message=(isset($_SESSION["msg"])?$_SESSION["msg"]:"");
	if(!isset($_GET["step"]) || $_GET["step"]=="1" || $_GET["step"] < "1"){
		$step = "1";
	}elseif($_GET["step"] > "1" && $_GET["step"]<="5"){
		$step = $_GET["step"];
	}else{
		die("Oups. Looks like you did not follow the instructions! Please follow the instructions otherwise you will not be able to install this script.");
	}
	switch ($step) {
		case '2':
			if(file_exists("includes/config.php")) $error='Configuration file already exists. Please delete or rename "config.php" and recopy "config_sample.php" from the original zip file. You cannot continue until you do this.';  

			if(isset($_POST["step2"])){
			if (empty($_POST["host"]))  $error.="<p>- You forgot to enter your host.</p>"; 
            if (empty($_POST["name"])) $error.="<p>- You forgot to enter your database name.</p>"; 
            if (empty($_POST["user"])) $error.="<p>- You forgot to enter your username.</p>"; 
	            if(empty($error)){
					 try{
					    $db = new PDO("mysql:host=".$_POST["host"].";dbname=".$_POST["name"]."", $_POST["user"], $_POST["pass"]);
						generate_config($_POST);
		                $query=get_query();
						foreach ($query as $q) {
						  $db->query($q);
						} 
						$_SESSION["msg"]="Database has been successfully imported and configuration file has been created.";
						header("Location: install.php?step=3");
					  }catch (PDOException $e){
					    $error = $e->getMessage();
					  }
	            }							
			}
		break;
		case '3':
			@include("includes/config.php");
				$_SESSION["msg"]="";
			    if(isset($_POST["step3"])){
			            if (empty($_POST["email"]))  $error.="<div class='error'>You forgot to enter your email.</div>"; 
			            if (empty($_POST["pass"])) $error.="<div class='error'>You forgot to enter your password.</div>"; 
			            if (empty($_POST["url"])) $error.="<div class='error'>You forgot to enter the url.</div>"; 
			    	if(!$error){

			    	$data=array(
				    	":admin"=>"1",
				    	":email"=>$_POST["email"],
				    	":username"=>$_POST["username"],
				    	":password"=>Main::encode($_POST["pass"]),
				    	":date"=>"NOW()",
				    	":pro"=>"1",
				    	":auth_key"=>Main::encode(Main::strrand()),
				    	":last_payment" => date("Y-m-d H:i:s",time()),
				    	":expiration" => date("Y-m-d H:i:s",time()+315360000),
				    	":api" => Main::strrand(12)
			    	);

					  $db->insert("user",$data);					  
					  $db->update("settings",array("var"=>"?"),array("config"=>"?"),array($_POST["url"],"url"));
					  $db->update("settings",array("var"=>"?"),array("config"=>"?"),array($_POST["email"],"email"));
					  $_SESSION["msg"]="Your admin account has been successfully created.";
					  $_SESSION["site"]=$_POST["url"];
					  $_SESSION["username"]=$_POST["username"];
					  $_SESSION["email"]=$_POST["email"];
					  $_SESSION["password"]=$_POST["pass"];
					  header("Location: install.php?step=4"); 
			        }   
			    }		
		break;
		case '4':
			$_SESSION["msg"]="";
			@include("includes/config.php");
		break;
		case '5':
			header("Location: index.php"); 
			unset($_SESSION);
			unlink(__FILE__);
			
			if(file_exists("main.zip")){
				unlink('main.zip');
			}
			if(file_exists("updater.php")){
				unlink('updater.php');
			}
		break;
	}
 ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Premium URL Shortener Installation</title>
	<style type="text/css">
		body{background:#f9f9f9;font-family:Helvetica, Arial;width:860px;line-height:25px;font-size:13px;margin:0 auto;}a{color:#009ee4;font-weight:700;text-decoration:none;}a:hover{color:#000;text-decoration:none;}.container{background:#fff;border:1px solid #eee;box-shadow:0 0 0 3px #f7f7f7;border-radius:3px;display:block;overflow:hidden;margin:50px 0;}.container h1{font-size:22px;display:block;border-bottom:1px solid #eee;margin:0!important;padding:10px;}.container h2{color:#999;font-size:18px;margin:10px;}.container h3{background:#f8f8f8;border-bottom:1px solid #eee;border-radius:3px 0 0 0;text-align:center;margin:0;padding:10px 0;}.left{float:left;width:258px;}.right{float:left;width:599px;border-left:1px solid #eee;}.form{width:90%;display:block;padding:10px;}.form label{font-size:15px;font-weight:700;margin:5px 0;}.form label a{float:right;color:#009ee4;font:bold 12px Helvetica, Arial; padding-top: 5px;}.form .input{display:block;width:98%;height:15px;border:1px #ccc solid;font:bold 15px Helvetica, Arial;color:#aaa;border-radius:2px;box-shadow:inset 1px 1px 3px #ccc,0 0 0 3px #f8f8f8;margin:10px 0;padding:10px;}.form .input:focus{border:1px #73B9D9 solid;outline:none;color:#222;box-shadow:inset 1px 1px 3px #ccc,0 0 0 3px #DEF1FA;}.form .button{height:35px;}.button{background:#0080FF;height:20px;width:90%;display:block;text-decoration:none;text-align:center;border-radius: 2px;color:#fff;font:15px Helvetica, Arial bold;cursor:pointer;border-radius:3px;margin:30px auto;padding:5px 0;border:0;width: 98%;}.button:active,.button:hover{background:#0069D2;color:#fff;}.content{color:#999;display:block;border-top:1px solid #eee;margin:10px 0;padding:10px;}li{color:#999;}li.current{color:#000;font-weight:700;}li span{float:right;margin-right:10px;font-size:11px;font-weight:700;color:#00B300;}.left > p{border-top:1px solid #eee;color:#999;font-size:12px;margin:0;padding:10px;}.left > p >a{color:#777;}.content > p{color:#222;font-weight:700;}span.ok{float:right;border-radius:3px;background:#00B300;color:#fff;padding:2px 10px;}span.fail{float:right;border-radius:3px;background:#B30000;color:#fff;padding:2px 10px;}span.warning{float:right;border-radius:3px;background:#D27900;color:#fff;padding:2px 10px;}.message{background:#1F800D;color:#fff;font:bold 15px Helvetica, Arial;border:1px solid #000;padding:10px;}.error{background:#980E0E;color:#fff;font:bold 15px Helvetica, Arial;border-bottom:1px solid #740C0C;border-top:1px solid #740C0C;margin:0;padding:10px;}.inner,.right > p{margin:10px;}	
	</style>
  </head>
  <body>
  	<div class="container">
  		<div class="left">
			<h3>Installation Process</h3>
			<ol>
				<li<?php echo ($step=="1")?" class='current'":""?>>Requirement Check <?php echo ($step>"1")?"<span>Completed</span>":"" ?></li>
				<li<?php echo ($step=="2")?" class='current'":""?>>Database Configuration<?php echo ($step>"2")?"<span>Completed</span>":"" ?></li>
				<li<?php echo ($step=="3")?" class='current'":""?>>Basic Configuration<?php echo ($step>"3")?"<span>Completed</span>":"" ?></li>
				<li<?php echo ($step=="4")?" class='current'":""?>>Installation Complete</li>
			</ol>
			<p>
				<a href="http://gempixel.com/" target="_blank">Home</a> | 
				<a href="http://support.gempixel.com/" target="_blank">Support</a> | 
				<a href="http://gempixel.com/profile" target="_blank">Profile</a> <br />
				2012-<?php echo date("Y") ?> &copy; <a href="http://gempixel.com" target="_blank">KBRmedia</a> - All Rights Reserved
			</p>
  		</div>
  		<div class="right">
			<h1>Installation of Premium URL Shortener</h1> 
			<?php if(!empty($message)) echo "<div class='message'>$message</div>"; ?>
			<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
			<?php if($step=="1"): ?>		
				<h2>1.0 Requirement Check</h2>
				<p>
					These are some of the important requirements for this software. "Red" means it is vital to this script, "Orange" means it is required but not vital and "Green" means it is good. If one of the checks is "Red", you will not be able to install this script because without that requirement, the script will not work.
				</p>
				<div class="content">
					<p>
					PHP Version (need at least version 5.3)
					<?php echo check('version')?>
					</p>
					It is very important to have at least PHP Version 5.3. It is highly recommended that you use 5.3.7 or later for better performance.
				</div>
				<div class="content">
					<p>PDO Driver must be enabled 
						<?php echo check('pdo')?>
					</p>
					PDO driver is very important so it must enabled. Without this, the script will not connect to the database hence it will not work at all. If this check fails, you will need to contact your web host and ask them to either enable it or configure it properly.
				</div>					
				<div class="content">
					<p><i>config_sample.php</i> must be accessible. 
						<?php echo check('config')?>
					</p>
					This installation will open that file to put values in so it must be accessible. Make sure that file is there in the <b>includes</b> folder and is writable.
				</div>		
				<div class="content">
					<p><i>content/</i> folder must writable. 
						<?php echo check('content')?>
					</p>
					Many things will be uploaded to that folder so please make sure it has the proper permission.
				</div>												
				<div class="content">
					<p><i>allow_url_fopen</i> Enabled
						<?php echo check('file')?>
					</p>
					The function <strong>file_get_contents</strong> is used to validate URLs and to get the country of the user. This function is not required but some features may not work properly.
				</div>
				<div class="content">
					<p>cURL Enabled <?php echo check('curl')?></p>
					cURL is mainly used to get statistics from Google Analytics, if you want to use the built-in statistics then this is not required.
				</div>				
			<?php if(!$error) echo '<a href="?step=2" class="button">Requirements are met. You can now Proceed.</a>'?>
		<?php elseif($step=="2"): ?>	
		<h2>2.0 Database Configuration</h2>
		<p>
			Now you have to set up your database by filling the following fields. Make sure you fill them correctly.
		</p>
		<form method="post" action="?step=2" class="form">
		    <label>Database Host <a>Usually it is localhost.</a></label>
		    <input type="text" name="host" class="input" required />
		    
		    <label>Database Name</label>
		    <input type="text" name="name" class="input" required />
		    
		    <label>Database User </label>
		    <input type="text" name="user" class="input" required />    
		    
		    <label>Database Password</label>
		    <input type="password" name="pass" class="input" />   

		    <label>Database Prefix <a>Prefix for your tables (Optional) e.g. short_</a></label>
		    <input type="text" name="prefix" class="input" value="" />       

		    <label>Security Key (Keep this secret) <a>This should never be changed!</a></label>
		    <input type="text" name="key" class="input" value="" />   

		    <label>Server's Timezone</label><br />
		    <p>If your server's timezone is not on the list, pick the one closest to you. You can always change this later.</p>
		    <select name="tz" style="padding: 5px;width: 100%; cursor: pointer;">    
					<?php
						$timezone_identifiers = DateTimeZone::listIdentifiers();
						foreach($timezone_identifiers as $tz){
						    echo "<option value='$tz'>$tz</option>";
						}
					?>		    
				</select> 
		    <button type="submit" name="step2" class='button'>Create my configuration file and go to step 3</button>    
		</form>
		<?php elseif($step=="3"): ?>
		<p>
			Now you have to create an admin account by filling the fields below. Make sure to add a valid email and a strong password. For the site URL, make sure to remove the last slash.
		</p>
		  <form method="post" action="?step=3" class="form">
		        <label>Admin Email</label>
		        <input type="text" name="email" class="input" required />

		        <label>Admin Username</label>
		        <input type="text" name="username" class="input" required />	

		        <label>Admin Password</label>
		        <input type="password" name="pass" class="input" required />   

		        <label>Site URL <a>Including http:// but without the ending slash "/"</a></label>
		        <input type="text" name="url" class="input" value="<?php echo get_domain() ?>" placeholder="http://" required />  

		        <input type="submit" name="step3" value="Finish Up Installation" class='button' />     
		  </form>		
		<?php elseif($step=="4"): ?>
	       <p>
 				The script has been successfully installed and your admin account has been created. Please click "Delete Install" button below to attempt to delete this file. Please make sure that it has been successfully deleted. 
	       </p>
	       <p>
	       	  Once clicked, you may see a blank page otherwise you will be redirected to your main page. If you see a blank, don't worry it is normal. All you have to do is to go to your main site, login using the info below and configure your site by clicking the "Admin" menu and then "Settings". Thanks for your purchase and enjoy :)
	       </p>
	       <p>
	       <strong>Login URL: <a href="<?php get('site') ?>/user/login" target="_blank"><?php get('site') ?>/user/login</a></strong> <br />
	       <strong>Email: <?php get('email') ?></strong> <br />
	       <strong>Username: <?php get('username') ?></strong> <br />
	       <strong>Password: <?php get('password') ?></strong>
	       </p>	       
	       <a href="?step=5" class="button">Delete install.php</a>	       
		<?php endif; ?>
  		</div>  		
  	</div>
  </body>
</html>
<?php 
function get_domain(){
	$url="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$url=str_replace("/install.php?step=3", "", $url);
	return $url;
	//return "http://{$url[2]}/{$url[3]}";
}
function get($what){
	if(isset($_SESSION[strip_tags(trim($what))])){
		echo $_SESSION[strip_tags(trim($what))];
	}
}
function check($what){
	switch ($what) {
		case 'version':
			if(PHP_VERSION>="5.3"){
				return "<span class='ok'>You have ".PHP_VERSION."</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>You have ".PHP_VERSION."</span>";
			}
			break;
		case 'config':
			if(@file_get_contents('includes/config_sample.php') && is_writable('includes/config_sample.php')){
				return "<span class='ok'>Accessible</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>Not Accessible</span>";
			}
			break;
		case 'content':
			if(is_writable('content')){
				return "<span class='ok'>Accessible</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>Not Accessible</span>";
			}
			break;			
		case 'pdo':
			if(defined('PDO::ATTR_DRIVER_NAME') && class_exists("PDO")){
				return "<span class='ok'>Enabled</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>Disabled</span>";
			}
			break;
		case 'file':
			if(ini_get('allow_url_fopen')){
				return "<span class='ok'>Enabled</span>";
			}else{
				return "<span class='warning'>Disabled</span>";
			}
			break;	
		case 'curl':
			if(in_array('curl', get_loaded_extensions())){
				return "<span class='ok'>Enabled</span>";
			}else{
				return "<span class='warning'>Disabled</span>";
			}
			break;						
	}
}
function get_query(){

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."bundle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `userid` mediumint(9) NOT NULL,
  `date` datetime NOT NULL,
  `access` varchar(10) NOT NULL DEFAULT 'private',
  `view` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `seo` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `menu` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` varchar(255) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `status` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."settings` (
  `config` varchar(255) NOT NULL,
  `var` text NOT NULL,
  PRIMARY KEY (`config`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$query[] = "INSERT INTO `".trim($_POST["prefix"])."settings` (`config`, `var`) VALUES
('url', ''),
('title', ''),
('description', ''),
('api', '1'),
('user', '1'),
('sharing', '1'),
('geotarget', '1'),
('adult', '1'),
('maintenance', '0'),
('keywords', ''),
('theme', 'default'),
('apikey', ''),
('ads', '1'),
('captcha', '0'),
('ad728', ''),
('ad468', ''),
('ad300', ''),
('frame', '0'),
('facebook', ''),
('twitter', ''),
('email', ''),
('fb_connect', '0'),
('analytic', ''),
('private', '0'),
('facebook_app_id', ''),
('facebook_secret', ''),
('twitter_key', ''),
('twitter_secret', ''),
('safe_browsing', ''),
('captcha_public', ''),
('captcha_private', ''),
('tw_connect', '0'),
('multiple_domains', '0'),
('domain_names', ''),
('tracking', '1'),
('update_notification', '0'),
('default_lang', ''),
('user_activate', '0'),
('domain_blacklist', ''),
('keyword_blacklist', ''),
('user_history', '0'),
('pro_yearly', ''),
('show_media', '0'),
('pro_monthly', ''),
('paypal_email', ''),
('logo', ''),
('timer', ''),
('smtp', ''),
('style', ''),
('font', ''),
('currency', 'USD'),
('news', '<strong>Installation successful</strong> Please go to the admin panel to configure important settings including this message!'),
('gl_connect', '0'),
('require_registration', '0'),
('phish_api', ''),
('aliases', ''),
('pro', '1'),
('google_cid', ''),
('google_cs', ''),
('public_dir', '0');";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."splash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` bigint(12) NOT NULL,
  `name` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short` varchar(255) NOT NULL,
  `urlid` bigint(20) NOT NULL,
  `urluserid` bigint(20) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `ip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `referer` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."url` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `userid` int(16) NOT NULL DEFAULT '0',
  `alias` varchar(10) NOT NULL,
  `custom` varchar(160) NOT NULL,
  `url` text NOT NULL,
  `location` text NOT NULL,
  `description` text NOT NULL,
  `date` datetime NOT NULL,
  `pass` varchar(255) NOT NULL,
  `click` bigint(20) NOT NULL DEFAULT '0',
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `ads` int(1) NOT NULL DEFAULT '1',
  `bundle` mediumint(9) NOT NULL,
  `public` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `type` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth` text NOT NULL,
  `auth_id` varchar(255) NOT NULL,
  `admin` int(1) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `api` varchar(255) NOT NULL,
  `ads` int(1) NOT NULL DEFAULT '1',
  `active` int(1) NOT NULL DEFAULT '1',
  `banned` int(1) NOT NULL DEFAULT '0',
  `public` int(1) NOT NULL DEFAULT '0',
  `domain` varchar(255) NOT NULL,
  `media` int(1) NOT NULL DEFAULT '0',
  `splash_opt` int(1) NOT NULL DEFAULT '0',
  `splash` text NOT NULL,
  `auth_key` varchar(255) NOT NULL,
  `last_payment` datetime NOT NULL,
  `expiration` datetime NOT NULL,
  `pro` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `api` (`api`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

$query[]=<<<QUERY
INSERT INTO `{$_POST["prefix"]}page` (`id`, `name`, `seo`, `content`, `menu`) VALUES
(1, 'Terms and Conditions', 'terms', 'Please edit me when you can. I am very important.', 1),
(2, 'Developer', 'developer', 'Please check out the template at http://gempixel.com/shortener/developer.html and copy the template or write your own developer page.', 1);
QUERY;
return $query;
}
function generate_config($array){
	if(!empty($array)){
	    $file=file_get_contents('includes/config_sample.php');
	    $file=str_replace("RHOST",trim($array["host"]),$file);
	    $file=str_replace("RDB",trim($array["name"]),$file);
	    $file=str_replace("RUSER",trim($array["user"]),$file);
	    $file=str_replace("RPASS",trim($array["pass"]),$file);                
	    $file=str_replace("RPRE",trim($array["prefix"]),$file);  
	    $file=str_replace("RTZ",trim($array["tz"]),$file);  
	    $file=str_replace("RPUB",trim(md5(api())),$file);
	    $file=str_replace("RKEY",trim($array["key"]),$file);
	    $fh = fopen('includes/config_sample.php', 'w') or die("Can't open config_sample.php. Make sure it is writable.");
	    fwrite($fh, $file);
	    fclose($fh); 
	    rename("includes/config_sample.php", "includes/config.php");
	}
}
function api(){
          $l='12';
          $api="";
          $r= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
            srand((double)microtime()*1000000); 
            for($i=0; $i<$l; $i++) { 
              $api.= $r[rand()%strlen($r)]; 
            } 
          return $api;    
      }
?>
