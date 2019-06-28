<?php
function criipto_verify_setWellKnown()
{
    $response = wp_remote_get("https://" . get_option('criipto-verify-domain') . "/.well-known/openid-configuration");
    return wp_remote_retrieve_body( $response );
}

add_action('setSessionWellKnown', 'criipto_verify_setWellKnown');

/**
 * Register a custom menu page.
 */
function register_criipto_verify_settings_menu_page()
{
    //add menu and icon
    add_menu_page('Criipto Settings', 'Criipto Settings', 'administrator', __FILE__, 'criipto_verify_settings_page', CRIIPTO_VERIFY_MAIN_PLUGIN_URL . 'assets/icon.png');

    //call register settings function
    add_action('admin_init', 'register_criipto_verify_plugin_settings');
}
add_action('admin_menu', 'register_criipto_verify_settings_menu_page');

//register form settings 
function register_criipto_verify_plugin_settings()
{
    $args = array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => NULL,
    );
    register_setting('criipto_verify_settings_group', 'criipto-verify-login-method', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-domain', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-client-id', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-client-secret', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-implicit', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-claims', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-redirect-uri', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-after-logout-redirect', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-admin-port', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-admin-scheme', $args);
    register_setting('criipto_verify_settings_group', 'criipto-verify-first-install', $args);
}

//Setting page content
function criipto_verify_settings_page()
{    

    if (get_option('criipto-verify-first-install') !== 'obsolete'){
        add_option('criipto-verify-first-install', 'firstInstall');
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
    <div id="criipto-verify-setting-content">
        <div class="criipto-verify-header">
            <div class="criipto-verify-header-border">
                <img src="<?php echo CRIIPTO_VERIFY_MAIN_PLUGIN_URL ?>assets/logo-criipto-dark-3.svg">
            </div>
        </div>
        <div class="criipto-verify-readonly-url">
            <h1>
                !important: 
            </h1>
            <h3>Add these url's to manage.criipto.id</h3>
            <table>
                <tr>
                    <th>Callback url</th>
                    <td><?php echo esc_url($parse_url['scheme'] . '://' . $parse_url['port'] . $parse_url['host'] . CRIIPTO_VERIFY_MAIN_PLUGIN_URL) ?>openIdConnect.php</td>

                </tr>
                <tr>
                    <th>Redirect url after log out</th>
                    <td><?php echo esc_url(get_option('criipto-verify-after-logout-redirect') != home_url() ? $parse_url['scheme'] . '://' . $parse_url['port'] . $parse_url['host'] . '/' . get_option('criipto-verify-after-logout-redirect') : get_option('criipto-verify-after-logout-redirect')) ?></td>
                </tr>
            </table>
        </div>
        <div class="criipto-verify-content">
            <form method="post" action="options.php">
                <?php settings_fields('criipto_verify_settings_group'); ?>
                <?php do_settings_sections('criipto_verify_settings_group'); ?>
                <input type="hidden" id="criipto-verify-redirect-uri" name="criipto-verify-redirect-uri" value="<?php echo esc_url(CRIIPTO_VERIFY_MAIN_PLUGIN_URL) ?>openIdConnect.php" />
                <input type="hidden" id="criipto-verify-admin-port" name="criipto-verify-admin-port" value="<?php echo esc_attr($port) ?>" />
                <input type="hidden" id="criipto-verify-admin-scheme" name="criipto-verify-admin-scheme" value="<?php echo esc_attr($scheme) ?>" />
                <input type="hidden" id="criipto-verify-first-install" name="criipto-verify-first-install" value="<?php echo esc_textarea('obsolete') ?>" />
                <h1>Criipto WordPress Plugin Settings</h1>
                <p>Basic settings related to the Criipto integration.</p>

                <table>

                    <tr>
                        <th>
                            *Domain
                        </th>
                        <td>
                            <input type="text" id="criipto-verify-domain" name="criipto-verify-domain" value="<?php echo esc_attr(get_option('criipto-verify-domain')); ?>" placeholder="*.criipto.id or your own custom domain" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            *Login Method
                        </th>
                        <td>
                            <select id="criipto-verify-login-method" name="criipto-verify-login-method">
                                <?php
                                foreach (json_decode(criipto_verify_setWellKnown(), true)['acr_values_supported'] as $acr_value) { ?>
                                    <option <?php echo esc_attr(get_option('criipto-verify-login-method') == $acr_value || (get_option('criipto-verify-login-method') == false && $acr_value == 'urn:grn:authn:dk:nemid:poces')  ? 'selected' : '') ?> value="<?php echo esc_attr($acr_value) ?>"><?php echo esc_attr($acr_value) ?></option>
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
                            <input type="text" id="criipto-verify-client-id" name="criipto-verify-client-id" value="<?php echo esc_attr(get_option('criipto-verify-client-id')); ?>" placeholder="urn:easyid:* or your own custom value" />
                        </td>

                    </tr>
                    <tr>
                        <th scope="row">
                            Redirect page after<br> log out
                        </th>
                        <td>
                            <input type="text" id="criipto-verify-after-logout-redirect" name="criipto-verify-after-logout-redirect" value="<?php echo esc_attr(get_option('criipto-verify-after-logout-redirect') != '' ? get_option('criipto-verify-after-logout-redirect') : home_url()); ?>" />
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
                            <input type="password" id="criipto-verify-client-secret" name="criipto-verify-client-secret" value="<?php echo esc_attr(get_option('criipto-verify-client-secret')); ?>" />
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
                            <input type="checkbox" id="criipto-verify-claims" name="criipto-verify-claims"  value="1" <?php echo esc_attr(get_option('criipto-verify-claims') == "1" || get_option('criipto-verify-first-install') == 'firstInstall' ? "checked" : "") ?> />
                            <i>For testing purpose, shows you the available user information returned from Criipto Verify</i>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Implicit
                        </th>
                        <td>
                            <input type="checkbox" id="criipto-verify-implicit" name="criipto-verify-implicit" value="1" <?php echo esc_attr(get_option('criipto-verify-implicit') == "1" ? "checked" : "") ?> />

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
