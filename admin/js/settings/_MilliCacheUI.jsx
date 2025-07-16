import { __ } from '@wordpress/i18n';
import { Animate, ProgressBar, TabPanel, Panel } from '@wordpress/components';
import { useSettings } from './context/Settings.jsx';
import Header from './partials/Header.jsx';
import StatusTab from './Status.jsx';
import GeneralSettings from './General.jsx';

const MilliCacheUI = () => {
	const { error, isLoading, activeTab, setActiveTab } = useSettings();

	return (
		<div style={ { maxWidth: '900px' } }>
			<Panel>
				<Header />

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
									style={{
										border: '1px solid #ddd',
										marginLeft: '-1px',
										marginRight: '-1px',
									}}
									initialTabName={activeTab}
									onSelect={(tabName) => {
										setActiveTab(tabName);
									}}
									tabs={ [
										{
											name: 'status',
											title: __( 'Status', 'millicache' ),
										},
										{
											name: 'settings',
											title: __(
												'Settings',
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
												{ tab.name === 'status' && (
													<StatusTab />
												) }
												{ tab.name === 'settings' && (
													<GeneralSettings />
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
