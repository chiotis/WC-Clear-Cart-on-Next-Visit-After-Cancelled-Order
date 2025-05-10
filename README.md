# WC Clear Cart on Next Visit After Cancelled Order

A lightweight WordPress plugin for WooCommerce that guarantees when an order is marked “cancelled,” the customer always starts from scratch—empty cart, fresh session, and reset persistent cart—on their very next front-end visit.

---

## 📋 Problem Description

When running **WooCommerce** alongside **Events Calendar Tickets** and using **Viva Smart Checkout** for payment processing, you may encounter this issue:

1. Viva Smart Checkout cancels an order (e.g., due to a failed or user-cancelled payment).  
2. The `woocommerce_order_status_cancelled` hook fires **in the admin area**, where the customer’s session and cart aren’t active.  
3. Consequently, calls to `destroy_session()` or `empty_cart()` have no effect on the customer’s front-end session, and their cart remains populated.

This plugin solves it by:

- **Flagging** the user when their order is cancelled.  
- On the customer’s **next front-end page load**, automatically **clearing**:  
  - The WooCommerce cart (`WC()->cart->empty_cart()`)  
  - The session (`WC()->session->destroy_session()` + `init()`)  
  - The persistent cart stored in user meta  
  - All relevant WooCommerce cookies  

With this approach, the customer will **always** see an empty cart and a new session, no matter where or how the cancellation took place.
