<?php
/*
  Plugin Name: debug-printr
  Plugin URI: http://www.iaps.ca/wordpress-plugins/debug-printr/
  Description: Useage: debug::print_r($obj) in template.
  Author: Brett Farrell
  Version: 1.1.0
  Author URI: http://www.iaps.ca/wordpress-plugins/
 */

class debug {

    const OPTION = 'debug_ips';

    function __construct() {
        if (!is_admin())
            return;
        // set defaults
        register_activation_hook(__FILE__, [$this, 'activate']);
        // Add our menu item
        add_action('admin_menu', [$this, 'plugin_menu']);
        // Add Plugin-Page Links: Settings
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_action_links']);
        // check post
        add_action('admin_init', [$this, 'check_post'], 10, 0);
    }

    function activate() {
        if (!get_option(self::OPTION))
            update_option(self::OPTION, json_encode(array("127.0.0.1", $_SERVER['REMOTE_ADDR'])));
    }

    function plugin_menu() {
        add_options_page('Debug PrintR', 'Debug PrintR', 'manage_options', 'debug-printr', [$this, 'admin_settings']);
    }

    function admin_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
        <style>#debug-ips textarea {min-height: 200px;}</style>
        <form action="" method="post" id="debug-ips">
            <p><?= __('Append any debug-enabled IP addresses or hostnames to the list, one per line.') ?></p>
            <?php wp_nonce_field('action_debug_printr_save_ips', 'debug_printr_save_ips') ?>
            <textarea name="ips"><?= implode("\r\n", json_decode(get_option(debug::OPTION))) ?></textarea>
            <input type="submit" class="button button-primary button-large" name="debug-printr" value="<?= __('Save') ?>" />
        </form>
        <p>Your IP: <strong><?= $_SERVER['REMOTE_ADDR'] ?></strong></p>
        <p>Debugging Enabled? <strong><?= var_export(debug::enabled(), true) ?></strong></p>
        <p>Usage: <pre>&lt;?php debug::print_r($var); ?&gt;</pre></p>
        <?php
    }

    function check_post() {
        if (array_key_exists('debug-printr', $_POST) && strstr($_SERVER['REQUEST_URI'], '?page=debug-printr')):
            if (!array_key_exists('debug_printr_save_ips', $_POST) || !wp_verify_nonce($_POST['debug_printr_save_ips'], 'action_debug_printr_save_ips'))
                return;
            $ips = explode("\r\n", trim($_POST['ips']));
            for ($i = 0; $i < count($ips); $i++):
                $ip = $ips[$i];
                if (!filter_var($ip, FILTER_VALIDATE_IP) && !filter_var(gethostbyname($ip), FILTER_VALIDATE_IP))
                    unset($ips[$i]);
            endfor;
            $ips = array_values($ips);
            if (json_decode(get_option(self::OPTION)) !== $ips)
                update_option(self::OPTION, json_encode($ips));
        endif;
    }

    function add_action_links($links) {
        // merge in our links first so they show first
        return array_merge(array('<a href="' . admin_url('admin.php?page=debug-printr') . '">Settings</a>'), $links);
    }

    /**
     * Prints $obj data to the screen.
     * @param mixed $obj The variable you want to print_r.
     * @param bool $exit If you want to call exit() right after printing $obj.  Default = false.
     * @param string $file Fallback if debug_backtrace() is not available.  $obj filename, default = null.
     * @param string $line Fallback if debug_backtrace() is not available.  $obj line number, default = null.
     * @param bool $print_pre If you want to enclose $obj in <pre></pre>.  Default = true.
     */
    public static function print_r($obj, $exit = false, $file = null, $line = null, $print_pre = true) {

        if (self::enabled()) {
            if ($print_pre)
                echo "<pre>\n";
            echo "-------------------------------------------------------------------------------\n";
            if ($debug_backtrace = debug_backtrace()) {
                $file = isset($debug_backtrace[0]['file']) ? $debug_backtrace[0]['file'] : $file;
                $line = isset($debug_backtrace[0]['line']) ? $debug_backtrace[0]['line'] : $line;
            }
            echo $file . ":[$line]\n\n";
            print_r($obj);
            echo "\n-------------------------------------------------------------------------------\n";
            if ($print_pre)
                echo "</pre>\n";
            if ($exit)
                exit('exited...');
        }
    }

    /**
     * Determines whether debugging is enabled based on the IPs provided.
     * @return boolean
     */
    public static function enabled() {
        $ips = (array) json_decode(get_option(self::OPTION));
        foreach ($ips as &$ip):
            if (filter_var($ip, FILTER_VALIDATE_IP))
                continue;
            if (filter_var(gethostbyname($ip), FILTER_VALIDATE_IP))
                $ip = gethostbyname($ip);
        endforeach;
        if (in_array($_SERVER["REMOTE_ADDR"], $ips))
            return true;
        return false;
    }

}

$debug_printr = new debug();
