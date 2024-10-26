<?php
/*
* Plugin Name: Spot Price Adjustment
* Plugin URI: http://eux-test-spot-price-adjustment-plugin.com
* Description: A plugin to adjust metal spot prices for WooCommerce.
* Version: 1.0
* Author: Weng Xinn Chow
* Author URI: https://www.linkedin.com/in/weng-xinn-chow/
*/

class SpotPriceAdjustmentPlugin {
  // Constructor
  function __construct() {
    add_action('admin_menu', [$this, 'adminSettingsPage']);
    add_action('admin_init', [$this, 'settings']);
  }

  // Add a setting menu to the admin page
  function adminSettingsPage() {
    add_menu_page(
      $page_title = 'Spot Price Adjustment Settings Page', 
      $menu_title = 'Spot Price Adjustment Settings', 
      $capability = 'manage_options',  // User roles and capabilities
      $menu_slug = 'spot-price-adjustment-settings', 
      $callback = [$this, 'renderSettingsPage']
    );
  }

  // Render the settings page
  function renderSettingsPage() { 
    ?>
    <div class="wrap">
      <h1>Spot Price Adjustment</h1>
      <h2 class="nav-tab-wrapper">
        <a href="?page=spot-price-adjustment-settings&tab=sell" class="nav-tab nav-tab-active">Sell</a>
        <a href="?page=spot-price-adjustment-settings&tab=buy" class="nav-tab">Buy</a>
    </h2>
      
      <?php settings_errors(); // Default admin notices ?>

      <form method="POST" action="options.php">
        <?php
          settings_fields('adjustment_settings_group');
          do_settings_sections('spot-price-adjustment-settings');
          submit_button();
        ?>
      </form>
    </div>
    <?php 
  }

  // Function to register settings, sections and fields
  function settings() {
    register_setting('adjustment_settings_group', 'adjustment_settings');

    // Sell section
    add_settings_section(
      $id = 'adjustment_sell_section',
      $title = 'Spot Price Adjustment',
      $callback = null,
      $page = 'spot-price-adjustment-settings'
    );

    // Sell gold
    add_settings_field(
      $id = 'gold_adjustment_sell',
      $title = 'Gold Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_sell_section',
      $args = ['label_for' => 'gold_adjustment_sell']
    );

    // Sell silver
    add_settings_field(
      $id = 'silver_adjustment_sell',
      $title = 'Silver Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_sell_section',
      $args = ['label_for' => 'silver_adjustment_sell']
    );

    // Sell platinum
    add_settings_field(
      $id = 'platinum_adjustment_sell',
      $title = 'Platinum Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_sell_section',
      $args = ['label_for' => 'platinum_adjustment_sell']
    );

    // Sell palladium
    add_settings_field(
      $id = 'palladium_adjustment_sell',
      $title = 'Palladium Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_sell_section',
      $args = ['label_for' => 'palladium_adjustment_sell']
    );

    // Settings section for buy
    add_settings_section(
      $id = 'adjustment_buy_section',
      $title = 'Spot Price Buy Adjustment',
      $callback = null,
      $page = 'spot-price-adjustment-settings'
    );

    // Buy gold
    add_settings_field(
      $id = 'gold_adjustment_buy',
      $title = 'Gold Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_buy_section',
      $args = ['label_for' => 'gold_adjustment_buy']
    );

    // Buy silver
    add_settings_field(
      $id = 'silver_adjustment_buy',
      $title = 'Silver Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_buy_section',
      $args = ['label_for' => 'silver_adjustment_buy']
    );

    // Buy platinum
    add_settings_field(
      $id = 'platinum_adjustment_buy',
      $title = 'Platinum Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_buy_section',
      $args = ['label_for' => 'platinum_adjustment_buy']
    );

    // Buy palladium
    add_settings_field(
      $id = 'palladium_adjustment_buy',
      $title = 'Palladium Adjustment',
      $callback = [$this, 'renderAdjustmentField'],
      $page = 'spot-price-adjustment-settings',
      $section = 'adjustment_buy_section',
      $args = ['label_for' => 'palladium_adjustment_buy']
    );
  }
  
  // Function to render settings field (for each metal)
  function renderAdjustmentField($args) {
    $option = get_option('adjustment_settings');
    $label_for = $args['label_for'];
    $name = 
    $value = isset($option[$label_for]) ? $option[$label_for] : '';
    ?>
    <input type="number" id="<?php echo esc_attr($label_for) ?>" name="adjustment_settings[<?php echo esc_attr($label_for) ?>]" value="<?php echo esc_attr($value) ?>">
    <?php
  }
}

// Initialize the plugin
if (class_exists('SpotPriceAdjustmentPlugin')) {
  $spotPriceAdjustmentPlugin = new SpotPriceAdjustmentPlugin();
}