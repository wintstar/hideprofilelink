<?php
/**
 *
 * @package phpBB Extension - wintstar Hide profile link
 * @author St. Frank <webdesign@stephan-frank.de> https://www.stephan-frank.de
 * @copyright (c) 2024 St.Frank
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * 
*/

namespace wintstar\hideprofilelink;

class ext extends \phpbb\extension\base
{
	/**
	 * Enable extension if phpBB minimum version requirement is met
	 *
	 * Requires phpBB 3.3.* due to usage of new exception classes.
	 *
	 * @return bool
	 * @aceess public
	 */
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>=') && phpbb_version_compare(PHP_VERSION, '7.4.0', '>=');
	}
}