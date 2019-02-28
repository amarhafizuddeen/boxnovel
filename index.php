<?php 
session_start();
$curl = curl_init();

function getTime($result){
	$regexTime = '!<span class="chapter\-release\-date">!';
	preg_match_all($regexTime, $result, $match, PREG_OFFSET_CAPTURE);
	$times = [];
	
	// Get size of array
	$size = count($match[0]);

	// Loop
	for ($i = 0; $i < $size; $i++){
		// Get the first occurence of time and save it into an array
		$startIndex = $match[0][$i][1];
		$startContent = substr($result,$startIndex);
		// Explode the array on </span> and get content inside <i></i>   
		$arr = explode("</span>",$startContent);
		$time = $arr[0];
		$time .= "</span>";
		
		$times[] = $time; 
	}
	
	return $times;
}

function timeIsNew($times){
	$arr = [];
	foreach ($times as $time) {			
		$match = explode("<i>",$time);
		$time = $match[1];
		$new = false;
		
    if (strpos($time, 'min')) {
      $new = true;
    } else if (strpos($time, 'hour')) {    
      // Get numbers from string
			$match = explode(" hour", $time);
			$time = explode(" hour", $match[0]);  
			$time = intval($time[0]);
			
      // Check if the time is new
      if ($time <= 10) {
        $new = true;
			}
		}
		$arr[] = $new;
	}
	return $arr;
}

function getHomePage(){
	global $curl;
	$novels = array("king-of-gods", "the-legend-of-futian", "reincarnation-of-the-strongest-sword-god", "library-of-heavens-path", "mmorpg-martial-gamer");

	foreach ($novels as $novel) { 
		$timeChange = false;
		$novelName = ucwords(str_replace("-"," ",$novel));
		$url = "https://boxnovel.com/novel/$novel/?";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curl);

		//match time released
		$time = getTime($result);
		$new = timeIsNew($time);
		
		// Call templates
		require("templates/_header.php");
		require("templates/homePage.php");
		require("templates/_footer.php");
	}	
}
	
function getTocPage(){
	global $curl;
	$novel = $_GET['novel'];
	$novelName = ucwords(str_replace("-"," ",$novel));
	$url = "https://boxnovel.com/novel/$novel/?";

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($curl);

	$name = array();
	$time = array();

	//match chapters
	preg_match_all('!<a href="https:\/\/boxnovel\.com\/novel\/' . addslashes($novel) . '\/.*">[^\t]*(.*)Chapter(.*?)<\/a>!', $result, $match);
	$chapters = $match[0];

	//match name
	preg_match_all('!Chapter [\d\s\w\'\-\(\)(&#039;)]*!', $result, $match);
	$name = $match[0];

	//match time released
	$time = getTime($result);
	$new = timeIsNew($time);
		
	$latest_chapter = 0;
	$chapters = [];

	for ($i = 0; $i < sizeof($name); $i++) {
		preg_match_all('!\d+\s!', $name[$i], $matches);
		$var = implode('', $matches[0]);
		$var = str_replace(' ', '', $var);

		if (isset($time[$i])) {
			$chapters[$i]["var"] = $var;
			$chapters[$i]["name"] = $name[$i];
			$chapters[$i]["new"] = $new[$i];
			$chapters[$i]["time"] = $time[$i];
		}
	}
	
	// Call templates
	require("templates/_header.php");	
	require("templates/tocPage.php");	
	require("templates/_footer.php");		
}
	
function getChapterPage(){
	global $curl;
	$novel = $_GET['novel'];
	$chapter = $_GET['chapter'];
	    
	$url = "https://boxnovel.com/novel/$novel/?";
	$novelName = ucwords(str_replace("-"," ",$novel));

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($curl);

	$name = array();

	//match chapters
	preg_match_all('!<a href="https:\/\/boxnovel\.com\/novel\/' . addslashes($novel) . '\/.*">[^\t]*(.*)Chapter(.*?)<\/a>!', $result, $match);
	$chapters = $match[0];

	//match name
	preg_match_all('!Chapter [\d\s\w\'\-\(\)(&#039;)]*!', $result, $match);
	$name = $match[0];

	$latest_chapter = 0;
	$chapterName = "Chapter name not found";

	for ($i = 0; $i < sizeof($name); $i++) {
		preg_match_all('!\d+\s!', $name[$i], $matches);
		$var = implode('', $matches[0]);
		$var = str_replace(' ', '', $var);

		if ($i == 0)
			$latest_chapter = $var;

		if ($var === $chapter){
			$chapterName = $name[$i];
			break;
		} 
	}

	//GET CONTENT
	$url = "https://boxnovel.com/novel/" . $novel . "/chapter-" . $var . "/";

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($curl);
	$result = str_replace("	","",$result);

	preg_match_all('!<div class="cha\-words">!', $result, $match, PREG_OFFSET_CAPTURE);

	if(sizeof($match[0]) === 0) {
		preg_match_all('!<div class="text\-left">!', $result, $match, PREG_OFFSET_CAPTURE);
	}

	$content = $match[0];
	$startIndex = $match[0][0][1];
	$startContent = substr($result,$startIndex);

	$arr = explode("</div>",$startContent);
	$content = $arr[0];
	$content .= "</div>";
	
	// Call templates
	require("templates/_header.php");	
	require("templates/chapterPage.php");
	require("templates/_footer.php");	
}

// Homepage
if (!isset($_GET['novel'])){ 
	getHomePage();
} 
// ToC Page
else if (isset($_GET['novel']) && !isset($_GET['chapter'])) {
	getTocPage();
} 
// Chapter Page
else {
	getChapterPage();
}