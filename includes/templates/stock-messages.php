<?php
/**
 * Stock Messages Template
 *
 * @package VariantTiles
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Stock Messages Container -->
<div class="vt-stock-message-container">
    <!-- In Stock Message -->
    <div id="vt-in-stock-message" class="vt-stock-message">
        <div class="stock-dot in-stock"></div>
        <span>In Stock</span>
    </div>

    <!-- Low Stock Message -->
    <div id="vt-low-stock-message" class="vt-stock-message">
        <div class="stock-dot low-stock"></div>
        <span>Low in Stock</span>
    </div>
</div>