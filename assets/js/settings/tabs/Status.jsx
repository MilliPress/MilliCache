import {
	Spinner,
	Popover,
	Button,
	Icon,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import {
	check,
	caution,
	error,
} from '@wordpress/icons';

const STATUS_ICON_SIZE = 20;

const DOCS_BASE = 'https://millipress.com/docs/millicache';
const DOC_LINKS = {
	connection: `${ DOCS_BASE }/08-storage-backends/01-overview#basic-connection`,
	dropIn: `${ DOCS_BASE }/01-getting-started/20-installation#advanced-cache-php-issues`,
	memorySizing: `${ DOCS_BASE }/08-storage-backends/01-overview#memory-sizing`,
	serverConfig: `${ DOCS_BASE }/08-storage-backends/01-overview#recommended-server-configuration`,
};

const STATUS_ICONS = {
	ok: { icon: check, className: 'millicache-status-ok' },
	warning: { icon: caution, className: 'millicache-status-warning' },
	error: {
		icon: error,
		className: 'millicache-status-error',
	},
};

const StatusIndicator = ( { status, message, docsLink } ) => {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ popoverAnchor, setPopoverAnchor ] = useState( null );
	const cfg = STATUS_ICONS[ status ];
	if ( ! cfg ) {
		return null;
	}

	const interactive =
		( status === 'warning' || status === 'error' ) && message;

	if ( ! interactive ) {
		return (
			<span
				className={ `millicache-status-icon ${ cfg.className }` }
				role="presentation"
			>
				<Icon icon={ cfg.icon } size={ STATUS_ICON_SIZE } />
			</span>
		);
	}

	return (
		<span className="millicache-status-popover-wrapper">
			<Button
				ref={ setPopoverAnchor }
				className={ `millicache-status-icon millicache-status-icon--button ${ cfg.className }` }
				icon={ cfg.icon }
				iconSize={ STATUS_ICON_SIZE }
				size="small"
				aria-label={ message }
				showTooltip={ false }
				onClick={ () => setIsOpen( ( o ) => ! o ) }
				aria-expanded={ isOpen }
			/>
			{ isOpen && popoverAnchor && (
				<Popover
					anchor={ popoverAnchor }
					placement="top"
					offset={ 8 }
					noArrow={ false }
					onClose={ () => setIsOpen( false ) }
					className="millicache-info-popover"
					focusOnMount="firstElement"
				>
					<div className="millicache-info-popover__content">
						<p>
							{ message }
							{ docsLink && (
								<>
									{ ' ' }
									<a
										href={ docsLink }
										target="_blank"
										rel="noopener noreferrer"
										className="millicache-info-popover__link"
									>
										{ __( 'Learn more', 'millicache' ) }
									</a>
								</>
							) }
						</p>
					</div>
				</Popover>
			) }
		</span>
	);
};

const renderTable = ( rows ) => (
	<table className="widefat striped fixed" cellSpacing="0">
		<tbody>
			{ rows.map(
				( {
					label,
					value,
					status,
					statusMessage,
					statusDocsLink,
				} ) => (
					<tr key={ label }>
						<td>
							<strong>{ label }:</strong>
						</td>
						<td>
							<StatusIndicator
								status={ status }
								message={ statusMessage }
								docsLink={ statusDocsLink }
							/>
							<code>{ value }</code>
						</td>
					</tr>
				)
			) }
		</tbody>
	</table>
);

const StatusTab = ( { status } ) => {
	const { isLoadingSettings } = window.MilliBase.hooks.useSettings();

	const cacheSize = status.cache?.size ?? 0;
	const cacheGross = status.cache?.gross ?? 0;
	const cacheIndex = status.cache?.index ?? 0;
	const dedupActive = cacheGross > cacheSize && cacheSize > 0;
	const savedPct = dedupActive
		? Math.round( ( ( cacheGross - cacheSize ) / cacheGross ) * 100 )
		: 0;
	const savedLabel = dedupActive
		? sprintf(
				/* translators: 1: bytes saved (formatted), 2: percentage saved */
				__( '%1$s (%2$d%%)', 'millicache' ),
				status.cache?.saved_human ?? 'N/A',
				savedPct
		  )
		: null;

	const dropinMissing =
		Array.isArray( status.dropin ) && status.dropin.length === 0;
	const dropinOutdated = ! dropinMissing && status.dropin?.outdated;
	const dropinSymlinked = ! dropinMissing && status.dropin?.type === 'symlink';
	const dropinCustomized = ! dropinMissing && status.dropin?.custom;

	const dropinStatusValue = dropinMissing
		? __( 'Missing', 'millicache' )
		: dropinOutdated
		? __( 'Outdated', 'millicache' )
		: __( 'Up to date', 'millicache' );

	const dropinStatusHealth = dropinMissing
		? 'error'
		: dropinOutdated
		? 'warning'
		: 'ok';

	const dropinStatusAdvice = dropinMissing
		? __(
				'The advanced-cache.php drop-in is not installed. Re-install it from the MilliCache settings to engage caching at the earliest possible moment.',
				'millicache'
		  )
		: dropinOutdated
		? __(
				'The installed drop-in is older than the version bundled with the plugin. Re-install it to pick up improvements and bug fixes.',
				'millicache'
		  )
		: null;

	const dropinTypeValue = dropinMissing
		? __( 'Missing', 'millicache' )
		: ( dropinSymlinked
				? __( 'Symlinked', 'millicache' )
				: __( 'Copied', 'millicache' ) ) +
		  ( dropinCustomized
				? ' & ' + __( 'Customized', 'millicache' )
				: '' );

	const dropinTypeHealth = dropinMissing
		? 'error'
		: dropinCustomized
		? 'warning'
		: dropinSymlinked
		? 'ok'
		: 'warning';

	const dropinTypeAdvice = dropinMissing
		? null
		: dropinCustomized
		? __(
				'A custom drop-in is in place. MilliCache won’t overwrite your changes, but you’ll need to merge plugin updates into it manually.',
				'millicache'
		  )
		: dropinSymlinked
		? null
		: __(
				'The drop-in is a static file copy. Switching to a symlink lets it update automatically with the plugin.',
				'millicache'
		  );

	const memoryPolicy = status.storage?.info?.Memory?.maxmemory_policy ?? '';
	const memoryPolicyOk = memoryPolicy === 'allkeys-lru';
	const memoryPolicyAdvice = memoryPolicyOk
		? null
		: __(
				'For a cache workload, allkeys-lru is recommended so the storage server can automatically evict least-recently-used entries when full.',
				'millicache'
		  );

	const maxMemoryRaw = status.storage?.info?.Memory?.maxmemory ?? 0;
	const maxMemoryHealth =
		Number( maxMemoryRaw ) > 0 ? 'ok' : 'warning';
	const maxMemoryAdvice =
		Number( maxMemoryRaw ) > 0
			? null
			: __(
					'No memory limit is set on the storage server. Without one, the cache can grow until it crowds out other workloads on the host.',
					'millicache'
			  );

	// Per-site multisite payload only carries `connected`; host/port/db
	// rows require the full storage payload (network admin & single-site).
	const connectionInfo = [
		{
			label: __( 'Status', 'millicache' ),
			value: status.storage?.connected
				? __( 'Connected', 'millicache' )
				: __( 'Disconnected', 'millicache' ),
			status: status.storage?.connected ? 'ok' : 'error',
			statusMessage: status.storage?.connected
				? null
				: __(
						'MilliCache cannot reach the configured storage server. Check the host, port, and credentials in the Storage settings.',
						'millicache'
				  ),
			statusDocsLink: DOC_LINKS.connection,
		},
		status.storage?.config?.host && {
			label: __( 'Host', 'millicache' ),
			value: status.storage.config.host,
		},
		status.storage?.config?.port && {
			label: __( 'Port', 'millicache' ),
			value: status.storage.config.port,
		},
		status.storage?.config?.database !== undefined && {
			label: __( 'Database', 'millicache' ),
			value: status.storage.config.database,
		},
	].filter( Boolean );

	// Drop-in is install-wide; only present in single-site and network payloads.
	const dropinRows = status.dropin
		? [
				{
					label: __( 'Drop-in Status', 'millicache' ),
					value: dropinStatusValue,
					status: dropinStatusHealth,
					statusMessage: dropinStatusAdvice,
					statusDocsLink: DOC_LINKS.dropIn,
				},
				{
					label: __( 'Drop-in Type', 'millicache' ),
					value: dropinTypeValue,
					status: dropinTypeHealth,
					statusMessage: dropinTypeAdvice,
					statusDocsLink: DOC_LINKS.dropIn,
				},
		  ]
		: [];

	const cacheInfo = [
		{
			label: __( 'Cached pages', 'millicache' ),
			value: cacheIndex,
		},
		{
			label: __( 'Cache size', 'millicache' ),
			value: status.cache?.size_human ?? 'N/A',
		},
		...( dedupActive
			? [
					{
						label: __( 'Storage saved', 'millicache' ),
						value: savedLabel,
						status: 'ok',
					},
			  ]
			: [] ),
		...( cacheIndex > 0
			? [
					{
						label: __( 'Largest entry', 'millicache' ),
						value: status.cache?.largest_human ?? 'N/A',
					},
			  ]
			: [] ),
		...dropinRows,
	];

	const storageInfo = [
		{
			label: __( 'Version', 'millicache' ),
			value: status.storage?.info?.Server?.version ?? 'N/A',
		},
		{
			label: __( 'Databases Available', 'millicache' ),
			value: status.storage?.config?.databases ?? 'N/A',
		},
		{
			label: __( 'Used Memory', 'millicache' ),
			value: status.storage?.info?.Memory?.used_memory_human ?? 'N/A',
		},
		{
			label: __( 'Max Memory', 'millicache' ),
			value: status.storage?.info?.Memory?.maxmemory_human ?? 'N/A',
			status: maxMemoryHealth,
			statusMessage: maxMemoryAdvice,
			statusDocsLink: DOC_LINKS.memorySizing,
		},
		{
			label: __( 'Max Memory Policy', 'millicache' ),
			value: memoryPolicy || 'N/A',
			status: memoryPolicyOk ? 'ok' : 'warning',
			statusMessage: memoryPolicyAdvice,
			statusDocsLink: DOC_LINKS.serverConfig,
		},
	];

	// Storage Server is install-wide; only network admin and single-site
	// payloads carry the server info needed to render it.
	const showStorageServer = Boolean( status.storage?.info );

	// Per-site multisite gets only the connection up/down indicator and
	// per-site cache numbers — collapse the lonely "Status" row into the
	// Cache table and drop the section headings.
	const isCompact = ! showStorageServer && connectionInfo.length === 1;

	return (
		<div>
			{ isLoadingSettings && <Spinner /> }
			{ status &&
				( isCompact ? (
					renderTable( [ ...connectionInfo, ...cacheInfo ] )
				) : (
					<>
						<h2>{ __( 'Connection', 'millicache' ) }</h2>
						{ renderTable( connectionInfo ) }

						<h2>{ __( 'Cache', 'millicache' ) }</h2>
						{ renderTable( cacheInfo ) }

						{ showStorageServer && (
							<>
								<h2>{ __( 'Storage Server', 'millicache' ) }</h2>
								{ renderTable( storageInfo ) }
							</>
						) }
					</>
				) ) }
		</div>
	);
};

export default StatusTab;
