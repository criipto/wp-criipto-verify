<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use Jumbojett\OpenIDConnectClient;

$sessionShortcodeArray = json_decode($_SESSION['shortcode'], true);

$oidc = new OpenIDConnectClient(
    "https://" . $sessionShortcodeArray['domain'],
    $sessionShortcodeArray['client_id']
);

/** 
 * When working on localhost, https connection can fail. As a work around, set setVerifyHost and setVerifyPeer to false when
 * $_SERVER['SERVER_NAME'] is equal localhost. 
 */
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $setVerifyAuth = false;
} else {
    $setVerifyAuth = true;
}

$oidc->setVerifyHost($setVerifyAuth);
$oidc->setVerifyPeer($setVerifyAuth);

if (isset($_GET['signout'])) {
    session_destroy();
    $oidc->signOut('', $sessionShortcodeArray['afterLogOutRedirect']);
} else {

    $implicit = $sessionShortcodeArray['implicit'] == '1';
    $oidc->setAllowImplicitFlow($implicit);
    $oidc->setRedirectURL($sessionShortcodeArray['redirect_uri']);
    $oidc->addAuthParam(array('acr_values' =>  $sessionShortcodeArray['acr_values']));

    if ($implicit) {
        $oidc->setResponseTypes(array('id_token'));
        $oidc->addAuthParam((array('response_mode' => 'form_post')));
    } else {
        $oidc->setResponseTypes(array('code'));
        $oidc->addAuthParam((array('response_mode' => 'query')));
    }
    try {
        $oidc->authenticate();
    } catch (OpenIDConnectClientException $e) {
        return "<div class='criipto-verify-error'>" . $e . "</div>";
    }
}
