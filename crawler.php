<?php 
session_start();
$curl = curl_init();

if(isset($_SESSION['timeTrack']))
	$timeTrack = $_SESSION['timeTrack'];
else
	$timeTrack = array();

$newTime = array();

if (!isset($_GET['novel'])){ 
	$novels = array("king-of-gods", "the-legend-of-futian", "reincarnation-of-the-strongest-sword-god", "library-of-heavens-path", "mmorpg-martial-gamer");

?>

<!DOCTYPE html>
	<html>
		<head>
			<title>My Novel</title>
			<meta name="viewport" content="width=device-width, initial-scale=1">
		</head>
		<body style=" background-color: rgb(30, 30, 30);">
			<div style="margin: 10px; color: white;">

<?php
	$index = 0;

	foreach ($novels as $novel) { 
		$timeChange = false;
		$novelName = ucwords(str_replace("-"," ",$novel));
		$url = "https://boxnovel.com/novel/$novel/?";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curl);

		//match time released
		preg_match_all('!<span class="chapter\-release\-date"> <i>(.*?)<\/i> <\/span>!', $result, $match);
		$time = $match[1];

		if($time[0] !== $timeTrack[$index]){
			//Extract numbers from time
			preg_match_all('!\d+\s!', $time[0], $matches);
			$newTimeNum = implode('', $matches[0]);
			$newTimeNum = str_replace(' ', '', $newTimeNum);

			preg_match_all('!\d+\s!', $timeTrack[0], $matches);
			$oldTimeNum = implode('', $matches[0]);
			$oldTimeNum = str_replace(' ', '', $oldTimeNum);

			//Extract time call (secs, minutes, hours, days)
			$newTimeCall = preg_replace('/[0-9]+/', '', $time[0]);
			$newTimeCall = str_replace(' ', '', $newTimeCall);

			$oldTimeCall = preg_replace('/[0-9]+/', '', $timeTrack[0]);
			$oldTimeCall = str_replace(' ', '', $oldTimeCall);

			//Compare time then compare numbers
			switch($newTimeCall){
				case 'days':
										$timeChange = false;
										break;
				case 'hours':
										if($oldTimeCall == 'days')
											$timeChange = true;
										else if($oldTimeCall == 'hours')
											$timeChange = ($newTimeNum < $oldTimeNum);
										break;
				case 'minutes':
										if($oldTimeCall == 'days' || $oldTimeCall == 'hours')
										$timeChange = true;
									else if($oldTimeCall == 'minutes')
											$timeChange = ($newTimeNum < $oldTimeNum);
										break;
				case 'seconds':
				if($oldTimeCall == 'days' || $oldTimeCall == 'hours' || $oldTimeCall == 'minutes')
										$timeChange = true;
									else if($oldTimeCall == 'seconds')
											$timeChange = ($newTimeNum < $oldTimeNum);
										break;
					
			}

		}

		array_push($newTime, $time[0]);

		$index++;
?>
		

				<p><a href="crawler.php?novel=<?= $novel ?>" style="color: white"><?= $novelName ?></a><span style="float: right;"><?php if($timeChange) echo "<i>NEW</i>" ?> <?= $time[0] ?></span></p></p>
				<hr>
<?php
	}
	$_SESSION['timeTrack'] = $newTime;
?>
			</div>
		</body>
			<script>
				function hideImg() {
					var img = document.getElementsByTagName("img");
					img[0].parentNode.removeChild(img[0]);
				}

				window.onload = hideImg;
			</script>
	</html>

<?php

} else if (isset($_GET['novel']) && !isset($_GET['chapter'])) {

	$novel = $_GET['novel'];
	$novelName = ucwords(str_replace("-"," ",$novel));

	?>

	<!DOCTYPE html>
	<html>
	<head>
		<title>My Novel - <?= $novelName ?></title>
	    <meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="style.css">
	</head>
	<body style=" background-color: rgb(30, 30, 30);">
		<div style="margin: 10px; color: white;">
			<?php 
					echo "<h2>". $novelName ."</h2>";
					echo "<h3><a href='crawler.php'>Back to home</a></h3>";
					$url = "https://boxnovel.com/novel/$novel/?";

					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

					$result = curl_exec($curl);

					$name = array();
					$time = array();

					//match chapters
					preg_match_all('!<a href="https:\/\/boxnovel\.com\/novel\/' . addslashes($novel) . '\/.*?"> Chapter(.*?)<\/a>!', $result, $match);
					$name = $match[1];

					//match time released
					preg_match_all('!<span class="chapter\-release\-date"> <i>(.*?)<\/i> <\/span>!', $result, $match);
					$time = $match[1];

					$latest_chapter = 0;

					for ($i = 0; $i < sizeof($name); $i++) {
						preg_match_all('!\d+\s!', $name[$i], $matches);
						$var = implode('', $matches[0]);
						$var = str_replace(' ', '', $var);

						if (isset($time[$i])) {
			?>
							
							<p style="border-bottom: 1px solid;"><a href="crawler.php?novel=<?= $novel ?>&chapter=<?= $var ?>"><?= $name[$i] ?></a> <span style="float: right;"> <?= $time[$i] ?></span></p>

			<?php	
						}
					}
			?>
		</div>
	</body>
			<script>
				function hideImg() {
					var img = document.getElementsByTagName("img");
					img[0].style.display = "none";
				}

				window.onload = hideImg;
			</script>
	</html>
	
	<?php } else {
	$novel = $_GET['novel'];
	$chapter = $_GET['chapter'];
	    
	$url = "https://boxnovel.com/novel/$novel/?";

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($curl);

	$name = array();

	//match chapters
	preg_match_all('!<a href="https:\/\/boxnovel\.com\/novel\/' . addslashes($novel) . '\/.*?"> Chapter(.*?)<\/a>!', $result, $match);
	$name = $match[1];

	$latest_chapter = 0;
	$chapterName = "Chapter name not found";

	for ($i = 0; $i < sizeof($name); $i++) {
		preg_match_all('!\d+\s!', $name[$i], $matches);
		$var = implode('', $matches[0]);
		$var = str_replace(' ', '', $var);

		if ($i == 0)
			$latest_chapter = $var;

		if ($var == $chapter) 
			$chapterName = $name[$i];
	}


    //GET CONTENT
	$url = "https://boxnovel.com/novel/$novel/chapter-" . $chapter . "/";

	$novelName = ucwords(str_replace("-"," ",$novel));

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($curl);

	preg_match_all('!<div class="cha\-words">(.*?)<\/div>!', $result, $match);
	$content = $match[1];
	?>

	<!DOCTYPE html>
	<html>
	<head>
		<title>My Novel - <?= $novelName . " - Chapter " . $chapter ?></title>
	    <meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="style.css">
	</head>
	<body style=" background-color: rgb(30, 30, 30);">
		<div style="margin: 10px; padding-top: 20px; padding-bottom: 20px; color: white;">
			<h2><?= $novelName . " - Chapter " . $chapter ?></h2>
			<h3><?= $chapterName ?></h3>
			<center>
				<p style="padding: 10px;">
				<a href="crawler.php?novel=<?= $novel ?>&chapter=<?= $chapter-1 ?>">< Prev</a>
				|
				<a href="crawler.php?novel=<?= $novel ?>" style="color: white">TOC</a>

			<?php if ($chapter < $latest_chapter) { ?>
				|
				<a href="crawler.php?novel=<?= $novel ?>&chapter=<?= $chapter+1 ?>">Next ></a>
				</p>
			<?php } ?>
			</center>
			
			<?php if(sizeof($content)!=0) { ?>

			<div style="font-family: 'Montserrat'"><?= $content[0] ?></div>
			
			<?php } else { ?>

			<center>
				<h1>Chapter not available!</h1>
			</center>

			<?php } ?>

			<h2><?= "Chapter " . $chapter ?></h2>
			<center>
				<p style="padding: 10px;">
				<a href="crawler.php?novel=<?= $novel ?>&chapter=<?= $chapter-1 ?>">< Prev</a>
				|
				<a href="crawler.php?novel=<?= $novel ?>" style="color: white">TOC</a>

    			<?php if ($chapter < $latest_chapter) { ?>
    				|
    				<a href="crawler.php?novel=<?= $novel ?>&chapter=<?= $chapter+1 ?>">Next ></a>
    				</p>
    			<?php } ?>
			</center>
		</div>
		<script>
			function hideImg() {
				var img = document.getElementsByTagName("img");
				img[0].style.display = "none";
			}

			window.onload = hideImg;
		</script>
	</body>
	</html>
<?php }

clearstatcache();