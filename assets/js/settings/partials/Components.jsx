import { Flex, Tooltip } from '@wordpress/components';
import { Icon, help } from '@wordpress/icons';

/**
 * Renders a label with a tooltip help icon.
 *
 * @param {Object} props Component properties.
 * @param {string|React.ReactNode} props.label The label text or component.
 * @param {string} props.tooltip The text to display in the tooltip.
 * @param {number} [props.iconSize=16] Size of the help icon.
 * @param {string} [props.justify='flex-start'] Justification of the label and icon.
 * @param {Object} [props.style] Additional styles for the container.
 * @param {Object} [props.tooltipProps] Additional props for the Tooltip component.
 * @param {Object} [props.iconProps] Additional props for the Icon component.
 * @return {React.ReactElement} The label with a tooltip component.
 */
export const LabelWithTooltip = ({
         label,
         tooltip,
         iconSize = 16,
         justify = 'flex-start',
         style = {},
         tooltipProps = {},
         iconProps = {}
     }) => (
    <Flex align="center" gap={1} style={style} justify={justify}>
        <span>{label}</span>
        <Tooltip text={tooltip} delay="250" style={{ maxWidth: '300px' }} {...tooltipProps}>
          <span className="millicache-tooltip-icon" style={{ display: 'flex', alignItems: 'center' }}>
            <Icon
                icon={help}
                size={iconSize}
                {...iconProps}
            />
          </span>
        </Tooltip>
    </Flex>
);
