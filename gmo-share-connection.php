<?php
/**
 * Plugin Name: GMO Share Connection
 * Plugin URI:  
 * Description: GMO Share Connection plugin is designed for easy social sharing by letting user choose place/pages to use icons. 9 social network services are supported in this plugin including Facebook and Twitter.
 * Version:     1.1
 * Author:      WP Shop byGMO
 * Author URI:  http://www.wpshop.com
 * License:     GPLv2
 * Text Domain: gmo_share_connection
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 WP Shop byGMO (http://www.wpshop.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */



define('GMO_SHARE_CONNECTION_URL',  plugins_url('', __FILE__));
define('GMO_SHARE_CONNECTION_PATH', dirname(__FILE__));

$gmoshareconnection = new GMO_Share_Connection();
$gmoshareconnection->register();

class GMO_Share_Connection {

private $version = '';
private $langs   = '';

private $buttons = array(
    'facebook'  => array(
        'label' => 'Facebook',
        'small_icon'  => 'facebook',
        'html'  => '<a rel="nofollow" href="http://www.facebook.com/sharer.php?u=%1$s&amp;t=%2$s" title="Share on Facebook">%3$s</a>',
    ),
    'twitter'  => array(
        'label' => 'Twitter',
        'small_icon' => 'twitter',
        'html'  => '<a rel="nofollow" href="http://twitter.com/share?url=%1$s&text=%2$s" title="Share on Twitter">%3$s</a>',
    ),
    'pinterest'  => array(
        'label' => 'Pinterest',
        'small_icon'  => 'pinterest',
        'html'  => '<a rel="nofollow" href="http://pinterest.com/pin/create/button/?url=%1$s&description=%2$s&media=%4$s" title="Share on Pinterest">%3$s</a>',
    ),
    'linkedin'  => array(
        'label' => 'LinkedIn',
        'small_icon'  => 'linkedin',
        'html'  => '<a rel="nofollow" href="http://www.linkedin.com/cws/share?url=%1$stoken=&isFramed=false" title="Share on LinkedIn">%3$s</a>',
    ),
    'google'  => array(
        'label' => 'Google+',
        'small_icon'  => 'google-plus',
        'html'  => '<a rel="nofollow" href="https://plus.google.com/share?url=%1$s" title="Share on Google+">%3$s</a>',
    ),
    'tumblr'  => array(
        'label' => 'Tumblr',
        'small_icon'  => 'tumblr',
        'html'  => '<a href="http://www.tumblr.com/share/link?url=%1$s&name=%2$s" title="Share on Tumblr">%3$s</a>',
    ),
    'reddit'  => array(
        'label' => 'Reddit',
        'small_icon'  => 'reddit',
        'html'  => '<a href="http://reddit.com/submit?url=%1$s&title=%2$s" title="Share on Reddit">%3$s</a>',
    ),
    'delicious'  => array(
        'label' => 'Delicious',
        'small_icon'  => 'delicious',
        'html'  => '<a href="https://delicious.com/save?v=5&provider=GMOShareConnection&noui&jump=close&url=%1$s&title=%2$s" title="Share on Delicious">%3$s</a>',
    ),

    'stumbleupon'  => array(
        'label' => 'StumbleUpon',
        'small_icon'  => 'stumbleupon',
        'html'  => '<a rel="nofollow" href="http://www.stumbleupon.com/submit?url=%1$s&title=%2$s" title="Share on StumbleUpon">%3$s</a>',
    ),
);

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
    add_action('plugins_loaded', array($this, 'plugins_loaded'));
}

public function plugins_loaded()
{
    load_plugin_textdomain(
        'gmo_share_connection',
        false,
        dirname(plugin_basename(__FILE__)).$this->langs
    );

    add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    add_action('admin_menu', array($this, 'admin_menu'));
    add_action('admin_init', array($this, 'admin_init'));
    add_filter('the_content', array($this, 'the_content'), 9);
    add_action('tidy_after_entry_meta', array($this, 'tidy_after_entry_meta'));
    add_action('wp_footer', array($this, 'wp_footer'));
}

public function wp_footer()
{
    if (is_single() && get_option('gmo_share_connection_single', 1)) {
        // continue
    } elseif (is_page() && get_option('gmo_share_connection_page', 1)) {
        // continue
    } elseif ( ( is_front_page() || is_home() ) && get_option('gmo_share_connection_home', 1)) {
        // continue
    } else {
        return;
    }

?>
<script type="text/javascript">
(function($){
    $('.gmo-shares a').click(function(){
        window.open(
            $(this).attr('href'),
            'gmoshare',
            'menubar=1,resizable=1,width=600,height=350'
        );
        return false;
    });

})(jQuery);
</script>
<?php
}

public function the_content($contents)
{
    if (is_single() && get_option('gmo_share_connection_single', 1)) {
        // continue
    } elseif (is_page() && get_option('gmo_share_connection_page', 1)) {
        // continue
    } else {
        return $contents;
    }

    $html = '';
    $share = $this->get_share_contents();
    if (get_option('gmo_share_connection_before_content')) {
        if ($share) {
            $html .= '<ul id="gmo-share-before-contents" class="gmo-shares">';
            $html .= $share;
            $html .= '</ul>';
        }
    }

    $html .= $contents;

    if (get_option('gmo_share_connection_after_content')) {
        if ($share) {
            $html .= '<ul id="gmo-share-after-contents" class="gmo-shares">';
            $html .= $share;
            $html .= '</ul>';
        }
    }

    return $html;
}
public function tidy_after_entry_meta() {
    if ( ( is_front_page() || is_home() ) && get_option('gmo_share_connection_home', 1)) {
        // continue
    } elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
        // continue
    } else {
        return;
    }

    $html = '';
    $share = $this->get_share_contents();
    if ($share) {
        $html .= '<ul id="gmo_share_connection_home" class="gmo-shares">';
        $html .= $share;
        $html .= '</ul>';
    }

    echo $html;
}

public function admin_init()
{
    if (isset($_POST['gmo_share_connection']) && $_POST['gmo_share_connection']){
        if (check_admin_referer('gmo_share_connection', 'gmo_share_connection')){
            if (isset($_POST['gmo_share_connection_before_content']) && $_POST['gmo_share_connection_before_content']) {
                update_option('gmo_share_connection_before_content', 1);
            } else {
                update_option('gmo_share_connection_before_content', 0);
            }

            if (isset($_POST['gmo_share_connection_after_content']) && $_POST['gmo_share_connection_after_content']) {
                update_option('gmo_share_connection_after_content', 1);
            } else {
                update_option('gmo_share_connection_after_content', 0);
            }

            if (isset($_POST['gmo_share_connection_single']) && $_POST['gmo_share_connection_single']) {
                update_option('gmo_share_connection_single', 1);
            } else {
                update_option('gmo_share_connection_single', 0);
            }

            if (isset($_POST['gmo_share_connection_page']) && $_POST['gmo_share_connection_page']) {
                update_option('gmo_share_connection_page', 1);
            } else {
                update_option('gmo_share_connection_page', 0);
            }

            if (isset($_POST['gmo_share_connection_home']) && $_POST['gmo_share_connection_home']) {
                update_option('gmo_share_connection_home', 1);
            } else {
                update_option('gmo_share_connection_home', 0);
            }

            if (isset($_POST['social']) && is_array($_POST['social'])) {
                update_option('gmo_share_connection_socials', $_POST['social']);
            } else {
                update_option('gmo_share_connection_socials', array());
            }

            wp_redirect('options-general.php?page=gmo-share-connection');
        }
    }
}

public function admin_menu()
{
    add_options_page(
        __('GMO Share Connection', 'gmo_share_connection'),
        __('GMO Share Connection', 'gmo_share_connection'),
        'publish_posts',
        'gmo-share-connection',
        array($this, 'options_page')
    );
}

public function options_page()
{
?>
<div id="gmo-share-connection" class="wrap">

<h2>GMO Share Connection</h2>

<div id="gmoplugLeft">
<form id="save-social" method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field('gmo_share_connection', 'gmo_share_connection'); ?>

<h3><?php _e('General Settings', 'gmo_share_connection'); ?></h3>

<table class="form-table">
<tbody>
    <tr>
        <th><?php _e('Position', 'gmo_share_connection'); ?></th>
        <td>

<?php if (get_option('gmo_share_connection_before_content')): ?>
            <label><input type="checkbox" name="gmo_share_connection_before_content" value="1" checked /> <?php _e('Before Contents', 'gmo_share_connection'); ?></label>
<?php else: ?>
            <label><input type="checkbox" name="gmo_share_connection_before_content" value="1" /> <?php _e('Before Contents', 'gmo_share_connection'); ?></label>
<?php endif; ?>

<?php if (get_option('gmo_share_connection_after_content')): ?>
            <label><input type="checkbox" name="gmo_share_connection_after_content" value="1" checked /> <?php _e('After Contents', 'gmo_share_connection'); ?></label>
<?php else: ?>
            <label><input type="checkbox" name="gmo_share_connection_after_content" value="1" /> <?php _e('After Contents', 'gmo_share_connection'); ?></label>
<?php endif; ?>

        </td>
    </tr>
    <tr>
        <th><?php _e('Single', 'gmo_share_connection'); ?></th>
        <td>

<?php if (get_option('gmo_share_connection_single', 1)): ?>
            <label><input type="radio" name="gmo_share_connection_single" value="1" checked> Yes</label>
            <label><input type="radio" name="gmo_share_connection_single" value="0"> No</label>
<?php else: ?>
            <label><input type="radio" name="gmo_share_connection_single" value="1"> Yes</label>
            <label><input type="radio" name="gmo_share_connection_single" value="0" checked> No</label>
<?php endif; ?>
        </td>
    </tr>
    <tr>
        <th><?php _e('Page', 'gmo_share_connection'); ?></th>
        <td>

<?php if (get_option('gmo_share_connection_page', 1)): ?>
            <label><input type="radio" name="gmo_share_connection_page" value="1" checked> Yes</label>
            <label><input type="radio" name="gmo_share_connection_page" value="0"> No</label>
<?php else: ?>
            <label><input type="radio" name="gmo_share_connection_page" value="1"> Yes</label>
            <label><input type="radio" name="gmo_share_connection_page" value="0" checked> No</label>
<?php endif; ?>

        </td>
    </tr>
    <tr>
        <th><?php _e('Home', 'gmo_share_connection'); ?></th>
        <td>

<?php if (get_option('gmo_share_connection_home', 1)): ?>
            <label><input type="radio" name="gmo_share_connection_home" value="1" checked> Yes</label>
            <label><input type="radio" name="gmo_share_connection_home" value="0"> No</label>
<?php else: ?>
            <label><input type="radio" name="gmo_share_connection_home" value="1"> Yes</label>
            <label><input type="radio" name="gmo_share_connection_home" value="0" checked> No</label>
<?php endif; ?>

        </td>
    </tr>
</tbody>
</table>

<h3><?php _e('Share Buttons', 'gmo_share_connection'); ?></h3>

<div class="gmo-share-connection-buttons-wrap">
    <div class="gmo-share-connection-button-wrap">
        <div class="gmo-share-connection-button-inner">
            <h4><?php _e('Available Services', 'gmo_share_connection'); ?></h4>
            <ul id="btn-deactive" class="gmo-share-connection-buttons">
<?php
    foreach ($this->get_buttons() as $id => $btn) {
        $hide = '';
        if (in_array($id, $this->get_active_buttons())) {
            $hide = 'style="display:none;"';
        }
?>
        <li data-social="<?php echo esc_attr($id); ?>" class="btn-preview" <?php echo $hide; ?>>
        	<span class="icon icon-<?php echo esc_attr($btn['small_icon']); ?>"></span>
            <?php echo esc_html($btn['label']); ?>
            <a href="javascript:void(0);" class="close" data-action="<?php echo esc_attr($id); ?>">&times;</a>
        </li>
<?php
    }
?>
            </ul>
        </div>
    </div>
    <div class="gmo-share-connection-button-wrap">
        <div class="gmo-share-connection-button-inner">
            <h4><?php _e('Activated Services', 'gmo_share_connection'); ?></h4>
            <ul id="btn-active" class="gmo-share-connection-buttons">
<?php
    foreach ($this->get_active_buttons() as $id) {
        $buttons = $this->get_buttons();
        $btn = $buttons[$id];
?>
        <li data-social="<?php echo esc_attr($id); ?>" class="btn-preview">
        	<span class="icon icon-<?php echo esc_attr($btn['small_icon']); ?>"></span>
            <?php echo esc_html($btn['label']); ?>
            <a href="javascript:void(0);" class="close" data-action="<?php echo esc_attr($id); ?>">&times;</a>
        </li>
<?php
    }
?>
            </ul>
        </div>
    </div>
</div>

<p style="margin-top: 3em;"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e("Save Changes", "gmo_share_connection"); ?>"></p>

</form>
</div><!-- #gmoplugLeft -->

<?php $plugin_file_url = plugins_url() . '/'; ?>
<div id="gmoplugRight">
<h3>WordPress Themes</h3>
<ul>
<li><a href="https://wordpress.org/themes/kotenhanagara" target="_blank">Kotehanagara</a></li>
<li><a href="https://wordpress.org/themes/madeini" target="_blank">Madeini</a></li>
<li><a href="https://wordpress.org/themes/azabu-juban" target="_blank">Azabu Juban</a></li>
</ul>
<a href="http://wpshop.com/themes?=vn_wps_shareconnection" target="_blank"><img src="<?php echo ($plugin_file_url.'gmo-share-connection/images/'.'wpshop_bnr_themes.png'); ?>" alt="WPShop by GMO WordPress Themes for Everyone!"></a>
<ul><li class="bnrlink"><a href="http://wpshop.com/themes?=wps_shareconnection" target="_blank">Visit WP Shop Themes</a></li></ul>
<h3>WordPress Plugins</h3>
<ul>
<li><a href="http://wordpress.org/plugins/gmo-showtime/" target="_blank">GMO Showtime</a></li>
<li><a href="http://wordpress.org/plugins/gmo-font-agent/" target="_blank">GMO Font Agent</a></li>
<li><a href="http://wordpress.org/plugins/gmo-share-connection/" target="_blank">GMO Share Connection</a></li>
<li><a href="http://wordpress.org/plugins/gmo-ads-master/" target="_blank">GMO Ads Master</a></li>
<li><a href="http://wordpress.org/plugins/gmo-page-transitions/" target="_blank">GMO Page Trasitions</a></li>
<li><a href="http://wordpress.org/plugins/gmo-go-to-top/" target="_blank">GMO Go to Top</a></li>
</ul>
<a href="http://wpshop.com/plugins?=vn_wps_shareconnection" target="_blank"><img src="<?php echo ($plugin_file_url.'gmo-share-connection/images/'.'wpshop_bnr_plugins.png'); ?>" alt="WPShop by GMO WordPress Plugins for Everyone!"></a>
<ul><li class="bnrlink"><a href="http://wpshop.com/plugins?=wps_shareconnection" target="_blank">Visit WP Shop Plugins</a></li></ul>
<h3>Contact Us</h3>
<a href="http://support.wpshop.com/?page_id=15" target="_blank"><img src="<?php echo ($plugin_file_url.'gmo-share-connection/images/'.'wpshop_logo.png'); ?>" alt="WPShop by GMO"></a>
</div><!-- #gmoplugRight -->

</div><!-- #gmo-share-connection -->
<?php
}

public function admin_enqueue_scripts()
{
    if (isset($_GET['page']) && $_GET['page'] === 'gmo-share-connection') {
        wp_enqueue_style(
            'iconmoon-tidy',
            plugins_url('iconmoon-tidy/style.css', __FILE__),
            array(),
            $this->version,
            'all'
        );

        wp_enqueue_style(
            'admin-gmo-share-connection-style',
            plugins_url('css/admin-gmo-share-connection.min.css', __FILE__),
            array('iconmoon-tidy'),
            $this->version,
            'all'
        );

        wp_enqueue_script(
            'admin-gmo-share-connection-script',
            plugins_url('js/admin-gmo-share-connection.min.js', __FILE__),
            array('jquery-ui-droppable', 'jquery-ui-sortable'),
            $this->version,
            true
        );
    }
}

private function get_share_contents()
{
    $btns = $this->get_active_buttons();
    if (!count($btns)) {
        return;
    }
    $btn_contents = array();
    foreach ($btns as $btn) {
        $buttons = $this->get_buttons();

		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => get_the_id()
			); 
		$attachments = get_posts($args);
		if ($attachments) {
			foreach ($attachments as $attachment) {
				$thumb = wp_get_attachment_image_src($attachment->ID, 'full', false);
				$thumb = $thumb[0];
			}
		} else {
			$thumb = plugins_url('img/tidy-thumb-small.png', __FILE__);
		}
        $btn_contents[] = sprintf(
            $buttons[$btn]['html'],
            urlencode(esc_attr(esc_url(apply_filters('the_permalink', get_permalink())))),
            esc_attr(get_the_title()),
            '<span class="icon icon-' . $buttons[$btn]['small_icon'] . '"></span>',
            $thumb
        );
    }

    $html = '<li class="social">';
    $html .= join('</li><li class="social">', $btn_contents);
    $html .= '</li>';

    return $html;
}

private function get_buttons()
{
    return apply_filters(
        'gmo_share_connection_socials',
        $this->buttons
    );
}

private function get_active_buttons()
{
    return get_option('gmo_share_connection_socials', array());
}

public function wp_enqueue_scripts()
{
    wp_enqueue_style(
        'iconmoon-tidy',
        plugins_url('iconmoon-tidy/style.css', __FILE__),
        array(),
        $this->version,
        'all'
    );
    wp_enqueue_style(
        'gmo-share-connection-style',
        plugins_url('css/gmo-share-connection.min.css', __FILE__),
        array('iconmoon-tidy'),
        $this->version,
        'all'
    );
    wp_enqueue_script('jquery');
}

} // end class GMO_Share_Connection

// EOF
