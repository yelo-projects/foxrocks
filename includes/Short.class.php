<?php 
/**
 * ====================================================================================
 *                           Premium URL Shortener (c) KBRmedia
 * ----------------------------------------------------------------------------------
 * @copyright This software is exclusively sold at CodeCanyon.net. If you have downloaded this
 *  from another site or received it from someone else than me, then you are engaged
 *  in an illegal activity. You must delete this software immediately or buy a proper
 *  license from http://codecanyon.net/user/KBRmedia/portfolio?ref=KBRmedia.
 *
 *  Thank you for your cooperation and don't hesitate to contact me if anything :)
 * ====================================================================================
 *
 * @author KBRmedia (http://gempixel.com)
 * @link http://gempixel.com 
 * @license http://gempixel.com/license
 * @package Premium URL Shortener
 * @subpackage Short Class
 */
class Short extends App{	
	/**
	 * Database and config
	 * @since 4.0
	 **/
	protected $db, $config = array(), $do = "", $action = "";
    /**
	 * The length of alias for each short URL
	 * @var integer, This will be incremented by 1 after 100 tries
	 * @since 4.0
	 */
	protected $alias_length=5;
	/**
	 * The return URL Format. Default = Main or Second. URL. To use subdomains, use http://{?}.YOURDOMAIN.com
	 * @var string
	 * @example http://goo.gl
	 * @since 4.1
	 */	
	protected $return_url=NULL;
	/**
	 * Webpage screenshot generator
	 * @var string, Possible value: "pagepeeker","mshots","webthumb" or your custom API using @URL@ as the placeholder
	 * @example http://athumbservice.com/thumb.php?key=SomeKey&w=500&h=500&url=@URL@
	 * @see image() function
	 * @since 4.1
	 */	
	protected $thumb_provider = "webthumb";
	/**
	 * Redirect Override
	 * @var boolean, If TRUE, user can use $_GET["r"] to override. E.g. ?r=splash or ?r=frame
	 * @deprecated 4.0
	 * @since v3.0
	 */	
	private $redirect_override=FALSE;
	/**
	 * Self-Shortening
	 * @var boolean, If TRUE, it will be possible to shorten the URL of the domain where this script is located.
	 * @since v3.0
	 */	
	private $self_shortening=FALSE;
	/**
	 * Anti-Flood Time
	 * @var integer Minutes, Stats will not be updated when the same visitor clicks the same url for this amount of time
	 * @since 4.0
	 */	
	private $anti_flood=30;	
	/**
	 * Reserved system aliases
	 * @var array of reserved keywords used by the application. These cannot and should not be used in anyway!
	 * @since 4.1
	 */	
	private $aliases = array("admin","includes","static","themes","content");		
	/**
	 * List of URL extensions to not allow! Feel free to add to the list.
	 * @example http://somesite.com/virus.exe will not be allowed
	 * @since 4.1
	 **/
	private $executables = array(".exe",".dll",".bin",".dat",".osx");
	/**
	 * Constructor: Checks logged user status
	 * @since 4.0
	 **/
	public function __construct($db,$config){
  	$this->config=$config;
  	$this->db=$db;
  	$this->db->object=TRUE;
  	// Clean Request
  	if(isset($_GET)) $_GET=array_map("Main::clean", $_GET);
		$this->http=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)?"https":"http");
		$this->check();
	}
	/**
	 * Run Short Class
	 * @since 4.0
	 **/	
	protected function analyze($action,$do){
		$this->action=$action;
		$this->do=$do;
		// Shorten URL
		if($this->action=="shorten"){
			if(!isset($_POST["url"])) return $this->_404();	
			if(isset($_POST["urls"]) && isset($_POST["multiple"]) && $_POST["multiple"]=="1"){
				$array = $this->bulk();
			}else{
				$array = $this->add();
			}
			echo json_encode($array);
			return;
		}
		// API
		if($this->action=="api"){
			return $this->api();
		}
		// Stats
		if(strpos($this->action," ") || strpos($this->action,"+")) {
			$this->action=rtrim(rtrim($this->action," "),"+");
			return $this->stats();
		}

		// Run Subactions
		if(in_array($this->do,array("qr","i")) && method_exists("Short", $this->do)){
			$fn = $this->do;
			return $this->$fn();
		}

		return $this->redirect();
	}			
	/**
	 * Main method that analyzes, validates and shortens a URL
	 * @since 4.2
	 **/
	protected function add($array=array(),$option=array()){
		// Kill the bots
		if(Main::bot()) die($this->_404());
		// if custom array is not try to use post
		if(empty($array) && isset($_POST) && !empty($_POST)){
			$array = $_POST;
		}

		// Validate URL
		$url=$this->validate(Main::clean($array["url"],3,FALSE));
		// Get domain name
		$domain=Main::domain($array["url"],TRUE,FALSE);
    // Get extension
    $ext = Main::extension($url);
		
		// Plug in Pre Shorten
		Main::plug("pre_shorten", $array);

    // If require registration
		if($this->config["require_registration"] && !isset($option["api"]) && !$this->logged()) return array('error' => 1, 'msg' => e('Please create a free account or login to shorten URLs.'));

		// Check if private
		if($this->config["private"] && !$this->logged()) return array('error' => 1, 'msg' => e('This service is meant to be used internally only.'));

		// Validate Request
		if(empty($url) || !$url) return array('error' => 1, 'msg' => e('Please enter a valid URL.'));

		// Prevent self-shortening
		if($domain==Main::domain($this->config["url"]) && !$this->self_shortening) return  array('error' => 1, 'msg' => 'You cannot shorten URLs of this website.');	

		// Check domain is blacklisted
		if($this->blacklist_domain($url)) return  array('error' => 1, 'msg' => e('This domain name has been blacklisted.'));	

		// Match the domain to the list of keywords
		if($this->config["adult"] && $this->blacklist_keywords($url)) return  array('error' => 1, 'msg' => e('This URL contains blacklisted keywords.'));	

		// Checks URL with Google
		if(!$this->safe($url)) return  array('error' => 1, 'msg' => e('URL is suspected to contain malwares and other harmful content.'));

		// Checks URL with Phistank
		if($this->phish($url)) return  array('error' => 1, 'msg' => e('URL is suspected to contain malwares and other harmful content.'));

		// Check if URL is linked to .exe, .dll, .bin, .dat, .osx,
		if($this->config["adult"] && in_array($ext,$this->executables)) return  array('error' => 1, 'msg' => e('Linking to executable files is not allowed.'));

		// Validate selected domain name
		if(isset($array["domain"]) && $this->validate_domain_names(trim($array["domain"]))){
			$this->config["url"]=trim($array["domain"]);
		}

		// Check Captcha
		if($this->config["captcha"] && !$this->logged() && !isset($_GET["bookmark"])){
			$captcha=Main::check_captcha($array);
			if($captcha!='ok'){
				return array('error' => 1, 'msg' => $captcha,'html'=>'captcha');
			}
		}	
		// Check custom alias
		if(isset($array["custom"]) && !empty($array["custom"])){			
			if(strlen($array["custom"]) < 3){
				return array('error' => 1, 'msg' => e('Custom alias must be at least 3 characters.'));
			}elseif($this->blacklist_keywords($array["custom"])){
				return array('error' => 1, 'msg' => e('Inappropriate aliases are not allowed.'));
			}elseif($this->db->get("url","custom=?","",array(Main::slug(Main::clean($array["custom"]))))){
				return array('error' => 1, 'msg' => e('That alias is taken. Please choose another one.'));
			}elseif($this->reserved_alias($array["custom"])){
				return array('error' => 1, 'msg' => e('That alias is reserved. Please choose another one.'));
			}elseif($this->premium_alias($array["custom"])){
				return array('error' => 1, 'msg' => e('That is a premium alias and is reserved to only pro members.'));
			}
		}
		// Generate formatted list of countries
		if(!empty($array['location'][0]) && !empty($array['target'][0])){
			foreach ($array['location'] as $i => $country) {
				if(!empty($country) && !empty($array['target'][$i])){
					$countries[strtolower(Main::clean($country))]=$this->validate(Main::clean($array['target'][$i],3,FALSE));
			  }
			}
			$countries=json_encode($countries);
		}else{
			$countries='';
		}
		// If logged and URL is already shortened, retrieve it
		if($this->userid && (!isset($array["password"]) || empty($array["password"])) &&  (!isset($array["custom"]) ||empty($array["custom"]))){
			if($data=$this->db->get("url",array("url"=>"?","userid"=>"?","location"=>"?","pass"=>"?"),array("limit"=>1),array($url,$this->userid,$countries,""))){
				return $this->build($data->alias.$data->custom);
			}
		}
		// If not logged and and URL is already shortened, retrieve it
		if(!$this->userid && (!isset($array["password"]) || empty($array["password"])) &&  (!isset($array["custom"]) ||empty($array["custom"]))){
			if($data=$this->db->get("url",array("url"=>"?","userid"=>"?","location"=>"?","pass"=>"?","custom"=>"?"),array("limit"=>1),array($url,0,"","",""))){
				// Add to public history			
				$this->check_history($data->alias.$data->custom);
				return $this->build($data->alias.$data->custom);			
			}			
		}	

		// Get meta data
		$meta_title="";
		$meta_description="";
		// Check link has meta data
		if(in_array($ext, array(".zip",".rar",".7z",".flv",".mp4",".avi",".mp3",".jpeg",".png",".jpg",".gif",".mk4"))){
			$meta_title="This is a downloadable file.";
			$meta_description="Please note that this short URL is linked to a downloadable file.";
		}else{
			$info=Main::get_meta_data($url);
			if(!empty($info)){
				$meta_title=Main::clean($info['title'],3,TRUE);
				$meta_description=Main::truncate(Main::clean($info['description'],3,TRUE),150);
			}			
		}
		$custom="";
		$alias="";
		if(isset($array["custom"]) && !empty($array["custom"])){
			$custom = Main::slug(Main::clean($array["custom"],3,TRUE));
		}else{
			$alias = $this->alias();
		}
		// Let's register new URL
		$data = array(
			":alias" => $alias,
			":custom" => $custom,
			":url" => $url,
			":description" => isset($array["description"]) && !empty($array["description"]) ? Main::clean($array["description"],3,TRUE) : "",
			":location" => $countries,
			":date" => "NOW()",
			":pass" => isset($array["password"]) ? Main::clean($array["password"],3,TRUE) : "",
			":meta_title" => $meta_title,
			":meta_description" => $meta_description,
			":userid" => $this->userid,
			":public" => $this->logged() ? $this->user->public : 1 // Based on user settings.
		);		
		// Custom redirect
		if(($this->config["frame"]=="3" || $this->pro()) && isset($array["type"]) && (is_numeric($array["type"]) || in_array($array["type"], array("direct","frame","splash")))) {
			$data[":type"]=Main::clean($array["type"],3,TRUE);
		}

		if($this->db->insert("url",$data)){
			// Add to public history
			if(!$this->logged()){
				$this->check_history($alias.$custom);
			}			
			return $this->build($alias.$custom);
		}		
	}
	/**
	 * Bulk URL Shortening
	 * @since 4.0
	 */	
	private function bulk(){
		$urls="";

		// Check Captcha
		if(!$this->logged()){
			$captcha=Main::check_captcha($_POST);
			if($captcha!='ok'){
				return array('error' => 1, 'msg' => $captcha,'html'=>'captcha');
			}
		}	
 	  $url=explode("\n",$_POST["urls"]);
 	  $i=0;
 	  foreach($url as $link){
 	  	if($i > 10) break;
 	  	if(!empty($link)){
 	  		$array = array("url"=>trim($link));
 	  		if(isset($_POST["domain"])){
 	  			$array["domain"] = Main::clean($_POST["domain"],3,TRUE);
 	  		}
 	  		if(isset($_POST["type"]) && $this->pro()){
 	  			$array["type"] = Main::clean($_POST["type"],3,TRUE);
 	  		}
 	  		$this->config["captcha"]=FALSE;
				$short=$this->add($array);
	    	if(!$short["error"]){
	    		$urls.=$link." => ".$short["short"]." \n";
	    	}else{		    		
	    		$urls.=$link." => ".$short["msg"]." \n";
	    	}
 	  	}
 	  	$i++;
 	  }
 		return array("error"=>0, "confirm"=>1, "short"=> str_replace("\r", "", $urls));	 	  
	}	
	/**
	 * Check Anon history
	 * @since 4.1
	 */
	private function check_history($alias){
		if($anonid = Main::cookie("aid")){
			$urls = json_decode($anonid,TRUE);
			if(!in_array($alias, $urls)){
				$urls[] = $alias;
				$new = array_reverse($urls);
				$keep = array_slice($new, 0, 9);
				Main::cookie("aid",json_encode($keep),60*24*365);		
			}
		}else{
			Main::cookie("aid",json_encode(array($alias)),60*24*365);      	
		}				
	}	
	/**
	 * Redirect
	 * @since 4.0
	 */	
	private function redirect(){
		// Filter do
		$this->filter($this->do);
		// Fetch URL and show 404 if doesn't exist
		if(!$url=$this->db->get("url","BINARY alias=:id OR BINARY custom=:id",array("limit"=>1),array(":id"=>$this->action))){
			return $this->_404();
		}
		// Add short URL
		if(!isset($_SESSION["passcheck"]) || $_SESSION["passcheck"]!==TRUE){
			$_SESSION["passcheck"]=FALSE;
		}
		// Note: Password check is stored in a session. User will have access until the browser is closed.
		if(isset($_POST["token"])){
			// Validate CSRF Token
			if(!Main::validate_csrf_token($_POST["token"])){
				return Main::redirect(Main::href($this->action,"",FALSE),array("danger",e("Invalid token. Please try again.")));
			}
			// if encryted Password (old version)
			if(strlen($url->pass)>= 32) $_POST["password"] = md5($_POST["password"]);
			// Check Password
			if($_POST["password"]!==$url->pass){
				return Main::redirect($this->action,array("danger",e("Please enter a valid password.")));
			}
			// Set Session
			$_SESSION["passcheck"]=TRUE;
		}

		// Let's check if it is password-protected
		if(!empty($url->pass) && (!isset($_SESSION["passcheck"]) || !$_SESSION["passcheck"])){
			return $this->pass();
		}
		// Update stats
		$this->update($url);
		// Check if URL is geotargeted
		if(!empty($url->location) && $this->config["geotarget"]){
			$country=$this->country();			
			$location=json_decode($url->location,TRUE);							
			if (isset($location[$country]) && $country) {
				$url->url=$location[$country];
			}
		}		
		// Set Meta info
		if(!empty($url->meta_title)) Main::set("title",$url->meta_title);
		if(!empty($url->meta_description)) Main::set("description",$url->meta_description);
		Main::set("url","{$this->config["url"]}/{$url->alias}{$url->custom}");
		Main::set("image","{$this->config["url"]}/{$url->alias}{$url->custom}/i");	
		Main::video($url->url);

		// Get User info
		if($url->userid!=0 && $user = $this->db->get(array("count"=>"id,auth,auth_id,email,username,banned,media,splash_opt,pro,expiration","table"=>"user"),array("id"=>$url->userid),array("limit"=>1))){			
			// Disable URLs of user is banned
			if($user->banned) return $this->_404();
			// If membership expired, switch to free
			if($this->config["pro"] && $user->pro && time() > strtotime($user->expiration)) $this->db->update("user",array("pro"=>0),array("id"=>$user->id));
			$url->media = $user->media;
			$url->pro=$user->pro;
		}else{
			$url->media = $this->config["show_media"];
			$url->pro=0;
		}
		$url->short= (empty($user->domain) ? $this->config["url"] : $user->domain)."/".$url->alias.$url->custom;

		// Custom Splash Page
		if(is_numeric($url->type) && $url->pro && $splash = $this->db->get("splash",array("id"=>"?","userid"=>"?"),array("limit"=>1),array($url->type,$url->userid))){
			return $this->custom($url, $splash);
		}
		
		// If is media, show media
		if($url->media && $media = $this->isMedia($url->url)){
			return $this->media($url,$media);
		}

		// Check redirect method
		if($this->config["frame"]=="3" || $url->pro){
			if(empty($url->type)){
				return $this->direct($url);
			}elseif(in_array($url->type, array("direct","frame","splash"))){
				$fn = $url->type;
				return $this->$fn($url,$user);
			}
		}

		// Switch to a method
		$methods = array("0"=>"direct","1"=>"frame","2"=>"splash", "3"=> "splash");
		$fn = $methods[$this->config["frame"]];
		return $this->$fn($url);
	}
	/**
	 * Update Stats
	 * @since 4.1
	 **/
	private function update($url){
		// Prevents Bots
		if(Main::bot()) return FALSE;
		// Check user visited recently
		if(Main::cookie("short_{$this->action}")) return FALSE;
		// Update clicks
		if($this->db->update("url",array("click"=>"click+1"),array("id"=>":a"),array(":a"=>$url->id))){
			// Set cookie to prevent flooding valid for XX minutes
			Main::cookie("short_{$this->action}",'1',$this->anti_flood);
		}

		if($this->config["tracking"]=="1"){
			// System Analytics
			if(isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"]) && !is_null($_SERVER["HTTP_REFERER"])){
				$referer=Main::clean($_SERVER["HTTP_REFERER"],3,TRUE);
				$domain=parse_url($referer);
				if(isset($domain["host"])){
					$domain=$domain["scheme"]."://".$domain["host"];
				}else{
					$domain="";
				}
			}else{
				$referer="direct";
				$domain="";
			}
			$data = array(
					":short" => $this->action,
					":urlid" => $url->id,
					":urluserid" => $url->userid,
					":date" => "NOW()",
					":country" => $this->country(),
					":referer" => $referer,
					":domain" => $domain,
					":ip" => Main::ip()
				);
			// Save data
			$this->db->insert("stats",$data);
		}
		return FALSE;				
	}
	/**
	 * Splash
	 * @since 4.2
	 **/
	private function splash($url){
		// Add timer animation	
		if(!empty($this->config["timer"]) || $this->config["timer"] !=="0"){
			Main::add('<script type="text/javascript">var count = '.$this->config['timer'].';var countdown = setInterval(function(){$("a.redirect").attr("href","#pleasewait").html(count + " seconds");if (count < 1) {clearInterval(countdown);$("a.redirect").attr("href","'.$url->url.'").text(lang.continue);}count--;}, 1000);</script>',"custom",FALSE);					
		}				
		$this->header();
		include($this->t(__FUNCTION__));
		$this->footer();
	}
	/**
	 * Splash
	 * @since 4.2
	 **/
	private function custom($url,$splash){
		// Add timer animation	
		if(!empty($this->config["timer"]) || $this->config["timer"] !=="0"){
			//Main::add("<script>setInterval(function(){ window.location='{$url["url"]}';}, ".($this->config["timer"]*1000).");</script>","custom",FALSE);
			Main::add('<script type="text/javascript">var count = '.$this->config['timer'].';var countdown = setInterval(function(){$(".c-countdown span").html(count);if (count < 1) {clearInterval(countdown);window.location="'.$url->url.'";}count--;}, 1000);</script>',"custom",FALSE);					
		}				
		$data = json_decode($splash->data);
		$data->avatar = Main::href("content/{$data->avatar}");
		$data->banner = Main::href("content/{$data->banner}");
		$this->header();
		include($this->t("custom_splash"));
		$this->footer();
	}	
	/**
	 * Frame
	 * @since 4.0
	 **/
	private function frame($url,$user){
		// Inject GA code
		if(!empty($this->config["analytic"])){					
			Main::add("<script type='text/javascript'>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '{$this->config["analytic"]}', '".Main::domain($this->config["url"])."');ga('send', 'pageview');</script>","custom",FALSE);
		}					
		if(!$this->url_framed($url->url)) return $this->direct($url);
		if($user->auth=="facebook" && !empty($user->auth_id)){
			$user->avatar="{$this->http}://graph.facebook.com/".$user->auth_id."/picture?type=large";
		}else{
			$user->avatar="{$this->http}://www.gravatar.com/avatar/".md5(trim($user->email))."?s=150";		
		}
		$user->isLogged = $this->logged();
		include($this->t(__FUNCTION__));
	}
	/**
	 * Direct Method
	 * @since 4.0
	 **/
	private function direct($url){
		// Add header
		header('HTTP/1.1 301 Moved Permanently');
		header("Location: {$url->url}",true,301);
		return;
	}
	/**
	 * Media Page
	 * @since 4.0
	 **/
	private function media($url, $data){
		$url->shorturl=$this->config["url"]."/".$url->alias.$url->custom;
		$url->embed = $this->embed($data);
		if(!$this->logged()) $this->user->domain=$this->config["url"];
		$this->header();
		include($this->t(__FUNCTION__));
		$this->footer();
	}
			/**
			 * Detect if URL is media
			 * @since 4.0
			 **/
			private function isMedia($url){
		    preg_match('((http://|https://|www.)([\w-\d]+\.)+[\w-\d]+)',$url, $r);
    		$host = @str_replace(".","", $r[2]);				
		    $hosts=array(
						"youtube" => "youtube\.(.*)\/watch\?v=([^\&\?\/]+)",
						"vimeo" => "vimeo\.(.*)\/([^\&\?\/]+)",
						"vine" => "vine\.(.*)\/v/([^\&\?\/]+)",
						"dailymotion" => "dailymotion\.(.*)\/video/([^\&\?\/]+)_([^\&\?\/]+)",
						"funnyordie" => "funnyordie\.(.*)\/videos/([^\&\?\/]+)",
						"collegehumor" => "collegehumor\.(.*)\/video/([^\&\?\/]+)\/([^\&\?\/]+)"
		    	);
		    if(array_key_exists($host, $hosts) && preg_match("~{$hosts[$host]}~", $url, $match)){
					return (object) array("host"=>$host,"id"=>$match[2],"url"=>$url);
		    }
		    return FALSE;
			}
			/**
			 * Embed Media
			 * @since 4.0
			 **/
			private function embed($data){
				if(!is_object($data))	return;
				$sites=array(
					// Youtube
					"youtube" => "<iframe id='ytplayer' type='text/html'  width='640' height='400' allowtransparency='true' src='//www.youtube.com/embed/{$data->id}?autoplay=0&origin={$this->config["url"]}' frameborder='0'></iframe>",
					// Vimeo
					"vimeo" => "<iframe src='//player.vimeo.com/video/{$data->id}' width='640' height='400' allowtransparency='true' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>",
					// Dailymotion
					"dailymotion" => "<iframe src='http://www.dailymotion.com/embed/video/{$data->id}' width='640' height='390' allowtransparency='true' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>",
					// FunnyOrDie
					"funnyordie" => "<iframe src='http://www.funnyordie.com/embed/{$data->id}' width='640' height='400' allowtransparency='true' frameborder='0' allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>",
					// Collegehumor
					"collegehumor" => "<iframe src='http://www.collegehumor.com/e/{$data->id}'  width='640' height='400' allowtransparency='true' frameborder='0' webkitAllowFullScreen allowFullScreen></iframe>",
					// Vine
					"vine" => "<iframe class='vine-embed' src='{$data->url}/embed/postcard' width='600' height='600' allowtransparency='true' frameborder='0'></iframe><script async src='//platform.vine.co/static/scripts/embed.js' charset='utf-8'></script>"
				);
				return $sites[$data->host];
			}
			/**
			 * Comments
			 * @since 4.1
			 **/
			protected function comment($appid=""){
				if(!empty($this->config["facebook_app_id"])) $appid=$this->config["facebook_app_id"];
				if(empty($appid)) return FALSE;

				$html='<div class="panel panel-default panel-body" id="'.__FUNCTION__.'">';
					$html.='<h3>'.e("Comments").'</h3>';  		
	  			$html.= '<script id="auto_css_facebook">$(document).ready(function(){$(".fb-comments").attr("data-width", $("#comment").width());FB.XFBML.parse($("#comment")[0]);});</script>';

	  		  $html.="<div id='fb-root'></div>
										<script>(function(d, s, id) {
										  var js, fjs = d.getElementsByTagName(s)[0];
										  if (d.getElementById(id)) return;
										  js = d.createElement(s); js.id = id;
										  js.src = '//connect.facebook.net/en_US/all.js#xfbml=1&appId=$appid';
										  fjs.parentNode.insertBefore(js, fjs);
										}(document, 'script', 'facebook-jssdk'));</script>
								<div class='fb-comments' data-href='{$this->config["url"]}/{$this->action}' data-width='600' data-numposts='5' data-colorscheme='light'></div>";
	      $html.="</div>";
	      return $html;
			}
	/**
	 * Show password form
	 * @since 4.0
	 **/
	private function pass(){
		// Set Meta info
		Main::set("title",e("Enter your password to unlock this URL"));
		Main::set("description",e('The access to this URL is restricted. Please enter your password to view it.'));
		// Let's show the password field
		$this->isUser=FALSE;
		$this->header();
		echo '<section>
						<div class="container">    
							<div class="centered form">';
		echo '     '.Main::message().'
					      <form role="form" class="live_form" method="post" action="'.Main::href($this->action).'">
									<h3>'.e("Enter your password to unlock this URL").'</h3>
									<p>'.e('The access to this URL is restricted. Please enter your password to view it.').'</p>					      
					        <div class="form-group">
					          <label for="pass1">'.e("Password").'</label>
					          <input type="password" class="form-control" id="pass1" placeholder="Password" name="password">             
					        </div>        
					        '.Main::csrf_token(TRUE).'
					        <button type="submit" class="btn btn-primary">'.e("Unlock").'</button>        
					      </form>';
		echo '    </div>
						</div>
					</section>';
		$this->footer();
		return;
	}
	/**
	 * Stats Page
	 * @since 4.2
	 **/
	private function stats(){
		// Get data
		if(!$url=$this->db->get("url","BINARY alias=:a OR BINARY custom=:a",array("limit"=>1),array(":a"=>$this->action))) return $this->_404();
		// Check if user is anon and url is public
		if(!$this->logged() && (!empty($url->pass) || !$url->public)) return Main::redirect(Main::href("user/login","",FALSE),array("danger",e("This URL is private and only the creator can access the stats. If you are the creator, please login to access it.")));
		// Check if user is logged and is admin or owner
		if($this->logged() && !$url->public && !$this->admin()){
			if($this->userid!==$url->userid) return Main::redirect(Main::href("user/login","",FALSE),array("danger",e("This URL is private and only the creator can access the stats. If you are the creator, please login to access it.")));
		}
		$url->short=$this->config["url"]."/".$url->alias.$url->custom;
		if($this->config["tracking"]==1){
			$count = Main::cache_get("url_unique_{$url->id}");
			if($count == null){
				$count = $this->db->count("stats","short='{$url->alias}' OR short='{$url->custom}' GROUP by ip");
				Main::cache_set("url_unique_{$url->id}",$count,30);
			}
			$url->unique=$count;
		}else{
			$url->unique = "n/a";
		}
		Main::set("title",e("Stats for")." ".$url->meta_title);
		Main::set("description","Advanced statistics page for the short URL {$this->config["url"]}/{$url->alias}{$url->custom}.");
		Main::set("url","{$this->config["url"]}/{$url->alias}{$url->custom}");
		Main::set("image","{$this->config["url"]}/{$url->alias}{$url->custom}/i");
		Main::cdn("flot");
		Main::add("{$this->config["url"]}/static/js/jvector.js");
		Main::add("{$this->config["url"]}/static/js/jvector.world.js");
		Main::add("{$this->config["url"]}/analytic/".str_replace("=", "", base64_encode("{$url->alias}{$url->custom}:{$url->click}"))."?token={$this->config["public_token"]}");
		$this->header();
		include($this->t(__FUNCTION__));
		$this->footer();
	}
	/**
	 * API
	 * @since 4.0
	 **/
	private function api(){
		// Validate request
		if(!$this->config["api"]) return $this->api_build(array("error"=>1,"msg"=>"API service is disabled."));		
		
		// Check API Key
		if(!isset($_GET["key"]) && !isset($_GET["api"])) return $this->api_build(array("error"=>1,"msg"=>"A valid API key is required to use this service."));
		
		$key = isset($_GET["key"]) ? $_GET["key"] : $_GET["api"];
		// Get user
		if(!$user = $this->db->get("user",array("api"=>"?"),array("limit"=>1),array($key))) return $this->api_build(array("error"=>1,"msg"=>"A valid API key is required to use this service."));
		$this->userid = $user->id;
		$this->config["captcha"]=0;
		// Check Request type
		if(!isset($_GET["url"]) && !isset($_GET["short"])) return $this->api_build(array("error"=>1,"msg"=>"Please enter a valid URL."));
	
		// Check if shorten request is made
		if(isset($_GET["url"])){
			$array = array();
			$array["url"]	= Main::clean($_GET["url"],3,TRUE);
			if(isset($_GET["custom"]) && !empty($_GET["custom"])) $array["custom"] = Main::slug($_GET["custom"]);
			$result = $this->add($array,TRUE);
			return $this->api_build($result,isset($result["short"])?$result["short"]:"");
		}

		// Check if retrieval request is made
		if(isset($_GET["short"])){
			$g = parse_url($_GET["short"]);
			$g = explode("/",$g["path"]);
			$g = array_reverse($g);
			if(!$url = $this->db->get("url","alias=:q OR custom=:q",array("limit"=>1),array(":q"=>$g[0]))) return $this->api_build(array("error"=>1,"msg"=>"This URL couldn't be found. Please double check it."));
			if((!empty($url->pass) || !$url->public) && $url->userid !== $user->id) return $this->api_build(array("error"=>1,"msg"=>"This URL is private or password-protected."));

			$array = array(
					"error" => 0,
					"long" => $url->url,
					"click" => $url->click,
					"date" => $url->date,
					"location" => json_decode($url->location,TRUE)
				);
			return $this->api_build($array,$url->url);
		}
		return;
	}
			/**
			 * API Build
			 * @since 4.0
			 **/
			private function api_build($array,$text=""){
				header("content-type: application/javascript");
				// JSONP Request
				if(isset($_GET["callback"])){
					return print("{$_GET["callback"]}(".json_encode($array).")");
				}
				// Text
				if(isset($_GET["format"]) && $_GET["format"]=="text"){
					header("content-Type: text/plain");
					return print($text);
				}
				// JSON
				return print(json_encode($array));		
			}
	/**
	 * Generate QR
	 * @since v2.1.1
	 */		
	protected function qr(){
		if(isset($_GET["size"]) && !empty($_GET["size"]) && preg_match('/[0-5]x[0-5]/', $_GET["size"])){
			$size=str_replace('"', "", str_replace("'", "", Main::clean($_GET["size"],3,TRUE)));
		}else{
			$size="149x149";
		}
		$api_url="http://chart.apis.google.com/chart?chs=$size&chld=L|0&choe=UTF-8&cht=qr&chl=".urlencode($this->config["url"]."/{$this->action}?source=qr");	
		$image=Main::curl($api_url);
		if(!$image){
			header("Location: $api_url");	
			exit;	
		}
		header('Pragma: public');
		header('Cache-Control: max-age=86400');
		header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));				
		header('Content-type: image/jpeg;');
		echo $image;
	}
	/**
	 * Generate Thumbnail
	 * @since v3.0
	 */		
	protected function i(){
		//$t_url=$this->config["url"]."/404";
		if($url=$this->db->get("url","BINARY alias=:a OR BINARY custom=:a",array("limit"=>1),array(":a"=>$this->action))){
				$t_url=$url->url;
		}else{
			return;
		}
		$t_url=urlencode($t_url);
		if($this->thumb_provider=="mshots"){
			$api_url="http://s.wordpress.com/mshots/v1/$t_url?w=800";
			header("Location: $api_url");	
			exit;				
		}elseif($this->thumb_provider=="pagepeeker"){
			$api_url="http://api.pagepeeker.com/v2/thumbs.php?size=l&url=$t_url";
			$format="image/jpeg;";	
			header("Location: $api_url");	
			exit;				
		}elseif($this->thumb_provider=="webthumb"){
			$api_url="http://api.webthumbnail.org?width=800&height=600&screen=1024&url=$t_url";
			$format="image/png;";		
			header("Location: $api_url");	
			exit;						
		}else{
			$api_url=str_replace("@URL@",$t_url,$this->thumb_provider);
		}		
		$image=Main::curl($api_url);
		if(!$image){
			header("Location: $api_url");	
			exit;	
		}
		header('Pragma: public');
		header('Cache-Control: max-age=86400');
		header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));		
		header('Content-type: $format');
		echo $image;		
	}
	/**
	 * Build return URL
	 * @since v2.1
	 */
	protected function build($data){
		//if(!empty($this->user_domain) && is_null($this->return_url) && $this->validate_domain_names($this->user_domain)) $this->return_url=$this->user_domain;		
		if(!is_null($this->return_url)){
			if(preg_match("~{?}~", $this->return_url)){
				return array("error"=>0,"short"=>str_replace("{?}",$data,$this->return_url));
			}else{
				return array("error"=>0,"short"=>"{$this->return_url}/$data");
			}
		}
		return array("error"=>0,"short"=>"{$this->config["url"]}/$data");
	}	
	/**
	 * Check if domain is blacklisted
	 * @since 4.1.1
	 */
	protected function blacklist_domain($url){
		if(empty($this->config["domain_blacklist"])) return FALSE;
		$url=parse_url($url);
		$array=explode(",",$this->config["domain_blacklist"]);
		foreach ($array as $domain) {	
		  if (preg_match("/$domain/",$url["host"])) {
		  	return TRUE;
		  }
		}
		return FALSE;		
	}
	/**
	 * Check if URL contains blacklisted keywords
	 * @since 3.1.1
	 */
	protected function blacklist_keywords($url){
		if(!$this->config["adult"]) return FALSE;
		if(empty($this->config["keyword_blacklist"])) {
			$array = array('porn','sex','porno','redtube','4tube','spankwire',
					'xshare','ziporn','naked','pornstar','pussy','fuck','suck','porntube',
					'scriptmaster','warez','scriptmafia','nulled','jigshare','gaaks','newone',
					'intercambiosgratis','scriptease','xtragfx','vivaprogram','kickassgfx',
					'gfxdl','fulltemplatedownload','dlscript','nigger','dick','faggot','cunt','gay',
					'asshole','penis','vagina','motherfucker','fucker','shit','fucked','boobs');
		}else{
			$array=explode(",",$this->config["keyword_blacklist"]);
		}
		foreach ($array as $domain) {
		  $domain=trim($domain);
		  if (strpos($url,$domain)) {
		  	return TRUE;
		  }
		}
		return FALSE;		
	}
	/**
	 * Check if url can framed
	 * @since 4.0
	 */	
	protected function url_framed($url){
		$array=array("facebook.","youtube.","google.","gmail.","yahoo.","github.");
		$domain=Main::domain($url,TRUE,FALSE);
		foreach ($array as $u) {
		  if (preg_match("!$u!",$domain)) {
		  	return FALSE;
		  }
		}
		return TRUE;			
	}
	/**
	 * List of reserved custom alias
	 */
	protected function reserved_alias($alias){
		// Check system alias
		if(in_array($alias,array_merge($this->aliases,$this->actions))) return TRUE;
		return FALSE;
	}	
	/**
	 * Premium Aliases
	 * @since 4.0
	 **/
	protected function premium_alias($alias){
		// Check reserved alias
		if(!$this->pro() && in_array($alias, explode(",",$this->config["aliases"]))) return TRUE;		
	}
	/**
	 * Validate URL
	 * @since 4.0
	 */	
	protected function validate($url){
		if(empty($url)) return FALSE;		
		if(preg_match('(((http://|https://|ftp://|www.)*?)([\w-\d]+\.)+[\w-\d]+)', $url)) {
			if(!preg_match('(http://|https://|ftp://)',$url)){
				$url="http://$url";
			}
		}else{
			return FALSE;
		}
		if(!filter_var($url, FILTER_VALIDATE_URL)) return FALSE;
		return $url;
	}
	/**
	 * Check if domain is safe using Google Safe (API Required)
	 * @since 4.0
	 */	
	protected function safe($url){
		if(!empty($this->config["safe_browsing"])){
			$api="https://sb-ssl.google.com/safebrowsing/api/lookup?client=api&apikey={$this->config["safe_browsing"]}&appver=1.5.2&pver=3.0&url=";
			$url=urlencode($url);
			$get=Main::curl($api.$url);
			if($get=="malware" || $get=="phishing" || $get=="phishing,malware"){
				return FALSE;
			}
		}	
		return TRUE;				
	}	
 /**
	 * Check if URL is a Phish using phishtank (API not required but recommended)
	 * @since 4.0
	 */	
	protected function phish($url){
		$api="http://checkurl.phishtank.com/checkurl/";
		$data["format"]="json";
		if(!empty($this->config["phish_api"])){
			$data["app_key"]=$this->config["phish_api"];
		}
		$data["url"]=urlencode($url);
		$get=Main::curl($api,array("post"=>true,"data"=>$data));
		$r = json_decode($get);
		if(isset($r->results->verified)) return TRUE;
		return FALSE;				
	}
	/**
	 * Unique Alias
	 * @since 4.0
	 **/
	protected function alias(){
		$unique=FALSE;
		$max_loop=100;
		$i=0;
		while (!$unique) {
			// retry if max attempt reached
			if($i>=$max_loop) {
				$this->alias_length++;
				$i=0;
			}
			$alias=Main::strrand($this->alias_length);
			if(!$this->db->get("url",array("alias"=>$alias))) $unique=TRUE;
			$i++;
		}		
		return $alias;
	}
}