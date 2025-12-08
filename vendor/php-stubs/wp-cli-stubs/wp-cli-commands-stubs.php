<?php
/**
 * Generated stub declarations for WP-CLI
 * @see https://wp-cli.org/
 * @see https://github.com/php-stubs/wp-cli-stubs
 */

namespace {
    class Oxymel
    {
        private $xml;
        private $dom;
        private $current_element;
        private $last_inserted;
        private $go_deep_on_next_element = 0;
        private $go_up_on_next_element = 0;
        private $nesting_level = 0;
        private $contains_nesting_level = 0;
        private $indentation = '  ';
        public function __construct()
        {
        }
        public function to_string()
        {
        }
        public function __call($name, $args)
        {
        }
        public function __get($name)
        {
        }
        public function contains()
        {
        }
        public function end()
        {
        }
        public function tag($name, $content_or_attributes = \null, $attributes = array())
        {
        }
        public function cdata($text)
        {
        }
        public function text($text)
        {
        }
        public function comment($text)
        {
        }
        public function xml()
        {
        }
        public function oxymel(\Oxymel $other)
        {
        }
        public function raw($raw_xml)
        {
        }
        private function add_element_to_dom($element)
        {
        }
        private function move_current_element_deep()
        {
        }
        private function move_current_element_up()
        {
        }
        private function get_content_and_attributes_from_tag_args($content_or_attributes, array $attributes)
        {
        }
        private function init_new_dom()
        {
        }
        private function xml_from_dom()
        {
        }
        private function create_element($name, $content, $attributes)
        {
        }
        private function add_opening_tag_from_element($element)
        {
        }
        private function add_closing_tag_from_tag_name($name)
        {
        }
        private function indent($string, $level)
        {
        }
    }
    class OxymelException extends \Exception
    {
    }
    /**
     * Adds, removes, fetches, and flushes the WP Object Cache object.
     *
     * By default, the WP Object Cache exists in PHP memory for the length of the
     * request (and is emptied at the end). Use a persistent object cache drop-in
     * to persist the object cache between requests.
     *
     * [Read the codex article](https://codex.wordpress.org/Class_Reference/WP_Object_Cache)
     * for more detail.
     *
     * ## EXAMPLES
     *
     *     # Set cache.
     *     $ wp cache set my_key my_value my_group 300
     *     Success: Set object 'my_key' in group 'my_group'.
     *
     *     # Get cache.
     *     $ wp cache get my_key my_group
     *     my_value
     *
     * @package wp-cli
     */
    class Cache_Command extends \WP_CLI_Command
    {
        /**
         * Adds a value to the object cache.
         *
         * Errors if a value already exists for the key, which means the value can't
         * be added.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * <value>
         * : Value to add to the key.
         *
         * [<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * [<expiration>]
         * : Define how long to keep the value, in seconds. `0` means as long as possible.
         * ---
         * default: 0
         * ---
         *
         * ## EXAMPLES
         *
         *     # Add cache.
         *     $ wp cache add my_key my_group my_value 300
         *     Success: Added object 'my_key' in group 'my_value'.
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Decrements a value in the object cache.
         *
         * Errors if the value can't be decremented.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * [<offset>]
         * : The amount by which to decrement the item's value.
         * ---
         * default: 1
         * ---
         *
         * [<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * ## EXAMPLES
         *
         *     # Decrease cache value.
         *     $ wp cache decr my_key 2 my_group
         *     48
         */
        public function decr($args, $assoc_args)
        {
        }
        /**
         * Removes a value from the object cache.
         *
         * Errors if the value can't be deleted.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * [<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * ## EXAMPLES
         *
         *     # Delete cache.
         *     $ wp cache delete my_key my_group
         *     Success: Object deleted.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Flushes the object cache.
         *
         * For WordPress multisite instances using a persistent object cache,
         * flushing the object cache will typically flush the cache for all sites.
         * Beware of the performance impact when flushing the object cache in
         * production.
         *
         * Errors if the object cache can't be flushed.
         *
         * ## EXAMPLES
         *
         *     # Flush cache.
         *     $ wp cache flush
         *     Success: The cache was flushed.
         */
        public function flush($args, $assoc_args)
        {
        }
        /**
         * Gets a value from the object cache.
         *
         * Errors if the value doesn't exist.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * [<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get cache.
         *     $ wp cache get my_key my_group
         *     my_value
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Increments a value in the object cache.
         *
         * Errors if the value can't be incremented.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * [<offset>]
         * : The amount by which to increment the item's value.
         * ---
         * default: 1
         * ---
         *
         * [<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * ## EXAMPLES
         *
         *     # Increase cache value.
         *     $ wp cache incr my_key 2 my_group
         *     50
         */
        public function incr($args, $assoc_args)
        {
        }
        /**
         * Replaces a value in the object cache, if the value already exists.
         *
         * Errors if the value can't be replaced.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * <value>
         * : Value to replace.
         *
         * [<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * [<expiration>]
         * : Define how long to keep the value, in seconds. `0` means as long as possible.
         * ---
         * default: 0
         * ---
         *
         * ## EXAMPLES
         *
         *     # Replace cache.
         *     $ wp cache replace my_key new_value my_group
         *     Success: Replaced object 'my_key' in group 'my_group'.
         */
        public function replace($args, $assoc_args)
        {
        }
        /**
         * Sets a value to the object cache, regardless of whether it already exists.
         *
         * Errors if the value can't be set.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * <value>
         * : Value to set on the key.
         *
         * [<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * [<expiration>]
         * : Define how long to keep the value, in seconds. `0` means as long as possible.
         * ---
         * default: 0
         * ---
         *
         * ## EXAMPLES
         *
         *     # Set cache.
         *     $ wp cache set my_key my_value my_group 300
         *     Success: Set object 'my_key' in group 'my_group'.
         */
        public function set($args, $assoc_args)
        {
        }
        /**
         * Attempts to determine which object cache is being used.
         *
         * Note that the guesses made by this function are based on the
         * WP_Object_Cache classes that define the 3rd party object cache extension.
         * Changes to those classes could render problems with this function's
         * ability to determine which object cache is being used.
         *
         * ## EXAMPLES
         *
         *     # Check cache type.
         *     $ wp cache type
         *     Default
         */
        public function type($args, $assoc_args)
        {
        }
        /**
         * Determines whether the object cache implementation supports a particular feature.
         *
         * ## OPTIONS
         *
         * <feature>
         * : Name of the feature to check for.
         *
         * ## EXAMPLES
         *
         *     # Check whether is add_multiple supported.
         *     $ wp cache supports add_multiple
         *     $ echo $?
         *     0
         *
         *     # Bash script for checking whether for support like this:
         *     if ! wp cache supports non_existing; then
         *         echo 'non_existing is not supported'
         *     fi
         */
        public function supports($args, $assoc_args)
        {
        }
        /**
         * Removes all cache items in a group, if the object cache implementation supports it.
         *
         * ## OPTIONS
         *
         * <group>
         * : Cache group key.
         *
         * ## EXAMPLES
         *
         *     # Clear cache group.
         *     $ wp cache flush-group my_group
         *     Success: Cache group 'my_group' was flushed.
         *
         * @subcommand flush-group
         */
        public function flush_group($args, $assoc_args)
        {
        }
        /**
         * Get a nested value from the cache.
         *
         * ## OPTIONS
         *
         * <key>
         * : Cache key.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to pluck.
         *
         * [--group=<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * [--format=<format>]
         * : The output format of the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         *   - yaml
         * ---
         */
        public function pluck($args, $assoc_args)
        {
        }
        /**
         * Update a nested value from the cache.
         *
         * ## OPTIONS
         *
         * <action>
         * : Patch action to perform.
         * ---
         * options:
         *   - insert
         *   - update
         *   - delete
         * ---
         *
         * <key>
         * : Cache key.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to patch.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--group=<group>]
         * : Method for grouping data within the cache which allows the same key to be used across groups.
         * ---
         * default: default
         * ---
         *
         * [--expiration=<expiration>]
         *  : Define how long to keep the value, in seconds. `0` means as long as possible.
         *  ---
         *  default: 0
         *  ---
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         */
        public function patch($args, $assoc_args)
        {
        }
    }
    /**
     * Adds, gets, and deletes entries in the WordPress Transient Cache.
     *
     * By default, the transient cache uses the WordPress database to persist values
     * between requests. On a single site installation, values are stored in the
     * `wp_options` table. On a multisite installation, values are stored in the
     * `wp_options` or the `wp_sitemeta` table, depending on use of the `--network`
     * flag.
     *
     * When a persistent object cache drop-in is installed (e.g. Redis or Memcached),
     * the transient cache skips the database and simply wraps the WP Object Cache.
     *
     * ## EXAMPLES
     *
     *     # Set transient.
     *     $ wp transient set sample_key "test data" 3600
     *     Success: Transient added.
     *
     *     # Get transient.
     *     $ wp transient get sample_key
     *     test data
     *
     *     # Delete transient.
     *     $ wp transient delete sample_key
     *     Success: Transient deleted.
     *
     *     # Delete expired transients.
     *     $ wp transient delete --expired
     *     Success: 12 expired transients deleted from the database.
     *
     *     # Delete all transients.
     *     $ wp transient delete --all
     *     Success: 14 transients deleted from the database.
     */
    class Transient_Command extends \WP_CLI_Command
    {
        /**
         * Gets a transient value.
         *
         * For a more complete explanation of the transient cache, including the
         * network|site cache, please see docs for `wp transient`.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the transient.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * [--network]
         * : Get the value of a network|site transient. On single site, this is
         * is a specially-named cache key. On multisite, this is a global cache
         * (instead of local to the site).
         *
         * ## EXAMPLES
         *
         *     $ wp transient get sample_key
         *     test data
         *
         *     $ wp transient get random_key
         *     Warning: Transient with key "random_key" is not set.
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Sets a transient value.
         *
         * `<expiration>` is the time until expiration, in seconds.
         *
         * For a more complete explanation of the transient cache, including the
         * network|site cache, please see docs for `wp transient`.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the transient.
         *
         * <value>
         * : Value to be set for the transient.
         *
         * [<expiration>]
         * : Time until expiration, in seconds.
         *
         * [--network]
         * : Set the value of a network|site transient. On single site, this is
         * is a specially-named cache key. On multisite, this is a global cache
         * (instead of local to the site).
         *
         * ## EXAMPLES
         *
         *     $ wp transient set sample_key "test data" 3600
         *     Success: Transient added.
         */
        public function set($args, $assoc_args)
        {
        }
        /**
         * Deletes a transient value.
         *
         * For a more complete explanation of the transient cache, including the
         * network|site cache, please see docs for `wp transient`.
         *
         * ## OPTIONS
         *
         * [<key>]
         * : Key for the transient.
         *
         * [--network]
         * : Delete the value of a network|site transient. On single site, this is
         * is a specially-named cache key. On multisite, this is a global cache
         * (instead of local to the site).
         *
         * [--all]
         * : Delete all transients.
         *
         * [--expired]
         * : Delete all expired transients.
         *
         * ## EXAMPLES
         *
         *     # Delete transient.
         *     $ wp transient delete sample_key
         *     Success: Transient deleted.
         *
         *     # Delete expired transients.
         *     $ wp transient delete --expired
         *     Success: 12 expired transients deleted from the database.
         *
         *     # Delete expired site transients.
         *     $ wp transient delete --expired --network
         *     Success: 1 expired transient deleted from the database.
         *
         *     # Delete all transients.
         *     $ wp transient delete --all
         *     Success: 14 transients deleted from the database.
         *
         *     # Delete all site transients.
         *     $ wp transient delete --all --network
         *     Success: 2 transients deleted from the database.
         *
         *     # Delete all transients in a multisite.
         *     $ wp transient delete --all --network && wp site list --field=url | xargs -n1 -I % wp --url=% transient delete --all
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Determines the type of transients implementation.
         *
         * Indicates whether the transients API is using an object cache or the
         * database.
         *
         * For a more complete explanation of the transient cache, including the
         * network|site cache, please see docs for `wp transient`.
         *
         * ## EXAMPLES
         *
         *     $ wp transient type
         *     Transients are saved to the database.
         */
        public function type()
        {
        }
        /**
         * Lists transients and their values.
         *
         * ## OPTIONS
         *
         * [--search=<pattern>]
         * : Use wildcards ( * and ? ) to match transient name.
         *
         * [--exclude=<pattern>]
         * : Pattern to exclude. Use wildcards ( * and ? ) to match transient name.
         *
         * [--network]
         * : Get the values of network|site transients. On single site, this is
         * a specially-named cache key. On multisite, this is a global cache
         * (instead of local to the site).
         *
         * [--unserialize]
         * : Unserialize transient values in output.
         *
         * [--human-readable]
         * : Human-readable output for expirations.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: table
         * options:
         *   - table
         *   - json
         *   - csv
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * This field will be displayed by default for each matching option:
         *
         * * name
         * * value
         * * expiration
         *
         * ## EXAMPLES
         *
         *     # List all transients
         *     $ wp transient list
         *      +------+-------+---------------+
         *      | name | value | expiration    |
         *      +------+-------+---------------+
         *      | foo  | bar   | 39 mins       |
         *      | foo2 | bar2  | no expiration |
         *      | foo3 | bar2  | expired       |
         *      | foo4 | bar4  | 4 hours       |
         *      +------+-------+---------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Get a nested value from a transient.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the transient.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to pluck.
         *
         * [--format=<format>]
         * : The output format of the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         *   - yaml
         * ---
         *
         * [--network]
         * : Get the value of a network|site transient. On single site, this is
         * a specially-named cache key. On multisite, this is a global cache
         * (instead of local to the site).
         */
        public function pluck($args, $assoc_args)
        {
        }
        /**
         * Update a nested value from a transient.
         *
         * ## OPTIONS
         *
         * <action>
         * : Patch action to perform.
         * ---
         * options:
         *   - insert
         *   - update
         *   - delete
         * ---
         *
         * <key>
         * : Key for the transient.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to patch.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * [--expiration=<expiration>]
         * : Time until expiration, in seconds.
         *
         * [--network]
         * : Get the value of a network|site transient. On single site, this is
         * a specially-named cache key. On multisite, this is a global cache
         * (instead of local to the site).
         */
        public function patch($args, $assoc_args)
        {
        }
        /**
         * Retrieves the expiration time.
         *
         * @param string $name              Transient name.
         * @param bool   $is_site_transient Optional. Whether this is a site transient. Default false.
         * @param bool   $human_readable    Optional. Whether to return the difference between now and the
         *                                  expiration time in a human-readable format. Default false.
         * @return string Expiration time string.
         */
        private function get_transient_expiration($name, $is_site_transient = \false, $human_readable = \false)
        {
        }
        /**
         * Deletes all expired transients.
         *
         * Only deletes the expired transients from the database.
         *
         * @param bool $network Whether to delete transients or network|site transients.
         */
        private function delete_expired($network)
        {
        }
        /**
         * Deletes all transients.
         *
         * Only deletes the transients from the database.
         *
         * @param bool $network Whether to delete transients or network|site transients.
         */
        private function delete_all($network)
        {
        }
    }
    /**
     * Base command that all checksum commands rely on.
     *
     * @package wp-cli
     */
    class Checksum_Base_Command extends \WP_CLI_Command
    {
        /**
         * Normalizes directory separators to slashes.
         *
         * @param string $path Path to convert.
         *
         * @return string Path with all backslashes replaced by slashes.
         */
        public static function normalize_directory_separators($path)
        {
        }
        /**
         * Read a remote file and return its contents.
         *
         * @param string $url URL of the remote file to read.
         *
         * @return mixed
         */
        protected static function _read($url)
        {
        }
        /**
         * Recursively get the list of files for a given path.
         *
         * @param string $path Root path to start the recursive traversal in.
         *
         * @return array<string>
         */
        protected function get_files($path)
        {
        }
        /**
         * Whether to include the file in the verification or not.
         *
         * Can be overridden in subclasses.
         *
         * @param string $filepath Path to a file.
         *
         * @return bool
         */
        protected function filter_file($filepath)
        {
        }
    }
    /**
     * Verifies core file integrity by comparing to published checksums.
     *
     * @package wp-cli
     */
    class Checksum_Core_Command extends \Checksum_Base_Command
    {
        /**
         * Whether or not to verify contents of the root directory.
         *
         * @var boolean
         */
        private $include_root = \false;
        /**
         * Files to exclude from the verification.
         *
         * @var array
         */
        private $exclude_files = [];
        /**
         * Verifies WordPress files against WordPress.org's checksums.
         *
         * Downloads md5 checksums for the current version from WordPress.org, and
         * compares those checksums against the currently installed files.
         *
         * For security, avoids loading WordPress when verifying checksums.
         *
         * If you experience issues verifying from this command, ensure you are
         * passing the relevant `--locale` and `--version` arguments according to
         * the values from the `Dashboard->Updates` menu in the admin area of the
         * site.
         *
         * ## OPTIONS
         *
         * [--include-root]
         * : Verify all files and folders in the root directory, and warn if any non-WordPress items are found.
         *
         * [--version=<version>]
         * : Verify checksums against a specific version of WordPress.
         *
         * [--locale=<locale>]
         * : Verify checksums against a specific locale of WordPress.
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * [--exclude=<files>]
         * : Exclude specific files from the checksum verification. Provide a comma-separated list of file paths.
         *
         * ## EXAMPLES
         *
         *     # Verify checksums
         *     $ wp core verify-checksums
         *     Success: WordPress installation verifies against checksums.
         *
         *     # Verify checksums for given WordPress version
         *     $ wp core verify-checksums --version=4.0
         *     Success: WordPress installation verifies against checksums.
         *
         *     # Verify checksums for given locale
         *     $ wp core verify-checksums --locale=en_US
         *     Success: WordPress installation verifies against checksums.
         *
         *     # Verify checksums for given locale
         *     $ wp core verify-checksums --locale=ja
         *     Warning: File doesn't verify against checksum: wp-includes/version.php
         *     Warning: File doesn't verify against checksum: readme.html
         *     Warning: File doesn't verify against checksum: wp-config-sample.php
         *     Error: WordPress installation doesn't verify against checksums.
         *
         *     # Verify checksums and exclude files
         *     $ wp core verify-checksums --exclude="readme.html"
         *     Success: WordPress installation verifies against checksums.
         *
         * @when before_wp_load
         */
        public function __invoke($args, $assoc_args)
        {
        }
        /**
         * Whether to include the file in the verification or not.
         *
         * @param string $filepath Path to a file.
         *
         * @return bool
         */
        protected function filter_file($filepath)
        {
        }
        /**
         * Gets version information from `wp-includes/version.php`.
         *
         * @return array {
         *     @type string $wp_version The WordPress version.
         *     @type int $wp_db_version The WordPress DB revision.
         *     @type string $tinymce_version The TinyMCE version.
         *     @type string $wp_local_package The TinyMCE version.
         * }
         */
        private static function get_wp_details()
        {
        }
        /**
         * Searches for the value assigned to variable `$var_name` in PHP code `$code`.
         *
         * This is equivalent to matching the `\$VAR_NAME = ([^;]+)` regular expression and returning
         * the first match either as a `string` or as an `integer` (depending if it's surrounded by
         * quotes or not).
         *
         * @param string $var_name Variable name to search for.
         * @param string $code PHP code to search in.
         *
         * @return int|string|null
         */
        private static function find_var($var_name, $code)
        {
        }
    }
    /**
     * Verifies plugin file integrity by comparing to published checksums.
     *
     * @package wp-cli
     */
    class Checksum_Plugin_Command extends \Checksum_Base_Command
    {
        /**
         * Cached plugin data for all installed plugins.
         *
         * @var array|null
         */
        private $plugins_data;
        /**
         * Array of detected errors.
         *
         * @var array
         */
        private $errors = array();
        /**
         * Verifies plugin files against WordPress.org's checksums.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to verify.
         *
         * [--all]
         * : If set, all plugins will be verified.
         *
         * [--strict]
         * : If set, even "soft changes" like readme.txt changes will trigger
         * checksum errors.
         *
         * [--version=<version>]
         * : Verify checksums against a specific plugin version.
         *
         * [--format=<format>]
         * : Render output in a specific format.
         * ---
         * default: table
         * options:
         *   - table
         *   - json
         *   - csv
         *   - yaml
         *   - count
         * ---
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * [--exclude=<name>]
         * : Comma separated list of plugin names that should be excluded from verifying.
         *
         * ## EXAMPLES
         *
         *     # Verify the checksums of all installed plugins
         *     $ wp plugin verify-checksums --all
         *     Success: Verified 8 of 8 plugins.
         *
         *     # Verify the checksums of a single plugin, Akismet in this case
         *     $ wp plugin verify-checksums akismet
         *     Success: Verified 1 of 1 plugins.
         */
        public function __invoke($args, $assoc_args)
        {
        }
        private function verify_hello_dolly_from_core($assoc_args)
        {
        }
        /**
         * Adds a new error to the array of detected errors.
         *
         * @param string $plugin_name Name of the plugin that had the error.
         * @param string $file Relative path to the file that had the error.
         * @param string $message Message explaining the error.
         */
        private function add_error($plugin_name, $file, $message)
        {
        }
        /**
         * Gets the currently installed version for a given plugin.
         *
         * @param string $path Relative path to plugin file to get the version for.
         *
         * @return string|false Installed version of the plugin, or false if not
         *                      found.
         */
        private function get_plugin_version($path)
        {
        }
        /**
         * Gets the names of all installed plugins.
         *
         * @return array<string> Names of all installed plugins.
         */
        private function get_all_plugin_names()
        {
        }
        /**
         * Gets the list of files that are part of the given plugin.
         *
         * @param string $path Relative path to the main plugin file.
         *
         * @return array<string> Array of files with their relative paths.
         */
        private function get_plugin_files($path)
        {
        }
        /**
         * Checks the integrity of a single plugin file by comparing it to the
         * officially provided checksum.
         *
         * @param string $path      Relative path to the plugin file to check the
         *                          integrity of.
         * @param array  $checksums Array of provided checksums to compare against.
         *
         * @return true|string
         */
        private function check_file_checksum($path, $checksums)
        {
        }
        /**
         * Checks whether the current environment supports 256-bit SHA-2.
         *
         * Should be supported for PHP 5+, but we might find edge cases depending on
         * host.
         *
         * @return bool
         */
        private function supports_sha256()
        {
        }
        /**
         * Gets the 256-bit SHA-2 of a given file.
         *
         * @param string $filepath Absolute path to the file to calculate the SHA-2
         *                         for.
         *
         * @return string
         */
        private function get_sha256($filepath)
        {
        }
        /**
         * Gets the MD5 of a given file.
         *
         * @param string $filepath Absolute path to the file to calculate the MD5
         *                         for.
         *
         * @return string
         */
        private function get_md5($filepath)
        {
        }
        /**
         * Gets the absolute path to a relative plugin file.
         *
         * @param string $path Relative path to get the absolute path for.
         *
         * @return string
         */
        private function get_absolute_path($path)
        {
        }
        /**
         * Returns a list of files that only trigger checksum errors in strict mode.
         *
         * @return array<string> Array of file names.
         */
        private function get_soft_change_files()
        {
        }
        /**
         * Checks whether a given file will only trigger checksum errors in strict
         * mode.
         *
         * @param string $file File to check.
         *
         * @return bool Whether the file only triggers checksum errors in strict
         * mode.
         */
        private function is_soft_change_file($file)
        {
        }
    }
    /**
     * Downloads, installs, updates, and manages a WordPress installation.
     */
    final class Core_Command_Namespace extends \WP_CLI\Dispatcher\CommandNamespace
    {
    }
    /**
     * Manages plugins, including installs, activations, and updates.
     */
    final class Plugin_Command_Namespace extends \WP_CLI\Dispatcher\CommandNamespace
    {
    }
}
namespace WP_CLI\Fetchers {
    /**
     * Fetch a WordPress plugin based on one of its attributes.
     *
     * This is a special version of the plugin fetcher. It doesn't use the
     * `all_plugins` filter, so that plugins cannot hide themselves from the
     * checks.
     *
     */
    class UnfilteredPlugin extends \WP_CLI\Fetchers\Base
    {
        /**
         * @var string $msg Error message to use when invalid data is provided
         */
        protected $msg = "The '%s' plugin could not be found.";
        /**
         * Get a plugin object by name.
         *
         * @param string $name
         *
         * @return object|false
         */
        public function get($name)
        {
        }
    }
}
namespace {
    /**
     * Generates and reads the wp-config.php file.
     *
     * ## EXAMPLES
     *
     *     # Create standard wp-config.php file.
     *     $ wp config create --dbname=testing --dbuser=wp --dbpass=securepswd --locale=ro_RO
     *     Success: Generated 'wp-config.php' file.
     *
     *     # List constants and variables defined in wp-config.php file.
     *     $ wp config list
     *     +------------------+------------------------------------------------------------------+----------+
     *     | key              | value                                                            | type     |
     *     +------------------+------------------------------------------------------------------+----------+
     *     | table_prefix     | wp_                                                              | variable |
     *     | DB_NAME          | wp_cli_test                                                      | constant |
     *     | DB_USER          | root                                                             | constant |
     *     | DB_PASSWORD      | root                                                             | constant |
     *     | AUTH_KEY         | r6+@shP1yO&$)1gdu.hl[/j;7Zrvmt~o;#WxSsa0mlQOi24j2cR,7i+QM/#7S:o^ | constant |
     *     | SECURE_AUTH_KEY  | iO-z!_m--YH$Tx2tf/&V,YW*13Z_HiRLqi)d?$o-tMdY+82pK$`T.NYW~iTLW;xp | constant |
     *     +------------------+------------------------------------------------------------------+----------+
     *
     *     # Get wp-config.php file path.
     *     $ wp config path
     *     /home/person/htdocs/project/wp-config.php
     *
     *     # Get the table_prefix as defined in wp-config.php file.
     *     $ wp config get table_prefix
     *     wp_
     *
     *     # Set the WP_DEBUG constant to true.
     *     $ wp config set WP_DEBUG true --raw
     *     Success: Updated the constant 'WP_DEBUG' in the 'wp-config.php' file with the raw value 'true'.
     *
     *     # Delete the COOKIE_DOMAIN constant from the wp-config.php file.
     *     $ wp config delete COOKIE_DOMAIN
     *     Success: Deleted the constant 'COOKIE_DOMAIN' from the 'wp-config.php' file.
     *
     *     # Launch system editor to edit wp-config.php file.
     *     $ wp config edit
     *
     *     # Check whether the DB_PASSWORD constant exists in the wp-config.php file.
     *     $ wp config has DB_PASSWORD
     *     $ echo $?
     *     0
     *
     *     # Assert if MULTISITE is true.
     *     $ wp config is-true MULTISITE
     *     $ echo $?
     *     0
     *
     *     # Get new salts for your wp-config.php file.
     *     $ wp config shuffle-salts
     *     Success: Shuffled the salt keys.
     *
     * @package wp-cli
     */
    class Config_Command extends \WP_CLI_Command
    {
        /**
         * List of characters that are valid for a key name.
         *
         * @var string
         */
        const VALID_KEY_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        /**
         * List of default constants that are generated by WordPress Core.
         *
         * @string
         */
        const DEFAULT_SALT_CONSTANTS = ['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];
        /**
         * Retrieve the initial locale from the WordPress version file.
         *
         * @return string Initial locale if present, or an empty string if not.
         */
        private static function get_initial_locale()
        {
        }
        /**
         * Generates a wp-config.php file.
         *
         * Creates a new wp-config.php with database constants, and verifies that
         * the database constants are correct.
         *
         * ## OPTIONS
         *
         * --dbname=<dbname>
         * : Set the database name.
         *
         * --dbuser=<dbuser>
         * : Set the database user.
         *
         * [--dbpass=<dbpass>]
         * : Set the database user password.
         *
         * [--dbhost=<dbhost>]
         * : Set the database host.
         * ---
         * default: localhost
         * ---
         *
         * [--dbprefix=<dbprefix>]
         * : Set the database table prefix.
         * ---
         * default: wp_
         * ---
         *
         * [--dbcharset=<dbcharset>]
         * : Set the database charset.
         * ---
         * default: utf8
         * ---
         *
         * [--dbcollate=<dbcollate>]
         * : Set the database collation.
         * ---
         * default:
         * ---
         *
         * [--locale=<locale>]
         * : Set the WPLANG constant. Defaults to $wp_local_package variable.
         *
         * [--extra-php]
         * : If set, the command copies additional PHP code into wp-config.php from STDIN.
         *
         * [--skip-salts]
         * : If set, keys and salts won't be generated, but should instead be passed via `--extra-php`.
         *
         * [--skip-check]
         * : If set, the database connection is not checked.
         *
         * [--force]
         * : Overwrites existing files, if present.
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be created. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * [--insecure]
         * : Retry API download without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Standard wp-config.php file
         *     $ wp config create --dbname=testing --dbuser=wp --dbpass=securepswd --locale=ro_RO
         *     Success: Generated 'wp-config.php' file.
         *
         *     # Enable WP_DEBUG and WP_DEBUG_LOG
         *     $ wp config create --dbname=testing --dbuser=wp --dbpass=securepswd --extra-php <<PHP
         *     define( 'WP_DEBUG', true );
         *     define( 'WP_DEBUG_LOG', true );
         *     PHP
         *     Success: Generated 'wp-config.php' file.
         *
         *     # Avoid disclosing password to bash history by reading from password.txt
         *     # Using --prompt=dbpass will prompt for the 'dbpass' argument
         *     $ wp config create --dbname=testing --dbuser=wp --prompt=dbpass < password.txt
         *     Success: Generated 'wp-config.php' file.
         */
        public function create($_, $assoc_args)
        {
        }
        /**
         * Gives error when wp-config already exist and try to create it.
         *
         * @param string $wp_config_file_name Config file name.
         * @return void
         */
        private function config_file_already_exist_error($wp_config_file_name)
        {
        }
        /**
         * Launches system editor to edit the wp-config.php file.
         *
         * ## OPTIONS
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be edited. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * ## EXAMPLES
         *
         *     # Launch system editor to edit wp-config.php file
         *     $ wp config edit
         *
         *     # Edit wp-config.php file in a specific editor
         *     $ EDITOR=vim wp config edit
         *
         * @when before_wp_load
         */
        public function edit($_, $assoc_args)
        {
        }
        /**
         * Gets the path to wp-config.php file.
         *
         * ## EXAMPLES
         *
         *     # Get wp-config.php file path
         *     $ wp config path
         *     /home/person/htdocs/project/wp-config.php
         *
         * @when before_wp_load
         */
        public function path()
        {
        }
        /**
         * Lists variables, constants, and file includes defined in wp-config.php file.
         *
         * ## OPTIONS
         *
         * [<filter>...]
         * : Name or partial name to filter the list by.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * Dotenv is limited to non-object values.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         *   - dotenv
         * ---
         *
         * [--strict]
         * : Enforce strict matching when a filter is provided.
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be read. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * ## EXAMPLES
         *
         *     # List constants and variables defined in wp-config.php file.
         *     $ wp config list
         *     +------------------+------------------------------------------------------------------+----------+
         *     | key              | value                                                            | type     |
         *     +------------------+------------------------------------------------------------------+----------+
         *     | table_prefix     | wp_                                                              | variable |
         *     | DB_NAME          | wp_cli_test                                                      | constant |
         *     | DB_USER          | root                                                             | constant |
         *     | DB_PASSWORD      | root                                                             | constant |
         *     | AUTH_KEY         | r6+@shP1yO&$)1gdu.hl[/j;7Zrvmt~o;#WxSsa0mlQOi24j2cR,7i+QM/#7S:o^ | constant |
         *     | SECURE_AUTH_KEY  | iO-z!_m--YH$Tx2tf/&V,YW*13Z_HiRLqi)d?$o-tMdY+82pK$`T.NYW~iTLW;xp | constant |
         *     +------------------+------------------------------------------------------------------+----------+
         *
         *     # List only database user and password from wp-config.php file.
         *     $ wp config list DB_USER DB_PASSWORD --strict
         *     +------------------+-------+----------+
         *     | key              | value | type     |
         *     +------------------+-------+----------+
         *     | DB_USER          | root  | constant |
         *     | DB_PASSWORD      | root  | constant |
         *     +------------------+-------+----------+
         *
         *     # List all salts from wp-config.php file.
         *     $ wp config list _SALT
         *     +------------------+------------------------------------------------------------------+----------+
         *     | key              | value                                                            | type     |
         *     +------------------+------------------------------------------------------------------+----------+
         *     | AUTH_SALT        | n:]Xditk+_7>Qi=>BmtZHiH-6/Ecrvl(V5ceeGP:{>?;BT^=[B3-0>,~F5z$(+Q$ | constant |
         *     | SECURE_AUTH_SALT | ?Z/p|XhDw3w}?c.z%|+BAr|(Iv*H%%U+Du&kKR y?cJOYyRVRBeB[2zF-`(>+LCC | constant |
         *     | LOGGED_IN_SALT   | +$@(1{b~Z~s}Cs>8Y]6[m6~TnoCDpE>O%e75u}&6kUH!>q:7uM4lxbB6[1pa_X,q | constant |
         *     | NONCE_SALT       | _x+F li|QL?0OSQns1_JZ{|Ix3Jleox-71km/gifnyz8kmo=w-;@AE8W,(fP<N}2 | constant |
         *     +------------------+------------------------------------------------------------------+----------+
         *
         * @when before_wp_load
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets the value of a specific constant or variable defined in wp-config.php file.
         *
         * ## OPTIONS
         *
         * <name>
         * : Name of the wp-config.php constant or variable.
         *
         * [--type=<type>]
         * : Type of config value to retrieve. Defaults to 'all'.
         * ---
         * default: all
         * options:
         *   - constant
         *   - variable
         *   - all
         * ---
         *
         * [--format=<format>]
         * : Get value in a particular format.
         * Dotenv is limited to non-object values.
         * ---
         * default: var_export
         * options:
         *   - var_export
         *   - json
         *   - yaml
         *   - dotenv
         * ---
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be read. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * ## EXAMPLES
         *
         *     # Get the table_prefix as defined in wp-config.php file.
         *     $ wp config get table_prefix
         *     wp_
         *
         * @when before_wp_load
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Determines whether value of a specific defined constant or variable is truthy.
         *
         * This determination is made by evaluating the retrieved value via boolval().
         *
         * ## OPTIONS
         *
         * <name>
         * : Name of the wp-config.php constant or variable.
         *
         * [--type=<type>]
         * : Type of config value to retrieve. Defaults to 'all'.
         * ---
         * default: all
         * options:
         *   - constant
         *   - variable
         *   - all
         * ---
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be read. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * ## EXAMPLES
         *
         *     # Assert if MULTISITE is true
         *     $ wp config is-true MULTISITE
         *     $ echo $?
         *     0
         *
         * @subcommand is-true
         * @when before_wp_load
         */
        public function is_true($args, $assoc_args)
        {
        }
        /**
         * Get the array of wp-config.php constants and variables.
         *
         * @param string $wp_config_path Config file path
         *
         * @return array
         */
        private static function get_wp_config_vars($wp_config_path = '')
        {
        }
        /**
         * Sets the value of a specific constant or variable defined in wp-config.php file.
         *
         * ## OPTIONS
         *
         * <name>
         * : Name of the wp-config.php constant or variable.
         *
         * <value>
         * : Value to set the wp-config.php constant or variable to.
         *
         * [--add]
         * : Add the value if it doesn't exist yet.
         * This is the default behavior, override with --no-add.
         *
         * [--raw]
         * : Place the value into the wp-config.php file as is, instead of as a quoted string.
         *
         * [--anchor=<anchor>]
         * : Anchor string where additions of new values are anchored around.
         * Defaults to "/* That's all, stop editing!".
         * The special case "EOF" string uses the end of the file as the anchor.
         *
         * [--placement=<placement>]
         * : Where to place the new values in relation to the anchor string.
         * ---
         * default: 'before'
         * options:
         *   - before
         *   - after
         * ---
         *
         * [--separator=<separator>]
         * : Separator string to put between an added value and its anchor string.
         * The following escape sequences will be recognized and properly interpreted: '\n' => newline, '\r' => carriage return, '\t' => tab.
         * Defaults to a single EOL ("\n" on *nix and "\r\n" on Windows).
         *
         * [--type=<type>]
         * : Type of the config value to set. Defaults to 'all'.
         * ---
         * default: all
         * options:
         *   - constant
         *   - variable
         *   - all
         * ---
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be modified. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * ## EXAMPLES
         *
         *     # Set the WP_DEBUG constant to true.
         *     $ wp config set WP_DEBUG true --raw
         *     Success: Updated the constant 'WP_DEBUG' in the 'wp-config.php' file with the raw value 'true'.
         *
         * @when before_wp_load
         */
        public function set($args, $assoc_args)
        {
        }
        /**
         * Deletes a specific constant or variable from the wp-config.php file.
         *
         * ## OPTIONS
         *
         * <name>
         * : Name of the wp-config.php constant or variable.
         *
         * [--type=<type>]
         * : Type of the config value to delete. Defaults to 'all'.
         * ---
         * default: all
         * options:
         *   - constant
         *   - variable
         *   - all
         * ---
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be modified. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * ## EXAMPLES
         *
         *     # Delete the COOKIE_DOMAIN constant from the wp-config.php file.
         *     $ wp config delete COOKIE_DOMAIN
         *     Success: Deleted the constant 'COOKIE_DOMAIN' from the 'wp-config.php' file.
         *
         * @when before_wp_load
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Checks whether a specific constant or variable exists in the wp-config.php file.
         *
         * ## OPTIONS
         *
         * <name>
         * : Name of the wp-config.php constant or variable.
         *
         * [--type=<type>]
         * : Type of the config value to set. Defaults to 'all'.
         * ---
         * default: all
         * options:
         *   - constant
         *   - variable
         *   - all
         * ---
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be checked. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * ## EXAMPLES
         *
         *     # Check whether the DB_PASSWORD constant exists in the wp-config.php file.
         *     $ wp config has DB_PASSWORD
         *
         * @when before_wp_load
         */
        public function has($args, $assoc_args)
        {
        }
        /**
         * Refreshes the salts defined in the wp-config.php file.
         *
         * ## OPTIONS
         *
         * [<keys>...]
         * : One ore more keys to shuffle. If none are provided, this falls back to the default WordPress Core salt keys.
         *
         * [--force]
         * : If an unknown key is requested to be shuffled, add it instead of throwing a warning.
         *
         * [--config-file=<path>]
         * : Specify the file path to the config file to be modified. Defaults to the root of the
         * WordPress installation and the filename "wp-config.php".
         *
         * [--insecure]
         * : Retry API download without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Get new salts for your wp-config.php file
         *     $ wp config shuffle-salts
         *     Success: Shuffled the salt keys.
         *
         *     # Add a cache key salt to the wp-config.php file
         *     $ wp config shuffle-salts WP_CACHE_KEY_SALT --force
         *     Success: Shuffled the salt keys.
         *
         * @subcommand shuffle-salts
         * @when before_wp_load
         */
        public function shuffle_salts($args, $assoc_args)
        {
        }
        /**
         * Filters wp-config.php file configurations.
         *
         * @param array $vars
         * @param array $previous_list
         * @param string $type
         * @param array $exclude_list
         * @return array
         */
        private static function get_wp_config_diff($vars, $previous_list, $type, $exclude_list = [])
        {
        }
        /**
         * Read the salts from the WordPress.org API.
         *
         * @param bool   $insecure Optional. Whether to retry without certificate validation on TLS handshake failure.
         * @return string String with a set of PHP define() statements to define the salts.
         * @throws ExitException If the remote request failed.
         */
        private static function fetch_remote_salts($insecure = \false)
        {
        }
        /**
         * Prints the value of a constant or variable defined in the wp-config.php file.
         *
         * If the constant or variable is not defined in the wp-config file then an error will be returned.
         *
         * @param string $name
         * @param string $type
         * @param string $type
         * @param string $wp_config_file_name Config file name
         * @return string The value of the requested constant or variable as defined in the wp-config.php file; if the
         *                requested constant or variable is not defined then the function will print an error and exit.
         */
        private function return_value($name, $type, $values, $wp_config_file_name)
        {
        }
        /**
         * Generates a unique key/salt for the wp-config file.
         *
         * @throws Exception
         *
         * @return string
         */
        private static function unique_key()
        {
        }
        /**
         * Filters the values based on a provider filter key.
         *
         * @param array $values
         * @param array $filters
         * @param bool $strict
         *
         * @return array
         */
        private function filter_values($values, $filters, $strict)
        {
        }
        /**
         * Gets the path to the wp-config.php file or gives a helpful error if none found.
         *
         * @param array $assoc_args associative arguments given while calling wp config subcommand
         * @return string Path to wp-config.php file.
         */
        private function get_config_path($assoc_args)
        {
        }
        /**
         * Gives error the wp-config file not found
         *
         * @param string $wp_config_file_name Config file name.
         * @return void
         */
        private function config_file_not_found_error($wp_config_file_name)
        {
        }
        /**
         * Parses the separator argument, to allow for special character handling.
         *
         * Does the following transformations:
         * - '\n' => "\n" (newline)
         * - '\r' => "\r" (carriage return)
         * - '\t' => "\t" (tab)
         *
         * @param string $separator Separator string to parse.
         *
         * @return mixed Parsed separator string.
         */
        private function parse_separator($separator)
        {
        }
        /**
         * Gets the value of a specific constant or variable defined in wp-config.php file.
         *
         * @param $assoc_args
         * @param $args
         *
         * @return string
         */
        protected function get_value($assoc_args, $args)
        {
        }
        /**
         * Writes a provided variable's key and value to stdout, in dotenv format.
         *
         * @param array $value
         */
        private function print_dotenv(array $value)
        {
        }
        /**
         * Escape a config value so it can be safely used within single quotes.
         *
         * @param string $key   Key into the arguments array.
         * @param mixed  $value Value to escape.
         * @return mixed Escaped value.
         */
        private function escape_config_value($key, $value)
        {
        }
    }
    /**
     * Downloads, installs, updates, and manages a WordPress installation.
     *
     * ## EXAMPLES
     *
     *     # Download WordPress core
     *     $ wp core download --locale=nl_NL
     *     Downloading WordPress 4.5.2 (nl_NL)...
     *     md5 hash verified: c5366d05b521831dd0b29dfc386e56a5
     *     Success: WordPress downloaded.
     *
     *     # Install WordPress
     *     $ wp core install --url=example.com --title=Example --admin_user=supervisor --admin_password=strongpassword --admin_email=info@example.com
     *     Success: WordPress installed successfully.
     *
     *     # Display the WordPress version
     *     $ wp core version
     *     4.5.2
     *
     * @package wp-cli
     */
    class Core_Command extends \WP_CLI_Command
    {
        /**
         * Checks for WordPress updates via Version Check API.
         *
         * Lists the most recent versions when there are updates available,
         * or success message when up to date.
         *
         * ## OPTIONS
         *
         * [--minor]
         * : Compare only the first two parts of the version number.
         *
         * [--major]
         * : Compare only the first part of the version number.
         *
         * [--force-check]
         * : Bypass the transient cache and force a fresh update check.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each update.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields. Defaults to version,update_type,package_url.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - count
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     $ wp core check-update
         *     +---------+-------------+-------------------------------------------------------------+
         *     | version | update_type | package_url                                                 |
         *     +---------+-------------+-------------------------------------------------------------+
         *     | 4.5.2   | major       | https://downloads.wordpress.org/release/wordpress-4.5.2.zip |
         *     +---------+-------------+-------------------------------------------------------------+
         *
         * @subcommand check-update
         */
        public function check_update($_, $assoc_args)
        {
        }
        /**
         * Downloads core WordPress files.
         *
         * Downloads and extracts WordPress core files to the specified path. Uses
         * current directory when no path is specified. Downloaded build is verified
         * to have the correct md5 and then cached to the local filesystem.
         * Subsequent uses of command will use the local cache if it still exists.
         *
         * ## OPTIONS
         *
         * [<download-url>]
         * : Download directly from a provided URL instead of fetching the URL from the wordpress.org servers.
         *
         * [--path=<path>]
         * : Specify the path in which to install WordPress. Defaults to current
         * directory.
         *
         * [--locale=<locale>]
         * : Select which language you want to download.
         *
         * [--version=<version>]
         * : Select which version you want to download. Accepts a version number, 'latest' or 'nightly'.
         *
         * [--skip-content]
         * : Download WP without the default themes and plugins.
         *
         * [--force]
         * : Overwrites existing files, if present.
         *
         * [--insecure]
         * : Retry download without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * [--extract]
         * : Whether to extract the downloaded file. Defaults to true.
         *
         * ## EXAMPLES
         *
         *     $ wp core download --locale=nl_NL
         *     Downloading WordPress 4.5.2 (nl_NL)...
         *     md5 hash verified: c5366d05b521831dd0b29dfc386e56a5
         *     Success: WordPress downloaded.
         *
         * @when before_wp_load
         */
        public function download($args, $assoc_args)
        {
        }
        /**
         * Checks if WordPress is installed.
         *
         * Determines whether WordPress is installed by checking if the standard
         * database tables are installed. Doesn't produce output; uses exit codes
         * to communicate whether WordPress is installed.
         *
         * ## OPTIONS
         *
         * [--network]
         * : Check if this is a multisite installation.
         *
         * ## EXAMPLES
         *
         *     # Bash script for checking if WordPress is not installed.
         *
         *     if ! wp core is-installed 2>/dev/null; then
         *         # WP is not installed. Let's try installing it.
         *         wp core install
         *     fi
         *
         *     # Bash script for checking if WordPress is installed, with fallback.
         *
         *     if wp core is-installed 2>/dev/null; then
         *         # WP is installed. Let's do some things we should only do in a confirmed WP environment.
         *         wp core verify-checksums
         *     else
         *         # Fallback if WP is not installed.
         *         echo 'Hey Friend, you are in the wrong spot. Move in to your WordPress directory and try again.'
         *     fi
         *
         * @subcommand is-installed
         */
        public function is_installed($args, $assoc_args)
        {
        }
        /**
         * Runs the standard WordPress installation process.
         *
         * Creates the WordPress tables in the database using the URL, title, and
         * default admin user details provided. Performs the famous 5 minute install
         * in seconds or less.
         *
         * Note: if you've installed WordPress in a subdirectory, then you'll need
         * to `wp option update siteurl` after `wp core install`. For instance, if
         * WordPress is installed in the `/wp` directory and your domain is example.com,
         * then you'll need to run `wp option update siteurl http://example.com/wp` for
         * your WordPress installation to function properly.
         *
         * Note: When using custom user tables (e.g. `CUSTOM_USER_TABLE`), the admin
         * email and password are ignored if the user_login already exists. If the
         * user_login doesn't exist, a new user will be created.
         *
         * ## OPTIONS
         *
         * --url=<url>
         * : The address of the new site.
         *
         * --title=<site-title>
         * : The title of the new site.
         *
         * --admin_user=<username>
         * : The name of the admin user.
         *
         * [--admin_password=<password>]
         * : The password for the admin user. Defaults to randomly generated string.
         *
         * --admin_email=<email>
         * : The email address for the admin user.
         *
         * [--locale=<locale>]
         * : The locale/language for the installation (e.g. `de_DE`). Default is `en_US`.
         *
         * [--skip-email]
         * : Don't send an email notification to the new admin user.
         *
         * ## EXAMPLES
         *
         *     # Install WordPress in 5 seconds
         *     $ wp core install --url=example.com --title=Example --admin_user=supervisor --admin_password=strongpassword --admin_email=info@example.com
         *     Success: WordPress installed successfully.
         *
         *     # Install WordPress without disclosing admin_password to bash history
         *     $ wp core install --url=example.com --title=Example --admin_user=supervisor --admin_email=info@example.com --prompt=admin_password < admin_password.txt
         */
        public function install($args, $assoc_args)
        {
        }
        /**
         * Transforms an existing single-site installation into a multisite installation.
         *
         * Creates the multisite database tables, and adds the multisite constants
         * to wp-config.php.
         *
         * For those using WordPress with Apache, remember to update the `.htaccess`
         * file with the appropriate multisite rewrite rules.
         *
         * [Review the multisite documentation](https://wordpress.org/support/article/create-a-network/)
         * for more details about how multisite works.
         *
         * ## OPTIONS
         *
         * [--title=<network-title>]
         * : The title of the new network.
         *
         * [--base=<url-path>]
         * : Base path after the domain name that each site url will start with.
         * ---
         * default: /
         * ---
         *
         * [--subdomains]
         * : If passed, the network will use subdomains, instead of subdirectories. Doesn't work with 'localhost'.
         *
         * [--skip-config]
         * : Don't add multisite constants to wp-config.php.
         *
         * ## EXAMPLES
         *
         *     $ wp core multisite-convert
         *     Set up multisite database tables.
         *     Added multisite constants to wp-config.php.
         *     Success: Network installed. Don't forget to set up rewrite rules.
         *
         * @subcommand multisite-convert
         * @alias install-network
         */
        public function multisite_convert($args, $assoc_args)
        {
        }
        /**
         * Installs WordPress multisite from scratch.
         *
         * Creates the WordPress tables in the database using the URL, title, and
         * default admin user details provided. Then, creates the multisite tables
         * in the database and adds multisite constants to the wp-config.php.
         *
         * For those using WordPress with Apache, remember to update the `.htaccess`
         * file with the appropriate multisite rewrite rules.
         *
         * ## OPTIONS
         *
         * [--url=<url>]
         * : The address of the new site.
         *
         * [--base=<url-path>]
         * : Base path after the domain name that each site url in the network will start with.
         * ---
         * default: /
         * ---
         *
         * [--subdomains]
         * : If passed, the network will use subdomains, instead of subdirectories. Doesn't work with 'localhost'.
         *
         * --title=<site-title>
         * : The title of the new site.
         *
         * --admin_user=<username>
         * : The name of the admin user.
         * ---
         * default: admin
         * ---
         *
         * [--admin_password=<password>]
         * : The password for the admin user. Defaults to randomly generated string.
         *
         * --admin_email=<email>
         * : The email address for the admin user.
         *
         * [--skip-email]
         * : Don't send an email notification to the new admin user.
         *
         * [--skip-config]
         * : Don't add multisite constants to wp-config.php.
         *
         * ## EXAMPLES
         *
         *     $ wp core multisite-install --title="Welcome to the WordPress" \
         *     > --admin_user="admin" --admin_password="password" \
         *     > --admin_email="user@example.com"
         *     Single site database tables already present.
         *     Set up multisite database tables.
         *     Added multisite constants to wp-config.php.
         *     Success: Network installed. Don't forget to set up rewrite rules.
         *
         * @subcommand multisite-install
         */
        public function multisite_install($args, $assoc_args)
        {
        }
        private static function set_multisite_defaults($assoc_args)
        {
        }
        private function do_install($assoc_args)
        {
        }
        private function multisite_convert_($assoc_args)
        {
        }
        // copied from populate_network()
        private static function create_initial_blog($network_id, $blog_id, $domain, $path, $subdomain_install, $site_user)
        {
        }
        // copied from populate_network()
        private static function add_site_admins($site_user)
        {
        }
        private static function modify_wp_config($content)
        {
        }
        private static function get_clean_basedomain()
        {
        }
        /**
         * Displays the WordPress version.
         *
         * ## OPTIONS
         *
         * [--extra]
         * : Show extended version information.
         *
         * ## EXAMPLES
         *
         *     # Display the WordPress version
         *     $ wp core version
         *     4.5.2
         *
         *     # Display WordPress version along with other information
         *     $ wp core version --extra
         *     WordPress version: 4.5.2
         *     Database revision: 36686
         *     TinyMCE version:   4.310 (4310-20160418)
         *     Package language:  en_US
         *
         * @when before_wp_load
         */
        public function version($args = [], $assoc_args = [])
        {
        }
        /**
         * Gets version information from `wp-includes/version.php`.
         *
         * @return array {
         *     @type string $wp_version The WordPress version.
         *     @type int $wp_db_version The WordPress DB revision.
         *     @type string $tinymce_version The TinyMCE version.
         *     @type string $wp_local_package The TinyMCE version.
         * }
         */
        private static function get_wp_details($abspath = \ABSPATH)
        {
        }
        /**
         * Gets the template path based on installation type.
         */
        private static function get_template_path($template)
        {
        }
        /**
         * Searches for the value assigned to variable `$var_name` in PHP code `$code`.
         *
         * This is equivalent to matching the `\$VAR_NAME = ([^;]+)` regular expression and returning
         * the first match either as a `string` or as an `integer` (depending if it's surrounded by
         * quotes or not).
         *
         * @param string $var_name Variable name to search for.
         * @param string $code PHP code to search in.
         *
         * @return int|string|null
         */
        private static function find_var($var_name, $code)
        {
        }
        /**
         * Security copy of the core function with Requests - Gets the checksums for the given version of WordPress.
         *
         * @param string $version  Version string to query.
         * @param string $locale   Locale to query.
         * @param bool   $insecure Whether to retry without certificate validation on TLS handshake failure.
         * @return string|array String message on failure. An array of checksums on success.
         */
        private static function get_core_checksums($version, $locale, $insecure)
        {
        }
        /**
         * Updates WordPress to a newer version.
         *
         * Defaults to updating WordPress to the latest version.
         *
         * If you see "Error: Another update is currently in progress.", you may
         * need to run `wp option delete core_updater.lock` after verifying another
         * update isn't actually running.
         *
         * ## OPTIONS
         *
         * [<zip>]
         * : Path to zip file to use, instead of downloading from wordpress.org.
         *
         * [--minor]
         * : Only perform updates for minor releases (e.g. update from WP 4.3 to 4.3.3 instead of 4.4.2).
         *
         * [--version=<version>]
         * : Update to a specific version, instead of to the latest version. Alternatively accepts 'nightly'.
         *
         * [--force]
         * : Update even when installed WP version is greater than the requested version.
         *
         * [--locale=<locale>]
         * : Select which language you want to download.
         *
         * [--insecure]
         * : Retry download without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Update WordPress
         *     $ wp core update
         *     Updating to version 4.5.2 (en_US)...
         *     Downloading update from https://downloads.wordpress.org/release/wordpress-4.5.2-no-content.zip...
         *     Unpacking the update...
         *     Cleaning up files...
         *     No files found that need cleaning up
         *     Success: WordPress updated successfully.
         *
         *     # Update WordPress using zip file.
         *     $ wp core update ../latest.zip
         *     Starting update...
         *     Unpacking the update...
         *     Success: WordPress updated successfully.
         *
         *     # Update WordPress to 3.1 forcefully
         *     $ wp core update --version=3.1 --force
         *     Updating to version 3.1 (en_US)...
         *     Downloading update from https://wordpress.org/wordpress-3.1.zip...
         *     Unpacking the update...
         *     Warning: Checksums not available for WordPress 3.1/en_US. Please cleanup files manually.
         *     Success: WordPress updated successfully.
         *
         * @alias upgrade
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Runs the WordPress database update procedure.
         *
         * ## OPTIONS
         *
         * [--network]
         * : Update databases for all sites on a network
         *
         * [--dry-run]
         * : Compare database versions without performing the update.
         *
         * ## EXAMPLES
         *
         *     # Update the WordPress database.
         *     $ wp core update-db
         *     Success: WordPress database upgraded successfully from db version 36686 to 35700.
         *
         *     # Update databases for all sites on a network.
         *     $ wp core update-db --network
         *     WordPress database upgraded successfully from db version 35700 to 29630 on example.com/
         *     Success: WordPress database upgraded on 123/123 sites.
         *
         * @subcommand update-db
         */
        public function update_db($args, $assoc_args)
        {
        }
        /**
         * Gets download url based on version, locale and desired file type.
         *
         * @param $version
         * @param string $locale
         * @param string $file_type
         * @return string
         */
        private function get_download_url($version, $locale = 'en_US', $file_type = 'zip')
        {
        }
        /**
         * Returns update information.
         *
         * @param array $assoc_args Associative array of arguments.
         * @return array List of available updates , or an empty array if no updates are available.
         */
        private function get_updates($assoc_args)
        {
        }
        /**
         * Clean up extra files.
         *
         * @param string $version_from Starting version that the installation was updated from.
         * @param string $version_to   Target version that the installation is updated to.
         * @param string $locale       Locale of the installation.
         * @param bool   $insecure     Whether to retry without certificate validation on TLS handshake failure.
         */
        private function cleanup_extra_files($version_from, $version_to, $locale, $insecure)
        {
        }
        private static function strip_content_dir($zip_file)
        {
        }
    }
}
namespace WP_CLI\Core {
    /**
     * A Core Upgrader class that caches the download, and uses cached if available.
     *
     * @package wp-cli
     */
    class CoreUpgrader extends \Core_Upgrader
    {
        /**
         * Whether to retry without certificate validation on TLS handshake failure.
         *
         * @var bool
         */
        private $insecure;
        /**
         * CoreUpgrader constructor.
         *
         * @param \WP_Upgrader_Skin|null $skin
         * @param bool                   $insecure
         */
        public function __construct($skin = null, $insecure = false)
        {
        }
        /**
         * Caches the download, and uses cached if available.
         *
         * @access public
         *
         * @param string $package          The URI of the package. If this is the full path to an
         *                                 existing local file, it will be returned untouched.
         * @param bool   $check_signatures Whether to validate file signatures. Default false.
         * @param array  $hook_extra       Extra arguments to pass to the filter hooks. Default empty array.
         * @return string|WP_Error The full path to the downloaded package file, or a WP_Error object.
         */
        public function download_package($package, $check_signatures = false, $hook_extra = [])
        {
        }
        /**
         * Upgrade WordPress core.
         *
         * @access public
         *
         * @global WP_Filesystem_Base $wp_filesystem Subclass
         * @global callable           $_wp_filesystem_direct_method
         *
         * @param object $current Response object for whether WordPress is current.
         * @param array  $args {
         *        Optional. Arguments for upgrading WordPress core. Default empty array.
         *
         *        @type bool $pre_check_md5    Whether to check the file checksums before
         *                                     attempting the upgrade. Default true.
         *        @type bool $attempt_rollback Whether to attempt to rollback the chances if
         *                                     there is a problem. Default false.
         *        @type bool $do_rollback      Whether to perform this "upgrade" as a rollback.
         *                                     Default false.
         * }
         * @return null|false|WP_Error False or WP_Error on failure, null on success.
         */
        public function upgrade($current, $args = [])
        {
        }
        /**
         * Error handler to ignore failures on accessing SSL "https://api.wordpress.org/core/checksums/1.0/" in `get_core_checksums()` which seem to occur intermittently.
         */
        public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext = null)
        {
        }
    }
    /**
     * A Core Upgrader class that leaves packages intact by default.
     *
     * @package wp-cli
     */
    class NonDestructiveCoreUpgrader extends \WP_CLI\Core\CoreUpgrader
    {
        // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Provide default value.
        public function unpack_package($package, $delete_package = false)
        {
        }
    }
}
namespace {
    /**
     * Tests, runs, and deletes WP-Cron events; manages WP-Cron schedules.
     *
     * ## EXAMPLES
     *
     *     # Test WP Cron spawning system
     *     $ wp cron test
     *     Success: WP-Cron spawning is working as expected.
     */
    class Cron_Command extends \WP_CLI_Command
    {
        /**
         * Tests the WP Cron spawning system and reports back its status.
         *
         * This command tests the spawning system by performing the following steps:
         *
         * * Checks to see if the `DISABLE_WP_CRON` constant is set; errors if true
         * because WP-Cron is disabled.
         * * Checks to see if the `ALTERNATE_WP_CRON` constant is set; warns if true.
         * * Attempts to spawn WP-Cron over HTTP; warns if non 200 response code is
         * returned.
         *
         * ## EXAMPLES
         *
         *     # Cron test runs successfully.
         *     $ wp cron test
         *     Success: WP-Cron spawning is working as expected.
         */
        public function test()
        {
        }
        /**
         * Spawns a request to `wp-cron.php` and return the response.
         *
         * This function is designed to mimic the functionality in `spawn_cron()`
         * with the addition of returning the result of the `wp_remote_post()`
         * request.
         *
         * @return WP_Error|array The response or WP_Error on failure.
         */
        protected static function get_cron_spawn()
        {
        }
    }
    /**
     * Schedules, runs, and deletes WP-Cron events.
     *
     * ## EXAMPLES
     *
     *     # Schedule a new cron event
     *     $ wp cron event schedule cron_test
     *     Success: Scheduled event with hook 'cron_test' for 2016-05-31 10:19:16 GMT.
     *
     *     # Run all cron events due right now
     *     $ wp cron event run --due-now
     *     Executed the cron event 'cron_test_1' in 0.01s.
     *     Executed the cron event 'cron_test_2' in 0.006s.
     *     Success: Executed a total of 2 cron events.
     *
     *     # Delete all scheduled cron events for the given hook
     *     $ wp cron event delete cron_test
     *     Success: Deleted a total of 2 cron events.
     *
     *     # List scheduled cron events in JSON
     *     $ wp cron event list --fields=hook,next_run --format=json
     *     [{"hook":"wp_version_check","next_run":"2016-05-31 10:15:13"},{"hook":"wp_update_plugins","next_run":"2016-05-31 10:15:13"},{"hook":"wp_update_themes","next_run":"2016-05-31 10:15:14"}]
     *
     * @package wp-cli
     */
    class Cron_Event_Command extends \WP_CLI_Command
    {
        private $fields = array('hook', 'next_run_gmt', 'next_run_relative', 'recurrence');
        private static $time_format = 'Y-m-d H:i:s';
        /**
         * Lists scheduled cron events.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--<field>=<value>]
         * : Filter by one or more fields.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each event.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each cron event:
         * * hook
         * * next_run_gmt
         * * next_run_relative
         * * recurrence
         *
         * These fields are optionally available:
         * * time
         * * sig
         * * args
         * * schedule
         * * interval
         * * next_run
         *
         * ## EXAMPLES
         *
         *     # List scheduled cron events
         *     $ wp cron event list
         *     +-------------------+---------------------+---------------------+------------+
         *     | hook              | next_run_gmt        | next_run_relative   | recurrence |
         *     +-------------------+---------------------+---------------------+------------+
         *     | wp_version_check  | 2016-05-31 22:15:13 | 11 hours 57 minutes | 12 hours   |
         *     | wp_update_plugins | 2016-05-31 22:15:13 | 11 hours 57 minutes | 12 hours   |
         *     | wp_update_themes  | 2016-05-31 22:15:14 | 11 hours 57 minutes | 12 hours   |
         *     +-------------------+---------------------+---------------------+------------+
         *
         *     # List scheduled cron events in JSON
         *     $ wp cron event list --fields=hook,next_run --format=json
         *     [{"hook":"wp_version_check","next_run":"2016-05-31 10:15:13"},{"hook":"wp_update_plugins","next_run":"2016-05-31 10:15:13"},{"hook":"wp_update_themes","next_run":"2016-05-31 10:15:14"}]
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Schedules a new cron event.
         *
         * ## OPTIONS
         *
         * <hook>
         * : The hook name.
         *
         * [<next-run>]
         * : A Unix timestamp or an English textual datetime description compatible with `strtotime()`. Defaults to now.
         *
         * [<recurrence>]
         * : How often the event should recur. See `wp cron schedule list` for available schedule names. Defaults to no recurrence.
         *
         * [--<field>=<value>]
         * : Arguments to pass to the hook for the event. <field> should be a numeric key, not a string.
         *
         * ## EXAMPLES
         *
         *     # Schedule a new cron event
         *     $ wp cron event schedule cron_test
         *     Success: Scheduled event with hook 'cron_test' for 2016-05-31 10:19:16 GMT.
         *
         *     # Schedule new cron event with hourly recurrence
         *     $ wp cron event schedule cron_test now hourly
         *     Success: Scheduled event with hook 'cron_test' for 2016-05-31 10:20:32 GMT.
         *
         *     # Schedule new cron event and pass arguments
         *     $ wp cron event schedule cron_test '+1 hour' --0=first-argument --1=second-argument
         *     Success: Scheduled event with hook 'cron_test' for 2016-05-31 11:21:35 GMT.
         */
        public function schedule($args, $assoc_args)
        {
        }
        /**
         * Runs the next scheduled cron event for the given hook.
         *
         * ## OPTIONS
         *
         * [<hook>...]
         * : One or more hooks to run.
         *
         * [--due-now]
         * : Run all hooks due right now.
         *
         * [--exclude=<hooks>]
         * : Comma-separated list of hooks to exclude.
         *
         * [--all]
         * : Run all hooks.
         *
         * ## EXAMPLES
         *
         *     # Run all cron events due right now
         *     $ wp cron event run --due-now
         *     Executed the cron event 'cron_test_1' in 0.01s.
         *     Executed the cron event 'cron_test_2' in 0.006s.
         *     Success: Executed a total of 2 cron events.
         */
        public function run($args, $assoc_args)
        {
        }
        /**
         * Unschedules all cron events for a given hook.
         *
         * ## OPTIONS
         *
         * <hook>
         * : Name of the hook for which all events should be unscheduled.
         *
         * ## EXAMPLES
         *
         *     # Unschedule a cron event on given hook.
         *     $ wp cron event unschedule cron_test
         *     Success: Unscheduled 2 events for hook 'cron_test'.
         */
        public function unschedule($args, $assoc_args)
        {
        }
        /**
         * Deletes all scheduled cron events for the given hook.
         *
         * ## OPTIONS
         *
         * [<hook>...]
         * : One or more hooks to delete.
         *
         * [--due-now]
         * : Delete all hooks due right now.
         *
         * [--exclude=<hooks>]
         * : Comma-separated list of hooks to exclude.
         *
         * [--all]
         * : Delete all hooks.
         *
         * ## EXAMPLES
         *
         *     # Delete all scheduled cron events for the given hook
         *     $ wp cron event delete cron_test
         *     Success: Deleted a total of 2 cron events.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Executes an event immediately.
         *
         * @param stdClass $event The event
         * @return bool Whether the event was successfully executed or not.
         */
        protected static function run_event(\stdClass $event)
        {
        }
        /**
         * Deletes a cron event.
         *
         * @param stdClass $event The event
         * @return bool Whether the event was successfully deleted or not.
         */
        protected static function delete_event(\stdClass $event)
        {
        }
        /**
         * Callback function to format a cron event.
         *
         * @param stdClass $event The event.
         * @return stdClass The formatted event object.
         */
        protected static function format_event(\stdClass $event)
        {
        }
        /**
         * Fetches an array of scheduled cron events.
         *
         * @return array|WP_Error An array of event objects, or a WP_Error object if there are no events scheduled.
         */
        protected static function get_cron_events($is_due_now = \false)
        {
        }
        /**
         * Fetches an array of scheduled cron events selected by the user.
         *
         * @param array $args       A list of event names
         * @param array $assoc_args An associative list of CLI parameters
         *
         * @return array|WP_Error An array of objects, or a WP_Error object is there are no events scheduled.
         */
        protected static function get_selected_cron_events($args, $assoc_args)
        {
        }
        /**
         * Converts a time interval into human-readable format.
         *
         * Similar to WordPress' built-in `human_time_diff()` but returns two time period chunks instead of just one.
         *
         * @param int $since An interval of time in seconds
         * @return string The interval in human readable format
         */
        private static function interval($since)
        {
        }
        private function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Gets WP-Cron schedules.
     *
     * ## EXAMPLES
     *
     *     # List available cron schedules
     *     $ wp cron schedule list
     *     +------------+-------------+----------+
     *     | name       | display     | interval |
     *     +------------+-------------+----------+
     *     | hourly     | Once Hourly | 3600     |
     *     | twicedaily | Twice Daily | 43200    |
     *     | daily      | Once Daily  | 86400    |
     *     +------------+-------------+----------+
     */
    class Cron_Schedule_Command extends \WP_CLI_Command
    {
        private $fields = array('name', 'display', 'interval');
        /**
         * List available cron schedules.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each schedule.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each cron schedule:
         *
         * * name
         * * display
         * * interval
         *
         * There are no additional fields.
         *
         * ## EXAMPLES
         *
         *     # List available cron schedules
         *     $ wp cron schedule list
         *     +------------+-------------+----------+
         *     | name       | display     | interval |
         *     +------------+-------------+----------+
         *     | hourly     | Once Hourly | 3600     |
         *     | twicedaily | Twice Daily | 43200    |
         *     | daily      | Once Daily  | 86400    |
         *     +------------+-------------+----------+
         *
         *     # List id of available cron schedule
         *     $ wp cron schedule list --fields=name --format=ids
         *     hourly twicedaily daily
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Callback function to format a cron schedule.
         *
         * @param array $schedule The schedule.
         * @param string $name The schedule name.
         * @return array The formatted schedule.
         */
        protected static function format_schedule(array $schedule, $name)
        {
        }
        /**
         * Return a list of the cron schedules sorted according to interval.
         *
         * @return array The array of cron schedules. Each schedule is itself an array.
         */
        protected static function get_schedules()
        {
        }
        /**
         * Callback function to sort the cron schedule array by interval.
         *
         */
        protected static function sort(array $a, array $b)
        {
        }
        private function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Performs basic database operations using credentials stored in wp-config.php.
     *
     * ## EXAMPLES
     *
     *     # Create a new database.
     *     $ wp db create
     *     Success: Database created.
     *
     *     # Drop an existing database.
     *     $ wp db drop --yes
     *     Success: Database dropped.
     *
     *     # Reset the current database.
     *     $ wp db reset --yes
     *     Success: Database reset.
     *
     *     # Execute a SQL query stored in a file.
     *     $ wp db query < debug.sql
     *
     * @when after_wp_config_load
     */
    class DB_Command extends \WP_CLI_Command
    {
        /**
         * Legacy UTF-8 encoding for MySQL.
         *
         * @var string
         */
        const ENCODING_UTF8 = 'utf8';
        /**
         * Standards-compliant UTF-8 encoding for MySQL.
         *
         * @var string
         */
        const ENCODING_UTF8MB4 = 'utf8mb4';
        /**
         * A list of incompatible SQL modes.
         *
         * Copied over from WordPress Core code.
         * @see https://github.com/WordPress/wordpress-develop/blob/5.4.0/src/wp-includes/wp-db.php#L559-L572
         *
         * @var string[] Array of SQL mode names that are incompatible with WordPress.
         */
        protected $sql_incompatible_modes = ['NO_ZERO_DATE', 'ONLY_FULL_GROUP_BY', 'STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'TRADITIONAL', 'ANSI'];
        /**
         * Creates a new database.
         *
         * Runs `CREATE_DATABASE` SQL statement using `DB_HOST`, `DB_NAME`,
         * `DB_USER` and `DB_PASSWORD` database credentials specified in
         * wp-config.php.
         *
         * ## OPTIONS
         *
         * [--dbuser=<value>]
         * : Username to pass to mysql. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysql. Defaults to DB_PASSWORD.
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     $ wp db create
         *     Success: Database created.
         */
        public function create($_, $assoc_args)
        {
        }
        /**
         * Deletes the existing database.
         *
         * Runs `DROP_DATABASE` SQL statement using `DB_HOST`, `DB_NAME`,
         * `DB_USER` and `DB_PASSWORD` database credentials specified in
         * wp-config.php.
         *
         * ## OPTIONS
         *
         * [--dbuser=<value>]
         * : Username to pass to mysql. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysql. Defaults to DB_PASSWORD.
         *
         * [--yes]
         * : Answer yes to the confirmation message.
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     $ wp db drop --yes
         *     Success: Database dropped.
         */
        public function drop($_, $assoc_args)
        {
        }
        /**
         * Removes all tables from the database.
         *
         * Runs `DROP_DATABASE` and `CREATE_DATABASE` SQL statements using
         * `DB_HOST`, `DB_NAME`, `DB_USER` and `DB_PASSWORD` database credentials
         * specified in wp-config.php.
         *
         * ## OPTIONS
         *
         * [--dbuser=<value>]
         * : Username to pass to mysql. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysql. Defaults to DB_PASSWORD.
         *
         * [--yes]
         * : Answer yes to the confirmation message.
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     $ wp db reset --yes
         *     Success: Database reset.
         */
        public function reset($_, $assoc_args)
        {
        }
        /**
         * Removes all tables with `$table_prefix` from the database.
         *
         * Runs `DROP_TABLE` for each table that has a `$table_prefix` as specified
         * in wp-config.php.
         *
         * ## OPTIONS
         *
         * [--dbuser=<value>]
         * : Username to pass to mysql. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysql. Defaults to DB_PASSWORD.
         *
         * [--yes]
         * : Answer yes to the confirmation message.
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     # Delete all tables that match the current site prefix.
         *     $ wp db clean --yes
         *     Success: Tables dropped.
         *
         * @when after_wp_load
         */
        public function clean($_, $assoc_args)
        {
        }
        /**
         * Checks the current status of the database.
         *
         * Runs `mysqlcheck` utility with `--check` using `DB_HOST`,
         * `DB_NAME`, `DB_USER` and `DB_PASSWORD` database credentials
         * specified in wp-config.php.
         *
         * [See docs](http://dev.mysql.com/doc/refman/5.7/en/check-table.html)
         * for more details on the `CHECK TABLE` statement.
         *
         * This command does not check whether WordPress is installed;
         * to do that run `wp core is-installed`.
         *
         * ## OPTIONS
         *
         * [--dbuser=<value>]
         * : Username to pass to mysqlcheck. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysqlcheck. Defaults to DB_PASSWORD.
         *
         * [--<field>=<value>]
         * : Extra arguments to pass to mysqlcheck. [Refer to mysqlcheck docs](https://dev.mysql.com/doc/en/mysqlcheck.html).
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     $ wp db check
         *     Success: Database checked.
         */
        public function check($_, $assoc_args)
        {
        }
        /**
         * Optimizes the database.
         *
         * Runs `mysqlcheck` utility with `--optimize=true` using `DB_HOST`,
         * `DB_NAME`, `DB_USER` and `DB_PASSWORD` database credentials
         * specified in wp-config.php.
         *
         * [See docs](http://dev.mysql.com/doc/refman/5.7/en/optimize-table.html)
         * for more details on the `OPTIMIZE TABLE` statement.
         *
         * ## OPTIONS
         *
         * [--dbuser=<value>]
         * : Username to pass to mysqlcheck. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysqlcheck. Defaults to DB_PASSWORD.
         *
         * [--<field>=<value>]
         * : Extra arguments to pass to mysqlcheck. [Refer to mysqlcheck docs](https://dev.mysql.com/doc/en/mysqlcheck.html).
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     $ wp db optimize
         *     Success: Database optimized.
         */
        public function optimize($_, $assoc_args)
        {
        }
        /**
         * Repairs the database.
         *
         * Runs `mysqlcheck` utility with `--repair=true` using `DB_HOST`,
         * `DB_NAME`, `DB_USER` and `DB_PASSWORD` database credentials
         * specified in wp-config.php.
         *
         * [See docs](http://dev.mysql.com/doc/refman/5.7/en/repair-table.html) for
         * more details on the `REPAIR TABLE` statement.
         *
         * ## OPTIONS
         *
         * [--dbuser=<value>]
         * : Username to pass to mysqlcheck. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysqlcheck. Defaults to DB_PASSWORD.
         *
         * [--<field>=<value>]
         * : Extra arguments to pass to mysqlcheck. [Refer to mysqlcheck docs](https://dev.mysql.com/doc/en/mysqlcheck.html).
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     $ wp db repair
         *     Success: Database repaired.
         */
        public function repair($_, $assoc_args)
        {
        }
        /**
         * Opens a MySQL console using credentials from wp-config.php
         *
         * ## OPTIONS
         *
         * [--database=<database>]
         * : Use a specific database. Defaults to DB_NAME.
         *
         * [--default-character-set=<character-set>]
         * : Use a specific character set. Defaults to DB_CHARSET when defined.
         *
         * [--dbuser=<value>]
         * : Username to pass to mysql. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysql. Defaults to DB_PASSWORD.
         *
         * [--<field>=<value>]
         * : Extra arguments to pass to mysql. [Refer to mysql docs](https://dev.mysql.com/doc/en/mysql-command-options.html).
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     # Open MySQL console
         *     $ wp db cli
         *     mysql>
         *
         * @alias connect
         */
        public function cli($_, $assoc_args)
        {
        }
        /**
         * Executes a SQL query against the database.
         *
         * Executes an arbitrary SQL query using `DB_HOST`, `DB_NAME`, `DB_USER`
         *  and `DB_PASSWORD` database credentials specified in wp-config.php.
         *
         * Use the `--skip-column-names` MySQL argument to exclude the headers
         * from a SELECT query. Pipe the output to remove the ASCII table
         * entirely.
         *
         * ## OPTIONS
         *
         * [<sql>]
         * : A SQL query. If not passed, will try to read from STDIN.
         *
         * [--dbuser=<value>]
         * : Username to pass to mysql. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysql. Defaults to DB_PASSWORD.
         *
         * [--<field>=<value>]
         * : Extra arguments to pass to mysql. [Refer to mysql docs](https://dev.mysql.com/doc/en/mysql-command-options.html).
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     # Execute a query stored in a file
         *     $ wp db query < debug.sql
         *
         *     # Query for a specific value in the database (pipe the result to remove the ASCII table borders)
         *     $ wp db query 'SELECT option_value FROM wp_options WHERE option_name="home"' --skip-column-names
         *     +---------------------+
         *     | https://example.com |
         *     +---------------------+
         *
         *     # Check all tables in the database
         *     $ wp db query "CHECK TABLE $(wp db tables | paste -s -d, -);"
         *     +---------------------------------------+-------+----------+----------+
         *     | Table                                 | Op    | Msg_type | Msg_text |
         *     +---------------------------------------+-------+----------+----------+
         *     | wordpress_dbase.wp_users              | check | status   | OK       |
         *     | wordpress_dbase.wp_usermeta           | check | status   | OK       |
         *     | wordpress_dbase.wp_posts              | check | status   | OK       |
         *     | wordpress_dbase.wp_comments           | check | status   | OK       |
         *     | wordpress_dbase.wp_links              | check | status   | OK       |
         *     | wordpress_dbase.wp_options            | check | status   | OK       |
         *     | wordpress_dbase.wp_postmeta           | check | status   | OK       |
         *     | wordpress_dbase.wp_terms              | check | status   | OK       |
         *     | wordpress_dbase.wp_term_taxonomy      | check | status   | OK       |
         *     | wordpress_dbase.wp_term_relationships | check | status   | OK       |
         *     | wordpress_dbase.wp_termmeta           | check | status   | OK       |
         *     | wordpress_dbase.wp_commentmeta        | check | status   | OK       |
         *     +---------------------------------------+-------+----------+----------+
         *
         *     # Pass extra arguments through to MySQL
         *     $ wp db query 'SELECT * FROM wp_options WHERE option_name="home"' --skip-column-names
         *     +---+------+------------------------------+-----+
         *     | 2 | home | http://wordpress-develop.dev | yes |
         *     +---+------+------------------------------+-----+
         *
         * ## MULTISITE USAGE
         *
         * Please note that the global `--url` parameter will have no effect on this command.
         * In order to query for data in a site other than your primary site,
         * you will need to manually modify the table names to use the prefix that includes the site's ID.
         *
         * For example, to get the `home` option for your second site, modify the example above like so:
         *
         *     $ wp db query 'SELECT option_value FROM wp_2_options WHERE option_name="home"' --skip-column-names
         *     +----------------------+
         *     | https://example2.com |
         *     +----------------------+
         *
         * To confirm the ID for the site you want to query, you can use the `wp site list` command:
         *
         *     # wp site list --fields=blog_id,url
         *     +---------+-----------------------+
         *     | blog_id | url                   |
         *     +---------+-----------------------+
         *     | 1       | https://example1.com/ |
         *     | 2       | https://example2.com/ |
         *     +---------+-----------------------+
         */
        public function query($args, $assoc_args)
        {
        }
        /**
         * Exports the database to a file or to STDOUT.
         *
         * Runs `mysqldump` utility using `DB_HOST`, `DB_NAME`, `DB_USER` and
         * `DB_PASSWORD` database credentials specified in wp-config.php. Accepts any valid `mysqldump` flags.
         *
         * ## OPTIONS
         *
         * [<file>]
         * : The name of the SQL file to export. If '-', then outputs to STDOUT. If
         * omitted, it will be '{dbname}-{Y-m-d}-{random-hash}.sql'.
         *
         * [--dbuser=<value>]
         * : Username to pass to mysqldump. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysqldump. Defaults to DB_PASSWORD.
         *
         * [--<field>=<value>]
         * : Extra arguments to pass to mysqldump. [Refer to mysqldump docs](https://dev.mysql.com/doc/en/mysqldump.html#mysqldump-option-summary).
         *
         * [--tables=<tables>]
         * : The comma separated list of specific tables to export. Excluding this parameter will export all tables in the database.
         *
         * [--exclude_tables=<tables>]
         * : The comma separated list of specific tables that should be skipped from exporting. Excluding this parameter will export all tables in the database.
         *
         * [--include-tablespaces]
         * : Skips adding the default --no-tablespaces option to mysqldump.
         *
         * [--porcelain]
         * : Output filename for the exported database.
         *
         * [--add-drop-table]
         * : Include a `DROP TABLE IF EXISTS` statement before each `CREATE TABLE` statement.
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     # Export database with drop query included
         *     $ wp db export --add-drop-table
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Export certain tables
         *     $ wp db export --tables=wp_options,wp_users
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Export all tables matching a wildcard
         *     $ wp db export --tables=$(wp db tables 'wp_user*' --format=csv)
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Export all tables matching prefix
         *     $ wp db export --tables=$(wp db tables --all-tables-with-prefix --format=csv)
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Export certain posts without create table statements
         *     $ wp db export --no-create-info=true --tables=wp_posts --where="ID in (100,101,102)"
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Export relating meta for certain posts without create table statements
         *     $ wp db export --no-create-info=true --tables=wp_postmeta --where="post_id in (100,101,102)"
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Skip certain tables from the exported database
         *     $ wp db export --exclude_tables=wp_options,wp_users
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Skip all tables matching a wildcard from the exported database
         *     $ wp db export --exclude_tables=$(wp db tables 'wp_user*' --format=csv)
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Skip all tables matching prefix from the exported database
         *     $ wp db export --exclude_tables=$(wp db tables --all-tables-with-prefix --format=csv)
         *     Success: Exported to 'wordpress_dbase-db72bb5.sql'.
         *
         *     # Export database to STDOUT.
         *     $ wp db export -
         *     -- MySQL dump 10.13  Distrib 5.7.19, for osx10.12 (x86_64)
         *     --
         *     -- Host: localhost    Database: wpdev
         *     -- ------------------------------------------------------
         *     -- Server version    5.7.19
         *     ...
         *
         * @alias dump
         */
        public function export($args, $assoc_args)
        {
        }
        /**
         * Get the current character set of the posts table.
         *
         * @param array Associative array of associative arguments.
         * @return string Posts table character set.
         */
        private function get_posts_table_charset($assoc_args)
        {
        }
        /**
         * Imports a database from a file or from STDIN.
         *
         * Runs SQL queries using `DB_HOST`, `DB_NAME`, `DB_USER` and
         * `DB_PASSWORD` database credentials specified in wp-config.php. This
         * does not create database by itself and only performs whatever tasks are
         * defined in the SQL.
         *
         * ## OPTIONS
         *
         * [<file>]
         * : The name of the SQL file to import. If '-', then reads from STDIN. If omitted, it will look for '{dbname}.sql'.
         *
         * [--dbuser=<value>]
         * : Username to pass to mysql. Defaults to DB_USER.
         *
         * [--dbpass=<value>]
         * : Password to pass to mysql. Defaults to DB_PASSWORD.
         *
         * [--<field>=<value>]
         * : Extra arguments to pass to mysql. [Refer to mysql binary docs](https://dev.mysql.com/doc/refman/8.0/en/mysql-command-options.html).
         *
         * [--skip-optimization]
         * : When using an SQL file, do not include speed optimization such as disabling auto-commit and key checks.
         *
         * [--defaults]
         * : Loads the environment's MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
         *
         * ## EXAMPLES
         *
         *     # Import MySQL from a file.
         *     $ wp db import wordpress_dbase.sql
         *     Success: Imported from 'wordpress_dbase.sql'.
         */
        public function import($args, $assoc_args)
        {
        }
        /**
         * Lists the database tables.
         *
         * Defaults to all tables registered to the $wpdb database handler.
         *
         * ## OPTIONS
         *
         * [<table>...]
         * : List tables based on wildcard search, e.g. 'wp_*_options' or 'wp_post?'.
         *
         * [--scope=<scope>]
         * : Can be all, global, ms_global, blog, or old tables. Defaults to all.
         *
         * [--network]
         * : List all the tables in a multisite install.
         *
         * [--all-tables-with-prefix]
         * : List all tables that match the table prefix even if not registered on $wpdb. Overrides --network.
         *
         * [--all-tables]
         * : List all tables in the database, regardless of the prefix, and even if not registered on $wpdb. Overrides --all-tables-with-prefix.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: list
         * options:
         *   - list
         *   - csv
         * ---
         *
         * ## EXAMPLES
         *
         *     # List tables for a single site, without shared tables like 'wp_users'
         *     $ wp db tables --scope=blog --url=sub.example.com
         *     wp_3_posts
         *     wp_3_comments
         *     wp_3_options
         *     wp_3_postmeta
         *     wp_3_terms
         *     wp_3_term_taxonomy
         *     wp_3_term_relationships
         *     wp_3_termmeta
         *     wp_3_commentmeta
         *
         *     # Export only tables for a single site
         *     $ wp db export --tables=$(wp db tables --url=sub.example.com --format=csv)
         *     Success: Exported to wordpress_dbase.sql
         *
         * @when after_wp_load
         */
        public function tables($args, $assoc_args)
        {
        }
        /**
         * Displays the database name and size.
         *
         * Display the database name and size for `DB_NAME` specified in wp-config.php.
         * The size defaults to a human-readable number.
         *
         * Available size formats include:
         * * b (bytes)
         * * kb (kilobytes)
         * * mb (megabytes)
         * * gb (gigabytes)
         * * tb (terabytes)
         * * B   (ISO Byte setting, with no conversion)
         * * KB  (ISO Kilobyte setting, with 1 KB  = 1,000 B)
         * * KiB (ISO Kibibyte setting, with 1 KiB = 1,024 B)
         * * MB  (ISO Megabyte setting, with 1 MB  = 1,000 KB)
         * * MiB (ISO Mebibyte setting, with 1 MiB = 1,024 KiB)
         * * GB  (ISO Gigabyte setting, with 1 GB  = 1,000 MB)
         * * GiB (ISO Gibibyte setting, with 1 GiB = 1,024 MiB)
         * * TB  (ISO Terabyte setting, with 1 TB  = 1,000 GB)
         * * TiB (ISO Tebibyte setting, with 1 TiB = 1,024 GiB)
         *
         * ## OPTIONS
         *
         * [--size_format=<format>]
         * : Display the database size only, as a bare number.
         * ---
         * options:
         *   - b
         *   - kb
         *   - mb
         *   - gb
         *   - tb
         *   - B
         *   - KB
         *   - KiB
         *   - MB
         *   - MiB
         *   - GB
         *   - GiB
         *   - TB
         *   - TiB
         * ---
         *
         * [--tables]
         * : Display each table name and size instead of the database size.
         *
         * [--human-readable]
         * : Display database sizes in human readable formats.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * [--scope=<scope>]
         * : Can be all, global, ms_global, blog, or old tables. Defaults to all.
         *
         * [--network]
         * : List all the tables in a multisite install.
         *
         * [--decimals=<decimals>]
         * : Number of digits after decimal point. Defaults to 0.
         *
         * [--all-tables-with-prefix]
         * : List all tables that match the table prefix even if not registered on $wpdb. Overrides --network.
         *
         * [--all-tables]
         * : List all tables in the database, regardless of the prefix, and even if not registered on $wpdb. Overrides --all-tables-with-prefix.
         *
         * [--order=<order>]
         * : Ascending or Descending order.
         * ---
         * default: asc
         * options:
         *   - asc
         *   - desc
         * ---
         *
         * [--orderby=<orderby>]
         * : Order by fields.
         * ---
         * default: name
         * options:
         *   - name
         *   - size
         * ---
         *
         * ## EXAMPLES
         *
         *     $ wp db size
         *     +-------------------+------+
         *     | Name              | Size |
         *     +-------------------+------+
         *     | wordpress_default | 6 MB |
         *     +-------------------+------+
         *
         *     $ wp db size --tables
         *     +-----------------------+-------+
         *     | Name                  | Size  |
         *     +-----------------------+-------+
         *     | wp_users              | 64 KB |
         *     | wp_usermeta           | 48 KB |
         *     | wp_posts              | 80 KB |
         *     | wp_comments           | 96 KB |
         *     | wp_links              | 32 KB |
         *     | wp_options            | 32 KB |
         *     | wp_postmeta           | 48 KB |
         *     | wp_terms              | 48 KB |
         *     | wp_term_taxonomy      | 48 KB |
         *     | wp_term_relationships | 32 KB |
         *     | wp_termmeta           | 48 KB |
         *     | wp_commentmeta        | 48 KB |
         *     +-----------------------+-------+
         *
         *     $ wp db size --size_format=b
         *     5865472
         *
         *     $ wp db size --size_format=kb
         *     5728
         *
         *     $ wp db size --size_format=mb
         *     6
         *
         * @when after_wp_load
         */
        public function size($args, $assoc_args)
        {
        }
        /**
         * Displays the database table prefix.
         *
         * Display the database table prefix, as defined by the database handler's interpretation of the current site.
         *
         * ## EXAMPLES
         *
         *     $ wp db prefix
         *     wp_
         *
         * @when after_wp_load
         */
        public function prefix()
        {
        }
        /**
         * Finds a string in the database.
         *
         * Searches through all of the text columns in a selection of database tables for a given string, Outputs colorized references to the string.
         *
         * Defaults to searching through all tables registered to $wpdb. On multisite, this default is limited to the tables for the current site.
         *
         * ## OPTIONS
         *
         * <search>
         * : String to search for. The search is case-insensitive by default.
         *
         * [<tables>...]
         * : One or more tables to search through for the string.
         *
         * [--network]
         * : Search through all the tables registered to $wpdb in a multisite install.
         *
         * [--all-tables-with-prefix]
         * : Search through all tables that match the registered table prefix, even if not registered on $wpdb. On one hand, sometimes plugins use tables without registering them to $wpdb. On another hand, this could return tables you don't expect. Overrides --network.
         *
         * [--all-tables]
         * : Search through ALL tables in the database, regardless of the prefix, and even if not registered on $wpdb. Overrides --network and --all-tables-with-prefix.
         *
         * [--before_context=<num>]
         * : Number of characters to display before the match.
         * ---
         * default: 40
         * ---
         *
         * [--after_context=<num>]
         * : Number of characters to display after the match.
         * ---
         * default: 40
         * ---
         *
         * [--regex]
         * : Runs the search as a regular expression (without delimiters). The search becomes case-sensitive (i.e. no PCRE flags are added). Delimiters must be escaped if they occur in the expression. Because the search is run on individual columns, you can use the `^` and `$` tokens to mark the start and end of a match, respectively.
         *
         * [--regex-flags=<regex-flags>]
         * : Pass PCRE modifiers to the regex search (e.g. 'i' for case-insensitivity).
         *
         * [--regex-delimiter=<regex-delimiter>]
         * : The delimiter to use for the regex. It must be escaped if it appears in the search string. The default value is the result of `chr(1)`.
         *
         * [--table_column_once]
         * : Output the 'table:column' line once before all matching row lines in the table column rather than before each matching row.
         *
         * [--one_line]
         * : Place the 'table:column' output on the same line as the row id and match ('table:column:id:match'). Overrides --table_column_once.
         *
         * [--matches_only]
         * : Only output the string matches (including context). No 'table:column's or row ids are outputted.
         *
         * [--stats]
         * : Output stats on the number of matches found, time taken, tables/columns/rows searched, tables skipped.
         *
         * [--table_column_color=<color_code>]
         * : Percent color code to use for the 'table:column' output. For a list of available percent color codes, see below. Default '%G' (bright green).
         *
         * [--id_color=<color_code>]
         * : Percent color code to use for the row id output. For a list of available percent color codes, see below. Default '%Y' (bright yellow).
         *
         * [--match_color=<color_code>]
         * : Percent color code to use for the match (unless both before and after context are 0, when no color code is used). For a list of available percent color codes, see below. Default '%3%k' (black on a mustard background).
         *
         * [--fields=<fields>]
         * : Get a specific subset of the fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         *   - ids
         *   - count
         * ---
         *
         * The percent color codes available are:
         *
         * | Code | Color
         * | ---- | -----
         * |  %y  | Yellow (dark) (mustard)
         * |  %g  | Green (dark)
         * |  %b  | Blue (dark)
         * |  %r  | Red (dark)
         * |  %m  | Magenta (dark)
         * |  %c  | Cyan (dark)
         * |  %w  | White (dark) (light gray)
         * |  %k  | Black
         * |  %Y  | Yellow (bright)
         * |  %G  | Green (bright)
         * |  %B  | Blue (bright)
         * |  %R  | Red (bright)
         * |  %M  | Magenta (bright)
         * |  %C  | Cyan (bright)
         * |  %W  | White
         * |  %K  | Black (bright) (dark gray)
         * |  %3  | Yellow background (dark) (mustard)
         * |  %2  | Green background (dark)
         * |  %4  | Blue background (dark)
         * |  %1  | Red background (dark)
         * |  %5  | Magenta background (dark)
         * |  %6  | Cyan background (dark)
         * |  %7  | White background (dark) (light gray)
         * |  %0  | Black background
         * |  %8  | Reverse
         * |  %U  | Underline
         * |  %F  | Blink (unlikely to work)
         *
         * They can be concatenated. For instance, the default match color of black on a mustard (dark yellow) background `%3%k` can be made black on a bright yellow background with `%Y%0%8`.
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each result:
         *
         * * table
         * * column
         * * match
         * * primary_key_name
         * * primary_key_value
         *
         * ## EXAMPLES
         *
         *     # Search through the database for the 'wordpress-develop' string
         *     $ wp db search wordpress-develop
         *     wp_options:option_value
         *     1:http://wordpress-develop.dev
         *     wp_options:option_value
         *     1:https://example.com/foo
         *         ...
         *
         *     # Search through a multisite database on the subsite 'foo' for the 'example.com' string
         *     $ wp db search example.com --url=example.com/foo
         *     wp_2_comments:comment_author_url
         *     1:https://example.com/
         *     wp_2_options:option_value
         *         ...
         *
         *     # Search through the database for the 'https?://' regular expression, printing stats.
         *     $ wp db search 'https?://' --regex --stats
         *     wp_comments:comment_author_url
         *     1:https://wordpress.org/
         *         ...
         *     Success: Found 99146 matches in 10.752s (10.559s searching). Searched 12 tables, 53 columns, 1358907 rows. 1 table skipped: wp_term_relationships.
         *
         *     # SQL search database table 'wp_options' where 'option_name' match 'foo'
         *     wp db query 'SELECT * FROM wp_options WHERE option_name like "%foo%"' --skip-column-names
         *     +----+--------------+--------------------------------+-----+
         *     | 98 | foo_options  | a:1:{s:12:"_multiwidget";i:1;} | yes |
         *     | 99 | foo_settings | a:0:{}                         | yes |
         *     +----+--------------+--------------------------------+-----+
         *
         *     # SQL search and delete records from database table 'wp_options' where 'option_name' match 'foo'
         *     wp db query "DELETE from wp_options where option_id in ($(wp db query "SELECT GROUP_CONCAT(option_id SEPARATOR ',') from wp_options where option_name like '%foo%';" --silent --skip-column-names))"
         *
         *     # Search for a string and print the result as a table
         *     $ wp db search https://localhost:8889 --format=table --fields=table,column,match
         *     +------------+--------------+-----------------------------+
         *     | table      | column       | match                       |
         *     +------------+--------------+-----------------------------+
         *     | wp_options | option_value | https://localhost:8889      |
         *     | wp_options | option_value | https://localhost:8889      |
         *     | wp_posts   | guid         | https://localhost:8889/?p=1 |
         *     | wp_users   | user_url     | https://localhost:8889      |
         *     +------------+--------------+-----------------------------+
         *
         *     # Search for a string and get only the IDs (only works for a single table)
         *     $ wp db search https://localhost:8889 wp_options --format=ids
         *     1 2
         *
         * @when after_wp_load
         */
        public function search($args, $assoc_args)
        {
        }
        /**
         * Displays information about a given table.
         *
         * ## OPTIONS
         *
         * <table>
         * : Name of the database table.
         *
         * [--format]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     $ wp db columns wp_posts
         *     +-----------------------+---------------------+------+-----+---------------------+----------------+
         *     |         Field         |        Type         | Null | Key |       Default       |     Extra      |
         *     +-----------------------+---------------------+------+-----+---------------------+----------------+
         *     | ID                    | bigint(20) unsigned | NO   | PRI |                     | auto_increment |
         *     | post_author           | bigint(20) unsigned | NO   | MUL | 0                   |                |
         *     | post_date             | datetime            | NO   |     | 0000-00-00 00:00:00 |                |
         *     | post_date_gmt         | datetime            | NO   |     | 0000-00-00 00:00:00 |                |
         *     | post_content          | longtext            | NO   |     |                     |                |
         *     | post_title            | text                | NO   |     |                     |                |
         *     | post_excerpt          | text                | NO   |     |                     |                |
         *     | post_status           | varchar(20)         | NO   |     | publish             |                |
         *     | comment_status        | varchar(20)         | NO   |     | open                |                |
         *     | ping_status           | varchar(20)         | NO   |     | open                |                |
         *     | post_password         | varchar(255)        | NO   |     |                     |                |
         *     | post_name             | varchar(200)        | NO   | MUL |                     |                |
         *     | to_ping               | text                | NO   |     |                     |                |
         *     | pinged                | text                | NO   |     |                     |                |
         *     | post_modified         | datetime            | NO   |     | 0000-00-00 00:00:00 |                |
         *     | post_modified_gmt     | datetime            | NO   |     | 0000-00-00 00:00:00 |                |
         *     | post_content_filtered | longtext            | NO   |     |                     |                |
         *     | post_parent           | bigint(20) unsigned | NO   | MUL | 0                   |                |
         *     | guid                  | varchar(255)        | NO   |     |                     |                |
         *     | menu_order            | int(11)             | NO   |     | 0                   |                |
         *     | post_type             | varchar(20)         | NO   | MUL | post                |                |
         *     | post_mime_type        | varchar(100)        | NO   |     |                     |                |
         *     | comment_count         | bigint(20)          | NO   |     | 0                   |                |
         *     +-----------------------+---------------------+------+-----+---------------------+----------------+
         *
         * @when after_wp_load
         */
        public function columns($args, $assoc_args)
        {
        }
        private static function get_create_query()
        {
        }
        /**
         * Run a single query via the 'mysql' binary.
         *
         * This includes the necessary setup to make sure the queries behave similar
         * to what WPDB produces.
         *
         * @param string $query      Query to execute.
         * @param array  $assoc_args Optional. Associative array of arguments.
         */
        protected function run_query($query, $assoc_args = [])
        {
        }
        /**
         * Run a MySQL command and optionally return the output.
         *
         * @param string $cmd           Command to run.
         * @param array  $assoc_args    Optional. Associative array of arguments to
         *                              use.
         * @param bool   $send_to_shell Optional. Whether to send STDOUT and STDERR
         *                              immediately to the shell. Defaults to true.
         * @param bool   $interactive   Optional. Whether MySQL is meant to be
         *                              executed as an interactive process. Defaults
         *                              to false.
         *
         * @return array {
         *     Associative array containing STDOUT and STDERR output.
         *
         *     @type string $stdout    Output that was sent to STDOUT.
         *     @type string $stderr    Output that was sent to STDERR.
         *     @type int    $exit_code Exit code of the process.
         * }
         */
        private static function run($cmd, $assoc_args = [], $send_to_shell = \true, $interactive = \false)
        {
        }
        /**
         * Helper to pluck 'dbuser' and 'dbpass' from associative args array.
         *
         * @param array $assoc_args Associative args array.
         * @return array Array with `dbuser' and 'dbpass' set if in passed-in associative args array.
         */
        private static function get_dbuser_dbpass_args($assoc_args)
        {
        }
        /**
         * Gets the column names of a db table differentiated into key columns and text columns and all columns.
         *
         * @param string $table The table name.
         * @return array A 3 element array consisting of an array of primary key column names, an array of text column names, and an array containing all column names.
         */
        private static function get_columns($table)
        {
        }
        /**
         * Determines whether a column is considered text or not.
         *
         * @param string Column type.
         * @return bool True if text column, false otherwise.
         */
        private static function is_text_col($type)
        {
        }
        /**
         * Escapes (backticks) MySQL identifiers (aka schema object names) - i.e. column names, table names, and database/index/alias/view etc names.
         * See https://dev.mysql.com/doc/refman/5.5/en/identifiers.html
         *
         * @param string|array $idents A single identifier or an array of identifiers.
         * @return string|array An escaped string if given a string, or an array of escaped strings if given an array of strings.
         */
        private static function esc_sql_ident($idents)
        {
        }
        /**
         * Gets the color codes from the options if any, and returns the passed in array colorized with 2 elements per entry, a color code (or '') and a reset (or '').
         *
         * @param array $assoc_args The associative argument array passed to the command.
         * @param array $colors Array of default percent color code strings keyed by the 3 color contexts 'table_column', 'id', 'match'.
         * @return array Array containing 3 2-element arrays.
         */
        private function get_colors($assoc_args, $colors)
        {
        }
        /**
         * Helper to pluck `mysql` options from associative args array.
         *
         * @param array $assoc_args Associative args array.
         * @return array Array with `mysql` options set if in passed-in associative args array.
         */
        private static function get_mysql_args($assoc_args)
        {
        }
        /**
         * Writes out the `--no-defaults` flag for MySQL commands unless the --defaults flag is specified for the WP_CLI command.
         *
         * @param array $assoc_args Associative args array.
         * @return string Either the '--no-defaults' flag for use in the command or an empty string.
         */
        protected function get_defaults_flag_string(&$assoc_args)
        {
        }
        /**
         * Get the query to change the current SQL mode, and ensure its WordPress compatibility.
         *
         * If no modes are passed, it will ensure the current MySQL server modes are
         * compatible.
         *
         * Copied and adapted from WordPress Core code.
         *
         * @see https://github.com/WordPress/wordpress-develop/blob/5.4.0/src/wp-includes/wp-db.php#L817-L880
         *
         * @param array $assoc_args The associative argument array passed to the command.
         * @param array $modes      Optional. A list of SQL modes to set.
         * @return string Query string to use for setting the SQL modes to a
         *                compatible state.
         */
        protected function get_sql_mode_query($assoc_args, $modes = [])
        {
        }
        /**
         * Get the list of current SQL modes.
         *
         * @param array $assoc_args The associative argument array passed to the command.
         * @return string[] Array of SQL modes.
         */
        protected function get_current_sql_modes($assoc_args)
        {
        }
        /**
         * Returns the correct `mysql` command based on the detected database type.
         *
         * @return string The appropriate check command.
         */
        private function get_mysql_command()
        {
        }
    }
}
namespace WP_CLI\Embeds {
    /**
     * Finds, triggers, and deletes oEmbed caches.
     *
     * ## EXAMPLES
     *
     *     # Find cache post ID for a given URL.
     *     $ wp embed cache find https://www.youtube.com/watch?v=dQw4w9WgXcQ --width=500
     *     123
     *
     *     # Clear cache for a post.
     *     $ wp embed cache clear 123
     *     Success: Cleared oEmbed cache.
     *
     *     # Triggers cache for a post.
     *     $ wp embed cache trigger 456
     *     Success: Caching triggered!
     *
     * @package wp-cli
     */
    class Cache_Command extends \WP_CLI_Command
    {
        /**
         * Deletes all oEmbed caches for a given post.
         *
         * oEmbed caches for a post are stored in the post's metadata.
         *
         * ## OPTIONS
         *
         * <post_id>
         * : ID of the post to clear the cache for.
         *
         * ## EXAMPLES
         *
         *     # Clear cache for a post
         *     $ wp embed cache clear 123
         *     Success: Cleared oEmbed cache.
         */
        public function clear($args, $assoc_args)
        {
        }
        /**
         * Finds an oEmbed cache post ID for a given URL.
         *
         * Starting with WordPress 4.9, embeds that aren't associated with a specific post will be cached in
         * a new oembed_cache post type. There can be more than one such entry for a url depending on attributes and context.
         *
         * Not to be confused with oEmbed caches for a given post which are stored in the post's metadata.
         *
         * ## OPTIONS
         *
         * <url>
         * : URL to retrieve oEmbed data for.
         *
         * [--width=<width>]
         * : Width of the embed in pixels. Part of cache key so must match. Defaults to `content_width` if set else 500px, so is theme and context dependent.
         *
         * [--height=<height>]
         * : Height of the embed in pixels. Part of cache key so must match. Defaults to 1.5 * default width (`content_width` or 500px), to a maximum of 1000px.
         *
         * [--discover]
         * : Whether to search with the discover attribute set or not. Part of cache key so must match. If not given, will search with attribute: unset, '1', '0', returning first.
         *
         * ## EXAMPLES
         *
         *     # Find cache post ID for a given URL.
         *     $ wp embed cache find https://www.youtube.com/watch?v=dQw4w9WgXcQ --width=500
         *     123
         */
        public function find($args, $assoc_args)
        {
        }
        /**
         * Triggers the caching of all oEmbed results for a given post.
         *
         * oEmbed caches for a post are stored in the post's metadata.
         *
         * ## OPTIONS
         *
         * <post_id>
         * : ID of the post to do the caching for.
         *
         * ## EXAMPLES
         *
         *     # Triggers cache for a post
         *     $ wp embed cache trigger 456
         *     Success: Caching triggered!
         */
        public function trigger($args, $assoc_args)
        {
        }
    }
    /**
     * Inspects oEmbed providers, clears embed cache, and more.
     *
     * ## EXAMPLES
     *
     *     # Get embed HTML for a given URL.
     *     $ wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ
     *     <iframe width="525" height="295" src="https://www.youtube.com/embed/dQw4w9WgXcQ?feature=oembed" ...
     *
     *     # Find cache post ID for a given URL.
     *     $ wp embed cache find https://www.youtube.com/watch?v=dQw4w9WgXcQ --width=500
     *     123
     *
     *     # List format,endpoint fields of available providers.
     *     $ wp embed provider list
     *     +------------------------------+-----------------------------------------+
     *     | format                       | endpoint                                |
     *     +------------------------------+-----------------------------------------+
     *     | #https?://youtu\.be/.*#i     | https://www.youtube.com/oembed          |
     *     | #https?://flic\.kr/.*#i      | https://www.flickr.com/services/oembed/ |
     *     | #https?://wordpress\.tv/.*#i | https://wordpress.tv/oembed/            |
     *
     *     # List id,regex,priority fields of available handlers.
     *     $ wp embed handler list --fields=priority,id
     *     +----------+-------------------+
     *     | priority | id                |
     *     +----------+-------------------+
     *     | 10       | youtube_embed_url |
     *     | 9999     | audio             |
     *     | 9999     | video             |
     *     +----------+-------------------+
     *
     * @package wp-cli
     */
    class Embeds_Namespace extends \WP_CLI\Dispatcher\CommandNamespace
    {
    }
    class Fetch_Command extends \WP_CLI_Command
    {
        /**
         * Attempts to convert a URL into embed HTML.
         *
         * In non-raw mode, starts by checking the URL against the regex of the registered embed handlers.
         * If none of the regex matches and it's enabled, then the URL will be given to the WP_oEmbed class.
         *
         * In raw mode, checks the providers directly and returns the data.
         *
         * ## OPTIONS
         *
         * <url>
         * : URL to retrieve oEmbed data for.
         *
         * [--width=<width>]
         * : Width of the embed in pixels.
         *
         * [--height=<height>]
         * : Height of the embed in pixels.
         *
         * [--post-id=<id>]
         * : Cache oEmbed response for a given post.
         *
         * [--discover]
         * : Enable oEmbed discovery. Defaults to true.
         *
         * [--skip-cache]
         * : Ignore already cached oEmbed responses. Has no effect if using the 'raw' option, which doesn't use the cache.
         *
         * [--skip-sanitization]
         * : Remove the filter that WordPress from 4.4 onwards uses to sanitize oEmbed responses. Has no effect if using the 'raw' option, which by-passes sanitization.
         *
         * [--do-shortcode]
         * : If the URL is handled by a registered embed handler and returns a shortcode, do shortcode and return result. Has no effect if using the 'raw' option, which by-passes handlers.
         *
         * [--limit-response-size=<size>]
         * : Limit the size of the resulting HTML when using discovery. Default 150 KB (the standard WordPress limit). Not compatible with 'no-discover'.
         *
         * [--raw]
         * : Return the raw oEmbed response instead of the resulting HTML. Ignores the cache and does not sanitize responses or use registered embed handlers.
         *
         * [--raw-format=<json|xml>]
         * : Render raw oEmbed data in a particular format. Defaults to json. Can only be specified in conjunction with the 'raw' option.
         * ---
         * options:
         *   - json
         *   - xml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get embed HTML for a given URL.
         *     $ wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ
         *     <iframe width="525" height="295" src="https://www.youtube.com/embed/dQw4w9WgXcQ?feature=oembed" ...
         *
         *     # Get raw oEmbed data for a given URL.
         *     $ wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw
         *     {"author_url":"https:\/\/www.youtube.com\/user\/RickAstleyVEVO","width":525,"version":"1.0", ...
         */
        public function __invoke($args, $assoc_args)
        {
        }
        /**
         * Creates an XML string from a given array.
         *
         * Same as `\_oembed_create_xml()` in "wp-includes\embed.php" introduced in WP 4.4.0. Polyfilled as marked private (and also to cater for older WP versions).
         *
         * @see _oembed_create_xml()
         *
         * @param array            $data The original oEmbed response data.
         * @param \SimpleXMLElement $node Optional. XML node to append the result to recursively.
         * @return string|false XML string on success, false on error.
         */
        protected function oembed_create_xml($data, $node = null)
        {
        }
    }
    /**
     * Retrieves embed handlers.
     *
     * ## EXAMPLES
     *
     *     # List id,regex,priority fields of available handlers.
     *     $ wp embed handler list --fields=priority,id
     *     +----------+-------------------+
     *     | priority | id                |
     *     +----------+-------------------+
     *     | 10       | youtube_embed_url |
     *     | 9999     | audio             |
     *     | 9999     | video             |
     *
     * @package wp-cli
     */
    class Handler_Command extends \WP_CLI_Command
    {
        protected $default_fields = array('id', 'regex');
        /**
         * Lists all available embed handlers.
         *
         * ## OPTIONS
         *
         * [--field=<field>]
         * : Display the value of a single field
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each handler:
         *
         * * id
         * * regex
         *
         * These fields are optionally available:
         *
         * * callback
         * * priority
         *
         * ## EXAMPLES
         *
         *     # List id,regex,priority fields of available handlers.
         *     $ wp embed handler list --fields=priority,id
         *     +----------+-------------------+
         *     | priority | id                |
         *     +----------+-------------------+
         *     | 10       | youtube_embed_url |
         *     | 9999     | audio             |
         *     | 9999     | video             |
         *
         * @subcommand list
         */
        public function list_handlers($args, $assoc_args)
        {
        }
        /**
         * Get Formatter object based on supplied parameters.
         *
         * @param array $assoc_args Parameters passed to command. Determines formatting.
         * @return \WP_CLI\Formatter
         */
        protected function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Retrieves oEmbed providers.
     *
     * ## EXAMPLES
     *
     *     # List format,endpoint fields of available providers.
     *     $ wp embed provider list
     *     +------------------------------+-----------------------------------------+
     *     | format                       | endpoint                                |
     *     +------------------------------+-----------------------------------------+
     *     | #https?://youtu\.be/.*#i     | https://www.youtube.com/oembed          |
     *     | #https?://flic\.kr/.*#i      | https://www.flickr.com/services/oembed/ |
     *     | #https?://wordpress\.tv/.*#i | https://wordpress.tv/oembed/            |
     *
     *     # Get the matching provider for the URL.
     *     $ wp embed provider match https://www.youtube.com/watch?v=dQw4w9WgXcQ
     *     https://www.youtube.com/oembed
     *
     * @package wp-cli
     */
    class Provider_Command extends \WP_CLI_Command
    {
        protected $default_fields = array('format', 'endpoint');
        /**
         * Lists all available oEmbed providers.
         *
         * ## OPTIONS
         *
         * [--field=<field>]
         * : Display the value of a single field
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         * ---
         *
         * [--force-regex]
         * : Turn the asterisk-type provider URLs into regexes.
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each provider:
         *
         * * format
         * * endpoint
         *
         * This field is optionally available:
         *
         * * regex
         *
         * ## EXAMPLES
         *
         *     # List format,endpoint fields of available providers.
         *     $ wp embed provider list --fields=format,endpoint
         *     +------------------------------+-----------------------------------------+
         *     | format                       | endpoint                                |
         *     +------------------------------+-----------------------------------------+
         *     | #https?://youtu\.be/.*#i     | https://www.youtube.com/oembed          |
         *     | #https?://flic\.kr/.*#i      | https://www.flickr.com/services/oembed/ |
         *     | #https?://wordpress\.tv/.*#i | https://wordpress.tv/oembed/            |
         *
         * @subcommand list
         */
        public function list_providers($args, $assoc_args)
        {
        }
        /**
         * Gets the matching provider for a given URL.
         *
         * ## OPTIONS
         *
         * <url>
         * : URL to retrieve provider for.
         *
         * [--discover]
         * : Whether to use oEmbed discovery or not. Defaults to true.
         *
         * [--limit-response-size=<size>]
         * : Limit the size of the resulting HTML when using discovery. Default 150 KB (the standard WordPress limit). Not compatible with 'no-discover'.
         *
         * [--link-type=<json|xml>]
         * : Whether to accept only a certain link type when using discovery. Defaults to any (json or xml), preferring json. Not compatible with 'no-discover'.
         * ---
         * options:
         *   - json
         *   - xml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get the matching provider for the URL.
         *     $ wp embed provider match https://www.youtube.com/watch?v=dQw4w9WgXcQ
         *     https://www.youtube.com/oembed
         *
         * @subcommand match
         */
        public function match_provider($args, $assoc_args)
        {
        }
        /**
         * Get Formatter object based on supplied parameters.
         *
         * @param array $assoc_args Parameters passed to command. Determines formatting.
         * @return \WP_CLI\Formatter
         */
        protected function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Polyfill for older WP versions.
     */
    class oEmbed extends \WP_oEmbed
    {
        /**
         * Takes a URL and returns the corresponding oEmbed provider's URL, if there is one.
         *
         * @since 4.0.0
         *
         * @see WP_oEmbed::discover()
         *
         * @param string        $url  The URL to the content.
         * @param string|array  $args Optional provider arguments.
         * @return false|string False on failure, otherwise the oEmbed provider URL.
         */
        public function get_provider($url, $args = '')
        {
        }
    }
}
namespace WP_CLI {
    /**
     * Base class for WP-CLI commands that deal with database objects.
     *
     * @package wp-cli
     */
    abstract class CommandWithDBObject extends \WP_CLI_Command
    {
        /**
         * @var string $object_type WordPress' expected name for the object.
         */
        protected $obj_type;
        /**
         * @var string $obj_id_key Key representing object's PK field in db.
         */
        protected $obj_id_key = 'ID';
        /**
         * @var array $obj_fields Default fields to display for each object.
         */
        protected $obj_fields;
        /**
         * Create a given database object.
         * Exits with status.
         *
         * @param array $args Arguments passed to command. Generally unused.
         * @param array $assoc_args Parameters passed to command to be passed to callback.
         * @param string $callback Function used to create object.
         */
        protected function _create($args, $assoc_args, $callback)
        {
        }
        /**
         * Update a given database object.
         * Exits with status.
         *
         * @param array $args Collection of one or more object ids to update.
         * @param array $assoc_args Fields => values to update on each object.
         * @param string $callback Function used to update object.
         */
        protected function _update($args, $assoc_args, $callback)
        {
        }
        /**
         * Transforms arguments with '__' from CSV into expected arrays
         *
         * @param array $assoc_args
         * @return array
         */
        protected static function process_csv_arguments_to_arrays($assoc_args)
        {
        }
        /**
         * Delete a given database object.
         * Exits with status.
         *
         * @param array $args Collection of one or more object ids to delete.
         * @param array $assoc_args Any arguments needed for the callback function.
         * @param callable $callback Function used to delete object.
         */
        protected function _delete($args, $assoc_args, $callback)
        {
        }
        /**
         * Format callback response to consistent format.
         *
         * @param WP_Error|true $response Response from CRUD callback.
         * @param string        $success_msg
         * @return array
         */
        protected function wp_error_to_resp($response, $success_msg)
        {
        }
        /**
         * Display success or warning based on response; return proper exit code.
         *
         * @param array $response Formatted from a CRUD callback.
         * @return int $status
         */
        protected function success_or_failure($response)
        {
        }
        /**
         * Get Formatter object based on supplied parameters.
         *
         * @param array $assoc_args Parameters passed to command. Determines formatting.
         * @return Formatter
         */
        protected function get_formatter(&$assoc_args)
        {
        }
        /**
         * Given a callback, display the URL for one or more objects.
         *
         * @param array $args One or more object references.
         * @param string $callback Function to get URL for the object.
         */
        protected function _url($args, $callback)
        {
        }
    }
}
namespace {
    /**
     * Creates, updates, deletes, and moderates comments.
     *
     * ## EXAMPLES
     *
     *     # Create a new comment.
     *     $ wp comment create --comment_post_ID=15 --comment_content="hello blog" --comment_author="wp-cli"
     *     Success: Created comment 932.
     *
     *     # Update an existing comment.
     *     $ wp comment update 123 --comment_author='That Guy'
     *     Success: Updated comment 123.
     *
     *     # Delete an existing comment.
     *     $ wp comment delete 1337 --force
     *     Success: Deleted comment 1337.
     *
     *     # Trash all spam comments.
     *     $ wp comment delete $(wp comment list --status=spam --format=ids)
     *     Success: Trashed comment 264.
     *     Success: Trashed comment 262.
     *
     * @package wp-cli
     */
    class Comment_Command extends \WP_CLI\CommandWithDBObject
    {
        protected $obj_type = 'comment';
        protected $obj_id_key = 'comment_ID';
        protected $obj_fields = ['comment_ID', 'comment_post_ID', 'comment_date', 'comment_approved', 'comment_author', 'comment_author_email'];
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Creates a new comment.
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : Associative args for the new comment. See wp_insert_comment().
         *
         * [--porcelain]
         * : Output just the new comment id.
         *
         * ## EXAMPLES
         *
         *     # Create comment.
         *     $ wp comment create --comment_post_ID=15 --comment_content="hello blog" --comment_author="wp-cli"
         *     Success: Created comment 932.
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Updates one or more comments.
         *
         * ## OPTIONS
         *
         * <id>...
         * : One or more IDs of comments to update.
         *
         * --<field>=<value>
         * : One or more fields to update. See wp_update_comment().
         *
         * ## EXAMPLES
         *
         *     # Update comment.
         *     $ wp comment update 123 --comment_author='That Guy'
         *     Success: Updated comment 123.
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Generates some number of new dummy comments.
         *
         * Creates a specified number of new comments with dummy data.
         *
         * ## OPTIONS
         *
         * [--count=<number>]
         * : How many comments to generate?
         * ---
         * default: 100
         * ---
         *
         * [--post_id=<post-id>]
         * : Assign comments to a specific post.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: progress
         * options:
         *   - progress
         *   - ids
         * ---
         *
         * ## EXAMPLES
         *
         *     # Generate comments for the given post.
         *     $ wp comment generate --format=ids --count=3 --post_id=123
         *     138 139 140
         *
         *     # Add meta to every generated comment.
         *     $ wp comment generate --format=ids --count=3 | xargs -d ' ' -I % wp comment meta add % foo bar
         *     Success: Added custom field.
         *     Success: Added custom field.
         *     Success: Added custom field.
         */
        public function generate($args, $assoc_args)
        {
        }
        /**
         * Gets the data of a single comment.
         *
         * ## OPTIONS
         *
         * <id>
         * : The comment to get.
         *
         * [--field=<field>]
         * : Instead of returning the whole comment, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get comment.
         *     $ wp comment get 21 --field=content
         *     Thanks for all the comments, everyone!
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Gets a list of comments.
         *
         * Display comments based on all arguments supported by
         * [WP_Comment_Query()](https://developer.wordpress.org/reference/classes/WP_Comment_Query/__construct/).
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : One or more args to pass to WP_Comment_Query.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each comment.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - ids
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each comment:
         *
         * * comment_ID
         * * comment_post_ID
         * * comment_date
         * * comment_approved
         * * comment_author
         * * comment_author_email
         *
         * These fields are optionally available:
         *
         * * comment_author_url
         * * comment_author_IP
         * * comment_date_gmt
         * * comment_content
         * * comment_karma
         * * comment_agent
         * * comment_type
         * * comment_parent
         * * user_id
         * * url
         *
         * ## EXAMPLES
         *
         *     # List comment IDs.
         *     $ wp comment list --field=ID
         *     22
         *     23
         *     24
         *
         *     # List comments of a post.
         *     $ wp comment list --post_id=1 --fields=ID,comment_date,comment_author
         *     +------------+---------------------+----------------+
         *     | comment_ID | comment_date        | comment_author |
         *     +------------+---------------------+----------------+
         *     | 1          | 2015-06-20 09:00:10 | Mr WordPress   |
         *     +------------+---------------------+----------------+
         *
         *     # List approved comments.
         *     $ wp comment list --number=3 --status=approve --fields=ID,comment_date,comment_author
         *     +------------+---------------------+----------------+
         *     | comment_ID | comment_date        | comment_author |
         *     +------------+---------------------+----------------+
         *     | 1          | 2015-06-20 09:00:10 | Mr WordPress   |
         *     | 30         | 2013-03-14 12:35:07 | John Doe       |
         *     | 29         | 2013-03-14 11:56:08 | Jane Doe       |
         *     +------------+---------------------+----------------+
         *
         *     # List unapproved comments.
         *     $ wp comment list --number=3 --status=hold --fields=ID,comment_date,comment_author
         *     +------------+---------------------+----------------+
         *     | comment_ID | comment_date        | comment_author |
         *     +------------+---------------------+----------------+
         *     | 8          | 2023-11-10 13:13:06 | John Doe       |
         *     | 7          | 2023-11-10 13:09:55 | Mr WordPress   |
         *     | 9          | 2023-11-10 11:22:31 | Jane Doe       |
         *     +------------+---------------------+----------------+
         *
         *     # List comments marked as spam.
         *     $ wp comment list --status=spam --fields=ID,comment_date,comment_author
         *     +------------+---------------------+----------------+
         *     | comment_ID | comment_date        | comment_author |
         *     +------------+---------------------+----------------+
         *     | 2          | 2023-11-10 11:22:31 | Jane Doe       |
         *     +------------+---------------------+----------------+
         *
         *     # List comments in trash.
         *     $ wp comment list --status=trash --fields=ID,comment_date,comment_author
         *     +------------+---------------------+----------------+
         *     | comment_ID | comment_date        | comment_author |
         *     +------------+---------------------+----------------+
         *     | 3          | 2023-11-10 11:22:31 | John Doe       |
         *     +------------+---------------------+----------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Deletes a comment.
         *
         * ## OPTIONS
         *
         * <id>...
         * : One or more IDs of comments to delete.
         *
         * [--force]
         * : Skip the trash bin.
         *
         * ## EXAMPLES
         *
         *     # Delete comment.
         *     $ wp comment delete 1337 --force
         *     Success: Deleted comment 1337.
         *
         *     # Delete multiple comments.
         *     $ wp comment delete 1337 2341 --force
         *     Success: Deleted comment 1337.
         *     Success: Deleted comment 2341.
         */
        public function delete($args, $assoc_args)
        {
        }
        private function call($args, $status, $success, $failure)
        {
        }
        private function set_status($args, $status, $success)
        {
        }
        /**
         * Warns if `$_SERVER['SERVER_NAME']` not set as used in email from-address sent to post author in `wp_notify_postauthor()`.
         */
        private function check_server_name()
        {
        }
        /**
         * Trashes a comment.
         *
         * ## OPTIONS
         *
         * <id>...
         * : The IDs of the comments to trash.
         *
         * ## EXAMPLES
         *
         *     # Trash comment.
         *     $ wp comment trash 1337
         *     Success: Trashed comment 1337.
         */
        public function trash($args, $assoc_args)
        {
        }
        /**
         * Untrashes a comment.
         *
         * ## OPTIONS
         *
         * <id>...
         * : The IDs of the comments to untrash.
         *
         * ## EXAMPLES
         *
         *     # Untrash comment.
         *     $ wp comment untrash 1337
         *     Success: Untrashed comment 1337.
         */
        public function untrash($args, $assoc_args)
        {
        }
        /**
         * Marks a comment as spam.
         *
         * ## OPTIONS
         *
         * <id>...
         * : The IDs of the comments to mark as spam.
         *
         * ## EXAMPLES
         *
         *     # Spam comment.
         *     $ wp comment spam 1337
         *     Success: Marked as spam comment 1337.
         */
        public function spam($args, $assoc_args)
        {
        }
        /**
         * Unmarks a comment as spam.
         *
         * ## OPTIONS
         *
         * <id>...
         * : The IDs of the comments to unmark as spam.
         *
         * ## EXAMPLES
         *
         *     # Unspam comment.
         *     $ wp comment unspam 1337
         *     Success: Unspammed comment 1337.
         */
        public function unspam($args, $assoc_args)
        {
        }
        /**
         * Approves a comment.
         *
         * ## OPTIONS
         *
         * <id>...
         * : The IDs of the comments to approve.
         *
         * ## EXAMPLES
         *
         *     # Approve comment.
         *     $ wp comment approve 1337
         *     Success: Approved comment 1337.
         */
        public function approve($args, $assoc_args)
        {
        }
        /**
         * Unapproves a comment.
         *
         * ## OPTIONS
         *
         * <id>...
         * : The IDs of the comments to unapprove.
         *
         * ## EXAMPLES
         *
         *     # Unapprove comment.
         *     $ wp comment unapprove 1337
         *     Success: Unapproved comment 1337.
         */
        public function unapprove($args, $assoc_args)
        {
        }
        /**
         * Counts comments, on whole blog or on a given post.
         *
         * ## OPTIONS
         *
         * [<post-id>]
         * : The ID of the post to count comments in.
         *
         * ## EXAMPLES
         *
         *     # Count comments on whole blog.
         *     $ wp comment count
         *     approved:        33
         *     spam:            3
         *     trash:           1
         *     post-trashed:    0
         *     all:             34
         *     moderated:       1
         *     total_comments:  37
         *
         *     # Count comments in a post.
         *     $ wp comment count 42
         *     approved:        19
         *     spam:            0
         *     trash:           0
         *     post-trashed:    0
         *     all:             19
         *     moderated:       0
         *     total_comments:  19
         */
        public function count($args, $assoc_args)
        {
        }
        /**
         * Recalculates the comment_count value for one or more posts.
         *
         * ## OPTIONS
         *
         * <id>...
         * : IDs for one or more posts to update.
         *
         * ## EXAMPLES
         *
         *     # Recount comment for the post.
         *     $ wp comment recount 123
         *     Updated post 123 comment count to 67.
         */
        public function recount($args)
        {
        }
        /**
         * Gets the status of a comment.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the comment to check.
         *
         * ## EXAMPLES
         *
         *     # Get status of comment.
         *     $ wp comment status 1337
         *     approved
         */
        public function status($args, $assoc_args)
        {
        }
        /**
         * Verifies whether a comment exists.
         *
         * Displays a success message if the comment does exist.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the comment to check.
         *
         * ## EXAMPLES
         *
         *     # Check whether comment exists.
         *     $ wp comment exists 1337
         *     Success: Comment with ID 1337 exists.
         */
        public function exists($args)
        {
        }
    }
}
namespace WP_CLI {
    /**
     * Base class for WP-CLI commands that deal with metadata
     *
     * @package wp-cli
     */
    abstract class CommandWithMeta extends \WP_CLI_Command
    {
        protected $meta_type;
        /**
         * List all metadata associated with an object.
         *
         * ## OPTIONS
         *
         * <id>
         * : ID for the object.
         *
         * [--keys=<keys>]
         * : Limit output to metadata of specific keys.
         *
         * [--fields=<fields>]
         * : Limit the output to specific row fields. Defaults to id,meta_key,meta_value.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         *   - count
         * ---
         *
         * [--orderby=<fields>]
         * : Set orderby which field.
         * ---
         * default: id
         * options:
         *  - id
         *  - meta_key
         *  - meta_value
         * ---
         *
         * [--order=<order>]
         * : Set ascending or descending order.
         * ---
         * default: asc
         * options:
         *  - asc
         *  - desc
         * ---
         *
         * [--unserialize]
         * : Unserialize meta_value output.
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Get meta field value.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * <key>
         * : The name of the meta field to get.
         *
         * [--single]
         * : Whether to return a single value.
         *
         * [--format=<format>]
         * : Get value in a particular format.
         * ---
         * default: var_export
         * options:
         *   - var_export
         *   - json
         *   - yaml
         * ---
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Delete a meta field.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * [<key>]
         * : The name of the meta field to delete.
         *
         * [<value>]
         * : The value to delete. If omitted, all rows with key will deleted.
         *
         * [--all]
         * : Delete all meta for the object.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Add a meta field.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * <key>
         * : The name of the meta field to create.
         *
         * [<value>]
         * : The value of the meta field. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Update a meta field.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * <key>
         * : The name of the meta field to update.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * @alias set
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Get a nested value from a meta field.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * <key>
         * : The name of the meta field to get.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to pluck.
         *
         * [--format=<format>]
         * : The output format of the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         *   - yaml
         */
        public function pluck($args, $assoc_args)
        {
        }
        /**
         * Update a nested value for a meta field.
         *
         * ## OPTIONS
         *
         * <action>
         * : Patch action to perform.
         * ---
         * options:
         *   - insert
         *   - update
         *   - delete
         * ---
         *
         * <id>
         * : The ID of the object.
         *
         * <key>
         * : The name of the meta field to update.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to patch.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         */
        public function patch($args, $assoc_args)
        {
        }
        /**
         * Wrapper method for add_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param bool   $unique     Optional, default is false. Whether the
         *                           specified metadata key should be unique for the
         *                           object. If true, and the object already has a
         *                           value for the specified metadata key, no change
         *                           will be made.
         *
         * @return int|false The meta ID on success, false on failure.
         */
        protected function add_metadata($object_id, $meta_key, $meta_value, $unique = false)
        {
        }
        /**
         * Wrapper method for update_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param mixed  $prev_value Optional. If specified, only update existing
         *                           metadata entries with the specified value.
         *                           Otherwise, update all entries.
         *
         * @return int|bool Meta ID if the key didn't exist, true on successful
         *                  update, false on failure.
         */
        protected function update_metadata($object_id, $meta_key, $meta_value, $prev_value = '')
        {
        }
        /**
         * Wrapper method for get_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id ID of the object the metadata is for.
         * @param string $meta_key  Optional. Metadata key. If not specified,
         *                          retrieve all metadata for the specified object.
         * @param bool   $single    Optional, default is false. If true, return only
         *                          the first value of the specified meta_key. This
         *                          parameter has no effect if meta_key is not
         *                          specified.
         *
         * @return mixed Single metadata value, or array of values.
         */
        protected function get_metadata($object_id, $meta_key = '', $single = false)
        {
        }
        /**
         * Wrapper method for delete_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object metadata is for
         * @param string $meta_key   Metadata key
         * @param mixed $meta_value  Optional. Metadata value. Must be serializable
         *                           if non-scalar. If specified, only delete
         *                           metadata entries with this value. Otherwise,
         *                           delete all entries with the specified meta_key.
         *                           Pass `null, `false`, or an empty string to skip
         *                           this check. For backward compatibility, it is
         *                           not possible to pass an empty string to delete
         *                           those entries with an empty string for a value.
         *
         * @return bool True on successful delete, false on failure.
         */
        protected function delete_metadata($object_id, $meta_key, $meta_value = '')
        {
        }
        /**
         * Get the fields for this object's meta
         *
         * @return array
         */
        private function get_fields()
        {
        }
        /**
         * Check that the object ID exists
         *
         * @param int
         */
        protected function check_object_id($object_id)
        {
        }
    }
}
namespace {
    /**
     * Adds, updates, deletes, and lists comment custom fields.
     *
     * ## EXAMPLES
     *
     *     # Set comment meta
     *     $ wp comment meta set 123 description "Mary is a WordPress developer."
     *     Success: Updated custom field 'description'.
     *
     *     # Get comment meta
     *     $ wp comment meta get 123 description
     *     Mary is a WordPress developer.
     *
     *     # Update comment meta
     *     $ wp comment meta update 123 description "Mary is an awesome WordPress developer."
     *     Success: Updated custom field 'description'.
     *
     *     # Delete comment meta
     *     $ wp comment meta delete 123 description
     *     Success: Deleted custom field.
     */
    class Comment_Meta_Command extends \WP_CLI\CommandWithMeta
    {
        protected $meta_type = 'comment';
        /**
         * Wrapper method for add_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param bool   $unique     Optional, default is false. Whether the
         *                           specified metadata key should be unique for the
         *                           object. If true, and the object already has a
         *                           value for the specified metadata key, no change
         *                           will be made.
         *
         * @return int|false The meta ID on success, false on failure.
         */
        protected function add_metadata($object_id, $meta_key, $meta_value, $unique = \false)
        {
        }
        /**
         * Wrapper method for update_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param mixed  $prev_value Optional. If specified, only update existing
         *                           metadata entries with the specified value.
         *                           Otherwise, update all entries.
         *
         * @return int|bool Meta ID if the key didn't exist, true on successful
         *                  update, false on failure.
         */
        protected function update_metadata($object_id, $meta_key, $meta_value, $prev_value = '')
        {
        }
        /**
         * Wrapper method for get_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id ID of the object the metadata is for.
         * @param string $meta_key  Optional. Metadata key. If not specified,
         *                          retrieve all metadata for the specified object.
         * @param bool   $single    Optional, default is false. If true, return only
         *                          the first value of the specified meta_key. This
         *                          parameter has no effect if meta_key is not
         *                          specified.
         *
         * @return mixed Single metadata value, or array of values.
         */
        protected function get_metadata($object_id, $meta_key = '', $single = \false)
        {
        }
        /**
         * Wrapper method for delete_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object metadata is for
         * @param string $meta_key   Metadata key
         * @param mixed $meta_value  Optional. Metadata value. Must be serializable
         *                           if non-scalar. If specified, only delete
         *                           metadata entries with this value. Otherwise,
         *                           delete all entries with the specified meta_key.
         *                           Pass `null, `false`, or an empty string to skip
         *                           this check. For backward compatibility, it is
         *                           not possible to pass an empty string to delete
         *                           those entries with an empty string for a value.
         *
         * @return bool True on successful delete, false on failure.
         */
        protected function delete_metadata($object_id, $meta_key, $meta_value = '')
        {
        }
        /**
         * Check that the comment ID exists
         *
         * @param int
         */
        protected function check_object_id($object_id)
        {
        }
    }
    /**
     * Lists, creates, assigns, and deletes the active theme's navigation menus.
     *
     * See the [Navigation Menus](https://developer.wordpress.org/themes/functionality/navigation-menus/) reference in the Theme Handbook.
     *
     * ## EXAMPLES
     *
     *     # Create a new menu
     *     $ wp menu create "My Menu"
     *     Success: Created menu 200.
     *
     *     # List existing menus
     *     $ wp menu list
     *     +---------+----------+----------+-----------+-------+
     *     | term_id | name     | slug     | locations | count |
     *     +---------+----------+----------+-----------+-------+
     *     | 200     | My Menu  | my-menu  |           | 0     |
     *     | 177     | Top Menu | top-menu | primary   | 7     |
     *     +---------+----------+----------+-----------+-------+
     *
     *     # Create a new menu link item
     *     $ wp menu item add-custom my-menu Apple http://apple.com --porcelain
     *     1922
     *
     *     # Assign the 'my-menu' menu to the 'primary' location
     *     $ wp menu location assign my-menu primary
     *     Success: Assigned location primary to menu my-menu.
     *
     * @package wp-cli
     */
    class Menu_Command extends \WP_CLI_Command
    {
        protected $obj_type = 'nav_menu';
        protected $obj_fields = ['term_id', 'name', 'slug', 'locations', 'count'];
        /**
         * Creates a new menu.
         *
         * ## OPTIONS
         *
         * <menu-name>
         * : A descriptive name for the menu.
         *
         * [--porcelain]
         * : Output just the new menu id.
         *
         * ## EXAMPLES
         *
         *     $ wp menu create "My Menu"
         *     Success: Created menu 200.
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Deletes one or more menus.
         *
         * ## OPTIONS
         *
         * <menu>...
         * : The name, slug, or term ID for the menu(s).
         *
         * ## EXAMPLES
         *
         *     $ wp menu delete "My Menu"
         *     Deleted menu 'My Menu'.
         *     Success: Deleted 1 of 1 menus.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Gets a list of menus.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - ids
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each menu:
         *
         * * term_id
         * * name
         * * slug
         * * count
         *
         * These fields are optionally available:
         *
         * * term_group
         * * term_taxonomy_id
         * * taxonomy
         * * description
         * * parent
         * * locations
         *
         * ## EXAMPLES
         *
         *     $ wp menu list
         *     +---------+----------+----------+-----------+-------+
         *     | term_id | name     | slug     | locations | count |
         *     +---------+----------+----------+-----------+-------+
         *     | 200     | My Menu  | my-menu  |           | 0     |
         *     | 177     | Top Menu | top-menu | primary   | 7     |
         *     +---------+----------+----------+-----------+-------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        protected function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * List, add, and delete items associated with a menu.
     *
     * ## EXAMPLES
     *
     *     # Add an existing post to an existing menu
     *     $ wp menu item add-post sidebar-menu 33 --title="Custom Test Post"
     *     Success: Menu item added.
     *
     *     # Create a new menu link item
     *     $ wp menu item add-custom sidebar-menu Apple http://apple.com
     *     Success: Menu item added.
     *
     *     # Delete menu item
     *     $ wp menu item delete 45
     *     Success: Deleted 1 of 1 menu items.
     */
    class Menu_Item_Command extends \WP_CLI_Command
    {
        protected $obj_fields = ['db_id', 'type', 'title', 'link', 'position'];
        /**
         * Gets a list of items associated with a menu.
         *
         * ## OPTIONS
         *
         * <menu>
         * : The name, slug, or term ID for the menu.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - ids
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each menu item:
         *
         * * db_id
         * * type
         * * title
         * * link
         * * position
         *
         * These fields are optionally available:
         *
         * * menu_item_parent
         * * object_id
         * * object
         * * type
         * * type_label
         * * target
         * * attr_title
         * * description
         * * classes
         * * xfn
         *
         * ## EXAMPLES
         *
         *     $ wp menu item list main-menu
         *     +-------+-----------+-------------+---------------------------------+----------+
         *     | db_id | type      | title       | link                            | position |
         *     +-------+-----------+-------------+---------------------------------+----------+
         *     | 5     | custom    | Home        | http://example.com              | 1        |
         *     | 6     | post_type | Sample Page | http://example.com/sample-page/ | 2        |
         *     +-------+-----------+-------------+---------------------------------+----------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Adds a post as a menu item.
         *
         * ## OPTIONS
         *
         * <menu>
         * : The name, slug, or term ID for the menu.
         *
         * <post-id>
         * : Post ID to add to the menu.
         *
         * [--title=<title>]
         * : Set a custom title for the menu item.
         *
         * [--link=<link>]
         * : Set a custom url for the menu item.
         *
         * [--description=<description>]
         * : Set a custom description for the menu item.
         *
         * [--attr-title=<attr-title>]
         * : Set a custom title attribute for the menu item.
         *
         * [--target=<target>]
         * : Set a custom link target for the menu item.
         *
         * [--classes=<classes>]
         * : Set a custom link classes for the menu item.
         *
         * [--position=<position>]
         * : Specify the position of this menu item.
         *
         * [--parent-id=<parent-id>]
         * : Make this menu item a child of another menu item.
         *
         * [--porcelain]
         * : Output just the new menu item id.
         *
         * ## EXAMPLES
         *
         *     $ wp menu item add-post sidebar-menu 33 --title="Custom Test Post"
         *     Success: Menu item added.
         *
         * @subcommand add-post
         */
        public function add_post($args, $assoc_args)
        {
        }
        /**
         * Adds a taxonomy term as a menu item.
         *
         * ## OPTIONS
         *
         * <menu>
         * : The name, slug, or term ID for the menu.
         *
         * <taxonomy>
         * : Taxonomy of the term to be added.
         *
         * <term-id>
         * : Term ID of the term to be added.
         *
         * [--title=<title>]
         * : Set a custom title for the menu item.
         *
         * [--link=<link>]
         * : Set a custom url for the menu item.
         *
         * [--description=<description>]
         * : Set a custom description for the menu item.
         *
         * [--attr-title=<attr-title>]
         * : Set a custom title attribute for the menu item.
         *
         * [--target=<target>]
         * : Set a custom link target for the menu item.
         *
         * [--classes=<classes>]
         * : Set a custom link classes for the menu item.
         *
         * [--position=<position>]
         * : Specify the position of this menu item.
         *
         * [--parent-id=<parent-id>]
         * : Make this menu item a child of another menu item.
         *
         * [--porcelain]
         * : Output just the new menu item id.
         *
         * ## EXAMPLES
         *
         *     $ wp menu item add-term sidebar-menu post_tag 24
         *     Success: Menu item added.
         *
         * @subcommand add-term
         */
        public function add_term($args, $assoc_args)
        {
        }
        /**
         * Adds a custom menu item.
         *
         * ## OPTIONS
         *
         * <menu>
         * : The name, slug, or term ID for the menu.
         *
         * <title>
         * : Title for the link.
         *
         * <link>
         * : Target URL for the link.
         *
         * [--description=<description>]
         * : Set a custom description for the menu item.
         *
         * [--attr-title=<attr-title>]
         * : Set a custom title attribute for the menu item.
         *
         * [--target=<target>]
         * : Set a custom link target for the menu item.
         *
         * [--classes=<classes>]
         * : Set a custom link classes for the menu item.
         *
         * [--position=<position>]
         * : Specify the position of this menu item.
         *
         * [--parent-id=<parent-id>]
         * : Make this menu item a child of another menu item.
         *
         * [--porcelain]
         * : Output just the new menu item id.
         *
         * ## EXAMPLES
         *
         *     $ wp menu item add-custom sidebar-menu Apple http://apple.com
         *     Success: Menu item added.
         *
         * @subcommand add-custom
         */
        public function add_custom($args, $assoc_args)
        {
        }
        /**
         * Updates a menu item.
         *
         * ## OPTIONS
         *
         * <db-id>
         * : Database ID for the menu item.
         *
         * [--title=<title>]
         * : Set a custom title for the menu item.
         *
         * [--link=<link>]
         * : Set a custom url for the menu item.
         *
         * [--description=<description>]
         * : Set a custom description for the menu item.
         *
         * [--attr-title=<attr-title>]
         * : Set a custom title attribute for the menu item.
         *
         * [--target=<target>]
         * : Set a custom link target for the menu item.
         *
         * [--classes=<classes>]
         * : Set a custom link classes for the menu item.
         *
         * [--position=<position>]
         * : Specify the position of this menu item.
         *
         * [--parent-id=<parent-id>]
         * : Make this menu item a child of another menu item.
         *
         * ## EXAMPLES
         *
         *     $ wp menu item update 45 --title=WordPress --link='http://wordpress.org' --target=_blank --position=2
         *     Success: Menu item updated.
         *
         * @subcommand update
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Deletes one or more items from a menu.
         *
         * ## OPTIONS
         *
         * <db-id>...
         * : Database ID for the menu item(s).
         *
         * ## EXAMPLES
         *
         *     $ wp menu item delete 45
         *     Success: Deleted 1 of 1 menu items.
         *
         * @subcommand delete
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Worker method to create new items or update existing ones.
         */
        private function add_or_update_item($method, $type, $args, $assoc_args)
        {
        }
        /**
         * Move block of items in one nav_menu up or down by incrementing/decrementing their menu_order field.
         * Expects the menu items to have proper menu_orders (i.e. doesn't fix errors from previous incorrect operations).
         *
         * @param int $menu_id ID of the nav_menu
         * @param int $min_position minimal menu_order to touch
         * @param int $increment how much to change menu_order: +1 to move down, -1 to move up
         * @param int $ignore_item_id menu item that should be ignored by the change (e.g. newly created menu item)
         * @return int number of rows affected
         */
        private function reorder_menu_items($menu_id, $min_position, $increment, $ignore_item_id = 0)
        {
        }
        protected function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Assigns, removes, and lists a menu's locations.
     *
     * ## EXAMPLES
     *
     *     # List available menu locations
     *     $ wp menu location list
     *     +----------+-------------------+
     *     | location | description       |
     *     +----------+-------------------+
     *     | primary  | Primary Menu      |
     *     | social   | Social Links Menu |
     *     +----------+-------------------+
     *
     *     # Assign the 'primary-menu' menu to the 'primary' location
     *     $ wp menu location assign primary-menu primary
     *     Success: Assigned location primary to menu primary-menu.
     *
     *     # Remove the 'primary-menu' menu from the 'primary' location
     *     $ wp menu location remove primary-menu primary
     *     Success: Removed location from menu.
     */
    class Menu_Location_Command extends \WP_CLI_Command
    {
        /**
         * Lists locations for the current theme.
         *
         * ## OPTIONS
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         *   - ids
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each location:
         *
         * * name
         * * description
         *
         * ## EXAMPLES
         *
         *     $ wp menu location list
         *     +----------+-------------------+
         *     | location | description       |
         *     +----------+-------------------+
         *     | primary  | Primary Menu      |
         *     | social   | Social Links Menu |
         *     +----------+-------------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Assigns a location to a menu.
         *
         * ## OPTIONS
         *
         * <menu>
         * : The name, slug, or term ID for the menu.
         *
         * <location>
         * : Location's slug.
         *
         * ## EXAMPLES
         *
         *     $ wp menu location assign primary-menu primary
         *     Success: Assigned location primary to menu primary-menu.
         *
         * @subcommand assign
         */
        public function assign($args, $assoc_args)
        {
        }
        /**
         * Removes a location from a menu.
         *
         * ## OPTIONS
         *
         * <menu>
         * : The name, slug, or term ID for the menu.
         *
         * <location>
         * : Location's slug.
         *
         * ## EXAMPLES
         *
         *     $ wp menu location remove primary-menu primary
         *     Success: Removed location from menu.
         *
         * @subcommand remove
         */
        public function remove($args, $assoc_args)
        {
        }
    }
    /**
     * Gets, adds, updates, deletes, and lists network custom fields.
     *
     * ## EXAMPLES
     *
     *     # Get a list of super-admins
     *     $ wp network meta get 1 site_admins
     *     array (
     *       0 => 'supervisor',
     *     )
     */
    class Network_Meta_Command extends \WP_CLI\CommandWithMeta
    {
        protected $meta_type = 'site';
    }
    /**
     * Perform network-wide operations.
     *
     * ## EXAMPLES
     *
     *     # Get a list of super-admins
     *     $ wp network meta get 1 site_admins
     *     array (
     *       0 => 'supervisor',
     *     )
     */
    class Network_Namespace extends \WP_CLI\Dispatcher\CommandNamespace
    {
    }
    /**
     * Retrieves and sets site options, including plugin and WordPress settings.
     *
     * See the [Plugin Settings API](https://developer.wordpress.org/plugins/settings/settings-api/) and the [Theme Options](https://developer.wordpress.org/themes/customize-api/) for more information on adding customized options.
     *
     * ## EXAMPLES
     *
     *     # Get site URL.
     *     $ wp option get siteurl
     *     http://example.com
     *
     *     # Add option.
     *     $ wp option add my_option foobar
     *     Success: Added 'my_option' option.
     *
     *     # Update option.
     *     $ wp option update my_option '{"foo": "bar"}' --format=json
     *     Success: Updated 'my_option' option.
     *
     *     # Delete option.
     *     $ wp option delete my_option
     *     Success: Deleted 'my_option' option.
     *
     * @package wp-cli
     */
    class Option_Command extends \WP_CLI_Command
    {
        /**
         * Gets the value for an option.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the option.
         *
         * [--format=<format>]
         * : Get value in a particular format.
         * ---
         * default: var_export
         * options:
         *   - var_export
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get option.
         *     $ wp option get home
         *     http://example.com
         *
         *     # Get blog description.
         *     $ wp option get blogdescription
         *     A random blog description
         *
         *     # Get blog name
         *     $ wp option get blogname
         *     A random blog name
         *
         *     # Get admin email.
         *     $ wp option get admin_email
         *     someone@example.com
         *
         *     # Get option in JSON format.
         *     $ wp option get active_plugins --format=json
         *     {"0":"dynamically-dynamic-sidebar\/dynamically-dynamic-sidebar.php","1":"monster-widget\/monster-widget.php","2":"show-current-template\/show-current-template.php","3":"theme-check\/theme-check.php","5":"wordpress-importer\/wordpress-importer.php"}
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Adds a new option value.
         *
         * Errors if the option already exists.
         *
         * ## OPTIONS
         *
         * <key>
         * : The name of the option to add.
         *
         * [<value>]
         * : The value of the option to add. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * [--autoload=<autoload>]
         * : Should this option be automatically loaded.
         * ---
         * options:
         *   - 'on'
         *   - 'off'
         *   - 'yes'
         *   - 'no'
         * ---
         *
         * ## EXAMPLES
         *
         *     # Create an option by reading a JSON file.
         *     $ wp option add my_option --format=json < config.json
         *     Success: Added 'my_option' option.
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Lists options and their values.
         *
         * ## OPTIONS
         *
         * [--search=<pattern>]
         * : Use wildcards ( * and ? ) to match option name.
         *
         * [--exclude=<pattern>]
         * : Pattern to exclude. Use wildcards ( * and ? ) to match option name.
         *
         * [--autoload=<value>]
         * : Match only autoload options when value is on, and only not-autoload option when off.
         *
         * [--transients]
         * : List only transients. Use `--no-transients` to ignore all transients.
         *
         * [--unserialize]
         * : Unserialize option values in output.
         *
         * [--field=<field>]
         * : Prints the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : The serialization format for the value. total_bytes displays the total size of matching options in bytes.
         * ---
         * default: table
         * options:
         *   - table
         *   - json
         *   - csv
         *   - count
         *   - yaml
         *   - total_bytes
         * ---
         *
         * [--orderby=<fields>]
         * : Set orderby which field.
         * ---
         * default: option_id
         * options:
         *  - option_id
         *  - option_name
         *  - option_value
         * ---
         *
         * [--order=<order>]
         * : Set ascending or descending order.
         * ---
         * default: asc
         * options:
         *  - asc
         *  - desc
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * This field will be displayed by default for each matching option:
         *
         * * option_name
         * * option_value
         *
         * These fields are optionally available:
         *
         * * autoload
         * * size_bytes
         *
         * ## EXAMPLES
         *
         *     # Get the total size of all autoload options.
         *     $ wp option list --autoload=on --format=total_bytes
         *     33198
         *
         *     # Find biggest transients.
         *     $ wp option list --search="*_transient_*" --fields=option_name,size_bytes | sort -n -k 2 | tail
         *     option_name size_bytes
         *     _site_transient_timeout_theme_roots 10
         *     _site_transient_theme_roots 76
         *     _site_transient_update_themes   181
         *     _site_transient_update_core 808
         *     _site_transient_update_plugins  6645
         *
         *     # List all options beginning with "i2f_".
         *     $ wp option list --search="i2f_*"
         *     +-------------+--------------+
         *     | option_name | option_value |
         *     +-------------+--------------+
         *     | i2f_version | 0.1.0        |
         *     +-------------+--------------+
         *
         *     # Delete all options beginning with "theme_mods_".
         *     $ wp option list --search="theme_mods_*" --field=option_name | xargs -I % wp option delete %
         *     Success: Deleted 'theme_mods_twentysixteen' option.
         *     Success: Deleted 'theme_mods_twentyfifteen' option.
         *     Success: Deleted 'theme_mods_twentyfourteen' option.
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Updates an option value.
         *
         * ## OPTIONS
         *
         * <key>
         * : The name of the option to update.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--autoload=<autoload>]
         * : Requires WP 4.2. Should this option be automatically loaded.
         * ---
         * options:
         *   - 'on'
         *   - 'off'
         *   - 'yes'
         *   - 'no'
         * ---
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Update an option by reading from a file.
         *     $ wp option update my_option < value.txt
         *     Success: Updated 'my_option' option.
         *
         *     # Update one option on multiple sites using xargs.
         *     $ wp site list --field=url | xargs -n1 -I {} sh -c 'wp --url={} option update my_option my_value'
         *     Success: Updated 'my_option' option.
         *     Success: Updated 'my_option' option.
         *
         *     # Update site blog name.
         *     $ wp option update blogname "Random blog name"
         *     Success: Updated 'blogname' option.
         *
         *     # Update site blog description.
         *     $ wp option update blogdescription "Some random blog description"
         *     Success: Updated 'blogdescription' option.
         *
         *     # Update admin email address.
         *     $ wp option update admin_email someone@example.com
         *     Success: Updated 'admin_email' option.
         *
         *     # Set the default role.
         *     $ wp option update default_role author
         *     Success: Updated 'default_role' option.
         *
         *     # Set the timezone string.
         *     $ wp option update timezone_string "America/New_York"
         *     Success: Updated 'timezone_string' option.
         *
         * @alias set
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Gets the 'autoload' value for an option.
         *
         * ## OPTIONS
         *
         * <key>
         * : The name of the option to get 'autoload' of.
         *
         * ## EXAMPLES
         *
         *     # Get the 'autoload' value for an option.
         *     $ wp option get-autoload blogname
         *     yes
         *
         * @subcommand get-autoload
         */
        public function get_autoload($args)
        {
        }
        /**
         * Sets the 'autoload' value for an option.
         *
         * ## OPTIONS
         *
         * <key>
         * : The name of the option to set 'autoload' for.
         *
         * <autoload>
         * : Should this option be automatically loaded.
         * ---
         * options:
         *   - 'on'
         *   - 'off'
         *   - 'yes'
         *   - 'no'
         * ---
         *
         * ## EXAMPLES
         *
         *     # Set the 'autoload' value for an option.
         *     $ wp option set-autoload abc_options no
         *     Success: Updated autoload value for 'abc_options' option.
         *
         * @subcommand set-autoload
         */
        public function set_autoload($args)
        {
        }
        /**
         * Deletes an option.
         *
         * ## OPTIONS
         *
         * <key>...
         * : Key for the option.
         *
         * ## EXAMPLES
         *
         *     # Delete an option.
         *     $ wp option delete my_option
         *     Success: Deleted 'my_option' option.
         *
         *     # Delete multiple options.
         *     $ wp option delete option_one option_two option_three
         *     Success: Deleted 'option_one' option.
         *     Success: Deleted 'option_two' option.
         *     Warning: Could not delete 'option_three' option. Does it exist?
         */
        public function delete($args)
        {
        }
        /**
         * Gets a nested value from an option.
         *
         * ## OPTIONS
         *
         * <key>
         * : The option name.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to pluck.
         *
         * [--format=<format>]
         * : The output format of the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         *   - yaml
         * ---
         */
        public function pluck($args, $assoc_args)
        {
        }
        /**
         * Updates a nested value in an option.
         *
         * ## OPTIONS
         *
         * <action>
         * : Patch action to perform.
         * ---
         * options:
         *   - insert
         *   - update
         *   - delete
         * ---
         *
         * <key>
         * : The option name.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to patch.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Add 'bar' to the 'foo' key on an option with name 'option_name'
         *     $ wp option patch insert option_name foo bar
         *     Success: Updated 'option_name' option.
         *
         *     # Update the value of 'foo' key to 'new' on an option with name 'option_name'
         *     $ wp option patch update option_name foo new
         *     Success: Updated 'option_name' option.
         *
         *     # Set nested value of 'bar' key to value we have in the patch file on an option with name 'option_name'.
         *     $ wp option patch update option_name foo bar < patch
         *     Success: Updated 'option_name' option.
         *
         *     # Update the value for the key 'not-a-key' which is not exist on an option with name 'option_name'.
         *     $ wp option patch update option_name foo not-a-key new-value
         *     Error: No data exists for key "not-a-key"
         *
         *     # Update the value for the key 'foo' without passing value on an option with name 'option_name'.
         *     $ wp option patch update option_name foo
         *     Error: Please provide value to update.
         *
         *     # Delete the nested key 'bar' under 'foo' key on an option with name 'option_name'.
         *     $ wp option patch delete option_name foo bar
         *     Success: Updated 'option_name' option.
         */
        public function patch($args, $assoc_args)
        {
        }
        private static function esc_like($old)
        {
        }
    }
    /**
     * Manages posts, content, and meta.
     *
     * ## EXAMPLES
     *
     *     # Create a new post.
     *     $ wp post create --post_type=post --post_title='A sample post'
     *     Success: Created post 123.
     *
     *     # Update an existing post.
     *     $ wp post update 123 --post_status=draft
     *     Success: Updated post 123.
     *
     *     # Delete an existing post.
     *     $ wp post delete 123
     *     Success: Trashed post 123.
     *
     * @package wp-cli
     */
    class Post_Command extends \WP_CLI\CommandWithDBObject
    {
        protected $obj_type = 'post';
        protected $obj_fields = ['ID', 'post_title', 'post_name', 'post_date', 'post_status'];
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Creates a new post.
         *
         * ## OPTIONS
         *
         * [--post_author=<post_author>]
         * : The ID of the user who added the post. Default is the current user ID.
         *
         * [--post_date=<post_date>]
         * : The date of the post. Default is the current time.
         *
         * [--post_date_gmt=<post_date_gmt>]
         * : The date of the post in the GMT timezone. Default is the value of $post_date.
         *
         * [--post_content=<post_content>]
         * : The post content. Default empty.
         *
         * [--post_content_filtered=<post_content_filtered>]
         * : The filtered post content. Default empty.
         *
         * [--post_title=<post_title>]
         * : The post title. Default empty.
         *
         * [--post_excerpt=<post_excerpt>]
         * : The post excerpt. Default empty.
         *
         * [--post_status=<post_status>]
         * : The post status. Default 'draft'.
         *
         * [--post_type=<post_type>]
         * : The post type. Default 'post'.
         *
         * [--comment_status=<comment_status>]
         * : Whether the post can accept comments. Accepts 'open' or 'closed'. Default is the value of 'default_comment_status' option.
         *
         * [--ping_status=<ping_status>]
         * : Whether the post can accept pings. Accepts 'open' or 'closed'. Default is the value of 'default_ping_status' option.
         *
         * [--post_password=<post_password>]
         * : The password to access the post. Default empty.
         *
         * [--post_name=<post_name>]
         * : The post name. Default is the sanitized post title when creating a new post.
         *
         * [--from-post=<post_id>]
         * : Post id of a post to be duplicated.
         *
         * [--to_ping=<to_ping>]
         * : Space or carriage return-separated list of URLs to ping. Default empty.
         *
         * [--pinged=<pinged>]
         * : Space or carriage return-separated list of URLs that have been pinged. Default empty.
         *
         * [--post_modified=<post_modified>]
         * : The date when the post was last modified. Default is the current time.
         *
         * [--post_modified_gmt=<post_modified_gmt>]
         * : The date when the post was last modified in the GMT timezone. Default is the current time.
         *
         * [--post_parent=<post_parent>]
         * : Set this for the post it belongs to, if any. Default 0.
         *
         * [--menu_order=<menu_order>]
         * : The order the post should be displayed in. Default 0.
         *
         * [--post_mime_type=<post_mime_type>]
         * : The mime type of the post. Default empty.
         *
         * [--guid=<guid>]
         * : Global Unique ID for referencing the post. Default empty.
         *
         * [--post_category=<post_category>]
         * : Array of category names, slugs, or IDs. Defaults to value of the 'default_category' option.
         *
         * [--tags_input=<tags_input>]
         * : Array of tag names, slugs, or IDs. Default empty.
         *
         * [--tax_input=<tax_input>]
         * : Array of taxonomy terms keyed by their taxonomy name. Default empty.
         *
         * [--meta_input=<meta_input>]
         * : Array in JSON format of post meta values keyed by their post meta key. Default empty.
         *
         * [<file>]
         * : Read post content from <file>. If this value is present, the
         *     `--post_content` argument will be ignored.
         *
         *   Passing `-` as the filename will cause post content to
         *   be read from STDIN.
         *
         * [--<field>=<value>]
         * : Associative args for the new post. See wp_insert_post().
         *
         * [--edit]
         * : Immediately open system's editor to write or edit post content.
         *
         *   If content is read from a file, from STDIN, or from the `--post_content`
         *   argument, that text will be loaded into the editor.
         *
         * [--porcelain]
         * : Output just the new post id.
         *
         *
         * ## EXAMPLES
         *
         *     # Create post and schedule for future
         *     $ wp post create --post_type=post --post_title='A future post' --post_status=future --post_date='2030-12-01 07:00:00'
         *     Success: Created post 1921.
         *
         *     # Create post with content from given file
         *     $ wp post create ./post-content.txt --post_category=201,345 --post_title='Post from file'
         *     Success: Created post 1922.
         *
         *     # Create a post with multiple meta values.
         *     $ wp post create --post_title='A post' --post_content='Just a small post.' --meta_input='{"key1":"value1","key2":"value2"}'
         *     Success: Created post 1923.
         *
         *     # Create a duplicate post from existing posts.
         *     $ wp post create --from-post=123 --post_title='Different Title'
         *     Success: Created post 2350.
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Updates one or more existing posts.
         *
         * ## OPTIONS
         *
         * <id>...
         * : One or more IDs of posts to update.
         *
         * [--post_author=<post_author>]
         * : The ID of the user who added the post. Default is the current user ID.
         *
         * [--post_date=<post_date>]
         * : The date of the post. Default is the current time.
         *
         * [--post_date_gmt=<post_date_gmt>]
         * : The date of the post in the GMT timezone. Default is the value of $post_date.
         *
         * [--post_content=<post_content>]
         * : The post content. Default empty.
         *
         * [--post_content_filtered=<post_content_filtered>]
         * : The filtered post content. Default empty.
         *
         * [--post_title=<post_title>]
         * : The post title. Default empty.
         *
         * [--post_excerpt=<post_excerpt>]
         * : The post excerpt. Default empty.
         *
         * [--post_status=<post_status>]
         * : The post status. Default 'draft'.
         *
         * [--post_type=<post_type>]
         * : The post type. Default 'post'.
         *
         * [--comment_status=<comment_status>]
         * : Whether the post can accept comments. Accepts 'open' or 'closed'. Default is the value of 'default_comment_status' option.
         *
         * [--ping_status=<ping_status>]
         * : Whether the post can accept pings. Accepts 'open' or 'closed'. Default is the value of 'default_ping_status' option.
         *
         * [--post_password=<post_password>]
         * : The password to access the post. Default empty.
         *
         * [--post_name=<post_name>]
         * : The post name. Default is the sanitized post title when creating a new post.
         *
         * [--to_ping=<to_ping>]
         * : Space or carriage return-separated list of URLs to ping. Default empty.
         *
         * [--pinged=<pinged>]
         * : Space or carriage return-separated list of URLs that have been pinged. Default empty.
         *
         * [--post_modified=<post_modified>]
         * : The date when the post was last modified. Default is the current time.
         *
         * [--post_modified_gmt=<post_modified_gmt>]
         * : The date when the post was last modified in the GMT timezone. Default is the current time.
         *
         * [--post_parent=<post_parent>]
         * : Set this for the post it belongs to, if any. Default 0.
         *
         * [--menu_order=<menu_order>]
         * : The order the post should be displayed in. Default 0.
         *
         * [--post_mime_type=<post_mime_type>]
         * : The mime type of the post. Default empty.
         *
         * [--guid=<guid>]
         * : Global Unique ID for referencing the post. Default empty.
         *
         * [--post_category=<post_category>]
         * : Array of category names, slugs, or IDs. Defaults to value of the 'default_category' option.
         *
         * [--tags_input=<tags_input>]
         * : Array of tag names, slugs, or IDs. Default empty.
         *
         * [--tax_input=<tax_input>]
         * : Array of taxonomy terms keyed by their taxonomy name. Default empty.
         *
         * [--meta_input=<meta_input>]
         * : Array in JSON format of post meta values keyed by their post meta key. Default empty.
         *
         * [<file>]
         * : Read post content from <file>. If this value is present, the
         *     `--post_content` argument will be ignored.
         *
         *   Passing `-` as the filename will cause post content to
         *   be read from STDIN.
         *
         * --<field>=<value>
         * : One or more fields to update. See wp_insert_post().
         *
         * [--defer-term-counting]
         * : Recalculate term count in batch, for a performance boost.
         *
         * ## EXAMPLES
         *
         *     $ wp post update 123 --post_name=something --post_status=draft
         *     Success: Updated post 123.
         *
         *     # Update a post with multiple meta values.
         *     $ wp post update 123 --meta_input='{"key1":"value1","key2":"value2"}'
         *     Success: Updated post 123.
         *
         *     # Update multiple posts at once.
         *     $ wp post update 123 456 --post_author=789
         *     Success: Updated post 123.
         *     Success: Updated post 456.
         *
         *     # Update all posts of a given post type at once.
         *     $ wp post update $(wp post list --post_type=page --format=ids) --post_author=123
         *     Success: Updated post 123.
         *     Success: Updated post 456.
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Launches system editor to edit post content.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the post to edit.
         *
         * ## EXAMPLES
         *
         *     # Launch system editor to edit post
         *     $ wp post edit 123
         */
        public function edit($args, $assoc_args)
        {
        }
        // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Whitelisting to provide backward compatibility to classes possibly extending this class.
        protected function _edit($content, $title)
        {
        }
        /**
         * Gets details about a post.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the post to get.
         *
         * [--field=<field>]
         * : Instead of returning the whole post, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Save the post content to a file
         *     $ wp post get 123 --field=content > file.txt
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Deletes an existing post.
         *
         * ## OPTIONS
         *
         * <id>...
         * : One or more IDs of posts to delete.
         *
         * [--force]
         * : Skip the trash bin.
         *
         * [--defer-term-counting]
         * : Recalculate term count in batch, for a performance boost.
         *
         * ## EXAMPLES
         *
         *     # Delete post skipping trash
         *     $ wp post delete 123 --force
         *     Success: Deleted post 123.
         *
         *     # Delete multiple posts
         *     $ wp post delete 123 456 789
         *     Success: Trashed post 123.
         *     Success: Trashed post 456.
         *     Success: Trashed post 789.
         *
         *     # Delete all pages
         *     $ wp post delete $(wp post list --post_type='page' --format=ids)
         *     Success: Trashed post 1164.
         *     Success: Trashed post 1186.
         *
         *     # Delete all posts in the trash
         *     $ wp post delete $(wp post list --post_status=trash --format=ids)
         *     Success: Deleted post 1268.
         *     Success: Deleted post 1294.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Callback used to delete a post.
         *
         * @param $post_id
         * @param $assoc_args
         * @return array
         */
        protected function delete_callback($post_id, $assoc_args)
        {
        }
        /**
         * Gets a list of posts.
         *
         * Display posts based on all arguments supported by [WP_Query()](https://developer.wordpress.org/reference/classes/wp_query/).
         * Only shows post types marked as post by default.
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : One or more args to pass to WP_Query.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each post.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each post:
         *
         * * ID
         * * post_title
         * * post_name
         * * post_date
         * * post_status
         *
         * These fields are optionally available:
         *
         * * post_author
         * * post_date_gmt
         * * post_content
         * * post_excerpt
         * * comment_status
         * * ping_status
         * * post_password
         * * to_ping
         * * pinged
         * * post_modified
         * * post_modified_gmt
         * * post_content_filtered
         * * post_parent
         * * guid
         * * menu_order
         * * post_type
         * * post_mime_type
         * * comment_count
         * * filter
         * * url
         *
         * ## EXAMPLES
         *
         *     # List post
         *     $ wp post list --field=ID
         *     568
         *     829
         *     1329
         *     1695
         *
         *     # List posts in JSON
         *     $ wp post list --post_type=post --posts_per_page=5 --format=json
         *     [{"ID":1,"post_title":"Hello world!","post_name":"hello-world","post_date":"2015-06-20 09:00:10","post_status":"publish"},{"ID":1178,"post_title":"Markup: HTML Tags and Formatting","post_name":"markup-html-tags-and-formatting","post_date":"2013-01-11 20:22:19","post_status":"draft"}]
         *
         *     # List all pages
         *     $ wp post list --post_type=page --fields=post_title,post_status
         *     +-------------+-------------+
         *     | post_title  | post_status |
         *     +-------------+-------------+
         *     | Sample Page | publish     |
         *     +-------------+-------------+
         *
         *     # List ids of all pages and posts
         *     $ wp post list --post_type=page,post --format=ids
         *     15 25 34 37 198
         *
         *     # List given posts
         *     $ wp post list --post__in=1,3
         *     +----+--------------+-------------+---------------------+-------------+
         *     | ID | post_title   | post_name   | post_date           | post_status |
         *     +----+--------------+-------------+---------------------+-------------+
         *     | 3  | Lorem Ipsum  | lorem-ipsum | 2016-06-01 14:34:36 | publish     |
         *     | 1  | Hello world! | hello-world | 2016-06-01 14:31:12 | publish     |
         *     +----+--------------+-------------+---------------------+-------------+
         *
         *     # List given post by a specific author
         *     $ wp post list --author=2
         *     +----+-------------------+-------------------+---------------------+-------------+
         *     | ID | post_title        | post_name         | post_date           | post_status |
         *     +----+-------------------+-------------------+---------------------+-------------+
         *     | 14 | New documentation | new-documentation | 2021-06-18 21:05:11 | publish     |
         *     +----+-------------------+-------------------+---------------------+-------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Generates some posts.
         *
         * Creates a specified number of new posts with dummy data.
         *
         * ## OPTIONS
         *
         * [--count=<number>]
         * : How many posts to generate?
         * ---
         * default: 100
         * ---
         *
         * [--post_type=<type>]
         * : The type of the generated posts.
         * ---
         * default: post
         * ---
         *
         * [--post_status=<status>]
         * : The status of the generated posts.
         * ---
         * default: publish
         * ---
         *
         * [--post_title=<post_title>]
         * : The post title.
         * ---
         * default:
         * ---
         *
         * [--post_author=<login>]
         * : The author of the generated posts.
         * ---
         * default:
         * ---
         *
         * [--post_date=<yyyy-mm-dd-hh-ii-ss>]
         * : The date of the post. Default is the current time.
         *
         * [--post_date_gmt=<yyyy-mm-dd-hh-ii-ss>]
         * : The date of the post in the GMT timezone. Default is the value of --post_date.
         *
         * [--post_content]
         * : If set, the command reads the post_content from STDIN.
         *
         * [--max_depth=<number>]
         * : For hierarchical post types, generate child posts down to a certain depth.
         * ---
         * default: 1
         * ---
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: progress
         * options:
         *   - progress
         *   - ids
         * ---
         *
         * ## EXAMPLES
         *
         *     # Generate posts.
         *     $ wp post generate --count=10 --post_type=page --post_date=1999-01-04
         *     Generating posts  100% [================================================] 0:01 / 0:04
         *
         *     # Generate posts with fetched content.
         *     $ curl -N https://loripsum.net/api/5 | wp post generate --post_content --count=10
         *       % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
         *                                      Dload  Upload   Total   Spent    Left  Speed
         *     100  2509  100  2509    0     0    616      0  0:00:04  0:00:04 --:--:--   616
         *     Generating posts  100% [================================================] 0:01 / 0:04
         *
         *     # Add meta to every generated posts.
         *     $ wp post generate --format=ids | xargs -d ' ' -I % wp post meta add % foo bar
         *     Success: Added custom field.
         *     Success: Added custom field.
         *     Success: Added custom field.
         */
        public function generate($args, $assoc_args)
        {
        }
        /**
         * Gets the post ID for a given URL.
         *
         * ## OPTIONS
         *
         * <url>
         * : The URL of the post to get.
         *
         * ## EXAMPLES
         *
         *     # Get post ID by URL
         *     $ wp post url-to-id https://example.com/?p=1
         *     1
         *
         * @subcommand url-to-id
         */
        public function url_to_id($args, $assoc_args)
        {
        }
        private function maybe_make_child()
        {
        }
        private function maybe_reset_depth()
        {
        }
        /**
         * Read post content from file or STDIN
         *
         * @param string $arg Supplied argument
         * @return string
         */
        private function read_from_file_or_stdin($arg)
        {
        }
        /**
         * Resolves post_category arg into an array of category ids.
         *
         * @param string $arg Supplied argument.
         * @return array
         */
        private function get_category_ids($arg)
        {
        }
        /**
         * Get post metadata.
         *
         * @param $post_id ID of the post.
         *
         * @return array
         */
        private function get_metadata($post_id)
        {
        }
        /**
         * Get Categories of a post.
         *
         * @param $post_id ID of the post.
         *
         * @return array
         */
        private function get_category($post_id)
        {
        }
        /**
         * Get Tags of a post.
         *
         * @param $post_id ID of the post.
         *
         * @return array
         */
        private function get_tags($post_id)
        {
        }
        /**
         * Verifies whether a post exists.
         *
         * Displays a success message if the post does exist.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the post to check.
         *
         * ## EXAMPLES
         *
         *     # The post exists.
         *     $ wp post exists 1337
         *     Success: Post with ID 1337 exists.
         *     $ echo $?
         *     0
         *
         *     # The post does not exist.
         *     $ wp post exists 10000
         *     $ echo $?
         *     1
         */
        public function exists($args)
        {
        }
        /**
         * Convert a date-time string with a hyphen separator to a space separator.
         *
         * @param string $date_string The date-time string to convert.
         * @return string The converted date-time string.
         *
         * Example:
         * maybe_convert_hyphenated_date_format( "2018-07-05-17:17:17" );
         * Returns: "2018-07-05 17:17:17"
         */
        private function maybe_convert_hyphenated_date_format($date_string)
        {
        }
    }
    /**
     * Adds, updates, deletes, and lists post custom fields.
     *
     * ## EXAMPLES
     *
     *     # Set post meta
     *     $ wp post meta set 123 _wp_page_template about.php
     *     Success: Updated custom field '_wp_page_template'.
     *
     *     # Get post meta
     *     $ wp post meta get 123 _wp_page_template
     *     about.php
     *
     *     # Update post meta
     *     $ wp post meta update 123 _wp_page_template contact.php
     *     Success: Updated custom field '_wp_page_template'.
     *
     *     # Delete post meta
     *     $ wp post meta delete 123 _wp_page_template
     *     Success: Deleted custom field.
     */
    class Post_Meta_Command extends \WP_CLI\CommandWithMeta
    {
        protected $meta_type = 'post';
        /**
         * Check that the post ID exists
         *
         * @param int
         */
        protected function check_object_id($object_id)
        {
        }
        /**
         * Wrapper method for add_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param bool   $unique     Optional, default is false. Whether the
         *                           specified metadata key should be unique for the
         *                           object. If true, and the object already has a
         *                           value for the specified metadata key, no change
         *                           will be made.
         *
         * @return int|false The meta ID on success, false on failure.
         */
        protected function add_metadata($object_id, $meta_key, $meta_value, $unique = \false)
        {
        }
        /**
         * Wrapper method for update_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param mixed  $prev_value Optional. If specified, only update existing
         *                           metadata entries with the specified value.
         *                           Otherwise, update all entries.
         *
         * @return int|bool Meta ID if the key didn't exist, true on successful
         *                  update, false on failure.
         */
        protected function update_metadata($object_id, $meta_key, $meta_value, $prev_value = '')
        {
        }
        /**
         * Wrapper method for get_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id ID of the object the metadata is for.
         * @param string $meta_key  Optional. Metadata key. If not specified,
         *                          retrieve all metadata for the specified object.
         * @param bool   $single    Optional, default is false. If true, return only
         *                          the first value of the specified meta_key. This
         *                          parameter has no effect if meta_key is not
         *                          specified.
         *
         * @return mixed Single metadata value, or array of values.
         */
        protected function get_metadata($object_id, $meta_key = '', $single = \false)
        {
        }
        /**
         * Wrapper method for delete_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object metadata is for
         * @param string $meta_key   Metadata key
         * @param mixed  $meta_value  Optional. Metadata value. Must be serializable
         *                            if non-scalar. If specified, only delete
         *                            metadata entries with this value. Otherwise,
         *                            delete all entries with the specified meta_key.
         *                            Pass `null, `false`, or an empty string to skip
         *                            this check. For backward compatibility, it is
         *                            not possible to pass an empty string to delete
         *                            those entries with an empty string for a value.
         *
         * @return bool True on successful delete, false on failure.
         */
        protected function delete_metadata($object_id, $meta_key, $meta_value = '')
        {
        }
        /**
         * Cleans up duplicate post meta values on a post.
         *
         * ## OPTIONS
         *
         * <id>
         * : ID of the post to clean.
         *
         * <key>
         * : Meta key to clean up.
         *
         * ## EXAMPLES
         *
         *     # Delete duplicate post meta.
         *     wp post meta clean-duplicates 1234 enclosure
         *     Success: Cleaned up duplicate 'enclosure' meta values.
         *
         * @subcommand clean-duplicates
         */
        public function clean_duplicates($args, $assoc_args)
        {
        }
    }
}
namespace WP_CLI {
    /**
     * Base class for WP-CLI commands that deal with terms
     *
     * @package wp-cli
     */
    abstract class CommandWithTerms extends \WP_CLI_Command
    {
        /**
         * @var string $object_type WordPress' expected name for the object.
         */
        protected $obj_type;
        /**
         * @var string $object_id WordPress' object id.
         */
        protected $obj_id;
        /**
         * @var array $obj_fields Default fields to display for each object.
         */
        protected $obj_fields = ['term_id', 'name', 'slug', 'taxonomy'];
        /**
         * List all terms associated with an object.
         *
         * ## OPTIONS
         *
         * <id>
         * : ID for the object.
         *
         * <taxonomy>...
         * : One or more taxonomies to list.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each term.
         *
         * [--fields=<fields>]
         * : Limit the output to specific row fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         *   - count
         *   - ids
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each term:
         *
         * * term_id
         * * name
         * * slug
         * * taxonomy
         *
         * These fields are optionally available:
         *
         * * term_taxonomy_id
         * * description
         * * term_group
         * * parent
         * * count
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Remove a term from an object.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * <taxonomy>
         * : The name of the term's taxonomy.
         *
         * [<term>...]
         * : The slug of the term or terms to be removed from the object.
         *
         * [--by=<field>]
         * : Explicitly handle the term value as a slug or id.
         * ---
         * default: slug
         * options:
         *   - slug
         *   - id
         * ---
         *
         * [--all]
         * : Remove all terms from the object.
         */
        public function remove($args, $assoc_args)
        {
        }
        /**
         * Add a term to an object.
         *
         * Append the term to the existing set of terms on the object.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * <taxonomy>
         * : The name of the taxonomy type to be added.
         *
         * <term>...
         * : The slug of the term or terms to be added.
         *
         * [--by=<field>]
         * : Explicitly handle the term value as a slug or id.
         * ---
         * default: slug
         * options:
         *   - slug
         *   - id
         * ---
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Set object terms.
         *
         * Replaces existing terms on the object.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the object.
         *
         * <taxonomy>
         * : The name of the taxonomy type to be updated.
         *
         * <term>...
         * : The slug of the term or terms to be updated.
         *
         * [--by=<field>]
         * : Explicitly handle the term value as a slug or id.
         * ---
         * default: slug
         * options:
         *   - slug
         *   - id
         * ---
         */
        public function set($args, $assoc_args)
        {
        }
        /**
         * Check if taxonomy exists
         *
         * @param $taxonomy
         */
        protected function taxonomy_exists($taxonomy)
        {
        }
        /**
         * Prepare terms if `--by=<field>` flag is used
         *
         * @param array $terms
         * @param string $field
         * @param string $taxonomy
         */
        protected function prepare_terms($field, $terms, $taxonomy)
        {
        }
        /**
         * Set obj_id Class variable
         *
         * @param string $obj_id
         */
        protected function set_obj_id($obj_id)
        {
        }
        /**
         * Get obj_id Class variable
         *
         * @return string
         */
        protected function get_obj_id()
        {
        }
        /**
         * Get obj_type Class variable
         *
         * @return string $obj_type
         */
        protected function get_object_type()
        {
        }
        /**
         * Get Formatter object based on supplied parameters.
         *
         * @param array $assoc_args Parameters passed to command. Determines formatting.
         *
         * @return WP_CLI\Formatter
         */
        protected function get_formatter(&$assoc_args)
        {
        }
    }
}
namespace {
    /**
     * Adds, updates, removes, and lists post terms.
     *
     * ## EXAMPLES
     *
     *     # Set category post term `test` to the post ID 123
     *     $ wp post term set 123 test category
     *     Success: Set term.
     *
     *     # Set category post terms `test` and `apple` to the post ID 123
     *     $ wp post term set 123 test apple category
     *     Success: Set terms.
     *
     *     # List category post terms for the post ID 123
     *     $ wp post term list 123 category --fields=term_id,slug
     *     +---------+-------+
     *     | term_id | slug  |
     *     +---------+-------+
     *     | 2       | apple |
     *     | 3       | test  |
     *     +----------+------+
     *
     *     # Remove category post terms `test` and `apple` for the post ID 123
     *     $ wp post term remove 123 category test apple
     *     Success: Removed terms.
     *
     */
    class Post_Term_Command extends \WP_CLI\CommandWithTerms
    {
        protected $obj_type = 'post';
        private $fetcher;
        public function __construct()
        {
        }
        protected function get_object_type()
        {
        }
    }
    /**
     * Retrieves details on the site's registered post types.
     *
     * Get information on WordPress' built-in and the site's [custom post types](https://developer.wordpress.org/plugins/post-types/).
     *
     * ## EXAMPLES
     *
     *     # Get details about a post type
     *     $ wp post-type get page --fields=name,label,hierarchical --format=json
     *     {"name":"page","label":"Pages","hierarchical":true}
     *
     *     # List post types with 'post' capability type
     *     $ wp post-type list --capability_type=post --fields=name,public
     *     +---------------+--------+
     *     | name          | public |
     *     +---------------+--------+
     *     | post          | 1      |
     *     | attachment    | 1      |
     *     | revision      |        |
     *     | nav_menu_item |        |
     *     +---------------+--------+
     *
     * @package wp-cli
     */
    class Post_Type_Command extends \WP_CLI_Command
    {
        private $fields = array('name', 'label', 'description', 'hierarchical', 'public', 'capability_type');
        /**
         * Gets the post counts for each supplied post type.
         *
         * @param array $post_types Post types to fetch counts for.
         * @return array Associative array of post counts keyed by post type.
         */
        protected function get_counts($post_types)
        {
        }
        /**
         * Lists registered post types.
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : Filter by one or more fields (see get_post_types() first parameter for a list of available fields).
         *
         * [--field=<field>]
         * : Prints the value of a single field for each post type.
         *
         * [--fields=<fields>]
         * : Limit the output to specific post type fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each post type:
         *
         * * name
         * * label
         * * description
         * * hierarchical
         * * public
         * * capability_type
         *
         * These fields are optionally available:
         *
         * * count
         *
         * ## EXAMPLES
         *
         *     # List registered post types
         *     $ wp post-type list --format=csv
         *     name,label,description,hierarchical,public,capability_type
         *     post,Posts,,,1,post
         *     page,Pages,,1,1,page
         *     attachment,Media,,,1,post
         *     revision,Revisions,,,,post
         *     nav_menu_item,"Navigation Menu Items",,,,post
         *
         *     # List post types with 'post' capability type
         *     $ wp post-type list --capability_type=post --fields=name,public
         *     +---------------+--------+
         *     | name          | public |
         *     +---------------+--------+
         *     | post          | 1      |
         *     | attachment    | 1      |
         *     | revision      |        |
         *     | nav_menu_item |        |
         *     +---------------+--------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets details about a registered post type.
         *
         * ## OPTIONS
         *
         * <post-type>
         * : Post type slug
         *
         * [--field=<field>]
         * : Instead of returning the whole taxonomy, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for the specified post type:
         *
         * * name
         * * label
         * * description
         * * hierarchical
         * * public
         * * capability_type
         * * labels
         * * cap
         * * supports
         *
         * These fields are optionally available:
         *
         * * count
         *
         * ## EXAMPLES
         *
         *     # Get details about the 'page' post type.
         *     $ wp post-type get page --fields=name,label,hierarchical --format=json
         *     {"name":"page","label":"Pages","hierarchical":true}
         */
        public function get($args, $assoc_args)
        {
        }
        private function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Manages signups on a multisite installation.
     *
     * ## EXAMPLES
     *
     *     # List signups.
     *     $ wp user signup list
     *     +-----------+------------+---------------------+---------------------+--------+------------------+
     *     | signup_id | user_login | user_email          | registered          | active | activation_key   |
     *     +-----------+------------+---------------------+---------------------+--------+------------------+
     *     | 1         | bobuser    | bobuser@example.com | 2024-03-13 05:46:53 | 1      | 7320b2f009266618 |
     *     | 2         | johndoe    | johndoe@example.com | 2024-03-13 06:24:44 | 0      | 9068d859186cd0b5 |
     *     +-----------+------------+---------------------+---------------------+--------+------------------+
     *
     *     # Activate signup.
     *     $ wp user signup activate 2
     *     Signup 2 activated. Password: bZFSGsfzb9xs
     *     Success: Activated 1 of 1 signups.
     *
     *     # Delete signup.
     *     $ wp user signup delete 3
     *     Signup 3 deleted.
     *     Success: Deleted 1 of 1 signups.
     *
     * @package wp-cli
     */
    class Signup_Command extends \WP_CLI\CommandWithDBObject
    {
        protected $obj_type = 'signup';
        protected $obj_id_key = 'signup_id';
        protected $obj_fields = ['signup_id', 'user_login', 'user_email', 'registered', 'active', 'activation_key'];
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Lists signups.
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : Filter the list by a specific field.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each signup.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * [--per_page=<per_page>]
         * : Limits the signups to the given number. Defaults to none.
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each signup:
         *
         * * signup_id
         * * user_login
         * * user_email
         * * registered
         * * active
         * * activation_key
         *
         * These fields are optionally available:
         *
         * * domain
         * * path
         * * title
         * * activated
         * * meta
         *
         * ## EXAMPLES
         *
         *     # List signup IDs.
         *     $ wp user signup list --field=signup_id
         *     1
         *
         *     # List all signups.
         *     $ wp user signup list
         *     +-----------+------------+---------------------+---------------------+--------+------------------+
         *     | signup_id | user_login | user_email          | registered          | active | activation_key   |
         *     +-----------+------------+---------------------+---------------------+--------+------------------+
         *     | 1         | bobuser    | bobuser@example.com | 2024-03-13 05:46:53 | 1      | 7320b2f009266618 |
         *     | 2         | johndoe    | johndoe@example.com | 2024-03-13 06:24:44 | 0      | 9068d859186cd0b5 |
         *     +-----------+------------+---------------------+---------------------+--------+------------------+
         *
         * @subcommand list
         *
         * @package wp-cli
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets details about a signup.
         *
         * ## OPTIONS
         *
         * <signup>
         * : The signup ID, user login, user email, or activation key.
         *
         * [--field=<field>]
         * : Instead of returning the whole signup, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get signup.
         *     $ wp user signup get 1 --field=user_login
         *     bobuser
         *
         *     # Get signup and export to JSON file.
         *     $ wp user signup get bobuser --format=json > bobuser.json
         *
         * @package wp-cli
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Activates one or more signups.
         *
         * ## OPTIONS
         *
         * <signup>...
         * : The signup ID, user login, user email, or activation key of the signup(s) to activate.
         *
         * ## EXAMPLES
         *
         *     # Activate signup.
         *     $ wp user signup activate 2
         *     Signup 2 activated. Password: bZFSGsfzb9xs
         *     Success: Activated 1 of 1 signups.
         *
         * @package wp-cli
         */
        public function activate($args, $assoc_args)
        {
        }
        /**
         * Deletes one or more signups.
         *
         * ## OPTIONS
         *
         * [<signup>...]
         * : The signup ID, user login, user email, or activation key of the signup(s) to delete.
         *
         * [--all]
         * : If set, all signups will be deleted.
         *
         * ## EXAMPLES
         *
         *     # Delete signup.
         *     $ wp user signup delete 3
         *     Signup 3 deleted.
         *     Success: Deleted 1 of 1 signups.
         *
         * @package wp-cli
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Deletes signup.
         *
         * @param stdClasss $signup
         * @return bool True if success; otherwise false.
         */
        private function delete_signup($signup)
        {
        }
        /**
         * Deletes all signup.
         *
         * @return bool True if success; otherwise false.
         */
        private function delete_all_signups()
        {
        }
    }
    /**
     * Creates, deletes, empties, moderates, and lists one or more sites on a multisite installation.
     *
     * ## EXAMPLES
     *
     *     # Create site
     *     $ wp site create --slug=example
     *     Success: Site 3 created: www.example.com/example/
     *
     *     # Output a simple list of site URLs
     *     $ wp site list --field=url
     *     http://www.example.com/
     *     http://www.example.com/subdir/
     *
     *     # Delete site
     *     $ wp site delete 123
     *     Are you sure you want to delete the 'http://www.example.com/example' site? [y/n] y
     *     Success: The site at 'http://www.example.com/example' was deleted.
     *
     * @package wp-cli
     */
    class Site_Command extends \WP_CLI\CommandWithDBObject
    {
        protected $obj_type = 'site';
        protected $obj_id_key = 'blog_id';
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Delete comments.
         */
        private function empty_comments()
        {
        }
        /**
         * Delete all posts.
         */
        private function empty_posts()
        {
        }
        /**
         * Delete terms, taxonomies, and tax relationships.
         */
        private function empty_taxonomies()
        {
        }
        /**
         * Delete all links, link_category terms, and related cache.
         */
        private function empty_links()
        {
        }
        /**
         * Insert default terms.
         */
        private function insert_default_terms()
        {
        }
        /**
         * Reset option values to default.
         */
        private function reset_options()
        {
        }
        /**
         * Empties a site of its content (posts, comments, terms, and meta).
         *
         * Truncates posts, comments, and terms tables to empty a site of its
         * content. Doesn't affect site configuration (options) or users.
         *
         * If running a persistent object cache, make sure to flush the cache
         * after emptying the site, as the cache values will be invalid otherwise.
         *
         * To also empty custom database tables, you'll need to hook into command
         * execution:
         *
         * ```
         * WP_CLI::add_hook( 'after_invoke:site empty', function(){
         *     global $wpdb;
         *     foreach( array( 'p2p', 'p2pmeta' ) as $table ) {
         *         $table = $wpdb->$table;
         *         $wpdb->query( "TRUNCATE $table" );
         *     }
         * });
         * ```
         *
         * ## OPTIONS
         *
         * [--uploads]
         * : Also delete *all* files in the site's uploads directory.
         *
         * [--yes]
         * : Proceed to empty the site without a confirmation prompt.
         *
         * ## EXAMPLES
         *
         *     $ wp site empty
         *     Are you sure you want to empty the site at http://www.example.com of all posts, links, comments, and terms? [y/n] y
         *     Success: The site at 'http://www.example.com' was emptied.
         *
         * @subcommand empty
         */
        public function empty_($args, $assoc_args)
        {
        }
        /**
         * Deletes a site in a multisite installation.
         *
         * ## OPTIONS
         *
         * [<site-id>]
         * : The id of the site to delete. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be deleted. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * [--yes]
         * : Answer yes to the confirmation message.
         *
         * [--keep-tables]
         * : Delete the blog from the list, but don't drop its tables.
         *
         * ## EXAMPLES
         *
         *     $ wp site delete 123
         *     Are you sure you want to delete the http://www.example.com/example site? [y/n] y
         *     Success: The site at 'http://www.example.com/example' was deleted.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Creates a site in a multisite installation.
         *
         * ## OPTIONS
         *
         * --slug=<slug>
         * : Path for the new site. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * [--title=<title>]
         * : Title of the new site. Default: prettified slug.
         *
         * [--email=<email>]
         * : Email for admin user. User will be created if none exists. Assignment to super admin if not included.
         *
         * [--network_id=<network-id>]
         * : Network to associate new site with. Defaults to current network (typically 1).
         *
         * [--private]
         * : If set, the new site will be non-public (not indexed)
         *
         * [--porcelain]
         * : If set, only the site id will be output on success.
         *
         * ## EXAMPLES
         *
         *     $ wp site create --slug=example
         *     Success: Site 3 created: http://www.example.com/example/
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Generate some sites.
         *
         * Creates a specified number of new sites.
         *
         * ## OPTIONS
         *
         * [--count=<number>]
         * : How many sites to generates?
         * ---
         * default: 100
         * ---
         *
         * [--slug=<slug>]
         * : Path for the new site. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * [--email=<email>]
         * : Email for admin user. User will be created if none exists. Assignment to super admin if not included.
         *
         * [--network_id=<network-id>]
         * : Network to associate new site with. Defaults to current network (typically 1).
         *
         * [--private]
         * : If set, the new site will be non-public (not indexed)
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: progress
         * options:
         *  - progress
         *  - ids
         * ---
         *
         * ## EXAMPLES
         *
         *    # Generate 10 sites.
         *    $ wp site generate --count=10
         *    Generating sites  100% [================================================] 0:01 / 0:04
         */
        public function generate($args, $assoc_args)
        {
        }
        /**
         * Retrieves a list of reserved site on a sub-directory Multisite installation.
         *
         * Works on older WordPress versions where get_subdirectory_reserved_names() does not exist.
         *
         * @return string[] Array of reserved names.
         */
        private function get_subdirectory_reserved_names()
        {
        }
        /**
         * Gets network data for a given id.
         *
         * @param int     $network_id
         * @return bool|array False if no network found with given id, array otherwise
         */
        private function get_network($network_id)
        {
        }
        /**
         * Lists all sites in a multisite installation.
         *
         * ## OPTIONS
         *
         * [--network=<id>]
         * : The network to which the sites belong.
         *
         * [--<field>=<value>]
         * : Filter by one or more fields (see "Available Fields" section). However,
         * 'url' isn't an available filter, as it comes from 'home' in wp_options.
         *
         * [--site__in=<value>]
         * : Only list the sites with these blog_id values (comma-separated).
         *
         * [--site_user=<value>]
         * : Only list the sites with this user.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each site.
         *
         * [--fields=<fields>]
         * : Comma-separated list of fields to show.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - count
         *   - ids
         *   - json
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each site:
         *
         * * blog_id
         * * url
         * * last_updated
         * * registered
         *
         * These fields are optionally available:
         *
         * * site_id
         * * domain
         * * path
         * * public
         * * archived
         * * mature
         * * spam
         * * deleted
         * * lang_id
         *
         * ## EXAMPLES
         *
         *     # Output a simple list of site URLs
         *     $ wp site list --field=url
         *     http://www.example.com/
         *     http://www.example.com/subdir/
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Archives one or more sites.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to archive. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to archive. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site archive 123
         *     Success: Site 123 archived.
         *
         *     $ wp site archive --slug=demo
         *     Success: Site 123 archived.
         */
        public function archive($args, $assoc_args)
        {
        }
        /**
         * Unarchives one or more sites.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to unarchive. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to unarchive. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site unarchive 123
         *     Success: Site 123 unarchived.
         *
         *     $ wp site unarchive --slug=demo
         *     Success: Site 123 unarchived.
         */
        public function unarchive($args, $assoc_args)
        {
        }
        /**
         * Activates one or more sites.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to activate. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be activated. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site activate 123
         *     Success: Site 123 activated.
         *
         *      $ wp site activate --slug=demo
         *      Success: Site 123 marked as activated.
         */
        public function activate($args, $assoc_args)
        {
        }
        /**
         * Deactivates one or more sites.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to deactivate. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be deactivated. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site deactivate 123
         *     Success: Site 123 deactivated.
         *
         *     $ wp site deactivate --slug=demo
         *     Success: Site 123 deactivated.
         */
        public function deactivate($args, $assoc_args)
        {
        }
        /**
         * Marks one or more sites as spam.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to be marked as spam. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be marked as spam. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site spam 123
         *     Success: Site 123 marked as spam.
         */
        public function spam($args, $assoc_args)
        {
        }
        /**
         * Removes one or more sites from spam.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to remove from spam. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be removed from spam. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site unspam 123
         *     Success: Site 123 removed from spam.
         *
         * @subcommand unspam
         */
        public function unspam($args, $assoc_args)
        {
        }
        /**
         * Sets one or more sites as mature.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to set as mature. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be set as mature. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site mature 123
         *     Success: Site 123 marked as mature.
         *
         *     $ wp site mature --slug=demo
         *     Success: Site 123 marked as mature.
         */
        public function mature($args, $assoc_args)
        {
        }
        /**
         * Sets one or more sites as immature.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to set as unmature. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be set as unmature. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site unmature 123
         *     Success: Site 123 marked as unmature.
         *
         *     $ wp site unmature --slug=demo
         *     Success: Site 123 marked as unmature.
         */
        public function unmature($args, $assoc_args)
        {
        }
        /**
         * Sets one or more sites as public.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to set as public. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be set as public. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site public 123
         *     Success: Site 123 marked as public.
         *
         *      $ wp site public --slug=demo
         *      Success: Site 123 marked as public.
         *
         * @subcommand public
         */
        public function set_public($args, $assoc_args)
        {
        }
        /**
         * Sets one or more sites as private.
         *
         * ## OPTIONS
         *
         * [<id>...]
         * : One or more IDs of sites to set as private. If not provided, you must set the --slug parameter.
         *
         * [--slug=<slug>]
         * : Path of the site to be set as private. Subdomain on subdomain installs, directory on subdirectory installs.
         *
         * ## EXAMPLES
         *
         *     $ wp site private 123
         *     Success: Site 123 marked as private.
         *
         *     $ wp site private --slug=demo
         *     Success: Site 123 marked as private.
         *
         * @subcommand private
         */
        public function set_private($args, $assoc_args)
        {
        }
        private function update_site_status($ids, $pref, $value)
        {
        }
        /**
         * Get an array of site IDs from the passed-in arguments or slug parameter.
         *
         * @param array $args Passed-in arguments.
         * @param array $assoc_args Passed-in parameters.
         *
         * @return array Site IDs.
         * @throws ExitException
         */
        private function get_sites_ids($args, $assoc_args)
        {
        }
        /**
         * Check that the site IDs or slug are provided.
         *
         * @param  array  $args  Passed-in arguments.
         * @param  array  $assoc_args  Passed-in parameters.
         *
         * @return bool
         * @throws ExitException If neither site ids nor site slug using --slug were provided.
         */
        private function check_site_ids_and_slug($args, $assoc_args)
        {
        }
    }
    /**
     * Adds, updates, deletes, and lists site custom fields.
     *
     * ## EXAMPLES
     *
     *     # Set site meta
     *     $ wp site meta set 123 bio "Mary is a WordPress developer."
     *     Success: Updated custom field 'bio'.
     *
     *     # Get site meta
     *     $ wp site meta get 123 bio
     *     Mary is a WordPress developer.
     *
     *     # Update site meta
     *     $ wp site meta update 123 bio "Mary is an awesome WordPress developer."
     *     Success: Updated custom field 'bio'.
     *
     *     # Delete site meta
     *     $ wp site meta delete 123 bio
     *     Success: Deleted custom field.
     */
    class Site_Meta_Command extends \WP_CLI\CommandWithMeta
    {
        protected $meta_type = 'blog';
        /**
         * Check that the site ID exists
         *
         * @param int
         */
        protected function check_object_id($object_id)
        {
        }
        /**
         * Wrapper method for add_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param bool   $unique     Optional, default is false. Whether the
         *                           specified metadata key should be unique for the
         *                           object. If true, and the object already has a
         *                           value for the specified metadata key, no change
         *                           will be made.
         *
         * @return int|false The meta ID on success, false on failure.
         */
        protected function add_metadata($object_id, $meta_key, $meta_value, $unique = \false)
        {
        }
        /**
         * Wrapper method for update_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param mixed  $prev_value Optional. If specified, only update existing
         *                           metadata entries with the specified value.
         *                           Otherwise, update all entries.
         *
         * @return int|bool Meta ID if the key didn't exist, true on successful
         *                  update, false on failure.
         */
        protected function update_metadata($object_id, $meta_key, $meta_value, $prev_value = '')
        {
        }
        /**
         * Wrapper method for get_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id ID of the object the metadata is for.
         * @param string $meta_key  Optional. Metadata key. If not specified,
         *                          retrieve all metadata for the specified object.
         * @param bool   $single    Optional, default is false. If true, return only
         *                          the first value of the specified meta_key. This
         *                          parameter has no effect if meta_key is not
         *                          specified.
         *
         * @return mixed Single metadata value, or array of values.
         */
        protected function get_metadata($object_id, $meta_key = '', $single = \false)
        {
        }
        /**
         * Wrapper method for delete_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object metadata is for
         * @param string $meta_key   Metadata key
         * @param mixed $meta_value  Optional. Metadata value. Must be serializable
         *                           if non-scalar. If specified, only delete
         *                           metadata entries with this value. Otherwise,
         *                           delete all entries with the specified meta_key.
         *                           Pass `null, `false`, or an empty string to skip
         *                           this check. For backward compatibility, it is
         *                           not possible to pass an empty string to delete
         *                           those entries with an empty string for a value.
         *
         * @return bool True on successful delete, false on failure.
         */
        protected function delete_metadata($object_id, $meta_key, $meta_value = '')
        {
        }
    }
    /**
     * Adds, updates, deletes, and lists site options in a multisite installation.
     *
     * ## EXAMPLES
     *
     *     # Get site registration
     *     $ wp site option get registration
     *     none
     *
     *     # Add site option
     *     $ wp site option add my_option foobar
     *     Success: Added 'my_option' site option.
     *
     *     # Update site option
     *     $ wp site option update my_option '{"foo": "bar"}' --format=json
     *     Success: Updated 'my_option' site option.
     *
     *     # Delete site option
     *     $ wp site option delete my_option
     *     Success: Deleted 'my_option' site option.
     */
    class Site_Option_Command extends \WP_CLI_Command
    {
        /**
         * Gets a site option.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the site option.
         *
         * [--format=<format>]
         * : Get value in a particular format.
         * ---
         * default: var_export
         * options:
         *   - var_export
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get site upload filetypes
         *     $ wp site option get upload_filetypes
         *     jpg jpeg png gif mov avi mpg
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Adds a site option.
         *
         * ## OPTIONS
         *
         * <key>
         * : The name of the site option to add.
         *
         * [<value>]
         * : The value of the site option to add. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Create a site option by reading a JSON file
         *     $ wp site option add my_option --format=json < config.json
         *     Success: Added 'my_option' site option.
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Lists site options.
         *
         * ## OPTIONS
         *
         * [--search=<pattern>]
         * : Use wildcards ( * and ? ) to match option name.
         *
         * [--site_id=<id>]
         * : Limit options to those of a particular site id.
         *
         * [--field=<field>]
         * : Prints the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : The serialization format for the value. total_bytes displays the total size of matching options in bytes.
         * ---
         * default: table
         * options:
         *   - table
         *   - json
         *   - csv
         *   - count
         *   - yaml
         *   - total_bytes
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * This field will be displayed by default for each matching option:
         *
         * * meta_key
         * * meta_value
         *
         * These fields are optionally available:
         *
         * * meta_id
         * * site_id
         * * size_bytes
         *
         * ## EXAMPLES
         *
         *     # List all site options beginning with "i2f_"
         *     $ wp site option list --search="i2f_*"
         *     +-------------+--------------+
         *     | meta_key    | meta_value   |
         *     +-------------+--------------+
         *     | i2f_version | 0.1.0        |
         *     +-------------+--------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Updates a site option.
         *
         * ## OPTIONS
         *
         * <key>
         * : The name of the site option to update.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Update a site option by reading from a file
         *     $ wp site option update my_option < value.txt
         *     Success: Updated 'my_option' site option.
         *
         * @alias set
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Deletes a site option.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the site option.
         *
         * ## EXAMPLES
         *
         *     $ wp site option delete my_option
         *     Success: Deleted 'my_option' site option.
         */
        public function delete($args)
        {
        }
        /**
         * Gets a nested value from an option.
         *
         * ## OPTIONS
         *
         * <key>
         * : The option name.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to pluck.
         *
         * [--format=<format>]
         * : The output format of the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         *   - yaml
         */
        public function pluck($args, $assoc_args)
        {
        }
        /**
         * Updates a nested value in an option.
         *
         * ## OPTIONS
         *
         * <action>
         * : Patch action to perform.
         * ---
         * options:
         *   - insert
         *   - update
         *   - delete
         * ---
         *
         * <key>
         * : The option name.
         *
         * <key-path>...
         * : The name(s) of the keys within the value to locate the value to patch.
         *
         * [<value>]
         * : The new value. If omitted, the value is read from STDIN.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         */
        public function patch($args, $assoc_args)
        {
        }
        private static function esc_like($old)
        {
        }
    }
    /**
     * Retrieves information about registered taxonomies.
     *
     * See references for [built-in taxonomies](https://developer.wordpress.org/themes/basics/categories-tags-custom-taxonomies/) and [custom taxonomies](https://developer.wordpress.org/plugins/taxonomies/working-with-custom-taxonomies/).
     *
     * ## EXAMPLES
     *
     *     # List all taxonomies with 'post' object type.
     *     $ wp taxonomy list --object_type=post --fields=name,public
     *     +-------------+--------+
     *     | name        | public |
     *     +-------------+--------+
     *     | category    | 1      |
     *     | post_tag    | 1      |
     *     | post_format | 1      |
     *     +-------------+--------+
     *
     *     # Get capabilities of 'post_tag' taxonomy.
     *     $ wp taxonomy get post_tag --field=cap
     *     {"manage_terms":"manage_categories","edit_terms":"manage_categories","delete_terms":"manage_categories","assign_terms":"edit_posts"}
     *
     * @package wp-cli
     */
    class Taxonomy_Command extends \WP_CLI_Command
    {
        private $fields = array('name', 'label', 'description', 'object_type', 'show_tagcloud', 'hierarchical', 'public');
        public function __construct()
        {
        }
        /**
         * Gets the term counts for each supplied taxonomy.
         *
         * @param array $taxonomies Taxonomies to fetch counts for.
         * @return array Associative array of term counts keyed by taxonomy.
         */
        protected function get_counts($taxonomies)
        {
        }
        /**
         * Lists registered taxonomies.
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : Filter by one or more fields (see get_taxonomies() first parameter for a list of available fields).
         *
         * [--field=<field>]
         * : Prints the value of a single field for each taxonomy.
         *
         * [--fields=<fields>]
         * : Limit the output to specific taxonomy fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each term:
         *
         * * name
         * * label
         * * description
         * * object_type
         * * show_tagcloud
         * * hierarchical
         * * public
         *
         * These fields are optionally available:
         *
         * * count
         *
         * ## EXAMPLES
         *
         *     # List all taxonomies.
         *     $ wp taxonomy list --format=csv
         *     name,label,description,object_type,show_tagcloud,hierarchical,public
         *     category,Categories,,post,1,1,1
         *     post_tag,Tags,,post,1,,1
         *     nav_menu,"Navigation Menus",,nav_menu_item,,,
         *     link_category,"Link Categories",,link,1,,
         *     post_format,Format,,post,,,1
         *
         *     # List all taxonomies with 'post' object type.
         *     $ wp taxonomy list --object_type=post --fields=name,public
         *     +-------------+--------+
         *     | name        | public |
         *     +-------------+--------+
         *     | category    | 1      |
         *     | post_tag    | 1      |
         *     | post_format | 1      |
         *     +-------------+--------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets details about a registered taxonomy.
         *
         * ## OPTIONS
         *
         * <taxonomy>
         * : Taxonomy slug.
         *
         * [--field=<field>]
         * : Instead of returning the whole taxonomy, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for the specified taxonomy:
         *
         * * name
         * * label
         * * description
         * * object_type
         * * show_tagcloud
         * * hierarchical
         * * public
         * * labels
         * * cap
         *
         * These fields are optionally available:
         *
         * * count
         *
         * ## EXAMPLES
         *
         *     # Get details of `category` taxonomy.
         *     $ wp taxonomy get category --fields=name,label,object_type
         *     +-------------+------------+
         *     | Field       | Value      |
         *     +-------------+------------+
         *     | name        | category   |
         *     | label       | Categories |
         *     | object_type | ["post"]   |
         *     +-------------+------------+
         *
         *     # Get capabilities of 'post_tag' taxonomy.
         *     $ wp taxonomy get post_tag --field=cap
         *     {"manage_terms":"manage_categories","edit_terms":"manage_categories","delete_terms":"manage_categories","assign_terms":"edit_posts"}
         */
        public function get($args, $assoc_args)
        {
        }
        private function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Manages taxonomy terms and term meta, with create, delete, and list commands.
     *
     * See reference for [taxonomies and their terms](https://codex.wordpress.org/Taxonomies).
     *
     * ## EXAMPLES
     *
     *     # Create a new term.
     *     $ wp term create category Apple --description="A type of fruit"
     *     Success: Created category 199.
     *
     *     # Get details about a term.
     *     $ wp term get category 199 --format=json --fields=term_id,name,slug,count
     *     {"term_id":199,"name":"Apple","slug":"apple","count":1}
     *
     *     # Update an existing term.
     *     $ wp term update category 15 --name=Apple
     *     Success: Term updated.
     *
     *     # Get the term's URL.
     *     $ wp term list post_tag --include=123 --field=url
     *     http://example.com/tag/tips-and-tricks
     *
     *     # Delete post category
     *     $ wp term delete category 15
     *     Success: Deleted category 15.
     *
     *     # Recount posts assigned to each categories and tags
     *     $ wp term recount category post_tag
     *     Success: Updated category term count
     *     Success: Updated post_tag term count
     *
     * @package wp-cli
     */
    class Term_Command extends \WP_CLI_Command
    {
        private $fields = ['term_id', 'term_taxonomy_id', 'name', 'slug', 'description', 'parent', 'count'];
        /**
         * Lists terms in a taxonomy.
         *
         * ## OPTIONS
         *
         * <taxonomy>...
         * : List terms of one or more taxonomies
         *
         * [--<field>=<value>]
         * : Filter by one or more fields (see get_terms() $args parameter for a list of fields).
         *
         * [--field=<field>]
         * : Prints the value of a single field for each term.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each term:
         *
         * * term_id
         * * term_taxonomy_id
         * * name
         * * slug
         * * description
         * * parent
         * * count
         *
         * These fields are optionally available:
         *
         * * url
         *
         * ## EXAMPLES
         *
         *     # List post categories
         *     $ wp term list category --format=csv
         *     term_id,term_taxonomy_id,name,slug,description,parent,count
         *     2,2,aciform,aciform,,0,1
         *     3,3,antiquarianism,antiquarianism,,0,1
         *     4,4,arrangement,arrangement,,0,1
         *     5,5,asmodeus,asmodeus,,0,1
         *
         *     # List post tags
         *     $ wp term list post_tag --fields=name,slug
         *     +-----------+-------------+
         *     | name      | slug        |
         *     +-----------+-------------+
         *     | 8BIT      | 8bit        |
         *     | alignment | alignment-2 |
         *     | Articles  | articles    |
         *     | aside     | aside       |
         *     +-----------+-------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Creates a new term.
         *
         * ## OPTIONS
         *
         * <taxonomy>
         * : Taxonomy for the new term.
         *
         * <term>
         * : A name for the new term.
         *
         * [--slug=<slug>]
         * : A unique slug for the new term. Defaults to sanitized version of name.
         *
         * [--description=<description>]
         * : A description for the new term.
         *
         * [--parent=<term-id>]
         * : A parent for the new term.
         *
         * [--porcelain]
         * : Output just the new term id.
         *
         * ## EXAMPLES
         *
         *     # Create a new category "Apple" with a description.
         *     $ wp term create category Apple --description="A type of fruit"
         *     Success: Created category 199.
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Gets details about a term.
         *
         * ## OPTIONS
         *
         * <taxonomy>
         * : Taxonomy of the term to get
         *
         * <term>
         * : ID or slug of the term to get
         *
         * [--by=<field>]
         * : Explicitly handle the term value as a slug or id.
         * ---
         * default: id
         * options:
         *   - slug
         *   - id
         * ---
         *
         * [--field=<field>]
         * : Instead of returning the whole term, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get details about a category with id 199.
         *     $ wp term get category 199 --format=json
         *     {"term_id":199,"name":"Apple","slug":"apple","term_group":0,"term_taxonomy_id":199,"taxonomy":"category","description":"A type of fruit","parent":0,"count":0,"filter":"raw"}
         *
         *     # Get details about a category with slug apple.
         *     $ wp term get category apple --by=slug --format=json
         *     {"term_id":199,"name":"Apple","slug":"apple","term_group":0,"term_taxonomy_id":199,"taxonomy":"category","description":"A type of fruit","parent":0,"count":0,"filter":"raw"}
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Updates an existing term.
         *
         * ## OPTIONS
         *
         * <taxonomy>
         * : Taxonomy of the term to update.
         *
         * <term>
         * : ID or slug for the term to update.
         *
         * [--by=<field>]
         * : Explicitly handle the term value as a slug or id.
         * ---
         * default: id
         * options:
         *   - slug
         *   - id
         * ---
         *
         * [--name=<name>]
         * : A new name for the term.
         *
         * [--slug=<slug>]
         * : A new slug for the term.
         *
         * [--description=<description>]
         * : A new description for the term.
         *
         * [--parent=<term-id>]
         * : A new parent for the term.
         *
         * ## EXAMPLES
         *
         *     # Change category with id 15 to use the name "Apple"
         *     $ wp term update category 15 --name=Apple
         *     Success: Term updated.
         *
         *     # Change category with slug apple to use the name "Apple"
         *     $ wp term update category apple --by=slug --name=Apple
         *     Success: Term updated.
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Deletes an existing term.
         *
         * Errors if the term doesn't exist, or there was a problem in deleting it.
         *
         * ## OPTIONS
         *
         * <taxonomy>
         * : Taxonomy of the term to delete.
         *
         * <term>...
         * : One or more IDs or slugs of terms to delete.
         *
         * [--by=<field>]
         * : Explicitly handle the term value as a slug or id.
         * ---
         * default: id
         * options:
         *   - slug
         *   - id
         * ---
         *
         * ## EXAMPLES
         *
         *     # Delete post category by id
         *     $ wp term delete category 15
         *     Deleted category 15.
         *     Success: Deleted 1 of 1 terms.
         *
         *     # Delete post category by slug
         *     $ wp term delete category apple --by=slug
         *     Deleted category 15.
         *     Success: Deleted 1 of 1 terms.
         *
         *     # Delete all post tags
         *     $ wp term list post_tag --field=term_id | xargs wp term delete post_tag
         *     Deleted post_tag 159.
         *     Deleted post_tag 160.
         *     Deleted post_tag 161.
         *     Success: Deleted 3 of 3 terms.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Generates some terms.
         *
         * Creates a specified number of new terms with dummy data.
         *
         * ## OPTIONS
         *
         * <taxonomy>
         * : The taxonomy for the generated terms.
         *
         * [--count=<number>]
         * : How many terms to generate?
         * ---
         * default: 100
         * ---
         *
         * [--max_depth=<number>]
         * : Generate child terms down to a certain depth.
         * ---
         * default: 1
         * ---
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: progress
         * options:
         *   - progress
         *   - ids
         * ---
         *
         * ## EXAMPLES
         *
         *     # Generate post categories.
         *     $ wp term generate category --count=10
         *     Generating terms  100% [=========] 0:02 / 0:02
         *
         *     # Add meta to every generated term.
         *     $ wp term generate category --format=ids --count=3 | xargs -d ' ' -I % wp term meta add % foo bar
         *     Success: Added custom field.
         *     Success: Added custom field.
         *     Success: Added custom field.
         */
        public function generate($args, $assoc_args)
        {
        }
        /**
         * Recalculates number of posts assigned to each term.
         *
         * In instances where manual updates are made to the terms assigned to
         * posts in the database, the number of posts associated with a term
         * can become out-of-sync with the actual number of posts.
         *
         * This command runs wp_update_term_count() on the taxonomy's terms
         * to bring the count back to the correct value.
         *
         * ## OPTIONS
         *
         * <taxonomy>...
         * : One or more taxonomies to recalculate.
         *
         * ## EXAMPLES
         *
         *     # Recount posts assigned to each categories and tags
         *     $ wp term recount category post_tag
         *     Success: Updated category term count.
         *     Success: Updated post_tag term count.
         *
         *     # Recount all listed taxonomies
         *     $ wp taxonomy list --field=name | xargs wp term recount
         *     Success: Updated category term count.
         *     Success: Updated post_tag term count.
         *     Success: Updated nav_menu term count.
         *     Success: Updated link_category term count.
         *     Success: Updated post_format term count.
         */
        public function recount($args)
        {
        }
        /**
         * Migrate a term of a taxonomy to another taxonomy.
         *
         * ## OPTIONS
         *
         * <term>
         * : Slug or ID of the term to migrate.
         *
         * [--by=<field>]
         * : Explicitly handle the term value as a slug or id.
         * ---
         * default: id
         * options:
         *   - slug
         *   - id
         * ---
         *
         * [--from=<taxonomy>]
         * : Taxonomy slug of the term to migrate.
         *
         * [--to=<taxonomy>]
         * : Taxonomy slug to migrate to.
         *
         * ## EXAMPLES
         *
         *     # Migrate a category's term (video) to tag taxonomy.
         *     $ wp term migrate 9190 --from=category --to=post_tag
         *     Term 'video' assigned to post 1155.
         *     Term 'video' migrated.
         *     Old instance of term 'video' removed from its original taxonomy.
         *     Success: Migrated the term 'video' from taxonomy 'category' to taxonomy 'post_tag' for 1 post.
         */
        public function migrate($args, $assoc_args)
        {
        }
        private function maybe_make_child()
        {
        }
        private function maybe_reset_depth()
        {
        }
        private function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Adds, updates, deletes, and lists term custom fields.
     *
     * ## EXAMPLES
     *
     *     # Set term meta
     *     $ wp term meta set 123 bio "Mary is a WordPress developer."
     *     Success: Updated custom field 'bio'.
     *
     *     # Get term meta
     *     $ wp term meta get 123 bio
     *     Mary is a WordPress developer.
     *
     *     # Update term meta
     *     $ wp term meta update 123 bio "Mary is an awesome WordPress developer."
     *     Success: Updated custom field 'bio'.
     *
     *     # Delete term meta
     *     $ wp term meta delete 123 bio
     *     Success: Deleted custom field.
     */
    class Term_Meta_Command extends \WP_CLI\CommandWithMeta
    {
        protected $meta_type = 'term';
        /**
         * Check that the term ID exists
         *
         * @param int
         */
        protected function check_object_id($object_id)
        {
        }
        /**
         * Wrapper method for add_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param bool   $unique     Optional, default is false. Whether the
         *                           specified metadata key should be unique for the
         *                           object. If true, and the object already has a
         *                           value for the specified metadata key, no change
         *                           will be made.
         *
         * @return int|false The meta ID on success, false on failure.
         */
        protected function add_metadata($object_id, $meta_key, $meta_value, $unique = \false)
        {
        }
        /**
         * Wrapper method for update_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param mixed  $prev_value Optional. If specified, only update existing
         *                           metadata entries with the specified value.
         *                           Otherwise, update all entries.
         *
         * @return int|bool Meta ID if the key didn't exist, true on successful
         *                  update, false on failure.
         */
        protected function update_metadata($object_id, $meta_key, $meta_value, $prev_value = '')
        {
        }
        /**
         * Wrapper method for get_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id ID of the object the metadata is for.
         * @param string $meta_key  Optional. Metadata key. If not specified,
         *                          retrieve all metadata for the specified object.
         * @param bool   $single    Optional, default is false. If true, return only
         *                          the first value of the specified meta_key. This
         *                          parameter has no effect if meta_key is not
         *                          specified.
         *
         * @return mixed Single metadata value, or array of values.
         */
        protected function get_metadata($object_id, $meta_key = '', $single = \false)
        {
        }
        /**
         * Wrapper method for delete_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object metadata is for
         * @param string $meta_key   Metadata key
         * @param mixed $meta_value  Optional. Metadata value. Must be serializable
         *                           if non-scalar. If specified, only delete
         *                           metadata entries with this value. Otherwise,
         *                           delete all entries with the specified meta_key.
         *                           Pass `null, `false`, or an empty string to skip
         *                           this check. For backward compatibility, it is
         *                           not possible to pass an empty string to delete
         *                           those entries with an empty string for a value.
         *
         * @return bool True on successful delete, false on failure.
         */
        protected function delete_metadata($object_id, $meta_key, $meta_value = '')
        {
        }
    }
    /**
     * Creates, updates, deletes, lists and retrieves application passwords.
     *
     * ## EXAMPLES
     *
     *     # List user application passwords and only show app name and password hash
     *     $ wp user application-password list 123 --fields=name,password
     *     +--------+------------------------------------+
     *     | name   | password                           |
     *     +--------+------------------------------------+
     *     | myapp  | $P$BVGeou1CUot114YohIemgpwxQCzb8O/ |
     *     +--------+------------------------------------+
     *
     *     # Get a specific application password and only show app name and created timestamp
     *     $ wp user application-password get 123 6633824d-c1d7-4f79-9dd5-4586f734d69e --fields=name,created
     *     +--------+------------+
     *     | name   | created    |
     *     +--------+------------+
     *     | myapp  | 1638395611 |
     *     +--------+------------+
     *
     *     # Create user application password
     *     $ wp user application-password create 123 myapp
     *     Success: Created application password.
     *     Password: ZG1bxdxdzjTwhsY8vK8l1C65
     *
     *     # Only print the password without any chrome
     *     $ wp user application-password create 123 myapp --porcelain
     *     ZG1bxdxdzjTwhsY8vK8l1C65
     *
     *     # Update an existing application password
     *     $ wp user application-password update 123 6633824d-c1d7-4f79-9dd5-4586f734d69e --name=newappname
     *     Success: Updated application password.
     *
     *     # Delete an existing application password
     *     $ wp user application-password delete 123 6633824d-c1d7-4f79-9dd5-4586f734d69e
     *     Success: Deleted 1 of 1 application password.
     *
     *     # Check if an application password for a given application exists
     *     $ wp user application-password exists 123 myapp
     *     $ echo $?
     *     1
     *
     *     # Bash script for checking whether an application password exists and creating one if not
     *     if ! wp user application-password exists 123 myapp; then
     *         PASSWORD=$(wp user application-password create 123 myapp --porcelain)
     *     fi
     */
    final class User_Application_Password_Command
    {
        /**
         * List of application password fields.
         *
         * @var array<string>
         */
        const APPLICATION_PASSWORD_FIELDS = ['uuid', 'app_id', 'name', 'password', 'created', 'last_used', 'last_ip'];
        /**
         * Lists all application passwords associated with a user.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to get application passwords for.
         *
         * [--<field>=<value>]
         * : Filter the list by a specific field.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each application password.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         *   - ids
         * ---
         *
         * [--orderby=<fields>]
         * : Set orderby which field.
         * ---
         * default: created
         * options:
         *  - uuid
         *  - app_id
         *  - name
         *  - password
         *  - created
         *  - last_used
         *  - last_ip
         * ---
         *
         * [--order=<order>]
         * : Set ascending or descending order.
         * ---
         * default: desc
         * options:
         *  - asc
         *  - desc
         * ---
         *
         * ## EXAMPLES
         *
         *     # List user application passwords and only show app name and password hash
         *     $ wp user application-password list 123 --fields=name,password
         *     +--------+------------------------------------+
         *     | name   | password                           |
         *     +--------+------------------------------------+
         *     | myapp  | $P$BVGeou1CUot114YohIemgpwxQCzb8O/ |
         *     +--------+------------------------------------+
         *
         * @subcommand list
         *
         * @param array $args       Indexed array of positional arguments.
         * @param array $assoc_args Associative array of associative arguments.
         * @throws ExitException If the user could not be found/parsed.
         * @throws ExitException If the application passwords could not be retrieved.
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets a specific application password.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to get the application password for.
         *
         * <uuid>
         * : The universally unique ID of the application password.
         *
         * [--field=<field>]
         * : Prints the value of a single field for the application password.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get a specific application password and only show app name and created timestamp
         *     $ wp user application-password get 123 6633824d-c1d7-4f79-9dd5-4586f734d69e --fields=name,created
         *     +--------+------------+
         *     | name   | created    |
         *     +--------+------------+
         *     | myapp  | 1638395611 |
         *     +--------+------------+
         *
         * @param array $args       Indexed array of positional arguments.
         * @param array $assoc_args Associative array of associative arguments.
         * @throws ExitException If the application passwords could not be retrieved.
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Creates a new application password.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to create a new application password for.
         *
         * <app-name>
         * : Unique name of the application to create an application password for.
         *
         * [--app-id=<app-id>]
         * : Application ID to attribute to the application password.
         *
         * [--porcelain]
         * : Output just the new password.
         *
         * ## EXAMPLES
         *
         *     # Create user application password
         *     $ wp user application-password create 123 myapp
         *     Success: Created application password.
         *     Password: ZG1bxdxdzjTwhsY8vK8l1C65
         *
         *     # Only print the password without any chrome
         *     $ wp user application-password create 123 myapp --porcelain
         *     ZG1bxdxdzjTwhsY8vK8l1C65
         *
         *     # Create user application with a custom application ID for internal tracking
         *     $ wp user application-password create 123 myapp --app-id=42 --porcelain
         *     ZG1bxdxdzjTwhsY8vK8l1C65
         *
         * @param array $args       Indexed array of positional arguments.
         * @param array $assoc_args Associative array of associative arguments.
         * @throws ExitException If the application password could not be created.
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Updates an existing application password.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to update the application password for.
         *
         * <uuid>
         * : The universally unique ID of the application password.
         *
         * [--<field>=<value>]
         * : Update the <field> with a new <value>. Currently supported fields: name.
         *
         * ## EXAMPLES
         *
         *     # Update an existing application password
         *     $ wp user application-password update 123 6633824d-c1d7-4f79-9dd5-4586f734d69e --name=newappname
         *     Success: Updated application password.
         *
         * @param array $args       Indexed array of positional arguments.
         * @param array $assoc_args Associative array of associative arguments.
         * @throws ExitException If the application password could not be created.
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Record usage of an application password.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to update the application password for.
         *
         * <uuid>
         * : The universally unique ID of the application password.
         *
         * ## EXAMPLES
         *
         *     # Record usage of an application password
         *     $ wp user application-password record-usage 123 6633824d-c1d7-4f79-9dd5-4586f734d69e
         *     Success: Recorded application password usage.
         *
         * @subcommand record-usage
         *
         * @param array $args       Indexed array of positional arguments.
         * @param array $assoc_args Associative array of associative arguments.
         * @throws ExitException If the application password could not be created.
         */
        public function record_usage($args, $assoc_args)
        {
        }
        /**
         * Delete an existing application password.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to delete the application password for.
         *
         * [<uuid>...]
         * : Comma-separated list of UUIDs of the application passwords to delete.
         *
         * [--all]
         * : Delete all of the user's application password.
         *
         * ## EXAMPLES
         *
         *     # Delete an existing application password
         *     $ wp user application-password delete 123 6633824d-c1d7-4f79-9dd5-4586f734d69e
         *     Success: Deleted 1 of 1 application password.
         *
         *     # Delete all of the user's application passwords
         *     $ wp user application-password delete 123 --all
         *     Success: Deleted all application passwords.
         *
         * @param array $args       Indexed array of positional arguments.
         * @param array $assoc_args Associative array of associative arguments.
         * @throws ExitException If the application password could not be created.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Checks whether an application password for a given application exists.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to check the existence of an application password for.
         *
         * <app-name>
         * : Name of the application to check the existence of an application password for.
         *
         * ## EXAMPLES
         *
         *     # Check if an application password for a given application exists
         *     $ wp user application-password exists 123 myapp
         *     $ echo $?
         *     1
         *
         *     # Bash script for checking whether an application password exists and creating one if not
         *     if ! wp user application-password exists 123 myapp; then
         *         PASSWORD=$(wp user application-password create 123 myapp --porcelain)
         *     fi
         *
         * @param array $args       Indexed array of positional arguments.
         * @param array $assoc_args Associative array of associative arguments.
         * @throws ExitException If the application password could not be created.
         */
        public function exists($args, $assoc_args)
        {
        }
        /**
         * Replaces user_login value with user ID.
         *
         * @param array $args Associative array of arguments.
         * @return array Associative array of arguments with the user login replaced with an ID.
         * @throws ExitException If the user is not found.
         */
        private function replace_login_with_user_id($args)
        {
        }
        /**
         * Checks if application name exists for the given user.
         *
         * This is a polyfill for WP_Application_Passwords::get_user_application_passwords(), which was only added for
         * WordPress 5.7+, but we're already supporting application passwords for WordPress 5.6+.
         *
         * @param int    $user_id  User ID to check the application passwords for.
         * @param string $app_name Application name to look for.
         * @return bool
         */
        private function application_name_exists_for_user($user_id, $app_name)
        {
        }
    }
    /**
     * Manages users, along with their roles, capabilities, and meta.
     *
     * See references for [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities) and [WP User class](https://codex.wordpress.org/Class_Reference/WP_User).
     *
     * ## EXAMPLES
     *
     *     # List user IDs
     *     $ wp user list --field=ID
     *     1
     *
     *     # Create a new user.
     *     $ wp user create bob bob@example.com --role=author
     *     Success: Created user 3.
     *     Password: k9**&I4vNH(&
     *
     *     # Update an existing user.
     *     $ wp user update 123 --display_name=Mary --user_pass=marypass
     *     Success: Updated user 123.
     *
     *     # Delete user 123 and reassign posts to user 567
     *     $ wp user delete 123 --reassign=567
     *     Success: Removed user 123 from http://example.com.
     *
     * @package wp-cli
     */
    class User_Command extends \WP_CLI\CommandWithDBObject
    {
        protected $obj_type = 'user';
        protected $obj_fields = ['ID', 'user_login', 'display_name', 'user_email', 'user_registered', 'roles'];
        private $cap_fields = ['name'];
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Lists users.
         *
         * Display WordPress users based on all arguments supported by
         * [WP_User_Query()](https://developer.wordpress.org/reference/classes/wp_user_query/prepare_query/).
         *
         * ## OPTIONS
         *
         * [--role=<role>]
         * : Only display users with a certain role.
         *
         * [--<field>=<value>]
         * : Control output by one or more arguments of WP_User_Query().
         *
         * [--network]
         * : List all users in the network for multisite.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each user.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each user:
         *
         * * ID
         * * user_login
         * * display_name
         * * user_email
         * * user_registered
         * * roles
         *
         * These fields are optionally available:
         *
         * * user_pass
         * * user_nicename
         * * user_url
         * * user_activation_key
         * * user_status
         * * spam
         * * deleted
         * * caps
         * * cap_key
         * * allcaps
         * * filter
         * * url
         *
         * ## EXAMPLES
         *
         *     # List user IDs
         *     $ wp user list --field=ID
         *     1
         *
         *     # List users with administrator role
         *     $ wp user list --role=administrator --format=csv
         *     ID,user_login,display_name,user_email,user_registered,roles
         *     1,supervisor,supervisor,supervisor@gmail.com,"2016-06-03 04:37:00",administrator
         *
         *     # List users with only given fields
         *     $ wp user list --fields=display_name,user_email --format=json
         *     [{"display_name":"supervisor","user_email":"supervisor@gmail.com"}]
         *
         *     # List users ordered by the 'last_activity' meta value.
         *     $ wp user list --meta_key=last_activity --orderby=meta_value_num
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets details about a user.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * [--field=<field>]
         * : Instead of returning the whole user, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Get a specific subset of the user's fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get user
         *     $ wp user get 12 --field=login
         *     supervisor
         *
         *     # Get user and export to JSON file
         *     $ wp user get bob --format=json > bob.json
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Deletes one or more users from the current site.
         *
         * On multisite, `wp user delete` only removes the user from the current
         * site. Include `--network` to also remove the user from the database, but
         * make sure to reassign their posts prior to deleting the user.
         *
         * ## OPTIONS
         *
         * <user>...
         * : The user login, user email, or user ID of the user(s) to delete.
         *
         * [--network]
         * : On multisite, delete the user from the entire network.
         *
         * [--reassign=<user-id>]
         * : User ID to reassign the posts to.
         *
         * [--yes]
         * : Answer yes to any confirmation prompts.
         *
         * ## EXAMPLES
         *
         *     # Delete user 123 and reassign posts to user 567
         *     $ wp user delete 123 --reassign=567
         *     Success: Removed user 123 from http://example.com.
         *
         *     # Delete all contributors and reassign their posts to user 2
         *     $ wp user delete $(wp user list --role=contributor --field=ID) --reassign=2
         *     Success: Removed user 813 from http://example.com.
         *     Success: Removed user 578 from http://example.com.
         *
         *     # Delete all contributors in batches of 100 (avoid error: argument list too long: wp)
         *     $ wp user delete $(wp user list --role=contributor --field=ID | head -n 100)
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Creates a new user.
         *
         * ## OPTIONS
         *
         * <user-login>
         * : The login of the user to create.
         *
         * <user-email>
         * : The email address of the user to create.
         *
         * [--role=<role>]
         * : The role of the user to create. Default: default role. Possible values
         * include 'administrator', 'editor', 'author', 'contributor', 'subscriber'.
         *
         * [--user_pass=<password>]
         * : The user password. Default: randomly generated.
         *
         * [--user_registered=<yyyy-mm-dd-hh-ii-ss>]
         * : The date the user registered. Default: current date.
         *
         * [--display_name=<name>]
         * : The display name.
         *
         * [--user_nicename=<nice_name>]
         * : A string that contains a URL-friendly name for the user. The default is the user's username.
         *
         * [--user_url=<url>]
         * : A string containing the user's URL for the user's web site.
         *
         * [--nickname=<nickname>]
         * : The user's nickname, defaults to the user's username.
         *
         * [--first_name=<first_name>]
         * : The user's first name.
         *
         * [--last_name=<last_name>]
         * : The user's last name.
         *
         * [--description=<description>]
         * : A string containing content about the user.
         *
         * [--rich_editing=<rich_editing>]
         * : A string for whether to enable the rich editor or not. False if not empty.
         *
         * [--send-email]
         * : Send an email to the user with their new account details.
         *
         * [--porcelain]
         * : Output just the new user id.
         *
         * ## EXAMPLES
         *
         *     # Create user
         *     $ wp user create bob bob@example.com --role=author
         *     Success: Created user 3.
         *     Password: k9**&I4vNH(&
         *
         *     # Create user without showing password upon success
         *     $ wp user create ann ann@example.com --porcelain
         *     4
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Updates an existing user.
         *
         * ## OPTIONS
         *
         * <user>...
         * : The user login, user email or user ID of the user(s) to update.
         *
         * [--user_pass=<password>]
         * : A string that contains the plain text password for the user.
         *
         * [--user_nicename=<nice_name>]
         * : A string that contains a URL-friendly name for the user. The default is the user's username.
         *
         * [--user_url=<url>]
         * : A string containing the user's URL for the user's web site.
         *
         * [--user_email=<email>]
         * : A string containing the user's email address.
         *
         * [--display_name=<display_name>]
         * : A string that will be shown on the site. Defaults to user's username.
         *
         * [--nickname=<nickname>]
         * : The user's nickname, defaults to the user's username.
         *
         * [--first_name=<first_name>]
         * : The user's first name.
         *
         * [--last_name=<last_name>]
         * : The user's last name.
         *
         * [--description=<description>]
         * : A string containing content about the user.
         *
         * [--rich_editing=<rich_editing>]
         * : A string for whether to enable the rich editor or not. False if not empty.
         *
         * [--user_registered=<yyyy-mm-dd-hh-ii-ss>]
         * : The date the user registered.
         *
         * [--role=<role>]
         * : A string used to set the user's role.
         *
         * --<field>=<value>
         * : One or more fields to update. For accepted fields, see wp_update_user().
         *
         * [--skip-email]
         * : Don't send an email notification to the user.
         *
         * ## EXAMPLES
         *
         *     # Update user
         *     $ wp user update 123 --display_name=Mary --user_pass=marypass
         *     Success: Updated user 123.
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Generates some users.
         *
         * Creates a specified number of new users with dummy data.
         *
         * ## OPTIONS
         *
         * [--count=<number>]
         * : How many users to generate?
         * ---
         * default: 100
         * ---
         *
         * [--role=<role>]
         * : The role of the generated users. Default: default role from WP
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: progress
         * options:
         *   - progress
         *   - ids
         * ---
         *
         * ## EXAMPLES
         *
         *     # Add meta to every generated users.
         *     $ wp user generate --format=ids --count=3 | xargs -d ' ' -I % wp user meta add % foo bar
         *     Success: Added custom field.
         *     Success: Added custom field.
         *     Success: Added custom field.
         */
        public function generate($args, $assoc_args)
        {
        }
        /**
         * Verifies whether a user exists.
         *
         * Displays a success message if the user does exist.
         *
         * ## OPTIONS
         *
         * <id>
         * : The ID of the user to check.
         *
         * ## EXAMPLES
         *
         *     # The user exists.
         *     $ wp user exists 1337
         *     Success: User with ID 1337 exists.
         *     $ echo $?
         *     0
         *
         *     # The user does not exist.
         *     $ wp user exists 10000
         *     $ echo $?
         *     1
         */
        public function exists($args)
        {
        }
        /**
         * Sets the user role.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * [<role>]
         * : Make the user have the specified role. If not passed, the default role is
         * used.
         *
         * ## EXAMPLES
         *
         *     $ wp user set-role 12 author
         *     Success: Added johndoe (12) to http://example.com as author.
         *
         * @subcommand set-role
         */
        public function set_role($args, $assoc_args)
        {
        }
        /**
         * Adds a role for a user.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * [<role>...]
         * : Add the specified role(s) to the user.
         *
         * ## EXAMPLES
         *
         *     $ wp user add-role 12 author
         *     Success: Added 'author' role for johndoe (12).
         *
         *     $ wp user add-role 12 author editor
         *     Success: Added 'author', 'editor' roles for johndoe (12).
         *
         * @subcommand add-role
         */
        public function add_role($args, $assoc_args)
        {
        }
        /**
         * Removes a user's role.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * [<role>...]
         * : Remove the specified role(s) from the user.
         *
         * ## EXAMPLES
         *
         *     $ wp user remove-role 12 author
         *     Success: Removed 'author' role for johndoe (12).
         *
         *     $ wp user remove-role 12 author editor
         *     Success: Removed 'author', 'editor' roles for johndoe (12).
         *
         * @subcommand remove-role
         */
        public function remove_role($args, $assoc_args)
        {
        }
        /**
         * Adds a capability to a user.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * <cap>
         * : The capability to add.
         *
         * ## EXAMPLES
         *
         *     # Add a capability for a user
         *     $ wp user add-cap john create_premium_item
         *     Success: Added 'create_premium_item' capability for john (16).
         *
         *     # Add a capability for a user
         *     $ wp user add-cap 15 edit_product
         *     Success: Added 'edit_product' capability for johndoe (15).
         *
         * @subcommand add-cap
         */
        public function add_cap($args, $assoc_args)
        {
        }
        /**
         * Removes a user's capability.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * <cap>
         * : The capability to be removed.
         *
         * [--force]
         * : Forcefully remove a capability.
         *
         * ## EXAMPLES
         *
         *     $ wp user remove-cap 11 publish_newsletters
         *     Success: Removed 'publish_newsletters' cap for supervisor (11).
         *
         *     $ wp user remove-cap 11 publish_posts
         *     Error: The 'publish_posts' cap for supervisor (11) is inherited from a role.
         *
         *     $ wp user remove-cap 11 nonexistent_cap
         *     Error: No such 'nonexistent_cap' cap for supervisor (11).
         *
         *     $ wp user remove-cap 11 publish_newsletters --force
         *     Success: Removed 'publish_newsletters' cap for supervisor (11).
         *
         * @subcommand remove-cap
         */
        public function remove_cap($args, $assoc_args)
        {
        }
        /**
         * Lists all capabilities for a user.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or login.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: list
         * options:
         *   - list
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * [--origin=<origin>]
         * : Render output in a particular format.
         * ---
         * default: all
         * options:
         *   - all
         *   - user
         *   - role
         * ---
         *
         * [--exclude-role-names]
         * : Exclude capabilities that match role names from output.
         *
         * ## EXAMPLES
         *
         *     $ wp user list-caps 21
         *     edit_product
         *     create_premium_item
         *
         * @subcommand list-caps
         */
        public function list_caps($args, $assoc_args)
        {
        }
        /**
         * Imports users from a CSV file.
         *
         * If the user already exists (matching the email address or login), then
         * the user is updated unless the `--skip-update` flag is used.
         *
         * ## OPTIONS
         *
         * <file>
         * : The local or remote CSV file of users to import. If '-', then reads from STDIN.
         *
         * [--send-email]
         * : Send an email to new users with their account details.
         *
         * [--skip-update]
         * : Don't update users that already exist.
         *
         * ## EXAMPLES
         *
         *     # Import users from local CSV file
         *     $ wp user import-csv /path/to/users.csv
         *     Success: bobjones created.
         *     Success: newuser1 created.
         *     Success: existinguser created.
         *
         *     # Import users from remote CSV file
         *     $ wp user import-csv http://example.com/users.csv
         *
         *     Sample users.csv file:
         *
         *     user_login,user_email,display_name,role
         *     bobjones,bobjones@example.com,Bob Jones,contributor
         *     newuser1,newuser1@example.com,New User,author
         *     existinguser,existinguser@example.com,Existing User,administrator
         *
         * @subcommand import-csv
         */
        public function import_csv($args, $assoc_args)
        {
        }
        /**
         * Resets the password for one or more users.
         *
         * ## OPTIONS
         *
         * <user>...
         * : one or more user logins or IDs.
         *
         * [--skip-email]
         * : Don't send an email notification to the affected user(s).
         *
         * [--show-password]
         * : Show the new password(s).
         *
         * [--porcelain]
         * : Output only the new password(s).
         *
         * ## EXAMPLES
         *
         *     # Reset the password for two users and send them the change email.
         *     $ wp user reset-password admin editor
         *     Reset password for admin.
         *     Reset password for editor.
         *     Success: Passwords reset for 2 users.
         *
         *     # Reset and display the password.
         *     $ wp user reset-password editor --show-password
         *     Reset password for editor.
         *     Password: N6hAau0fXZMN#rLCIirdEGOh
         *     Success: Password reset for 1 user.
         *
         *     # Reset the password for one user, displaying only the new password, and not sending the change email.
         *     $ wp user reset-password admin --skip-email --porcelain
         *     yV6BP*!d70wg
         *
         *     # Reset password for all users.
         *     $ wp user reset-password $(wp user list --format=ids)
         *     Reset password for admin.
         *     Reset password for editor.
         *     Reset password for subscriber.
         *     Success: Passwords reset for 3 users.
         *
         *     # Reset password for all users with a particular role.
         *     $ wp user reset-password $(wp user list --format=ids --role=administrator)
         *     Reset password for admin.
         *     Success: Password reset for 1 user.
         *
         * @subcommand reset-password
         */
        public function reset_password($args, $assoc_args)
        {
        }
        /**
         * Checks whether the role is valid
         *
         * @param $role string
         * @param $warn_user_only bool
         */
        private static function validate_role($role, $warn_user_only = \false)
        {
        }
        /**
         * Accommodates three different behaviors for wp_new_user_notification()
         * - 4.3.1 and above: expect second argument to be deprecated
         * - 4.3: Second argument was repurposed as $notify
         * - Below 4.3: Send the password in the notification
         *
         * @param string $user_id
         * @param string $password
         */
        public static function wp_new_user_notification($user_id, $password)
        {
        }
        /**
         * Marks one or more users as spam on multisite.
         *
         * ## OPTIONS
         *
         * <user>...
         * : The user login, user email, or user ID of the user(s) to mark as spam.
         *
         * ## EXAMPLES
         *
         *     # Mark user as spam.
         *     $ wp user spam 123
         *     User 123 marked as spam.
         *     Success: Spammed 1 of 1 users.
         */
        public function spam($args)
        {
        }
        /**
         * Removes one or more users from spam on multisite.
         *
         * ## OPTIONS
         *
         * <user>...
         * : The user login, user email, or user ID of the user(s) to remove from spam.
         *
         * ## EXAMPLES
         *
         *     # Remove user from spam.
         *     $ wp user unspam 123
         *     User 123 removed from spam.
         *     Success: Unspamed 1 of 1 users.
         */
        public function unspam($args)
        {
        }
        /**
         * Common command for updating user data.
         */
        private function update_msuser_status($user_ids, $pref, $value)
        {
        }
        /**
         * Checks if a user's password is valid or not.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email or user ID of the user to check credentials for.
         *
         * <user_pass>
         * : A string that contains the plain text password for the user.
         *
         * [--escape-chars]
         * : Escape password with `wp_slash()` to mimic the same behavior as `wp-login.php`.
         *
         * ## EXAMPLES
         *
         *     # Check whether given credentials are valid; exit status 0 if valid, otherwise 1
         *     $ wp user check-password admin adminpass
         *     $ echo $?
         *     1
         *
         *     # Bash script for checking whether given credentials are valid or not
         *     if ! $(wp user check-password admin adminpass); then
         *      notify-send "Invalid Credentials";
         *     fi
         *
         * @subcommand check-password
         */
        public function check_password($args, $assoc_args)
        {
        }
    }
    /**
     * Adds, updates, deletes, and lists user custom fields.
     *
     * ## EXAMPLES
     *
     *     # Add user meta
     *     $ wp user meta add 123 bio "Mary is an WordPress developer."
     *     Success: Added custom field.
     *
     *     # List user meta
     *     $ wp user meta list 123 --keys=nickname,description,wp_capabilities
     *     +---------+-----------------+--------------------------------+
     *     | user_id | meta_key        | meta_value                     |
     *     +---------+-----------------+--------------------------------+
     *     | 123     | nickname        | supervisor                     |
     *     | 123     | description     | Mary is a WordPress developer. |
     *     | 123     | wp_capabilities | {"administrator":true}         |
     *     +---------+-----------------+--------------------------------+
     *
     *     # Update user meta
     *     $ wp user meta update 123 bio "Mary is an awesome WordPress developer."
     *     Success: Updated custom field 'bio'.
     *
     *     # Delete user meta
     *     $ wp user meta delete 123 bio
     *     Success: Deleted custom field.
     */
    class User_Meta_Command extends \WP_CLI\CommandWithMeta
    {
        protected $meta_type = 'user';
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Lists all metadata associated with a user.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to get metadata for.
         *
         * [--keys=<keys>]
         * : Limit output to metadata of specific keys.
         *
         * [--fields=<fields>]
         * : Limit the output to specific row fields. Defaults to id,meta_key,meta_value.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * [--orderby=<fields>]
         * : Set orderby which field.
         * ---
         * default: id
         * options:
         *  - id
         *  - meta_key
         *  - meta_value
         * ---
         *
         * [--order=<order>]
         * : Set ascending or descending order.
         * ---
         * default: asc
         * options:
         *  - asc
         *  - desc
         * ---
         *
         * [--unserialize]
         * : Unserialize meta_value output.
         *
         * ## EXAMPLES
         *
         *     # List user meta
         *     $ wp user meta list 123 --keys=nickname,description,wp_capabilities
         *     +---------+-----------------+--------------------------------+
         *     | user_id | meta_key        | meta_value                     |
         *     +---------+-----------------+--------------------------------+
         *     | 123     | nickname        | supervisor                     |
         *     | 123     | description     | Mary is a WordPress developer. |
         *     | 123     | wp_capabilities | {"administrator":true}         |
         *     +---------+-----------------+--------------------------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets meta field value.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to get metadata for.
         *
         * <key>
         * : The metadata key.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get user meta
         *     $ wp user meta get 123 bio
         *     Mary is an WordPress developer.
         *
         *     # Get the primary site of a user (for multisite)
         *     $ wp user meta get 2 primary_blog
         *     3
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Deletes a meta field.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to delete metadata from.
         *
         * <key>
         * : The metadata key.
         *
         * [<value>]
         * : The value to delete. If omitted, all rows with key will deleted.
         *
         * ## EXAMPLES
         *
         *     # Delete user meta
         *     $ wp user meta delete 123 bio
         *     Success: Deleted custom field.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Adds a meta field.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to add metadata for.
         *
         * <key>
         * : The metadata key.
         *
         * <value>
         * : The new metadata value.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Add user meta
         *     $ wp user meta add 123 bio "Mary is an WordPress developer."
         *     Success: Added custom field.
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Updates a meta field.
         *
         * ## OPTIONS
         *
         * <user>
         * : The user login, user email, or user ID of the user to update metadata for.
         *
         * <key>
         * : The metadata key.
         *
         * <value>
         * : The new metadata value.
         *
         * [--format=<format>]
         * : The serialization format for the value.
         * ---
         * default: plaintext
         * options:
         *   - plaintext
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Update user meta
         *     $ wp user meta update 123 bio "Mary is an awesome WordPress developer."
         *     Success: Updated custom field 'bio'.
         *
         * @alias set
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Wrapper method for add_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param bool   $unique     Optional, default is false. Whether the
         *                           specified metadata key should be unique for the
         *                           object. If true, and the object already has a
         *                           value for the specified metadata key, no change
         *                           will be made.
         *
         * @return int|false The meta ID on success, false on failure.
         */
        protected function add_metadata($object_id, $meta_key, $meta_value, $unique = \false)
        {
        }
        /**
         * Wrapper method for update_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object the metadata is for.
         * @param string $meta_key   Metadata key to use.
         * @param mixed  $meta_value Metadata value. Must be serializable if
         *                           non-scalar.
         * @param mixed  $prev_value Optional. If specified, only update existing
         *                           metadata entries with the specified value.
         *                           Otherwise, update all entries.
         *
         * @return int|bool Meta ID if the key didn't exist, true on successful
         *                  update, false on failure.
         */
        protected function update_metadata($object_id, $meta_key, $meta_value, $prev_value = '')
        {
        }
        /**
         * Wrapper method for get_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id ID of the object the metadata is for.
         * @param string $meta_key  Optional. Metadata key. If not specified,
         *                          retrieve all metadata for the specified object.
         * @param bool   $single    Optional, default is false. If true, return only
         *                          the first value of the specified meta_key. This
         *                          parameter has no effect if meta_key is not
         *                          specified.
         *
         * @return mixed Single metadata value, or array of values.
         */
        protected function get_metadata($object_id, $meta_key = '', $single = \false)
        {
        }
        /**
         * Wrapper method for delete_metadata that can be overridden in sub classes.
         *
         * @param int    $object_id  ID of the object metadata is for
         * @param string $meta_key   Metadata key
         * @param mixed $meta_value  Optional. Metadata value. Must be serializable
         *                           if non-scalar. If specified, only delete
         *                           metadata entries with this value. Otherwise,
         *                           delete all entries with the specified meta_key.
         *                           Pass `null, `false`, or an empty string to skip
         *                           this check. For backward compatibility, it is
         *                           not possible to pass an empty string to delete
         *                           those entries with an empty string for a value.
         *
         * @return bool True on successful delete, false on failure.
         */
        protected function delete_metadata($object_id, $meta_key, $meta_value = '')
        {
        }
        /**
         * Replaces user_login value with user ID
         * user meta is a special case that also supports user_login
         *
         * @param array
         * @return array
         */
        private function replace_login_with_user_id($args)
        {
        }
    }
    /**
     * Destroys and lists a user's sessions.
     *
     * ## EXAMPLES
     *
     *     # List a user's sessions.
     *     $ wp user session list admin@example.com --format=csv
     *     login_time,expiration_time,ip,ua
     *     "2016-01-01 12:34:56","2016-02-01 12:34:56",127.0.0.1,"Mozilla/5.0..."
     *
     *     # Destroy the most recent session of the given user.
     *     $ wp user session destroy admin
     *     Success: Destroyed session. 3 sessions remaining.
     *
     * @package wp-cli
     */
    class User_Session_Command extends \WP_CLI_Command
    {
        private $fields = ['token', 'login_time', 'expiration_time', 'ip', 'ua'];
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Destroy a session for the given user.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * [<token>]
         * : The token of the session to destroy. Defaults to the most recently created session.
         *
         * [--all]
         * : Destroy all of the user's sessions.
         *
         * ## EXAMPLES
         *
         *     # Destroy the most recent session of the given user.
         *     $ wp user session destroy admin
         *     Success: Destroyed session. 3 sessions remaining.
         *
         *     # Destroy a specific session of the given user.
         *     $ wp user session destroy admin e073ad8540a9c2...
         *     Success: Destroyed session. 2 sessions remaining.
         *
         *     # Destroy all the sessions of the given user.
         *     $ wp user session destroy admin --all
         *     Success: Destroyed all sessions.
         *
         *     # Destroy all sessions for all users.
         *     $ wp user list --field=ID | xargs -n 1 wp user session destroy --all
         *     Success: Destroyed all sessions.
         *     Success: Destroyed all sessions.
         */
        public function destroy($args, $assoc_args)
        {
        }
        /**
         * List sessions for the given user.
         *
         * Note: The `token` field does not return the actual token, but a hash of
         * it. The real token is not persisted and can only be found in the
         * corresponding cookies on the client side.
         *
         * ## OPTIONS
         *
         * <user>
         * : User ID, user email, or user login.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         *   - count
         *   - ids
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each session:
         *
         * * token
         * * login_time
         * * expiration_time
         * * ip
         * * ua
         *
         * These fields are optionally available:
         *
         * * expiration
         * * login
         *
         * ## EXAMPLES
         *
         *     # List a user's sessions.
         *     $ wp user session list admin@example.com --format=csv
         *     login_time,expiration_time,ip,ua
         *     "2016-01-01 12:34:56","2016-02-01 12:34:56",127.0.0.1,"Mozilla/5.0..."
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        protected function get_all_sessions(\WP_Session_Tokens $manager)
        {
        }
        protected function destroy_session(\WP_Session_Tokens $manager, $token)
        {
        }
        private function get_formatter(&$assoc_args)
        {
        }
    }
    /**
     * Adds, updates, removes, and lists user terms.
     *
     * ## EXAMPLES
     *
     *     # Set user terms
     *     $ wp user term set 123 test category
     *     Success: Set terms.
     */
    class User_Term_Command extends \WP_CLI\CommandWithTerms
    {
        protected $obj_type = 'user';
    }
    class EvalFile_Command extends \WP_CLI_Command
    {
        /**
         * Regular expression pattern to match the shell shebang.
         *
         * @var string
         */
        const SHEBANG_PATTERN = '/^(#!.*)$/m';
        /**
         * Loads and executes a PHP file.
         *
         * Note: because code is executed within a method, global variables need
         * to be explicitly globalized.
         *
         * ## OPTIONS
         *
         * <file>
         * : The path to the PHP file to execute.  Use '-' to run code from STDIN.
         *
         * [<arg>...]
         * : One or more positional arguments to pass to the file. They are placed in the $args variable.
         *
         * [--skip-wordpress]
         * : Load and execute file without loading WordPress.
         *
         * [--use-include]
         * : Process the provided file via include instead of evaluating its contents.
         *
         * @when before_wp_load
         *
         * ## EXAMPLES
         *
         *     # Execute file my-code.php and pass value1 and value2 arguments.
         *     # Access arguments in $args array ($args[0] = value1, $args[1] = value2).
         *     $ wp eval-file my-code.php value1 value2
         */
        public function __invoke($args, $assoc_args)
        {
        }
        /**
         * Evaluate a provided file.
         *
         * @param string $file Filepath to execute, or - for STDIN.
         * @param mixed  $args Array of positional arguments to pass to the file.
         * @param bool $use_include Process the provided file via include instead of evaluating its contents.
         */
        private static function execute_eval($file, $args, $use_include)
        {
        }
    }
    class Eval_Command extends \WP_CLI_Command
    {
        /**
         * Executes arbitrary PHP code.
         *
         * Note: because code is executed within a method, global variables need
         * to be explicitly globalized.
         *
         * ## OPTIONS
         *
         * <php-code>
         * : The code to execute, as a string.
         *
         * [--skip-wordpress]
         * : Execute code without loading WordPress.
         *
         * ## EXAMPLES
         *
         *     # Display WordPress content directory.
         *     $ wp eval 'echo WP_CONTENT_DIR;'
         *     /var/www/wordpress/wp-content
         *
         *     # Generate a random number.
         *     $ wp eval 'echo rand();' --skip-wordpress
         *     479620423
         *
         * @when before_wp_load
         */
        public function __invoke($args, $assoc_args)
        {
        }
    }
    /**
     * Exports WordPress content to a WXR file.
     *
     * ## EXAMPLES
     *
     *     # Export posts published by the user between given start and end date
     *     $ wp export --dir=/tmp/ --user=admin --post_type=post --start_date=2011-01-01 --end_date=2011-12-31
     *     Starting export process...
     *     Writing to file /tmp/staging.wordpress.2016-05-24.000.xml
     *     Success: All done with export.
     *
     * @package wp-cli
     */
    class Export_Command extends \WP_CLI_Command
    {
        /**
         * Initialize the array of arguments that will be eventually be passed to export_wp.
         *
         * @var array
         */
        public $export_args = [];
        private $stdout;
        private $max_file_size;
        private $include_once;
        private $wxr_path;
        /**
         * Exports WordPress content to a WXR file.
         *
         * Generates one or more WXR files containing authors, terms, posts,
         * comments, and attachments. WXR files do not include site configuration
         * (options) or the attachment files themselves.
         *
         * ## OPTIONS
         *
         * [--dir=<dirname>]
         * : Full path to directory where WXR export files should be stored. Defaults
         * to current working directory.
         *
         * [--stdout]
         * : Output the whole XML using standard output (incompatible with --dir=)
         *
         * [--skip_comments]
         * : Don't include comments in the WXR export file.
         *
         * [--max_file_size=<MB>]
         * : A single export file should have this many megabytes. -1 for unlimited.
         * ---
         * default: 15
         * ---
         *
         * [--filename_format=<format>]
         * : Use a custom format for export filenames. Defaults to '{site}.wordpress.{date}.{n}.xml'.
         *
         * [--include_once=<before_posts>]
         * : Include specified export section only in the first export file. Valid options
         * are categories, tags, nav_menu_items, custom_taxonomies_terms. Separate multiple
         * sections with a comma. Defaults to none.
         *
         * [--allow_orphan_terms]
         * : Export orphaned terms with `parent=0`, instead of throwing an exception.
         *
         * ## FILTERS
         *
         * [--start_date=<date>]
         * : Export only posts published after this date, in format YYYY-MM-DD.
         *
         * [--end_date=<date>]
         * : Export only posts published before this date, in format YYYY-MM-DD.
         *
         * [--post_type=<post-type>]
         * : Export only posts with this post_type. Separate multiple post types with a
         * comma.
         * ---
         * default: any
         * ---
         *
         * [--post_type__not_in=<post-type>]
         * : Export all post types except those identified. Separate multiple post types
         * with a comma. Defaults to none.
         *
         * [--post__in=<pid>]
         * : Export all posts specified as a comma-separated or space-separated list of IDs.
         * Post's attachments won't be exported unless --with_attachments is specified.
         *
         * [--with_attachments]
         * : Force including attachments in case --post__in has been specified.
         *
         * [--start_id=<pid>]
         * : Export only posts with IDs greater than or equal to this post ID.
         *
         * [--max_num_posts=<num>]
         * : Export no more than <num> posts (excluding attachments).
         *
         * [--author=<author>]
         * : Export only posts by this author. Can be either user login or user ID.
         *
         * [--category=<name|id>]
         * : Export only posts in this category.
         *
         * [--post_status=<status>]
         * : Export only posts with this status.
         *
         * ## EXAMPLES
         *
         *     # Export posts published by the user between given start and end date
         *     $ wp export --dir=/tmp/ --user=admin --post_type=post --start_date=2011-01-01 --end_date=2011-12-31
         *     Starting export process...
         *     Writing to file /tmp/staging.wordpress.2016-05-24.000.xml
         *     Success: All done with export.
         *
         *     # Export posts by IDs
         *     $ wp export --dir=/tmp/ --post__in=123,124,125
         *     Starting export process...
         *     Writing to file /tmp/staging.wordpress.2016-05-24.000.xml
         *     Success: All done with export.
         *
         *     # Export a random subset of content
         *     $ wp export --post__in="$(wp post list --post_type=post --orderby=rand --posts_per_page=8 --format=ids)"
         *     Starting export process...
         *     Writing to file /var/www/example.com/public_html/staging.wordpress.2016-05-24.000.xml
         *     Success: All done with export.
         */
        public function __invoke($_, $assoc_args)
        {
        }
        private static function get_filename_template($filename_format)
        {
        }
        public static function load_export_api()
        {
        }
        private function validate_args($args)
        {
        }
        private function check_dir($path)
        {
        }
        private function check_start_date($date)
        {
        }
        private function check_end_date($date)
        {
        }
        private function check_post_type($post_type)
        {
        }
        private function check_post_type__not_in($post_type)
        {
        }
        private function check_post__in($post__in)
        {
        }
        private function check_start_id($start_id)
        {
        }
        private function check_author($author)
        {
        }
        private function check_max_num_posts($num)
        {
        }
        private function check_category($category)
        {
        }
        private function check_post_status($status)
        {
        }
        private function check_skip_comments($skip)
        {
        }
        private function check_max_file_size($size)
        {
        }
        private function check_include_once($once)
        {
        }
        private function check_allow_orphan_terms($allow_orphan_terms)
        {
        }
    }
    abstract class WP_Export_Base_Writer
    {
        protected $formatter;
        public function __construct($formatter)
        {
        }
        public function export()
        {
        }
        abstract protected function write($xml);
    }
    class WP_Export_Exception extends \RuntimeException
    {
    }
    class WP_Export_File_Writer extends \WP_Export_Base_Writer
    {
        private $f;
        private $file_name;
        public function __construct($formatter, $file_name)
        {
        }
        public function export()
        {
        }
        protected function write($xml)
        {
        }
    }
    class WP_Export_Oxymel extends \Oxymel
    {
        public function optional($tag_name, $contents)
        {
        }
        public function optional_cdata($tag_name, $contents)
        {
        }
        public function cdata($text)
        {
        }
    }
    /**
     * Represents a set of posts and other site data to be exported.
     *
     * An immutable object, which gathers all data needed for the export.
     */
    class WP_Export_Query
    {
        const QUERY_CHUNK = 100;
        private static $defaults = ['post_ids' => \null, 'post_type' => \null, 'status' => \null, 'author' => \null, 'start_date' => \null, 'end_date' => \null, 'start_id' => \null, 'max_num_posts' => \null, 'category' => \null, 'allow_orphan_terms' => \null];
        private $post_ids;
        private $filters;
        private $wheres = [];
        private $joins = [];
        private $author;
        private $category;
        public $missing_parents = \false;
        public function __construct($filters = [])
        {
        }
        public function post_ids()
        {
        }
        public function charset()
        {
        }
        public function site_metadata()
        {
        }
        public function wp_generator_tag()
        {
        }
        public function authors()
        {
        }
        public function categories()
        {
        }
        public function tags()
        {
        }
        public function custom_taxonomies_terms()
        {
        }
        public function nav_menu_terms()
        {
        }
        public function exportify_post($post)
        {
        }
        public function posts()
        {
        }
        private function calculate_post_ids()
        {
        }
        private function post_type_where()
        {
        }
        private function status_where()
        {
        }
        private function author_where()
        {
        }
        private function start_date_where()
        {
        }
        private function end_date_where()
        {
        }
        private function start_id_where()
        {
        }
        private function get_timestamp_for_the_last_day_of_a_month($yyyy_mm)
        {
        }
        private function category_where()
        {
        }
        private function max_num_posts()
        {
        }
        private function include_attachment_ids($post_ids)
        {
        }
        private function bloginfo_rss($section)
        {
        }
        private function find_user_from_any_object($user)
        {
        }
        private function find_category_from_any_object($category)
        {
        }
        private static function topologically_sort_terms($terms)
        {
        }
        private function process_orphaned_terms($terms)
        {
        }
        private static function get_terms_for_post($post)
        {
        }
        private static function get_meta_for_post($post)
        {
        }
        private function get_comments_for_post($post)
        {
        }
    }
    class WP_Export_Returner extends \WP_Export_Base_Writer
    {
        private $result = '';
        public function export()
        {
        }
        protected function write($xml)
        {
        }
    }
    class WP_Export_Split_Files_Writer extends \WP_Export_Base_Writer
    {
        private $max_file_size;
        private $destination_directory;
        private $filename_template;
        private $before_posts_xml;
        private $after_posts_xml;
        private $f;
        private $next_file_number = 0;
        private $current_file_size = 0;
        private $available_sections = array('header', 'site_metadata', 'authors', 'categories', 'tags', 'nav_menu_terms', 'custom_taxonomies_terms', 'rss2_head_action');
        private $subsequent_sections = array();
        public function __construct($formatter, $writer_args = [])
        {
        }
        public function export()
        {
        }
        protected function write($xml)
        {
        }
        private function start_new_file()
        {
        }
        private function close_current_file()
        {
        }
        private function next_file_name()
        {
        }
        private function next_file_path()
        {
        }
    }
    class WP_Export_Term_Exception extends \RuntimeException
    {
    }
    //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress constant.
    /**
     * Responsible for formatting the data in WP_Export_Query to WXR
     */
    class WP_Export_WXR_Formatter
    {
        public function __construct($export)
        {
        }
        public function before_posts($requested_sections = [])
        {
        }
        public function posts()
        {
        }
        public function after_posts()
        {
        }
        public function header()
        {
        }
        public function site_metadata()
        {
        }
        public function authors()
        {
        }
        public function categories()
        {
        }
        public function tags()
        {
        }
        public function nav_menu_terms()
        {
        }
        public function custom_taxonomies_terms()
        {
        }
        public function rss2_head_action()
        {
        }
        public function post($post)
        {
        }
        public function footer()
        {
        }
        protected function terms($terms)
        {
        }
        protected function comment_meta($comment)
        {
        }
    }
    class WP_Export_XML_Over_HTTP extends \WP_Export_Base_Writer
    {
        private $file_name;
        public function __construct($formatter, $file_name)
        {
        }
        public function export()
        {
        }
        protected function write($xml)
        {
        }
        protected function get_export()
        {
        }
        protected function send_headers()
        {
        }
    }
    class WP_Iterator_Exception extends \Exception
    {
    }
    class WP_Map_Iterator extends \IteratorIterator
    {
        public function __construct($iterator, $callback)
        {
        }
        #[\ReturnTypeWillChange]
        public function current()
        {
        }
    }
    class WP_Post_IDs_Iterator implements \Iterator
    {
        private $limit = 100;
        private $post_ids;
        private $ids_left;
        private $results = array();
        public function __construct($post_ids, $limit = \null)
        {
        }
        public function current()
        {
        }
        public function key()
        {
        }
        public function next()
        {
        }
        public function rewind()
        {
        }
        public function valid()
        {
        }
        private function load_next_posts_from_db()
        {
        }
    }
}
namespace WP_CLI {
    trait ParsePluginNameInput
    {
        /**
         * If have optional args ([<plugin>...]) and an all option, then check have something to do.
         *
         * @param array  $args Passed-in arguments.
         * @param bool   $all All flag.
         * @param string $verb Optional. Verb to use. Defaults to 'install'.
         * @return array Same as $args if not all, otherwise all slugs.
         * @param string $exclude Comma separated list of plugin slugs.
         * @throws ExitException If neither plugin name nor --all were provided.
         */
        protected function check_optional_args_and_all($args, $all, $verb = 'install', $exclude = null)
        {
        }
        /**
         * Gets all available plugins.
         *
         * Uses the same filter core uses in plugins.php to determine which plugins
         * should be available to manage through the WP_Plugins_List_Table class.
         *
         * @return array
         */
        private function get_all_plugins()
        {
        }
    }
}
namespace {
    /**
     * Manages plugin auto-updates.
     *
     * ## EXAMPLES
     *
     *     # Enable the auto-updates for a plugin
     *     $ wp plugin auto-updates enable hello
     *     Plugin auto-updates for 'hello' enabled.
     *     Success: Enabled 1 of 1 plugin auto-updates.
     *
     *     # Disable the auto-updates for a plugin
     *     $ wp plugin auto-updates disable hello
     *     Plugin auto-updates for 'hello' disabled.
     *     Success: Disabled 1 of 1 plugin auto-updates.
     *
     *     # Get the status of plugin auto-updates
     *     $ wp plugin auto-updates status hello
     *     Auto-updates for plugin 'hello' are disabled.
     *
     * @package wp-cli
     */
    class Plugin_AutoUpdates_Command
    {
        use \WP_CLI\ParsePluginNameInput;
        /**
         * Site option that stores the status of plugin auto-updates.
         *
         * @var string
         */
        const SITE_OPTION = 'auto_update_plugins';
        /**
         * Plugin fetcher instance.
         *
         * @var Plugin
         */
        protected $fetcher;
        /**
         * Plugin_AutoUpdates_Command constructor.
         */
        public function __construct()
        {
        }
        /**
         * Enables the auto-updates for a plugin.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to enable auto-updates for.
         *
         * [--all]
         * : If set, auto-updates will be enabled for all plugins.
         *
         * [--disabled-only]
         * : If set, filters list of plugins to only include the ones that have
         * auto-updates disabled.
         *
         * ## EXAMPLES
         *
         *     # Enable the auto-updates for a plugin
         *     $ wp plugin auto-updates enable hello
         *     Plugin auto-updates for 'hello' enabled.
         *     Success: Enabled 1 of 1 plugin auto-updates.
         */
        public function enable($args, $assoc_args)
        {
        }
        /**
         * Disables the auto-updates for a plugin.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to disable auto-updates for.
         *
         * [--all]
         * : If set, auto-updates will be disabled for all plugins.
         *
         * [--enabled-only]
         * : If set, filters list of plugins to only include the ones that have
         * auto-updates enabled.
         *
         * ## EXAMPLES
         *
         *     # Disable the auto-updates for a plugin
         *     $ wp plugin auto-updates disable hello
         *     Plugin auto-updates for 'hello' disabled.
         *     Success: Disabled 1 of 1 plugin auto-updates.
         */
        public function disable($args, $assoc_args)
        {
        }
        /**
         * Shows the status of auto-updates for a plugin.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to show the status of the auto-updates of.
         *
         * [--all]
         * : If set, the status of auto-updates for all plugins will be shown.
         *
         * [--enabled-only]
         * : If set, filters list of plugins to only include the ones that have
         * auto-updates enabled.
         *
         * [--disabled-only]
         * : If set, filters list of plugins to only include the ones that have
         * auto-updates disabled.
         *
         * [--field=<field>]
         * : Only show the provided field.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         *   - count
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get the status of plugin auto-updates
         *     $ wp plugin auto-updates status hello
         *     +-------+----------+
         *     | name  | status   |
         *     +-------+----------+
         *     | hello | disabled |
         *     +-------+----------+
         *
         *     # Get the list of plugins that have auto-updates enabled
         *     $ wp plugin auto-updates status --all --enabled-only --field=name
         *     akismet
         *     duplicate-post
         */
        public function status($args, $assoc_args)
        {
        }
    }
}
namespace WP_CLI {
    abstract class CommandWithUpgrade extends \WP_CLI_Command
    {
        protected $fetcher;
        protected $item_type;
        protected $obj_fields;
        protected $upgrade_refresh;
        protected $upgrade_transient;
        protected $chained_command = false;
        /**
         * The GitHub Releases public api endpoint.
         *
         * @var string
         */
        private $github_releases_api_endpoint = 'https://api.github.com/repos/%s/releases';
        /**
         * The GitHub latest release url format.
         *
         * @var string
         */
        private $github_latest_release_url = '/^https:\/\/github\.com\/(.*)\/releases\/latest\/?$/';
        // Invalid version message.
        const INVALID_VERSION_MESSAGE = 'version higher than expected';
        public function __construct()
        {
        }
        abstract protected function get_upgrader_class($force);
        abstract protected function get_item_list();
        /**
         * @param array List of update candidates
         * @param array List of item names
         * @return array List of update candidates
         */
        abstract protected function filter_item_list($items, $args);
        abstract protected function get_all_items();
        abstract protected function get_status($file);
        abstract protected function status_single($args);
        abstract protected function install_from_repo($slug, $assoc_args);
        public function status($args)
        {
        }
        private function status_all()
        {
        }
        private function get_padding($items)
        {
        }
        private function show_legend($items)
        {
        }
        public function install($args, $assoc_args)
        {
        }
        /**
         * Prepare an API response for downloading a particular version of an item.
         *
         * @param object $response wordpress.org API response
         * @param string $version The desired version of the package
         */
        protected static function alter_api_response($response, $version)
        {
        }
        protected function get_upgrader($assoc_args)
        {
        }
        protected function update_many($args, $assoc_args)
        {
        }
        // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Whitelisting to provide backward compatibility to classes possibly extending this class.
        protected function _list($_, $assoc_args)
        {
        }
        /**
         * Check whether an item has an update available or not.
         *
         * @param string $slug The plugin/theme slug
         *
         * @return bool
         */
        protected function has_update($slug)
        {
        }
        /**
         * Get the available update info
         *
         * @return mixed
         */
        protected function get_update_info()
        {
        }
        private $map = ['short' => ['inactive' => 'I', 'active' => 'A', 'active-network' => 'N', 'must-use' => 'M', 'parent' => 'P', 'dropin' => 'D'], 'long' => ['inactive' => 'Inactive', 'active' => 'Active', 'active-network' => 'Network Active', 'must-use' => 'Must Use', 'parent' => 'Parent', 'dropin' => 'Drop-In']];
        protected function format_status($status, $format)
        {
        }
        private function get_color($status)
        {
        }
        /**
         * Get the minor or patch version for plugins and themes with available updates
         *
         * @param array  $items    Items with updates.
         * @param string $type     Either 'minor' or 'patch'.
         * @param bool   $insecure Whether to retry without certificate validation on TLS handshake failure.
         * @param bool   $require_stable Whether to require stable version when comparing versions.
         * @param string $item_type Item type, either 'plugin' or 'theme'.
         * @return array
         */
        private function get_minor_or_patch_updates($items, $type, $insecure, $require_stable, $item_type)
        {
        }
        /**
         * Search wordpress.org repo.
         *
         * @param  array $args       A arguments array containing the search term in the first element.
         * @param  array $assoc_args Data passed in from command.
         */
        // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Whitelisting to provide backward compatibility to classes possibly extending this class.
        protected function _search($args, $assoc_args)
        {
        }
        protected function get_formatter(&$assoc_args)
        {
        }
        /**
         * Error handler to ignore failures on accessing SSL "https://api.wordpress.org/themes/update-check/1.1/" in `wp_update_themes()`
         * and "https://api.wordpress.org/plugins/update-check/1.1/" in `wp_update_plugins()` which seem to occur intermittently.
         */
        public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext = null)
        {
        }
        /**
         * Retrieves PHP_URL_HOST component from URL.
         *
         * @param int $component The component to retrieve.
         *
         * @return string
         */
        private function parse_url_host_component($url, $component)
        {
        }
        /**
         * Add versioned GitHub URLs to cache allowlist.
         *
         * @param string $url The URL to check.
         */
        protected static function maybe_cache($url, $item_type)
        {
        }
        /**
         * Get the latest package version based on a given repo slug.
         *
         * @param string $repo_slug
         *
         * @return array{ name: string, url: string }|\WP_Error
         */
        protected function get_the_latest_github_version($repo_slug)
        {
        }
        /**
         * Get the asset URL from the release array. When the asset is not present, we fallback to the zipball_url (source code) property.
         */
        private function get_asset_url_from_release($release)
        {
        }
        /**
         * Get the GitHub repo from the URL.
         *
         * @param string $url
         *
         * @return string|null
         */
        protected function get_github_repo_from_releases_url($url)
        {
        }
        /**
         * Build the error message we display in WP-CLI for the API Rate limiting error response.
         *
         * @param $decoded_body
         *
         * @return string
         */
        private function build_rate_limiting_error_message($decoded_body)
        {
        }
    }
}
namespace {
    /**
     * Manages plugins, including installs, activations, and updates.
     *
     * See the WordPress [Plugin Handbook](https://developer.wordpress.org/plugins/) developer resource for more information on plugins.
     *
     * ## EXAMPLES
     *
     *     # Activate plugin
     *     $ wp plugin activate hello
     *     Plugin 'hello' activated.
     *     Success: Activated 1 of 1 plugins.
     *
     *     # Deactivate plugin
     *     $ wp plugin deactivate hello
     *     Plugin 'hello' deactivated.
     *     Success: Deactivated 1 of 1 plugins.
     *
     *     # Delete plugin
     *     $ wp plugin delete hello
     *     Deleted 'hello' plugin.
     *     Success: Deleted 1 of 1 plugins.
     *
     *     # Install the latest version from wordpress.org and activate
     *     $ wp plugin install bbpress --activate
     *     Installing bbPress (2.5.9)
     *     Downloading install package from https://downloads.wordpress.org/plugin/bbpress.2.5.9.zip...
     *     Using cached file '/home/vagrant/.wp-cli/cache/plugin/bbpress-2.5.9.zip'...
     *     Unpacking the package...
     *     Installing the plugin...
     *     Plugin installed successfully.
     *     Activating 'bbpress'...
     *     Plugin 'bbpress' activated.
     *     Success: Installed 1 of 1 plugins.
     *
     * @package wp-cli
     */
    class Plugin_Command extends \WP_CLI\CommandWithUpgrade
    {
        use \WP_CLI\ParsePluginNameInput;
        protected $item_type = 'plugin';
        protected $upgrade_refresh = 'wp_update_plugins';
        protected $upgrade_transient = 'update_plugins';
        protected $check_wporg = ['status' => \false, 'last_updated' => \false];
        protected $check_headers = ['tested_up_to' => \false];
        protected $obj_fields = array('name', 'status', 'update', 'version', 'update_version', 'auto_update');
        /**
         * Plugin fetcher instance.
         *
         * @var \WP_CLI\Fetchers\Plugin
         */
        protected $fetcher;
        public function __construct()
        {
        }
        protected function get_upgrader_class($force)
        {
        }
        /**
         * Reveals the status of one or all plugins.
         *
         * ## OPTIONS
         *
         * [<plugin>]
         * : A particular plugin to show the status for.
         *
         * ## EXAMPLES
         *
         *     # Displays status of all plugins
         *     $ wp plugin status
         *     5 installed plugins:
         *       I akismet                3.1.11
         *       I easy-digital-downloads 2.5.16
         *       A theme-check            20160523.1
         *       I wen-logo-slider        2.0.3
         *       M ns-pack                1.0.0
         *     Legend: I = Inactive, A = Active, M = Must Use
         *
         *     # Displays status of a plugin
         *     $ wp plugin status theme-check
         *     Plugin theme-check details:
         *         Name: Theme Check
         *         Status: Active
         *         Version: 20160523.1
         *         Author: Otto42, pross
         *         Description: A simple and easy way to test your theme for all the latest WordPress standards and practices. A great theme development tool!
         */
        public function status($args)
        {
        }
        /**
         * Searches the WordPress.org plugin directory.
         *
         * Displays plugins in the WordPress.org plugin directory matching a given
         * search query.
         *
         * ## OPTIONS
         *
         * <search>
         * : The string to search for.
         *
         * [--page=<page>]
         * : Optional page to display.
         * ---
         * default: 1
         * ---
         *
         * [--per-page=<per-page>]
         * : Optional number of results to display.
         * ---
         * default: 10
         * ---
         *
         * [--field=<field>]
         * : Prints the value of a single field for each plugin.
         *
         * [--fields=<fields>]
         * : Ask for specific fields from the API. Defaults to name,slug,author_profile,rating. Acceptable values:
         *
         *     **name**: Plugin Name
         *     **slug**: Plugin Slug
         *     **version**: Current Version Number
         *     **author**: Plugin Author
         *     **author_profile**: Plugin Author Profile
         *     **contributors**: Plugin Contributors
         *     **requires**: Plugin Minimum Requirements
         *     **tested**: Plugin Tested Up To
         *     **compatibility**: Plugin Compatible With
         *     **rating**: Plugin Rating in Percent and Total Number
         *     **ratings**: Plugin Ratings for each star (1-5)
         *     **num_ratings**: Number of Plugin Ratings
         *     **homepage**: Plugin Author's Homepage
         *     **description**: Plugin's Description
         *     **short_description**: Plugin's Short Description
         *     **sections**: Plugin Readme Sections: description, installation, FAQ, screenshots, other notes, and changelog
         *     **downloaded**: Plugin Download Count
         *     **last_updated**: Plugin's Last Update
         *     **added**: Plugin's Date Added to wordpress.org Repository
         *     **tags**: Plugin's Tags
         *     **versions**: Plugin's Available Versions with D/L Link
         *     **donate_link**: Plugin's Donation Link
         *     **banners**: Plugin's Banner Image Link
         *     **icons**: Plugin's Icon Image Link
         *     **active_installs**: Plugin's Number of Active Installs
         *     **contributors**: Plugin's List of Contributors
         *     **url**: Plugin's URL on wordpress.org
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - count
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     $ wp plugin search dsgnwrks --per-page=20 --format=json
         *     Success: Showing 3 of 3 plugins.
         *     [{"name":"DsgnWrks Instagram Importer Debug","slug":"dsgnwrks-instagram-importer-debug","rating":0},{"name":"DsgnWrks Instagram Importer","slug":"dsgnwrks-instagram-importer","rating":84},{"name":"DsgnWrks Twitter Importer","slug":"dsgnwrks-twitter-importer","rating":80}]
         *
         *     $ wp plugin search dsgnwrks --fields=name,version,slug,rating,num_ratings
         *     Success: Showing 3 of 3 plugins.
         *     +-----------------------------------+---------+-----------------------------------+--------+-------------+
         *     | name                              | version | slug                              | rating | num_ratings |
         *     +-----------------------------------+---------+-----------------------------------+--------+-------------+
         *     | DsgnWrks Instagram Importer Debug | 0.1.6   | dsgnwrks-instagram-importer-debug | 0      | 0           |
         *     | DsgnWrks Instagram Importer       | 1.3.7   | dsgnwrks-instagram-importer       | 84     | 23          |
         *     | DsgnWrks Twitter Importer         | 1.1.1   | dsgnwrks-twitter-importer         | 80     | 1           |
         *     +-----------------------------------+---------+-----------------------------------+--------+-------------+
         */
        public function search($args, $assoc_args)
        {
        }
        protected function status_single($args)
        {
        }
        protected function get_all_items()
        {
        }
        /**
         * Activates one or more plugins.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to activate.
         *
         * [--all]
         * : If set, all plugins will be activated.
         *
         * [--exclude=<name>]
         * : Comma separated list of plugin slugs to be excluded from activation.
         *
         * [--network]
         * : If set, the plugin will be activated for the entire multisite network.
         *
         * ## EXAMPLES
         *
         *     # Activate plugin
         *     $ wp plugin activate hello
         *     Plugin 'hello' activated.
         *     Success: Activated 1 of 1 plugins.
         *
         *     # Activate plugin in entire multisite network
         *     $ wp plugin activate hello --network
         *     Plugin 'hello' network activated.
         *     Success: Network activated 1 of 1 plugins.
         *
         *     # Activate plugins that were recently active.
         *     $ wp plugin activate $(wp plugin list --recently-active --field=name)
         *     Plugin 'bbpress' activated.
         *     Plugin 'buddypress' activated.
         *     Success: Activated 2 of 2 plugins.
         *
         *     # Activate plugins that were recently active on a multisite.
         *     $ wp plugin activate $(wp plugin list --recently-active --field=name) --network
         *     Plugin 'bbpress' network activated.
         *     Plugin 'buddypress' network activated.
         *     Success: Activated 2 of 2 plugins.
         */
        public function activate($args, $assoc_args = array())
        {
        }
        /**
         * Deactivates one or more plugins.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to deactivate.
         *
         * [--uninstall]
         * : Uninstall the plugin after deactivation.
         *
         * [--all]
         * : If set, all plugins will be deactivated.
         *
         * [--exclude=<name>]
         * : Comma separated list of plugin slugs that should be excluded from deactivation.
         *
         * [--network]
         * : If set, the plugin will be deactivated for the entire multisite network.
         *
         * ## EXAMPLES
         *
         *     # Deactivate plugin
         *     $ wp plugin deactivate hello
         *     Plugin 'hello' deactivated.
         *     Success: Deactivated 1 of 1 plugins.
         *
         *     # Deactivate all plugins with exclusion
         *     $ wp plugin deactivate --all --exclude=hello,wordpress-seo
         *     Plugin 'contact-form-7' deactivated.
         *     Plugin 'ninja-forms' deactivated.
         *     Success: Deactivated 2 of 2 plugins.
         */
        public function deactivate($args, $assoc_args = array())
        {
        }
        /**
         * Toggles a plugin's activation state.
         *
         * If the plugin is active, then it will be deactivated. If the plugin is
         * inactive, then it will be activated.
         *
         * ## OPTIONS
         *
         * <plugin>...
         * : One or more plugins to toggle.
         *
         * [--network]
         * : If set, the plugin will be toggled for the entire multisite network.
         *
         * ## EXAMPLES
         *
         *     # Akismet is currently activated
         *     $ wp plugin toggle akismet
         *     Plugin 'akismet' deactivated.
         *     Success: Toggled 1 of 1 plugins.
         *
         *     # Akismet is currently deactivated
         *     $ wp plugin toggle akismet
         *     Plugin 'akismet' activated.
         *     Success: Toggled 1 of 1 plugins.
         */
        public function toggle($args, $assoc_args = array())
        {
        }
        /**
         * Gets the path to a plugin or to the plugin directory.
         *
         * ## OPTIONS
         *
         * [<plugin>]
         * : The plugin to get the path to. If not set, will return the path to the
         * plugins directory.
         *
         * [--dir]
         * : If set, get the path to the closest parent directory, instead of the
         * plugin file.
         *
         * ## EXAMPLES
         *
         *     $ cd $(wp plugin path) && pwd
         *     /var/www/wordpress/wp-content/plugins
         */
        public function path($args, $assoc_args)
        {
        }
        protected function install_from_repo($slug, $assoc_args)
        {
        }
        /**
         * Updates one or more plugins.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to update.
         *
         * [--all]
         * : If set, all plugins that have updates will be updated.
         *
         * [--exclude=<name>]
         * : Comma separated list of plugin names that should be excluded from updating.
         *
         * [--minor]
         * : Only perform updates for minor releases (e.g. from 1.3 to 1.4 instead of 2.0)
         *
         * [--patch]
         * : Only perform updates for patch releases (e.g. from 1.3 to 1.3.3 instead of 1.4)
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - summary
         * ---
         *
         * [--version=<version>]
         * : If set, the plugin will be updated to the specified version.
         *
         * [--dry-run]
         * : Preview which plugins would be updated.
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     $ wp plugin update bbpress --version=dev
         *     Installing bbPress (Development Version)
         *     Downloading install package from https://downloads.wordpress.org/plugin/bbpress.zip...
         *     Unpacking the package...
         *     Installing the plugin...
         *     Removing the old version of the plugin...
         *     Plugin updated successfully.
         *     Success: Updated 1 of 2 plugins.
         *
         *     $ wp plugin update --all
         *     Enabling Maintenance mode...
         *     Downloading update from https://downloads.wordpress.org/plugin/akismet.3.1.11.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the plugin...
         *     Plugin updated successfully.
         *     Downloading update from https://downloads.wordpress.org/plugin/nginx-champuru.3.2.0.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the plugin...
         *     Plugin updated successfully.
         *     Disabling Maintenance mode...
         *     +------------------------+-------------+-------------+---------+
         *     | name                   | old_version | new_version | status  |
         *     +------------------------+-------------+-------------+---------+
         *     | akismet                | 3.1.3       | 3.1.11      | Updated |
         *     | nginx-cache-controller | 3.1.1       | 3.2.0       | Updated |
         *     +------------------------+-------------+-------------+---------+
         *     Success: Updated 2 of 2 plugins.
         *
         *     $ wp plugin update --all --exclude=akismet
         *     Enabling Maintenance mode...
         *     Downloading update from https://downloads.wordpress.org/plugin/nginx-champuru.3.2.0.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the plugin...
         *     Plugin updated successfully.
         *     Disabling Maintenance mode...
         *     +------------------------+-------------+-------------+---------+
         *     | name                   | old_version | new_version | status  |
         *     +------------------------+-------------+-------------+---------+
         *     | nginx-cache-controller | 3.1.1       | 3.2.0       | Updated |
         *     +------------------------+-------------+-------------+---------+
         *
         * @alias upgrade
         */
        public function update($args, $assoc_args)
        {
        }
        protected function get_item_list()
        {
        }
        /**
         * Get the wordpress.org status of a plugin.
         *
         * @param string $plugin_name The plugin slug.
         *
         * @return string The status of the plugin, includes the last update date.
         */
        protected function get_wporg_data($plugin_name)
        {
        }
        protected function filter_item_list($items, $args)
        {
        }
        /**
         * Installs one or more plugins.
         *
         * ## OPTIONS
         *
         * <plugin|zip|url>...
         * : One or more plugins to install. Accepts a plugin slug, the path to a local zip file, or a URL to a remote zip file.
         *
         * [--version=<version>]
         * : If set, get that particular version from wordpress.org, instead of the
         * stable version.
         *
         * [--force]
         * : If set, the command will overwrite any installed version of the plugin, without prompting
         * for confirmation.
         *
         * [--ignore-requirements]
         * :If set, the command will install the plugin while ignoring any WordPress or PHP version requirements
         * specified by the plugin authors.
         *
         * [--activate]
         * : If set, the plugin will be activated immediately after install.
         *
         * [--activate-network]
         * : If set, the plugin will be network activated immediately after install
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Install the latest version from wordpress.org and activate
         *     $ wp plugin install bbpress --activate
         *     Installing bbPress (2.5.9)
         *     Downloading install package from https://downloads.wordpress.org/plugin/bbpress.2.5.9.zip...
         *     Using cached file '/home/vagrant/.wp-cli/cache/plugin/bbpress-2.5.9.zip'...
         *     Unpacking the package...
         *     Installing the plugin...
         *     Plugin installed successfully.
         *     Activating 'bbpress'...
         *     Plugin 'bbpress' activated.
         *     Success: Installed 1 of 1 plugins.
         *
         *     # Install the development version from wordpress.org
         *     $ wp plugin install bbpress --version=dev
         *     Installing bbPress (Development Version)
         *     Downloading install package from https://downloads.wordpress.org/plugin/bbpress.zip...
         *     Unpacking the package...
         *     Installing the plugin...
         *     Plugin installed successfully.
         *     Success: Installed 1 of 1 plugins.
         *
         *     # Install from a local zip file
         *     $ wp plugin install ../my-plugin.zip
         *     Unpacking the package...
         *     Installing the plugin...
         *     Plugin installed successfully.
         *     Success: Installed 1 of 1 plugins.
         *
         *     # Install from a remote zip file
         *     $ wp plugin install http://s3.amazonaws.com/bucketname/my-plugin.zip?AWSAccessKeyId=123&Expires=456&Signature=abcdef
         *     Downloading install package from http://s3.amazonaws.com/bucketname/my-plugin.zip?AWSAccessKeyId=123&Expires=456&Signature=abcdef
         *     Unpacking the package...
         *     Installing the plugin...
         *     Plugin installed successfully.
         *     Success: Installed 1 of 1 plugins.
         *
         *     # Update from a remote zip file
         *     $ wp plugin install https://github.com/envato/wp-envato-market/archive/master.zip --force
         *     Downloading install package from https://github.com/envato/wp-envato-market/archive/master.zip
         *     Unpacking the package...
         *     Installing the plugin...
         *     Renamed Github-based project from 'wp-envato-market-master' to 'wp-envato-market'.
         *     Plugin updated successfully
         *     Success: Installed 1 of 1 plugins.
         *
         *     # Forcefully re-install all installed plugins
         *     $ wp plugin install $(wp plugin list --field=name) --force
         *     Installing Akismet (3.1.11)
         *     Downloading install package from https://downloads.wordpress.org/plugin/akismet.3.1.11.zip...
         *     Unpacking the package...
         *     Installing the plugin...
         *     Removing the old version of the plugin...
         *     Plugin updated successfully
         *     Success: Installed 1 of 1 plugins.
         */
        public function install($args, $assoc_args)
        {
        }
        /**
         * Gets details about an installed plugin.
         *
         * ## OPTIONS
         *
         * <plugin>
         * : The plugin to get.
         *
         * [--field=<field>]
         * : Instead of returning the whole plugin, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for the plugin:
         *
         * * name
         * * title
         * * author
         * * version
         * * description
         * * status
         *
         * These fields are optionally available:
         *
         * * requires_wp
         * * requires_php
         * * requires_plugins
         *
         * ## EXAMPLES
         *
         *     # Get plugin details.
         *     $ wp plugin get bbpress --format=json
         *     {"name":"bbpress","title":"bbPress","author":"The bbPress Contributors","version":"2.6.9","description":"bbPress is forum software with a twist from the creators of WordPress.","status":"active"}
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Uninstalls one or more plugins.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to uninstall.
         *
         * [--deactivate]
         * : Deactivate the plugin before uninstalling. Default behavior is to warn and skip if the plugin is active.
         *
         * [--skip-delete]
         * : If set, the plugin files will not be deleted. Only the uninstall procedure
         * will be run.
         *
         * [--all]
         * : If set, all plugins will be uninstalled.
         *
         * [--exclude=<name>]
         * : Comma separated list of plugin slugs to be excluded from uninstall.
         *
         * ## EXAMPLES
         *
         *     $ wp plugin uninstall hello
         *     Uninstalled and deleted 'hello' plugin.
         *     Success: Uninstalled 1 of 1 plugins.
         *
         *     # Uninstall all plugins excluding specified ones
         *     $ wp plugin uninstall --all --exclude=hello-dolly,jetpack
         *     Uninstalled and deleted 'akismet' plugin.
         *     Uninstalled and deleted 'tinymce-templates' plugin.
         *     Success: Uninstalled 2 of 2 plugins.
         */
        public function uninstall($args, $assoc_args = array())
        {
        }
        /**
         * Checks if a given plugin is installed.
         *
         * Returns exit code 0 when installed, 1 when uninstalled.
         *
         * ## OPTIONS
         *
         * <plugin>
         * : The plugin to check.
         *
         * ## EXAMPLES
         *
         *     # Check whether plugin is installed; exit status 0 if installed, otherwise 1
         *     $ wp plugin is-installed hello
         *     $ echo $?
         *     1
         *
         * @subcommand is-installed
         */
        public function is_installed($args, $assoc_args = array())
        {
        }
        /**
         * Checks if a given plugin is active.
         *
         * Returns exit code 0 when active, 1 when not active.
         *
         * ## OPTIONS
         *
         * <plugin>
         * : The plugin to check.
         *
         * [--network]
         * : If set, check if plugin is network-activated.
         *
         * ## EXAMPLES
         *
         *     # Check whether plugin is Active; exit status 0 if active, otherwise 1
         *     $ wp plugin is-active hello
         *     $ echo $?
         *     1
         *
         * @subcommand is-active
         */
        public function is_active($args, $assoc_args = array())
        {
        }
        /**
         * Deletes plugin files without deactivating or uninstalling.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to delete.
         *
         * [--all]
         * : If set, all plugins will be deleted.
         *
         * [--exclude=<name>]
         * : Comma separated list of plugin slugs to be excluded from deletion.
         *
         * ## EXAMPLES
         *
         *     # Delete plugin
         *     $ wp plugin delete hello
         *     Deleted 'hello' plugin.
         *     Success: Deleted 1 of 1 plugins.
         *
         *     # Delete inactive plugins
         *     $ wp plugin delete $(wp plugin list --status=inactive --field=name)
         *     Deleted 'tinymce-templates' plugin.
         *     Success: Deleted 1 of 1 plugins.
         *
         *     # Delete all plugins excluding specified ones
         *     $ wp plugin delete --all --exclude=hello-dolly,jetpack
         *     Deleted 'akismet' plugin.
         *     Deleted 'tinymce-templates' plugin.
         *     Success: Deleted 2 of 2 plugins.
         */
        public function delete($args, $assoc_args = array())
        {
        }
        /**
         * Gets a list of plugins.
         *
         * Displays a list of the plugins installed on the site with activation
         * status, whether or not there's an update available, etc.
         *
         * Use `--status=dropin` to list installed dropins (e.g. `object-cache.php`).
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : Filter results based on the value of a field.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each plugin.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - count
         *   - json
         *   - yaml
         * ---
         *
         * [--status=<status>]
         * : Filter the output by plugin status.
         * ---
         * options:
         *   - active
         *   - active-network
         *   - dropin
         *   - inactive
         *   - must-use
         * ---
         *
         * [--skip-update-check]
         * : If set, the plugin update check will be skipped.
         *
         * [--recently-active]
         * : If set, only recently active plugins will be shown and the status filter will be ignored.
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each plugin:
         *
         * * name
         * * status
         * * update
         * * version
         * * update_version
         * * auto_update
         *
         * These fields are optionally available:
         *
         * * update_package
         * * update_id
         * * title
         * * description
         * * file
         * * author
         * * tested_up_to
         * * requires
         * * requires_php
         * * wporg_status
         * * wporg_last_updated
         *
         * ## EXAMPLES
         *
         *     # List active plugins on the site.
         *     $ wp plugin list --status=active --format=json
         *     [{"name":"dynamic-hostname","status":"active","update":"none","version":"0.4.2","update_version":"","auto_update":"off"},{"name":"tinymce-templates","status":"active","update":"none","version":"4.8.1","update_version":"","auto_update":"off"},{"name":"wp-multibyte-patch","status":"active","update":"none","version":"2.9","update_version":"","auto_update":"off"},{"name":"wp-total-hacks","status":"active","update":"none","version":"4.7.2","update_version":"","auto_update":"off"}]
         *
         *     # List plugins on each site in a network.
         *     $ wp site list --field=url | xargs -I % wp plugin list --url=%
         *     +---------+----------------+-----------+---------+-----------------+------------+
         *     | name    | status         | update    | version | update_version | auto_update |
         *     +---------+----------------+-----------+---------+----------------+-------------+
         *     | akismet | active-network | none      | 5.3.1   |                | on          |
         *     | hello   | inactive       | available | 1.6     | 1.7.2          | off         |
         *     +---------+----------------+-----------+---------+----------------+-------------+
         *     +---------+----------------+-----------+---------+----------------+-------------+
         *     | name    | status         | update    | version | update_version | auto_update |
         *     +---------+----------------+-----------+---------+----------------+-------------+
         *     | akismet | active-network | none      | 5.3.1   |                | on          |
         *     | hello   | inactive       | available | 1.6     | 1.7.2          | off         |
         *     +---------+----------------+-----------+---------+----------------+-------------+
         *
         *     # Check whether plugins are still active on WordPress.org
         *     $ wp plugin list --fields=name,wporg_status,wporg_last_updated
         *     +--------------------+--------------+--------------------+
         *     | name               | wporg_status | wporg_last_updated |
         *     +--------------------+--------------+--------------------+
         *     | akismet            | active       | 2023-12-11         |
         *     | user-switching     | active       | 2023-11-17         |
         *     | wordpress-importer | active       | 2023-04-28         |
         *     | local              |              |                    |
         *     +--------------------+--------------+--------------------+
         *
         *     # List recently active plugins on the site.
         *     $ wp plugin list --recently-active --field=name --format=json
         *     ["akismet","bbpress","buddypress"]
         *
         * @subcommand list
         */
        public function list_($_, $assoc_args)
        {
        }
        /* PRIVATES */
        private function check_active($file, $network_wide)
        {
        }
        private function active_output($name, $file, $network_wide, $action)
        {
        }
        protected function get_status($file)
        {
        }
        /**
         * Gets the template path based on installation type.
         */
        private static function get_template_path($template)
        {
        }
        /**
         * Gets the details of a plugin.
         *
         * @param object
         * @return array
         */
        private function get_details($file)
        {
        }
        /**
         * Performs deletion of plugin files
         *
         * @param $plugin - Plugin fetcher object (name, file)
         * @return bool - If plugin was deleted
         */
        private function delete_plugin($plugin)
        {
        }
    }
}
namespace WP_CLI {
    trait ParseThemeNameInput
    {
        /**
         * If have optional args ([<theme>...]) and an all option, then check have something to do.
         *
         * @param array  $args Passed-in arguments.
         * @param bool   $all All flag.
         * @param string $verb Optional. Verb to use. Defaults to 'install'.
         * @return array Same as $args if not all, otherwise all slugs.
         * @throws ExitException If neither plugin name nor --all were provided.
         */
        protected function check_optional_args_and_all($args, $all, $verb = 'install')
        {
        }
        /**
         * Gets all available themes.
         *
         * Uses the same filter core uses in themes.php to determine which themes
         * should be available to manage through the WP_Themes_List_Table class.
         *
         * @return array
         */
        private function get_all_themes()
        {
        }
        /**
         * Check if current version of the theme is higher than the one available at WP.org.
         *
         * @param string $slug Theme slug.
         * @param string $version Theme current version.
         *
         * @return bool|string
         */
        protected function is_theme_version_valid($slug, $version)
        {
        }
        /**
         * Get the status for a given theme.
         *
         * @param WP_Theme $theme Theme to get the status for.
         *
         * @return string Status of the theme.
         */
        protected function get_status($theme)
        {
        }
        /**
         * Check whether a given theme is the active theme.
         *
         * @param WP_Theme $theme Theme to check.
         *
         * @return bool Whether the provided theme is the active theme.
         */
        protected function is_active_theme($theme)
        {
        }
        /**
         * Check whether a given theme is the active theme parent.
         *
         * @param WP_Theme $theme Theme to check.
         *
         * @return bool Whether the provided theme is the active theme.
         */
        protected function is_active_parent_theme($theme)
        {
        }
        /**
         * Get the available update info.
         *
         * @return mixed Available update info.
         */
        protected function get_update_info()
        {
        }
    }
}
namespace {
    /**
     * Manages theme auto-updates.
     *
     * ## EXAMPLES
     *
     *     # Enable the auto-updates for a theme
     *     $ wp theme auto-updates enable twentysixteen
     *     Theme auto-updates for 'twentysixteen' enabled.
     *     Success: Enabled 1 of 1 theme auto-updates.
     *
     *     # Disable the auto-updates for a theme
     *     $ wp theme auto-updates disable twentysixteen
     *     Theme auto-updates for 'twentysixteen' disabled.
     *     Success: Disabled 1 of 1 theme auto-updates.
     *
     *     # Get the status of theme auto-updates
     *     $ wp theme auto-updates status twentysixteen
     *     Auto-updates for theme 'twentysixteen' are disabled.
     *
     * @package wp-cli
     */
    class Theme_AutoUpdates_Command
    {
        use \WP_CLI\ParseThemeNameInput;
        /**
         * Site option that stores the status of theme auto-updates.
         *
         * @var string
         */
        const SITE_OPTION = 'auto_update_themes';
        /**
         * Theme fetcher instance.
         *
         * @var Theme
         */
        protected $fetcher;
        /**
         * Theme_AutoUpdates_Command constructor.
         */
        public function __construct()
        {
        }
        /**
         * Enables the auto-updates for a theme.
         *
         * ## OPTIONS
         *
         * [<theme>...]
         * : One or more themes to enable auto-updates for.
         *
         * [--all]
         * : If set, auto-updates will be enabled for all themes.
         *
         * [--disabled-only]
         * : If set, filters list of themes to only include the ones that have
         * auto-updates disabled.
         *
         * ## EXAMPLES
         *
         *     # Enable the auto-updates for a theme
         *     $ wp theme auto-updates enable twentysixteen
         *     Theme auto-updates for 'twentysixteen' enabled.
         *     Success: Enabled 1 of 1 theme auto-updates.
         */
        public function enable($args, $assoc_args)
        {
        }
        /**
         * Disables the auto-updates for a theme.
         *
         * ## OPTIONS
         *
         * [<theme>...]
         * : One or more themes to disable auto-updates for.
         *
         * [--all]
         * : If set, auto-updates will be disabled for all themes.
         *
         * [--enabled-only]
         * : If set, filters list of themes to only include the ones that have
         * auto-updates enabled.
         *
         * ## EXAMPLES
         *
         *     # Disable the auto-updates for a theme
         *     $ wp theme auto-updates disable twentysixteen
         *     Theme auto-updates for 'twentysixteen' disabled.
         *     Success: Disabled 1 of 1 theme auto-updates.
         */
        public function disable($args, $assoc_args)
        {
        }
        /**
         * Shows the status of auto-updates for a theme.
         *
         * ## OPTIONS
         *
         * [<theme>...]
         * : One or more themes to show the status of the auto-updates of.
         *
         * [--all]
         * : If set, the status of auto-updates for all themes will be shown.
         *
         * [--enabled-only]
         * : If set, filters list of themes to only include the ones that have
         * auto-updates enabled.
         *
         * [--disabled-only]
         * : If set, filters list of themes to only include the ones that have
         * auto-updates disabled.
         *
         * [--field=<field>]
         * : Only show the provided field.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         *   - count
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get the status of theme auto-updates
         *     $ wp theme auto-updates status twentysixteen
         *     +---------------+----------+
         *     | name          | status   |
         *     +---------------+----------+
         *     | twentysixteen | disabled |
         *     +---------------+----------+
         *
         *     # Get the list of themes that have auto-updates enabled
         *     $ wp theme auto-updates status --all --enabled-only --field=name
         *     twentysixteen
         *     twentyseventeen
         */
        public function status($args, $assoc_args)
        {
        }
    }
    /**
     * Manages themes, including installs, activations, and updates.
     *
     * See the WordPress [Theme Handbook](https://developer.wordpress.org/themes/) developer resource for more information on themes.
     *
     * ## EXAMPLES
     *
     *     # Install the latest version of a theme from wordpress.org and activate
     *     $ wp theme install twentysixteen --activate
     *     Installing Twenty Sixteen (1.2)
     *     Downloading install package from http://downloads.wordpress.org/theme/twentysixteen.1.2.zip...
     *     Unpacking the package...
     *     Installing the theme...
     *     Theme installed successfully.
     *     Activating 'twentysixteen'...
     *     Success: Switched to 'Twenty Sixteen' theme.
     *     Success: Installed 1 of 1 themes.
     *
     *     # Get details of an installed theme
     *     $ wp theme get twentysixteen --fields=name,title,version
     *     +---------+----------------+
     *     | Field   | Value          |
     *     +---------+----------------+
     *     | name    | Twenty Sixteen |
     *     | title   | Twenty Sixteen |
     *     | version | 1.2            |
     *     +---------+----------------+
     *
     *     # Get status of theme
     *     $ wp theme status twentysixteen
     *     Theme twentysixteen details:
     *          Name: Twenty Sixteen
     *          Status: Active
     *          Version: 1.2
     *          Author: the WordPress team
     *
     * @package wp-cli
     */
    class Theme_Command extends \WP_CLI\CommandWithUpgrade
    {
        use \WP_CLI\ParseThemeNameInput;
        protected $item_type = 'theme';
        protected $upgrade_refresh = 'wp_update_themes';
        protected $upgrade_transient = 'update_themes';
        protected $obj_fields = ['name', 'status', 'update', 'version', 'update_version', 'auto_update'];
        public function __construct()
        {
        }
        protected function get_upgrader_class($force)
        {
        }
        /**
         * Reveals the status of one or all themes.
         *
         * ## OPTIONS
         *
         * [<theme>]
         * : A particular theme to show the status for.
         *
         * ## EXAMPLES
         *
         *     $ wp theme status twentysixteen
         *     Theme twentysixteen details:
         *          Name: Twenty Sixteen
         *          Status: Inactive
         *          Version: 1.2
         *          Author: the WordPress team
         */
        public function status($args)
        {
        }
        /**
         * Searches the WordPress.org theme directory.
         *
         * Displays themes in the WordPress.org theme directory matching a given
         * search query.
         *
         * ## OPTIONS
         *
         * <search>
         * : The string to search for.
         *
         * [--page=<page>]
         * : Optional page to display.
         * ---
         * default: 1
         * ---
         *
         * [--per-page=<per-page>]
         * : Optional number of results to display. Defaults to 10.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each theme.
         *
         * [--fields=<fields>]
         * : Ask for specific fields from the API. Defaults to name,slug,author,rating. Acceptable values:
         *
         *     **name**: Theme Name
         *     **slug**: Theme Slug
         *     **version**: Current Version Number
         *     **author**: Theme Author
         *     **preview_url**: Theme Preview URL
         *     **screenshot_url**: Theme Screenshot URL
         *     **rating**: Theme Rating
         *     **num_ratings**: Number of Theme Ratings
         *     **homepage**: Theme Author's Homepage
         *     **description**: Theme Description
         *     **url**: Theme's URL on wordpress.org
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     $ wp theme search photo --per-page=6
         *     Success: Showing 6 of 203 themes.
         *     +----------------------+----------------------+--------+
         *     | name                 | slug                 | rating |
         *     +----------------------+----------------------+--------+
         *     | Photos               | photos               | 100    |
         *     | Infinite Photography | infinite-photography | 100    |
         *     | PhotoBook            | photobook            | 100    |
         *     | BG Photo Frame       | bg-photo-frame       | 0      |
         *     | fPhotography         | fphotography         | 0      |
         *     | Photo Perfect        | photo-perfect        | 98     |
         *     +----------------------+----------------------+--------+
         */
        public function search($args, $assoc_args)
        {
        }
        protected function status_single($args)
        {
        }
        protected function get_all_items()
        {
        }
        /**
         * Activates a theme.
         *
         * ## OPTIONS
         *
         * <theme>
         * : The theme to activate.
         *
         * ## EXAMPLES
         *
         *     $ wp theme activate twentysixteen
         *     Success: Switched to 'Twenty Sixteen' theme.
         */
        public function activate($args = array())
        {
        }
        /**
         * Enables a theme on a WordPress multisite install.
         *
         * Permits theme to be activated from the dashboard of a site on a WordPress
         * multisite install.
         *
         * ## OPTIONS
         *
         * <theme>
         * : The theme to enable.
         *
         * [--network]
         * : If set, the theme is enabled for the entire network
         *
         * [--activate]
         * : If set, the theme is activated for the current site. Note that
         * the "network" flag has no influence on this.
         *
         * ## EXAMPLES
         *
         *     # Enable theme
         *     $ wp theme enable twentysixteen
         *     Success: Enabled the 'Twenty Sixteen' theme.
         *
         *     # Network enable theme
         *     $ wp theme enable twentysixteen --network
         *     Success: Network enabled the 'Twenty Sixteen' theme.
         *
         *     # Network enable and activate theme for current site
         *     $ wp theme enable twentysixteen --activate
         *     Success: Enabled the 'Twenty Sixteen' theme.
         *     Success: Switched to 'Twenty Sixteen' theme.
         */
        public function enable($args, $assoc_args)
        {
        }
        /**
         * Disables a theme on a WordPress multisite install.
         *
         * Removes ability for a theme to be activated from the dashboard of a site
         * on a WordPress multisite install.
         *
         * ## OPTIONS
         *
         * <theme>
         * : The theme to disable.
         *
         * [--network]
         * : If set, the theme is disabled on the network level. Note that
         * individual sites may still have this theme enabled if it was
         * enabled for them independently.
         *
         * ## EXAMPLES
         *
         *     # Disable theme
         *     $ wp theme disable twentysixteen
         *     Success: Disabled the 'Twenty Sixteen' theme.
         *
         *     # Disable theme in network level
         *     $ wp theme disable twentysixteen --network
         *     Success: Network disabled the 'Twenty Sixteen' theme.
         */
        public function disable($args, $assoc_args)
        {
        }
        /**
         * Gets the path to a theme or to the theme directory.
         *
         * ## OPTIONS
         *
         * [<theme>]
         * : The theme to get the path to. Path includes "style.css" file.
         * If not set, will return the path to the themes directory.
         *
         * [--dir]
         * : If set, get the path to the closest parent directory, instead of the
         * theme's "style.css" file.
         *
         * ## EXAMPLES
         *
         *     # Get theme path
         *     $ wp theme path
         *     /var/www/example.com/public_html/wp-content/themes
         *
         *     # Change directory to theme path
         *     $ cd $(wp theme path)
         */
        public function path($args, $assoc_args)
        {
        }
        protected function install_from_repo($slug, $assoc_args)
        {
        }
        protected function get_item_list()
        {
        }
        protected function filter_item_list($items, $args)
        {
        }
        /**
         * Installs one or more themes.
         *
         * ## OPTIONS
         *
         * <theme|zip|url>...
         * : One or more themes to install. Accepts a theme slug, the path to a local zip file, or a URL to a remote zip file.
         *
         * [--version=<version>]
         * : If set, get that particular version from wordpress.org, instead of the
         * stable version.
         *
         * [--force]
         * : If set, the command will overwrite any installed version of the theme, without prompting
         * for confirmation.
         *
         * [--ignore-requirements]
         * : If set, the command will install the theme while ignoring any WordPress or PHP version requirements
         * specified by the theme authors.
         *
         * [--activate]
         * : If set, the theme will be activated immediately after install.
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Install the latest version from wordpress.org and activate
         *     $ wp theme install twentysixteen --activate
         *     Installing Twenty Sixteen (1.2)
         *     Downloading install package from http://downloads.wordpress.org/theme/twentysixteen.1.2.zip...
         *     Unpacking the package...
         *     Installing the theme...
         *     Theme installed successfully.
         *     Activating 'twentysixteen'...
         *     Success: Switched to 'Twenty Sixteen' theme.
         *     Success: Installed 1 of 1 themes.
         *
         *     # Install from a local zip file
         *     $ wp theme install ../my-theme.zip
         *
         *     # Install from a remote zip file
         *     $ wp theme install http://s3.amazonaws.com/bucketname/my-theme.zip?AWSAccessKeyId=123&Expires=456&Signature=abcdef
         */
        public function install($args, $assoc_args)
        {
        }
        /**
         * Gets details about a theme.
         *
         * ## OPTIONS
         *
         * <theme>
         * : The theme to get.
         *
         * [--field=<field>]
         * : Instead of returning the whole theme, returns the value of a single field.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     $ wp theme get twentysixteen --fields=name,title,version
         *     +---------+----------------+
         *     | Field   | Value          |
         *     +---------+----------------+
         *     | name    | Twenty Sixteen |
         *     | title   | Twenty Sixteen |
         *     | version | 1.2            |
         *     +---------+----------------+
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Updates one or more themes.
         *
         * ## OPTIONS
         *
         * [<theme>...]
         * : One or more themes to update.
         *
         * [--all]
         * : If set, all themes that have updates will be updated.
         *
         * [--exclude=<theme-names>]
         * : Comma separated list of theme names that should be excluded from updating.
         *
         * [--minor]
         * : Only perform updates for minor releases (e.g. from 1.3 to 1.4 instead of 2.0)
         *
         * [--patch]
         * : Only perform updates for patch releases (e.g. from 1.3 to 1.3.3 instead of 1.4)
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - summary
         * ---
         *
         * [--version=<version>]
         * : If set, the theme will be updated to the specified version.
         *
         * [--dry-run]
         * : Preview which themes would be updated.
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Update multiple themes
         *     $ wp theme update twentyfifteen twentysixteen
         *     Downloading update from https://downloads.wordpress.org/theme/twentyfifteen.1.5.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the theme...
         *     Theme updated successfully.
         *     Downloading update from https://downloads.wordpress.org/theme/twentysixteen.1.2.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the theme...
         *     Theme updated successfully.
         *     +---------------+-------------+-------------+---------+
         *     | name          | old_version | new_version | status  |
         *     +---------------+-------------+-------------+---------+
         *     | twentyfifteen | 1.4         | 1.5         | Updated |
         *     | twentysixteen | 1.1         | 1.2         | Updated |
         *     +---------------+-------------+-------------+---------+
         *     Success: Updated 2 of 2 themes.
         *
         *     # Exclude themes updates when bulk updating the themes
         *     $ wp theme update --all --exclude=twentyfifteen
         *     Downloading update from https://downloads.wordpress.org/theme/astra.1.0.5.1.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the theme...
         *     Theme updated successfully.
         *     Downloading update from https://downloads.wordpress.org/theme/twentyseventeen.1.2.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the theme...
         *     Theme updated successfully.
         *     +-----------------+----------+---------+----------------+
         *     | name            | status   | version | update_version |
         *     +-----------------+----------+---------+----------------+
         *     | astra           | inactive | 1.0.1   | 1.0.5.1        |
         *     | twentyseventeen | inactive | 1.1     | 1.2            |
         *     +-----------------+----------+---------+----------------+
         *     Success: Updated 2 of 2 themes.
         *
         *     # Update all themes
         *     $ wp theme update --all
         *
         * @alias upgrade
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Checks if a given theme is installed.
         *
         * Returns exit code 0 when installed, 1 when uninstalled.
         *
         * ## OPTIONS
         *
         * <theme>
         * : The theme to check.
         *
         * ## EXAMPLES
         *
         *     # Check whether theme is installed; exit status 0 if installed, otherwise 1
         *     $ wp theme is-installed hello
         *     $ echo $?
         *     1
         *
         * @subcommand is-installed
         */
        public function is_installed($args, $assoc_args = array())
        {
        }
        /**
         * Checks if a given theme is active.
         *
         * Returns exit code 0 when active, 1 when not active.
         *
         * ## OPTIONS
         *
         * <theme>
         * : The plugin to check.
         *
         * ## EXAMPLES
         *
         *     # Check whether theme is Active; exit status 0 if active, otherwise 1
         *     $ wp theme is-active twentyfifteen
         *     $ echo $?
         *     1
         *
         * @subcommand is-active
         */
        public function is_active($args, $assoc_args = array())
        {
        }
        /**
         * Deletes one or more themes.
         *
         * Removes the theme or themes from the filesystem.
         *
         * ## OPTIONS
         *
         * [<theme>...]
         * : One or more themes to delete.
         *
         * [--all]
         * : If set, all themes will be deleted except active theme.
         *
         * [--force]
         * : To delete active theme use this.
         *
         * ## EXAMPLES
         *
         *     $ wp theme delete twentytwelve
         *     Deleted 'twentytwelve' theme.
         *     Success: Deleted 1 of 1 themes.
         *
         * @alias uninstall
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Gets a list of themes.
         *
         * ## OPTIONS
         *
         * [--<field>=<value>]
         * : Filter results based on the value of a field.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each theme.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * [--status=<status>]
         * : Filter the output by theme status.
         * ---
         * options:
         *   - active
         *   - parent
         *   - inactive
         * ---
         *
         * [--skip-update-check]
         * : If set, the theme update check will be skipped.
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each theme:
         *
         * * name
         * * status
         * * update
         * * version
         * * update_version
         * * auto_update
         *
         * These fields are optionally available:
         *
         * * update_package
         * * update_id
         * * title
         * * description
         *
         * ## EXAMPLES
         *
         *     # List inactive themes.
         *     $ wp theme list --status=inactive --format=csv
         *     name,status,update,version,update_version,auto_update
         *     twentyfourteen,inactive,none,3.8,,off
         *     twentysixteen,inactive,available,3.0,3.1,off
         *
         * @subcommand list
         */
        public function list_($_, $assoc_args)
        {
        }
        /**
         * Gets the template path based on installation type.
         */
        private static function get_template_path($template)
        {
        }
    }
    /**
     * Sets, gets, and removes theme mods.
     *
     * ## EXAMPLES
     *
     *     # Set the 'background_color' theme mod to '000000'.
     *     $ wp theme mod set background_color 000000
     *     Success: Theme mod background_color set to 000000.
     *
     *     # Get single theme mod in JSON format.
     *     $ wp theme mod get background_color --format=json
     *     [{"key":"background_color","value":"dd3333"}]
     *
     *     # Remove all theme mods.
     *     $ wp theme mod remove --all
     *     Success: Theme mods removed.
     */
    class Theme_Mod_Command extends \WP_CLI_Command
    {
        private $fields = ['key', 'value'];
        /**
         * Gets one or more theme mods.
         *
         * ## OPTIONS
         *
         * [<mod>...]
         * : One or more mods to get.
         *
         * [--field=<field>]
         * : Returns the value of a single field.
         *
         * [--all]
         * : List all theme mods
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - json
         *   - csv
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Get all theme mods.
         *     $ wp theme mod get --all
         *     +------------------+---------+
         *     | key              | value   |
         *     +------------------+---------+
         *     | background_color | dd3333  |
         *     | link_color       | #dd9933 |
         *     | main_text_color  | #8224e3 |
         *     +------------------+---------+
         *
         *     # Get single theme mod in JSON format.
         *     $ wp theme mod get background_color --format=json
         *     [{"key":"background_color","value":"dd3333"}]
         *
         *     # Get value of a single theme mod.
         *     $ wp theme mod get background_color --field=value
         *     dd3333
         *
         *     # Get multiple theme mods.
         *     $ wp theme mod get background_color header_textcolor
         *     +------------------+--------+
         *     | key              | value  |
         *     +------------------+--------+
         *     | background_color | dd3333 |
         *     | header_textcolor |        |
         *     +------------------+--------+
         */
        public function get($args = array(), $assoc_args = array())
        {
        }
        /**
         * Gets a list of theme mods.
         *
         * ## OPTIONS
         *
         * [--field=<field>]
         * : Returns the value of a single field.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - json
         *   - csv
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Gets a list of theme mods.
         *     $ wp theme mod list
         *     +------------------+---------+
         *     | key              | value   |
         *     +------------------+---------+
         *     | background_color | dd3333  |
         *     | link_color       | #dd9933 |
         *     | main_text_color  | #8224e3 |
         *     +------------------+---------+
         *
         * @subcommand list
         */
        public function list_($args = array(), $assoc_args = array())
        {
        }
        /**
         * Removes one or more theme mods.
         *
         * ## OPTIONS
         *
         * [<mod>...]
         * : One or more mods to remove.
         *
         * [--all]
         * : Remove all theme mods.
         *
         * ## EXAMPLES
         *
         *     # Remove all theme mods.
         *     $ wp theme mod remove --all
         *     Success: Theme mods removed.
         *
         *     # Remove single theme mod.
         *     $ wp theme mod remove background_color
         *     Success: 1 mod removed.
         *
         *     # Remove multiple theme mods.
         *     $ wp theme mod remove background_color header_textcolor
         *     Success: 2 mods removed.
         */
        public function remove($args = array(), $assoc_args = array())
        {
        }
        /**
         * Sets the value of a theme mod.
         *
         * ## OPTIONS
         *
         * <mod>
         * : The name of the theme mod to set or update.
         *
         * <value>
         * : The new value.
         *
         * ## EXAMPLES
         *
         *     # Set theme mod
         *     $ wp theme mod set background_color 000000
         *     Success: Theme mod background_color set to 000000.
         */
        public function set($args = array(), $assoc_args = array())
        {
        }
    }
}
namespace WP_CLI {
    /**
     * A plugin upgrader class that clears the destination directory.
     */
    class DestructivePluginUpgrader extends \Plugin_Upgrader
    {
        public function install_package($args = array())
        {
        }
    }
    /**
     * A theme upgrader class that clears the destination directory.
     */
    class DestructiveThemeUpgrader extends \Theme_Upgrader
    {
        public function install_package($args = array())
        {
        }
    }
}
namespace WP_CLI\Fetchers {
    /**
     * Fetch a WordPress plugin based on one of its attributes.
     */
    class Plugin extends \WP_CLI\Fetchers\Base
    {
        /**
         * @var string $msg Error message to use when invalid data is provided
         */
        protected $msg = "The '%s' plugin could not be found.";
        /**
         * Get a plugin object by name
         *
         * @param string $name
         * @return object|false
         */
        public function get($name)
        {
        }
    }
    /**
     * Fetch a WordPress theme based on one of its attributes.
     */
    class Theme extends \WP_CLI\Fetchers\Base
    {
        /**
         * @var string $msg Error message to use when invalid data is provided
         */
        protected $msg = "The '%s' theme could not be found.";
        /**
         * Get a theme object by name
         *
         * @param string $name
         * @return object|false
         */
        public function get($name)
        {
        }
        /**
         * Find and return the key in $existing_themes that matches $name with
         * a case insensitive string comparison.
         *
         * @param string $name Name of theme received by command.
         * @param array  $existing_themes Key/value pair of existing themes, key is
         *                                a case sensitive name.
         * @return string|boolean Case sensitive name if match found, otherwise false.
         */
        private function find_inexact_match($name, $existing_themes)
        {
        }
    }
}
namespace {
    class Import_Command extends \WP_CLI_Command
    {
        public $processed_posts = array();
        /**
         * Imports content from a given WXR file.
         *
         * Provides a command line interface to the WordPress Importer plugin, for
         * performing data migrations.
         *
         * Use `define( 'IMPORT_DEBUG', true );` for more verbosity during importing.
         *
         * ## OPTIONS
         *
         * <file>...
         * : Path to one or more valid WXR files for importing. Directories are also accepted.
         *
         * --authors=<authors>
         * : How the author mapping should be handled. Options are 'create', 'mapping.csv', or 'skip'. The first will create any non-existent users from the WXR file. The second will read author mapping associations from a CSV, or create a CSV for editing if the file path doesn't exist. The CSV requires two columns, and a header row like "old_user_login,new_user_login". The last option will skip any author mapping.
         *
         * [--skip=<data-type>]
         * : Skip importing specific data. Supported options are: 'attachment' and 'image_resize' (skip time-consuming thumbnail generation).
         *
         * ## EXAMPLES
         *
         *     # Import content from a WXR file
         *     $ wp import example.wordpress.2016-06-21.xml --authors=create
         *     Starting the import process...
         *     Processing post #1 ("Hello world!") (post_type: post)
         *     -- 1 of 1
         *     -- Tue, 21 Jun 2016 05:31:12 +0000
         *     -- Imported post as post_id #1
         *     Success: Finished importing from 'example.wordpress.2016-06-21.xml' file.
         */
        public function __invoke($args, $assoc_args)
        {
        }
        /**
         * Imports a WXR file.
         */
        private function import_wxr($file, $args)
        {
        }
        public function filter_set_image_sizes($sizes)
        {
        }
        /**
         * Defines useful verbosity filters for the WXR importer.
         */
        private function add_wxr_filters()
        {
        }
        /**
         * Determines whether the requested importer is available.
         */
        private function is_importer_available()
        {
        }
        /**
         * Processes how the authors should be mapped
         *
         * @param string            $authors_arg      The `--author` argument originally passed to command
         * @param array             $author_data      An array of WP_User-esque author objects
         * @return array|WP_Error   $author_mapping   Author mapping array if successful, WP_Error if something bad happened
         */
        private function process_author_mapping($authors_arg, $author_data)
        {
        }
        /**
         * Reads an author mapping file.
         */
        private function read_author_mapping_file($file)
        {
        }
        /**
         * Creates an author mapping file, based on provided author data.
         *
         * @return WP_Error      The file was just now created, so some action needs to be taken
         */
        private function create_author_mapping_file($file, $author_data)
        {
        }
        /**
         * Creates users if they don't exist, and build an author mapping file.
         */
        private function create_authors_for_mapping($author_data)
        {
        }
        /**
         * Suggests a blog user based on the levenshtein distance.
         */
        private function suggest_user($author_user_login, $author_user_email = '')
        {
        }
    }
}
namespace WP_CLI {
    /**
     * Base class for WP-CLI commands that deal with translations
     *
     * @package wp-cli
     */
    abstract class CommandWithTranslation extends \WP_CLI_Command
    {
        protected $obj_type;
        protected $obj_fields;
        /**
         * Callback to sort array by a 'language' key.
         */
        protected function sort_translations_callback($a, $b)
        {
        }
        /**
         * Updates installed languages for the current object type.
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Get all updates available for all translations.
         *
         * @see wp_get_translation_updates()
         *
         * @return array
         */
        protected function get_translation_updates()
        {
        }
        /**
         * Download a language pack.
         *
         * @see wp_download_language_pack()
         *
         * @param string $download Language code to download.
         * @param string $slug Plugin or theme slug. Not used for core.
         * @return string|\WP_Error Returns the language code if successfully downloaded, or a WP_Error object on failure.
         */
        protected function download_language_pack($download, $slug = null)
        {
        }
        /**
         * Return a list of installed languages.
         *
         * @param string $slug Optional. Plugin or theme slug. Defaults to 'default' for core.
         *
         * @return array
         */
        protected function get_installed_languages($slug = 'default')
        {
        }
        /**
         * Return a list of all languages.
         *
         * @param string $slug Optional. Plugin or theme slug. Not used for core.
         *
         * @return array
         */
        protected function get_all_languages($slug = null)
        {
        }
        /**
         * Get Formatter object based on supplied parameters.
         *
         * @param array $assoc_args Parameters passed to command. Determines formatting.
         * @return Formatter
         */
        protected function get_formatter(&$assoc_args)
        {
        }
    }
}
namespace {
    /**
     * Installs, activates, and manages core language packs.
     *
     * ## EXAMPLES
     *
     *     # Install the Dutch core language pack.
     *     $ wp language core install nl_NL
     *     Downloading translation from https://downloads.wordpress.org/translation/core/6.4.3/nl_NL.zip...
     *     Unpacking the update...
     *     Installing the latest version...
     *     Removing the old version of the translation...
     *     Translation updated successfully.
     *     Language 'nl_NL' installed.
     *     Success: Installed 1 of 1 languages.
     *
     *     # Activate the Dutch core language pack.
     *     $ wp site switch-language nl_NL
     *     Success: Language activated.
     *
     *     # Uninstall the Dutch core language pack.
     *     $ wp language core uninstall nl_NL
     *     Success: Language uninstalled.
     *
     *     # List installed core language packs.
     *     $ wp language core list --status=installed
     *     +----------+--------------+-------------+-----------+-----------+---------------------+
     *     | language | english_name | native_name | status    | update    | updated             |
     *     +----------+--------------+-------------+-----------+-----------+---------------------+
     *     | nl_NL    | Dutch        | Nederlands  | installed | available | 2024-01-31 10:24:06 |
     *     +----------+--------------+-------------+-----------+-----------+---------------------+
     *
     * @package wp-cli
     */
    class Core_Language_Command extends \WP_CLI\CommandWithTranslation
    {
        protected $obj_type = 'core';
        protected $obj_fields = array('language', 'english_name', 'native_name', 'status', 'update', 'updated');
        /**
         * Lists all available languages.
         *
         * ## OPTIONS
         *
         * [--field=<field>]
         * : Display the value of a single field
         *
         * [--<field>=<value>]
         * : Filter results by key=value pairs.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each translation:
         *
         * * language
         * * english_name
         * * native_name
         * * status
         * * update
         * * updated
         *
         * ## EXAMPLES
         *
         *     # List language,english_name,status fields of available languages.
         *     $ wp language core list --fields=language,english_name,status
         *     +----------------+-------------------------+-------------+
         *     | language       | english_name            | status      |
         *     +----------------+-------------------------+-------------+
         *     | ar             | Arabic                  | uninstalled |
         *     | ary            | Moroccan Arabic         | uninstalled |
         *     | az             | Azerbaijani             | uninstalled |
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Checks if a given language is installed.
         *
         * Returns exit code 0 when installed, 1 when uninstalled.
         *
         * ## OPTIONS
         *
         * <language>
         * : The language code to check.
         *
         * ## EXAMPLES
         *
         *     # Check whether the German language is installed; exit status 0 if installed, otherwise 1.
         *     $ wp language core is-installed de_DE
         *     $ echo $?
         *     1
         *
         * @subcommand is-installed
         */
        public function is_installed($args, $assoc_args = array())
        {
        }
        /**
         * Installs a given language.
         *
         * Downloads the language pack from WordPress.org. Find your language code at: https://translate.wordpress.org/
         *
         * ## OPTIONS
         *
         * <language>...
         * : Language code to install.
         *
         * [--activate]
         * : If set, the language will be activated immediately after install.
         *
         * ## EXAMPLES
         *
         *     # Install the Brazilian Portuguese language.
         *     $ wp language core install pt_BR
         *     Downloading translation from https://downloads.wordpress.org/translation/core/6.5/pt_BR.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the translation...
         *     Translation updated successfully.
         *     Language 'pt_BR' installed.
         *     Success: Installed 1 of 1 languages.
         *
         * @subcommand install
         */
        public function install($args, $assoc_args)
        {
        }
        /**
         * Uninstalls a given language.
         *
         * ## OPTIONS
         *
         * <language>...
         * : Language code to uninstall.
         *
         * ## EXAMPLES
         *
         *     # Uninstall the Japanese core language pack.
         *     $ wp language core uninstall ja
         *     Success: Language uninstalled.
         *
         * @subcommand uninstall
         * @throws WP_CLI\ExitException
         */
        public function uninstall($args, $assoc_args)
        {
        }
        /**
         * Updates installed languages for core.
         *
         * ## OPTIONS
         *
         * [--dry-run]
         * : Preview which translations would be updated.
         *
         * ## EXAMPLES
         *
         *     # Update installed core languages packs.
         *     $ wp language core update
         *     Updating 'Japanese' translation for WordPress 6.4.3...
         *     Downloading translation from https://downloads.wordpress.org/translation/core/6.4.3/ja.zip...
         *     Translation updated successfully.
         *     Success: Updated 1/1 translation.
         *
         * @subcommand update
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Activates a given language.
         *
         * **Warning: `wp language core activate` is deprecated. Use `wp site switch-language` instead.**
         *
         * ## OPTIONS
         *
         * <language>
         * : Language code to activate.
         *
         * ## EXAMPLES
         *
         *     # Activate the given language.
         *     $ wp language core activate ja
         *     Success: Language activated.
         *
         * @subcommand activate
         * @throws WP_CLI\ExitException
         */
        public function activate($args, $assoc_args)
        {
        }
        private function activate_language($language_code)
        {
        }
    }
    /**
     * Installs, activates, and manages language packs.
     *
     * ## EXAMPLES
     *
     *     # Install the Dutch core language pack.
     *     $ wp language core install nl_NL
     *     Downloading translation from https://downloads.wordpress.org/translation/core/6.4.3/nl_NL.zip...
     *     Unpacking the update...
     *     Installing the latest version...
     *     Removing the old version of the translation...
     *     Translation updated successfully.
     *     Language 'nl_NL' installed.
     *     Success: Installed 1 of 1 languages.
     *
     *     # Activate the Dutch core language pack.
     *     $ wp site switch-language nl_NL
     *     Success: Language activated.
     *
     *     # Install the Dutch theme language pack for Twenty Ten.
     *     $ wp language theme install twentyten nl_NL
     *     Downloading translation from https://downloads.wordpress.org/translation/theme/twentyten/4.0/nl_NL.zip...
     *     Unpacking the update...
     *     Installing the latest version...
     *     Removing the old version of the translation...
     *     Translation updated successfully.
     *     Language 'nl_NL' installed.
     *     Success: Installed 1 of 1 languages.
     *
     *     # Install the Dutch plugin language pack for Hello Dolly.
     *     $ wp language plugin install hello-dolly nl_NL
     *     Downloading translation from https://downloads.wordpress.org/translation/plugin/hello-dolly/1.7.2/nl_NL.zip...
     *     Unpacking the update...
     *     Installing the latest version...
     *     Removing the old version of the translation...
     *     Translation updated successfully.
     *     Language 'nl_NL' installed.
     *     Success: Installed 1 of 1 languages.
     *
     * @package wp-cli
     */
    class Language_Namespace extends \WP_CLI\Dispatcher\CommandNamespace
    {
    }
    /**
     * Installs, activates, and manages plugin language packs.
     *
     * ## EXAMPLES
     *
     *     # Install the Dutch plugin language pack for Hello Dolly.
     *     $ wp language plugin install hello-dolly nl_NL
     *     Downloading translation from https://downloads.wordpress.org/translation/plugin/hello-dolly/1.7.2/nl_NL.zip...
     *     Unpacking the update...
     *     Installing the latest version...
     *     Removing the old version of the translation...
     *     Translation updated successfully.
     *     Language 'nl_NL' installed.
     *     Success: Installed 1 of 1 languages.
     *
     *     # Uninstall the Dutch plugin language pack for Hello Dolly.
     *     $ wp language plugin uninstall hello-dolly nl_NL
     *     Language 'nl_NL' for 'hello-dolly' uninstalled.
     *     +-------------+--------+-------------+
     *     | name        | locale | status      |
     *     +-------------+--------+-------------+
     *     | hello-dolly | nl_NL  | uninstalled |
     *     +-------------+--------+-------------+
     *     Success: Uninstalled 1 of 1 languages.
     *
     *     # List installed plugin language packs for Hello Dolly.
     *     $ wp language plugin list hello-dolly --status=installed
     *     +-------------+----------+--------------+-------------+-----------+--------+---------------------+
     *     | plugin      | language | english_name | native_name | status    | update | updated             |
     *     +-------------+----------+--------------+-------------+-----------+--------+---------------------+
     *     | hello-dolly | nl_NL    | Dutch        | Nederlands  | installed | none   | 2023-11-13 12:34:15 |
     *     +-------------+----------+--------------+-------------+-----------+--------+---------------------+
     *
     * @package wp-cli
     */
    class Plugin_Language_Command extends \WP_CLI\CommandWithTranslation
    {
        protected $obj_type = 'plugins';
        protected $obj_fields = array('plugin', 'language', 'english_name', 'native_name', 'status', 'update', 'updated');
        /**
         * Lists all available languages for one or more plugins.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to list languages for.
         *
         * [--all]
         * : If set, available languages for all plugins will be listed.
         *
         * [--field=<field>]
         * : Display the value of a single field.
         *
         * [--<field>=<value>]
         * : Filter results by key=value pairs.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each translation:
         *
         * * plugin
         * * language
         * * english_name
         * * native_name
         * * status
         * * update
         * * updated
         *
         * ## EXAMPLES
         *
         *     # List available language packs for the plugin.
         *     $ wp language plugin list hello-dolly --fields=language,english_name,status
         *     +----------------+-------------------------+-------------+
         *     | language       | english_name            | status      |
         *     +----------------+-------------------------+-------------+
         *     | ar             | Arabic                  | uninstalled |
         *     | ary            | Moroccan Arabic         | uninstalled |
         *     | az             | Azerbaijani             | uninstalled |
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Checks if a given language is installed.
         *
         * Returns exit code 0 when installed, 1 when uninstalled.
         *
         * ## OPTIONS
         *
         * <plugin>
         * : Plugin to check for.
         *
         * <language>...
         * : The language code to check.
         *
         * ## EXAMPLES
         *
         *     # Check whether the German language is installed for Akismet; exit status 0 if installed, otherwise 1.
         *     $ wp language plugin is-installed akismet de_DE
         *     $ echo $?
         *     1
         *
         * @subcommand is-installed
         */
        public function is_installed($args, $assoc_args = array())
        {
        }
        /**
         * Installs a given language for a plugin.
         *
         * Downloads the language pack from WordPress.org.
         *
         * ## OPTIONS
         *
         * [<plugin>]
         * : Plugin to install language for.
         *
         * [--all]
         * : If set, languages for all plugins will be installed.
         *
         * <language>...
         * : Language code to install.
         *
         * [--format=<format>]
         * : Render output in a particular format. Used when installing languages for all plugins.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - summary
         * ---
         *
         * ## EXAMPLES
         *
         *     # Install the Japanese language for Akismet.
         *     $ wp language plugin install akismet ja
         *     Downloading translation from https://downloads.wordpress.org/translation/plugin/akismet/4.0.3/ja.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Removing the old version of the translation...
         *     Translation updated successfully.
         *     Language 'ja' installed.
         *     Success: Installed 1 of 1 languages.
         *
         * @subcommand install
         */
        public function install($args, $assoc_args)
        {
        }
        /**
         * Installs translations for a plugin.
         *
         * @param array $args       Runtime arguments.
         * @param array $assoc_args Runtime arguments.
         */
        private function install_one($args, $assoc_args)
        {
        }
        /**
         * Installs translations for all installed plugins.
         *
         * @param array $args       Runtime arguments.
         * @param array $assoc_args Runtime arguments.
         */
        private function install_many($args, $assoc_args)
        {
        }
        /**
         * Uninstalls a given language for a plugin.
         *
         * ## OPTIONS
         *
         * [<plugin>]
         * : Plugin to uninstall language for.
         *
         * [--all]
         * : If set, languages for all plugins will be uninstalled.
         *
         * <language>...
         * : Language code to uninstall.
         *
         * [--format=<format>]
         * : Render output in a particular format. Used when installing languages for all plugins.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - summary
         * ---
         *
         * ## EXAMPLES
         *
         *     # Uninstall the Japanese plugin language pack for Hello Dolly.
         *     $ wp language plugin uninstall hello-dolly ja
         *     Language 'ja' for 'hello-dolly' uninstalled.
         *     +-------------+--------+-------------+
         *     | name        | locale | status      |
         *     +-------------+--------+-------------+
         *     | hello-dolly | ja     | uninstalled |
         *     +-------------+--------+-------------+
         *     Success: Uninstalled 1 of 1 languages.
         *
         * @subcommand uninstall
         */
        public function uninstall($args, $assoc_args)
        {
        }
        /**
         * Updates installed languages for one or more plugins.
         *
         * ## OPTIONS
         *
         * [<plugin>...]
         * : One or more plugins to update languages for.
         *
         * [--all]
         * : If set, languages for all plugins will be updated.
         *
         * [--dry-run]
         * : Preview which translations would be updated.
         *
         * ## EXAMPLES
         *
         *     # Update all installed language packs for all plugins.
         *     $ wp language plugin update --all
         *     Updating 'Japanese' translation for Akismet 3.1.11...
         *     Downloading translation from https://downloads.wordpress.org/translation/plugin/akismet/3.1.11/ja.zip...
         *     Translation updated successfully.
         *     Success: Updated 1/1 translation.
         *
         * @subcommand update
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Gets all available plugins.
         *
         * Uses the same filter core uses in plugins.php to determine which plugins
         * should be available to manage through the WP_Plugins_List_Table class.
         *
         * @return array
         */
        private function get_all_plugins()
        {
        }
    }
    class Site_Switch_Language_Command extends \WP_CLI\CommandWithTranslation
    {
        protected $obj_type = 'core';
        /**
         * Activates a given language.
         *
         * ## OPTIONS
         *
         * <language>
         * : Language code to activate.
         *
         * ## EXAMPLES
         *
         *     $ wp site switch-language ja
         *     Success: Language activated.
         *
         * @throws WP_CLI\ExitException
         */
        public function __invoke($args, $assoc_args)
        {
        }
    }
    /**
     * Installs, activates, and manages theme language packs.
     *
     * ## EXAMPLES
     *
     *     # Install the Dutch theme language pack for Twenty Ten.
     *     $ wp language theme install twentyten nl_NL
     *     Downloading translation from https://downloads.wordpress.org/translation/theme/twentyten/4.0/nl_NL.zip...
     *     Unpacking the update...
     *     Installing the latest version...
     *     Removing the old version of the translation...
     *     Translation updated successfully.
     *     Language 'nl_NL' installed.
     *     Success: Installed 1 of 1 languages.
     *
     *     # Uninstall the Dutch theme language pack for Twenty Ten.
     *     $ wp language theme uninstall twentyten nl_NL
     *     Language 'nl_NL' for 'twentyten' uninstalled.
     *     +-----------+--------+-------------+
     *     | name      | locale | status      |
     *     +-----------+--------+-------------+
     *     | twentyten | nl_NL  | uninstalled |
     *     +-----------+--------+-------------+
     *     Success: Uninstalled 1 of 1 languages.
     *
     *     # List installed theme language packs for Twenty Ten.
     *     $ wp language theme list twentyten --status=installed
     *     +-----------+----------+--------------+-------------+-----------+--------+---------------------+
     *     | theme     | language | english_name | native_name | status    | update | updated             |
     *     +-----------+----------+--------------+-------------+-----------+--------+---------------------+
     *     | twentyten | nl_NL    | Dutch        | Nederlands  | installed | none   | 2023-12-29 21:21:39 |
     *     +-----------+----------+--------------+-------------+-----------+--------+---------------------+
     *
     * @package wp-cli
     */
    class Theme_Language_Command extends \WP_CLI\CommandWithTranslation
    {
        protected $obj_type = 'themes';
        protected $obj_fields = array('theme', 'language', 'english_name', 'native_name', 'status', 'update', 'updated');
        /**
         * Lists all available languages for one or more themes.
         *
         * ## OPTIONS
         *
         * [<theme>...]
         * : One or more themes to list languages for.
         *
         * [--all]
         * : If set, available languages for all themes will be listed.
         *
         * [--field=<field>]
         * : Display the value of a single field.
         *
         * [--<field>=<value>]
         * : Filter results by key=value pairs.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each translation:
         *
         * * theme
         * * language
         * * english_name
         * * native_name
         * * status
         * * update
         * * updated
         *
         * ## EXAMPLES
         *
         *     # List available language packs for the theme.
         *     $ wp language theme list twentyten --fields=language,english_name,status
         *     +----------------+-------------------------+-------------+
         *     | language       | english_name            | status      |
         *     +----------------+-------------------------+-------------+
         *     | ar             | Arabic                  | uninstalled |
         *     | ary            | Moroccan Arabic         | uninstalled |
         *     | az             | Azerbaijani             | uninstalled |
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Checks if a given language is installed.
         *
         * Returns exit code 0 when installed, 1 when uninstalled.
         *
         * ## OPTIONS
         *
         * <theme>
         * : Theme to check for.
         *
         * <language>...
         * : The language code to check.
         *
         * ## EXAMPLES
         *
         *     # Check whether the German language is installed for Twenty Seventeen; exit status 0 if installed, otherwise 1.
         *     $ wp language theme is-installed twentyseventeen de_DE
         *     $ echo $?
         *     1
         *
         * @subcommand is-installed
         */
        public function is_installed($args, $assoc_args = array())
        {
        }
        /**
         * Installs a given language for a theme.
         *
         * Downloads the language pack from WordPress.org.
         *
         * ## OPTIONS
         *
         * [<theme>]
         * : Theme to install language for.
         *
         * [--all]
         * : If set, languages for all themes will be installed.
         *
         * <language>...
         * : Language code to install.
         *
         * [--format=<format>]
         * : Render output in a particular format. Used when installing languages for all themes.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - summary
         * ---
         *
         * ## EXAMPLES
         *
         *     # Install the Japanese language for Twenty Seventeen.
         *     $ wp language theme install twentyseventeen ja
         *     Downloading translation from https://downloads.wordpress.org/translation/theme/twentyseventeen/1.3/ja.zip...
         *     Unpacking the update...
         *     Installing the latest version...
         *     Translation updated successfully.
         *     Language 'ja' installed.
         *     Success: Installed 1 of 1 languages.
         *
         * @subcommand install
         */
        public function install($args, $assoc_args)
        {
        }
        /**
         * Installs translations for a theme.
         *
         * @param array $args       Runtime arguments.
         * @param array $assoc_args Runtime arguments.
         */
        private function install_one($args, $assoc_args)
        {
        }
        /**
         * Installs translations for all installed themes.
         *
         * @param array $args       Runtime arguments.
         * @param array $assoc_args Runtime arguments.
         */
        private function install_many($args, $assoc_args)
        {
        }
        /**
         * Uninstalls a given language for a theme.
         *
         * ## OPTIONS
         *
         * [<theme>]
         * : Theme to uninstall language for.
         *
         * [--all]
         * : If set, languages for all themes will be uninstalled.
         *
         * <language>...
         * : Language code to uninstall.
         *
         * [--format=<format>]
         * : Render output in a particular format. Used when installing languages for all themes.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - summary
         * ---
         *
         * ## EXAMPLES
         *
         *     # Uninstall the Japanese theme language pack for Twenty Ten.
         *     $ wp language theme uninstall twentyten ja
         *     Language 'ja' for 'twentyten' uninstalled.
         *     +-----------+--------+-------------+
         *     | name      | locale | status      |
         *     +-----------+--------+-------------+
         *     | twentyten | ja     | uninstalled |
         *     +-----------+--------+-------------+
         *     Success: Uninstalled 1 of 1 languages.
         *
         * @subcommand uninstall
         */
        public function uninstall($args, $assoc_args)
        {
        }
        /**
         * Updates installed languages for one or more themes.
         *
         * ## OPTIONS
         *
         * [<theme>...]
         * : One or more themes to update languages for.
         *
         * [--all]
         * : If set, languages for all themes will be updated.
         *
         * [--dry-run]
         * : Preview which translations would be updated.
         *
         * ## EXAMPLES
         *
         *     # Update all installed language packs for all themes.
         *     $ wp language theme update --all
         *     Updating 'Japanese' translation for Twenty Fifteen 1.5...
         *     Downloading translation from https://downloads.wordpress.org/translation/theme/twentyfifteen/1.5/ja.zip...
         *     Translation updated successfully.
         *     Success: Updated 1/1 translation.
         *
         * @subcommand update
         */
        public function update($args, $assoc_args)
        {
        }
    }
}
namespace WP_CLI {
    /**
     * A Language Pack Upgrader class that caches the download, and uses cached if available
     *
     * @package wp-cli
     */
    class LanguagePackUpgrader extends \Language_Pack_Upgrader
    {
        /**
         * Initialize the upgrade strings.
         *
         * Makes sure that the strings are always in English.
         */
        public function upgrade_strings()
        {
        }
        /**
         * Initialize the generic strings.
         *
         * Makes sure that the strings are always in English.
         */
        public function generic_strings()
        {
        }
        /**
         * Caches the download, and uses cached if available.
         *
         * @param string $package          The URI of the package. If this is the full path to an
         *                                 existing local file, it will be returned untouched.
         * @param bool   $check_signatures Whether to validate file signatures. Default false.
         * @param array  $hook_extra       Extra arguments to pass to the filter hooks. Default empty array.
         * @return string|\WP_Error The full path to the downloaded package file, or a WP_Error object.
         */
        public function download_package($package, $check_signatures = false, $hook_extra = [])
        {
        }
    }
}
namespace WP_CLI\MaintenanceMode {
    /**
     * Activates, deactivates or checks the status of the maintenance mode of a site.
     *
     * ## EXAMPLES
     *
     *     # Activate Maintenance mode.
     *     $ wp maintenance-mode activate
     *     Enabling Maintenance mode...
     *     Success: Activated Maintenance mode.
     *
     *     # Deactivate Maintenance mode.
     *     $ wp maintenance-mode deactivate
     *     Disabling Maintenance mode...
     *     Success: Deactivated Maintenance mode.
     *
     *     # Display Maintenance mode status.
     *     $ wp maintenance-mode status
     *     Maintenance mode is active.
     *
     *     # Get Maintenance mode status for scripting purpose.
     *     $ wp maintenance-mode is-active
     *     $ echo $?
     *     1
     *
     * @when    after_wp_load
     * @package wp-cli
     */
    class MaintenanceModeCommand extends \WP_CLI_Command
    {
        /**
         * Instance of WP_Upgrader.
         *
         * @var WP_Upgrader
         */
        private $upgrader;
        /**
         * Instantiate a MaintenanceModeCommand object.
         */
        public function __construct()
        {
        }
        /**
         * Activates maintenance mode.
         *
         * ## OPTIONS
         *
         * [--force]
         * : Force maintenance mode activation operation.
         *
         * ## EXAMPLES
         *
         *     $ wp maintenance-mode activate
         *     Enabling Maintenance mode...
         *     Success: Activated Maintenance mode.
         */
        public function activate($_, $assoc_args)
        {
        }
        /**
         * Deactivates maintenance mode.
         *
         * ## EXAMPLES
         *
         *     $ wp maintenance-mode deactivate
         *     Disabling Maintenance mode...
         *     Success: Deactivated Maintenance mode.
         */
        public function deactivate()
        {
        }
        /**
         * Displays maintenance mode status.
         *
         * ## EXAMPLES
         *
         *     $ wp maintenance-mode status
         *     Maintenance mode is active.
         */
        public function status()
        {
        }
        /**
         * Detects maintenance mode status.
         *
         * ## EXAMPLES
         *
         *     $ wp maintenance-mode is-active
         *     $ echo $?
         *     1
         *
         * @subcommand is-active
         */
        public function is_active()
        {
        }
        /**
         * Returns status of maintenance mode.
         *
         * @return bool
         */
        private function get_maintenance_mode_status()
        {
        }
        /**
         * Initializes WP_Filesystem.
         *
         * @return WP_Filesystem_Base
         */
        protected function init_wp_filesystem()
        {
        }
    }
}
namespace {
    /**
     * Imports files as attachments, regenerates thumbnails, or lists registered image sizes.
     *
     * ## EXAMPLES
     *
     *     # Re-generate all thumbnails, without confirmation.
     *     $ wp media regenerate --yes
     *     Found 3 images to regenerate.
     *     1/3 Regenerated thumbnails for "Sydney Harbor Bridge" (ID 760).
     *     2/3 Regenerated thumbnails for "Boardwalk" (ID 757).
     *     3/3 Regenerated thumbnails for "Sunburst Over River" (ID 756).
     *     Success: Regenerated 3 of 3 images.
     *
     *     # Import a local image and set it to be the featured image for a post.
     *     $ wp media import ~/Downloads/image.png --post_id=123 --title="A downloaded picture" --featured_image
     *     Imported file '/home/person/Downloads/image.png' as attachment ID 1753 and attached to post 123 as featured image.
     *     Success: Imported 1 of 1 images.
     *
     *     # List all registered image sizes
     *     $ wp media image-size
     *     +---------------------------+-------+--------+-------+
     *     | name                      | width | height | crop  |
     *     +---------------------------+-------+--------+-------+
     *     | full                      |       |        | N/A   |
     *     | twentyfourteen-full-width | 1038  | 576    | hard  |
     *     | large                     | 1024  | 1024   | soft  |
     *     | medium_large              | 768   | 0      | soft  |
     *     | medium                    | 300   | 300    | soft  |
     *     | thumbnail                 | 150   | 150    | hard  |
     *     +---------------------------+-------+--------+-------+
     *
     *     # Fix orientation for specific images.
     *     $ wp media fix-orientation 63
     *     1/1 Fixing orientation for "Portrait_6" (ID 63).
     *     Success: Fixed 1 of 1 images.
     *
     * @package wp-cli
     */
    class Media_Command extends \WP_CLI_Command
    {
        /**
         * Clear the WP object cache after this many regenerations/imports.
         *
         * @var integer
         */
        const WP_CLEAR_OBJECT_CACHE_INTERVAL = 500;
        /**
         * Regenerates thumbnails for one or more attachments.
         *
         * ## OPTIONS
         *
         * [<attachment-id>...]
         * : One or more IDs of the attachments to regenerate.
         *
         * [--image_size=<image_size>]
         * : Name of the image size to regenerate. Only thumbnails of this image size will be regenerated, thumbnails of other image sizes will not.
         *
         * [--skip-delete]
         * : Skip deletion of the original thumbnails. If your thumbnails are linked from sources outside your control, it's likely best to leave them around. Defaults to false.
         *
         * [--only-missing]
         * : Only generate thumbnails for images missing image sizes.
         *
         * [--delete-unknown]
         * : Only delete thumbnails for old unregistered image sizes.
         *
         * [--yes]
         * : Answer yes to the confirmation message. Confirmation only shows when no IDs passed as arguments.
         *
         * ## EXAMPLES
         *
         *     # Regenerate thumbnails for given attachment IDs.
         *     $ wp media regenerate 123 124 125
         *     Found 3 images to regenerate.
         *     1/3 Regenerated thumbnails for "Vertical Image" (ID 123).
         *     2/3 Regenerated thumbnails for "Horizontal Image" (ID 124).
         *     3/3 Regenerated thumbnails for "Beautiful Picture" (ID 125).
         *     Success: Regenerated 3 of 3 images.
         *
         *     # Regenerate all thumbnails, without confirmation.
         *     $ wp media regenerate --yes
         *     Found 3 images to regenerate.
         *     1/3 Regenerated thumbnails for "Sydney Harbor Bridge" (ID 760).
         *     2/3 Regenerated thumbnails for "Boardwalk" (ID 757).
         *     3/3 Regenerated thumbnails for "Sunburst Over River" (ID 756).
         *     Success: Regenerated 3 of 3 images.
         *
         *     # Re-generate all thumbnails that have IDs between 1000 and 2000.
         *     $ seq 1000 2000 | xargs wp media regenerate
         *     Found 4 images to regenerate.
         *     1/4 Regenerated thumbnails for "Vertical Featured Image" (ID 1027).
         *     2/4 Regenerated thumbnails for "Horizontal Featured Image" (ID 1022).
         *     3/4 Regenerated thumbnails for "Unicorn Wallpaper" (ID 1045).
         *     4/4 Regenerated thumbnails for "I Am Worth Loving Wallpaper" (ID 1023).
         *     Success: Regenerated 4 of 4 images.
         *
         *     # Re-generate only the thumbnails of "large" image size for all images.
         *     $ wp media regenerate --image_size=large
         *     Do you really want to regenerate the "large" image size for all images? [y/n] y
         *     Found 3 images to regenerate.
         *     1/3 Regenerated "large" thumbnail for "Sydney Harbor Bridge" (ID 760).
         *     2/3 No "large" thumbnail regeneration needed for "Boardwalk" (ID 757).
         *     3/3 Regenerated "large" thumbnail for "Sunburst Over River" (ID 756).
         *     Success: Regenerated 3 of 3 images.
         */
        public function regenerate($args, $assoc_args = array())
        {
        }
        /**
         * Creates attachments from local files or URLs.
         *
         * ## OPTIONS
         *
         * <file>...
         * : Path to file or files to be imported. Supports the glob(3) capabilities of the current shell.
         *     If file is recognized as a URL (for example, with a scheme of http or ftp), the file will be
         *     downloaded to a temp file before being sideloaded.
         *
         * [--post_id=<post_id>]
         * : ID of the post to attach the imported files to.
         *
         * [--post_name=<post_name>]
         * : Name of the post to attach the imported files to.
         *
         * [--file_name=<name>]
         * : Attachment name (post_name field).
         *
         * [--title=<title>]
         * : Attachment title (post title field).
         *
         * [--caption=<caption>]
         * : Caption for attachment (post excerpt field).
         *
         * [--alt=<alt_text>]
         * : Alt text for image (saved as post meta).
         *
         * [--desc=<description>]
         * : "Description" field (post content) of attachment post.
         *
         * [--skip-copy]
         * : If set, media files (local only) are imported to the library but not moved on disk.
         * File names will not be run through wp_unique_filename() with this set.
         *
         * [--preserve-filetime]
         * : Use the file modified time as the post published & modified dates.
         * Remote files will always use the current time.
         *
         * [--featured_image]
         * : If set, set the imported image as the Featured Image of the post it is attached to.
         *
         * [--porcelain[=<field>]]
         * : Output a single field for each imported image. Defaults to attachment ID when used as flag.
         * ---
         * options:
         *   - url
         * ---
         *
         * ## EXAMPLES
         *
         *     # Import all jpgs in the current user's "Pictures" directory, not attached to any post.
         *     $ wp media import ~/Pictures/**\/*.jpg
         *     Imported file '/home/person/Pictures/landscape-photo.jpg' as attachment ID 1751.
         *     Imported file '/home/person/Pictures/fashion-icon.jpg' as attachment ID 1752.
         *     Success: Imported 2 of 2 items.
         *
         *     # Import a local image and set it to be the post thumbnail for a post.
         *     $ wp media import ~/Downloads/image.png --post_id=123 --title="A downloaded picture" --featured_image
         *     Imported file '/home/person/Downloads/image.png' as attachment ID 1753 and attached to post 123 as featured image.
         *     Success: Imported 1 of 1 images.
         *
         *     # Import a local image, but set it as the featured image for all posts.
         *     # 1. Import the image and get its attachment ID.
         *     # 2. Assign the attachment ID as the featured image for all posts.
         *     $ ATTACHMENT_ID="$(wp media import ~/Downloads/image.png --porcelain)"
         *     $ wp post list --post_type=post --format=ids | xargs -d ' ' -I % wp post meta add % _thumbnail_id $ATTACHMENT_ID
         *     Success: Added custom field.
         *     Success: Added custom field.
         *
         *     # Import an image from the web.
         *     $ wp media import http://s.wordpress.org/style/images/wp-header-logo.png --title='The WordPress logo' --alt="Semantic personal publishing"
         *     Imported file 'http://s.wordpress.org/style/images/wp-header-logo.png' as attachment ID 1755.
         *     Success: Imported 1 of 1 images.
         *
         *     # Get the URL for an attachment after import.
         *     $ wp media import http://s.wordpress.org/style/images/wp-header-logo.png --porcelain | xargs -I {} wp post list --post__in={} --field=url --post_type=attachment
         *     http://wordpress-develop.dev/wp-header-logo/
         */
        public function import($args, $assoc_args = array())
        {
        }
        /**
         * Lists image sizes registered with WordPress.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a specific format
         * ---
         * default: table
         * options:
         *   - table
         *   - json
         *   - csv
         *   - yaml
         *   - count
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each image size:
         * * name
         * * width
         * * height
         * * crop
         * * ratio
         *
         * ## EXAMPLES
         *
         *     # List all registered image sizes
         *     $ wp media image-size
         *     +---------------------------+-------+--------+-------+-------+
         *     | name                      | width | height | crop  | ratio |
         *     +---------------------------+-------+--------+-------+-------+
         *     | full                      |       |        | N/A   | N/A   |
         *     | twentyfourteen-full-width | 1038  | 576    | hard  | 173:96|
         *     | large                     | 1024  | 1024   | soft  | N/A   |
         *     | medium_large              | 768   | 0      | soft  | N/A   |
         *     | medium                    | 300   | 300    | soft  | N/A   |
         *     | thumbnail                 | 150   | 150    | hard  | 1:1   |
         *     +---------------------------+-------+--------+-------+-------+
         *
         * @subcommand image-size
         */
        public function image_size($args, $assoc_args)
        {
        }
        private function get_ratio($width, $height)
        {
        }
        private function gcd($num1, $num2)
        {
        }
        // wp_tempnam() inexplicably forces a .tmp extension, which spoils MIME type detection
        private function make_copy($path)
        {
        }
        private function process_regeneration($id, $skip_delete, $only_missing, $delete_unknown, $image_size, $progress, &$successes, &$errors, &$skips)
        {
        }
        private function remove_old_images($metadata, $fullsizepath, $image_size)
        {
        }
        private function needs_regeneration($att_id, $fullsizepath, $is_pdf, $image_size, $skip_delete, &$skip_it)
        {
        }
        // Whether there's new image sizes or the width/height of existing image sizes have changed.
        private function image_sizes_differ($image_sizes, $meta_sizes)
        {
        }
        /**
         * Returns image sizes for a given attachment.
         *
         * Like WP's get_intermediate_image_sizes(), but removes sizes that won't be generated for a particular attachment due to it being on or below their thresholds,
         * and returns associative array with size name => width/height entries, resolved to crop values if applicable.
         *
         * @param string $fullsizepath Filepath of the attachment
         * @param bool   $is_pdf       Whether it is a PDF.
         * @param array  $metadata     Attachment metadata.
         * @param int    $att_id       Attachment ID.
         *
         * @return array|WP_Error Image sizes on success, WP_Error instance otherwise.
         */
        private function get_intermediate_image_sizes_for_attachment($fullsizepath, $is_pdf, $metadata, $att_id)
        {
        }
        // Like WP's get_intermediate_image_sizes(), but returns associative array with name => size info entries (and caters for PDFs also).
        private function get_intermediate_sizes($is_pdf, $metadata, $att_id)
        {
        }
        // Add filters to only process a particular intermediate image size in wp_generate_attachment_metadata().
        private function add_image_size_filters($image_size)
        {
        }
        // Remove above intermediate image size filters.
        private function remove_image_size_filters($image_size_filters)
        {
        }
        // Update attachment sizes metadata just for a particular intermediate image size.
        private function update_attachment_metadata_for_image_size($id, $new_metadata, $image_size, $metadata)
        {
        }
        /**
         * Get images from the installation.
         *
         * @param array $args                  The query arguments to use. Optional.
         * @param array $additional_mime_types The additional mime types to search for. Optional.
         *
         * @return WP_Query The query result.
         */
        private function get_images($args = array(), $additional_mime_types = array())
        {
        }
        /**
         * Get all the registered image sizes along with their dimensions.
         *
         * @return array $image_sizes The image sizes
         */
        private function get_registered_image_sizes()
        {
        }
        /**
         * Returns a normalized list of all currently registered image sub-sizes.
         *
         * If exists, uses output of wp_get_registered_image_subsizes() function (introduced in WP 5.3).
         * Definition of this method is modified version of core function wp_get_registered_image_subsizes().
         *
         * @global array $_wp_additional_image_sizes
         *
         * @return array[] Associative array of arrays of image sub-size information, keyed by image size name.
         */
        private function wp_get_registered_image_subsizes()
        {
        }
        /**
         * Fix image orientation for one or more attachments.
         *
         * ## OPTIONS
         *
         * [<attachment-id>...]
         * : One or more IDs of the attachments to regenerate.
         *
         * [--dry-run]
         * : Check images needing orientation without performing the operation.
         *
         * ## EXAMPLES
         *
         *     # Fix orientation for all images.
         *     $ wp media fix-orientation
         *     1/3 Fixing orientation for "Landscape_4" (ID 62).
         *     2/3 Fixing orientation for "Landscape_3" (ID 61).
         *     3/3 Fixing orientation for "Landscape_2" (ID 60).
         *     Success: Fixed 3 of 3 images.
         *
         *     # Fix orientation dry run.
         *     $ wp media fix-orientation 63 --dry-run
         *     1/1 "Portrait_6" (ID 63) will be affected.
         *     Success: 1 of 1 image will be affected.
         *
         *     # Fix orientation for specific images.
         *     $ wp media fix-orientation 63
         *     1/1 Fixing orientation for "Portrait_6" (ID 63).
         *     Success: Fixed 1 of 1 images.
         *
         * @subcommand fix-orientation
         */
        public function fix_orientation($args, $assoc_args)
        {
        }
        /**
         * Perform orientation fix on attachments.
         *
         * @param int    $id        Attachment Id.
         * @param string $progress  Current progress string.
         * @param int    $successes Count of success in current operation.
         * @param int    $errors    Count of errors in current operation.
         * @param bool   $dry_run   Is this a dry run?
         */
        private function process_orientation_fix($id, $progress, &$successes, &$errors, $dry_run)
        {
        }
        /**
         * Perform image rotate operations on the image.
         *
         * @param int    $id             Attachment Id.
         * @param array  $metadata       Attachment Metadata.
         * @param array  $image_meta     `image_meta` information for the attachment.
         * @param string $full_size_path Path to original image.
         *
         * @return bool Whether the image rotation operation succeeded.
         */
        private function flip_rotate_image($id, $metadata, $image_meta, $full_size_path)
        {
        }
        /**
         * Return array of operations to be done for provided orientation value.
         *
         * @param int $orientation EXIF orientation value.
         *
         * @return array
         */
        private function calculate_transformation($orientation)
        {
        }
        /**
         * Add compatibility indirection to get_attached_file().
         *
         * In WordPress 5.3, behavior changed to account for automatic resizing of
         * big image files.
         *
         * @see https://core.trac.wordpress.org/ticket/47873
         *
         * @param int $attachment_id ID of the attachment to get the filepath for.
         * @return string|false Filepath of the attachment, or false if not found.
         */
        private function get_attached_file($attachment_id)
        {
        }
        /**
         * Image-friendly alternative to wp_get_attachment_url(). Will return the full size URL of an image instead of the `-scaled` version.
         *
         * In WordPress 5.3, behavior changed to account for automatic resizing of
         * big image files.
         *
         * @see https://core.trac.wordpress.org/ticket/47873
         *
         * @param int $attachment_id ID of the attachment to get the URL for.
         * @return string|false URL of the attachment, or false if not found.
         */
        private function get_real_attachment_url($attachment_id)
        {
        }
        /**
         * Create image slug based on user input slug.
         * Add basename extension to slug.
         *
         * @param string $basename Default slu of image.
         * @param string $slug User input slug.
         *
         * @return string Image slug with extension.
         */
        private function get_image_name($basename, $slug)
        {
        }
        /**
         * Removes files for unknown/unregistered image sizes.
         *
         * Similar to {@see self::remove_old_images} but also updates metadata afterwards.
         *
         * @param int    $id           Attachment ID.
         * @param string $fullsizepath Filepath of the attachment.
         *
         * @return void
         */
        private function delete_unknown_image_sizes($id, $fullsizepath)
        {
        }
    }
    /**
     * Lists, installs, and removes WP-CLI packages.
     *
     * WP-CLI packages are community-maintained projects built on WP-CLI. They can
     * contain WP-CLI commands, but they can also just extend WP-CLI in some way.
     *
     * Learn how to create your own command from the
     * [Commands Cookbook](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/)
     *
     * ## EXAMPLES
     *
     *     # List installed packages.
     *     $ wp package list
     *     +-----------------------+------------------+----------+-----------+----------------+
     *     | name                  | authors          | version  | update    | update_version |
     *     +-----------------------+------------------+----------+-----------+----------------+
     *     | wp-cli/server-command | Daniel Bachhuber | dev-main | available | 2.x-dev        |
     *     +-----------------------+------------------+----------+-----------+----------------+
     *
     *     # Install the latest development version of the package.
     *     $ wp package install wp-cli/server-command
     *     Installing package wp-cli/server-command (dev-main)
     *     Updating /home/person/.wp-cli/packages/composer.json to require the package...
     *     Using Composer to install the package...
     *     ---
     *     Loading composer repositories with package information
     *     Updating dependencies
     *     Resolving dependencies through SAT
     *     Dependency resolution completed in 0.005 seconds
     *     Analyzed 732 packages to resolve dependencies
     *     Analyzed 1034 rules to resolve dependencies
     *      - Installing package
     *     Writing lock file
     *     Generating autoload files
     *     ---
     *     Success: Package installed.
     *
     *     # Uninstall package.
     *     $ wp package uninstall wp-cli/server-command
     *     Removing require statement for package 'wp-cli/server-command' from /home/person/.wp-cli/packages/composer.json
     *     Removing repository details from /home/person/.wp-cli/packages/composer.json
     *     Removing package directories and regenerating autoloader...
     *     Success: Uninstalled package.
     *
     * @package WP-CLI
     *
     * @when before_wp_load
     */
    class Package_Command extends \WP_CLI_Command
    {
        const PACKAGE_INDEX_URL = 'https://wp-cli.org/package-index/';
        const DEFAULT_DEV_BRANCH_CONSTRAINTS = 'dev-main || dev-master || dev-trunk';
        private $version_selector = \false;
        /**
         * Default author data used while creating default WP-CLI packages composer.json.
         *
         * @var array
         */
        private $author_data = ['name' => 'WP-CLI', 'email' => 'noreply@wpcli.org'];
        /**
         * Default repository data used while creating default WP-CLI packages composer.json.
         * @var array
         */
        private $composer_type_package = ['type' => 'composer', 'url' => self::PACKAGE_INDEX_URL];
        /**
         * Browses WP-CLI packages available for installation.
         *
         * Lists packages available for installation from the [Package Index](http://wp-cli.org/package-index/).
         * Although the package index will remain in place for backward compatibility reasons, it has been
         * deprecated and will not be updated further. Please refer to https://github.com/wp-cli/ideas/issues/51
         * to read about its potential replacement.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each package:
         *
         * * name
         * * description
         * * authors
         * * version
         *
         * There are no optionally available fields.
         *
         * ## EXAMPLES
         *
         *     $ wp package browse --format=yaml
         *     ---
         *     10up/mu-migration:
         *       name: 10up/mu-migration
         *       description: A set of WP-CLI commands to support the migration of single WordPress instances to multisite
         *       authors: Nícholas André
         *       version: dev-main, dev-develop
         *     aaemnnosttv/wp-cli-dotenv-command:
         *       name: aaemnnosttv/wp-cli-dotenv-command
         *       description: Dotenv commands for WP-CLI
         *       authors: Evan Mattson
         *       version: v0.1, v0.1-beta.1, v0.2, dev-main, dev-dev, dev-develop, dev-tests/behat
         *     aaemnnosttv/wp-cli-http-command:
         *       name: aaemnnosttv/wp-cli-http-command
         *       description: WP-CLI command for using the WordPress HTTP API
         *       authors: Evan Mattson
         *       version: dev-main
         */
        public function browse($_, $assoc_args)
        {
        }
        /**
         * Installs a WP-CLI package.
         *
         * Packages are required to be a valid Composer package, and can be
         * specified as:
         *
         * * Package name from WP-CLI's package index.
         * * Git URL accessible by the current shell user.
         * * Path to a directory on the local machine.
         * * Local or remote .zip file.
         *
         * Packages are installed to `~/.wp-cli/packages/` by default. Use the
         * `WP_CLI_PACKAGES_DIR` environment variable to provide a custom path.
         *
         * When installing a local directory, WP-CLI simply registers a
         * reference to the directory. If you move or delete the directory, WP-CLI's
         * reference breaks.
         *
         * When installing a .zip file, WP-CLI extracts the package to
         * `~/.wp-cli/packages/local/<package-name>`.
         *
         * If Github token authorization is required, a GitHub Personal Access Token
         * (https://github.com/settings/tokens) can be used. The following command
         * will add a GitHub Personal Access Token to Composer's global configuration:
         * composer config -g github-oauth.github.com <GITHUB_TOKEN>
         * Once this has been added, the value used for <GITHUB_TOKEN> will be used
         * for future authorization requests.
         *
         * ## OPTIONS
         *
         * <name|git|path|zip>
         * : Name, git URL, directory path, or .zip file for the package to install.
         * Names can optionally include a version constraint
         * (e.g. wp-cli/server-command:@stable).
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Install a package hosted at a git URL.
         *     $ wp package install runcommand/hook
         *
         *     # Install the latest stable version.
         *     $ wp package install wp-cli/server-command:@stable
         *
         *     # Install a package hosted at a GitLab.com URL.
         *     $ wp package install https://gitlab.com/foo/wp-cli-bar-command.git
         *
         *     # Install a package in a .zip file.
         *     $ wp package install google-sitemap-generator-cli.zip
         */
        public function install($args, $assoc_args)
        {
        }
        /**
         * Lists installed WP-CLI packages.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to all fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each package:
         *
         * * name
         * * authors
         * * version
         * * update
         * * update_version
         *
         * These fields are optionally available:
         *
         * * description
         *
         * ## EXAMPLES
         *
         *     # List installed packages.
         *     $ wp package list
         *     +-----------------------+------------------+----------+-----------+----------------+
         *     | name                  | authors          | version  | update    | update_version |
         *     +-----------------------+------------------+----------+-----------+----------------+
         *     | wp-cli/server-command | Daniel Bachhuber | dev-main | available | 2.x-dev        |
         *     +-----------------------+------------------+----------+-----------+----------------+
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets the path to an installed WP-CLI package, or the package directory.
         *
         * If you want to contribute to a package, this is a great way to jump to it.
         *
         * ## OPTIONS
         *
         * [<name>]
         * : Name of the package to get the directory for.
         *
         * ## EXAMPLES
         *
         *     # Get package path.
         *     $ wp package path
         *     /home/person/.wp-cli/packages/
         *
         *     # Get path to an installed package.
         *     $ wp package path wp-cli/server-command
         *     /home/person/.wp-cli/packages/vendor/wp-cli/server-command
         *
         *     # Change directory to package path.
         *     $ cd $(wp package path) && pwd
         *     /home/vagrant/.wp-cli/packages
         */
        public function path($args)
        {
        }
        /**
         * Updates all installed WP-CLI packages to their latest version.
         *
         * ## EXAMPLES
         *
         *     $ wp package update
         *     Using Composer to update packages...
         *     ---
         *     Loading composer repositories with package information
         *     Updating dependencies
         *     Resolving dependencies through SAT
         *     Dependency resolution completed in 0.074 seconds
         *     Analyzed 1062 packages to resolve dependencies
         *     Analyzed 22383 rules to resolve dependencies
         *     Writing lock file
         *     Generating autoload files
         *     ---
         *     Success: Packages updated.
         */
        public function update()
        {
        }
        /**
         * Uninstalls a WP-CLI package.
         *
         * ## OPTIONS
         *
         * <name>
         * : Name of the package to uninstall.
         *
         * [--insecure]
         * : Retry downloads without certificate validation if TLS handshake fails. Note: This makes the request vulnerable to a MITM attack.
         *
         * ## EXAMPLES
         *
         *     # Uninstall package.
         *     $ wp package uninstall wp-cli/server-command
         *     Removing require statement for package 'wp-cli/server-command' from /home/person/.wp-cli/packages/composer.json
         *     Removing repository details from /home/person/.wp-cli/packages/composer.json
         *     Removing package directories and regenerating autoloader...
         *     Success: Uninstalled package.
         */
        public function uninstall($args, $assoc_args)
        {
        }
        /**
         * Checks whether a package is a WP-CLI community package based
         * on membership in our package index.
         *
         * @param object      $package     A package object
         * @return bool
         */
        private function is_community_package($package)
        {
        }
        /**
         * Gets a Composer instance.
         */
        private function get_composer()
        {
        }
        /**
         * Gets all of the community packages.
         *
         * @return array
         */
        private function get_community_packages()
        {
        }
        /**
         * Gets the package index instance
         *
         * We need to construct the instance manually, because there's no way to select
         * a particular instance using $composer->getRepositoryManager()
         *
         * @return ComposerRepository
         */
        private function package_index()
        {
        }
        /**
         * Displays a set of packages
         *
         * @param string $context
         * @param array
         * @param array
         */
        private function show_packages($context, $packages, $assoc_args)
        {
        }
        /**
         * Gets a package by its shortened identifier.
         *
         * A shortened identifier has the form `<vendor>/<package>`.
         *
         * This method first checks the deprecated package index, for BC reasons,
         * and then falls back to the corresponding GitHub URL.
         *
         * @param string $package_name Name of the package to get.
         * @param bool   $insecure     Optional. Whether to insecurely retry downloads that failed TLS handshake. Defaults
         *                             to false.
         */
        private function get_package_by_shortened_identifier($package_name, $insecure = \false)
        {
        }
        /**
         * Gets the installed community packages.
         */
        private function get_installed_packages()
        {
        }
        /**
         * Gets an installed package by its name.
         */
        private function get_installed_package_by_name($package_name)
        {
        }
        /**
         * Checks if the package name provided is already installed.
         */
        private function is_package_installed($package_name)
        {
        }
        /**
         * Gets the name of the package from the composer.json in a directory path
         *
         * @param string $dir_package
         * @return array Two-element array containing package name and version.
         */
        private static function get_package_name_and_version_from_dir_package($dir_package)
        {
        }
        /**
         * Gets the WP-CLI packages composer.json object.
         */
        private function get_composer_json()
        {
        }
        /**
         * Gets the absolute path to the WP-CLI packages composer.json.
         */
        private function get_composer_json_path()
        {
        }
        /**
         * Gets the WP-CLI version for composer.json
         */
        private static function get_wp_cli_version_composer()
        {
        }
        /**
         * Creates a default WP-CLI packages composer.json.
         *
         * @param string $composer_path Where the composer.json should be created
         * @return string Returns the absolute path of the newly created default WP-CLI packages composer.json.
         */
        private function create_default_composer_json($composer_path)
        {
        }
        /**
         * Given a package, this finds the latest package matching it
         *
         * @param  PackageInterface $package
         * @param  Composer         $composer
         * @param  string           $phpVersion
         * @param  bool             $minorOnly
         *
         * @return PackageInterface|null
         */
        private function find_latest_package(\Composer\Package\PackageInterface $package, \Composer\Composer $composer, $php_version, $minor_only = \false)
        {
        }
        private function get_version_selector(\Composer\Composer $composer)
        {
        }
        /**
         * Checks whether a given package is a git repository.
         *
         * @param string $package Package name to check.
         *
         * @return bool Whether the package is a git repository.
         */
        private function is_git_repository($package)
        {
        }
        /**
         * Checks that `$package_name` matches the name in composer.json at Github.com, and return corrected value if not.
         *
         * @param string $package_name Package name to check.
         * @param string $version      Optional. Package version. Defaults to empty string.
         * @param bool   $insecure     Optional. Whether to insecurely retry downloads that failed TLS handshake. Defaults
         *                             to false.
         */
        private function check_github_package_name($package_name, $version = '', $insecure = \false)
        {
        }
        /**
         * Checks that `$package_name` matches the name in composer.json at the corresponding upstream repository, and return corrected value if not.
         *
         * @param string $package_name Package name to check.
         * @param string $url          URL to fetch the package from.
         * @param string $version      Optional. Package version. Defaults to empty string.
         * @param bool   $insecure     Optional. Whether to insecurely retry downloads that failed TLS handshake. Defaults
         *                             to false.
         */
        private function check_git_package_name($package_name, $url = '', $version = '', $insecure = \false)
        {
        }
        /**
         * Checks that `$package_name` matches the name in composer.json at GitLab.com, and return corrected value if not.
         *
         * @param string $project_name Package name to check.
         * @param string $version      Optional. Package version. Defaults to empty string.
         * @param bool   $insecure     Optional. Whether to insecurely retry downloads that failed TLS handshake. Defaults
         *                             to false.
         */
        private function check_gitlab_package_name($project_name, $version = '', $insecure = \false)
        {
        }
        /**
         * Get the version to use for raw GitHub request. Very basic.
         *
         * @string $version Package version.
         * @string Version to use for GitHub request.
         */
        private function get_raw_git_version($version)
        {
        }
        /**
         * Gets the release tag for the latest stable release of a GitHub repository.
         *
         * @param string $package_name Name of the repository.
         *
         * @return string Release tag.
         */
        private function get_github_latest_release_tag($package_name, $insecure)
        {
        }
        /**
         * Guesses the version constraint from a release tag.
         *
         * @param string $tag Release tag to guess the version constraint from.
         *
         * @return string Version constraint.
         */
        private function guess_version_constraint_from_tag($tag)
        {
        }
        /**
         * Sets `COMPOSER_AUTH` environment variable (which Composer merges into the config setup in `Composer\Factory::createConfig()`) depending on available environment variables.
         * Avoids authorization failures when accessing various sites.
         */
        private function set_composer_auth_env_var()
        {
        }
        /**
         * Avoid using default Composer CA bundle if in phar as we don't include it.
         * See https://github.com/composer/ca-bundle/blob/1.1.0/src/CaBundle.php#L64
         */
        private function avoid_composer_ca_bundle()
        {
        }
        /**
         * Reads the WP-CLI packages composer.json, checking validity and returning array containing its path, contents, and decoded contents.
         *
         * @return array Indexed array containing the path, the contents, and the decoded contents of the WP-CLI packages composer.json.
         */
        private function get_composer_json_path_backup_decoded()
        {
        }
        /**
         * Registers a PHP shutdown function to revert composer.json unless
         * referenced `$revert` flag is false.
         *
         * @param string $json_path       Path to composer.json.
         * @param string $composer_backup Original contents of composer.json.
         * @param bool   &$revert         Flags whether to revert or not.
         */
        private function register_revert_shutdown_function($json_path, $composer_backup, &$revert)
        {
        }
        /**
         * Check whether we are dealing with Composer version 2.0.0+.
         *
         * @return bool
         */
        private function is_composer_v2()
        {
        }
        /**
         * Try to retrieve default branch via GitHub API.
         *
         * @param string $package_name GitHub package name to retrieve the default branch from.
         * @param bool   $insecure     Optional. Whether to insecurely retry downloads that failed TLS handshake. Defaults
         *                             to false.
         * @return string Default branch, or 'master' if it could not be retrieved.
         */
        private function get_github_default_branch($package_name, $insecure = \false)
        {
        }
    }
}
namespace WP_CLI {
    /**
     * @author Jordi Boggiano <j.boggiano@seld.be>
     */
    class JsonManipulator
    {
        private static $DEFINES = '(?(DEFINE)
       (?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )
       (?<boolean>   true | false | null )
       (?<string>    " ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
       (?<array>     \[  (?:  (?&json) \s* (?: , (?&json) \s* )*  )?  \s* \] )
       (?<pair>      \s* (?&string) \s* : (?&json) \s* )
       (?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
       (?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) )
    )';
        private $contents;
        private $newline;
        private $indent;
        public function __construct($contents)
        {
        }
        public function getContents()
        {
        }
        public function addLink($type, $package, $constraint, $sortPackages = false, $caseInsensitive = false)
        {
        }
        /**
         * Sorts packages by importance (platform packages first, then PHP dependencies) and alphabetically.
         *
         * @link https://getcomposer.org/doc/02-libraries.md#platform-packages
         *
         * @param array $packages
         */
        private function sortPackages(array &$packages = array())
        {
        }
        public function addRepository($name, $config)
        {
        }
        public function removeRepository($name)
        {
        }
        public function addConfigSetting($name, $value)
        {
        }
        public function removeConfigSetting($name)
        {
        }
        public function addProperty($name, $value)
        {
        }
        public function removeProperty($name)
        {
        }
        public function addSubNode($mainNode, $name, $value, $caseInsensitive = false)
        {
        }
        public function removeSubNode($mainNode, $name, $caseInsensitive = false)
        {
        }
        public function addMainKey($key, $content)
        {
        }
        public function removeMainKey($key)
        {
        }
        public function format($data, $depth = 0)
        {
        }
        protected function detectIndenting()
        {
        }
        protected function pregMatch($re, $str, &$matches = array())
        {
        }
    }
}
namespace WP_CLI\Package\Compat\Min_Composer_1_10 {
    trait NullIOMethodsTrait
    {
        /**
         * {@inheritDoc}
         */
        public function isVerbose()
        {
        }
        /**
         * {@inheritDoc}
         */
        public function write($messages, $newline = true, $verbosity = self::NORMAL)
        {
        }
        /**
         * {@inheritDoc}
         */
        public function writeError($messages, $newline = true, $verbosity = self::NORMAL)
        {
        }
        private static function output_clean_message($messages)
        {
        }
    }
}
namespace WP_CLI\Package\Compat\Min_Composer_2_3 {
    trait NullIOMethodsTrait
    {
        /**
         * {@inheritDoc}
         */
        public function isVerbose(): bool
        {
        }
        /**
         * {@inheritDoc}
         */
        public function write($messages, bool $newline = true, int $verbosity = self::NORMAL): void
        {
        }
        /**
         * {@inheritDoc}
         */
        public function writeError($messages, bool $newline = true, int $verbosity = self::NORMAL): void
        {
        }
        private static function output_clean_message($messages)
        {
        }
    }
}
namespace WP_CLI\Package\Compat {
    trait NullIOMethodsTrait
    {
        use \WP_CLI\Package\Compat\Min_Composer_2_3\NullIOMethodsTrait;
    }
}
namespace WP_CLI\Package {
    class ComposerIO extends \Composer\IO\NullIO
    {
        use \WP_CLI\Package\Compat\NullIOMethodsTrait;
    }
}
namespace {
    /**
     * Lists or flushes the site's rewrite rules, updates the permalink structure.
     *
     * See the WordPress [Rewrite API](https://codex.wordpress.org/Rewrite_API) and
     * [WP Rewrite](https://codex.wordpress.org/Class_Reference/WP_Rewrite) class reference.
     *
     * ## EXAMPLES
     *
     *     # Flush rewrite rules
     *     $ wp rewrite flush
     *     Success: Rewrite rules flushed.
     *
     *     # Update permalink structure
     *     $ wp rewrite structure '/%year%/%monthnum%/%postname%'
     *     Success: Rewrite structure set.
     *
     *     # List rewrite rules
     *     $ wp rewrite list --format=csv
     *     match,query,source
     *     ^wp-json/?$,index.php?rest_route=/,other
     *     ^wp-json/(.*)?,index.php?rest_route=/$matches[1],other
     *     category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$,index.php?category_name=$matches[1]&feed=$matches[2],category
     *     category/(.+?)/(feed|rdf|rss|rss2|atom)/?$,index.php?category_name=$matches[1]&feed=$matches[2],category
     *     category/(.+?)/embed/?$,index.php?category_name=$matches[1]&embed=true,category
     *
     * @package wp-cli
     */
    class Rewrite_Command extends \WP_CLI_Command
    {
        /**
         * Flushes rewrite rules.
         *
         * Resets WordPress' rewrite rules based on registered post types, etc.
         *
         * To regenerate a .htaccess file with WP-CLI, you'll need to add the mod_rewrite module
         * to your wp-cli.yml or config.yml. For example:
         *
         * ```
         * apache_modules:
         *   - mod_rewrite
         * ```
         *
         * ## OPTIONS
         *
         * [--hard]
         * : Perform a hard flush - update `.htaccess` rules as well as rewrite rules in database. Works only on single site installs.
         *
         * ## EXAMPLES
         *
         *     $ wp rewrite flush
         *     Success: Rewrite rules flushed.
         */
        public function flush($args, $assoc_args)
        {
        }
        /**
         * Updates the permalink structure.
         *
         * Sets the post permalink structure to the specified pattern.
         *
         * To regenerate a .htaccess file with WP-CLI, you'll need to add
         * the mod_rewrite module to your [WP-CLI config](https://make.wordpress.org/cli/handbook/config/#config-files).
         * For example:
         *
         * ```
         * apache_modules:
         *   - mod_rewrite
         * ```
         *
         * ## OPTIONS
         *
         * <permastruct>
         * : The new permalink structure to apply.
         *
         * [--category-base=<base>]
         * : Set the base for category permalinks, i.e. '/category/'.
         *
         * [--tag-base=<base>]
         * : Set the base for tag permalinks, i.e. '/tag/'.
         *
         * [--hard]
         * : Perform a hard flush - update `.htaccess` rules as well as rewrite rules in database.
         *
         * ## EXAMPLES
         *
         *     $ wp rewrite structure '/%year%/%monthnum%/%postname%/'
         *     Success: Rewrite structure set.
         */
        public function structure($args, $assoc_args)
        {
        }
        /**
         * Gets a list of the current rewrite rules.
         *
         * ## OPTIONS
         *
         * [--match=<url>]
         * : Show rewrite rules matching a particular URL.
         *
         * [--source=<source>]
         * : Show rewrite rules from a particular source.
         *
         * [--fields=<fields>]
         * : Limit the output to specific fields. Defaults to match,query,source.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     $ wp rewrite list --format=csv
         *     match,query,source
         *     ^wp-json/?$,index.php?rest_route=/,other
         *     ^wp-json/(.*)?,index.php?rest_route=/$matches[1],other
         *     category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$,index.php?category_name=$matches[1]&feed=$matches[2],category
         *     category/(.+?)/(feed|rdf|rss|rss2|atom)/?$,index.php?category_name=$matches[1]&feed=$matches[2],category
         *     category/(.+?)/embed/?$,index.php?category_name=$matches[1]&embed=true,category
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Exposes apache modules if present in config
         *
         * Implementation Notes: This function exposes a global function
         * apache_get_modules and also sets the $is_apache global variable.
         *
         * This is so that flush_rewrite_rules will actually write out the
         * .htaccess file for apache WordPress installations. There is a check
         * to see:
         *
         * 1. if the $is_apache variable is set.
         * 2. if the mod_rewrite module is returned from the apache_get_modules
         *    function.
         *
         * To get this to work with wp-cli you'll need to add the mod_rewrite module
         * to your config.yml. For example
         *
         * ```
         * apache_modules:
         *   - mod_rewrite
         * ```
         *
         * If this isn't done then the .htaccess rewrite rules won't be flushed out
         * to disk.
         */
        private static function apache_modules()
        {
        }
        /**
         * Displays a warning if --skip-plugins or --skip-themes are in use.
         *
         * Skipping the loading of plugins or themes can mean some rewrite rules
         * are unregistered, which may cause erroneous behavior.
         */
        private static function check_skip_plugins_themes()
        {
        }
    }
    /**
     * Adds, removes, and lists capabilities of a user role.
     *
     * See references for [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities) and [WP User class](https://codex.wordpress.org/Class_Reference/WP_User).
     *
     * ## EXAMPLES
     *
     *     # Add 'spectate' capability to 'author' role.
     *     $ wp cap add 'author' 'spectate'
     *     Success: Added 1 capability to 'author' role.
     *
     *     # Add all caps from 'editor' role to 'author' role.
     *     $ wp cap list 'editor' | xargs wp cap add 'author'
     *     Success: Added 24 capabilities to 'author' role.
     *
     *     # Remove all caps from 'editor' role that also appear in 'author' role.
     *     $ wp cap list 'author' | xargs wp cap remove 'editor'
     *     Success: Removed 34 capabilities from 'editor' role.
     */
    class Capabilities_Command extends \WP_CLI_Command
    {
        /**
         * List of available fields.
         *
         * @var array
         */
        private $fields = ['name'];
        /**
         * Lists capabilities for a given role.
         *
         * ## OPTIONS
         *
         * <role>
         * : Key for the role.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: list
         * options:
         *   - list
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * [--show-grant]
         * : Display all capabilities defined for a role including grant.
         * ---
         * default: false
         * ---
         *
         * ## EXAMPLES
         *
         *     # Display alphabetical list of Contributor capabilities.
         *     $ wp cap list 'contributor' | sort
         *     delete_posts
         *     edit_posts
         *     level_0
         *     level_1
         *     read
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Adds capabilities to a given role.
         *
         * ## OPTIONS
         *
         * <role>
         * : Key for the role.
         *
         * <cap>...
         * : One or more capabilities to add.
         *
         * [--grant]
         * : Adds the capability as an explicit boolean value, instead of implicitly defaulting to `true`.
         * ---
         * default: true
         * options:
         *   - true
         *   - false
         * ---
         *
         * ## EXAMPLES
         *
         *     # Add 'spectate' capability to 'author' role.
         *     $ wp cap add author spectate
         *     Success: Added 1 capability to 'author' role.
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Removes capabilities from a given role.
         *
         * ## OPTIONS
         *
         * <role>
         * : Key for the role.
         *
         * <cap>...
         * : One or more capabilities to remove.
         *
         * ## EXAMPLES
         *
         *     # Remove 'spectate' capability from 'author' role.
         *     $ wp cap remove author spectate
         *     Success: Removed 1 capability from 'author' role.
         */
        public function remove($args)
        {
        }
        /**
         * Retrieve a specific role from the system.
         *
         * @param string $role Role to retrieve.
         * @return WP_Role Requested role.
         * @throws \WP_CLI\ExitException If the role could not be found.
         */
        private static function get_role($role)
        {
        }
        /**
         * Assert that the roles are persisted to the database.
         *
         * @throws \WP_CLI\ExitException If the roles are not persisted to the
         *                               database.
         */
        private static function persistence_check()
        {
        }
    }
    /**
     * Manages user roles, including creating new roles and resetting to defaults.
     *
     * See references for [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities) and [WP User class](https://codex.wordpress.org/Class_Reference/WP_User).
     *
     * ## EXAMPLES
     *
     *     # List roles.
     *     $ wp role list --fields=role --format=csv
     *     role
     *     administrator
     *     editor
     *     author
     *     contributor
     *     subscriber
     *
     *     # Check to see if a role exists.
     *     $ wp role exists editor
     *     Success: Role with ID 'editor' exists.
     *
     *     # Create a new role.
     *     $ wp role create approver Approver
     *     Success: Role with key 'approver' created.
     *
     *     # Delete an existing role.
     *     $ wp role delete approver
     *     Success: Role with key 'approver' deleted.
     *
     *     # Reset existing roles to their default capabilities.
     *     $ wp role reset administrator author contributor
     *     Success: Reset 3/3 roles.
     *
     * @package wp-cli
     */
    class Role_Command extends \WP_CLI_Command
    {
        /**
         * List of available fields.
         *
         * @var array
         */
        private $fields = ['name', 'role'];
        /**
         * Default roles as provided by WordPress Core.
         *
         * @var array
         */
        private $roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
        /**
         * Lists all roles.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--field=<field>]
         * : Prints the value of a single field.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each role:
         *
         * * name
         * * role
         *
         * There are no optional fields.
         *
         * ## EXAMPLES
         *
         *     # List roles.
         *     $ wp role list --fields=role --format=csv
         *     role
         *     administrator
         *     editor
         *     author
         *     contributor
         *     subscriber
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Checks if a role exists.
         *
         * Exits with return code 0 if the role exists, 1 if it does not.
         *
         * ## OPTIONS
         *
         * <role-key>
         * : The internal name of the role.
         *
         * ## EXAMPLES
         *
         *     # Check if a role exists.
         *     $ wp role exists editor
         *     Success: Role with ID 'editor' exists.
         */
        public function exists($args)
        {
        }
        /**
         * Creates a new role.
         *
         * ## OPTIONS
         *
         * <role-key>
         * : The internal name of the role.
         *
         * <role-name>
         * : The publicly visible name of the role.
         *
         * [--clone=<role>]
         * : Clone capabilities from an existing role.
         *
         * ## EXAMPLES
         *
         *     # Create role for Approver.
         *     $ wp role create approver Approver
         *     Success: Role with key 'approver' created.
         *
         *     # Create role for Product Administrator.
         *     $ wp role create productadmin "Product Administrator"
         *     Success: Role with key 'productadmin' created.
         */
        public function create($args, $assoc_args)
        {
        }
        /**
         * Deletes an existing role.
         *
         * ## OPTIONS
         *
         * <role-key>
         * : The internal name of the role.
         *
         * ## EXAMPLES
         *
         *     # Delete approver role.
         *     $ wp role delete approver
         *     Success: Role with key 'approver' deleted.
         *
         *     # Delete productadmin role.
         *     $ wp role delete productadmin
         *     Success: Role with key 'productadmin' deleted.
         */
        public function delete($args)
        {
        }
        /**
         * Resets any default role to default capabilities.
         *
         * Uses WordPress' `populate_roles()` function to put one or more
         * roles back into the state they were at in the a fresh
         * WordPress install. Removes any capabilities that were added,
         * and restores any capabilities that were removed. Custom roles
         * are not affected.
         *
         * ## OPTIONS
         *
         * [<role-key>...]
         * : The internal name of one or more roles to reset.
         *
         * [--all]
         * : If set, all default roles will be reset.
         *
         * ## EXAMPLES
         *
         *     # Reset three roles.
         *     $ wp role reset administrator author contributor
         *     Restored 1 capability to and removed 0 capabilities from 'administrator' role.
         *     No changes necessary for 'author' role.
         *     No changes necessary for 'contributor' role.
         *     Success: 1 of 3 roles reset.
         *
         *     # Reset a custom role.
         *     $ wp role reset custom_role
         *     Custom role 'custom_role' not affected.
         *     Error: Must specify a default role to reset.
         *
         *     # Reset all default roles.
         *     $ wp role reset --all
         *     Success: All default roles reset.
         */
        public function reset($args, $assoc_args)
        {
        }
        /**
         * Assert that the roles are persisted to the database.
         *
         * @throws \WP_CLI\ExitException If the roles are not persisted to the
         *                               database.
         */
        private static function persistence_check()
        {
        }
    }
    /**
     * Generates code for post types, taxonomies, plugins, child themes, etc.
     *
     * ## EXAMPLES
     *
     *     # Generate a new plugin with unit tests.
     *     $ wp scaffold plugin sample-plugin
     *     Success: Created plugin files.
     *     Success: Created test files.
     *
     *     # Generate theme based on _s.
     *     $ wp scaffold _s sample-theme --theme_name="Sample Theme" --author="John Doe"
     *     Success: Created theme 'Sample Theme'.
     *
     *     # Generate code for post type registration in given theme.
     *     $ wp scaffold post-type movie --label=Movie --theme=simple-life
     *     Success: Created '/var/www/example.com/public_html/wp-content/themes/simple-life/post-types/movie.php'.
     *
     * @package wp-cli
     */
    class Scaffold_Command extends \WP_CLI_Command
    {
        /**
         * Generates PHP code for registering a custom post type.
         *
         * ## OPTIONS
         *
         * <slug>
         * : The internal name of the post type.
         *
         * [--label=<label>]
         * : The text used to translate the update messages.
         *
         * [--textdomain=<textdomain>]
         * : The textdomain to use for the labels.
         *
         * [--dashicon=<dashicon>]
         * : The dashicon to use in the menu.
         *
         * [--theme]
         * : Create a file in the active theme directory, instead of sending to
         * STDOUT. Specify a theme with `--theme=<theme>` to have the file placed in that theme.
         *
         * [--plugin=<plugin>]
         * : Create a file in the given plugin's directory, instead of sending to STDOUT.
         *
         * [--raw]
         * : Just generate the `register_post_type()` call and nothing else.
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     # Generate a 'movie' post type for the 'simple-life' theme
         *     $ wp scaffold post-type movie --label=Movie --theme=simple-life
         *     Success: Created '/var/www/example.com/public_html/wp-content/themes/simple-life/post-types/movie.php'.
         *
         * @subcommand post-type
         *
         * @alias      cpt
         */
        public function post_type($args, $assoc_args)
        {
        }
        /**
         * Generates PHP code for registering a custom taxonomy.
         *
         * ## OPTIONS
         *
         * <slug>
         * : The internal name of the taxonomy.
         *
         * [--post_types=<post-types>]
         * : Post types to register for use with the taxonomy.
         *
         * [--label=<label>]
         * : The text used to translate the update messages.
         *
         * [--textdomain=<textdomain>]
         * : The textdomain to use for the labels.
         *
         * [--theme]
         * : Create a file in the active theme directory, instead of sending to
         * STDOUT. Specify a theme with `--theme=<theme>` to have the file placed in that theme.
         *
         * [--plugin=<plugin>]
         * : Create a file in the given plugin's directory, instead of sending to STDOUT.
         *
         * [--raw]
         * : Just generate the `register_taxonomy()` call and nothing else.
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     # Generate PHP code for registering a custom taxonomy and save in a file
         *     $ wp scaffold taxonomy venue --post_types=event,presentation > taxonomy.php
         *
         * @subcommand taxonomy
         *
         * @alias      tax
         */
        public function taxonomy($args, $assoc_args)
        {
        }
        private function scaffold($slug, $assoc_args, $defaults, $subdir, $templates)
        {
        }
        /**
         * Generates PHP, JS and CSS code for registering a Gutenberg block for a plugin or theme.
         *
         * **Warning: `wp scaffold block` is deprecated.**
         *
         * The official script to generate a block is the [@wordpress/create-block](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-create-block/) package.
         *
         * See the [Create a Block tutorial](https://developer.wordpress.org/block-editor/getting-started/tutorial/) for a complete walk-through.
         *
         * ## OPTIONS
         *
         * <slug>
         * : The internal name of the block.
         *
         * [--title=<title>]
         * : The display title for your block.
         *
         * [--dashicon=<dashicon>]
         * : The dashicon to make it easier to identify your block.
         *
         * [--category=<category>]
         * : The category name to help users browse and discover your block.
         * ---
         * default: widgets
         * options:
         *   - common
         *   - embed
         *   - formatting
         *   - layout
         *   - widgets
         * ---
         *
         * [--theme]
         * : Create files in the active theme directory. Specify a theme with `--theme=<theme>` to have the file placed in that theme.
         *
         * [--plugin=<plugin>]
         * : Create files in the given plugin's directory.
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * @subcommand block
         */
        public function block($args, $assoc_args)
        {
        }
        /**
         * Generates starter code for a theme based on _s.
         *
         * See the [Underscores website](https://underscores.me/) for more details.
         *
         * ## OPTIONS
         *
         * <slug>
         * : The slug for the new theme, used for prefixing functions.
         *
         * [--activate]
         * : Activate the newly downloaded theme.
         *
         * [--enable-network]
         * : Enable the newly downloaded theme for the entire network.
         *
         * [--theme_name=<title>]
         * : What to put in the 'Theme Name:' header in 'style.css'.
         *
         * [--author=<full-name>]
         * : What to put in the 'Author:' header in 'style.css'.
         *
         * [--author_uri=<uri>]
         * : What to put in the 'Author URI:' header in 'style.css'.
         *
         * [--sassify]
         * : Include stylesheets as SASS.
         *
         * [--woocommerce]
         * : Include WooCommerce boilerplate files.
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     # Generate a theme with name "Sample Theme" and author "John Doe"
         *     $ wp scaffold _s sample-theme --theme_name="Sample Theme" --author="John Doe"
         *     Success: Created theme 'Sample Theme'.
         *
         * @alias _s
         */
        public function underscores($args, $assoc_args)
        {
        }
        /**
         * Generates child theme based on an existing theme.
         *
         * Creates a child theme folder with `functions.php` and `style.css` files.
         *
         * ## OPTIONS
         *
         * <slug>
         * : The slug for the new child theme.
         *
         * --parent_theme=<slug>
         * : What to put in the 'Template:' header in 'style.css'.
         *
         * [--theme_name=<title>]
         * : What to put in the 'Theme Name:' header in 'style.css'.
         *
         * [--author=<full-name>]
         * : What to put in the 'Author:' header in 'style.css'.
         *
         * [--author_uri=<uri>]
         * : What to put in the 'Author URI:' header in 'style.css'.
         *
         * [--theme_uri=<uri>]
         * : What to put in the 'Theme URI:' header in 'style.css'.
         *
         * [--activate]
         * : Activate the newly created child theme.
         *
         * [--enable-network]
         * : Enable the newly created child theme for the entire network.
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     # Generate a 'sample-theme' child theme based on TwentySixteen
         *     $ wp scaffold child-theme sample-theme --parent_theme=twentysixteen
         *     Success: Created '/var/www/example.com/public_html/wp-content/themes/sample-theme'.
         *
         * @subcommand child-theme
         */
        public function child_theme($args, $assoc_args)
        {
        }
        private function get_output_path($assoc_args, $subdir)
        {
        }
        /**
         * Generates starter code for a plugin.
         *
         * The following files are always generated:
         *
         * * `plugin-slug.php` is the main PHP plugin file.
         * * `readme.txt` is the readme file for the plugin.
         * * `package.json` needed by NPM holds various metadata relevant to the project. Packages: `grunt`, `grunt-wp-i18n` and `grunt-wp-readme-to-markdown`. Scripts: `start`, `readme`, `i18n`.
         * * `Gruntfile.js` is the JS file containing Grunt tasks. Tasks: `i18n` containing `addtextdomain` and `makepot`, `readme` containing `wp_readme_to_markdown`.
         * * `.editorconfig` is the configuration file for Editor.
         * * `.gitignore` tells which files (or patterns) git should ignore.
         * * `.distignore` tells which files and folders should be ignored in distribution.
         *
         * The following files are also included unless the `--skip-tests` is used:
         *
         * * `phpunit.xml.dist` is the configuration file for PHPUnit.
         * * `.circleci/config.yml` is the configuration file for CircleCI. Use `--ci=<provider>` to select a different service.
         * * `bin/install-wp-tests.sh` configures the WordPress test suite and a test database.
         * * `tests/bootstrap.php` is the file that makes the current plugin active when running the test suite.
         * * `tests/test-sample.php` is a sample file containing test cases.
         * * `.phpcs.xml.dist` is a collection of PHP_CodeSniffer rules.
         *
         * ## OPTIONS
         *
         * <slug>
         * : The internal name of the plugin.
         *
         * [--dir=<dirname>]
         * : Put the new plugin in some arbitrary directory path. Plugin directory will be path plus supplied slug.
         *
         * [--plugin_name=<title>]
         * : What to put in the 'Plugin Name:' header.
         *
         * [--plugin_description=<description>]
         * : What to put in the 'Description:' header.
         *
         * [--plugin_author=<author>]
         * : What to put in the 'Author:' header.
         *
         * [--plugin_author_uri=<url>]
         * : What to put in the 'Author URI:' header.
         *
         * [--plugin_uri=<url>]
         * : What to put in the 'Plugin URI:' header.
         *
         * [--skip-tests]
         * : Don't generate files for unit testing.
         *
         * [--ci=<provider>]
         * : Choose a configuration file for a continuous integration provider.
         * ---
         * default: circle
         * options:
         *   - circle
         *   - gitlab
         *   - bitbucket
         *   - github
         * ---
         *
         * [--activate]
         * : Activate the newly generated plugin.
         *
         * [--activate-network]
         * : Network activate the newly generated plugin.
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     $ wp scaffold plugin sample-plugin
         *     Success: Created plugin files.
         *     Success: Created test files.
         */
        public function plugin($args, $assoc_args)
        {
        }
        /**
         * Generates files needed for running PHPUnit tests in a plugin.
         *
         * The following files are generated by default:
         *
         * * `phpunit.xml.dist` is the configuration file for PHPUnit.
         * * `.circleci/config.yml` is the configuration file for CircleCI. Use `--ci=<provider>` to select a different service.
         * * `bin/install-wp-tests.sh` configures the WordPress test suite and a test database.
         * * `tests/bootstrap.php` is the file that makes the current plugin active when running the test suite.
         * * `tests/test-sample.php` is a sample file containing the actual tests.
         * * `.phpcs.xml.dist` is a collection of PHP_CodeSniffer rules.
         *
         * Learn more from the [plugin unit tests documentation](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/).
         *
         * ## ENVIRONMENT
         *
         * The `tests/bootstrap.php` file looks for the WP_TESTS_DIR environment
         * variable.
         *
         * ## OPTIONS
         *
         * [<plugin>]
         * : The name of the plugin to generate test files for.
         *
         * [--dir=<dirname>]
         * : Generate test files for a non-standard plugin path. If no plugin slug is specified, the directory name is used.
         *
         * [--ci=<provider>]
         * : Choose a configuration file for a continuous integration provider.
         * ---
         * default: circle
         * options:
         *   - circle
         *   - gitlab
         *   - bitbucket
         *   - github
         * ---
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     # Generate unit test files for plugin 'sample-plugin'.
         *     $ wp scaffold plugin-tests sample-plugin
         *     Success: Created test files.
         *
         * @subcommand plugin-tests
         */
        public function plugin_tests($args, $assoc_args)
        {
        }
        /**
         * Generates files needed for running PHPUnit tests in a theme.
         *
         * The following files are generated by default:
         *
         * * `phpunit.xml.dist` is the configuration file for PHPUnit.
         * * `.circleci/config.yml` is the configuration file for CircleCI. Use `--ci=<provider>` to select a different service.
         * * `bin/install-wp-tests.sh` configures the WordPress test suite and a test database.
         * * `tests/bootstrap.php` is the file that makes the current theme active when running the test suite.
         * * `tests/test-sample.php` is a sample file containing the actual tests.
         * * `.phpcs.xml.dist` is a collection of PHP_CodeSniffer rules.
         *
         * Learn more from the [plugin unit tests documentation](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/).
         *
         * ## ENVIRONMENT
         *
         * The `tests/bootstrap.php` file looks for the WP_TESTS_DIR environment
         * variable.
         *
         * ## OPTIONS
         *
         * [<theme>]
         * : The name of the theme to generate test files for.
         *
         * [--dir=<dirname>]
         * : Generate test files for a non-standard theme path. If no theme slug is specified, the directory name is used.
         *
         * [--ci=<provider>]
         * : Choose a configuration file for a continuous integration provider.
         * ---
         * default: circle
         * options:
         *   - circle
         *   - gitlab
         *   - bitbucket
         *   - github
         * ---
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     # Generate unit test files for theme 'twentysixteenchild'.
         *     $ wp scaffold theme-tests twentysixteenchild
         *     Success: Created test files.
         *
         * @subcommand theme-tests
         */
        public function theme_tests($args, $assoc_args)
        {
        }
        private function scaffold_plugin_theme_tests($args, $assoc_args, $type)
        {
        }
        /**
         * Checks that the `$target_dir` is a child directory of the WP themes or plugins directory, depending on `$type`.
         *
         * @param string $type       "theme" or "plugin"
         * @param string $target_dir The theme/plugin directory to check.
         *
         * @return null|string Returns null on success, error message on error.
         */
        private function check_target_directory($type, $target_dir)
        {
        }
        protected function create_files($files_and_contents, $force)
        {
        }
        protected function prompt_if_files_will_be_overwritten($filename, $force)
        {
        }
        protected function log_whether_files_written($files_written, $skip_message, $success_message)
        {
        }
        /**
         * Extracts dashicon name when provided or return null otherwise.
         *
         * @param array $assoc_args
         * @return string|null
         */
        private function extract_dashicon($assoc_args)
        {
        }
        /**
         * If you're writing your files to your theme directory your textdomain also needs to be the same as your theme.
         * Same goes for when plugin is being used.
         */
        private function get_textdomain($textdomain, $args)
        {
        }
        /**
         * Generates the machine name for function declarations.
         *
         * @param string $slug Slug name to convert.
         * @return string
         */
        private function generate_machine_name($slug)
        {
        }
        /**
         * Pluralizes a noun.
         *
         * @see    Inflector::pluralize()
         * @param  string $word Word to be pluralized.
         * @return string
         */
        private function pluralize($word)
        {
        }
        protected function extract_args($assoc_args, $defaults)
        {
        }
        protected function quote_comma_list_elements($comma_list)
        {
        }
        /**
         * Creates the themes directory if it doesn't already exist.
         */
        protected function maybe_create_themes_dir()
        {
        }
        /**
         * Creates the plugins directory if it doesn't already exist.
         */
        protected function maybe_create_plugins_dir()
        {
        }
        /**
         * Initializes WP_Filesystem.
         */
        protected function init_wp_filesystem()
        {
        }
        /**
         * Localizes the template path.
         */
        private static function mustache_render($template, $data = [])
        {
        }
        /**
         * Gets the template path based on installation type.
         */
        private static function get_template_path($template)
        {
        }
        /*
         * Returns the canonicalized path, with dot and double dot segments resolved.
         *
         * Copied from Symfony\Component\DomCrawler\AbstractUriElement::canonicalizePath().
         * Implements RFC 3986, section 5.2.4.
         *
         * @param string $path The path to make canonical.
         *
         * @return string The canonicalized path.
         */
        private static function canonicalize_path($path)
        {
        }
        /**
         * Gets an active theme's name when true provided or the same name otherwise.
         *
         * @param string|bool $theme Theme name or true.
         * @return string
         */
        private function get_theme_name($theme)
        {
        }
    }
    class Search_Replace_Command extends \WP_CLI_Command
    {
        private $dry_run;
        private $export_handle = \false;
        private $export_insert_size;
        private $recurse_objects;
        private $regex;
        private $regex_flags;
        private $regex_delimiter;
        private $regex_limit = -1;
        private $skip_tables;
        private $skip_columns;
        private $include_columns;
        private $format;
        private $report;
        private $verbose;
        private $report_changed_only;
        private $log_handle = \null;
        private $log_before_context = 40;
        private $log_after_context = 40;
        private $log_prefixes = array('< ', '> ');
        private $log_colors;
        private $log_encoding;
        private $start_time;
        /**
         * Searches/replaces strings in the database.
         *
         * Searches through all rows in a selection of tables and replaces
         * appearances of the first string with the second string.
         *
         * By default, the command uses tables registered to the `$wpdb` object. On
         * multisite, this will just be the tables for the current site unless
         * `--network` is specified.
         *
         * Search/replace intelligently handles PHP serialized data, and does not
         * change primary key values.
         *
         * ## OPTIONS
         *
         * <old>
         * : A string to search for within the database.
         *
         * <new>
         * : Replace instances of the first string with this new string.
         *
         * [<table>...]
         * : List of database tables to restrict the replacement to. Wildcards are
         * supported, e.g. `'wp_*options'` or `'wp_post*'`.
         *
         * [--dry-run]
         * : Run the entire search/replace operation and show report, but don't save
         * changes to the database.
         *
         * [--network]
         * : Search/replace through all the tables registered to $wpdb in a
         * multisite install.
         *
         * [--all-tables-with-prefix]
         * : Enable replacement on any tables that match the table prefix even if
         * not registered on $wpdb.
         *
         * [--all-tables]
         * : Enable replacement on ALL tables in the database, regardless of the
         * prefix, and even if not registered on $wpdb. Overrides --network
         * and --all-tables-with-prefix.
         *
         * [--export[=<file>]]
         * : Write transformed data as SQL file instead of saving replacements to
         * the database. If <file> is not supplied, will output to STDOUT.
         *
         * [--export_insert_size=<rows>]
         * : Define number of rows in single INSERT statement when doing SQL export.
         * You might want to change this depending on your database configuration
         * (e.g. if you need to do fewer queries). Default: 50
         *
         * [--skip-tables=<tables>]
         * : Do not perform the replacement on specific tables. Use commas to
         * specify multiple tables. Wildcards are supported, e.g. `'wp_*options'` or `'wp_post*'`.
         *
         * [--skip-columns=<columns>]
         * : Do not perform the replacement on specific columns. Use commas to
         * specify multiple columns.
         *
         * [--include-columns=<columns>]
         * : Perform the replacement on specific columns. Use commas to
         * specify multiple columns.
         *
         * [--precise]
         * : Force the use of PHP (instead of SQL) which is more thorough,
         * but slower.
         *
         * [--recurse-objects]
         * : Enable recursing into objects to replace strings. Defaults to true;
         * pass --no-recurse-objects to disable.
         *
         * [--verbose]
         * : Prints rows to the console as they're updated.
         *
         * [--regex]
         * : Runs the search using a regular expression (without delimiters).
         * Warning: search-replace will take about 15-20x longer when using --regex.
         *
         * [--regex-flags=<regex-flags>]
         * : Pass PCRE modifiers to regex search-replace (e.g. 'i' for case-insensitivity).
         *
         * [--regex-delimiter=<regex-delimiter>]
         * : The delimiter to use for the regex. It must be escaped if it appears in the search string. The default value is the result of `chr(1)`.
         *
         * [--regex-limit=<regex-limit>]
         * : The maximum possible replacements for the regex per row (or per unserialized data bit per row). Defaults to -1 (no limit).
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - count
         * ---
         *
         * [--report]
         * : Produce report. Defaults to true.
         *
         * [--report-changed-only]
         * : Report changed fields only. Defaults to false, unless logging, when it defaults to true.
         *
         * [--log[=<file>]]
         * : Log the items changed. If <file> is not supplied or is "-", will output to STDOUT.
         * Warning: causes a significant slow down, similar or worse to enabling --precise or --regex.
         *
         * [--before_context=<num>]
         * : For logging, number of characters to display before the old match and the new replacement. Default 40. Ignored if not logging.
         *
         * [--after_context=<num>]
         * : For logging, number of characters to display after the old match and the new replacement. Default 40. Ignored if not logging.
         *
         * ## EXAMPLES
         *
         *     # Search and replace but skip one column
         *     $ wp search-replace 'http://example.test' 'http://example.com' --skip-columns=guid
         *
         *     # Run search/replace operation but dont save in database
         *     $ wp search-replace 'foo' 'bar' wp_posts wp_postmeta wp_terms --dry-run
         *
         *     # Run case-insensitive regex search/replace operation (slow)
         *     $ wp search-replace '\[foo id="([0-9]+)"' '[bar id="\1"' --regex --regex-flags='i'
         *
         *     # Turn your production multisite database into a local dev database
         *     $ wp search-replace --url=example.com example.com example.test 'wp_*options' wp_blogs wp_site --network
         *
         *     # Search/replace to a SQL file without transforming the database
         *     $ wp search-replace foo bar --export=database.sql
         *
         *     # Bash script: Search/replace production to development url (multisite compatible)
         *     #!/bin/bash
         *     if $(wp --url=http://example.com core is-installed --network); then
         *         wp search-replace --url=http://example.com 'http://example.com' 'http://example.test' --recurse-objects --network --skip-columns=guid --skip-tables=wp_users
         *     else
         *         wp search-replace 'http://example.com' 'http://example.test' --recurse-objects --skip-columns=guid --skip-tables=wp_users
         *     fi
         */
        public function __invoke($args, $assoc_args)
        {
        }
        private function php_export_table($table, $old, $new)
        {
        }
        private function sql_handle_col($col, $primary_keys, $table, $old, $new)
        {
        }
        private function php_handle_col($col, $primary_keys, $table, $old, $new)
        {
        }
        private function write_sql_row_fields($table, $rows)
        {
        }
        private static function get_columns($table)
        {
        }
        private static function is_text_col($type)
        {
        }
        private static function esc_like($old)
        {
        }
        /**
         * Escapes (backticks) MySQL identifiers (aka schema object names) - i.e. column names, table names, and database/index/alias/view etc names.
         * See https://dev.mysql.com/doc/refman/5.5/en/identifiers.html
         *
         * @param string|array $idents A single identifier or an array of identifiers.
         * @return string|array An escaped string if given a string, or an array of escaped strings if given an array of strings.
         */
        private static function esc_sql_ident($idents)
        {
        }
        /**
         * Puts MySQL string values in single quotes, to avoid them being interpreted as column names.
         *
         * @param string|array $values A single value or an array of values.
         * @return string|array A quoted string if given a string, or an array of quoted strings if given an array of strings.
         */
        private static function esc_sql_value($values)
        {
        }
        /**
         * Gets the color codes from the options if any, and returns the passed in array colorized with 2 elements per entry, a color code (or '') and a reset (or '').
         *
         * @param array $assoc_args The associative argument array passed to the command.
         * @param array $colors Array of default percent color code strings keyed by the color contexts.
         * @return array Array containing 2-element arrays keyed to the input $colors array.
         */
        private function get_colors($assoc_args, $colors)
        {
        }
        /*
         * Logs the difference between old match and new replacement for SQL replacement.
         *
         * @param string $col Column being processed.
         * @param array $primary_keys Primary keys for table.
         * @param string $table Table being processed.
         * @param string $old Old value to match.
         * @param string $new New value to replace the old value with.
         * @return int Count of changed rows.
         */
        private function log_sql_diff($col, $primary_keys, $table, $old, $new)
        {
        }
        /*
         * Logs the difference between old matches and new replacements at the end of a PHP (regex) replacement of a database row.
         *
         * @param string $col Column being processed.
         * @param array $keys Associative array (or object) of primary key names and their values for the row being processed.
         * @param string $table Table being processed.
         * @param string $old Old value to match.
         * @param string $new New value to replace the old value with.
         * @param array $log_data Array of data strings before replacements.
         */
        private function log_php_diff($col, $keys, $table, $old, $new, $log_data)
        {
        }
        /**
         * Returns the arrays of old matches and new replacements based on the passed-in matches, with context.
         *
         * @param string $search_regex The search regular expression.
         * @param string $old_data Existing data being processed.
         * @param array $old_matches Old matches array returned by `preg_match_all()`.
         * @param string $new New value to replace the old value with.
         * @return array Two element array containing the array of old match log strings and the array of new replacement log strings with before/after contexts.
         */
        private function log_bits($search_regex, $old_data, $old_matches, $new)
        {
        }
        /*
         * Outputs the log strings.
         *
         * @param string $col Column being processed.
         * @param array $keys Associative array (or object) of primary key names and their values for the row being processed.
         * @param string $table Table being processed.
         * @param array $old_bits Array of old match log strings.
         * @param array $new_bits Array of new replacement log strings.
         */
        private function log_write($col, $keys, $table, $old_bits, $new_bits)
        {
        }
    }
}
namespace WP_CLI {
    class SearchReplacer
    {
        private $from;
        private $to;
        private $recurse_objects;
        private $regex;
        private $regex_flags;
        private $regex_delimiter;
        private $regex_limit;
        private $logging;
        private $log_data;
        private $max_recursion;
        /**
         * @param string  $from            String we're looking to replace.
         * @param string  $to              What we want it to be replaced with.
         * @param bool    $recurse_objects Should objects be recursively replaced?
         * @param bool    $regex           Whether `$from` is a regular expression.
         * @param string  $regex_flags     Flags for regular expression.
         * @param string  $regex_delimiter Delimiter for regular expression.
         * @param bool    $logging         Whether logging.
         * @param integer $regex_limit     The maximum possible replacements for each pattern in each subject string.
         */
        public function __construct($from, $to, $recurse_objects = false, $regex = false, $regex_flags = '', $regex_delimiter = '/', $logging = false, $regex_limit = -1)
        {
        }
        /**
         * Take a serialised array and unserialise it replacing elements as needed and
         * unserialising any subordinate arrays and performing the replace on those too.
         * Ignores any serialized objects unless $recurse_objects is set to true.
         *
         * @param array|string $data            The data to operate on.
         * @param bool         $serialised      Does the value of $data need to be unserialized?
         *
         * @return array       The original array with all elements replaced as needed.
         */
        public function run($data, $serialised = false)
        {
        }
        /**
         * @param int          $recursion_level Current recursion depth within the original data.
         * @param array        $visited_data    Data that has been seen in previous recursion iterations.
         */
        private function run_recursively($data, $serialised, $recursion_level = 0, $visited_data = array())
        {
        }
        /**
         * Gets existing data saved for this run when logging.
         * @return array Array of data strings, prior to replacements.
         */
        public function get_log_data()
        {
        }
        /**
         * Clears data stored for logging.
         */
        public function clear_log_data()
        {
        }
        /**
         * Get the PCRE error constant name from an error value.
         *
         * @param  integer $error Error code.
         * @return string         Error constant name.
         */
        private function preg_error_message($error)
        {
        }
    }
}
namespace {
    class Server_Command extends \WP_CLI_Command
    {
        /**
         * Launches PHP's built-in web server for a specific WordPress installation.
         *
         * Uses `php -S` to launch a web server serving the WordPress webroot.
         * <http://php.net/manual/en/features.commandline.webserver.php>
         *
         * Importantly, PHP's built-in web server doesn't support `.htaccess` files.
         * If this is a requirement, please use a more advanced web server.
         *
         * ## OPTIONS
         *
         * [--host=<host>]
         * : The hostname to bind the server to.
         * ---
         * default: localhost
         * ---
         *
         * [--port=<port>]
         * : The port number to bind the server to.
         * ---
         * default: 8080
         * ---
         *
         * [--docroot=<path>]
         * : The path to use as the document root. If the path global parameter is
         * set, the default value is it.
         *
         * [--config=<file>]
         * : Configure the server with a specific .ini file.
         *
         * ## EXAMPLES
         *
         *     # Make the instance available on any address (with port 8080)
         *     $ wp server --host=0.0.0.0
         *     PHP 5.6.9 Development Server started at Tue May 24 01:27:11 2016
         *     Listening on http://0.0.0.0:8080
         *     Document root is /
         *     Press Ctrl-C to quit.
         *
         *     # Run on port 80 (for multisite)
         *     $ wp server --host=localhost.localdomain --port=80
         *     PHP 5.6.9 Development Server started at Tue May 24 01:30:06 2016
         *     Listening on http://localhost1.localdomain1:80
         *     Document root is /
         *     Press Ctrl-C to quit.
         *
         *     # Configure the server with a specific .ini file
         *     $ wp server --config=development.ini
         *     PHP 7.0.9 Development Server started at Mon Aug 22 12:09:04 2016
         *     Listening on http://localhost:8080
         *     Document root is /
         *     Press Ctrl-C to quit.
         *
         * @when before_wp_load
         */
        public function __invoke($_, $assoc_args)
        {
        }
    }
    class Shell_Command extends \WP_CLI_Command
    {
        /**
         * Opens an interactive PHP console for running and testing PHP code.
         *
         * `wp shell` allows you to evaluate PHP statements and expressions
         * interactively, from within a WordPress environment. Type a bit of code,
         * hit enter, and see the code execute right before you. Because WordPress
         * is loaded, you have access to all the functions, classes and globals
         * that you can use within a WordPress plugin, for example.
         *
         * ## OPTIONS
         *
         * [--basic]
         * : Force the use of WP-CLI's built-in PHP REPL, even if the Boris or
         * PsySH PHP REPLs are available.
         *
         * ## EXAMPLES
         *
         *     # Call get_bloginfo() to get the name of the site.
         *     $ wp shell
         *     wp> get_bloginfo( 'name' );
         *     => string(6) "WP-CLI"
         */
        public function __invoke($_, $assoc_args)
        {
        }
    }
}
namespace WP_CLI\Shell {
    class REPL
    {
        private $prompt;
        private $history_file;
        public function __construct($prompt)
        {
        }
        public function start()
        {
        }
        private static function non_expressions()
        {
        }
        private function prompt()
        {
        }
        private static function create_prompt_cmd($prompt, $history_path)
        {
        }
        private function set_history_file()
        {
        }
        private static function starts_with($tokens, $line)
        {
        }
    }
}
namespace {
    /**
     * Lists, adds, or removes super admin users on a multisite installation.
     *
     * ## EXAMPLES
     *
     *     # List user with super-admin capabilities.
     *     $ wp super-admin list
     *     supervisor
     *     administrator
     *
     *     # Grant super-admin privileges to the user.
     *     $ wp super-admin add superadmin2
     *     Success: Granted super-admin capabilities to 1 user.
     *
     *     # Revoke super-admin privileges from the user.
     *     $ wp super-admin remove superadmin2
     *     Success: Revoked super-admin capabilities from 1 user.
     *
     * @package wp-cli
     */
    class Super_Admin_Command extends \WP_CLI_Command
    {
        private $fields = ['user_login'];
        private $fetcher;
        public function __construct()
        {
        }
        /**
         * Lists users with super admin capabilities.
         *
         * ## OPTIONS
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: list
         * options:
         *   - list
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - ids
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # List user with super-admin capabilities.
         *     $ wp super-admin list
         *     supervisor
         *     administrator
         *
         * @subcommand list
         */
        public function list_subcommand($_, $assoc_args)
        {
        }
        /**
         * Grants super admin privileges to one or more users.
         *
         * ## OPTIONS
         *
         * <user>...
         * : One or more user IDs, user emails, or user logins.
         *
         * ## EXAMPLES
         *
         *     # Grant super-admin privileges to the user.
         *     $ wp super-admin add superadmin2
         *     Success: Granted super-admin capabilities to 1 user.
         */
        public function add($args, $_)
        {
        }
        /**
         * Removes super admin privileges from one or more users.
         *
         * ## OPTIONS
         *
         * <user>...
         * : One or more user IDs, user emails, or user logins.
         *
         * ## EXAMPLES
         *
         *     # Revoke super-admin privileges from the user.
         *     $ wp super-admin remove superadmin2
         *     Success: Revoked super-admin capabilities from 1 user.
         */
        public function remove($args, $_)
        {
        }
        private static function get_admins()
        {
        }
    }
    /**
     * Lists registered sidebars.
     *
     * A [sidebar](https://developer.wordpress.org/themes/functionality/sidebars/) is any widgetized area of your theme.
     *
     * ## EXAMPLES
     *
     *     # List sidebars
     *     $ wp sidebar list --fields=name,id --format=csv
     *     name,id
     *     "Widget Area",sidebar-1
     *     "Inactive Widgets",wp_inactive_widgets
     */
    class Sidebar_Command extends \WP_CLI_Command
    {
        private $fields = ['name', 'id', 'description'];
        /**
         * Lists registered sidebars.
         *
         * ## OPTIONS
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - ids
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each sidebar:
         *
         * * name
         * * id
         * * description
         *
         * These fields are optionally available:
         *
         * * class
         * * before_widget
         * * after_widget
         * * before_title
         * * after_title
         *
         * ## EXAMPLES
         *
         *     $ wp sidebar list --fields=name,id --format=csv
         *     name,id
         *     "Widget Area",sidebar-1
         *     "Inactive Widgets",wp_inactive_widgets
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
    }
    /**
     * Manages widgets, including adding and moving them within sidebars.
     *
     * A [widget](https://developer.wordpress.org/themes/functionality/widgets/) adds content and features to a widget area (also called a [sidebar](https://developer.wordpress.org/themes/functionality/sidebars/)).
     *
     * ## EXAMPLES
     *
     *     # List widgets on a given sidebar
     *     $ wp widget list sidebar-1
     *     +----------+------------+----------+----------------------+
     *     | name     | id         | position | options              |
     *     +----------+------------+----------+----------------------+
     *     | meta     | meta-6     | 1        | {"title":"Meta"}     |
     *     | calendar | calendar-2 | 2        | {"title":"Calendar"} |
     *     +----------+------------+----------+----------------------+
     *
     *     # Add a calendar widget to the second position on the sidebar
     *     $ wp widget add calendar sidebar-1 2
     *     Success: Added widget to sidebar.
     *
     *     # Update option(s) associated with a given widget
     *     $ wp widget update calendar-1 --title="Calendar"
     *     Success: Widget updated.
     *
     *     # Delete one or more widgets entirely
     *     $ wp widget delete calendar-2 archive-1
     *     Success: 2 widgets removed from sidebar.
     */
    class Widget_Command extends \WP_CLI_Command
    {
        private $fields = ['name', 'id', 'position', 'options'];
        /**
         * Lists widgets associated with a sidebar.
         *
         * ## OPTIONS
         *
         * <sidebar-id>
         * : ID for the corresponding sidebar.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - ids
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## AVAILABLE FIELDS
         *
         * These fields will be displayed by default for each widget:
         *
         * * name
         * * id
         * * position
         * * options
         *
         * There are no optionally available fields.
         *
         * ## EXAMPLES
         *
         *     $ wp widget list sidebar-1 --fields=name,id --format=csv
         *     name,id
         *     meta,meta-5
         *     search,search-3
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Adds a widget to a sidebar.
         *
         * Creates a new widget entry in the database, and associates it with the
         * sidebar.
         *
         * ## OPTIONS
         *
         * <name>
         * : Widget name.
         *
         * <sidebar-id>
         * : ID for the corresponding sidebar.
         *
         * [<position>]
         * : Widget's current position within the sidebar. Defaults to last
         *
         * [--<field>=<value>]
         * : Widget option to add, with its new value
         *
         * ## EXAMPLES
         *
         *     # Add a new calendar widget to sidebar-1 with title "Calendar"
         *     $ wp widget add calendar sidebar-1 2 --title="Calendar"
         *     Success: Added widget to sidebar.
         *
         * @subcommand add
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Updates options for an existing widget.
         *
         * ## OPTIONS
         *
         * <widget-id>
         * : Unique ID for the widget
         *
         * [--<field>=<value>]
         * : Field to update, with its new value
         *
         * ## EXAMPLES
         *
         *     # Change calendar-1 widget title to "Our Calendar"
         *     $ wp widget update calendar-1 --title="Our Calendar"
         *     Success: Widget updated.
         *
         * @subcommand update
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Moves the position of a widget.
         *
         * Changes the order of a widget in its existing sidebar, or moves it to a
         * new sidebar.
         *
         * ## OPTIONS
         *
         * <widget-id>
         * : Unique ID for the widget
         *
         * [--position=<position>]
         * : Assign the widget to a new position.
         *
         * [--sidebar-id=<sidebar-id>]
         * : Assign the widget to a new sidebar
         *
         * ## EXAMPLES
         *
         *     # Change position of widget
         *     $ wp widget move recent-comments-2 --position=2
         *     Success: Widget moved.
         *
         *     # Move widget to Inactive Widgets
         *     $ wp widget move recent-comments-2 --sidebar-id=wp_inactive_widgets
         *     Success: Widget moved.
         *
         * @subcommand move
         */
        public function move($args, $assoc_args)
        {
        }
        /**
         * Deactivates one or more widgets from an active sidebar.
         *
         * Moves widgets to Inactive Widgets.
         *
         * ## OPTIONS
         *
         * <widget-id>...
         * : Unique ID for the widget(s)
         *
         * ## EXAMPLES
         *
         *     # Deactivate the recent-comments-2 widget.
         *     $ wp widget deactivate recent-comments-2
         *     Success: 1 widget deactivated.
         *
         * @subcommand deactivate
         */
        public function deactivate($args, $assoc_args)
        {
        }
        /**
         * Deletes one or more widgets from a sidebar.
         *
         * ## OPTIONS
         *
         * <widget-id>...
         * : Unique ID for the widget(s)
         *
         * ## EXAMPLES
         *
         *     # Delete the recent-comments-2 widget from its sidebar.
         *     $ wp widget delete recent-comments-2
         *     Success: Deleted 1 of 1 widgets.
         *
         * @subcommand delete
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Resets sidebar.
         *
         * Removes all widgets from the sidebar and places them in Inactive Widgets.
         *
         * ## OPTIONS
         *
         * [<sidebar-id>...]
         * : One or more sidebars to reset.
         *
         * [--all]
         * : If set, all sidebars will be reset.
         *
         * ## EXAMPLES
         *
         *     # Reset a sidebar
         *     $ wp widget reset sidebar-1
         *     Success: Sidebar 'sidebar-1' reset.
         *
         *     # Reset multiple sidebars
         *     $ wp widget reset sidebar-1 sidebar-2
         *     Success: Sidebar 'sidebar-1' reset.
         *     Success: Sidebar 'sidebar-2' reset.
         *
         *     # Reset all sidebars
         *     $ wp widget reset --all
         *     Success: Sidebar 'sidebar-1' reset.
         *     Success: Sidebar 'sidebar-2' reset.
         *     Success: Sidebar 'sidebar-3' reset.
         */
        public function reset($args, $assoc_args)
        {
        }
        /**
         * Checks whether a sidebar is a valid sidebar
         *
         * @param string $sidebar_id
         */
        private function validate_sidebar($sidebar_id)
        {
        }
        /**
         * Checks whether the specified widget is on the sidebar
         *
         * @param string $widget_id
         */
        private function validate_sidebar_widget($widget_id)
        {
        }
        /**
         * Gets the widgets (and their associated data) for a given sidebar
         *
         * @param string $sidebar_id
         * @return array
         */
        private function get_sidebar_widgets($sidebar_id)
        {
        }
        /**
         * Re-implementation of wp_get_sidebars_widgets()
         * because the original has a nasty global component
         */
        private function wp_get_sidebars_widgets()
        {
        }
        /**
         * Gets the widget's name, option index, sidebar, and sidebar index from its ID
         *
         * @param string $widget_id
         * @return array
         */
        private function get_widget_data($widget_id)
        {
        }
        /**
         * Gets the options for a given widget
         *
         * @param string $name
         * @return array
         */
        private function get_widget_options($name)
        {
        }
        /**
         * Updates the options for a given widget
         *
         * @param string $name
         * @param mixed
         */
        private function update_widget_options($name, $value)
        {
        }
        /**
         * Repositions a widget within a sidebar or move to another sidebar.
         *
         * @param string $widget_id
         * @param string|null $current_sidebar_id
         * @param string $new_sidebar_id
         * @param int|null $current_index
         * @param int $new_index
         */
        private function move_sidebar_widget($widget_id, $current_sidebar_id, $new_sidebar_id, $current_index, $new_index)
        {
        }
        /**
         * Gets a widget's instantiated object based on its name
         *
         * @param string $id_base Name of the widget
         * @return WP_Widget|false
         */
        private function get_widget_obj($id_base)
        {
        }
        /**
         * Cleans up a widget's options based on its update callback
         *
         * @param string $id_base Name of the widget
         * @param mixed $dirty_options
         * @param mixed $old_options
         * @return mixed
         */
        private function sanitize_widget_options($id_base, $dirty_options, $old_options)
        {
        }
    }
}