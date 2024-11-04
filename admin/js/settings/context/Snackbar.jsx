import { createContext, useContext, useState } from '@wordpress/element';
import { SnackbarList } from '@wordpress/components';

const SnackbarContext = createContext();

export const SnackbarProvider = ( { children } ) => {
	const [ snackMessages, setSnackMessages ] = useState( [] );

	const showSnackbar = (
		message,
		actions = [],
		timeout = 3000,
		explicitDismiss = false
	) => {
		const id = Math.random().toString(36).slice(2, 11);
		setSnackMessages( ( prevMessages ) => [
			...prevMessages,
			{
				id,
				content: message,
				actions,
				explicitDismiss,
				spokenMessage: message,
			},
		] );

		setTimeout( () => {
			hideSnackbar( id );
		}, timeout );
	};

	const hideSnackbar = ( id ) => {
		setSnackMessages( ( prevMessages ) =>
			prevMessages.filter( ( msg ) => msg.id !== id )
		);
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
