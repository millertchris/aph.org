# Prolific Digital Advanced CLI Posts Management Plugin
## Implementation Documentation

### Plugin Overview
**Plugin Name:** Prolific Digital Advanced CLI Posts Manager  
**Plugin Slug:** `prolific-advanced-cli-posts-manager`  
**Directory Name:** `prolific-advanced-cli-posts-manager`  
**Command Namespace:** `prolific` (standalone CLI)  
**Primary Command:** `prolific`

### ✅ Current Implementation Status
**Phase 1:** ✅ **COMPLETED** - Core Foundation with Standalone CLI  
**Phase 2:** ✅ **COMPLETED** - Advanced Features with WordPress Admin Interface  
**Phase 3:** 🟡 **PARTIALLY IMPLEMENTED** - WordPress Admin Interface completed

### Implementation Plan

#### Phase 1: Core Foundation (Initial Implementation)
This phase establishes the basic plugin structure and core functionality.

##### 1.1 Plugin Structure
```
prolific-advanced-cli-posts-manager/
├── prolific-advanced-cli-posts-manager.php (main plugin file)
├── includes/
│   ├── class-prolific-cli-posts-manager.php (main manager class)
│   ├── commands/
│   │   └── class-cleanup-posts-command.php (primary CLI command)
│   ├── utilities/
│   │   ├── class-post-query-builder.php (query construction)
│   │   ├── class-post-filter.php (status and meta filtering)
│   │   └── class-safety-manager.php (backup and safety)
│   └── interfaces/
│       └── interface-post-operation.php (extensibility interface)
├── assets/
│   └── admin/
│       ├── css/
│       └── js/
└── README.md
```

##### 1.2 Core Command Implementation ✅ COMPLETED
**Base Command Structure:**
```bash
prolific [--operation] [--target] [--filters] [--execution-options]
```

**✅ Implemented Operations:**
- `--operation=list` (default) - List matching posts with details
- `--operation=delete` - Remove posts permanently with backup
- `--operation=export-backup` - Create backup without deletion
- `--operation=modify` - Bulk post modifications
- `--dry-run` - Simulate operations without execution

**✅ Implemented Target Types:**
- `--posts` - Standard WordPress posts
- `--pages` - WordPress pages
- `--custom-post-type=TYPE` - Specific custom post type
- `--woocommerce-products` - WooCommerce products
- `--events` - Event post types

**✅ Implemented Filtering:**
- `--status=STATUS` - Filter by post status (standard & custom statuses)
- `--date-from=DATE --date-to=DATE` - Date range filtering
- `--author=AUTHOR` - Author-based filtering
- `--meta-key=KEY --meta-value=VALUE` - Custom field filtering
- `--category=SLUG` - Category filtering
- `--tag=SLUG` - Tag filtering
- `--exclude-ids=1,2,3` - Specific post exclusion
- `--limit=N` - Maximum number of posts to process
- `--batch-size=N` - Processing batch size (default: 100)

##### 1.3 Smart Query Strategy ✅ IMPLEMENTED
**Intelligent Status Detection:**
- **Standard Statuses:** Uses WordPress native filtering for efficiency
- **Custom Statuses:** Uses two-phase approach for compatibility

**Phase 1 - WordPress Native (Standard Statuses):**
```php
// For standard statuses (publish, draft, private, etc.)
$args = array(
    'post_type' => $target_post_types,
    'posts_per_page' => $limit, // Direct limit application
    'post_status' => $target_status, // WordPress handles filtering
    'fields' => 'ids'
);
```

**Phase 2 - Custom Status Handling:**
```php
// For custom statuses (e.g., WooCommerce custom statuses)
// 1. Get all posts
$args = array('post_status' => 'any', 'posts_per_page' => -1);
$posts = get_posts($args);

// 2. Filter by custom status
$filtered_posts = $this->post_filter->filter_by_status($posts, $custom_status);

// 3. Apply limit after filtering
$limited_posts = array_slice($filtered_posts, 0, $limit);
```

##### 1.4 Safety Implementation
**Pre-Operation Checks:**
- Verify user capabilities (`manage_options`, `delete_posts`)
- Validate post type existence
- Check for required plugins (WooCommerce for products, etc.)
- Confirm database connectivity

**Backup System:**
- JSON export of post data before deletion
- Backup file naming: `prolific-backup-YYYY-MM-DD-HH-MM-SS.json`
- Backup retention (configurable, default: 30 days)

**Confirmation System:**
- Interactive confirmation for destructive operations
- `--force` flag to skip confirmations
- Clear summary of what will be affected

#### Phase 2: Advanced Features ✅ COMPLETED

##### 2.1 Enhanced Filtering ✅ IMPLEMENTED
- ✅ `--meta-key=KEY --meta-value=VALUE` - Custom field filtering
- ✅ `--author=ID` - Author-based filtering  
- ✅ `--category=SLUG` - Taxonomy term filtering
- ✅ `--tag=SLUG` - Tag filtering
- ✅ `--exclude-ids=1,2,3` - Specific post exclusion
- ✅ `--limit=N` - Maximum posts limit (NEW)

##### 2.2 Extended Operations ✅ IMPLEMENTED
- ✅ `--operation=export-backup` - Create backup without deletion
- ✅ `--operation=modify` - Bulk post modifications (status changes, meta updates)
- ✅ Automatic backup creation before all delete operations
- 🔄 Restore functionality available via WordPress admin interface

##### 2.3 WooCommerce Integration ✅ IMPLEMENTED
- ✅ `--woocommerce-products` - Target WooCommerce products
- ✅ Support for custom WooCommerce statuses
- ✅ Full compatibility with WooCommerce product filtering
- ✅ Bypasses WP-CLI/WooCommerce conflicts via standalone runner

##### 2.4 Event Plugin Integration ✅ IMPLEMENTED
- ✅ `--events` - Target event post types
- ✅ Auto-detection of Event Calendar plugins, Events Manager, etc.
- ✅ Event-specific filtering support

##### 2.5 WordPress Admin Interface ✅ IMPLEMENTED
- ✅ Complete admin interface under Tools menu
- ✅ CLI Installation guide with multiple methods
- ✅ Available commands documentation  
- ✅ Database statistics viewer
- ✅ Backup management interface (view, details, file info)
- ✅ Plugin settings configuration
- ✅ Real-time system status indicators

#### Phase 3: Enterprise Features (Future Enhancement)

##### 3.1 Advanced Logging and Audit
- Detailed operation logs with timestamps
- Success/failure tracking
- Performance metrics
- Compliance reporting

##### 3.2 Plugin Hook System
```php
// Allow other plugins to register custom filters
do_action('prolific_register_post_filters');

// Allow modification of query arguments
$args = apply_filters('prolific_pre_query_args', $args, $operation);

// Allow post-processing of results
$results = apply_filters('prolific_post_operation_results', $results, $operation);
```

##### 3.3 WP Admin Interface ✅ COMPLETED
- ✅ Settings page for default configurations
- ✅ Operation history and logs viewer
- ✅ Backup management interface
- ✅ User role and capability management

## 🚀 Standalone CLI Implementation

### CLI Architecture
The plugin implements a **standalone CLI runner** that bypasses WP-CLI compatibility issues:

**Files:**
- `standalone-runner.php` - Main CLI entry point
- `install.php` - Smart installer with multiple installation methods
- `install-cli.sh` - Shell script installer

### Installation Methods
The plugin supports multiple installation approaches:

1. **Smart Installer (Recommended):**
   ```bash
   cd /path/to/plugin && php install.php
   ```

2. **Direct Usage:**
   ```bash
   php /path/to/plugin/standalone-runner.php --help
   ```

3. **Global Command:** 
   ```bash
   prolific --help  # After installation
   ```

### WordPress Path Detection
Enhanced path detection supports various WordPress structures:
- Standard WordPress installations
- Bedrock/custom structures (`/wp/`)
- Subdirectory installations
- Custom structures like `/files/web/wp/`

### Current Command Examples

**Basic Usage:**
```bash
# List posts
prolific --posts

# List with limit
prolific --posts --limit=20

# List WooCommerce products with custom status
prolific --woocommerce-products --status=louis --limit=50

# Delete with dry run
prolific --operation=delete --posts --status=draft --limit=100 --dry-run

# Actual deletion
prolific --operation=delete --posts --status=draft --limit=100

# Export backup
prolific --operation=export-backup --posts --category=archived

# Modify posts
prolific --operation=modify --posts --status=draft --dry-run
```

**Advanced Filtering:**
```bash
# Date range filtering
prolific --posts --date-from=2023-01-01 --date-to=2023-12-31 --limit=200

# Meta field filtering
prolific --posts --meta-key=featured --meta-value=yes --limit=50

# Category and author filtering
prolific --posts --category=news --author=john.doe --limit=30

# Exclude specific posts
prolific --posts --exclude-ids=1,2,3,4 --limit=100
```

## 🔧 Recent Updates & Fixes

### Version 1.0.0 Improvements

**🎯 Limit Parameter Fix:**
- Fixed `--limit` parameter not working with custom post statuses
- Implemented smart query strategy for standard vs custom statuses
- Now correctly processes only specified number of posts

**🛠️ WordPress Path Detection:**
- Enhanced path detection for custom WordPress structures
- Added support for `/files/web/wp/` directory structure
- Improved compatibility with various hosting environments

**📚 Documentation Updates:**
- Updated all command examples to use `prolific` instead of `wp prolific cleanup-posts`
- Corrected admin interface documentation to reflect actual implementation
- Added comprehensive installation guide with multiple methods

**🔄 Plugin Activation Improvements:**
- Smart installer automatically runs during plugin activation
- Multiple fallback installation methods
- Enhanced error handling and user feedback
- Graceful handling of servers with disabled exec() functions

**🎨 Admin Interface Enhancements:**
- Complete WordPress admin interface under Tools menu
- Installation guide with copy-paste commands
- Real-time system status indicators
- Comprehensive command documentation
- Fixed backup management interface with proper data type handling
- Enhanced backup file viewing and management

### Key Features Summary

✅ **Fully Functional CLI** - Complete standalone implementation  
✅ **WooCommerce Compatible** - Bypasses WP-CLI conflicts  
✅ **Custom Status Support** - Works with any custom post status  
✅ **Smart Filtering** - Efficient queries for both standard and custom statuses  
✅ **Safety Features** - Automatic backups, dry-run mode, confirmations  
✅ **WordPress Admin** - Complete admin interface for management  
✅ **Multiple Installation** - User-friendly installation with multiple methods  
✅ **Path Detection** - Works with various WordPress directory structures  

### Technical Implementation Details

#### Database Interaction Strategy
**Memory Management:**
- Process posts in configurable batches (default: 100)
- Use `get_posts()` with `fields => 'ids'` for memory efficiency
- Implement chunked processing for large datasets

**Transaction Safety:**
- Wrap multi-step operations in database transactions where possible
- Implement rollback mechanisms for failed operations
- Validate data integrity after operations

**Query Optimization:**
- Use efficient WordPress query methods
- Leverage existing WordPress caching
- Minimize database calls through batch operations

#### Error Handling and Logging
**Error Categories:**
- System errors (database, file system)
- User errors (invalid parameters, insufficient permissions)
- Operation errors (post not found, dependency missing)

**Logging Levels:**
- ERROR: Critical failures that stop operations
- WARNING: Non-critical issues that don't stop operations
- INFO: General operation information
- DEBUG: Detailed technical information

**Log File Management:**
- Default location: `wp-content/uploads/prolific-logs/`
- Automatic log rotation (daily files, 30-day retention)
- Configurable log levels and retention periods

#### Security Implementation
**Capability Checks:**
```php
// Verify user can perform operation
if (!current_user_can('manage_options') || !current_user_can('delete_posts')) {
    WP_CLI::error('Insufficient permissions for this operation.');
}
```

**Input Validation:**
- Sanitize all user inputs
- Validate post type existence
- Verify date formats and ranges
- Check for valid operation parameters

**Operation Verification:**
- Confirm operations before execution
- Provide detailed summaries of planned actions
- Implement dry-run mode for all operations

### Performance Considerations

#### Memory Management
- Use WordPress `WP_Query` with pagination for large datasets
- Implement garbage collection between batches
- Monitor memory usage and warn when approaching limits

#### Time Management
- Implement progress indicators for long operations
- Support for resuming interrupted operations
- Configurable timeouts and batch sizes

#### Database Performance
- Use appropriate indexes for custom queries
- Minimize JOIN operations
- Leverage WordPress object caching where beneficial

### Testing Strategy

#### Unit Testing
- Test individual classes and methods
- Mock WordPress functions for isolated testing
- Validate error handling and edge cases

#### Integration Testing
- Test with real WordPress installations
- Verify compatibility with major plugins (WooCommerce, event plugins)
- Test with various post types and custom statuses

#### Performance Testing
- Test with large datasets (10k+ posts)
- Measure memory usage and execution time
- Validate batch processing efficiency

### Deployment and Maintenance

#### Version Control
- Semantic versioning (MAJOR.MINOR.PATCH)
- Backward compatibility maintenance
- Migration scripts for setting changes

#### Documentation
- Inline code documentation (PHPDoc)
- User documentation with examples
- Developer API documentation

#### Support and Updates
- WordPress version compatibility testing
- PHP version compatibility (7.4+)
- Regular security audits and updates

This implementation plan provides a solid foundation for the Prolific Digital Advanced CLI Posts Manager plugin, with clear phases for development and extensive consideration for security, performance, and extensibility.