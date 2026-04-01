# Cart Token Exchange Plugin - Development Context

## Project Overview

The Cart Token Exchange plugin enables seamless cart synchronization between the Louis search application (louis.aph.org) and the WooCommerce store (aph.org). This plugin handles bidirectional cart token exchange for a headless commerce workflow.

## Architecture

### Cart Token Flow
1. **Louis → WooCommerce**: User adds items in Louis, gets cart token, visits WooCommerce with token parameter
2. **Token Detection**: Plugin detects cart token from URL, POST, or session
3. **Cart Restoration**: Plugin fetches cart data from WooCommerce Store API and restores locally
4. **Bidirectional Sync**: Local cart changes sync back to Louis Store API automatically
5. **Clean Redirect**: URL parameters removed after successful restoration

### Key Components

#### Main Plugin Class: `CartTokenExchange`
- **Singleton pattern** for single instance management
- **Multiple WordPress hooks** for early token detection (`wp`, `wp_loaded`, `template_redirect`)
- **WooCommerce integration** with settings panel and cart modification hooks

#### Core Methods

**Cart Restoration (`handle_cart_token_restoration`)**:
- Detects cart tokens from multiple sources with fallback to raw REQUEST_URI parsing
- Prevents duplicate restoration using token-specific tracking
- Validates JWT tokens (expiration, issuer, structure)
- Rate limiting (50 attempts/hour) with IP-based tracking
- Restores cart via WooCommerce Store API and redirects to clean URL

**Bidirectional Sync (`perform_cart_sync`)**:
- Hooks into WooCommerce cart modification events
- Syncs changes back to Store API using cart token
- Prevents sync during restoration process (5-second cooldown)
- Clears and rebuilds Store API cart to match local changes

**Token Detection (`detect_cart_token`)**:
- Priority: REQUEST_URI regex → GET → POST → Session
- Raw URI parsing handles cases where other plugins strip parameters
- Session tokens only used when no URL parameter present

## Security Features

### JWT Token Validation
- **Structure validation**: Ensures proper JWT format (header.payload.signature)
- **Expiration checking**: Rejects expired tokens
- **Issuer verification**: Only accepts tokens from "store-api"
- **Input sanitization**: All tokens sanitized before processing

### Rate Limiting
- **IP-based limiting**: 50 attempts per hour per IP address
- **Transient storage**: Uses WordPress transients for rate tracking
- **Graceful failure**: Rate limit exceeded logs warning but doesn't break site

### Duplicate Prevention
- **Static variables**: Prevents multiple runs per request
- **Token tracking**: Each unique token can only restore once per session
- **Session isolation**: Prevents session tokens from causing repeated restoration

## Configuration

### Admin Settings (WooCommerce → Settings → Cart Token Exchange)

| Setting | Default | Description |
|---------|---------|-------------|
| **Enable Cart Restoration** | Yes | Master switch for cart token functionality |
| **Token Expiry (Hours)** | 48 | Maximum age for accepted cart tokens |
| **Debug Logging** | No | Enables detailed logging for troubleshooting |

### WordPress Options
- `cte_enable_cart_restoration`: Enable/disable functionality
- `cte_token_expiry_hours`: Token expiration time
- `cte_debug_logging`: Debug logging toggle

## API Integration

### Store API Endpoints Used

**Cart Retrieval** (`GET /wp-json/wc/store/v1/cart`):
- Headers: `Cart-Token: {jwt_token}`
- Returns: Cart data with items array
- Used: During cart restoration

**Cart Clear** (`DELETE /wp-json/wc/store/v1/cart/items`):
- Headers: `Cart-Token: {jwt_token}`
- Clears: All items from Store API cart
- Used: Before syncing local changes

**Add Items** (`POST /wp-json/wc/store/v1/cart/add-item`):
- Headers: `Cart-Token: {jwt_token}`
- Body: `{id, quantity, variation}`
- Used: Syncing individual items back to Store API

## Session Management

### Session Data Stored
- `louis_cart_token`: Current cart token for bidirectional sync
- `louis_cart_restored_time`: Timestamp of last restoration (prevents immediate sync)
- `louis_last_restored_token`: Prevents duplicate restoration of same token

### Session Lifecycle
1. **Token restoration**: Cart token stored in session
2. **User modifications**: Changes synced using stored token
3. **New token arrival**: Session updated with new token
4. **Session cleanup**: WordPress handles session expiration

## Error Handling

### Graceful Degradation
- **Invalid tokens**: Fail silently, allow normal cart usage
- **API failures**: Log errors but don't break checkout process
- **Network issues**: Timeout after 30 seconds, continue normally
- **Rate limiting**: Log warning, prevent further attempts

### User Notifications
- **Restoration failure**: "Unable to restore your cart from Louis. Please add items again."
- **No error popups**: Silent failures maintain user experience
- **Admin logging**: Detailed logs available when debug mode enabled

## Logging Strategy

### Log Levels
- **Info**: Successful restorations and syncs
- **Warning**: Invalid tokens, empty carts, rate limit exceeded
- **Error**: API failures, network issues, exceptions

### Debug Mode
When enabled, additional logging includes:
- Request parameters ($_GET, $_POST, $_REQUEST)
- Raw REQUEST_URI parsing
- Token detection steps
- Cart restoration progress

### Log Format
```
[Cart Token Exchange] {
  "timestamp": "2025-07-23 02:48:37",
  "level": "info",
  "message": "Successfully restored 2 items from cart token",
  "context": {"items_count": 2},
  "user_ip": "192.168.1.100",
  "user_agent": "Mozilla/5.0..."
}
```

## Troubleshooting

### Common Issues

**Cart token not detected**:
- Check if other plugins are stripping URL parameters
- Verify raw REQUEST_URI contains cart_token parameter
- Enable debug logging to see detection attempts

**Duplicate cart items on refresh**:
- Fixed by token-specific tracking system
- Each token can only restore once per session
- Check session storage for `louis_last_restored_token`

**Bidirectional sync not working**:
- Verify WooCommerce cart modification hooks are firing
- Check that cart token exists in session
- Ensure Store API endpoints are accessible

**Rate limiting issues**:
- Current limit: 50 attempts/hour per IP
- Clear transients: `delete_transient('cte_cart_attempts_' . md5($ip))`
- Adjust limit in `check_rate_limit()` method if needed

### Debug Steps
1. **Enable debug logging** in plugin settings
2. **Test cart token URL** with valid JWT token
3. **Check WordPress error logs** for detailed execution trace
4. **Verify Store API responses** are returning expected cart data
5. **Test bidirectional sync** by modifying cart after restoration

## Development History

### Major Milestones
- ✅ **Initial cart restoration** - Basic token detection and cart restoration
- ✅ **Bidirectional sync** - Cart changes sync back to Louis Store API  
- ✅ **Security hardening** - Rate limiting, validation, duplicate prevention
- ✅ **Production cleanup** - Debug logging controlled, error handling improved
- ✅ **Bug fixes** - Duplicate restoration prevention, URL parameter handling
- ✅ **Testing & Deployment** - Local and Vercel testing completed successfully
- ✅ **Documentation** - Complete README.md and CLAUDE.md context files
- ✅ **Production Ready** - All debug logging cleaned up, fully deployed

### Technical Challenges Solved
1. **URL parameter stripping**: Other plugins removing cart_token before plugin runs
   - **Solution**: Raw REQUEST_URI regex parsing as fallback
2. **Duplicate restoration**: Cart items multiplying on page refresh
   - **Solution**: Token-specific tracking with session storage
3. **WooCommerce page detection**: `is_cart()` returning false during early hooks
   - **Solution**: Multiple hook approach with cart token presence override
4. **Bidirectional sync timing**: Sync interfering with restoration process
   - **Solution**: Time-based cooldown and restoration state tracking

## Production Deployment

### Pre-deployment Checklist
- [x] Debug logging disabled (`cte_debug_logging` = 'no')
- [x] Rate limiting configured appropriately (currently 50/hour)
- [x] Store API endpoints accessible and working
- [x] WooCommerce sessions functioning correctly
- [x] Error logging monitored and alerting configured
- [x] Local testing completed successfully
- [x] Vercel deployment testing completed successfully
- [x] Bidirectional sync verified working
- [x] Documentation completed (README.md + CLAUDE.md)

### Monitoring
- Monitor WordPress error logs for plugin-related messages
- Track cart restoration success rates through log analysis
- Watch for rate limiting warnings indicating potential abuse
- Verify bidirectional sync is maintaining cart consistency

### Support
- Plugin settings: WooCommerce → Settings → Cart Token Exchange
- Debug logging: Enable for detailed troubleshooting information
- Rate limit adjustment: Modify `check_rate_limit()` method if needed
- Session cleanup: WordPress handles automatically, manual cleanup via WC()->session

## Project Status

### Current Status: ✅ **PRODUCTION READY & DEPLOYED**

**Completion Date**: July 25, 2025  
**Testing Status**: Local ✅ | Vercel ✅ | Production ✅  
**Documentation Status**: Complete ✅  
**Code Status**: Clean & Production Ready ✅  

### Future Development Sessions

This plugin is **complete and fully functional**. Future sessions may involve:

1. **Performance Optimization**: Monitor and optimize if needed
2. **Feature Enhancements**: Additional cart features as requested
3. **Integration Updates**: Louis application changes requiring plugin updates
4. **Maintenance**: WordPress/WooCommerce version compatibility updates

### Session Handoff Notes

- **All core functionality implemented and tested**
- **Security features in place and validated**
- **Error handling comprehensive and production-ready**
- **Documentation complete for maintenance and enhancement**
- **No outstanding bugs or issues**

---

**Plugin Version**: 1.0.0  
**WordPress Compatibility**: 5.0+  
**WooCommerce Compatibility**: 5.0+  
**PHP Requirement**: 7.4+  
**Author**: Prolific Digital (https://prolificdigital.com)  
**Development Status**: ✅ **COMPLETE**