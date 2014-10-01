<?php defined("APP") or die() ?>
<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
  <head>       
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0" />  
    <meta name="description" content="<?php echo Main::description() ?>" />
    <!-- Open Graph Tags -->
    <?php echo Main::ogp(); ?> 

    <title><?php echo Main::title() ?></title> 
        
    <!-- Bootstrap core CSS -->
    <link href="<?php echo $this->config["url"] ?>/static/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body{
        background: transparent;
      }
      #frame{
        position: absolute;
        left:0;
        bottom: 0;
        width: 100%;
        box-sizing:border-box;
        margin-left:20px;
        margin-bottom:20px;
      }
      .site-logo a{
        color: #fff;
        text-decoration: none;
      }
      .site-logo{
        font-size: 16px;
        padding-bottom: 5px;
      }
      .btn-group{
        margin: 15px 15px 0 0
      }
      .bubble {
        position:relative;
        padding:15px;
        min-height:100%;
        margin-left:20px;
        margin-top: 20px;
        color:#999;
        background:#fdfdfd; /* default background for browsers without gradient support */
        -webkit-border-radius:10px;
        -moz-border-radius:10px;
        border-radius:10px;
        min-width: 325px;
        min-height: 100px;
        max-width:50%;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
      }
      .bubble:after, .bubble:before{
        content:"";
        position:absolute;
        bottom:-15px; /* value = - border-top-width - border-bottom-width */
        left:50px; /* controls horizontal position */
        border-width:15px 15px 0; /* vary these values to change the angle of the vertex */
        border-style:solid;
        border-color:#fdfdfd transparent;
        /* reduce the damage in FF3.0 */
        display:block;
        width:0;
        top:16px; /* controls vertical position */
        left:-15px; /* value = - border-left-width - border-right-width */
        bottom:auto;
        border-width:15px 15px 15px 0;
        border-color:transparent #fdfdfd;
      }
      .bubble:before{
        border-color:transparent rgba(0,0,0,0.1);
        top:20px;
        left:-15px;
      }
      .c-avatar, .bubble{
        float:left;
      }
      .c-user{
        color:#ccc;
        font-size:80%;
      }
    </style>
    <!-- Required Javascript Files -->
    <script type="text/javascript" src="<?php echo $this->config["url"] ?>/static/js/jquery.min.js?v=1.11.0"></script>
    <script type="text/javascript">
      var appurl="<?php echo $this->config["url"] ?>";
      var token="<?php echo $this->config["public_token"] ?>";
    </script>
    <?php Main::enqueue() // Add scripts when needed ?>    
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body<?php echo Main::body_class() ?> id="body" style="overflow:hidden">
    <section id="frame">
      <div class="c-avatar"><img src="<?php echo $user->avatar ?>"></div><!-- /.avatar -->
        <div class="bubble">
          <div class="bubble-contents clearfix">
            <div class="c-user clearfix">
                <?php echo $user->username ?>
            </div>
            <div class="c-message">
                <?php echo $url->description ?>
            </div>
            <?php if($user->isLogged){?>
              <div class="btn-group btn-group-sm pull-left">
                <a href='<?php echo Main::href("user/edit/{$url->id}") ?>'  target="_blank" class="btn btn-primary btn-xs"><?php echo e("Edit")?></a>
                <a href="<?php echo Main::href()?>/<?php echo $url->alias.$url->custom ?>+" target="_blank" class="btn btn-primary btn-xs"><?php echo e("Clicks") ?></a>
              </div>
            <?php };?>
            <div class="btn-group btn-group-sm pull-right">
              <a href="https://www.facebook.com/sharer.php?u=<?php echo $this->user->domain ?>/<?php echo $url->alias.$url->custom ?>" class="btn btn-primary u_share" target="_blank"><?php echo e("Share") ?></a>
              <a href="https://twitter.com/share?url=<?php echo $this->user->domain ?>/<?php echo $url->alias.$url->custom ?>&amp;text=Check+out+this+url" class="btn btn-primary u_share" target="_blank"><?php echo e("Tweet") ?></a>
              <a href="<?php echo $this->config["url"] ?>/?r=<?php echo base64_encode($url->url) ?>" class="btn btn-primary"><?php echo e("Close") ?></a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <iframe id="site" src="<?php echo $url->url;?>" frameborder="0" style="border: 0; width: 100%; height: 100%" scrolling="yes"></iframe>
    <script type="text/javascript">
       $("iframe#site").height($(document).height());
       window.location.hash = '<?php echo $url->url ?>';
    </script>
    <?php Main::enqueue('footer') ?>  
  </body>
</html>  