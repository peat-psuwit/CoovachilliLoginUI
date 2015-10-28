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

function handleUsernamePasswordLogin() {
	global $config;

  if (!isset($_REQUEST['UserName']) || !isset($_REQUEST['Password'])) {
    redirect($config['UAM_UI_URL'] . '/error.php?code=invalidReq');
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
			redirect($config['UAM_UI_URL'] . '/WISPrError.php?code=254');
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

	if (isset($_REQUEST['redirurl'])) {
		$url = $_REQUEST['redirurl'];
	}
	else {
		$url = $_REQUEST['userurl'];
	}

	redirect($config['UAM_UI_URL'] . '/redirect.php?userurl=' . urlencode($url));
}

function redirectToLoginPage() {
	global $config;

	if (!isset($_REQUEST['challenge'])) {
		redirect($config['UAM_URL'] . '/prelogin');
	}
	$url = $config['UAM_UI_URL'] . '/loginForm.php?challenge=' . urlencode($_REQUEST['challenge']);

	if (isset($_REQUEST['userurl'])) {
		$url .= '&userurl=' . urlencode($_REQUEST['userurl']);
	}

	if ($_REQUEST['res'] == 'failed') {
		if (isset($_REQUEST['reply'])) {
			$url .= '&errMsg=' . urlencode($_REQUEST['reply']);
		}
		else {
			$url .= '&wrongPwd=1';
		}
	}

	redirect($url);
}

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
