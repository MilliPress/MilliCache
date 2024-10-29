import { createContext, useContext, useState } from '@wordpress/element';
import { Snackbar } from '@wordpress/components';

const SnackbarContext = createContext();

export const SnackbarProvider = ( { children } ) => {
	const [ snackbarMessage, setSnackbarMessage ] = useState( '' );
	const [ isSnackbarVisible, setIsSnackbarVisible ] = useState( false );

	const showSnackbar = ( message ) => {
		setSnackbarMessage( message );
		setIsSnackbarVisible( true );
	};

	const hideSnackbar = () => {
		setIsSnackbarVisible( false );
	};

	return (
		<SnackbarContext.Provider value={ { showSnackbar } }>
			{ children }
			{ isSnackbarVisible && (
				<Snackbar
					className="millicache-settings-snacks"
					onRemove={ hideSnackbar }
				>
					{ snackbarMessage }
				</Snackbar>
			) }
		</SnackbarContext.Provider>
	);
};

export const useSnackbar = () => {
	return useContext( SnackbarContext );
};
