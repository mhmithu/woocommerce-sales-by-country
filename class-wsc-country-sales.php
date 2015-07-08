<?php
/**
 * @class         WSC_Country_Sales
 * @since         1.4
 * @package       WooCommerce Sales by Country
 * @subpackage    Base class
 * @author        MH Mithu <mail@mithu.me>
 * @link          https://github.com/mhmithu
 * @license       http://www.gnu.org/licenses/gpl-3.0.html
 *
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Including WP core file
if ( ! function_exists( 'get_plugins' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// If class not exists
if ( ! class_exists( 'WSC_Country_Sales' ) ) :


// Base class
class WSC_Country_Sales {

    public $version;        // Plugin version
    public $start_date;     // Range start date
    public $end_date;       // Range end date


    /**
     * Class constructor
     *
     * @access public
     * @param integer $start_date
     * @param integer $end_date
     */
    public function __construct( $start_date, $end_date ) {
        // Sync plugin version
        $wsc_version   = get_plugin_data( plugin_dir_path( __FILE__ ) . 'sales-by-country.php' );
        $this->version = $wsc_version['Version'];

        // Range timestamp to formatted date
        $this->start_date = date( 'Y-m-d', $start_date );
        $this->end_date   = date( 'Y-m-d', strtotime( '+1 day', $end_date ) );

        // Enqueue script hook
        add_action( 'admin_enqueue_scripts', array( $this, 'wsc_enqueue' ) );
    }


    /**
     * Get country wise total sales
     *
     * @access public
     * @return object
     */
    public function get_country_sales() {
        global $wpdb;

        $sql = "SELECT country.meta_value AS country_name,
                SUM(order_item_meta.meta_value) AS sale_total
                FROM {$wpdb->prefix}woocommerce_order_items AS order_items

                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                    ON order_items.order_item_id = order_item_meta.order_item_id
                LEFT JOIN {$wpdb->postmeta} AS country
                    ON order_items.order_id = country.post_id
                LEFT JOIN {$wpdb->posts} AS posts
                    ON order_items.order_id = posts.ID

                WHERE posts.post_type             = 'shop_order'
                AND   country.meta_key            = '_billing_country'
                AND   order_item_meta.meta_key    = '_line_total'
                AND   order_items.order_item_type = 'line_item'
                AND   posts.post_date            >= '$this->start_date'
                AND   posts.post_date             < '$this->end_date'
                AND   posts.post_status IN ('wc-processing','wc-on-hold','wc-completed')
                GROUP BY country.meta_value";

        return $wpdb->get_results( $sql );
    }


    /**
     * Check WooCommerce version
     *
     * @access public
     * @return boolean
     */
    public function is_wc_old() {
        global $woocommerce;

        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file   = 'woocommerce.php';
        $wc_version    = $plugin_folder[$plugin_file]['Version'];
        $wc_version    = isset( $wc_version ) ? $wc_version : $woocommerce->version;

        return version_compare( $wc_version, '2.1', 'lt' );
    }


    /**
     * Get full country name
     *
     * @access public
     * @param  string $name
     * @return string
     */
    public function country_name( $name ) {
        $get = new WC_Countries;
        return $get->countries[$name];
    }


    /**
     * Plugin script enqueue function
     *
     * @access public
     * @return void
     */
    public function wsc_enqueue() {
        wp_enqueue_script( 'wsc_datepicker', plugins_url( 'script.js', __FILE__ ), array(), $this->version, true );
    }


}

endif;
