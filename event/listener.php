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

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                   $auth       Authentication object
	 * @param \phpbb\user                        $user
	 * @param \phpbb\language\language           $lang
	 * @param string                             $root_path
	 * @param string                             $php_ext
	 */
	public function __construct(\phpbb\auth\auth $auth,
		\phpbb\user $user,
		\phpbb\language\language $lang,
		$phpbb_root_path,
		$php_ext)
	{
		$this->auth	= $auth;
		$this->user = $user;
		$this->lang = $lang;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Report on 24 Jan 2025 19:09 from Anișor, priority to 1
	 * https://www.phpbb.com/community/viewtopic.php?p=16049530#p16049530
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.user_setup'                        => 'load_language_on_setup',
			'core.permissions'                       => 'core_add_permission',
			'core.modify_username_string'            => ['remove_profile_link', 1],
			'core.memberlist_modify_viewprofile_sql' => ['no_view_profile', 1],
		);
	}

	/**
	 * Load common language files during user setup
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];

		$lang_set_ext[] = array(
			'ext_name' => 'wintstar/hideprofilelink',
			'lang_set' => array('hideprofilelink'),
		);
		$event['lang_set_ext'] = $lang_set_ext;
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
		$username = $event['username'];
		$self_user_id = ($user_id == $this->user->data['user_id']) ? true : false;

		if (!$this->auth->acl_gets('a_hpl_view_profilelink', 'm_hpl_view_profilelink', 'u_hpl_view_profilelink', $user_id))
		{
			$profile_url = ($event['custom_profile_url'] !== false) ? $event['custom_profile_url'] . '&amp;u=' . (int) $user_id : str_replace(array('={USER_ID}', '=%7BUSER_ID%7D'), '=' . (int) $user_id, $event['_profile_cache']['base_url']);
			$tpl_profile = "<a href=\"" . $profile_url . "\" class=\"username\">" . $username . "</a>";


			if ($mode == 'full')
			{
				if ($self_user_id)
				{
					$username_string = $tpl_profile;
				}
				else
				{
					$username = $event['username'];
					$username_colour_code = ($username_colour) ? '' . $username_colour : '';
					$username_string = $username_colour ? "<span style='color: {$username_colour_code};' class='username-coloured'>{$username}</span>" : "<span class='username'>{$username}</span>";
				}
			}

			if ($mode == 'profile')
			{
				if ($self_user_id && ($this->user->data['user_id'] != ANONYMOUS))
				{
					$username_string = $profile_url;
				}
				else
				{
					$username_string = "javascript:void(0);";
				}
			}
		}

		//Send it back to the event
		$event['username_string'] = $username_string;
	}

	public function no_view_profile($event)
	{
		$this->user->add_lang_ext('wintstar/hideprofilelink', 'hideprofilelink');

		$check_page = $this->user->page['query_string'];
		$userid_page = ($check_page == 'mode=viewprofile&u=' . $event['user_id']) ? true : false;
		$username_page = ($check_page == 'mode=viewprofile&un=' . $event['username']) ? true : false;
		$self_user_id = ($event['user_id'] == $this->user->data['user_id']) ? true : false;
		$self_username = ($event['username'] == $this->user->data['username']) ? true : false;
		$message = $this->lang->lang('NO_VIEW_USERSPROFILE') . '<br /><br />' . sprintf($this->lang->lang('RETURN_INDEX'), '<a href="' . append_sid("{$this->phpbb_root_path}index.$this->php_ext") . '">', '</a> ');

		if (!$this->auth->acl_gets('a_hpl_view_profilelink', 'm_hpl_view_profilelink', 'u_hpl_view_profilelink', $this->user->data['user_id'])) {
			if (($userid_page && !$self_user_id) || ($username_page && !$self_username)) {
				send_status_line(403, 'Forbidden');
				trigger_error($message);
			}
		}
	}
}
