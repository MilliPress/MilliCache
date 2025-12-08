import {
	createContext,
	useContext,
	useState,
	useEffect,
	useCallback,
	useRef,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { stripTags } from '@wordpress/sanitize';
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
	const [ hasStorageChanges, setHasStorageChanges ] = useState( false );
	const [ activeTab, setActiveTab ] = useState('status');
	const [ isRetrying, setIsRetrying ] = useState( false );
	const statusIntervalRef = useRef( null );
	const { showSnackbar } = useSnackbar();

	const delay = ( ms ) =>
		new Promise( ( resolve ) => setTimeout( resolve, ms ) );

	// Use WordPress's built-in error handling
	const handleApiError = useCallback( (error) => {
		let message = __( 'An unexpected error occurred.', 'millicache' );

		// Handle different error types
		if ( error?.message ) {
			message = error.message;
		} else if ( error?.code ) {
			switch ( error.code ) {
				case 'rest_no_route':
					message = __( 'API endpoint not found. Please check your WordPress installation.', 'millicache' );
					break;
				case 'rest_forbidden':
					message = __( 'Access denied. Please check your permissions.', 'millicache' );
					break;
				case 'rest_cookie_invalid_nonce':
					message = __( 'Security check failed. Please refresh the page.', 'millicache' );
					break;
				default:
					message = error.message || __( 'API request failed.', 'millicache' );
			}
		}

		return  typeof message === 'string' ? stripTags(message) : message;
	}, []);

	// Basic API wrapper with WordPress error handling
	const apiRequest = useCallback( async ( options ) => {
		try {
			await delay(300);
			return await apiFetch( options );
		} catch ( error ) {
			const errorMessage = handleApiError( error );
			throw new Error( errorMessage );
		}
	}, [ handleApiError ] );

	const triggerAction = async ( action, data = {} ) => {
		setIsLoading( true );
		try {
            const endpoint = action.startsWith('clear') ? 'cache' : 'settings';
            const response = await apiRequest( {
				path: `/millicache/v1/${endpoint}`,
				method: 'POST',
				data: { action, ...data },
			} );

			await delay( 800 );

			if ( response.success ) {
				showSnackbar( response.message );
				fetchSettings();
				fetchStatus();
			} else {
				throw new Error( response.message || __( 'Action failed', 'millicache' ) );
			}
		} catch ( error ) {
			const errorText = error.message || __( 'Action failed', 'millicache' );
			showSnackbar( errorText, [], 6000, true );
			throw error;
		} finally {
			setIsLoading( false );
		}
	};

	const fetchStatus = useCallback( async () => {
		try {
			const response = await apiRequest( {
				path: '/millicache/v1/status',
				method: 'GET',
			} );

			setStatus( response );
			setError( null ); // Clear any previous errors
			return response;
		} catch ( error ) {
			const errorMessage = error.message;
			setStatus( {
				connected: false,
				error: errorMessage,
			} );
			setError( errorMessage );
			return errorMessage;
		}
	}, [ apiRequest ] );

	const fetchSettings = useCallback( async () => {
		try {
			setIsLoading( true );
			const response = await apiRequest( { path: '/wp/v2/settings' } );
			setSettings( response?.millicache );
			setInitialSettings( response?.millicache );
			setError( null ); // Clear any previous errors
		} catch ( error ) {
			const errorMessage = error.message;
			setError( errorMessage );
		} finally {
			setIsLoading( false );
		}
	}, [ apiRequest ] );

	const retryConnection = useCallback( async () => {
		setIsRetrying( true );
		setError( null );

		try {
			await Promise.all([
				fetchSettings(),
				fetchStatus()
			]);
		} catch ( error ) {
			// Errors are already handled in the individual functions
		} finally {
			setIsRetrying( false );
		}
	}, [ fetchSettings, fetchStatus ] );

	// Basic periodic status check
	useEffect( () => {
		fetchSettings();
		fetchStatus();

		if ( statusIntervalRef.current ) {
			clearInterval( statusIntervalRef.current );
		}

		// Only poll if there's no error
		statusIntervalRef.current = setInterval( () => {
			if ( !error ) {
				fetchStatus();
			}
		}, 15000 );

		return () => {
			if ( statusIntervalRef.current ) {
				clearInterval( statusIntervalRef.current );
			}
		};
	}, [ fetchSettings, fetchStatus, error ] );

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

			if ( module === 'storage' ) {
				setHasStorageChanges( true );
			}

			return updatedSettings;
		} );
	};

	// Save settings with WordPress error handling
	const saveSettings = async () => {
		if (!hasChanges) {
			return;
		}

		try {
			setIsSaving(true);

			await apiRequest({
				path: '/wp/v2/settings',
				method: 'POST',
				data: {
					millicache: settings,
				},
			});

			setInitialSettings(settings);
			showSnackbar(__('Settings saved successfully.', 'millicache'));
			setHasChanges(false);

			if (hasStorageChanges) {
				const previousStatus = { ...status }; // Make a copy to ensure it's stable

				await delay(500);
				showSnackbar(
					__(
						'Storage settings updated. Testing connection…',
						'millicache'
					)
				);

				await delay(3000);
				const newStatus = await fetchStatus();

				// Add additional null/undefined checks here
				if (newStatus && previousStatus) {
					if (
						previousStatus.storage?.connected &&
						!newStatus.storage?.connected
					) {
						await delay(50);
						showSnackbar(
							__(
								'Storage connection lost. Please check your settings.',
								'millicache'
							)
						);
					} else if (
						!previousStatus.storage?.connected &&
						newStatus.storage?.connected
					) {
						showSnackbar(
							__(
								'Storage connection established successfully.',
								'millicache'
							)
						);
					}

					if (newStatus.storage?.error) {
						showSnackbar(newStatus.storage.error, [], 6000, true);
					}
				}

				setHasStorageChanges(false);
			}
		} catch (error) {
			const errorMessage = error.message || __('Failed to save settings.', 'millicache');
			showSnackbar(errorMessage, [], 6000, true);
		} finally {
			setTimeout(() => setIsSaving(false), 1200);
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
				triggerAction,
				activeTab,
				setActiveTab,
				retryConnection,
				isRetrying,
			} }
		>
			{ children }
		</SettingsContext.Provider>
	);
};

export const useSettings = () => {
	return useContext( SettingsContext );
};