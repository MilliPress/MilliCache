import { useState } from '@wordpress/element';
import {
	Panel,
	PanelBody,
	Button,
	TextareaControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const RulesSettings = () => {
	const [ rules, setRules ] = useState( [] );
	const [ newRule, setNewRule ] = useState( { condition: '', action: '' } );

	const addRule = () => {
		setRules( [ ...rules, newRule ] );
		setNewRule( { condition: '', action: '' } );
	};

	return (
		<Panel header={ __( 'Rules Settings', 'millicache' ) }>
			<PanelBody title={ __( 'Add New Rule', 'millicache' ) }>
				<TextareaControl
					label={ __( 'Condition', 'millicache' ) }
					value={ newRule.condition }
					onChange={ ( value ) =>
						setNewRule( { ...newRule, condition: value } )
					}
				/>
				<TextareaControl
					label={ __( 'Action', 'millicache' ) }
					value={ newRule.action }
					onChange={ ( value ) =>
						setNewRule( { ...newRule, action: value } )
					}
				/>
				<Button isPrimary onClick={ addRule }>
					{ __( 'Add Rule', 'millicache' ) }
				</Button>
			</PanelBody>
			<PanelBody title={ __( 'Existing Rules', 'millicache' ) }>
				{ rules.length === 0 ? (
					<p>{ __( 'No rules added yet.', 'millicache' ) }</p>
				) : (
					<ul>
						{ rules.map( ( rule, index ) => (
							<li key={ index }>
								<strong>
									{ __( 'Condition:', 'millicache' ) }
								</strong>{ ' ' }
								{ rule.condition }
								<br />
								<strong>
									{ __( 'Action:', 'millicache' ) }
								</strong>{ ' ' }
								{ rule.action }
							</li>
						) ) }
					</ul>
				) }
			</PanelBody>
		</Panel>
	);
};

export default RulesSettings;
