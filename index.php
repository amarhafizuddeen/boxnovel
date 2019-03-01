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
		$time = trim(str_replace('<span class="chapter-release-date">', '', $time));
		$time = str_replace('</span>', '', $time);
		
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

function interpolate($data, $template){
	
	foreach($data as $key => $value) {
		$find = "{".$key."}";
		$replace = $value;
		$template = str_replace($find, $replace, $template);
	}
	
	return $template;
}

// returns string containing center > p > a tags.
function chapterNavigation($novel, $chapter, $latestChapter = false){
	$str = '<center>';
	$str .= '<p style="padding: 10px;">';
	
	if ($chapter !== 1){
		$linkPrev = 'index.php?novel='.$novel.'&chapter='.($chapter-1);
		$str .= '<a href="'.$linkPrev.'">< PREV</a>';
		$str .= '	|	';
	}
		
	$linkToc = 'index.php?novel='.$novel;
	$str .= '<a href="'.$linkToc.'">TOC</a>';
	$str .= '	|	';
	$str .= '<a href="./">HOME</a>';
	
	if (!$latestChapter){
		$linkNext = 'index.php?novel='.$novel.'&chapter='.($chapter+1);
		$str .= '	|	';
		$str .= '<a href="'.$linkNext.'">NEXT ></a>';
	}
	
	$str .= '<p style="padding: 10px;">';
	$str .= '</center>';
	
	return $str;
	
}

function getHomePage(){
	global $curl;
	$novels = array("king-of-gods", "the-legend-of-futian", "reincarnation-of-the-strongest-sword-god", "library-of-heavens-path", "mmorpg-martial-gamer");
	$str = "";
	
	foreach ($novels as $novel) { 
		$novelName = ucwords(str_replace("-"," ",$novel));
		$url = "https://boxnovel.com/novel/$novel/?";
		$header = file_get_contents("templates/_header.html");
		$footer = file_get_contents("templates/_footer.html");

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);

		//match time released
		$time = getTime($result);
		$new = timeIsNew($time);
		$new = $new[0] ? "NEW" : "";
		
		$data["novel"] = $novel;
		$data["novelName"] = $novelName;
		$data["time"] = $time[0];
		$data["new"] = $new;
		
		// Get page templates and interpolate
		$template = file_get_contents("templates/homePage.html");
		$str .= interpolate($data, $template);
	}	
	
	echo $header . $str . $footer;
}
	
function getTocPage(){
	global $curl;
	$novel = $_GET['novel'];
	$novelName = ucwords(str_replace("-"," ",$novel));
	$url = "https://boxnovel.com/novel/$novel/?";
	$header = file_get_contents("templates/_header.html");
	$footer = file_get_contents("templates/_footer.html");
	$temp = '<p style="border-bottom: 1px solid;"><a href="index.php?novel={novel}&chapter={chapter}">{chapterName}</a> <span style="float: right;">
	{new} {time}</span></p>';
	$str = '';

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
	$template = file_get_contents("templates/tocPage.html");

	// Interpolate for each chapters
	for ($i = 0; $i < sizeof($name); $i++) {
		preg_match_all('!(\d)+[^\s]!', $name[$i], $matches);
		$var = implode('', $matches[0]);
		$var = str_replace(' ', '', $var);

		if (isset($time[$i])) {
			$data["novel"] = $novel;
			$data["chapter"] = $var;
			$data["chapterName"] = $name[$i];
			$data["new"] = $new[$i];
			$data["time"] = $time[$i];
		}
		
		$str = $str . interpolate($data, $temp);
	}
	
	// Interpolate the chapters into main template
	$str = str_replace("{content}", $str, $template);
	echo $header . $str . $footer;	
}
	
function getChapterPage(){
	global $curl;
	$novel = $_GET['novel'];
	$chapter = $_GET['chapter'];
	$header = file_get_contents("templates/_header.html");
	$footer = file_get_contents("templates/_footer.html");
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
		preg_match_all('!\d+[^\s]*[^(039;)]*[\-]*!', $name[$i], $matches);
		$var = implode('', $matches[0]);
		$var = explode('-', $var);
		$var = str_replace(' ', '', $var[0]);

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
	
	if(sizeof($match[0]) > 0) {
		$content = $match[0];
		$startIndex = $match[0][0][1];
		$startContent = substr($result,$startIndex);
	
		$arr = explode("</div>",$startContent);
		$content = $arr[0];
		$content .= "</div>";
	} else {
		$content = '<center><h1>Chapter not available!</h1></center>';
	}
	 
	// Get navigation for interpolation
	$chapter = intval($chapter);
	$isLatestChapter = $chapter == $latest_chapter ? true : false;
	$chapterNavigation = chapterNavigation($novel, $chapter, $isLatestChapter);
	
	// Bind data to send for interpolation
	$data = [];
	$data["novelName"] = $novelName;
	$data["chapter"] = $chapter;
	$data["chapterName"] = $chapterName;
	$data["chapterNavigation"] = $chapterNavigation;
	$data["content"] = $content;
	
	// Get page templates and interpolate
	$template = file_get_contents("templates/chapterPage.html");
	$str = interpolate($data, $template);
	
	// Call templates
	echo $header . $str . $footer;
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