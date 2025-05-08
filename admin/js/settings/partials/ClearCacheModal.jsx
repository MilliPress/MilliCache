import { useState } from '@wordpress/element';
import {
	Modal,
	Flex,
	FlexItem,
	FormTokenField,
	Button,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSnackbar } from '../context/Snackbar.jsx';
import { useSettings } from '../context/Settings.jsx';

const ClearCacheModal = ( { isModalOpen, onRequestClose } ) => {
	const { triggerAction } = useSettings();
	const { showSnackbar } = useSnackbar();

	const [ inputValue, setInputValue ] = useState( [] );

	const handleModalClose = () => {
		onRequestClose();
		setInputValue( [] );
	};

	const handleAction = async () => {
		try {
			await triggerAction( 'clear_cache_by_targets', {
				targets: inputValue,
			} );
			handleModalClose();
		} catch ( error ) {
			showSnackbar( __( 'Error clearing cache.', 'millicache' ) );
		}
	};

	return (
		isModalOpen && (
			<Modal
				title={ __( 'Clear Cache', 'millicache' ) }
				onRequestClose={ handleModalClose }
				focusOnMount={ 'firstContentElement' }
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
							__nextHasNoMarginBottom
							label={ __(
								'Targets to clear (Optional)',
								'millicache'
							) }
							value={ inputValue }
							onChange={ setInputValue }
							suggestions={ [] }
						/>
					</FlexItem>
					<FlexItem>
						<Button isPrimary onClick={ handleAction }>
							{ inputValue.length === 0
								? __( 'Clear Website Cache', 'millicache' )
								: __( 'Clear Targeted Cache', 'millicache' ) }
						</Button>
					</FlexItem>
				</Flex>
			</Modal>
		)
	);
};

export default ClearCacheModal;
