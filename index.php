<?php 
$curl = curl_init();

function getNovels(){
    return array("i-alone-level-up", "tales-of-demons-and-gods", "versatile-mage", "black-tech-internet-cafe-system", "survival-records-of-3650-days-in-the-otherworld", "legend-of-ling-tian", "monster-pet-evolution", "white-robed-chief", "the-human-emperor", "king-of-gods", "the-legend-of-futian", "reincarnation-of-the-strongest-sword-god", "library-of-heavens-path", "mmorpg-martial-gamer")
}

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

function stripScript($content) {
	$content = str_replace("<div class='code-block code-block-1' style='margin: 8px 0; clear: both;'>", '', $content);
	$content = str_replace("<div class='code-block code-block-2' style='margin: 8px 0; clear: both;'>", '', $content);
	$content = str_replace("<div class='code-block code-block-3' style='margin: 8px 0; clear: both;'>", '', $content);
	$content = str_replace("<div class='code-block code-block-4' style='margin: 8px 0; clear: both;'>", '', $content);
	$content = str_replace("<div class='code-block code-block-5' style='margin: 8px 0; clear: both;'>", '', $content);
	$content = str_replace('<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>', '', $content);
	$content = str_replace('<ins class="adsbygoogle"', '', $content);
	$content = str_replace('style="display:block; text-align:center;"', '', $content);
	$content = str_replace('data-ad-layout="in-article"', '', $content);
	$content = str_replace('data-ad-format="fluid"', '', $content);
	$content = str_replace('data-ad-client="ca-pub-4659203075673373"', '', $content);
	$content = str_replace('data-ad-slot="1888842457"></ins>', '', $content);
	$content = str_replace('<script>', '', $content);
	$content = str_replace('(adsbygoogle = window.adsbygoogle || []).push({});', '', $content);
	$content = str_replace('</script></div>', '', $content);
	$content = str_replace('<div class="ad c-ads custom-code body-bottom-ads">', '', $content);
	$content = str_replace('<!-- QC van ban hinh anh -->', '', $content);
	$content = str_replace('style="display:block"  data-ad-slot="6603264463" data-ad-format="auto" data-full-width-responsive="true"></ins>', '', $content);
	$content = str_replace('</div>


		</div>
	</div>
	</div>
	</div></div>', '', $content);
	
	return $content;
}

// returns string containing center > p > a tags.
function chapterNavigation($novel, $chapter, $isLatestChapter = false){
	$str = '<div style="color: white">';
	$str .= '<center>';
	$str .= '<p style="padding: 10px;">';
	
	if ($chapter !== 1){
		$linkPrev = 'index.php?novel='.$novel.'&chapter='.($chapter-1);
		$str .= '<a href="'.$linkPrev.'">< PREV</a>';
		$str .= '	|	';
	}
		
	$linkToc = 'index.php?novel='.$novel;
	$str .= '<a href="'.$linkToc.'" style="color: white">TOC</a>';
	$str .= '	|	';
	$str .= '<a href="./" style="color: white">HOME</a>';
	
	if (!$isLatestChapter){
		$linkNext = 'index.php?novel='.$novel.'&chapter='.($chapter+1);
		$str .= '	|	';
		$str .= '<a href="'.$linkNext.'">NEXT ></a>';
	}
	
	$str .= '<p style="padding: 10px;">';
	$str .= '</center>';
	$str .= '</div>';
	
	return $str;
	
}

function getHomePage(){
	global $curl;
	$novels = getNovels();
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
	preg_match_all('!Chapter [\d\s\w\'’\?\!\.\,\-\(\)(&#039;)]*!', $result, $match);
	$name = $match[0];

	//match time released
	$time = getTime($result);
	$isNew = timeIsNew($time);
		
	$latest_chapter = 0;
	$chapters = [];
	$template = file_get_contents("templates/tocPage.html");

	// Interpolate data for each chapters
	for ($i = 0; $i < sizeof($name); $i++) {
		$arr = explode(" ", $name[$i]);
		$arr = explode(" ", $arr[1]);
		$var = $arr[0];

		if($i < 30)
			$new = $isNew[$i] ? "NEW" : "";
		else
			$new = false;

		if (isset($time[$i])) {
			$data["novel"] = $novel;
			$data["chapter"] = $var;
			$data["chapterName"] = $name[$i];
			$data["new"] = $new;
			$data["time"] = $time[$i];
		}
		
		$str = $str . interpolate($data, $temp);
		if ($var == 1) break;
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
	preg_match_all('!Chapter [\d\s\w\'’\.\,\?\!\-\(\)(&#039;)]*!', $result, $match);
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

	// Quick fix for I Alone Level Up chapter 270
	$var = $novel == 'i-alone-level-up' && $var == '270' ? '270-end' : $var;


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
		
		$startContent = stripScript($startContent);
		$arr = explode("<div class=\"c-select-bottom\">",$startContent);
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
