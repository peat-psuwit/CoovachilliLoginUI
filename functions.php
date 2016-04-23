<?php
  function redirect($url) {
    header("Location: " . $url);
    exit;
  }

  function goToFile($file) {
    include $file;
    exit;
  }
?>
