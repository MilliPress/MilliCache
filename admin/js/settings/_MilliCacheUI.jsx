import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';
import {
	Animate,
	Button,
	Flex,
	FlexItem,
	Modal,
	ProgressBar,
	TabPanel,
	Panel,
	PanelBody,
	ExternalLink,
} from '@wordpress/components';
import { useSettings } from './context/Settings.jsx';
import GeneralSettings from './General.jsx';
import RulesSettings from './Rules.jsx';

const MilliCacheUI = () => {
	const [ isOpen, setOpen ] = useState( false );
	const [ status, setStatus ] = useState( {} );
	const { error, saveSettings, isSaving, isLoading, hasChanges } =
		useSettings();

	const openModal = () => setOpen( true );
	const closeModal = () => setOpen( false );

	useEffect( () => {
		const loadStatus = async () => {
			try {
				const response = await apiFetch( {
					path: '/millicache/v1/status',
					method: 'GET',
				} );

				setStatus( response );
			} catch ( fetchError ) {
				setStatus( {
					connected: false,
					error: __( 'Failed to load status', 'millicache' ),
				} );
			}
		};

		loadStatus();
	}, [] );

	return (
		<div
			style={ { maxWidth: '1200px', minHeight: '100%', margin: 'auto' } }
		>
			<Panel>
				<PanelBody>
					<Flex align="center">
						<FlexItem>
							<h1 style={ { padding: '0' } }>
								{ __( 'MilliCache Settings', 'millicache' ) }
							</h1>

							<Flex expanded="false" justify="start">
								<FlexItem>
									<ExternalLink
										className="external-link"
										href="https://millipress.com/docs"
									>
										{ __( 'Documentation', 'millicache' ) }
									</ExternalLink>
								</FlexItem>
								<FlexItem>
									<ExternalLink
										className="external-link"
										href="https://millipress.com/support"
									>
										{ __( 'Support', 'millicache' ) }
									</ExternalLink>
								</FlexItem>
							</Flex>
						</FlexItem>
						<FlexItem>
							<Button
								style={ { marginRight: '10px' } }
								className={ `editor-post-publish-button ${
									isSaving ? 'is-busy' : ''
								}` }
								isPrimary
								onClick={ saveSettings }
								disabled={ ! hasChanges || isSaving }
							>
								{ isSaving
									? __( 'Saving…', 'millicache' )
									: __( 'Save Settings', 'millicache' ) }
							</Button>
							<Button variant="secondary" onClick={ openModal }>
								{ __( 'Clear Cache', 'millicache' ) }
							</Button>
							{ isOpen && (
								<Modal
									title={ __( 'Clear Cache', 'millicache' ) }
									onRequestClose={ closeModal }
								>
									<Button
										variant="secondary"
										onClick={ closeModal }
									>
										{ __( 'Flush & Close', 'millicache' ) }
									</Button>
								</Modal>
							) }
						</FlexItem>
					</Flex>
				</PanelBody>

				{ isLoading ||
					( isSaving && (
						<Animate
							type="slide-in"
							options={ { origin: 'top center' } }
						>
							{ ( { className } ) => (
								<ProgressBar
									className={ `millicache-settings-progress ${ className }` }
								/>
							) }
						</Animate>
					) ) }

				{ ( () => {
					if ( isLoading ) {
						return (
							<Animate
								type="slide-in"
								options={ { origin: 'top center' } }
							>
								{ ( { className } ) => (
									<ProgressBar
										className={ `millicache-settings-progress ${ className }` }
									/>
								) }
							</Animate>
						);
					} else if ( error ) {
						return <div className="error">{ error }</div>;
					}
					return (
						<Animate type="slide-in" options={ { origin: 'top' } }>
							{ ( { className } ) => (
								<TabPanel
									className={ `millicache-settings-tabs ${ className }` }
									style={ {
										border: '1px solid #ddd',
										marginLeft: '-1px',
										marginRight: '-1px',
									} }
									tabs={ [
										{
											name: 'general',
											title: __(
												'General Settings',
												'millicache'
											),
										},
										{
											name: 'rules',
											title: __(
												'Cache Rules',
												'millicache'
											),
										},
									] }
								>
									{ ( tab ) => (
										<div
											className="millicache-settings-tab-content"
											style={ { margin: '-1px' } }
										>
											<div>
												{ tab.name === 'general' && (
													<GeneralSettings
														status={ status }
													/>
												) }
												{ tab.name === 'rules' && (
													<RulesSettings />
												) }
											</div>
										</div>
									) }
								</TabPanel>
							) }
						</Animate>
					);
				} )() }
			</Panel>
		</div>
	);
};

export default MilliCacheUI;
