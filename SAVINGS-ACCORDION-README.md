# ZG - Product Variant Tile: Savings & Accordion Feature

## Overview

This plugin now includes a new feature that displays total savings and an accordion section with included items above the "Add to Cart" button on product pages. The content changes dynamically based on the selected variation, just like swatch images.

## Features

### 1. Total Savings Display

- Automatically calculates savings based on regular price vs sale price
- Updates dynamically for variable products when variations are selected
- Displays savings in red text with proper currency formatting
- Always available and changes with variation selection

### 2. Included Items Accordion

- Collapsible section showing what's included with the product
- Uses existing variation fields: "Description – Drop Down Text" and "Include Excerpt – Drop Down Preview Text"
- Content changes dynamically based on selected variation
- Smooth animations and hover effects
- Keyboard accessible

## Admin Configuration

### Setting Variation-Specific Content

1. Go to **Products** → **Edit Product**
2. Scroll to **Variations** section
3. For each variation, set:
   - **Description – Drop Down Text**: Main content for the accordion (e.g., "Grill and 2 x Food Temperature Probes")
   - **Include Excerpt – Drop Down Preview Text**: Additional preview text (e.g., "These are what you get...")
4. The content will automatically change when customers select different variations

### How It Works

- The accordion title is always "What's included?"
- The main content comes from the "Description – Drop Down Text" field
- The preview text comes from the "Include Excerpt – Drop Down Preview Text" field
- Savings are calculated automatically from the variation's pricing
- Content updates instantly when variations are selected, just like swatch images

## Technical Details

### Files Added/Modified

- `includes/class-product-tiles-public.php` - Added display methods and variation data handling
- `includes/admin/class-product-tiles-admin.php` - Enhanced variation data loading
- `assets/css/savings-accordion.css` - Styling for the new elements
- `assets/js/savings-accordion.js` - JavaScript functionality for dynamic updates

### Hooks Used

- `woocommerce_before_add_to_cart_button` - Displays the savings section
- `wp_enqueue_scripts` - Enqueues CSS and JS files
- `woocommerce_available_variation` - Loads variation-specific data

### CSS Classes

- `.zg-product-savings-section` - Main container
- `.zg-included-items-accordion` - Accordion container
- `.zg-accordion-header` - Clickable accordion header
- `.zg-accordion-content` - Collapsible content area
- `.zg-accordion-content-inner` - Inner content wrapper
- `.zg-accordion-text` - Main accordion text
- `.zg-accordion-preview` - Preview text styling
- `.zg-total-savings` - Savings display container
- `.zg-savings-amount` - Savings amount styling

### JavaScript Functions

- `initAccordion()` - Sets up accordion functionality
- `handleVariationChanges()` - Listens for variation changes
- `updateContentForVariation()` - Updates content for selected variation
- `resetToDefaultContent()` - Resets to default state
- `updateSavingsDisplay()` - Updates the savings amount display
- `formatCurrency()` - Formats currency amounts

## Browser Support

- Modern browsers with CSS3 and ES5+ support
- Responsive design for mobile devices
- Accessibility features for screen readers

## Customization

### Styling

You can customize the appearance by modifying the CSS in `assets/css/savings-accordion.css`.

### Functionality

The JavaScript is modular and can be extended by adding functions to the `window.ZGSavingsAccordion` object.

## Troubleshooting

### Savings Not Showing

1. Check if the product has a sale price set
2. Ensure the product is on a product page
3. Verify variations have proper pricing

### Accordion Not Working

1. Check browser console for JavaScript errors
2. Verify jQuery is loaded
3. Ensure the CSS and JS files are being enqueued
4. Check that variation fields have content

### Content Not Changing with Variations

1. Verify variation fields are filled in admin
2. Check that "Description – Drop Down Text" has content
3. Ensure WooCommerce variation data is loading correctly
