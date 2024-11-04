import {
	createContext,
	useContext,
	useState,
	useEffect,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useSnackbar } from './Snackbar.jsx';

const SettingsContext = createContext();

export const SettingsProvider = ( { children } ) => {
	const [ status, setStatus ] = useState( {} );
	const [ settings, setSettings ] = useState( {} );
	const [ initialSettings, setInitialSettings ] = useState( {} );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ error, setError ] = useState( null );
	const [ hasChanges, setHasChanges ] = useState( false );
	const [ hasRedisChanges, setHasRedisChanges ] = useState( false );
	const { showSnackbar } = useSnackbar();

	const delay = ( ms ) =>
		new Promise( ( resolve ) => setTimeout( resolve, ms ) );

	const fetchStatus = async () => {
		try {
			const response = await apiFetch( {
				path: '/millicache/v1/status',
				method: 'GET',
			} );

			setStatus( response );
			return response;
		} catch ( fetchError ) {
			const errorMessage =
				fetchError?.message ??
				__( 'Failed to load status.', 'millicache' );

			setStatus( {
				connected: false,
				error: errorMessage,
			} );

			showSnackbar( errorMessage, [], 6000, true );
			return errorMessage;
		}
	};

	const fetchSettings = async () => {
		try {
			setIsLoading( true );
			const response = await apiFetch( { path: '/wp/v2/settings' } );
			setSettings( response?.millicache );
			setInitialSettings( response?.millicache );
		} catch ( fetchError ) {
			const errorMessage =
				fetchError?.message ??
				__( 'Failed to load settings.', 'millicache' );

			setError( errorMessage );
			showSnackbar( errorMessage );
		} finally {
			setIsLoading( false );
		}
	};

	// Load the settings and status when the component mounts
	useEffect( () => {
		fetchSettings();
		fetchStatus();
	}, [] );

	// Update a setting in the context
	const updateSetting = ( module, key, value ) => {
		setSettings( ( prevSettings ) => {
			const updatedSettings = {
				...prevSettings,
				[ module ]: {
					...prevSettings[ module ],
					[ key ]: value,
				},
			};

			setHasChanges(
				JSON.stringify( updatedSettings ) !==
					JSON.stringify( initialSettings )
			);

			if ( module === 'redis' ) {
				setHasRedisChanges( true );
			}

			return updatedSettings;
		} );
	};

	// Save the settings to the server
	const saveSettings = async () => {
		if ( ! hasChanges ) {
			return;
		}

		try {
			setIsSaving( true );

			await apiFetch( {
				path: '/wp/v2/settings',
				method: 'POST',
				data: {
					millicache: settings,
				},
			} );

			setInitialSettings( settings );
			showSnackbar( __( 'Settings saved successfully.', 'millicache' ) );
			setHasChanges( false );

			if ( hasRedisChanges ) {
				const previousStatus = status;

				await delay( 500 );
				showSnackbar(
					__(
						'Redis settings updated. Testing connection…',
						'millicache'
					)
				);

				await delay( 3000 );
				const newStatus = await fetchStatus();

				if (
					previousStatus.redis?.connected &&
					! newStatus.redis?.connected
				) {
					await delay( 50 );
					showSnackbar(
						__(
							'Redis connection lost. Please check your settings.',
							'millicache'
						)
					);
				} else if (
					! previousStatus.redis?.connected &&
					newStatus.redis?.connected
				) {
					showSnackbar(
						__(
							'Redis connection established successfully.',
							'millicache'
						)
					);
				}

				if ( newStatus.redis?.error ) {
					showSnackbar( newStatus.redis?.error, [], 6000, true );
				}

				setHasRedisChanges( false );
			}
		} catch ( fetchError ) {
			showSnackbar( __( 'Failed to save settings.', 'millicache' ) );
		} finally {
			setTimeout( () => setIsSaving( false ), 1200 );
		}
	};

	return (
		<SettingsContext.Provider
			value={ {
				status,
				settings,
				error,
				isLoading,
				isSaving,
				hasChanges,
				updateSetting,
				saveSettings,
			} }
		>
			{ children }
		</SettingsContext.Provider>
	);
};

// Custom hook to use the SettingsContext
export const useSettings = () => {
	return useContext( SettingsContext );
};
