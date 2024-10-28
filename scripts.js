jQuery(document).ready(function($) {
    // Get active tab from the URL (default tab is sell)
    const activeTab = new URLSearchParams(window.location.search).get('tab') || 'sell';

    // Hide all tables initially
    $('.form-table').hide();
    
    // Only show table for the active tab, and hide heading for the inactive
    if (activeTab === 'sell') {
      $('h2:contains("Spot Price Buy Adjustment")').hide();
      $('h2:contains("Spot Price Adjustment")').nextAll('.form-table').first().show();
    } else {
      $('h2:contains("Spot Price Adjustment")').hide();
      $('h2:contains("Spot Price Buy Adjustment")').nextAll('.form-table').first().show();
    }
});
