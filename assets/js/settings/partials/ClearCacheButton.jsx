import { useState } from '@wordpress/element';
import {
	Button,
	Modal,
	Flex,
	FlexItem,
	FormTokenField,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ClearCacheButton = ( { status, triggerAction, isSaving } ) => {
	const { isLoadingAction } = window.MilliBase.hooks.useSettings();
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ targets, setTargets ] = useState( [] );

	const handleClose = () => {
		setIsModalOpen( false );
		setTargets( [] );
	};

	const handleClear = async () => {
		try {
			await triggerAction( 'clear_targets', { targets } );
		} catch {
			// triggerAction handles error notifications.
		}
		handleClose();
	};

	return (
		<>
			<Button
				__next40pxDefaultSize
				label={ __( 'Clear Cache', 'millicache' ) }
				showTooltip
				variant="secondary"
				onClick={ () => setIsModalOpen( true ) }
				disabled={
					isSaving ||
					isLoadingAction ||
					! status.storage?.connected ||
					status.cache?.index < 1
				}
			>
				{ __( 'Clear Cache', 'millicache' ) }
			</Button>

			{ isModalOpen && (
				<Modal
					title={ __( 'Clear Cache', 'millicache' ) }
					onRequestClose={ handleClose }
					focusOnMount="firstContentElement"
				>
					<Flex direction="column">
						<FlexItem>
							<p>
								{ __(
									'Enter one or more Cache Flags, Post-IDs or URLs for which you want to clear the cache. You can use wildcards (*) to clear multiple related flags.',
									'millicache'
								) }
							</p>
						</FlexItem>
						<FlexItem>
							<FormTokenField
								__next40pxDefaultSize
								label={ __(
									'Targets to clear (Optional)',
									'millicache'
								) }
								value={ targets }
								onChange={ setTargets }
								suggestions={ [] }
							/>
						</FlexItem>
						<FlexItem>
							<Button isPrimary onClick={ handleClear }>
								{ targets.length === 0
									? __( 'Clear Website Cache', 'millicache' )
									: __(
											'Clear Cache of Targets',
											'millicache'
									  ) }
							</Button>
						</FlexItem>
					</Flex>
				</Modal>
			) }
		</>
	);
};

export default ClearCacheButton;
