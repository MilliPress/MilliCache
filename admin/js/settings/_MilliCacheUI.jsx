import { __ } from '@wordpress/i18n';
import { Animate, ProgressBar, TabPanel, Panel, Button } from '@wordpress/components';
import { warning } from '@wordpress/icons';
import { Icon } from '@wordpress/components';
import { useSettings } from './context/Settings.jsx';
import Header from './partials/Header.jsx';
import StatusTab from './Status.jsx';
import GeneralSettings from './General.jsx';

const MilliCacheUI = () => {
	const {
		error,
		isLoading,
		activeTab,
		setActiveTab,
		retryConnection,
		isRetrying
	} = useSettings();

	// Error Notice Component.
	const ErrorDisplay = ({ error, onRetry, isRetrying }) => (
		<div className="millicache-error-container" style={{
			padding: '60px 20px',
			textAlign: 'center',
			maxWidth: '600px',
			margin: '0 auto'
		}}>
			{/* Error Icon */}
			<div style={{ marginBottom: '24px' }}>
				<Icon
					icon={warning}
					size={48}
					style={{
						color: '#dc3232',
						opacity: 0.8
					}}
				/>
			</div>

			{/* Error Title */}
			<h2 style={{
				margin: '0 0 16px 0',
				fontSize: '24px',
				fontWeight: '600',
				color: '#1e1e1e'
			}}>
				{__('Connection Error', 'millicache')}
			</h2>

			{/* Error Message */}
			<p style={{
				fontSize: '16px',
				lineHeight: '1.5',
				color: '#646970',
				maxWidth: '500px',
				margin: '0 auto 32px auto'
			}}>
				{error}
			</p>

			{/* Action Buttons */}
			<div style={{
				marginBottom: '32px',
				display: 'flex',
				justifyContent: 'center',
				gap: '12px',
				flexWrap: 'wrap'
			}}>
				<Button
					variant="primary"
					onClick={onRetry}
					isBusy={isRetrying}
					disabled={isRetrying}
				>
					{isRetrying
						? __('Retrying...', 'millicache')
						: __('Try Again', 'millicache')
					}
				</Button>
			</div>

			{/* Help Links */}
			<div style={{
				fontSize: '14px',
				color: '#646970',
				lineHeight: '1.6'
			}}>
				<p style={{ margin: '0' }}>
					{__('Need help fixing this issue?', 'millicache')}
				</p>

				<Button
					href="https://www.millipress.com/docs/millicache/troubleshooting"
					target="_blank"
					variant="tertiary"
					size="compact"
					style={{ margin: '0' }}
				>
					{ __('View Troubleshooting Guide', 'millicache') + ' →' }
				</Button>
			</div>
		</div>
	);

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
									<div className="millicache-loading-container">
										<ProgressBar
											className={ `millicache-settings-progress ${ className }` }
										/>
										<p style={{ textAlign: 'center', margin: '10px 0' }}>
											{__('Loading MilliCache settings...', 'millicache')}
										</p>
									</div>
								) }
							</Animate>
						);
					} else if ( error ) {
						return (
							<ErrorDisplay
								error={error}
								onRetry={retryConnection}
								isRetrying={isRetrying}
							/>
						);
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