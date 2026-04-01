/**
 * Library dependencies
 */
import { Checkbox } from '../../lib/components'

/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import { __ } from '@wordpress/i18n'

const { roles } = window.ivtModuleVars ?? {}

export function UserRoles({ rolesValues, onChange }) {
	const { flags } = window.ivtVars ?? {}

	/**
	 * Updates roles array
	 */
	const updateRoles = (role, checked) => {
		let newRoles = [...rolesValues]
		if (checked) {
			newRoles.push(role)
		} else {
			newRoles = newRoles.filter((el) => el !== role)
		}
		onChange(newRoles)
	}

	return (
		<div
			role="group"
			className="sui-form-field"
			aria-labelledby="user-roles-label"
		>
			{flags?.multisite > 0 && (
				<Checkbox
					key="network-admin"
					checked={true}
					id="wpmudev-videos-roles-network-administrator"
					label={__('Network Administrator', 'wpmudev_vids')}
					disabled={true}
				/>
			)}
			{flags?.network > 0 && (
				<Checkbox
					key="super-admin"
					checked={true}
					id="playlist-role-super-admin"
					label={__('Super Admin', 'wpmudev_vids')}
					disabled={true}
				/>
			)}
			<Checkbox
				key="Administrator"
				checked={true}
				id="wpmudev-videos-roles-administrator"
				label={__('Administrator', 'wpmudev_vids')}
				disabled={true}
			/>
			{Object.keys(roles).map((role) => (
				<Checkbox
					key={role}
					checked={rolesValues.includes(role)}
					onChange={(checked) => updateRoles(role, checked)}
					id={`wpmudev-videos-roles-${role}`}
					label={roles[role]}
				/>
			))}
		</div>
	)
}

UserRoles.defaultProps = {
	rolesValues: [],
	onChange: () => null,
}

UserRoles.propTypes = {
	rolesValues: PropTypes.array.isRequired,
	onChange: PropTypes.func,
}

export default UserRoles
