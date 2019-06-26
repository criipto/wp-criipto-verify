<?php

session_start();
require __DIR__ . '/vendor/autoload.php';
use Jumbojett\OpenIDConnectClient;

if (isset($_POST['id_token'])) {

    $id_token = $_POST['id_token'];
} else if (isset($_GET['code'])) {
    $wellKnown = json_decode($_SESSION['wellKnown'], true);
    $criiptoSettings = json_decode($_SESSION["criiptoSettings"], true);


    $url = $wellKnown['token_endpoint'];

    $secret_id = $criiptoSettings['secret_id'];
    //HTTP password.
    $client_id = $criiptoSettings['client_id'];

    $oidc = new OpenIDConnectClient(
        'https://svenbjorn.criipto.id',
        $client_id,
        $secret_id
    );
    $oidc->setVerifyHost(false);
    $oidc->setVerifyPeer(false);
    $oidc->providerConfigParam(
        array(
            'token_endpoint' => $url
        )
    );
    $oidc->addScope('openid');
    $oidc->setRedirectURL($criiptoSettings['redirect_uri']);
    


    //Perform the auth and return the token (to validate check if the access_token property is there and a valid JWT) :
    $token = JSON_encode($oidc->authenticate());
    echo $token;
} else {
    echo "Bad request";
}
