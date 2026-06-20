import { useState, useEffect, createInterpolateElement } from '@wordpress/element';
import { Button, Modal, TabPanel, Icon } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { check, caution, error } from '@wordpress/icons';

const ISSUE_URL = 'https://github.com/MilliPress/MilliCache/issues/new';
const ISSUE_TEMPLATE = 'bug_report.yml';

// Other surfaces dispatch this on `window` to open the modal.
const OPEN_MODAL_EVENT = 'millicache:open-status-modal';

// Conservative ceiling for the final new-issue URL.
const MAX_ISSUE_URL_LENGTH = 8000;

const KNOWN_BACKENDS = [ 'Redis', 'KeyDB', 'Dragonfly', 'Valkey' ];

const CHECK_ICONS = {
	good: { icon: check, className: 'millicache-footer-status__check-icon--good' },
	recommended: {
		icon: caution,
		className: 'millicache-footer-status__check-icon--warning',
	},
	critical: {
		icon: error,
		className: 'millicache-footer-status__check-icon--critical',
	},
};

const resolveBackend = ( serverVersion ) => {
	if ( typeof serverVersion !== 'string' || ! serverVersion ) {
		return '';
	}
	const head = serverVersion.split( /\s+/, 1 )[ 0 ];
	return KNOWN_BACKENDS.includes( head ) ? head : 'Other';
};

const buildIssueUrl = ( status ) => {
	const params = new URLSearchParams( { template: ISSUE_TEMPLATE } );
	const debug = status?.[ 'debug' ];
	const pluginVersion = debug?.plugin?.version;
	const wpVersion = debug?.versions?.wp;
	const phpVersion = debug?.versions?.php;
	const backend = resolveBackend( status?.storage?.info?.[ 'Server' ]?.version );
	const markdown = debug?.markdown;

	if ( pluginVersion ) {
		params.set( 'plugin-version', pluginVersion );
	}
	if ( wpVersion ) {
		params.set( 'wp-version', wpVersion );
	}
	if ( phpVersion ) {
		params.set( 'php-version', phpVersion );
	}
	if ( backend ) {
		params.set( 'cache-backend', backend );
	}

	if ( typeof markdown === 'string' && markdown ) {
		params.set( 'debug-info', markdown );
		const candidate = `${ ISSUE_URL }?${ params.toString() }`;
		if ( candidate.length <= MAX_ISSUE_URL_LENGTH ) {
			return candidate;
		}
		params.delete( 'debug-info' );
	}

	return `${ ISSUE_URL }?${ params.toString() }`;
};

/**
 * Pick a short status-aware label that summarizes the failing checks count.
 */
const summarize = ( health, checks ) => {
	if ( health === 'loading' ) {
		return __( 'Checking…', 'millicache' );
	}

	const critical = checks.filter( ( c ) => c.status === 'critical' ).length;
	const recommended = checks.filter( ( c ) => c.status === 'recommended' ).length;

	if ( critical > 0 ) {
		return sprintf(
			/* translators: %d: number of critical issues */
			_n( '%d Issue', '%d Issues', critical, 'millicache' ),
			critical
		);
	}

	if ( recommended > 0 ) {
		return sprintf(
			/* translators: %d: number of recommendations (warnings) */
			_n( '%d Warning', '%d Warnings', recommended, 'millicache' ),
			recommended
		);
	}

	return __( 'Healthy', 'millicache' );
};

// Render a check description, turning any <code> markup into a real element.
const withCode = ( text ) =>
	text.includes( '<code' )
		? createInterpolateElement( text, {
				code: (
					<code className="millicache-footer-status__check-code" />
				),
		  } )
		: text;

const ChecksList = ( { checks } ) => {
	if ( ! checks || checks.length === 0 ) {
		return (
			<p className="millicache-footer-status__empty">
				{ __(
					'No status checks are available yet. Try refreshing this page.',
					'millicache'
				) }
			</p>
		);
	}

	return (
		<ul className="millicache-footer-status__checks">
			{ checks.map( ( c ) => {
				const cfg = CHECK_ICONS[ c.status ] ?? CHECK_ICONS.recommended;
				return (
					<li
						key={ c.id }
						className={ `millicache-footer-status__check millicache-footer-status__check--${ c.status }` }
					>
						<span
							className={ `millicache-footer-status__check-icon ${ cfg.className }` }
							aria-hidden="true"
						>
							<Icon icon={ cfg.icon } size={ 20 } />
						</span>
						<div className="millicache-footer-status__check-body">
							<div className="millicache-footer-status__check-head">
								<strong>{ c.label }</strong>
								{ c.value && (
									<span className="millicache-footer-status__check-value">
										{ c.value }
									</span>
								) }
							</div>
							<p className="millicache-footer-status__check-desc">
								{ withCode( c.description ) }
								{ c.status !== 'good' && c.url && (
									<>
										{ ' ' }
										<a
											href={ c.url }
											target="_blank"
											rel="noopener noreferrer"
											className="millicache-footer-status__check-link"
										>
											{ __( 'Learn more', 'millicache' ) }
										</a>
									</>
								) }
							</p>
						</div>
					</li>
				);
			} ) }
		</ul>
	);
};

const DebugTab = ( { markdown, copyState, onCopy, onOpenIssue } ) => (
	<>
		<p>
			{ __(
				'A sanitized debug snapshot. No hosts, credentials, or customer paths are included. “Open GitHub Issue” starts a bug report with the snapshot and your environment versions pre-filled.',
				'millicache'
			) }
		</p>

		<textarea
			className="millicache-footer-status__payload"
			value={ markdown }
			readOnly
			rows={ 18 }
			onFocus={ ( event ) => event.target.select() }
		/>

		<div className="millicache-footer-status__actions">
			<Button variant="primary" onClick={ onCopy } disabled={ ! markdown }>
				{ copyState === 'copied'
					? __( 'Copied!', 'millicache' )
					: copyState === 'manual'
					? __( 'Select & copy manually', 'millicache' )
					: __( 'Copy to clipboard', 'millicache' ) }
			</Button>

			<Button variant="secondary" onClick={ onOpenIssue }>
				{ __( 'Open GitHub Issue', 'millicache' ) }
			</Button>
		</div>
	</>
);

const FooterStatus = ( { status } ) => {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ copyState, setCopyState ] = useState( 'idle' );

	useEffect( () => {
		const handler = () => setIsOpen( true );
		window.addEventListener( OPEN_MODAL_EVENT, handler );
		return () => window.removeEventListener( OPEN_MODAL_EVENT, handler );
	}, [] );

	const debug = status?.[ 'debug' ];
	const health = debug?.health ?? 'loading';
	const markdown = debug?.markdown ?? '';
	const checks = Array.isArray( debug?.checks ) ? debug.checks : [];

	const handleCopy = async () => {
		if ( ! markdown ) {
			return;
		}

		if ( navigator.clipboard?.writeText ) {
			try {
				await navigator.clipboard.writeText( markdown );
				setCopyState( 'copied' );
				window.setTimeout( () => setCopyState( 'idle' ), 2000 );
				return;
			} catch {
				// Fall through to manual mode when the Clipboard API
				// rejects (e.g. insecure context, permission denied).
			}
		}

		setCopyState( 'manual' );
	};

	const handleOpenIssue = () => {
		window.open( buildIssueUrl( status ), '_blank', 'noopener,noreferrer' );
	};

	const label = summarize( health, checks );

	return (
		<>
			<button
				type="button"
				className={ `millicache-footer-status__trigger millicache-footer-status__trigger--${ health }` }
				onClick={ () => setIsOpen( true ) }
				aria-label={ sprintf(
					/* translators: %s: status summary */
					__( 'MilliCache status: %s. Open details.', 'millicache' ),
					label
				) }
				title={ label }
			>
				<span
					className="millicache-footer-status__dot"
					aria-hidden="true"
				/>
				<span className="millicache-footer-status__label">{ label }</span>
			</button>

			{ isOpen && (
				<Modal
					title={ __( 'MilliCache Status', 'millicache' ) }
					onRequestClose={ () => setIsOpen( false ) }
					size="medium"
					className="millicache-footer-status__modal"
				>
					<TabPanel
						className="millicache-footer-status__tabs"
						activeClass="is-active"
						tabs={ [
							{
								name: 'checks',
								title: __( 'Status Checks', 'millicache' ),
							},
							{
								name: 'debug',
								title: __( 'Debug Info', 'millicache' ),
							},
						] }
					>
						{ ( tab ) =>
							tab.name === 'checks' ? (
								<ChecksList checks={ checks } />
							) : (
								<DebugTab
									markdown={ markdown }
									copyState={ copyState }
									onCopy={ handleCopy }
									onOpenIssue={ handleOpenIssue }
								/>
							)
						}
					</TabPanel>
				</Modal>
			) }
		</>
	);
};

export default FooterStatus;
