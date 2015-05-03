<?php
/**
 * Plugin Name: WooCommerce Sales by Country
 * Plugin URI: https://github.com/mhmithu/woocommerce-sales-by-country
 * Description: Adds a report page to display country specific product sales report.
 * Version: 1.2
 * Author: MH Mithu
 * Author URI: http://mithu.me/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * ----------------------------------------------------------------------
 * Copyright (C) 2015  MH Mithu  (email: mail@mithu.me)
 * ----------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ----------------------------------------------------------------------
 */


if(in_array('woocommerce/woocommerce.php', get_option('active_plugins'))) {

    function is_wc_new() {
        if (!function_exists('get_plugins'))
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            $version = $plugin_folder[$plugin_file]['Version'];
        } else {
            $version = NULL;
        }
        return version_compare($version, '2.1', 'gt');
    }

    if(is_wc_new()) {

        add_filter('woocommerce_admin_reports', 'sales_by_country');
        function sales_by_country($reports) {
            $reports['orders']['reports']['sales_by_country'] = array(
                'title'       => 'Sales by country',
                'description' => '',
                'hide_title'  => true,
                'callback'    => 'wc_country_sales'
            );
            return $reports;
        }

        function wc_country_sales() {

            global $woocommerce, $wpdb;

            $sql = "SELECT country.meta_value AS country_name,
                    SUM(order_item_meta.meta_value ) AS sale_total
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
                    AND   posts.post_status IN ('wc-processing','wc-on-hold','wc-completed')
                    GROUP BY country.meta_value";

            $results       = $wpdb->get_results($sql);
            $country      = new WC_Countries;
        ?>

            <table class="widefat">
            <thead>
                <tr>
                    <th>Country Name</th>
                    <th>Sale Total</th>
                </tr>
            </thead>
            <tbody>
        <?php
            foreach($results as $result) {
        ?>
                <tr>
                    <td><?php echo $country->countries[$result->country_name]; ?></td>
                    <td><?php echo get_woocommerce_currency_symbol().$result->sale_total; ?></td>
                </tr>
        <?php

            }
        ?>
            </tbody>
            </table>

    <?php
        }

    } else {
        add_action('admin_notices', 'sc_error_notice');
        function sc_error_notice(){
            global $current_screen;
            echo '<div class="error"><p>Your <strong>WooCommerce</strong> version is too old. Please update to latest version.</p></div>';
        }

    }

} else {
    add_action('admin_notices', 'sales_by_country_error_notice');
    function sales_by_country_error_notice(){
        global $current_screen;
        if($current_screen->parent_base == 'plugins'){
            echo '<div class="error"><p>'.__('The <strong>WooCommerce Sales by Country</strong> plugin requires the <a href="http://wordpress.org/plugins/woocommerce" target="_blank">WooCommerce</a> plugin to be activated in order to work. Please <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">install WooCommerce</a> or <a href="'.admin_url('plugins.php').'">activate</a> first.').'</p></div>';
        }
    }

}
