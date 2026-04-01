<?php

/**
 * APH.org - Bedrock Application Configuration
 *
 * Your base production configuration goes in this file. Environment-specific
 * overrides go in their respective config/environments/{{WP_ENV}}.php file.
 *
 * All secrets and credentials are loaded from .env — never hardcode them here.
 */

use Roots\WPConfig\Config;
use function Env\env;

// USE_ENV_ARRAY + CONVERT_* + STRIP_QUOTES
Env\Env::$options = 31;

/**
 * Directory containing all of the site's files
 */
$root_dir = dirname(__DIR__);

/**
 * Document Root
 */
$webroot_dir = $root_dir . '/web';

/**
 * Use Dotenv to set required environment variables and load .env file in root
 * .env.local will override .env if it exists
 */
if (file_exists($root_dir . '/.env')) {
    $env_files = file_exists($root_dir . '/.env.local')
        ? ['.env', '.env.local']
        : ['.env'];

    $repository = Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(Dotenv\Repository\Adapter\EnvConstAdapter::class)
        ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
        ->immutable()
        ->make();

    $dotenv = Dotenv\Dotenv::create($repository, $root_dir, $env_files, false);
    $dotenv->load();

    $dotenv->required(['WP_HOME', 'WP_SITEURL']);
    if (!env('DATABASE_URL')) {
        $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
    }
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

/**
 * Infer WP_ENVIRONMENT_TYPE based on WP_ENV
 */
if (!env('WP_ENVIRONMENT_TYPE') && in_array(WP_ENV, ['production', 'staging', 'development', 'local'])) {
    Config::define('WP_ENVIRONMENT_TYPE', WP_ENV);
}

/**
 * URLs
 */
Config::define('WP_HOME', env('WP_HOME'));
Config::define('WP_SITEURL', env('WP_SITEURL'));

/**
 * Custom Content Directory
 */
Config::define('CONTENT_DIR', '/app');
Config::define('WP_CONTENT_DIR', $webroot_dir . Config::get('CONTENT_DIR'));
Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . Config::get('CONTENT_DIR'));

/**
 * DB settings
 */
if (env('DB_SSL')) {
    Config::define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL);
}

Config::define('DB_NAME', env('DB_NAME'));
Config::define('DB_USER', env('DB_USER'));
Config::define('DB_PASSWORD', env('DB_PASSWORD'));
Config::define('DB_HOST', env('DB_HOST') ?: 'localhost');
Config::define('DB_CHARSET', 'utf8mb4');
Config::define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

if (env('DATABASE_URL')) {
    $dsn = (object) parse_url(env('DATABASE_URL'));

    Config::define('DB_NAME', substr($dsn->path, 1));
    Config::define('DB_USER', $dsn->user);
    Config::define('DB_PASSWORD', isset($dsn->pass) ? $dsn->pass : null);
    Config::define('DB_HOST', isset($dsn->port) ? "{$dsn->host}:{$dsn->port}" : $dsn->host);
}

/**
 * Authentication Unique Keys and Salts
 */
Config::define('AUTH_KEY', env('AUTH_KEY'));
Config::define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
Config::define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
Config::define('NONCE_KEY', env('NONCE_KEY'));
Config::define('AUTH_SALT', env('AUTH_SALT'));
Config::define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
Config::define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
Config::define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', true);
Config::define('DISALLOW_FILE_EDIT', true);
Config::define('DISALLOW_FILE_MODS', true);
Config::define('WP_POST_REVISIONS', env('WP_POST_REVISIONS') ?? true);
Config::define('CONCATENATE_SCRIPTS', false);

/**
 * WordPress Memory Limit
 */
Config::define('WP_MEMORY_LIMIT', '2048M');
Config::define('WP_MAX_MEMORY_LIMIT', '2048M');

/**
 * Disable WP Cron (use server cron instead)
 */
Config::define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?? true);

/**
 * Debugging Settings
 */
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('WP_DEBUG_LOG', false);
Config::define('SCRIPT_DEBUG', false);
ini_set('display_errors', '0');

/**
 * Allow WordPress to detect HTTPS when behind a reverse proxy
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

/**
 * Load environment config
 */
$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

Config::apply();

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}

/**
 * WP Offload Media / S3 Settings
 * Configure via .env variables
 */
if (env('AS3CF_SETTINGS_BUCKET')) {
    define('AS3CF_SETTINGS', serialize([
        'provider' => env('AS3CF_SETTINGS_PROVIDER') ?: 'aws',
        'access-key-id' => env('AWS_ACCESS_KEY_ID') ?: '',
        'secret-access-key' => env('AWS_SECRET_ACCESS_KEY') ?: '',
        'bucket' => env('AS3CF_SETTINGS_BUCKET'),
        'region' => env('AS3CF_SETTINGS_REGION') ?: 'us-east-1',
        'use-yearmonth-folders' => true,
        'enable-object-prefix' => true,
        'object-prefix' => 'app/uploads/',
        'remove-local-file' => env('AS3CF_REMOVE_LOCAL') ?? false,
        'delivery-provider' => env('AS3CF_DELIVERY_PROVIDER') ?: 'aws',
        'serve-from-s3' => true,
        'enable-delivery-domain' => env('AS3CF_DELIVERY_DOMAIN') ? true : false,
        'delivery-domain' => env('AS3CF_DELIVERY_DOMAIN') ?: '',
        'copy-to-s3' => env('AS3CF_COPY_TO_S3') ?? false,
    ]));
}

/**
 * Plugin License Keys (loaded from .env)
 */
if (env('FACETWP_LICENSE_KEY')) {
    define('FACETWP_LICENSE_KEY', env('FACETWP_LICENSE_KEY'));
}
if (env('ACF_PRO_LICENSE')) {
    define('ACF_PRO_LICENSE', env('ACF_PRO_LICENSE'));
}
if (env('ACP_LICENCE')) {
    define('ACP_LICENCE', env('ACP_LICENCE'));
}
if (env('WPMDB_LICENCE')) {
    define('WPMDB_LICENCE', env('WPMDB_LICENCE'));
}
if (env('WPMUDEV_APIKEY')) {
    define('WPMUDEV_APIKEY', env('WPMUDEV_APIKEY'));
}
if (env('URE_LICENSE_KEY')) {
    define('URE_LICENSE_KEY', env('URE_LICENSE_KEY'));
}

/**
 * License Manager for WooCommerce
 */
if (env('LMFWC_PLUGIN_SECRET')) {
    define('LMFWC_PLUGIN_SECRET', env('LMFWC_PLUGIN_SECRET'));
}
if (env('LMFWC_PLUGIN_DEFUSE')) {
    define('LMFWC_PLUGIN_DEFUSE', env('LMFWC_PLUGIN_DEFUSE'));
}

/**
 * FQ and NET API Credentials
 */
if (env('FQ_URL_PRD')) {
    define('FQ_URL_PRD', env('FQ_URL_PRD'));
}
if (env('FQ_KEY_PRD')) {
    define('FQ_KEY_PRD', env('FQ_KEY_PRD'));
}
if (env('FQ_URL_STG')) {
    define('FQ_URL_STG', env('FQ_URL_STG'));
}
if (env('FQ_KEY_STG')) {
    define('FQ_KEY_STG', env('FQ_KEY_STG'));
}
if (env('NT_URL_PRD')) {
    define('NT_URL_PRD', env('NT_URL_PRD'));
}
if (env('NT_USR_PRD')) {
    define('NT_USR_PRD', env('NT_USR_PRD'));
}
if (env('NT_PSW_PRD')) {
    define('NT_PSW_PRD', env('NT_PSW_PRD'));
}
if (env('NT_URL_STG')) {
    define('NT_URL_STG', env('NT_URL_STG'));
}
if (env('NT_USR_STG')) {
    define('NT_USR_STG', env('NT_USR_STG'));
}
if (env('NT_PSW_STG')) {
    define('NT_PSW_STG', env('NT_PSW_STG'));
}

/**
 * HumanWare API Secret
 */
if (env('HUMANWARE_API_SECRET')) {
    define('HUMANWARE_API_SECRET', env('HUMANWARE_API_SECRET'));
}

/**
 * Environment URLs for stage switcher
 */
$envs = [
    'staging'     => env('STAGING_URL') ?: 'https://aph-staging.prolificdigital.io/',
    'production'  => env('PRODUCTION_URL') ?: 'https://www.aph.org/',
];
define('ENVIRONMENTS', $envs);

/**
 * CORS handling for Louis application
 */
$louis_referrer = '';
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $louis_referrer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME);
    $louis_referrer .= '://' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
}

$allowed_cors_origins = array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS') ?: '')));
$default_cors_origins = [
    'https://staging.louis.aph.org',
    'https://staginglouis.aph.org',
    'https://louis.aph.org',
    'https://louis-staging.prolificdigital.io',
    'http://aph-local',
    'http://aph-louis.local',
    'https://aph-local',
    'https://aph-louis.local',
];
$cors_origins = !empty($allowed_cors_origins) ? $allowed_cors_origins : $default_cors_origins;

// Normalize: strip trailing slashes for comparison
$louis_referrer_normalized = rtrim($louis_referrer, '/');
$cors_origins_normalized = array_map(function ($url) { return rtrim($url, '/'); }, $cors_origins);

if (in_array($louis_referrer_normalized, $cors_origins_normalized)) {
    header("Access-Control-Allow-Origin: $louis_referrer");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages, Link, X-WC-Store-API-Nonce");
}

define('WP_LOCAL_DEV_CORS', env('WP_LOCAL_DEV_CORS') ?? true);
