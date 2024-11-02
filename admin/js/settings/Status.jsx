import { Spinner, Notice, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSettings } from './context/Settings.jsx';

const StatusTab = () => {
	const { error, status, isLoading } = useSettings();

	const connectionInfo = {
		[ __( 'Status', 'millicache' ) ]: status.redis?.connected
			? __( 'Connected', 'millicache' )
			: __( 'Disconnected', 'millicache' ),
		[ __( 'Host', 'millicache' ) ]: status.redis?.config.host ?? 'N/A',
		[ __( 'Port', 'millicache' ) ]: status.redis?.config.port ?? 'N/A',
		[ __( 'Database', 'millicache' ) ]:
			status.redis?.config.database ?? 'N/A',
	};

	const cacheInfo = {
		[ __( 'Cache Index', 'millicache' ) ]: status.cache?.index ?? 'N/A',
		[ __( 'Cache Size', 'millicache' ) ]: status.cache?.size_human ?? 'N/A',
		[ __( 'Drop-in Status', 'millicache' ) ]:
			// eslint-disable-next-line no-nested-ternary
			Array.isArray( status.dropin ) && status.dropin.length === 0
				? __( 'Missing', 'millicache' )
				: status.dropin?.outdated
				? __( 'Outdated', 'millicache' )
				: __( 'Up to date', 'millicache' ),
		[ __( 'Drop-in Type', 'millicache' ) ]:
			Array.isArray( status.dropin ) && status.dropin.length === 0
				? __( 'Missing', 'millicache' )
				: ( status.dropin?.file === 'symlink'
						? __( 'Symlinked', 'millicache' )
						: __( 'Copied', 'millicache' ) ) +
				  ( status.dropin?.custom
						? ' & ' + __( 'Customized', 'millicache' )
						: '' ),
	};

	const redisInfo = {
		[ __( 'Version', 'millicache' ) ]:
			status.redis?.info?.Server?.redis_version ?? 'N/A',
		[ __( 'Databases Available', 'millicache' ) ]:
			status.redis?.config?.databases ?? 'N/A',
		[ __( 'Used Memory', 'millicache' ) ]:
			status.redis?.info?.Memory?.used_memory_human ?? 'N/A',
		[ __( 'Max Memory', 'millicache' ) ]:
			status.redis?.info?.Memory.maxmemory_human ?? 'N/A',
		[ __( 'Max Memory Policy', 'millicache' ) ]:
			status.redis?.info?.Memory.maxmemory_policy ?? 'N/A',
	};

	return (
		<PanelBody>
			{ isLoading && <Spinner /> }
			{ error && <Notice status="error">{ error }</Notice> }
			{ status && (
				<>
					<h2>{ __( 'Connection', 'millicache' ) }</h2>
					<table className="widefat striped fixed" cellSpacing="0">
						<tbody>
							{ Object.entries( connectionInfo ).map(
								( [ key, value ] ) => (
									<tr key={ key }>
										<td>
											<strong>{ key }:</strong>
										</td>
										<td>
											<code>{ value }</code>
										</td>
									</tr>
								)
							) }
						</tbody>
					</table>

					<h2>{ __( 'Cache', 'millicache' ) }</h2>
					<table className="widefat striped fixed" cellSpacing="0">
						<tbody>
							{ Object.entries( cacheInfo ).map(
								( [ key, value ] ) => (
									<tr key={ key }>
										<td>
											<strong>{ key }:</strong>
										</td>
										<td>
											<code>{ value }</code>
										</td>
									</tr>
								)
							) }
						</tbody>
					</table>

					<h2>{ __( 'Redis', 'millicache' ) }</h2>
					<table className="widefat striped fixed" cellSpacing="0">
						<tbody>
							{ Object.entries( redisInfo ).map(
								( [ key, value ] ) => (
									<tr key={ key }>
										<td>
											<strong>{ key }:</strong>
										</td>
										<td>
											<code>{ value }</code>
										</td>
									</tr>
								)
							) }
						</tbody>
					</table>
				</>
			) }
		</PanelBody>
	);
};

export default StatusTab;
