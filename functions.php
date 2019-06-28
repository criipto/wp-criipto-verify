<?php

/**
 * Build url after parse url.
 */
function criipto_verify_build_url(array $parts)
{
    return (isset($parts['scheme']) ? "{$parts['scheme']}://" : '') . ((isset($parts['user']) || isset($parts['host'])) ? '' : '') . (isset($parts['user']) ? "{$parts['user']}" : '') . (isset($parts['pass']) ? ":{$parts['pass']}" : '') . (isset($parts['user']) ? '@' : '') . (isset($parts['host']) ? "{$parts['host']}" : '') . (isset($parts['port']) ? ":{$parts['port']}" : '') . (isset($parts['path']) ? "{$parts['path']}" : '') . (isset($parts['query']) ? "?{$parts['query']}" : '') . (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
}

/**
 * Add iframe to page where shortcode is [criipto].
 */

function criipto_verify_add_iframe($atts)
{
    $response = wp_remote_get("https://" . get_option('criipto-verify-domain') . "/.well-known/openid-configuration");
    if (get_option('criipto-verify-domain') === '' || get_option('criipto-verify-domain') == null) {
        if (!wp_remote_retrieve_body( $response )) {
            return "<div class='criipto-verify-error'>The Criipto Verify domain settings are not provided correctly. Please visit criipto.com/wordpress for how to configure the settings.</div>";;
        }
    }

    if (get_option('criipto-verify-admin-scheme') == 'https') {
        $authority = 'https';
    } else if (get_option('criipto-verify-admin-scheme') == 'http') {
        $authority = 'http';
    } else {
        $authority = '';
    }

    $port = get_option('criipto-verify-admin-port') != '' ? get_option('criipto-verify-admin-port') : '';

    $redirectUri = $_SERVER['SERVER_NAME'] . get_option('criipto-verify-redirect-uri');
    $parsed_redirectUri = parse_url($redirectUri);
    $port != '' ? $parsed_redirectUri['port'] = ':' . $port : '';
    $parsed_redirectUri['scheme'] = $authority;
    $redirectUri = criipto_verify_build_url($parsed_redirectUri);

    $afterLogOutRedirect = parse_url(get_option('criipto-verify-after-logout-redirect'))['path'] ? '/' . get_option('criipto-verify-after-logout-redirect') : '';
    $parsed_afterLogOutRedirect = parse_url($afterLogOutRedirect);
    $port != '' ? $parsed_afterLogOutRedirect['port'] = ':' . $port : '';
    $parsed_afterLogOutRedirect['scheme'] = $authority;
    $parsed_afterLogOutRedirect['host'] = $_SERVER['SERVER_NAME'];
    $afterLogOutRedirect = criipto_verify_build_url($parsed_afterLogOutRedirect);

    $atts = shortcode_atts(array(
        'acr_values' => get_option('criipto-verify-login-method'),
        'client_id' => get_option('criipto-verify-client-id'),
        'implicit' => get_option('criipto-verify-implicit'),
        'redirect_uri' => $redirectUri,
        'domain' => get_option('criipto-verify-domain'),
        'authority' => $authority,
        'port' =>  $port,
        'afterLogOutRedirect' => $afterLogOutRedirect
    ), $atts, 'criipto-verify');

    $_SESSION['shortcode'] = json_encode($atts);
    $sessionShortcodeArray = json_decode($_SESSION['shortcode'], true);
    if (!isset($_SESSION['sessionId'])) {
        return "
        <div id='criipto-verify-login'>
            <iframe src='" . plugins_url('/openIdConnect.php', __FILE__) . "' id='criipto-verify' title='Criipto-Verify' class='login-frame-" . substr($sessionShortcodeArray['acr_values'], strrpos($sessionShortcodeArray['acr_values'], ':') + 1) . "' allowfullscreen='true' scrolling='no' frameborder='0' class='hidden-frame'>
            </iframe>
        </div>
        ";
    } else if (get_option('criipto-verify-claims') === '1' && isset($_SESSION['sessionId'])) {
        $array = json_decode($_SESSION['VerifiedClaims']);
        $rows = '';
        foreach ($array as $key =>  $value) {
            $rows .= "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>";
        }
        echo "
        <p id='criipto-verify-signout'>Logout</p>
        <table>
            <tr>
                <th>Type</th>
                <th>Attribute</th>
            </tr>
            " . $rows . "
        </table>";
    } else {
        return "<p id='criipto-verify-signout'>Logout</p>";
    }
}

/**
 * Register the shortcode "criipto" 
 */
add_shortcode('criipto', 'criipto_verify_add_iframe');
