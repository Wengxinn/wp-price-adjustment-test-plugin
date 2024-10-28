# WooCommerce Spot Price Adjustment Plugin

## Implementation

1. Implemented a class for SpotPriceAdjustmentPlugin to organise code and reduce complexity.
2. Added a plugin settings menu to the admin page.
3. Added two section to the settings page/group: **Sell** and **Buy**.
4. Ensure 8 data types are stored in the database, 4 metals, including Gold, Silver, Platinum and Palldium, each of them is stored with twice for different sections/tabs. All of them are stored in the `adjustment_settings` option.
5. 4 input fields, one for each of the metals, are added to both sections/tabs respectively.
6. In `renderAdjustmentField` function, only the active tab's content (input fields) will be displayed. However, inactive tab's content will be rendered (but hidden) to ensure all 8 data are stored persistently in the database. 
7. To ensure dynamic rendering for sections/tabs, I utilised Javascript (jQuery). This is to ensure that the **heading** and the whole **table form** of the inactive tab/section will be hidden.
8. Upon activation, product categories (*Gold, Silver, Platinum, Palldium*) and attributes (*Metal, Weight, Weight unit, Purity*) will be created, if they have not already existed. Weight units (*oz, g, kg*) and metal attributes (*Gold, Silver, Platinum, Palldium*) will be added as terms to the corresponding attributes.
9. To display the adjusted product price on the frontend, the product category or the metal attribute will be retrieved. If the product belongs to any of the metal category or has `Metal` attribute, the frontend price should be updated. 
10. The product is computed based on the saved adjustment price stored in the database (`adjustment_settings` option), product attributes such as weight and purity, and the current market metal price. 
11. The metal price in the current market is fetched via the api, by passing the metal (category or Metal attribute) and weight unit as the arguments.
12. A function to get the **Buy Back Price** was implemented. The function first checks if a product SKU or metal is given. If an SKU is given, the product returned will be used to get the type of metal/category. 