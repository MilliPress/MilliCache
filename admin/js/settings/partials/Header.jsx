import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	Animate,
	Button,
	Dropdown,
	Flex,
	FlexItem,
	MenuGroup,
	MenuItem,
	PanelBody,
	ExternalLink,
	ProgressBar,
} from '@wordpress/components';
import { flipVertical, lifesaver, moreVertical } from '@wordpress/icons';
import { useSettings } from '../context/Settings.jsx';
import ClearCacheModal from './ClearCacheModal.jsx';

const Header = () => {
	const {
		status,
		saveSettings,
		isSaving,
		isLoading,
		hasChanges,
		triggerAction,
	} = useSettings();

	const [ isActionModalOpen, setIsActionModalOpen ] = useState( false );

	const openActionModal = () => {
		setIsActionModalOpen( true );
	};

	const closeActionModal = () => {
		setIsActionModalOpen( false );
	};

	return (
		<>
			<PanelBody>
				<Flex align="center">
					<FlexItem>
						<h1 style={ { padding: '0' } }>
							{ __( 'MilliCache', 'millicache' ) }
						</h1>

						<Flex expanded="false" justify="start">
							<FlexItem>
								<ExternalLink
									className="external-link"
									href="https://github.com/MilliPress/MilliCache/blob/main/README.md"
								>
									{ __( 'Documentation', 'millicache' ) }
								</ExternalLink>
							</FlexItem>
							<FlexItem>
								<ExternalLink
									className="external-link"
									href="https://github.com/MilliPress/MilliCache/issues"
								>
									{ __( 'Support', 'millicache' ) }
								</ExternalLink>
							</FlexItem>
						</Flex>
					</FlexItem>
					<FlexItem align="end">
						<Button
							__next40pxDefaultSize
							style={ { marginRight: '10px' } }
							isBusy={ isSaving }
							isPrimary
							onClick={ saveSettings }
							disabled={ ! hasChanges || isSaving }
						>
							{ isSaving
								? __( 'Saving…', 'millicache' )
								: __( 'Save Settings', 'millicache' ) }
						</Button>

						<Button
							__next40pxDefaultSize
							label={ __( 'Clear Cache', 'millicache' ) }
							showTooltip
							variant="secondary"
							onClick={ () =>
								openActionModal( 'clear_site', false )
							}
							disabled={
								isSaving ||
								isLoading ||
								! status.redis?.connected ||
								status.cache?.index < 1
							}
						>
							{ __( 'Clear Cache', 'millicache' ) }
						</Button>

						<Dropdown
							className="millicache-actions-dropdown"
							contentClassName="millicache-actions-dropdown-content"
							popoverProps={ { placement: 'bottom-end' } }
							renderToggle={ ( { isOpen, onToggle } ) => {
								return (
									<Button
										__next40pxDefaultSize
										icon={ moreVertical }
										label={ __(
											'Cache Actions',
											'millicache'
										) }
										disabled={ isSaving || isLoading }
										onClick={ onToggle }
										aria-expanded={ isOpen }
									></Button>
								);
							} }
							renderContent={ () => {
								return (
									<>
										<MenuGroup
											label={ __(
												'More Actions',
												'millicache'
											) }
										>
											<MenuItem
												__next40pxDefaultSize
												icon={ lifesaver }
												iconPosition="left"
												onClick={ () =>
													window.open(
														'https://github.com/MilliPress/MilliCache/issues',
														'_blank'
													)
												}
											>
												{ __(
													'Get Help',
													'millicache'
												) }
											</MenuItem>
											<MenuItem
												__next40pxDefaultSize
												icon={ flipVertical }
												iconPosition="left"
												onClick={ () =>
													triggerAction(
														'reset_settings'
													)
												}
											>
												{ __(
													'Reset all Settings',
													'millicache'
												) }
											</MenuItem>
										</MenuGroup>
									</>
								);
							} }
						/>

						<ClearCacheModal
							isModalOpen={ isActionModalOpen }
							onRequestClose={ closeActionModal }
						/>
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
		</>
	);
};

export default Header;
