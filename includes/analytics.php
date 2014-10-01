<?php defined("APP") or die() ?>
/**
 * Custom Analytics - Premium URL Shortener
 * Version <?php echo _VERSION ?>
 */
// Generate Data
var countries ={<?php foreach($country as $c => $click): ?>"<?php echo $c ?>":<?php echo $click ?>,<?php endforeach ?>}
var data =  [{ data: [<?php foreach($clicks as $time => $click): ?>[<?php echo strtotime($time)*1000?>,<?php echo $click ?>],<?php endforeach ?>] }]

var options = {
  series: {
  //lines: { show: true, lineWidth: 2,fill: true},                
  //points: { show: true, lineWidth: 2 }, 
  bars: { show: true, barWidth: 1000*60*60*24, align: 'center' },
  shadowSize: 0
  },
  grid: { hoverable: true, clickable: true, tickColor: 'transparent', borderWidth:0 },
  colors: ['#0da1f5', '#1ABC9C','#F11010'],
  yaxis: {ticks:3, tickDecimals: 0, color: '#CFD2E0'},
  xaxes: [ { mode: 'time', timeformat: "%d %b"} ]
} 

$.plot("#url-chart",data, options);
// fetch one series, adding to what we got
var alreadyFetched = {};

$(".chart_data").click(function (e) {
  e.preventDefault();  
  var id = $(this).attr("data-value");
  $(".chart_data").removeClass("active");
  $(this).addClass("active");  

  if(id=="d"){
    options.series.bars.barWidth= 1000*60*60*24;
    options.xaxes[0].timeformat= "%d %b";
    return $.plot("#url-chart", data, options);
  }
  
  $.ajax({
      type: "POST",
      url: appurl+"/server",
      data: "request=chart&id="+id+"&token="+token,
      dataType: 'json',
      success: function(series){
        if(id[2]=="m"){
          options.series.bars.barWidth= 1000*60*60*24*31;
          options.xaxes[0].timeformat= "%b %Y";
        }else if(id[2]=="y"){
          options.series.bars.barWidth= 1000*60*60*24*365;
          options.xaxes[0].timeformat= "%Y";
        }
        $.plot("#url-chart", [series], options);
      }
  });
});
$('#country').vectorMap({
  map: 'world_mill_en',
  backgroundColor: 'transparent',
  series: {
    regions: [{
      values: countries,
      scale: ['#C8EEFF', '#0071A4'],
      normalizeFunction: 'polynomial'
    }]
  },
  onRegionLabelShow: function(e, el, code){
    if(typeof countries[code]!="undefined") el.html(el.html()+' ('+countries[code]+' Clicks)');
  }     
});
// Append Country List
<?php foreach($top_country as $c=>$click): ?>
$("#country-list").append('<li><?php echo $c ?> <small>(<?php echo round(($click/$total*100),1) ?>%)</small><span class="label label-primary pull-right"><?php echo $click?></span></li>');
<?php endforeach ?> 
// Append Referrer 
<?php foreach($referrers as $r=>$click): ?>
$("#referrer").append('<li><?php echo $r ?> <small>(<?php echo round(($click/$total*100),1) ?>%)</small><span class="label label-primary pull-right"><?php echo $click?></span></li>');
<?php endforeach ?>    
// Append Social Cicks
<?php if(!$fb && !$tw && !$gl): ?>
  $("#social-shares").animate({height: "40px"}).html('<p style="color: #bbb;padding-top: 10px;text-align: center;font-weight: 700;">No clicks from social media.</p>');
<?php else: ?>
var social = [
    {data: <?php echo $fb ?>, color: '#3B5998',label: "Facebook (<?php echo $fb ?>)"},
    {data: <?php echo $tw ?>, color: '#409DD5',label: "Twitter  (<?php echo $tw ?>)"},
    {data: <?php echo $gl ?>, color: '#D34836',label: "Google Plus (<?php echo $gl ?>)"}
];
$.plot($("#social-shares"), social,
{
series: {
  pie: { 
    show: true,
    radius: 1,
    label: {
      show: true,
      radius: 2/3,
      formatter: function(label, series){
      console.log(series);
        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
      },
      threshold: 0.1
    }
  }},legend: {show: false },grid: { hoverable: true}});
<?php endif; ?>  