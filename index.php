<?php require_once "function.php" ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tic Tac Toe</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link rel="stylesheet" type="text/css" href="Milad.css">

  <link rel="shortcut icon" type="text/css" href="tictactoe.png">
</head>
<body>
  <div class="container" id="container">
      <?php echo game(); ?>
  </div>
  <footer>
    <?php echo "&copy" ?> Designed By <span id="milad">Milad  SorkheZadeh</span>
  </footer>
</body>
</html>