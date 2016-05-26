<?php
/* Peat's version of coova chilli hotspot login page */

/*
 * possible Cases:
 *
 *  attempt to login                          login=Login
 *  1: Login successful                       res=success
 *  2: Login failed                           res=failed
 *  3: Logged out                             res=logoff
 *  4: Tried to login while already logged in res=already
 *  5: Not logged in yet                      res=notyet
 * 11: Popup                                  res=popup1
 * 12: Popup                                  res=popup2
 * 13: Popup                                  res=popup3
 *  0: It was not a form request              res=''
 *
 * Read query parameters which we care about
 *
 * $_GET['res'];
 * $_GET['challenge'];
 * $_GET['uamip'];
 * $_GET['uamport'];
 * $_GET['reply'];
 * $_GET['userurl'];
 * $_GET['timeleft'];
 * $_GET['redirurl'];
 *
 * Read form parameters which we care about
 *
 * $_GET['username'];
 * $_GET['password'];
 * $_GET['chal'];
 * $_GET['login'];
 * $_GET['logout'];
 * $_GET['prelogin'];
 * $_GET['res'];
 * $_GET['uamip'];
 * $_GET['uamport'];
 * $_GET['userurl'];
 * $_GET['timeleft'];
 * $_GET['redirurl'];
 * $_GET['store_cookie'];
 */

require_once 'config.php';
require_once 'functions.php';

function readUamUrl() {
	global $config;

	if (isset($_REQUEST['uamip']) && isset($_REQUEST['uamport'])) {
		$config['UAM_URL']='http://'.$_REQUEST['uamip'].':'.$_REQUEST['uamport'];
	}
	else if (isset($_REQUEST['uamurl'])) {
		$config['UAM_URL']=$_REQUEST['uamurl'];
	}
}

function handleUsernamePasswordLogin() {
	global $config;

  if (!isset($_REQUEST['UserName']) || !isset($_REQUEST['Password'])) {
		global $errorCode;
    $errorCode = 'invalidReq';
		goToFile("./error.php");
  }
  else if (!isset($_REQUEST['challenge'])) {
    redirect($config['UAM_URL'] . '/prelogin');
  }

  if ($config['UAM_RAD_PROTO'] == 'pap') {
    $response = exec('chilli_response -pap ' .
                escapeshellarg($_REQUEST['challenge']) . ' ' .
                escapeshellarg($config['UAM_SECRET']) . ' ' .
                escapeshellarg($_REQUEST['Password']));
    $url = $config['UAM_URL'] .
           '/login?username=' . urlencode($_REQUEST['UserName']) .
           '&password=' . urlencode($response);
  }
  else if ($config['UAM_RAD_PROTO'] == 'mschapv2') {
    $response = exec('chilli_response -nt ' .
                escapeshellarg($_REQUEST['challenge']) . ' ' .
                escapeshellarg($config['UAM_SECRET']) . ' ' .
                escapeshellarg($_REQUEST['UserName']) . ' ' .
                escapeshellarg($_REQUEST['Password']));
    $url = $config['UAM_URL'] .
           '/login?username=' . urlencode($_REQUEST['UserName']) .
           '&ntresponse=' . urlencode($response);
  }
  else {
    $response = exec('chilli_response ' .
                escapeshellarg($_REQUEST['challenge']) . ' ' .
                escapeshellarg($config['UAM_SECRET']) . ' ' .
                escapeshellarg($_REQUEST['Password']));
    $url = $config['UAM_URL'] .
           '/login?username=' . urlencode($_REQUEST['UserName']) .
           '&response=' . urlencode($response);
  }

  if (isset($_REQUEST['userurl'])) {
    $url = $url . '&userurl=' . urlencode($_REQUEST['userurl']);
  }

  redirect($url);
}

function handleWISPrLogin() {
	global $config;

	$WISPrVersion = '1.0';
	if (isset($_REQUEST['WISPrVersion'])) {
		$WISPrVersion = $_REQUEST['WISPrVersion'];
	}
	if ($WISPrVersion != '1.0' && $WISPrVersion != '2.0') {
		$WISPrVersion = '2.0';
	}

	if (isset($_REQUEST['WISPrEAPMsg'])) {
		if ($WISPrVersion != '2.0' || isset($_REQUEST['Password'])) {
			global $errorCode;
			$errorCode = "254";
			goToFile("./WISPrError.php");
		}
		else {
			redirect($config['UAM_URL'] .
							 '/login?username=' . urlencode($_REQUEST['UserName']) .
							 '&WISPrEAPMsg=' . urlencode($_REQUEST['WISPrEAPMsg']) .
							 '&WISPrVersion=2.0');
		}
	}
	else {
		handleUsernamePasswordLogin();
	}
}

function validateReqest() {
	//Do nothing until I figure out algorithm
}

function redirectToTarget() {
	global $config;
	global $url;

	if (isset($_REQUEST['redirurl'])) {
		$url = $_REQUEST['redirurl'];
	}
	else {
		$url = $_REQUEST['userurl'];
	}

	$url = htmlspecialchars($url);
	goToFile("./redirect.php");
}

function redirectToLoginPage() {
	global $config;
	global $chal, $userurl, $errMsg;

	if (!isset($_REQUEST['challenge'])) {
		redirect($config['UAM_URL'] . '/prelogin');
	}

	$chal = htmlspecialchars($_REQUEST['challenge']);

	if (isset($_REQUEST['userurl'])) {
		$userurl = htmlspecialchars($_REQUEST['userurl']);
	} else {
		$userurl = '';
	}

	if ($_REQUEST['res'] == 'failed') {
		if (isset($_REQUEST['reply'])) {
			$errMsg = htmlspecialchars($_REQUEST['reply']);
		}
		else {
			//TODO: use string file, for translation and customization.
			$errMsg = 'Wrong username or password.';
		}
	}	else {
		$errMsg = '';
	}

	goToFile("./loginForm.php");
}

readUamUrl();

//Handle login
if ($_REQUEST['res'] == 'wispr') {
	handleWISPrLogin();
}
else if ($_REQUEST['UserName'] != '') {
	handleUsernamePasswordLogin();
}
else {
	validateReqest();
	//Handle redirect to target
	if ($_REQUEST['res'] == 'success' || $_REQUEST['res'] == 'already') {
		redirectToTarget();
	}

	//Handle redirect to login
	else {
		redirectToLoginPage();
	}
}
?>
