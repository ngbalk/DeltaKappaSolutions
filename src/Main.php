<?php

require_once "ultimate-web-scraper/support/http.php";
require_once "ultimate-web-scraper/support/web_browser.php";
require_once "ultimate-web-scraper/support/simple_html_dom.php";
require_once "Page.php";
require_once "ZillowPage.php";

$Zillow = new ZillowPage("http://www.zillow.com/homes/94611/");

foreach ($Zillow->parse() as $url) {
	echo $url . "<br>";
}

?>