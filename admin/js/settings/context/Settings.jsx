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
	const [ settings, setSettings ] = useState( {} );
	const [ initialSettings, setInitialSettings ] = useState( {} );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ error, setError ] = useState( null );
	const [ hasChanges, setHasChanges ] = useState( false );
	const { showSnackbar } = useSnackbar();

	// Load the settings when the component mounts
	useEffect( () => {
		const fetchSettings = async () => {
			try {
				setIsLoading( true );
				const response = await apiFetch( { path: '/wp/v2/settings' } );
				setSettings( response?.millicache );
				setInitialSettings( response?.millicache );
			} catch ( fetchError ) {
				setError( __( 'Failed to load settings', 'millicache' ) );
			} finally {
				setIsLoading( false );
			}
		};

		fetchSettings();
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
			return updatedSettings;
		} );
	};

	// Save the settings to the server
	const saveSettings = async () => {
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
			setHasChanges( false );
			showSnackbar( __( 'Settings saved successfully!', 'millicache' ) );
		} catch ( fetchError ) {
			showSnackbar( __( 'Failed to save settings', 'millicache' ) );
		} finally {
			setTimeout( () => setIsSaving( false ), 1200 );
		}
	};

	return (
		<SettingsContext.Provider
			value={ {
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
