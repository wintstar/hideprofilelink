<?php
/**
 *
 * @package phpBB Extension - wintstar Hide profile link
 * @author St. Frank <webdesign@stephan-frank.de> https://www.stephan-frank.de
 * @copyright (c) 2024 St.Frank
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
*/

namespace wintstar\hideprofilelink\migrations\v10x;

class release_0_0_1 extends \phpbb\db\migration\container_aware_migration
{
	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v330\v330');
	}

	public function update_data()
	{
		return array(
			// The needed permissions
			['permission.add', ['a_hpl_view_profilelink']],
			['permission.add', ['m_hpl_view_profilelink']],
			['permission.add', ['u_hpl_view_profilelink']],

			// Join permissions to administrators
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_hpl_view_profilelink']]
		);
	}
}