foxrocks
========

Website for foxrocks

# To Install

* create a mySQL database
* delete `includes/config.php`
* rename `_install.php` to `install.php`
* load the site in your browser 

or simply edit the config in `config.php`

If hosted on hostgator, edit `.htaccess` and uncomment the lines:

```
  #Action application/x-hg-php53 /cgi-sys/php53
  #AddHandler application/x-hg-php53 .php
  
