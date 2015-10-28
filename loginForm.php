<?php
  if (!isset($_REQUEST['challenge'])) {
    redirect($config['UAM_URL'] . '/prelogin');
  }

  $chal = htmlspecialchars($_REQUEST['challenge']);
  $userurl = htmlspecialchars($_REQUEST['userurl']);

  if (isset($_REQUEST['errMsg'])) {
    $errMsg = htmlspecialchars($_REQUEST['errMsg']);
  }
  else if ($_REQUEST['wrongPwd'] == '1') {
    $errMsg = 'Wrong username or password.'; //Customisable text
  }
  else {
    $errMsg = '';
  }
?>
<html>
  <head>
    <title>My hotspot</title>
  </head>

  <body>
    <h1>My hotspot</h1>
    <p>Please enter your username or password</p>
    <?php
      if ($errMsg != '') {
        echo '<h3>' . $errMsg . '</h3>';
      }
    ?>
    <form action="login.php" method="post">
      <input type="hidden" name="challenge" value="<?php echo $chal; ?>" />
      <?php
        if ($userurl != '') {
          echo '<input type="hidden" name="userurl" value="' . $userurl . '" />';
        }
      ?>

      Username: <input type="text" name="UserName" /><br />
      Password: <input type="password" name="Password" /><br />
      <input type="submit" value="Login" />
    </form>
  </body>
</html>
