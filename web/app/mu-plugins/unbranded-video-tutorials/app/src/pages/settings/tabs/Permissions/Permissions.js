/**
 * Library dependencies
 */
import { Box } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { TabsFooter } from '../../components/TabsFooter'
import { UserRoles } from '../../../../components'
import { setOption, getOption } from '../../../../store/slices/settings'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'
import { __ } from '@wordpress/i18n'

export function Permissions() {
	const dispatch = useDispatch()
	const rolesValues = useSelector((state) => getOption(state, 'roles', []))

	const updateRoles = (roles) => {
		dispatch(setOption({ key: 'roles', value: roles }))
	}

	return (
		<Box>
			<Box.Header>
				<Box.Title>{__('Permissions', 'wpmudev_vids')}</Box.Title>
			</Box.Header>
			<Box.Body>
				<Box.Row>
					<Box.Col1>
						<Box.Label>
							{__('User Permissions', 'wpmudev_vids')}
						</Box.Label>
						<Box.Description>
							{__(
								'Configure which user roles can access and configure plugin’s settings.',
								'wpmudev_vids'
							)}
						</Box.Description>
					</Box.Col1>
					<Box.Col2 id="user-permissions">
						<h4
							id="user-roles-label"
							className="sui-settings-label"
						>
							{__('User Roles', 'wpmudev_vids')}
						</h4>
						<p className="sui-description">
							{__(
								'Choose which user roles can have access and configure Integrated Video Tutorials.',
								'wpmudev_vids'
							)}
						</p>

						<UserRoles
							rolesValues={rolesValues}
							onChange={updateRoles}
						/>
					</Box.Col2>
				</Box.Row>
			</Box.Body>
			<TabsFooter />
		</Box>
	)
}

export default Permissions
