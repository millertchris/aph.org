# Cart Token Exchange WordPress Plugin

A WordPress plugin that enables seamless bidirectional cart synchronization between the Louis search application and WooCommerce store using JWT cart tokens.

## Overview

This plugin facilitates headless commerce by allowing users to search and add items in the Louis application (louis.aph.org), then seamlessly continue their shopping experience in WooCommerce (aph.org) with their cart automatically restored.

## Key Features

### 🔄 **Bidirectional Cart Sync**
- **Louis → WooCommerce**: Cart restoration via JWT tokens
- **WooCommerce → Louis**: Real-time cart changes sync back to Louis Store API
- **Session Management**: Maintains cart state across platforms

### 🔒 **Security & Validation**
- **JWT Token Validation**: Expiration, issuer, and structure verification
- **Rate Limiting**: 50 attempts per hour per IP address
- **Duplicate Prevention**: Each token can only restore once per session
- **Input Sanitization**: All parameters properly sanitized

### ⚡ **Performance Optimized**
- **Early Hook Detection**: Multiple WordPress hooks for reliable token capture
- **Smart Caching**: Prevents unnecessary API calls
- **Graceful Degradation**: Silent failures maintain user experience

## Installation

1. Upload the plugin files to `/wp-content/plugins/cart-token-exchange/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **WooCommerce → Settings → Cart Token Exchange**
4. Configure settings as needed

## Configuration

### Admin Settings

Access via **WooCommerce → Settings → Cart Token Exchange**

| Setting | Default | Description |
|---------|---------|-------------|
| **Enable Cart Restoration** | Yes | Master switch for cart token functionality |
| **Token Expiry (Hours)** | 48 | Maximum age for accepted cart tokens |
| **Debug Logging** | No | Enables detailed logging for troubleshooting |

### WordPress Options

```php
// Enable/disable cart restoration
update_option('cte_enable_cart_restoration', 'yes');

// Set token expiry time (hours)
update_option('cte_token_expiry_hours', 48);

// Enable debug logging
update_option('cte_debug_logging', 'no');
```

## Usage

### Cart Restoration Flow

1. **User adds items in Louis** and gets a cart token
2. **User navigates to WooCommerce** with URL: `https://aph.org/cart?cart_token=JWT_TOKEN`
3. **Plugin detects token** and validates it
4. **Cart is restored** from WooCommerce Store API
5. **User is redirected** to clean URL without token parameter

### Bidirectional Sync

After cart restoration, any changes made in WooCommerce automatically sync back to Louis:
- Adding items
- Removing items  
- Changing quantities
- Emptying cart

## API Integration

### WooCommerce Store API Endpoints

**Cart Retrieval:**
```http
GET /wp-json/wc/store/v1/cart
Headers: Cart-Token: {jwt_token}
```

**Cart Management:**
```http
DELETE /wp-json/wc/store/v1/cart/items
POST /wp-json/wc/store/v1/cart/add-item
Headers: Cart-Token: {jwt_token}
```

### JWT Token Format

Cart tokens are JSON Web Tokens with this structure:

```json
{
  "user_id": "t_816efb79c407c692347850970...",
  "exp": 1753409820,
  "iss": "store-api", 
  "iat": 1753237020
}
```

## Error Handling

### Graceful Failures
- Invalid tokens fail silently without breaking checkout
- Network failures don't prevent normal cart operation
- Rate limiting logs warnings but allows continued use
- Missing products are skipped during restoration

### User Notifications
- **Restoration failure**: "Unable to restore your cart from Louis. Please add items again."
- **No error popups**: Maintains smooth user experience
- **Admin logging**: Detailed error tracking when debug enabled

## Troubleshooting

### Common Issues

**Cart token not detected:**
- Check if other plugins are stripping URL parameters
- Enable debug logging to see detection attempts
- Verify the plugin is activated and WooCommerce is running

**Duplicate items on refresh:**
- This is prevented by token-specific tracking
- Each token can only restore once per session
- Check browser developer tools for multiple requests

**Bidirectional sync not working:**
- Ensure WooCommerce sessions are functioning
- Verify Store API endpoints are accessible
- Check that cart token exists in session storage

### Debug Mode

Enable debug logging in plugin settings to see:
- Token detection attempts
- API request/response details
- Cart restoration progress
- Sync operation results

Debug logs appear in WordPress error logs with prefix `[Cart Token Exchange]`.

### Rate Limiting

If you hit rate limits:
```php
// Clear rate limiting for specific IP
$ip = 'user.ip.address';
delete_transient('cte_cart_attempts_' . md5($ip));

// Adjust rate limit (modify in plugin code)
// Current: 50 attempts per hour
```

## Hooks & Filters

### Actions Used
- `wp` - Early token detection
- `wp_loaded` - Secondary token detection  
- `template_redirect` - Final token detection
- `woocommerce_add_to_cart` - Bidirectional sync trigger
- `woocommerce_cart_item_removed` - Sync on item removal
- `woocommerce_after_cart_item_quantity_update` - Sync on quantity change
- `woocommerce_cart_emptied` - Sync on cart clear

### Settings Integration
- `woocommerce_settings_tabs_array` - Adds settings tab
- `woocommerce_settings_tabs_cart_token` - Settings page display
- `woocommerce_update_options_cart_token` - Settings save

## Development

### File Structure
```
cart-token-exchange/
├── cart-token-exchange.php     # Main plugin file
├── README.md                   # This file
├── CLAUDE.md                   # Development context
├── readme.txt                  # WordPress plugin readme
├── includes/                   # (Optional - currently unused)
└── languages/                  # Translation files
```

### Key Classes & Methods

**Main Class: `CartTokenExchange`**
- `handle_cart_token_restoration()` - Core restoration logic
- `detect_cart_token()` - Token detection from multiple sources
- `validate_cart_token()` - JWT validation
- `restore_cart_from_token()` - Store API integration
- `perform_cart_sync()` - Bidirectional sync logic

### Testing

**Test Cart Token (for development):**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoidF81YzI4OTE4ZTUyYzU5ZDE3MmYzOGNkODk2N2EyMjgiLCJleHAiOjE3NTM0MTEzNDMsImlzcyI6InN0b3JlLWFwaSIsImlhdCI6MTc1MzIzODU0M30.XG_pPEO0kuyIfGrHTOMgbQNAEyEWGPxGvt-8EhL68oM
```

**Test URLs:**
```
https://aph.org/cart?cart_token={TOKEN}
https://aph.org/checkout?cart_token={TOKEN}
```

## Security Considerations

### Token Security
- JWT tokens include expiration timestamps
- Tokens are validated for proper structure and issuer
- Rate limiting prevents brute force attempts
- All input is sanitized before processing

### Network Security
- HTTPS recommended for all token exchanges
- API calls use WordPress's `wp_remote_*` functions
- No sensitive data is logged (tokens are truncated in logs)
- Session data is handled by WooCommerce's session system

## Deployment

### Production Checklist
- [ ] Debug logging disabled
- [ ] Rate limiting configured appropriately
- [ ] SSL certificates installed
- [ ] Error monitoring configured
- [ ] WooCommerce Store API accessible
- [ ] Louis application deployment completed

### Monitoring
Monitor these metrics after deployment:
- Cart restoration success rates
- API response times
- Rate limiting warnings
- Session management performance

## Support

### Plugin Settings
Navigate to: **WooCommerce → Settings → Cart Token Exchange**

### Debug Information
Enable debug logging for detailed troubleshooting information that appears in WordPress error logs.

### Common WordPress Commands
```php
// Check if plugin is active
is_plugin_active('cart-token-exchange/cart-token-exchange.php');

// Get plugin options
get_option('cte_enable_cart_restoration');
get_option('cte_debug_logging');

// Check WooCommerce sessions
WC()->session->get_session_data();
```

---

## Technical Specifications

- **WordPress Version**: 5.0+
- **WooCommerce Version**: 5.0+  
- **PHP Version**: 7.4+
- **Author**: Prolific Digital
- **License**: GPLv2 or later

## Changelog

### Version 1.0.0
- Initial release
- JWT cart token validation
- Bidirectional cart synchronization
- Admin settings interface
- Rate limiting and security features
- Production-ready error handling

---

**For detailed development context and architecture information, see [CLAUDE.md](CLAUDE.md)**