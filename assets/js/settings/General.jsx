import {
	Flex,
	FlexItem,
	Panel,
	PanelBody,
	PanelRow,
	ToggleControl,
	FormTokenField,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { connection, plugins } from '@wordpress/icons';
import { useSettings } from './context/Settings.jsx';
import { LabelWithTooltip } from './partials/Components';

const GeneralSettings = () => {
	const { status, settings, updateSetting } = useSettings();

	return (
		<Panel>
			<PanelBody>
				<PanelRow>
					{ __(
						'Configure the general settings for the cache.',
						'millicache'
					) }
				</PanelRow>
			</PanelBody>
			<PanelBody
				title={ __( 'Storage Server', 'millicache' ) }
				className={ `storage-settings-${
					status?.storage?.connected ? 'connected' : 'disconnected'
				}` }
				icon={ ! status?.storage?.connected ? plugins : connection }
				initialOpen={ ! status?.storage?.connected }
			>
				<Flex direction="column" gap="4">
					<Flex justify="start">
						<FlexItem isBlock="true">
							<InputControl
								__next40pxDefaultSize
								label={
									<LabelWithTooltip
										label={ __( 'Server Host', 'millicache' ) }
										tooltip={ __( 'The hostname or IP address of your Redis, Valkey, KeyDB, or other compatible server. Typically "localhost" or "127.0.0.1" for local servers.', 'millicache' ) }
									/>
								}
								value={
									settings.storage.host ??
									status.storage?.config.host
								}
								disabled={ ! ( 'host' in settings.storage ) }
								onChange={ ( value ) =>
									updateSetting( 'storage', 'host', value )
								}
							/>
						</FlexItem>
						<FlexItem>
							<NumberControl
								__next40pxDefaultSize
								label={
									<LabelWithTooltip
										label={ __( 'Server Port', 'millicache' ) }
										tooltip={ __( 'The port your storage server listens on. Default is 6379 for most installations.', 'millicache' ) }
									/>
								}
								style={ { width: '120px' } }
								value={
									settings.storage.port ??
									status.storage?.config.port
								}
								disabled={ ! ( 'port' in settings.storage ) }
								min="1024"
								max="65535"
								onChange={ ( value ) =>
									updateSetting( 'storage', 'port', value )
								}
							/>
						</FlexItem>
					</Flex>
					<Flex justify="start">
						<FlexItem isBlock="true">
							<InputControl
								__next40pxDefaultSize
								type="password"
								label={
									<LabelWithTooltip
										label={ __( 'Authentication Password', 'millicache' ) }
										tooltip={ __( 'Password used to authenticate with your Redis, Valkey, or other compatible server. Leave empty if your server does not require authentication.', 'millicache' ) }
									/>
								}
								value={ settings.storage.enc_password ?? '' }
								disabled={
									! ( 'enc_password' in settings.storage )
								}
								onChange={ ( value ) =>
									updateSetting(
										'storage',
										'enc_password',
										value
									)
								}
							/>
						</FlexItem>
						<FlexItem>
							<NumberControl
								__next40pxDefaultSize
								label={
									<LabelWithTooltip
										label={ __( 'Database ID', 'millicache' ) }
										tooltip={ __( 'The database to use within your storage server (typically 0-15, with 0 being the default).', 'millicache' ) }
									/>
								}
								style={ { width: '120px' } }
								value={
									settings.storage.db ??
									status.storage?.config.database
								}
								disabled={ ! ( 'db' in settings.storage ) }
								max={ status?.storage?.config.databases -1 ?? 15 }
								min="0"
								onChange={ ( value ) =>
									updateSetting( 'storage', 'db', value )
								}
							/>
						</FlexItem>
					</Flex>
					<FlexItem style={ { flexGrow: 0 } }>
						<ToggleControl
							__nextHasNoMarginBottom
							label={
								<LabelWithTooltip
									label={ __( 'Persistent Storage Connection', 'millicache' ) }
									tooltip={ __( 'When enabled, maintains a persistent connection to the server instead of creating a new connection for each request. Improves performance but uses more server resources.', 'millicache' ) }
								/>
							}
							checked={
								settings.storage.persistent ??
								status.storage?.config.persistent
							}
							disabled={ ! ( 'persistent' in settings.storage ) }
							onChange={ ( value ) =>
								updateSetting( 'storage', 'persistent', value )
							}
						/>
					</FlexItem>
				</Flex>
			</PanelBody>
			<PanelBody
				title={ __( 'Cache Settings', 'millicache' ) }
				initialOpen={ status?.storage?.connected }
			>
				<Flex direction="column" gap="4">
					<Flex justify="start" align={ 'start' }>
						<FlexItem isBlock="true">
							<UnitControl
								__next40pxDefaultSize
								label={
									<LabelWithTooltip
										label={ __( 'TTL (Cache Expiry)', 'millicache' ) }
                                        tooltip={ __( 'Time To Live - How long cache is considered fresh before becoming stale. Fresh cache is served directly without regeneration.', 'millicache' ) }
                                    />
								}
								disabled={ ! ( 'ttl' in settings.cache ) }
								value={ ( () => {
									const ttl =
										settings.cache.ttl ?? status.cache?.ttl;
									let value, unit;
									if ( ttl % 86400 === 0 ) {
										value = ttl / 86400;
										unit = 'd';
									} else if ( ttl % 3600 === 0 ) {
										value = ttl / 3600;
										unit = 'h';
									} else if ( ttl % 60 === 0 ) {
										value = ttl / 60;
										unit = 'm';
									} else {
										value = ttl;
										unit = 's';
									}
									return `${ value }${ unit }`;
								} )() }
								onChange={ ( combinedValue ) => {
									const value = parseFloat( combinedValue );
									const unit = combinedValue.replace(
										value,
										''
									);

									let ttlInSeconds = value;
									switch ( unit ) {
										case 'm':
											ttlInSeconds = value * 60;
											break;
										case 'h':
											ttlInSeconds = value * 3600;
											break;
										case 'd':
											ttlInSeconds = value * 86400;
											break;
										case 's':
										default:
											ttlInSeconds = value;
											break;
									}
									updateSetting(
										'cache',
										'ttl',
										ttlInSeconds
									);
								} }
								min="1"
								units={ [
									{
										value: 's',
										label: __( 'Seconds', 'millicache' ),
										default: 3000,
									},
									{
										value: 'm',
										label: __( 'Minutes', 'millicache' ),
										default: 120,
									},
									{
										value: 'h',
										label: __( 'Hours', 'millicache' ),
										default: 24,
									},
									{
										value: 'd',
										label: __( 'Days', 'millicache' ),
										default: 7,
									},
								] }
							/>
						</FlexItem>
						<FlexItem isBlock="true">
							<UnitControl
								__next40pxDefaultSize
								label={
									<LabelWithTooltip
										label={ __( 'Grace Period', 'millicache' ) }
                                        tooltip={ __( 'Time after TTL expiration when stale cache can still be served while new content is generated in the background. Helps reduce user wait times. 0 = disable.', 'millicache' ) }
                                    />
								}
								disabled={ ! ( 'grace' in settings.cache ) }
								value={ ( () => {
									const ttl =
										settings.cache.grace ??
										status.cache?.grace;
									let value, unit;
									if ( ttl % 2592000 === 0 ) {
										value = ttl / 2592000;
										unit = 'm';
									} else if ( ttl % 604800 === 0 ) {
										value = ttl / 604800;
										unit = 'w';
									} else if ( ttl % 86400 === 0 ) {
										value = ttl / 86400;
										unit = 'd';
									} else if ( ttl % 3600 === 0 ) {
										value = ttl / 3600;
										unit = 'h';
									} else if ( ttl % 60 === 0 ) {
										value = ttl / 60;
										unit = 'm';
									} else {
										value = ttl;
										unit = 's';
									}
									return `${ value }${ unit }`;
								} )() }
								onChange={ ( combinedValue ) => {
									const value = parseFloat( combinedValue );
									const unit = combinedValue.replace(
										value,
										''
									);

									let ttlInSeconds = value;
									switch ( unit ) {
										case 'h':
											ttlInSeconds = value * 3600;
											break;
										case 'd':
											ttlInSeconds = value * 86400;
											break;
										case 'w':
											ttlInSeconds = value * 604800;
											break;
										case 'm':
											ttlInSeconds = value * 2592000;
											break;
										case 's':
										default:
											ttlInSeconds = value;
											break;
									}
									updateSetting(
										'cache',
										'grace',
										ttlInSeconds
									);
								} }
								units={ [
									{
										value: 'h',
										label: __( 'Hours', 'millicache' ),
										default: 12,
									},
									{
										value: 'd',
										label: __( 'Days', 'millicache' ),
										default: 7,
									},
									{
										value: 'w',
										label: __( 'Weeks', 'millicache' ),
										default: 4,
									},
									{
										value: 'm',
										label: __( 'Months', 'millicache' ),
										default: 1,
									},
								] }
							/>
						</FlexItem>
					</Flex>
					<ToggleControl
						__nextHasNoMarginBottom
						label={
							<LabelWithTooltip
								label={ __( 'Enable Gzip Compression', 'millicache' ) }
								tooltip={ __( 'Compresses cached data to reduce storage space usage. Slightly increases CPU usage but significantly reduces memory consumption.', 'millicache' ) }
							/>
						}
						checked={ settings.cache.gzip ?? status.cache?.gzip }
						disabled={ ! ( 'gzip' in settings.cache ) }
						onChange={ ( value ) =>
							updateSetting( 'cache', 'gzip', value )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={
							<LabelWithTooltip
								label={ __( 'Enable Debugging', 'millicache' ) }
								tooltip={ __( 'Adds detailed debug information to response headers such as the cache flags and times.', 'millicache' ) }
							/>
						}
						checked={ settings.cache.debug ?? status.cache?.debug }
						disabled={ ! ( 'debug' in settings.cache ) }
						onChange={ ( value ) =>
							updateSetting( 'cache', 'debug', value )
						}
					/>
                    <FormTokenField
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label={
                            <LabelWithTooltip
                                label={ __( 'No-Cache Paths', 'millicache' ) }
                                tooltip={ __( 'URL paths that are not cached. You can use * wildcards (e.g. "/shop/*") or regular expressions, which must be enclosed in / characters (e.g. "/^/products/[0-9]+$/").' ) }
                            />
                        }
                        placeholder={ __('Add path or pattern (e.g. "/shop/", "/blog/*", "/^/products/[0-9]+$/")', 'millicache') }
                        value={
                            settings.cache.nocache_paths
                                ? settings.cache.nocache_paths
                                : status.cache?.nocache_paths
                        }
                        disabled={ ! ( 'nocache_paths' in settings.cache ) }
                        onChange={ ( tokens ) =>
                            updateSetting( 'cache', 'nocache_paths', tokens )
                        }
                        suggestions={ [] }
                    />
                    <FormTokenField
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label={
                            <LabelWithTooltip
                                label={ __( 'No-Cache Cookies', 'millicache' ) }
                                tooltip={ __( 'Cookies that prevent caching. Example: "session_*" will skip caching if a cookie starting with "session_" is set. * = wildcard.' ) }
                            />
                        }
                        placeholder={ __('Add cookie name or pattern (e.g. "session_*")', 'millicache') }
                        value={
                            settings.cache.nocache_cookies
                                ? settings.cache.nocache_cookies
                                : status.cache?.nocache_cookies
                        }
                        disabled={ ! ( 'nocache_cookies' in settings.cache ) }
                        onChange={ ( tokens ) =>
                            updateSetting( 'cache', 'nocache_cookies', tokens )
                        }
                        suggestions={ [] }
                    />
					<FormTokenField
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={
							<LabelWithTooltip
								label={ __( 'Ignored Cookies', 'millicache' ) }
								tooltip={ __( 'Cookies that are ignored when creating cache keys. Example: "dark_mode" means all users share the same cache regardless of the preference. * = wildcard.' ) }
							/>
						}
                        placeholder={ __('Add cookie name or pattern (e.g. "dark_mode")', 'millicache') }
						value={
							settings.cache.ignore_cookies
								? settings.cache.ignore_cookies
								: status.cache?.ignore_cookies
						}
						disabled={ ! ( 'ignore_cookies' in settings.cache ) }
						onChange={ ( tokens ) =>
							updateSetting( 'cache', 'ignore_cookies', tokens )
						}
						suggestions={ [] }
					/>
					<FormTokenField
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={
							<LabelWithTooltip
								label={ __( 'Ignored Request Keys', 'millicache' ) }
								tooltip={ __( 'URL parameters that are ignored when creating cache keys. Example: "utm_*" means analytics parameters such as "utm_source" are ignored when creating cache keys. * = wildcard.' ) }
							/>
						}
						value={
							settings.cache.ignore_request_keys
								? settings.cache.ignore_request_keys
								: status.cache?.ignore_request_keys
						}
                        placeholder={ __('Add parameter name or pattern (e.g. "utm_*")', 'millicache') }
						disabled={
							! ( 'ignore_request_keys' in settings.cache )
						}
						onChange={ ( tokens ) =>
							updateSetting(
								'cache',
								'ignore_request_keys',
								tokens
							)
						}
						suggestions={ [] }
					/>
				</Flex>
			</PanelBody>
		</Panel>
	);
};

export default GeneralSettings;
