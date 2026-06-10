import { Spinner } from '@wordpress/components';
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

// Listened to by FooterStatus to open its modal.
const OPEN_MODAL_EVENT = 'millicache:open-status-modal';

// InfoPopover now lives in MilliBase; consume it from the shared registry.
const { InfoPopover } = window.MilliBase?.components ?? {};

const dispatchOpenModal = () => {
	window.dispatchEvent( new CustomEvent( OPEN_MODAL_EVENT ) );
};

const sortByWeight = ( panels ) =>
	[ ...panels ].sort(
		( a, b ) => ( a.weight ?? 100 ) - ( b.weight ?? 100 )
	);

// Monotone cubic (Fritsch–Carlson) — soft curves that never dip below baseline.
const smoothPath = ( pts ) => {
	const n = pts.length;
	if ( n < 2 ) {
		return '';
	}
	const xs = pts.map( ( p ) => p[ 0 ] );
	const ys = pts.map( ( p ) => p[ 1 ] );

	const slope = [];
	for ( let i = 0; i < n - 1; i++ ) {
		slope[ i ] = ( ys[ i + 1 ] - ys[ i ] ) / ( xs[ i + 1 ] - xs[ i ] );
	}

	const m = new Array( n );
	m[ 0 ] = slope[ 0 ];
	m[ n - 1 ] = slope[ n - 2 ];
	for ( let i = 1; i < n - 1; i++ ) {
		m[ i ] =
			slope[ i - 1 ] * slope[ i ] <= 0
				? 0
				: ( slope[ i - 1 ] + slope[ i ] ) / 2;
	}
	for ( let i = 0; i < n - 1; i++ ) {
		if ( slope[ i ] === 0 ) {
			m[ i ] = 0;
			m[ i + 1 ] = 0;
			continue;
		}
		const a = m[ i ] / slope[ i ];
		const b = m[ i + 1 ] / slope[ i ];
		const s = a * a + b * b;
		if ( s > 9 ) {
			const t = 3 / Math.sqrt( s );
			m[ i ] = t * a * slope[ i ];
			m[ i + 1 ] = t * b * slope[ i ];
		}
	}

	let d = `M${ xs[ 0 ].toFixed( 1 ) },${ ys[ 0 ].toFixed( 1 ) }`;
	for ( let i = 0; i < n - 1; i++ ) {
		const dx = ( xs[ i + 1 ] - xs[ i ] ) / 3;
		const c1x = xs[ i ] + dx;
		const c1y = ys[ i ] + m[ i ] * dx;
		const c2x = xs[ i + 1 ] - dx;
		const c2y = ys[ i + 1 ] - m[ i + 1 ] * dx;
		d += ` C${ c1x.toFixed( 1 ) },${ c1y.toFixed( 1 ) } ${ c2x.toFixed(
			1
		) },${ c2y.toFixed( 1 ) } ${ xs[ i + 1 ].toFixed( 1 ) },${ ys[
			i + 1
		].toFixed( 1 ) }`;
	}
	return d;
};

// Aggregate hourly buckets ({t, hits, misses}) into one entry per calendar day.
const dailyBuckets = ( series ) => {
	const days = [];
	series.forEach( ( bucket ) => {
		const date = ( bucket.t ?? '' ).slice( 0, 8 );
		if ( date.length < 8 ) {
			return;
		}
		const last = days[ days.length - 1 ];
		if ( ! last || last.date !== date ) {
			days.push( { date, hits: 0, misses: 0 } );
		}
		const day = days[ days.length - 1 ];
		day.hits += bucket.hits ?? 0;
		day.misses += bucket.misses ?? 0;
	} );
	return days;
};

// Dependency-free hit-ratio sparkline for the KPI tile (per-day ratios).
const Sparkline = ( { series } ) => {
	if ( ! Array.isArray( series ) || series.length < 2 ) {
		return null;
	}

	const width = 120;
	const height = 28;

	// Per-day ratios (non-empty days), spread evenly — smoother than hourly.
	const ratios = dailyBuckets( series )
		.map( ( day ) => {
			const total = day.hits + day.misses;
			return total > 0 ? day.hits / total : null;
		} )
		.filter( ( ratio ) => ratio !== null );

	if ( ratios.length < 2 ) {
		return null;
	}

	const coords = ratios.map( ( ratio, index ) => {
		const x = ( index / ( ratios.length - 1 ) ) * width;
		const y = height - ratio * ( height - 4 ) - 2;
		return [ x, y ];
	} );

	const line = smoothPath( coords );
	const area = `${ line } L${ coords[ coords.length - 1 ][ 0 ].toFixed(
		1
	) },${ height } L${ coords[ 0 ][ 0 ].toFixed( 1 ) },${ height } Z`;

	return (
		<svg
			className="millicache-status-kpi__spark"
			viewBox={ `0 0 ${ width } ${ height }` }
			preserveAspectRatio="none"
			aria-hidden="true"
			focusable="false"
		>
			<path className="millicache-status-kpi__spark-area" d={ area } />
			<path className="millicache-status-kpi__spark-line" d={ line } />
		</svg>
	);
};

const KpiTile = ( { panel } ) => (
	<div className="millicache-status-kpi">
		<div className="millicache-status-kpi__label">
			<span className="millicache-status-kpi__label-text">
				{ panel.label }
			</span>
			{ InfoPopover && <InfoPopover info={ panel.info } /> }
		</div>
		<span className="millicache-status-kpi__value">{ panel.value }</span>
		{ panel.detail && (
			<span className="millicache-status-kpi__detail">
				{ panel.detail }
			</span>
		) }
		{ panel.series && <Sparkline series={ panel.series } /> }
	</div>
);

// Subtle one-line Pro teaser; `%PRO%` is where the linked label is spliced in.
const ProTeaser = ( { panel } ) => {
	if ( ! panel.text || ! panel.cta_url || ! panel.cta_label ) {
		return null;
	}

	const [ before, after = '' ] = panel.text.split( '%PRO%' );

	return (
		<p className="millicache-status-pro">
			{ before }
			{ /* No `noreferrer` — Referer is wanted for stats; `noopener` still blocks tabnabbing. */ }
			{ /* eslint-disable-next-line react/jsx-no-target-blank */ }
			<a
				href={ panel.cta_url }
				className="millicache-status-pro__link"
				target="_blank"
				rel="noopener"
			>
				{ panel.cta_label }
			</a>
			{ after }
		</p>
	);
};

// Clean axis max that hugs the data (~10% headroom, snapped to a nice step).
const axisTop = ( max ) => {
	if ( max <= 0 ) {
		return 1;
	}
	const target = max * 1.1;
	const mag = Math.pow( 10, Math.floor( Math.log10( target ) ) );
	const norm = target / mag;
	const step =
		[ 1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10 ].find( ( s ) => s >= norm ) ?? 10;
	return step * mag;
};

// Horizontal inset (viewBox units) for the axis labels. The chart runs
// edge-to-edge (the plot bleeds past the card padding), so the line/area/grid
// fill the full width while the labels keep this gap from the card edges.
const AXIS_PAD = 20;

const compact = ( n ) => {
	if ( n >= 1000000 ) {
		return `${ +( n / 1000000 ).toFixed( 1 ) }M`;
	}
	if ( n >= 1000 ) {
		return `${ +( n / 1000 ).toFixed( 1 ) }k`;
	}
	return String( n );
};

// Daily "requests served" stacked area chart — hourly buckets summed per day.
const TrendChart = ( { series } ) => {
	const [ activeDay, setActiveDay ] = useState( null );
	const svgRef = useRef( null );

	if ( ! Array.isArray( series ) || series.length < 2 ) {
		return null;
	}

	const days = dailyBuckets( series );

	if ( days.length < 2 ) {
		return null;
	}

	const W = 760;
	const H = 210;
	// No horizontal gutter — full-width area; y-labels float top-left.
	const pad = { l: 0, r: 0, t: 12, b: 20 };
	const iw = W - pad.l - pad.r;
	const ih = H - pad.t - pad.b;
	const n = days.length;

	// Stacked: uncached on the baseline, cached the band up to the total envelope.
	const top = axisTop( Math.max( 1, ...days.map( ( d ) => d.hits + d.misses ) ) );
	const xAt = ( i ) => pad.l + ( i / ( n - 1 ) ) * iw;
	const yAt = ( v ) => pad.t + ih - ( v / top ) * ih;
	const baseY = ( pad.t + ih ).toFixed( 1 );

	const uncachedPts = days.map( ( d, i ) => [ xAt( i ), yAt( d.misses ) ] );
	const totalPts = days.map( ( d, i ) => [ xAt( i ), yAt( d.hits + d.misses ) ] );

	const uncachedLine = smoothPath( uncachedPts );
	const totalLine = smoothPath( totalPts );
	const lastX = uncachedPts[ n - 1 ][ 0 ].toFixed( 1 );
	const firstX = uncachedPts[ 0 ][ 0 ].toFixed( 1 );

	const uncachedArea = `${ uncachedLine } L${ lastX },${ baseY } L${ firstX },${ baseY } Z`;
	const reversedUncached = smoothPath( [ ...uncachedPts ].reverse() );
	const cachedArea = `${ totalLine } L${ reversedUncached.slice( 1 ) } Z`;

	const dayLabels = days.map( ( d, i ) => ( {
		x: i === 0 ? AXIS_PAD : i === n - 1 ? W - AXIS_PAD : xAt( i ),
		label: `${ d.date.slice( 6, 8 ) }.${ d.date.slice( 4, 6 ) }`,
		anchor: i === 0 ? 'start' : i === n - 1 ? 'end' : 'middle',
	} ) );

	const ticks = [ 0, top / 2, top ];

	const onMove = ( event ) => {
		const svg = svgRef.current;
		if ( ! svg ) {
			return;
		}
		const rect = svg.getBoundingClientRect();
		if ( ! rect.width ) {
			return;
		}
		const dataX = ( ( event.clientX - rect.left ) / rect.width ) * W;
		const raw = Math.round( ( ( dataX - pad.l ) / iw ) * ( n - 1 ) );
		const idx = Math.max( 0, Math.min( n - 1, raw ) );
		setActiveDay( ( prev ) => ( prev === idx ? prev : idx ) );
	};

	const active =
		activeDay !== null && activeDay >= 0 && activeDay < n
			? days[ activeDay ]
			: null;
	let tip = null;
	if ( active ) {
		tip = {
			x: totalPts[ activeDay ][ 0 ],
			y: totalPts[ activeDay ][ 1 ],
			total: active.hits + active.misses,
			uncached: active.misses,
			date: `${ active.date.slice( 6, 8 ) }.${ active.date.slice(
				4,
				6
			) }.${ active.date.slice( 0, 4 ) }`,
			left: Math.min(
				92,
				Math.max( 8, ( totalPts[ activeDay ][ 0 ] / W ) * 100 )
			),
		};
	}

	return (
		<div className="millicache-status-chart__plot">
			<svg
				ref={ svgRef }
				className="millicache-status-chart__svg"
				viewBox={ `0 0 ${ W } ${ H }` }
				role="img"
				aria-label={ __(
					'Total and uncached requests over the last 7 days',
					'millicache'
				) }
				onMouseMove={ onMove }
				onMouseLeave={ () => setActiveDay( null ) }
			>
				<defs>
					<linearGradient id="mc-area-hits" x1="0" x2="0" y1="0" y2="1">
						<stop offset="0" className="millicache-status-chart__grad-hits-top" />
						<stop offset="1" className="millicache-status-chart__grad-fade" />
					</linearGradient>
					<linearGradient id="mc-area-misses" x1="0" x2="0" y1="0" y2="1">
						<stop offset="0" className="millicache-status-chart__grad-misses-top" />
						<stop offset="1" className="millicache-status-chart__grad-fade" />
					</linearGradient>
				</defs>
				{ ticks.map( ( t ) => (
					<line
						key={ `grid-${ t }` }
						className="millicache-status-chart__grid"
						x1={ pad.l }
						x2={ W - pad.r }
						y1={ yAt( t ) }
						y2={ yAt( t ) }
					/>
				) ) }
				<path
					className="millicache-status-chart__area-hits"
					d={ cachedArea }
					fill="url(#mc-area-hits)"
				/>
				<path
					className="millicache-status-chart__area-misses"
					d={ uncachedArea }
					fill="url(#mc-area-misses)"
				/>
				<path
					className="millicache-status-chart__line-total"
					d={ totalLine }
				/>
				<path
					className="millicache-status-chart__line-misses"
					d={ uncachedLine }
				/>
				{ ticks
					.filter( ( t ) => t > 0 )
					.map( ( t ) => (
						<text
							key={ `ylabel-${ t }` }
							className="millicache-status-chart__axis"
							x={ AXIS_PAD }
							y={ yAt( t ) - 3 }
							textAnchor="start"
						>
							{ compact( Math.round( t ) ) }
						</text>
					) ) }
				{ dayLabels.map( ( d ) => (
					<text
						key={ d.x }
						className="millicache-status-chart__axis"
						x={ d.x }
						y={ H - 5 }
						textAnchor={ d.anchor }
					>
						{ d.label }
					</text>
				) ) }
				{ tip && (
					<g>
						<line
							className="millicache-status-chart__crosshair"
							x1={ tip.x }
							x2={ tip.x }
							y1={ pad.t }
							y2={ pad.t + ih }
						/>
						<circle
							className="millicache-status-chart__marker"
							cx={ tip.x }
							cy={ tip.y }
							r="3.5"
						/>
					</g>
				) }
			</svg>
			{ tip && (
				<div
					className="millicache-status-chart__tooltip"
					style={ { left: `${ tip.left }%` } }
				>
					<div className="millicache-status-chart__tooltip-date">
						{ tip.date }
					</div>
					<div className="millicache-status-chart__tooltip-row is-total">
						<span className="millicache-status-chart__tooltip-label">
							{ __( 'Requests', 'millicache' ) }
						</span>
						<span className="millicache-status-chart__tooltip-value">
							{ tip.total.toLocaleString() }
						</span>
					</div>
					<div className="millicache-status-chart__tooltip-row is-uncached">
						<span className="millicache-status-chart__tooltip-label">
							{ __( 'Uncached', 'millicache' ) }
						</span>
						<span className="millicache-status-chart__tooltip-value">
							{ tip.uncached.toLocaleString() }
						</span>
					</div>
				</div>
			) }
		</div>
	);
};

// Renders the "Requests served" stat chips + chart from a time-series breakdown.
const BreakdownCard = ( { panel } ) => {
	if ( ! Array.isArray( panel.series ) || panel.series.length < 2 ) {
		return null;
	}

	const buckets = Array.isArray( panel.buckets ) ? panel.buckets : [];

	return (
		<div className="millicache-status-breakdown millicache-status-breakdown--chart">
			<div className="millicache-status-breakdown__header">
				<span className="millicache-status-breakdown__label">
					{ panel.label }
				</span>
			</div>
			<div className="millicache-status-chart__stats">
				{ buckets.map( ( bucket ) => (
					<span
						key={ bucket.label }
						className={ `millicache-status-chart__stat${
							bucket.tone ? ` is-${ bucket.tone }` : ''
						}` }
					>
						<span className="millicache-status-chart__stat-value">
							{ bucket.display ?? bucket.value }
						</span>
						<span className="millicache-status-chart__stat-label">
							{ bucket.label }
						</span>
					</span>
				) ) }
			</div>
			<TrendChart series={ panel.series } />
		</div>
	);
};

const PANEL_TYPES = {
	kpi: KpiTile,
	breakdown: BreakdownCard,
};

const Panel = ( { panel } ) => {
	const Component = PANEL_TYPES[ panel.type ];
	return Component ? <Component panel={ panel } /> : null;
};

const StatusTab = ( { status } ) => {
	const { isLoadingSettings } = window.MilliBase.hooks.useSettings();

	// Initial load: the status payload hasn't resolved yet — show a centered
	// spinner (matching the Pro Entries/Rules tabs) instead of an empty
	// dashboard. Once status is present, a later refetch keeps the dashboard.
	if ( isLoadingSettings && ! status ) {
		return (
			<div className="millicache-loading">
				<Spinner />
			</div>
		);
	}

	const panels = Array.isArray( status?.panels )
		? sortByWeight( status.panels )
		: [];

	const groups = {
		kpi: panels.filter( ( p ) => p.type === 'kpi' ),
		breakdown: panels.filter( ( p ) => p.type === 'breakdown' ),
		pro: panels.filter( ( p ) => p.type === 'pro' ),
	};

	const health = status?.debug?.health ?? 'ok';
	const healthLabel = {
		ok: __( 'Healthy', 'millicache' ),
		warning: __( 'Needs attention', 'millicache' ),
		error: __( 'Issues detected', 'millicache' ),
	}[ health ];

	return (
		<div className="millicache-status-dashboard">
			{ status && (
				<>
					{ /* Hidden when healthy; the footer Status pill still opens the modal. */ }
					{ health !== 'ok' && (
						<button
							type="button"
							className={ `millicache-status-banner millicache-status-banner--${ health }` }
							onClick={ dispatchOpenModal }
							aria-label={ __(
								'Open the full Status checks and debug info',
								'millicache'
							) }
						>
							<span
								className="millicache-status-banner__dot"
								aria-hidden="true"
							/>
							<span className="millicache-status-banner__label">
								{ healthLabel }
							</span>
							<span className="millicache-status-banner__hint">
								{ __(
									'Open the full check list and debug snapshot',
									'millicache'
								) }
							</span>
						</button>
					) }

					{ groups.kpi.length > 0 && (
						<div
							className="millicache-status-grid millicache-status-grid--kpis"
							role="list"
						>
							{ groups.kpi.map( ( panel ) => (
								<div role="listitem" key={ panel.id }>
									<Panel panel={ panel } />
								</div>
							) ) }
						</div>
					) }

					{ groups.breakdown.length > 0 && (
						<div
							className="millicache-status-grid millicache-status-grid--breakdowns"
							role="list"
						>
							{ groups.breakdown.map( ( panel ) => {
								// The series chart spans both columns (full-width hero).
								const isWide =
									Array.isArray( panel.series ) &&
									panel.series.length > 1;
								return (
									<div
										role="listitem"
										key={ panel.id }
										className={
											isWide
												? 'millicache-status-grid__item--wide'
												: undefined
										}
									>
										<Panel panel={ panel } />
									</div>
								);
							} ) }
						</div>
					) }

					{ groups.pro.map( ( panel ) => (
							<ProTeaser key={ panel.id } panel={ panel } />
						) ) }
					</>

			) }
		</div>
	);
};

export default StatusTab;
