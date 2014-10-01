<?php defined("APP") or die() ?>
<section>
  <div class="container">
    <br>
     <?php echo Main::message() ?>
     <div class="row flat">            
        <div class="col-md-4">
            <ul class="plan plan1">
                <li class="plan-name"><?php echo e("Starter") ?></li><li class="plan-price"><strong><?php echo e("Free") ?></strong> Forever</li>                
                <li><?php echo e("Limited Features") ?></li>
                <li><?php echo e("Advertisements") ?></li>          
                <li><?php echo e("Limited Support") ?></li>
            </ul>
        </div>
        <div class="col-md-4">
            <ul class="plan featured">
                <li class="plan-name"><?php echo e("Business")?></li>
                <li class="plan-price"><strong><?php echo Main::currency($this->config["currency"],$this->config["pro_monthly"]) ?></strong> / <?php echo e("month") ?></li>
                <li><?php echo e("All Features") ?></li>
                <li><?php echo e("Custom Splash Pages"); ?></li>
                <li><?php echo e("No Advertisements") ?></li>
                <li><?php echo e("Export Data") ?></li>
                <li><?php echo e("Prioritized Support") ?></li>
                <li class="plan-action"><a href="<?php echo Main::href("upgrade/monthly") ?>" class="btn btn-default btn-lg btn-block"><?php echo e("Upgrade") ?></a></li>
            </ul>
        </div>
        <?php 
          if($this->config["pro_monthly"]){
            $discount=round((($this->config["pro_monthly"]*12)-$this->config["pro_yearly"])*100/($this->config["pro_monthly"]*12),0);
            $discount=($discount < 1)?"":(" ".e("and Save")." $discount%");
          }else{
            $discount="";
          }
         ?>
        <div class="col-md-4">
            <ul class="plan featured">
                <li class="plan-name"><?php echo e("Business")?></li>
                <li class="plan-price"><strong><?php echo Main::currency($this->config["currency"],$this->config["pro_yearly"]) ?></strong> / <?php echo e("year") ?></li>
                <li><?php echo e("All Features") ?></li>
                <li><?php echo e("Custom Splash Page"); ?></li>
                <li><?php echo e("No Advertisements") ?></li>
                <li><?php echo e("Export Data") ?></li>
                <li><?php echo e("Prioritized Support") ?></li>
                <li class="plan-action"><a href="<?php echo Main::href("upgrade/yearly") ?>" class="btn btn-default btn-lg btn-block"><?php echo e("Upgrade") ?> <?php echo $discount ?></a></li>
            </ul>
        </div>        
    </div>         
  </div>
</section>