<?php
function setWellKnown()
{

    $isDeveloperMode  = $_SERVER['SERVER_NAME'] != 'localhost';
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => $isDeveloperMode,
            "verify_peer_name" => $isDeveloperMode,
        ),
    );
    return file_get_contents("https://easyid.www.grean.id/.well-known/openid-configuration", false, stream_context_create($arrContextOptions));
}

add_action('setSessionWellKnown', 'setWellKnown');

/**
 * Register a custom menu page.
 */
function wpdocs_register_criipto_settings_menu_page()
{
    //add menu and icon
    add_menu_page('Criipto Settings', 'Criipto Settings', 'administrator', __FILE__, 'criipto_settings_page', MAIN_PLUGIN_URL . 'assets/icon.png');

    //call register settings function
    add_action('admin_init', 'register_criipto_plugin_settings');
}
add_action('admin_menu', 'wpdocs_register_criipto_settings_menu_page');

//register form settings 
function register_criipto_plugin_settings()
{
    $args = array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => NULL,
    );
    register_setting('criipto_settings_group', 'criipto-login-method', $args);
    register_setting('criipto_settings_group', 'criipto-domain', $args);
    register_setting('criipto_settings_group', 'criipto-client-id', $args);
    register_setting('criipto_settings_group', 'criipto-client-secret', $args);
    register_setting('criipto_settings_group', 'criipto-implicit', $args);
    register_setting('criipto_settings_group', 'criipto-claims', $args);
    register_setting('criipto_settings_group', 'criipto-redirect-uri', $args);
    register_setting('criipto_settings_group', 'criipto-after-logout-redirect', $args);
    register_setting('criipto_settings_group', 'criipto-admin-port', $args);
    register_setting('criipto_settings_group', 'criipto-admin-scheme', $args);
    register_setting('criipto_settings_group', 'criipto-first-install', $args);
}

//Setting page content
function criipto_settings_page()
{    

    if (get_option('criipto-first-install') !== 'obsolete'){
        add_option('criipto-first-install', 'firstInstall');
    }
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    } else {
        $scheme = 'http';
    }
    if ($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443') {
        $port = '';
    } else {
        $port = $_SERVER['SERVER_PORT'];
    }
    $parse_url = parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    ?>
    <div id="criipto-setting-content">
        <div class="criipto-header">
            <div class="criipto-header-border">
                <img src="https://criipto.com/images/logo-criipto-dark-3.svg">
            </div>
        </div>
        <div class="criipt-readonly-url">
            <h1>
                !important: 
            </h1>
            <h3>Add these url's to manage.criipto.id</h3>
            <table>
                <tr>
                    <th>Callback url</th>
                    <td><?php echo $parse_url['scheme'] . '://' . $parse_url['port'] . $parse_url['host'] . MAIN_PLUGIN_URL ?>openIdConnect.php</td>

                </tr>
                <tr>
                    <th>Redirect url after log out</th>
                    <td><?php echo get_option('criipto-after-logout-redirect') != home_url() ? $parse_url['scheme'] . '://' . $parse_url['port'] . $parse_url['host'] . '/' . get_option('criipto-after-logout-redirect') : get_option('criipto-after-logout-redirect') ?></td>
                </tr>
            </table>
        </div>
        <div class="criipto-content">
            <form method="post" action="options.php">
                <?php settings_fields('criipto_settings_group'); ?>
                <?php do_settings_sections('criipto_settings_group'); ?>
                <input type="hidden" id="criipto-redirect-uri" name="criipto-redirect-uri" value="<?php echo MAIN_PLUGIN_URL ?>openIdConnect.php" />
                <input type="hidden" id="criipto-admin-port" name="criipto-admin-port" value="<?php echo $port ?>" />
                <input type="hidden" id="criipto-admin-scheme" name="criipto-admin-scheme" value="<?php echo $scheme ?>" />
                <input type="hidden" id="criipto-first-install" name="criipto-first-install" value="<?php echo 'obsolete' ?>" />
                <h1>Criipto WordPress Plugin Settings</h1>
                <p>Basic settings related to the Criipto integration.</p>

                <table>

                    <tr>
                        <th>
                            *Domain
                        </th>
                        <td>
                            <input type="text" id="criipto-domain" name="criipto-domain" value="<?php echo esc_attr(get_option('criipto-domain')); ?>" placeholder="*.criipto.id or your own custom domain" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            *Login Method
                        </th>
                        <td>
                            <select id="criipto-login-method" name="criipto-login-method">
                                <?php
                                foreach (json_decode(setWellKnown(), true)['acr_values_supported'] as $acr_value) { ?>
                                    <option <?php echo get_option('criipto-login-method') == $acr_value || (get_option('criipto-login-method') == false && $acr_value == 'urn:grn:authn:dk:nemid:poces')  ? 'selected' : '' ?> value="<?php echo $acr_value ?>"><?php echo $acr_value ?></option>
                                <?php
                            }
                            ?>
                            </select>
                            <i>

                            </i>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            *Client Id
                        </th>
                        <td>
                            <input type="text" id="criipto-client-id" name="criipto-client-id" value="<?php echo esc_attr(get_option('criipto-client-id')); ?>" placeholder="urn:easyid:* or your own custom value" />
                        </td>

                    </tr>
                    <tr>
                        <th scope="row">
                            Redirect page after<br> log out
                        </th>
                        <td>
                            <input type="text" id="criipto-after-logout-redirect" name="criipto-after-logout-redirect" value="<?php echo esc_attr(get_option('criipto-after-logout-redirect') != '' ? get_option('criipto-after-logout-redirect') : home_url()); ?>" />
                            <i>
                                Default is root homepage name<br>
                            </i>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Client secret
                        </th>
                        <td>
                            <input type="password" id="criipto-client-secret" name="criipto-client-secret" value="<?php echo esc_attr(get_option('criipto-client-secret')); ?>" />
                            <span class="criipto-toggle-secret">Show</span>
                            <script type="text/javascript">

                            </script>
                            <i>
                                Get 'Client secret' from manage.criipto.id
                            </i>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Show me user information
                        </th>
                        <td>
                            <input type="checkbox" id="criipto-claims" name="criipto-claims"  value="1" <?php echo get_option('criipto-claims') == "1" || get_option('criipto-first-install') == 'firstInstall' ? "checked" : "" ?> />
                            <i>For testing purpose, shows you the available user information returned from Criipto Verify</i>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Implicit
                        </th>
                        <td>
                            <input type="checkbox" id="criipto-implicit" name="criipto-implicit" value="1" <?php echo get_option('criipto-implicit') == "1" ? "checked" : "" ?> />

                            <i>Return an id token directly. Default is the authorization code flow</i>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
<?php
}
