<?php defined("APP") or die() // Main Page ?>  
<section>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="promo">
          <h1>
            <?php echo $this->config["title"] ?>            
          </h1>
        </div>
        <?php echo Main::message() ?>
        <?php echo $this->shortener(array("multiple"=>FALSE)) ?>
        <div class="call-to-action">
          <span><?php echo $this->config["description"] ?></span>
          <a href="<?php echo Main::href("user/register") ?>" class="btn btn-primary btn-lg"><?php echo e("Get Started") ?></a>
          <a href="#more" class="btn btn-primary btn-lg"><?php echo e("Learn More") ?></a>
        </div>
      </div>
    </div>
  </div>     
</section>
<?php if($this->history()): ?>
  <?php // If anon user has history show it otherwise show landing page ?>
<?php else: ?>
  <section id="more">
    <div class="container">
      <div class="row feature">
        <div class="col-sm-7 image">
          <img src="<?php echo Main::href("static/stats.png") ?>" alt="<?php echo e("One dashboard to manage all everthing.") ?>">
        </div>
        <div class="col-sm-5 info">
          <h2>
            <?php echo e("Complete Analytics") ?>
            <small><?php echo e("Track each and every user who clicks a link.") ?></small>
          </h2>
          <p>
            <?php echo e("Our system allows you to track everything. Whether it is the amount of clicks, the country or the referrer, the data is there.") ?>
          </p>
        </div>      
      </div>      
    </div>
  </section>
  <section>
    <div class="container">
      <div class="row feature">
        <div class="col-sm-5 info">
          <h2>
            <?php echo e("Powerful Dashboard") ?>
            <small><?php echo e("One dashboard to manage everything.") ?></small>
          </h2>
          <p>
            <?php echo e("Our dashboard lets you control everything. Manage your URLs, create bundles, manage your splash pages and your settings, all from the same dashboard.") ?>
          </p>
        </div>
        <div class="col-sm-7 image">
          <img src="<?php echo Main::href("static/dashboard.png") ?>" alt="<?php echo e("One dashboard to manage everything.") ?>">
        </div>
      </div>         
    </div> 
  </section>
  <section class="light">
    <div class="container">
      <div class="row featurette">
        <div class="col-sm-3">
          <i class="glyphicon glyphicon-lock"></i>
          <h3><?php echo e("Password Protect") ?></h3>
          <p><?php echo e("Set a password to protect your links from unauthorized access.") ?></p>
        </div>
        <div class="col-sm-3">
          <i class="glyphicon glyphicon-globe"></i>
          <h3><?php echo e("Geotarget") ?></h3>
          <p><?php echo e("Geotarget your links to redirect visitors to specialized pages and increase your conversion.") ?></p>
        </div>      
        <div class="col-sm-3">
          <i class="glyphicon glyphicon-briefcase"></i>
          <h3><?php echo e("Bundle") ?></h3>
          <p><?php echo e("Bundle your links for easy access and share them with the public on your public profile.") ?></p>
        </div>
        <div class="col-sm-3">
          <i class="glyphicon glyphicon-share"></i>
          <h3><?php echo e("Share") ?></h3>
          <p><?php echo e("Share your links in one click via the dashboard.") ?></p>        
        </div>
      </div>    
    </div>
  </section>
<?php endif; ?>
<?php $this->public_list() ?>
<section>
  <div class="container">
    <div class="row stats">
      <div class="col-xs-4">
        <h3><?php echo $this->count("urls") ?></h3>
        <strong><?php echo e("URLs Created") ?></strong>
      </div>
      <div class="col-xs-4">
        <h3><?php echo $this->count("clicks") ?></h3>
        <strong><?php echo e("Clicks Served") ?></strong>
      </div>
      <div class="col-xs-4">
        <h3><?php echo $this->count("users") ?></h3>
        <strong><?php echo e("Users Registered") ?></strong>
      </div>
    </div>
  </div>
</section>