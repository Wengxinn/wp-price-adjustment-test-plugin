# WooCommerce Spot Price Adjustment Plugin

## Overview
The **Spot Price Adjustment Plugin** is a WordPress plugin designed for WooCommerce that allow users to manage and adjust spot prices of metals, including Gold, Silver, Platinum and Palladium. With the plugin installed and activated, users can adjust spot prices for these metals based on the current market rates, and the product prices will be automatically computed and rendered accordingly.


## Features
- **Manage Spot Price Adjustments:** Easily manage spot price adjustments for various metals, including Gold, Silver, Platinum, and Palladium.
- **Product Categorization:** Instantly create product categories and attributes related to weight and purity.
- **Real-time Price Fetching:** Get current market metal prices, ensuring accurate and precise spot pricing strategies.
- **Automatic Frontend Updates:** Eliminate the hassle of complex calculations, as adjusted product prices are computed on the backend and updated on the frontend automatically.


## Installation
1. Download the plugin folder `spot-price-adjustment`.
2. Upload the plugin to Wordpress via the Plugins menu or manually via FTP to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.


## Usage

### Spot price adjustment settings configurations
1. Once activated, navigate to **Spot Price Adjustment Settings** in the WordPress admin sidebar.
2. The settings page consists of two tabs: **Sell** and **Buy**. Adjust respective spot prices by navigating the tabs.
3. In each tab, there are four input fields to adjust spot prices for each metal (Gold, Silver, Platinum, Palladium).
4. Enter the adjustment values and click the **Save Changes** button to save your price adjustments.

### Product Attributes and Categories
- The plugin automatically create necessary product categories (*Gold, Silver, Platinum, Palladium*) and attributes (*Metal, Weight, Weight unit, Purity*) upon activation.
- **Make sure to update corresponding values for product attributes as they are necessary to compute product prices**.
- The plugin supports for three different weight units, including *oz, g and kg*.

### Real-time metal prices fetching
- The plugin fetches current metal prices from an external API `https://api.nfusionsolutions.biz/api/v1/Metals/spot/summary?metals='.$metal.'&unitofmeasure='.$unit.'&currency=aud&format=json&token=a1f2ffe5-6b4f-4cad-9947-0bdba9ea8af0`. Make sure the API is accessible and properly configured in the plugin.

### Product price
- When a product belongs to any of the metal categories or has the relevant metal attributes, the adjusted price will be displayed on the frontend.
- The display logic is defined as:

```
Saved adjustment price / 31.10 x weight (attribute) x purity (attribute) - current product price
```

### Buy Back Price Calculation
- To get the buy back price of a metal product, use the `getBuyBackPrice` function by passing arguments: product SKU or category (metal), weight, weight unit, and purity.


## Example
To get a buyback price for a metal product, you can call the function as follows:

```php
$buyback_price = $spotPriceAdjustmentPlugin->getBuyBackPrice($sku_metal, $weight, $purity, $unit);
echo "Buyback Price: " . $buyback_price;
```

