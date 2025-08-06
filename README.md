# Track Geolocation Of Users Using Contact Form 7

A WordPress plugin that tracks and stores geolocation data when users submit Contact Form 7 forms.

## Features

### Advanced Admin Panel Filtering and Search

The plugin now includes comprehensive filtering and search functionality in the admin panel:

#### Filter Options
- **Form Filter**: Filter submissions by specific Contact Form 7 forms
- **Country Filter**: Filter by country using dropdown populated with actual data
- **City Filter**: Filter by city using dropdown populated with actual data
- **Date Range Filter**: Filter submissions by date range (from/to dates)
- **Search Functionality**: Search across all fields including country, city, state, and coordinates

#### Enhanced Features
- **AJAX Real-time Filtering**: All filters work instantly without page reloads
- **Sortable Columns**: All geolocation columns (Country, State, City, Lat/Long, API) are now sortable
- **Smart CSV Export**: Export functionality respects all active filters
- **Debounced Search**: Search input with intelligent delay for better performance
- **Date Validation**: Prevents invalid date ranges
- **Filter Count Display**: Shows number of submissions matching current filters
- **Clear Filters**: One-click option to reset all filters
- **Loading States**: Visual feedback during AJAX operations
- **URL State Management**: Filter state preserved in browser URL

#### User Experience Improvements
- **Modern UI**: Clean, professional styling with WordPress admin theme integration
- **Responsive Design**: Filters adapt to different screen sizes
- **Keyboard Navigation**: Search with Enter key support
- **Visual Feedback**: Clear indication of active filters and results count

## Installation

1. Upload the plugin files to `/wp-content/plugins/track-geolocation-of-users-using-contact-form-7/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure Contact Form 7 is installed and activated
4. Configure your API keys in the plugin settings

## Usage

### Admin Panel Access
Navigate to the admin panel and look for the "CF7 Geolocation Data" menu item. The advanced filtering interface will be displayed above the submissions list.

### Filtering Submissions
1. Use the search box to find specific submissions by any field
2. Select a specific form from the dropdown to filter by form
3. Choose a country or city from the respective dropdowns
4. Set date ranges using the date pickers
5. Click "Filter" to apply all filters or use the auto-submit feature
6. Use "Clear Filters" to reset all filters

### Exporting Data
1. Apply any desired filters
2. Click "Export CSV" to download filtered data
3. The exported file will include only submissions matching your current filters

### Sorting Data
Click on any column header to sort by that field. Sortable columns include:
- Country
- State  
- City
- Latitude/Longitude
- API Used
- Submission Date

## Requirements

- WordPress 4.7 or higher
- Contact Form 7 plugin
- PHP 7.0 or higher

## Support

For support and documentation, visit the plugin's documentation page or contact support.

## Changelog

### Version 2.9+
- **AJAX-Based Filtering**: Implemented real-time AJAX filtering and search functionality
- **External Assets**: Moved all inline CSS and JavaScript to existing admin.css and admin.js files
- **Proper Enqueuing**: Fixed asset loading using admin_enqueue_scripts hook for proper screen detection
- **Localized Scripts**: Added proper localization for AJAX nonces and error messages
- **Enhanced UX**: Added loading states, debounced search, and real-time pagination

### Version 2.8+
- Added advanced filtering and search functionality
- Enhanced admin panel with modern UI
- Improved CSV export with filter support
- Added sortable columns for all geolocation data
- Implemented real-time filtering and validation

