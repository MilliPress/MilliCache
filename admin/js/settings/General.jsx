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
				title={ __( 'Redis Settings', 'millicache' ) }
				className={ `redis-settings-${
					status?.redis?.connected ? 'connected' : 'disconnected'
				}` }
				icon={ ! status?.redis?.connected ? plugins : connection }
				initialOpen={ ! status?.redis?.connected }
			>
				<Flex direction="column" gap="4">
					<Flex justify="start">
						<FlexItem isBlock="true">
							<InputControl
								__next40pxDefaultSize
								label={ __( 'Redis Host', 'millicache' ) }
								value={
									settings.redis.host ??
									status.redis?.config.host
								}
								disabled={ ! ( 'host' in settings.redis ) }
								onChange={ ( value ) =>
									updateSetting( 'redis', 'host', value )
								}
							/>
						</FlexItem>
						<FlexItem>
							<NumberControl
								__next40pxDefaultSize
								label={ __( 'Redis Port', 'millicache' ) }
								value={
									settings.redis.port ??
									status.redis?.config.port
								}
								disabled={ ! ( 'port' in settings.redis ) }
								min="1024"
								max="65535"
								onChange={ ( value ) =>
									updateSetting( 'redis', 'port', value )
								}
							/>
						</FlexItem>
					</Flex>
					<Flex justify="start">
						<FlexItem isBlock="true">
							<InputControl
								__next40pxDefaultSize
								type="password"
								label={ __( 'Redis Password', 'millicache' ) }
								value={ settings.redis.enc_password ?? '' }
								disabled={
									! ( 'enc_password' in settings.redis )
								}
								onChange={ ( value ) =>
									updateSetting(
										'redis',
										'enc_password',
										value
									)
								}
							/>
						</FlexItem>
						<FlexItem>
							<NumberControl
								__next40pxDefaultSize
								label={ __( 'Redis Database', 'millicache' ) }
								value={
									settings.redis.db ??
									status.redis?.config.database
								}
								disabled={ ! ( 'db' in settings.redis ) }
								max={ status?.redis?.config.databases ?? 16 }
								min="0"
								onChange={ ( value ) =>
									updateSetting( 'redis', 'db', value )
								}
							/>
						</FlexItem>
					</Flex>
					<FlexItem style={ { flexGrow: 0 } }>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __(
								'Persistent Redis Connection',
								'millicache'
							) }
							checked={
								settings.redis.persistent ??
								status.redis?.config.persistent
							}
							disabled={ ! ( 'persistent' in settings.redis ) }
							onChange={ ( value ) =>
								updateSetting( 'redis', 'persistent', value )
							}
						/>
					</FlexItem>
				</Flex>
			</PanelBody>
			<PanelBody
				title={ __( 'Cache Settings', 'millicache' ) }
				initialOpen={ status?.redis?.connected }
			>
				<Flex direction="column" gap="4">
					<Flex justify="start" align={ 'start' }>
						<FlexItem isBlock="true">
							<UnitControl
								__next40pxDefaultSize
								label={ __(
									'TTL (Cache Expiry)',
									'millicache'
								) }
								help={ __(
									'The time that the cache will be stored for.',
									'millicache'
								) }
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
								label={ __( 'Max TTL', 'millicache' ) }
								help={ __(
									'The maximum time stale cache will be stored for background update.',
									'millicache'
								) }
								disabled={ ! ( 'max_ttl' in settings.cache ) }
								value={ ( () => {
									const ttl =
										settings.cache.max_ttl ??
										status.cache?.max_ttl;
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
										'max_ttl',
										ttlInSeconds
									);
								} }
								min="1"
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
						label={ __( 'Enable Gzip Compression', 'millicache' ) }
						checked={ settings.cache.gzip ?? status.cache?.gzip }
						disabled={ ! ( 'gzip' in settings.cache ) }
						onChange={ ( value ) =>
							updateSetting( 'cache', 'gzip', value )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Enable Debugging', 'millicache' ) }
						checked={ settings.cache.debug ?? status.cache?.debug }
						disabled={ ! ( 'debug' in settings.cache ) }
						onChange={ ( value ) =>
							updateSetting( 'cache', 'debug', value )
						}
					/>
					<FormTokenField
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Ignored Cookies', 'millicache' ) }
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
						label={ __( 'No-Cache Cookies', 'millicache' ) }
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
						label={ __( 'Ignored Request Keys', 'millicache' ) }
						value={
							settings.cache.ignore_request_keys
								? settings.cache.ignore_request_keys
								: status.cache?.ignore_request_keys
						}
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
