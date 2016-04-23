<?php
  if ($errorCode == 'invalidReq') {
    $error = "The request was incomplete."; //Customisable
  }
  else {
    $error = "Unknown error."; //Customisable
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Authentication error</title>
  </head>

  <body>
    <h1>There was error in processing your request.</h1>
    <p>The error is: <?php echo $error; ?></p>
    <p>Please <a href="<?php echo $config['UAM_URL'] . '/prelogin'; ?>">retry the login.</a></p>
  </body>
</html>
