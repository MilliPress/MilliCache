import { useState } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ISSUE_URL = 'https://github.com/MilliPress/MilliCache/issues/new';
const ISSUE_TEMPLATE = 'bug_report.yml';

// Conservative ceiling for the final new-issue URL.
const MAX_ISSUE_URL_LENGTH = 8000;

const KNOWN_BACKENDS = [ 'Redis', 'KeyDB', 'Dragonfly', 'Valkey' ];

const HEALTH_LABELS = {
	ok: __( 'MilliCache is healthy', 'millicache' ),
	warning: __( 'MilliCache has warnings', 'millicache' ),
	error: __( 'MilliCache has errors', 'millicache' ),
	loading: __( 'Loading MilliCache status…', 'millicache' ),
};

/**
 * Pluck the backend name (e.g. "Redis") from a server-version string
 * ("Redis 7.2.4"). Used to prefill the bug-report cache-backend dropdown.
 */
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

	// Prefill the debug-info textarea when the resulting URL stays within the safe length budget.
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

const FooterStatus = ( { status } ) => {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ copyState, setCopyState ] = useState( 'idle' );

	const debug = status?.[ 'debug' ];
	const health = debug?.health ?? 'loading';
	const markdown = debug?.markdown ?? '';

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
		window.open(
			buildIssueUrl( status ),
			'_blank',
			'noopener,noreferrer'
		);
	};

	const dotLabel = HEALTH_LABELS[ health ] ?? HEALTH_LABELS.loading;

	return (
		<>
			<button
				type="button"
				className={ `millicache-footer-status__trigger millicache-footer-status__trigger--${ health }` }
				onClick={ () => setIsOpen( true ) }
				aria-label={ dotLabel }
				title={ dotLabel }
			>
				<span
					className="millicache-footer-status__dot"
					aria-hidden="true"
				/>
				<span className="millicache-footer-status__label">
					{ __( 'Status', 'millicache' ) }
				</span>
			</button>

			{ isOpen && (
				<Modal
					title={ __( 'MilliCache Status', 'millicache' ) }
					onRequestClose={ () => setIsOpen( false ) }
					size="medium"
					className="millicache-footer-status__modal"
				>

					<textarea
						className="millicache-footer-status__payload"
						value={ markdown }
						readOnly
						rows={ 18 }
						onFocus={ ( event ) => event.target.select() }
					/>

					<div className="millicache-footer-status__actions">
						<Button
							variant="primary"
							onClick={ handleCopy }
							disabled={ ! markdown }
						>
							{ copyState === 'copied'
								? __( 'Copied!', 'millicache' )
								: copyState === 'manual'
								? __(
										'Select & copy manually',
										'millicache'
								  )
								: __( 'Copy to clipboard', 'millicache' ) }
						</Button>

						<Button
							variant="secondary"
							onClick={ handleOpenIssue }
						>
							{ __( 'Open GitHub Issue', 'millicache' ) }
						</Button>
					</div>
				</Modal>
			) }
		</>
	);
};

export default FooterStatus;
