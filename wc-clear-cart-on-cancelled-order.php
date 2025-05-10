<?php
/**
 * Plugin Name: WC Clear Cart on Next Visit After Cancelled Order
 * Description: Όταν μια παραγγελία γίνεται cancelled, σημαδεύει τον πελάτη και στην επόμενη front-end φόρτωση καθαρίζει πλήρως το cart & session.
 * Version:     1.0
 * Author:      Christos Chiotis
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1) Σημαδέψου τον πελάτη στο hook cancelled
 */
add_action( 'woocommerce_order_status_cancelled', function( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;
    $user_id = intval( $order->get_customer_id() );
    if ( $user_id ) {
        update_user_meta( $user_id, '_wc_needs_cart_clear', time() );
    }
}, 10 );

/**
 * 2) Στην επόμενη front-end init, αν έχεις flag, καθάρισε
 */
add_action( 'wp', function() {
    if ( is_admin() || ! is_user_logged_in() ) {
        return;
    }
    $user_id = get_current_user_id();
    $flag    = get_user_meta( $user_id, '_wc_needs_cart_clear', true );
    if ( ! $flag ) {
        return;
    }

    // -- a) Άδειασμα WooCommerce cart
    if ( WC()->cart ) {
        WC()->cart->empty_cart();
    }

    // -- b) Καταστροφή και re-init session
    if ( WC()->session ) {
        WC()->session->destroy_session();
        WC()->session->init();
    }

    // -- c) Διαγραφή persistent cart από usermeta
    $blog_id = get_current_blog_id();
    delete_user_meta( $user_id, 'woocommerce_persistent_cart_' . $blog_id );
    delete_user_meta( $user_id, 'woocommerce_persistent_cart_expires_' . $blog_id );

    // -- d) Καθαρισμός WooCommerce cookies (client-side)
    $cookies = [
        'woocommerce_cart_hash',
        'woocommerce_items_in_cart',
        'woocommerce_recently_viewed',
    ];
    foreach ( $cookies as $c ) {
        if ( isset( $_COOKIE[ $c ] ) ) {
            setcookie( $c, '', time() - YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '', is_ssl(), true );
            unset( $_COOKIE[ $c ] );
        }
    }

    // -- e) Remove flag so we don't loop
    delete_user_meta( $user_id, '_wc_needs_cart_clear' );
} );
