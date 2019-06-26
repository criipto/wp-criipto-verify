<?php
function build_url(array $parts)
{
    return (isset($parts['scheme']) ? "{$parts['scheme']}://" : '') . ((isset($parts['user']) || isset($parts['host'])) ? '' : '') . (isset($parts['user']) ? "{$parts['user']}" : '') . (isset($parts['pass']) ? ":{$parts['pass']}" : '') . (isset($parts['user']) ? '@' : '') . (isset($parts['host']) ? "{$parts['host']}" : '') . (isset($parts['port']) ? ":{$parts['port']}" : '') . (isset($parts['path']) ? "{$parts['path']}" : '') . (isset($parts['query']) ? "?{$parts['query']}" : '') . (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
}

/**
 * Add iframe to page where shortcode is [criipto].
 */

function add_iframe($atts)
{

    if (get_option('criipto-domain') === '') {
        $isDeveloperMode  = $_SERVER['SERVER_NAME'] != 'localhost';
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => $isDeveloperMode,
                "verify_peer_name" => $isDeveloperMode,
            ),
        );
        if (!file_get_contents("https://" . get_option('criipto-domain') . "/.well-known/openid-configuration", false, stream_context_create($arrContextOptions))) {
            return "<div class='criipto-error'>The criipto domain settings are not provided correctly. Please visit criipto.com/wordpress for how to configure the settings.</div>";;
        }
    }

    if (get_option('criipto-admin-scheme') == 'https') {
        $authority = 'https';
    } else if (get_option('criipto-admin-scheme') == 'http') {
        $authority = 'http';
    } else {
        $authority = '';
    }

    $port = get_option('criipto-admin-port') != '' ? get_option('criipto-admin-port') : '';

    $redirectUri = $_SERVER['SERVER_NAME'] . get_option('criipto-redirect-uri');
    $parsed_redirectUri = parse_url($redirectUri);
    $port != '' ? $parsed_redirectUri['port'] = ':' . $port : '';
    $parsed_redirectUri['scheme'] = $authority;
    $redirectUri = build_url($parsed_redirectUri);

    $afterLogOutRedirect = parse_url(get_option('criipto-after-logout-redirect'))['path'] ? '/' . get_option('criipto-after-logout-redirect') : '';
    $parsed_afterLogOutRedirect = parse_url($afterLogOutRedirect);
    $port != '' ? $parsed_afterLogOutRedirect['port'] = ':' . $port : '';
    $parsed_afterLogOutRedirect['scheme'] = $authority;
    $parsed_afterLogOutRedirect['host'] = $_SERVER['SERVER_NAME'];
    $afterLogOutRedirect = build_url($parsed_afterLogOutRedirect);

    $atts = shortcode_atts(array(
        'acr_values' => get_option('criipto-login-method'),
        'client_id' => get_option('criipto-client-id'),
        'implicit' => get_option('criipto-implicit'),
        'redirect_uri' => $redirectUri,
        'domain' => get_option('criipto-domain'),
        'authority' => $authority,
        'port' =>  $port,
        'afterLogOutRedirect' => $afterLogOutRedirect
    ), $atts, 'criipto');

    $_SESSION['shortcode'] = json_encode($atts);
    $sessionShortcodeArray = json_decode($_SESSION['shortcode'], true);
    if (!isset($_SESSION['sessionId'])) {
        return "
        <div id='criipto-login'>
            <iframe src='" . plugins_url('/openIdConnect.php', __FILE__) . "' id='criipto-verify' title='Criipto-Verify' class='login-frame-" . substr($sessionShortcodeArray['acr_values'], strrpos($sessionShortcodeArray['acr_values'], ':') + 1) . "' allowfullscreen='true' scrolling='no' frameborder='0' class='hidden-frame'>
            </iframe>
        </div>
        ";
    } else if (get_option('criipto-claims') === '1' && isset($_SESSION['sessionId'])) {
        $array = json_decode($_SESSION['VerifiedClaims']);
        $rows = '';
        foreach ($array as $key =>  $value) {
            $rows .= "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>";
        }
        echo "
        <p id='criipto-signout'>Logout</p>
        <table>
            <tr>
                <th>Type</th>
                <th>Attribute</th>
            </tr>
            " . $rows . "
        </table>";
    } else {
        return "<p id='criipto-signout'>Logout</p>";
    }
}

add_shortcode('criipto', 'add_iframe');
