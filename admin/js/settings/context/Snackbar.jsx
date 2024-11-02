import { createContext, useContext, useState } from '@wordpress/element';
import { SnackbarList } from '@wordpress/components';

const SnackbarContext = createContext();

export const SnackbarProvider = ( { children } ) => {
	const [ snackMessages, setSnackMessages ] = useState( [] );

	const showSnackbar = ( message, actions = [], timeout = 3000, explicitDismiss= false ) => {
		const id = Date.now();
		setSnackMessages( ( prevMessages ) => [ ...prevMessages, {
			id: id,
			content: message,
			actions: actions,
			explicitDismiss: explicitDismiss,
			spokenMessage: message,
		} ] );

		setTimeout(() => {
			hideSnackbar(id);
		}, timeout);
	};

	const hideSnackbar = ( id ) => {
		setSnackMessages( ( prevMessages ) => prevMessages.filter( ( msg ) => msg.id !== id ) );
	};

	return (
		<SnackbarContext.Provider value={ { showSnackbar } }>
			{ children }
			<SnackbarList
				className="millicache-settings-snacks"
				notices={ snackMessages }
				onRemove={ ( id ) => hideSnackbar( id ) }
			/>
		</SnackbarContext.Provider>
	);
};

export const useSnackbar = () => {
	return useContext( SnackbarContext );
};