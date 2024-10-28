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
  private $active_tab;

  // Constructor
  function __construct() {
    add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    add_action('admin_menu', [$this, 'adminSettingsPage']);
    add_action('admin_init', [$this, 'settings']);
    register_activation_hook(__FILE__, [$this, 'initialisePrices']);
    add_action('init', [$this, 'createProductAttributesCategories']);
  }

  // Add a setting menu to the admin page
  function adminSettingsPage() {
    add_menu_page(
      $page_title = 'Spot Price Adjustment Settings', 
      $menu_title = 'Spot Price Adjustment Settings', 
      $capability = 'manage_options',  // User roles and capabilities
      $menu_slug = 'spot-price-adjustment-settings', 
      $callback = [$this, 'renderSettingsPage']
    );
  }

  // Function to initialise spot prices (as activation hook)
  function initialisePrices() {
    $default_prices = array(
      'gold_adjustment_sell' => 0, 
      'silver_adjustment_sell' => 0, 
      'platinum_adjustment_sell' => 0, 
      'palladium_adjustment_sell' => 0, 
      'gold_adjustment_buy' => 0, 
      'silver_adjustment_buy' => 0, 
      'platinum_adjustment_buy' => 0, 
      'palladium_adjustment_buy' => 0
    );
    // Initialise adjustment settings options with default values
    add_option('adjustment_settings', $default_prices);
  }

  // Render the settings page
  function renderSettingsPage() { 
    // Set default active tab to sell (if not set)
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sell';
    $this->active_tab = $active_tab;

    ?>
    <div class="wrap">
      <h1>Spot Price Adjustment Settings</h1>

      <h2 class="nav-tab-wrapper">
        <a href="?page=spot-price-adjustment-settings" class="nav-tab <?php echo $active_tab == 'sell' ? 'nav-tab-active' : ''; ?>">Sell</a>
        <a href="?page=spot-price-adjustment-settings&tab=buy" class="nav-tab <?php echo $active_tab == 'buy' ? 'nav-tab-active' : ''; ?>">Buy</a>
      </h2>
      
      <?php settings_errors(); // Default admin notices ?>

      <form method="POST" action="options.php">
        <?php
          settings_fields('adjustment_settings_group');
          do_settings_sections("spot-price-adjustment-settings");
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

    // Buy section
    add_settings_section(
      $id = 'adjustment_buy_section',
      $title = 'Spot Price Buy Adjustment',
      $callback = null,
      $page = 'spot-price-adjustment-settings'
    );

    // Add adjustment fields
    $this->addAdjustmentFields();
  }

  // Function to add fields to section
  function addAdjustmentFields() {
    $tabs = ['sell' => 'adjustment_sell_section', 'buy' => 'adjustment_buy_section'];
    $metals = ['gold', 'silver', 'platinum', 'palladium'];

    foreach ($tabs as $tab => $section) {
      foreach ($metals as $metal) {
        add_settings_field(
          $id = "{$metal}_adjustment_{$tab}",
          $title = ucfirst($metal) . ' Adjustment',
          $callback = [$this, 'renderAdjustmentField'], 
          $page = "spot-price-adjustment-settings",
          $section = $section,
          $args = [
            'tab' => $tab,
            'metal' => $metal,
          ]
        );
      }
    }
  }
  
  // Function to render settings field (for each metal)
  function renderAdjustmentField($args) {
    $option = get_option('adjustment_settings');
    $tab = $args['tab'];
    $metal = $args['metal'];
    $value = isset($option["{$metal}_adjustment_{$tab}"]) ? $option["{$metal}_adjustment_{$tab}"] : '';

    // Only render content for active tab (but also keep track of inactive tab content)
    if ($tab === $this->active_tab) {
      ?>
      <div class="currency-input-wrapper">
        <input type="number" class="currency-input" id="<?php echo esc_attr("{$metal}_adjustment_{$tab}") ?>" name="adjustment_settings[<?php echo esc_attr("{$metal}_adjustment_{$tab}"); ?>]" value="<?php echo esc_attr($value) ?>">
      </div>
      <?php
    } else {
      ?>
      <input type="hidden" class="currency-input" id="<?php echo esc_attr("{$metal}_adjustment_{$tab}") ?>" name="adjustment_settings[<?php echo esc_attr("{$metal}_adjustment_{$tab}"); ?>]" value="<?php echo esc_attr($value) ?>">
      <?php
    }
  }

  // Function to create product attributes and categories
  function createProductAttributesCategories() {
    $attributes = ['weight', 'purity'];
    $categories = ['Gold', 'Silver', 'Platinum', 'Palladium'];

    // Attributes
    foreach ($attributes as $attribute) {
      if (!term_exists($attribute, "pa_{$attribute}")) {
          wp_insert_term($attribute, "pa_{$attribute}"
          );
      }
    }

    // Category
    foreach ($categories as $category) {
      if (!term_exists($category, 'product_cat')) {
          wp_insert_term($category, 'product_cat');
      }
    }
  }

  // Function to enqueue external css style/scripts
  function enqueueScripts() {
    wp_enqueue_style('spot-price-adjustment-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('spot-price-adjustment-script', plugin_dir_url(__FILE__) . 'scripts.js', ['jquery'], null, true);
  }
}

// Initialize the plugin
if (class_exists('SpotPriceAdjustmentPlugin')) {
  $spotPriceAdjustmentPlugin = new SpotPriceAdjustmentPlugin();
}