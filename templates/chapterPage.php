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

<?php if(strlen($content)!=0) { ?>

<div style="font-family: 'Montserrat'"><?= $content ?></div>

<?php } else { ?>

<center>
  <h1>Chapter not available!</h1>
</center>

<?php } ?>

<h3 style="color:white"><?= $chapterName ?></h3>
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