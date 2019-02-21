<?php 
session_start();
$curl = curl_init();

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
	foreach ($novels as $novel) { 
		$timeChange = false;
		$novelName = ucwords(str_replace("-"," ",$novel));
		$url = "https://boxnovel.com/novel/$novel/?";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curl);

		//match time released
		preg_match_all('!<span class="chapter\-release\-date">[^\t]*(.*)<i>(.*?)<\/i>!', $result, $match);
		$time = $match[0];

		array_push($newTime, $time);
?>
				<p><a href="index.php?novel=<?= $novel ?>" style="color: white"><?= $novelName ?></a><span style="float: right;"> <?= $time[0] ?></span></p></p>
				<hr>
<?php	}?>
			</div>
		</body>
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
					echo "<h3><a href='index.php'>Back to home</a></h3>";
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
					preg_match_all('!Chapter [\d\s\w\-\(\)]*!', $result, $match);
					$name = $match[0];

					//match time released
					preg_match_all('!<span class="chapter\-release\-date">[^\t]*(.*)<i>(.*?)<\/i>!', $result, $match);
					$time = $match[0];

					$latest_chapter = 0;

					for ($i = 0; $i < sizeof($name); $i++) {
						preg_match_all('!\d+\s!', $name[$i], $matches);
						$var = implode('', $matches[0]);
						$var = str_replace(' ', '', $var);

						if (isset($time[$i])) {
			?>
							
							<p style="border-bottom: 1px solid;"><a href="index.php?novel=<?= $novel ?>&chapter=<?= $var ?>"><?= $name[$i] ?></a> <span style="float: right;"> <?= $time[$i] ?></span></p>

			<?php	
						}
					}
			?>
		</div>
	</body>
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
	preg_match_all('!<a href="https:\/\/boxnovel\.com\/novel\/' . addslashes($novel) . '\/.*">[^\t]*(.*)Chapter(.*?)<\/a>!', $result, $match);
	$chapters = $match[0];

	//match name
	preg_match_all('!Chapter [\d\s\w\-\(\)]*!', $result, $match);
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
	$url = "https://boxnovel.com/novel/$novel/chapter-" . $chapter . "/";

	$novelName = ucwords(str_replace("-"," ",$novel));

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($curl);

	preg_match_all('!<div class="cha\-words">[^\t]*(.*)(.*?)<\/div>!', $result, $match);

	if(sizeof($match[0]) === 0)
		preg_match_all('!<div class="reading\-content">[^\t]*(.*)(.*?)<\/div>!', $result, $match);

	$content = $match[0];
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
				<a href="index.php?novel=<?= $novel ?>&chapter=<?= $chapter-1 ?>">< Prev</a>
				|
				<a href="index.php?novel=<?= $novel ?>" style="color: white">TOC</a>
				|
				<a href="./" style="color: white">HOME</a>

			<?php if ($chapter < $latest_chapter) { ?>
				|
				<a href="index.php?novel=<?= $novel ?>&chapter=<?= $chapter+1 ?>">Next ></a>
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

			<h3><?= $chapterName ?></h3>
			<center>
				<p style="padding: 10px;">
				<a href="index.php?novel=<?= $novel ?>&chapter=<?= $chapter-1 ?>">< Prev</a>
				|
				<a href="index.php?novel=<?= $novel ?>" style="color: white">TOC</a>
				|
				<a href="./" style="color: white">HOME</a>

    			<?php if ($chapter < $latest_chapter) { ?>
    				|
    				<a href="index.php?novel=<?= $novel ?>&chapter=<?= $chapter+1 ?>">Next ></a>
    				</p>
    			<?php } ?>
			</center>
		</div>
	</body>
	</html>
<?php }