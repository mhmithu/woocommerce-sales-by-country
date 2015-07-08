<?php
/**
 * Plugin Name: WooCommerce Sales by Country
 * Plugin URI: https://github.com/mhmithu/woocommerce-sales-by-country
 * Description: Adds a report page to display country specific product sales report.
 * Version: 1.4
 * Author: MH Mithu
 * Author URI: http://mithu.me/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * ----------------------------------------------------------------------
 * Copyright (C) 2015  MH Mithu  (Email: mail@mithu.me)
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


// Including WP core file
if ( ! function_exists( 'get_plugins' ) )
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Including base class
if ( ! class_exists( 'WSC_Country_Sales' ) )
    require_once plugin_dir_path( __FILE__ ) . 'class-wsc-country-sales.php';


// Whether plugin active or not
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) :

    // Post data
    $start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
    $end_date   = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

    // Fallback
    if ( ! $start_date )
        $start_date = date( 'Ymd', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );

    if ( ! $end_date )
        $end_date = date( 'Ymd', current_time( 'timestamp' ) );

    // Timestamps
    $start_date = strtotime( $start_date );
    $end_date   = strtotime( $end_date );

    // The object
    $wsc = new WSC_Country_Sales( $start_date, $end_date );

    if ( ! $wsc->is_wc_old() ) :

        /**
         * WooCommerce hook
         *
         * @param array   $reports
         * @return array
         */
        function sales_by_country( $reports ) {
            $reports['orders']['reports']['sales_by_country'] = array(
                'title'       => 'Sales by country',
                'description' => '',
                'hide_title'  => true,
                'callback'    => 'wc_country_sales'
            );
            return $reports;
        }
        add_filter( 'woocommerce_admin_reports', 'sales_by_country' );


        /**
         * Function to hook into WooCommerce
         * 
         * @return string
         */
        function wc_country_sales() {
            global $wsc;
?>
            <br />
            <form method="post">
                <label>Select date range:</label>
                <input type="text" size="9" placeholder="yyyy-mm-dd" value="<?php if ( ! empty( $_POST['start_date'] ) ) echo esc_attr( $_POST['start_date'] ); ?>" name="start_date" class="wsc_datepicker from" />
                <input type="text" size="9" placeholder="yyyy-mm-dd" value="<?php if ( ! empty( $_POST['end_date'] ) ) echo esc_attr( $_POST['end_date'] ); ?>" name="end_date" class="wsc_datepicker to" />
                <input type="submit" class="button" value="Go" />
            </form>
            <br />
            <table class="wp-list-table widefat fixed posts">
                <thead>
                    <tr>
                        <th>Country Name</th>
                        <th>Sale Total</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ( $wsc->get_country_sales() as $view ) : ?>
                    <tr>
                        <td><?php echo $wsc->country_name( $view->country_name ); ?></td>
                        <td><?php echo get_woocommerce_currency_symbol() . $view->sale_total; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

    <?php

        }

    else :

        /**
         * WooCommerce warning message
         * @desc If WC too old, getting an warning message
         * 
         * @return string
         */
        function wsc_warning() {
            global $current_screen;
            echo '<div class="error"><p>Your <strong>WooCommerce</strong> version is too old. Please update to latest version.</p></div>';
        }
        add_action( 'admin_notices', 'wsc_warning' );

    endif;

else :

    /**
     * Getting notice if WooCommerce not active
     * 
     * @return string
     */
    function wsc_notice() {
        global $current_screen;
        if ( $current_screen->parent_base == 'plugins' ) {
            echo '<div class="error"><p>'.__( 'The <strong>WooCommerce Sales by Country</strong> plugin requires the <a href="http://wordpress.org/plugins/woocommerce" target="_blank">WooCommerce</a> plugin to be activated in order to work. Please <a href="'.admin_url( 'plugin-install.php?tab=search&type=term&s=WooCommerce' ).'" target="_blank">install WooCommerce</a> or <a href="'.admin_url( 'plugins.php' ).'">activate</a> first.' ).'</p></div>';
        }
    }
    add_action( 'admin_notices', 'wsc_notice' );

endif;
