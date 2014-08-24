<?php
/**
*
* This file is part of the Thanks for posts extension package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace gfksx\ThanksForPosts\notification;

/**
* Thanks for posts notifications class
* This class handles notifying users when they have been thanked for a post
*/

class thanks extends \phpbb\notification\type\base
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'thanks';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_THANKS';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_THANKS',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	);

	/**
	* Is available
	*/
	public function is_available()
	{
		return true;
	}

	/**
	* Get the id of the item
	*
	* @param array $thanks_data The data from the thank
	*/
	public static function get_item_id($thanks_data)
	{
		return (int) $thanks_data['post_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $thanks_data The data from the thank
	*/
	public static function get_item_parent_id($thanks_data)
	{
		return (int) $thanks_data['topic_id'];
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $thanks_data The data from the thank
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($thanks_data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		$users = array((int) $thanks_data['poster_id']);

		return $this->check_user_notification_options($users, $options);
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('user_id'));
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('user_id'), 'no_profile');

		return $this->user->lang($this->language_key . '_' . $this->get_data('lang_act'), $username);
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		$thankers = $this->get_data('thankers');
		$users = array(
			$this->get_data('user_id'),
		);

		if (is_array($thankers))
		{
			foreach ($thankers as $thanker)
			{
				$users[] = $thanker['user_id'];
			}
		}

		return $users;
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p={$this->item_id}#p{$this->item_id}");
	}

	/**
	* {inheritDoc}
	*/
	public function get_redirect_url()
	{
		return $this->get_url();
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return '@gfksx_ThanksForPosts/user_thanks';
	}

	/**
	* Get the HTML formatted reference of the notification
	*
	* @return string
	*/
	public function get_reference()
	{
		return $this->user->lang(
			'NOTIFICATION_REFERENCE',
			censor_text($this->get_data('post_subject'))
		);
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$user_data = $this->user_loader->get_user($this->get_data('poster_id'));

		return array(
				'THANKS_SUBG'	=> htmlspecialchars_decode($this->user->lang['GRATITUDES']),
				'USERNAME'		=> htmlspecialchars_decode($this->user->data['username']),
				'POST_THANKS'	=> htmlspecialchars_decode($this->user->lang['THANKS_PM_MES_'. $this->get_data('lang_act')]),
				'U_POST_THANKS'	=> generate_board_url() . '/viewtopic.' . $this->php_ext . "?p={$this->item_id}#p{$this->item_id}",
		);
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $thanks_data Data from insert_thanks
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($thanks_data, $pre_create_data = array())
	{
		$this->set_data('user_id', $thanks_data['user_id']);
		$this->set_data('post_id', $thanks_data['post_id']);
		$this->set_data('lang_act', $thanks_data['lang_act']);
		$this->set_data('post_subject', $thanks_data['post_subject']);

		return parent::create_insert_array($thanks_data, $pre_create_data);
	}
}