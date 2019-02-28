<h2><?= $novelName ?></h2>
<h3><a href='index.php'>Back to home</a></h3>

<?php
foreach($chapters as $chapter) {
?>
  <p style="border-bottom: 1px solid;"><a href="index.php?novel=<?= $novel ?>&chapter=<?= $chapter["var"] ?>"><?= $chapter["name"] ?></a> <span style="float: right;"> <?php if($chapter["new"]) echo "NEW " ?><?= $chapter["time"] ?></span></p>
<?php
}
?>

