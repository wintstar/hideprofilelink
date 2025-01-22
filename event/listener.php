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
		$this->auth = $auth;
	}

	public static function getSubscribedEvents()
	{
		return array(
			'core.permissions'							=> 'core_add_permission',
			'core.modify_username_string'				=> 'remove_profil_link',
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

	public function remove_profil_link($vars)
	{
		if (!$this->auth->acl_gets('a_hpl_view_profilelink', 'm_hpl_view_profilelink', 'u_hpl_view_profilelink'))
		{
			if ($vars['mode'] == 'full')
			{
				$username = strip_tags($vars['username_string']);
				$colour = !empty($vars['username_colour']) ? ' style="color: ' . $vars['username_colour'] . ';"' : '';

				$vars['username_string'] =  '<span' . $colour . ' class="username-coloured">' . $username . '</span>';
			}
		}
	}
}
