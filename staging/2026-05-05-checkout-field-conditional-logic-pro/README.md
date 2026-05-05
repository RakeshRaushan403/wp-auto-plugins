# Checkout Field Conditional Logic Pro

> Make checkout faster by showing customers only the fields they actually need to fill out.

## What does this plugin do?

This plugin automatically shows or hides checkout fields on your WooCommerce store based on what your customer selects during checkout. For example, if someone chooses "Local Pickup" as their shipping method, you can hide the "Shipping Address" fields since they don't need them. This makes checkout faster, less confusing, and helps more customers complete their purchase.

## Why you need it

- **Reduce cart abandonment** — Long, complicated checkout forms scare customers away. Show only relevant fields and watch more people complete their orders.
- **Personalize the checkout experience** — Different customers need different information. Business buyers might need a VAT field, while regular shoppers don't. Show the right fields to the right people automatically.
- **Save customer time** — Nobody likes filling out unnecessary fields. A streamlined checkout means happier customers who come back to buy again.

## Requirements

- WordPress 5.8 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the plugin ZIP file from your purchase confirmation email or account dashboard
2. In your WordPress admin area, go to **Plugins → Add New → Upload Plugin**
3. Click **Choose File**, select the ZIP file you downloaded, then click **Install Now**
4. After installation completes, click **Activate Plugin**
5. You'll see a welcome message confirming the plugin is active

## How to use it

1. Go to **WooCommerce → Checkout Fields Logic** in your WordPress admin menu
2. Click the **Add New Rule** button at the top of the page
3. Give your rule a descriptive name (like "Hide shipping address for local pickup")
4. Under **When should this rule apply?**, select what triggers the rule:
   - Choose a condition type (Shipping Method, Payment Gateway, Product Category, Cart Total, etc.)
   - Select the specific option (like "Local Pickup" or "Cash on Delivery")
5. Under **What should happen?**, choose **Show** or **Hide**
6. Select which checkout field(s) to show or hide from the dropdown menu
7. Click **Save Rule** at the bottom
8. Test your checkout page by adding products to cart and trying different options
9. To create more rules, repeat steps 2-7

You can create unlimited rules and combine multiple conditions for advanced scenarios.

## Features

- Show or hide any WooCommerce checkout field based on customer selections
- Create rules based on shipping method (Local Pickup, Flat Rate, Free Shipping, etc.)
- Create rules based on payment gateway (PayPal, Stripe, Cash on Delivery, etc.)
- Create rules based on products in cart or product categories
- Create rules based on cart total amount (show fields only for orders over $100, for example)
- Works with default WooCommerce fields and custom checkout fields
- Real-time field visibility updates — no page refresh needed
- Unlimited conditional rules
- Drag-and-drop rule priority ordering
- Works with all WordPress themes
- Mobile-responsive and touch-friendly

## Frequently Asked Questions

**Q: Will this slow down my site?**
A: No. The plugin uses lightweight JavaScript that runs only on the checkout page. It's optimized for speed and won't affect your site's performance or loading times.

**Q: Is this compatible with page builders like Elementor?**
A: Yes. This plugin works with the WooCommerce checkout process itself, so it's compatible with any theme or page builder. As long as you're using the standard WooCommerce checkout, it will work perfectly.

**Q: What happens if I deactivate the plugin?**
A: All checkout fields will return to their normal, always-visible state. No data is lost, and your checkout will work exactly as it did before you installed the plugin. Your conditional rules are saved, so if you reactivate the plugin later, they'll still be there.

**Q: Can I show a field only when multiple conditions are met?**
A: Yes. You can add multiple conditions to a single rule using "AND" logic. For example, you can show a field only when the customer chooses "Free Shipping" AND has products from the "Wholesale" category in their cart.

**Q: Does this work with custom checkout fields from other plugins?**
A: Yes. The plugin detects all checkout fields on your site, whether they're default WooCommerce fields or custom fields added by other plugins. You can create conditional rules for any field that appears on your checkout page.

## Changelog

### 1.0.0 — 5 May 2026
- Initial release
- Create unlimited conditional rules for checkout fields
- Support for shipping method, payment gateway, product category, and cart total conditions
- Real-time field visibility updates without page refresh
- Drag-and-drop rule priority management
- Compatible with all WooCommerce checkout fields including custom fields

## License

GPL v2 or later. https://www.gnu.org/licenses/gpl-2.0.html