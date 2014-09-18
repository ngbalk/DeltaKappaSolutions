<?php

abstract class Page {
	public $html; 
	public $url; 

	public function __construct($url){
		$this->url = $url;
		$this->html = file_get_html($url);
	}

	//abstract function parse();

	public function parse_title(){
		if(is_object($this->html)){
			return $this->html->find("title",0);
		}
		else{
			return "--Unable to read title--";
		}
	}
	

	public function store_links_in(){
		$global_url = $GLOBALS['root_url'];
		$linksin = array();
		$all_links = $this->html->find("a"); 
		foreach ($all_links as $link) {
			$href = $link->href;
			if($this->validate_url($href)){
				$href = ConvertRelativeToAbsoluteURL(ExtractURL($global_url), $href);
				if(ExtractURL($href)['host'] == ExtractURL($global_url)['host']){
					array_push($linksin, $href);
				}
			}
		}
		return $linksin;
	}
	public function store_links_out(){
		$myurl = $this->url;
		@$mydomain = getRegisteredDomain(parse_url($myurl, PHP_URL_HOST));
		$linksout = array();
		$all_links = $this->html->find("a"); 
		foreach ($all_links as $link) {
			$href = $link->href;
			@$nextdomain = getRegisteredDomain(parse_url($href, PHP_URL_HOST));
			if($nextdomain != "" && $nextdomain != $mydomain){
				array_push($linksout, $href);
			}
		}
		return $linksout;
	}


	public function find_html_tags(){
		$myTags = array();
		$htmltags = array("head","meta[content]","title","body","div","a[href]","span","bold","p",
				"header","ul","ol","li","table","tr","td","h1","h2","h3","h4","h5", "h6", "footer",
				"img[src]","img[alt]","menu","strong","a","a[href]");
		foreach ($htmltags as $tag) {
			$concat = "";
			$elements = $this->html->find($tag);
			foreach ($elements as $element) {
				if ($tag == "img[src]") {
					$concat.= ", " . $element->src;
				}
				elseif ($tag == "img[alt]"){
					$concat.= ", " . $element->alt;
				}
				elseif ($tag == "a[href]"){
					$concat.= ", " . $element->href;
				}
				elseif($tag == "meta[content]"){
					$concat.= ", " . $element->content;
				}
				elseif($this->validate_word($element->innertext)){
					$concat.= ", " . $element->innertext;
				}
			}
			$myTags[$tag] = $concat;			
		}
		return $myTags;
	}
	public function getTag($tag){
		$elements = $this->html->find($tag);
		return $elements;
	}
	public function find_all_words(){
			$words = $this->html->find("body",0)->plaintext;
			$this->Dictionary->parseString($words);
			$wordsCn = $this->Dictionary->chineseWords;
			$wordsEn = $this->Dictionary->englishWords;
			$wordsAll = array_merge($wordsCn, $wordsEn);
			$freqList = array();
			foreach ($wordsAll as $word) {
				if(ctype_space($word) || $word == " " || $word == ""){
					continue;
				}
				if(!array_key_exists($word, $freqList)){
					$freqList[$word] = 1;
				}
				else{
					$freqList[$word] += 1;
			}
		}
		$this->Dictionary->clear();
		return $freqList;
		}
	public function get_file_size(){
		return strlen($this->html->plaintext);
	}


	public function get_css(){
		$all_css = array();
		foreach ($this->html->find('link[type = text/css]') as $obj) {
			$src = $obj->href;
			array_push($all_css, $src);
		}

		return $all_css;
	}
	public function get_images(){
		$all_img = array();
		foreach ($this->html->find('img') as $obj) {
			$src = $obj->src;
			$src = ConvertRelativeToAbsoluteURL(ExtractURL($GLOBALS['root_url']), $src);
			array_push($all_img, $src);
		}
		return $all_img;
	}
	public function table_traverse($tableid, $xright, $ydown){  
		$table = $this->html->find("#" . $tableid);
		$row = $table->children([$ydown]);
		$cell = $row->children([$xright]);
		return $cell->innertext;

	}
	public function get_all_table_data(){
		$tables_array = array();
		$tables = $this->html->find("table");
		foreach ($tables as $table) {
			$rows_array = array();
			$rows = $table->find("tr");
			foreach ($rows as $row) {
				$cells = $row->find("td");
				$cells_array = array();
				foreach ($cells as $cell) {
					$value = $cell->innertext;
					array_push($cells_array, $value);
				}
				array_push($rows_array, $cells_array);
			}
			array_push($tables_array, $rows_array);
		}
		return $tables_array;
	}
	public function validate_word($word){
		if($word == "" || $word == " "){
			return false;
		}
		if(is_numeric($word)){
			return false;
		}
		return true;

	}
	public function validate_url($url){
		if(substr($url, 0, 1) == "#" || strpos($url, "@")!==false){
			return false;
		}
		$extension = end(explode(".", $url));
		if($extension == "jpg" || $extension == "png" || $extension == "gif"){
			return false;
		}		
		return true;
	}


}