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
  private $metals = ['Gold', 'Silver', 'Platinum', 'Palladium'];

  // Constructor
  function __construct() {
    add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    add_action('admin_menu', [$this, 'adminSettingsPage']);
    add_action('admin_init', [$this, 'settings']);
    register_activation_hook(__FILE__, [$this, 'initialisePrices']);
    add_action('init', [$this, 'addProductCategories']);
    add_action('init', [$this, 'addProductAttributes']);
    add_filter('woocommerce_get_price_html', [$this, 'displayProductPrice'], 10, 2);
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

  // Initialise spot prices upon activation (as an activation hook)
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

  // Register settings, sections and fields
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

  // Add fields to sections
  function addAdjustmentFields() {
    $tabs = ['sell' => 'adjustment_sell_section', 'buy' => 'adjustment_buy_section'];

    foreach ($tabs as $tab => $section) {
      foreach ($this->metals as $metal) {
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
  
  // Render settings field (for each metal)
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

  // Add product attributes (weight, purity, weight unit)
  function addProductAttributes() {
    // Insert weight and purity if not already exist
    $attributes = ['weight', 'purity'];
    foreach ($attributes as $attribute) {
      if (!taxonomy_exists("pa_{$attribute}")) {
        $attribute_data = [
          'name' => ucfirst($attribute),
          'slug' => sanitize_title($attribute),
          'type' => 'text', 
          'order_by' => 'menu_order'
        ];
        wc_create_attribute($attribute_data);
      }
    }

    // Insert weight unit attribute if not already exists
    $weight_units = ['oz', 'g', 'kg'];
    if (!taxonomy_exists("pa_weight_unit")) {
      $attribute_data = [
        'name' => 'Weight unit',
        'slug' => 'weight_unit',
        'type' => 'select', 
        'order_by' => 'menu_order'
      ];
      wc_create_attribute($attribute_data);
    }
    // Add unit terms to the attribute (pa_weight_units)
    foreach ($weight_units as $unit) {
      if (!term_exists($unit, 'pa_weight_unit')) {
        wp_insert_term($unit, 'pa_weight_unit'); 
     }
    }
  }

  // Add product categories, and metal attribute
  function addProductCategories() {
    // Insert categories if not already exist
    foreach ($this->metals as $metal) {
      if (!term_exists($metal, 'product_cat')) {
        wp_insert_term($metal, 'product_cat');
      }
    }

    // Insert metal attribute if not already exist
    if (!taxonomy_exists("pa_metal")) {
      $attribute_data = [
        'name' => 'Metal',
        'slug' => 'metal',
        'type' => 'select', 
        'order_by' => 'menu_order'
      ];
      wc_create_attribute($attribute_data);
    }
    // Add unit terms to the attribute (pa_metal)
    foreach ($this->metals as $metal) {
      if (!term_exists($metal, 'pa_metal')) {
        wp_insert_term($metal, 'pa_metal'); 
     }
    }
  }

  // Function to fetch the current metal price based on the specified unit
  function fetchMetalPrice($metal, $unit) {
    // Fetch current metal price using the remote api
    $api_url = 'https://api.nfusionsolutions.biz/api/v1/Metals/spot/summary?metals=' . $metal . '&unitofmeasure=' . $unit . '&currency=aud&format=json&token=a1f2ffe5-6b4f-4cad-9947-0bdba9ea8af0';
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
      error_log('Error when fetching metal price: ' . $response->get_error_message());
      return false;
    }

    // Example reponse: [{"requestedSymbol":"gold","requestedCurrency":"AUD","requestedUnitOfMeasure":"oz","success":true,"data":{"symbol":"Gold","baseCurrency":"USD","last":3771.0540491760253,"bid":3769.673997228417,"ask":3772.4341011236324,"high":3794.535633064572,"low":3760.7519613871323,"open":3792.3068491691856,"oneDayValue":3792.3068491691856,"oneDayChange":-21.25279999316051,"oneDayPercentChange":-0.56042,"timeStamp":"2024-10-28T09:05:54+00:00"}}]
    // Convert JSON response to array
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($data[0]['data'])) {
      // Use the last traded price
      return $data[0]['data']['last'];
    }
  }

  function displayProductPrice($price, $product) {
    // Retrieve all categories of the product (only retrieve names)
    $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);

    // Check if the product belongs to one of the metals/categories
    $metal_array = array_intersect($this->metals, $categories);

    // If the product does not belongs to any of the metals or no Metal attribute, display initial price
    if (!empty($metal_array)) {
      $metal = reset($metal_array);
    } else {
      // Retrieve metal attribute
      $metal = $product->get_attribute('pa_metal');
      // Not a metal product
      if (!isset($metal)) {
        return $price;
      }
    }

    // Update adjusted price
    $weight = (float) $product->get_attribute('pa_weight');
    $purity = (float) $product->get_attribute('pa_purity');
    $weight_unit = $product->get_attribute('pa_weight_unit');

    $adjustment_option = get_option('adjustment_settings');
    // Assign to the value if adjusted; otherwise set to 0
    $saved_adjustment_price = $adjustment_option["{$metal}_adjustment_sell"] ?? 0;

    // Adjust product price if spot price is set
    // Saved adjustment price / 31.10 x weight (attribute) x purity (attribute) - current product price  
    if ($saved_adjustment_price > 0) {
      // Fetch the current metal price
      $current_price = $this->fetchMetalPrice($metal, $weight_unit);
      if ($current_price === false) {
        return false;
      }

      $new_price = ($saved_adjustment_price / 31.10) * $weight * $purity - $current_price;
      return wc_price($new_price);
    }
  }

  // Function to get the buyback price
  function getBuyBackPrice($sku_metal, $weight, $purity, $unit) {
    // Try to fetch product through sku
    $product = wc_get_product($sku_metal);
        
    // Sku is given, get the product category
    if ($product) {
      // Retrieve all categories of the product (only retrieve names)
      $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
      // Check if the product belongs to one of the metals/categories
      $metal_array = array_intersect($this->metals, $categories);
      
      // The product belongs to any of the metals
      if (!empty($metal_array)) {
        $metal = reset($metal_array);
        // Check metal attribute if category not found
        if (!isset($metal)) {
          $metal = $product->get_attribute('pa_metal');
        }
      }
    } else {
      // Metal (category) is given
      $metal = $sku_metal;
    }

    // Retrieve saved adjustment buyback price
    $adjustment_option = get_option('adjustment_settings');
    $saved_adjustment_price = $adjustment_option["{$metal}_adjustment_buy"] ?? 0;

    // Adjust product by back price if spot buy price is set
    // (Current metal price - Saved adjustment buy back price) x weight (attribute) x purity (attribute)
    if ($saved_adjustment_price > 0) {
      // Fetch the current metal price
      $current_price = $this->fetchMetalPrice($metal, $weight_unit);
      if ($current_price === false) {
        return false;
      }

      $buyback_price = ($current_price - $saved_adjustment_price) * $weight * $purity;
      return wc_price($buyback_price);
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