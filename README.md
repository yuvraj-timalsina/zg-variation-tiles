# ZG - Variation Tiles Plugin

A comprehensive WooCommerce plugin that transforms product variations into interactive tiles with advanced functionality including savings calculations, included items accordions, and seamless cart integration.

## Features

### 🎯 Core Functionality

- **Interactive Variation Tiles** - Display product variations as visually appealing tiles
- **Total Savings Calculator** - Automatic calculation and display of bundle savings
- **Included Items Accordion** - Expandable sections showing what's included in each bundle
- **Smart Cart Integration** - Seamless add-to-cart functionality with validation
- **Dynamic Pricing** - Real-time price updates based on variation selection

### 🎨 Visual Enhancements

- **Custom Images** - Upload specific images for each variation tile
- **Offer Labels** - Display promotional badges and offers on tiles
- **Best Value Badges** - Automatic highlighting of recommended bundles
- **Responsive Design** - Optimized for all screen sizes and devices
- **Smooth Animations** - Professional transitions and interactions

### ⚙️ Advanced Features

- **Grill-Only Bundle Support** - Special handling for grill-only variations
- **Front Bench Management** - Automatic hiding/showing of front bench options
- **Controller Integration** - Wireless and non-wireless controller support
- **Variation State Management** - Persistent selection states across page interactions
- **Error Prevention** - Robust error handling and validation

## Installation

### Requirements

- **WordPress** 5.0 or higher
- **WooCommerce** 3.0 or higher
- **Elementor** 2.0 or higher (for widget functionality)
- **PHP** 7.4 or higher

### Installation Steps

1. Upload the plugin files to `/wp-content/plugins/zg-variation-tiles/`
2. Activate the plugin through the WordPress admin panel
3. Navigate to any variable product to configure variation tiles
4. Configure individual variation settings

## Configuration

### Product Setup

1. **Configure Variations**: Set up each variation with:
   - **Dropdown Text** - Detailed description of the bundle
   - **Preview Text** - Short excerpt for accordion preview
   - **Custom Image** - Specific image for the variation tile
   - **Offer Label** - Promotional text or badge

### Widget Configuration

The plugin provides two Elementor widgets:

#### Product Variant Tiles (V4)

- **Layout Controls** - Customize tile arrangement and spacing
- **Styling Options** - Colors, fonts, and visual effects
- **Badge Settings** - Configure "Best Value" and offer badges
- **Responsive Settings** - Mobile and tablet optimizations

#### Product Variant Tiles (Legacy)

- Basic tile display functionality
- Compatible with older Elementor versions

## Usage

### Basic Implementation

1. Create or edit a variable product
2. Add variations (bundles, controllers, front bench options)
3. Configure variation-specific content:
   - Add descriptive text for each bundle
   - Upload relevant images
   - Set preview text for accordions
   - Add offer labels if applicable
4. Add the "Product Variant Tiles" widget to your product page
5. Configure widget settings and styling

### Advanced Features

#### Grill-Only Bundle Handling

The plugin includes special logic for grill-only bundles:

- Automatic front bench hiding when grill-only is selected
- Enhanced cart validation for grill-only variations
- Persistent controller selection maintenance
- Continuous state monitoring and correction

#### Savings Calculation

- Automatic calculation of bundle savings
- Real-time price updates
- Display of total savings amount
- Integration with WooCommerce pricing

#### Accordion Functionality

- Expandable "What's Included?" sections
- Dynamic content based on selected variation
- Preview text with character limits
- Image support in accordion content

## Technical Details

### File Structure

```
zg-variation-tiles/
├── assets/
│   ├── css/
│   │   └── savings-accordion.css
│   └── js/
│       ├── general.js
│       └── savings-accordion.js
├── includes/
│   ├── admin/
│   │   └── class-product-tiles-admin.php
│   ├── widgets/
│   │   ├── class-product-variant-tiles.php
│   │   └── class-product-variant-tiles-v4.php
│   ├── class-product-tiles-plugin-loaded.php
│   ├── class-product-tiles-public.php
│   ├── functions.php
│   └── templates/
└── zg-product-variantion-tiles.php
```

### Key Classes

- **Product_Tiles_Plugin_loaded** - Main plugin initialization
- **Product_Tiles_Public** - Frontend functionality and cart integration
- **Product_Tiles_Admin** - Backend configuration interface
- **Product_Variant_Tiles_V4** - Advanced Elementor widget
- **Product_Variant_Tiles** - Legacy Elementor widget

### Hooks and Filters

The plugin integrates with WooCommerce through various hooks:

- `woocommerce_add_to_cart_validation` - Cart validation
- `woocommerce_available_variations` - Variation availability
- `woocommerce_available_variation` - Individual variation data
- `woocommerce_variation_select_change` - Variation selection events

### JavaScript Features

- **Debounced Updates** - Optimized performance for rapid changes
- **State Management** - Persistent selection tracking
- **Error Prevention** - Robust error handling and validation
- **Cart Integration** - Seamless add-to-cart functionality
- **Dynamic Pricing** - Real-time price updates

## Troubleshooting

### Common Issues

#### Cart Button Not Working

- Check that all required variations are configured
- Verify WooCommerce is properly installed and activated

#### Images Not Displaying

- Confirm images are uploaded through the media library
- Check file permissions on upload directory
- Verify image IDs are properly saved in variation meta

#### Styling Issues

- Clear browser cache and WordPress cache
- Check for CSS conflicts with theme or other plugins
- Verify Elementor widget settings are properly configured

### Debug Mode

Enable WordPress debug mode to troubleshoot issues:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Changelog

### Version 1.0.5

- Fixed grill-only bundle cart integration
- Enhanced error prevention and validation
- Improved performance with debounced updates
- Added comprehensive state management
- Removed debug logging for production use

### Version 1.0.4

- Added image upload enhancement with media library
- Improved field labels and validation
- Enhanced security with proper sanitization
- Better responsive design implementation

### Version 1.0.3

- Initial release with core functionality
- Basic variation tile display
- Savings calculation
- Accordion functionality

## Support

For technical support or feature requests:

- Check the troubleshooting section above
- Review WordPress and WooCommerce compatibility
- Ensure all requirements are met
- Test with default theme to isolate conflicts

## License

This plugin is proprietary software developed for Z Grills. All rights reserved.

---

**Note**: This plugin is specifically designed for Z Grills product variations and includes custom logic for grill bundles, controllers, and front bench options. It may require customization for use with other product types.
