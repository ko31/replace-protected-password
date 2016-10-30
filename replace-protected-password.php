<?php
/**
 * Plugin Name: Replace Protected Password
 * Plugin URI:  https://github.com/ko31/replace-protected-password
 * Description: This plugin allows you to update the password for the post or page at a time.
 * Version:     1.0.2
 * Author:      Ko Takagi
 * Author URI:  http://go-sign.info/
 * License:     GPLv2
 * Text Domain: replace-protected-password
 * Domain Path: /languages
 */

/*  Copyright (c) 2016 Ko Takagi (http://go-sign.info/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$replaceProtectedPassword = new replace_protected_password();
$replaceProtectedPassword->register();

class replace_protected_password {

    private $version = '';
    private $langs   = '';

    function __construct()
    {
        $data = get_file_data(
            __FILE__,
            array('ver' => 'Version', 'langs' => 'Domain Path')
        );
        $this->version = $data['ver'];
        $this->langs   = $data['langs'];
    }

    public function register()
    {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
    }

    public function plugins_loaded()
    {
        load_plugin_textdomain(
            'replace-protected-password',
            false,
            dirname( plugin_basename( __FILE__ ) ) . $this->langs
        );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function admin_menu()
    {
        add_options_page(
            __( 'Replace Protected Password', 'replace-protected-password' ),
            __( 'Replace Protected Password', 'replace-protected-password' ),
            'manage_options',
            'replace-protected-password',
            array( $this, 'options_page' )
        );
    }

    public function admin_init()
    {
        if ( isset($_POST['replace-protected-password-nonce']) && $_POST['replace-protected-password-nonce'] ) {
            if ( check_admin_referer( 'replace-protected-password', 'replace-protected-password-nonce' ) ) {
                global $wpdb;
                $e = new WP_Error();
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                if ( !$current_password ) {
                    $e->add( 'error', esc_html__( 'Please select a current password.', 'replace-protected-password' ) );
                    set_transient( 'replace-protected-password-errors', $e->get_error_messages(), 5 );
                }
                if ( !$new_password ) {
                    $e->add( 'error', esc_html__( 'Please input a new password.', 'replace-protected-password' ) );
                    set_transient( 'replace-protected-password-errors', $e->get_error_messages(), 5 );
                }
                if ( $current_password && $new_password ) {
                    $sql = $wpdb->prepare( "UPDATE `" . $wpdb->posts . "` SET `post_password` = %s WHERE `post_password` = %s", array( $new_password, $current_password ) );
                    $wpdb->query($sql);
                    set_transient( 'replace-protected-password-updated', true, 5 );
                }

                wp_redirect( 'options-general.php?page=replace-protected-password' );
            }
        }
    }

    public function admin_notices()
    {
?>
        <?php if ( $messages = get_transient( 'replace-protected-password-errors' ) ): ?>
            <div class="error">
            <ul>
            <?php foreach ( $messages as $message ): ?>
                <li><?php echo esc_html( $message );?></li>
            <?php endforeach; ?>
            </ul>
            </div>
        <?php endif; ?>
        <?php if ( $messages = get_transient( 'replace-protected-password-updated' ) ): ?>
            <div class="updated">
            <ul>
                <li><?php esc_html_e( 'Password has been updated.', 'replace-protected-password' );?></li>
            </ul>
            </div>
        <?php endif; ?>
<?php
    }

    public function options_page()
    {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT `post_password`, COUNT(`post_password`) count FROM " . $wpdb->posts . " WHERE `post_password` <> '' GROUP BY `post_password`" );
        $passwords = $wpdb->get_results( $sql );
?>
<div id="replace-protected-password" class="wrap">
<h2>Replace Protected Password</h2>

<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field( 'replace-protected-password', 'replace-protected-password-nonce' ); ?>

<table class="form-table">
<tbody>
<tr>
<th scope="row"><label for="current_password"><?php esc_html_e( 'Current Password', 'replace-protected-password' );?></label></th>
<td>
<select name="current_password" id="current_password">
    <option value=""><?php esc_html_e( 'Select current Password', 'replace-protected-password' );?></option>
<?php
        foreach ( $passwords as $password ):
?>
    <option class="level-0" value="<?php echo $password->post_password;?>"><?php echo $password->post_password;?>(<?php echo $password->count;?>)</option>
<?php
        endforeach;
?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="new_password"><?php esc_html_e( 'New Password', 'replace-protected-password' );?></label></th>
<td><input name="new_password" type="text" id="new_password" placeholder="<?php esc_html_e( 'Input new password', 'replace-protected-password' );?>" value="" class="regular-text"></td>
</tr>
</tbody>
</table>

<p class="submit">
<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Update', 'replace-protected-password' );?>">
</p>
</form>
</div><!-- #replace-protected-password -->
<?php
    }

} // end class replace-protected-password

// EOF
