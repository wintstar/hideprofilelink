<?php
/**
 *
 * @package phpBB Extension - wintstar Board offline
 * @author St. Frank <webdesign@stephan-frank.de> https://www.stephan-frank.de
 * @copyright (c) 2024 St.Frank
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 *
*/

namespace wintstar\hideprofilelink\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * hideprofilelink event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                   $auth       Authentication object
	 * @param \phpbb\language\language           $lang
	 */
	public function __construct(\phpbb\auth\auth $auth)
	{
		$this->auth	= $auth;
	}

	/**
	 * Report on 24 Jan 2025 19:09 from Anișor, priority to 1
	 * https://www.phpbb.com/community/viewtopic.php?p=16049530#p16049530
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.permissions' 		=> 'core_add_permission',
			'core.modify_username_string'	=> ['remove_profile_link', 1],
		);
	}

	public function core_add_permission($event)
	{
		$permission = $event['permissions'];
		$permission['a_hpl_view_profilelink'] = ['lang' => 'ACL_A_HPL_VIEW_PROFILELINK', 'cat' => 'user_group'];
		$permission['m_hpl_view_profilelink'] = ['lang' => 'ACL_M_HPL_VIEW_PROFILELINK', 'cat' => 'misc'];
		$permission['u_hpl_view_profilelink'] = ['lang' => 'ACL_U_HPL_VIEW_PROFILELINK', 'cat' => 'profile'];

		$event['permissions'] = $permission;
	}

	/**
	 * Report on 24 Jan 2025 19:09 from Anișor, Compatibility with Ext Verified Profiles
	 * https://www.phpbb.com/community/viewtopic.php?p=16049602#p16049602
	 */
	public function remove_profile_link($event)
	{
		$mode = $event['mode'];
		$username_colour = $event['username_colour'];
		$username_string = $event['username_string'];
		$user_id = $event['user_id'];

		if (!$this->auth->acl_gets('a_hpl_view_profilelink', 'm_hpl_view_profilelink', 'u_hpl_view_profilelink', $user_id)) {
			if ($mode == 'full')
			{
				$username = $event['username'];
				$username_colour_code = ($username_colour) ? '' . $username_colour : '';
				$username_string = $username_colour ? "<span style='color: {$username_colour_code};' class='username-coloured'>{$username}</span>" : "<span class='username'>{$username}</span>";
			}
			else if ($mode == 'profile')
			{
				$username_string = '';
			}
		}

		//Send it back to the event
		$event['username_string'] = $username_string;
	}
}
