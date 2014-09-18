<?php

class ZillowPage extends Page{
	public function parse(){
		return $this->getListingURLs();

	}
	public function getListingURLs(){
		$listingsArr = array();
		$listings = $this->getTag("article");
		echo sizeof($listings) . "<br>";
		foreach ($listings as $house) {
			echo $house->plaintext . "<br>";
			$url = $house->find("a", 0)->href;
			array_push($listingsArr, $url);
			return $listingsArr;
		}

	}
}