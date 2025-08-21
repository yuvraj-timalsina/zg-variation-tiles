# ZG - Product Variant Tile Plugin

This plugin allows you to display product variants as tiles with total savings and included items accordion functionality.

## Recent Updates

### Field Label Improvements

All variant tile fields now have more descriptive and consistent labels:

- **Variant Tile Dropdown Text** (`_vt_dd_text`) - Description text for the variant tile dropdown
- **Variant Tile Dropdown Image** (`_vt_dd_image_id`) - Image to display in the variant tile dropdown
- **Variant Tile Dropdown Preview Text** (`_vt_dd_preview`) - Preview text for the variant tile dropdown
- **Variant Tile Offer Label** (`_vt_offer_label`) - Offer label displayed on the variant tile

### Image Upload Enhancement

The image field has been converted from a simple number input to a full WordPress media upload interface with:

- **Upload Button** - Opens WordPress media library for image selection
- **Image Preview** - Shows thumbnail preview of selected image
- **Remove Button** - Allows easy removal of selected image
- **Proper Validation** - Ensures only valid image attachments are saved

### Technical Improvements

- **WordPress Media Integration** - Uses native WordPress media library
- **Enhanced Security** - Proper sanitization and validation of all fields
- **Better UX** - Intuitive interface with visual feedback
- **Responsive Design** - Works well on all screen sizes

## Usage

1. Enable "Variant Tile (CK augment)" on your variable product
2. Configure variant tile fields for each variation:
   - Add descriptive dropdown text
   - Upload an image using the media uploader
   - Add preview text
   - Set offer labels
3. Save the product to apply changes

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- Elementor 2.0+ (for widget functionality)
- PHP 7.0+

## Installation

1. Upload the plugin files to `/wp-content/plugins/variant-tiles-main/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure variant tile settings on your variable products
