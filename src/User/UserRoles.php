<?php

namespace DSI\TechPub\User;

use DSI\TechPub\Singleton;

defined('ABSPATH') || exit;
/**
 * Class UserRoles
 * @package DSI\TechPub
 */
class UserRoles extends Singleton
{
	/**
	 * User distributor role.
	 * note: This roles was already crated in the WP.
	 * The plugin wont' add or delete this role.
	 * we are only using its role slug to be used in the system.
	 *
	 * @var ROLE_DSI_RETAIL
	 */
	public const ROLE_DSI_DISTRIBUTOR = 'dma-distributor';

	/**
	 * User custom role.
	 *
	 * @var ROLE_DSI_RETAIL
	 */
	public const ROLE_DSI_RETAIL = 'dsi-retail';

	/**
	 * User custom role.
	 *
	 * @var ROLE_DSI_DISTRIBUTOR_PLUS
	 */
	public const ROLE_DSI_DISTRIBUTOR_PLUS = 'dsi-distributor-plus';

	/**
	 * User custom role.
	 *
	 * @var ROLE_DSI_STAFF_EMPLOYEE
	 */
	public const ROLE_DSI_STAFF_EMPLOYEE = 'dsi-staff-employee';

	/**
	 * Add user to custom role.
	 */
	public function add_role()
	{
		remove_role(self::ROLE_DSI_RETAIL);
		remove_role(self::ROLE_DSI_DISTRIBUTOR_PLUS);
		remove_role(self::ROLE_DSI_STAFF_EMPLOYEE);
		add_role(
			self::ROLE_DSI_RETAIL,
			'Retail Customer',
			get_role('subscriber')->capabilities
		);
		add_role(
			self::ROLE_DSI_DISTRIBUTOR_PLUS,
			'Distributor Plus',
			get_role('subscriber')->capabilities
		);
		add_role(
			self::ROLE_DSI_STAFF_EMPLOYEE,
			'Staff',
			get_role('subscriber')->capabilities
		);
	}

	/**
	 * Remove custom role.
	 */
	public function remove_role()
	{
		remove_role(self::ROLE_DSI_RETAIL);
		remove_role(self::ROLE_DSI_DISTRIBUTOR_PLUS);
		remove_role(self::ROLE_DSI_STAFF_EMPLOYEE);
	}

	/**
	 * Set a user role.
	 * @param int $user_id
	 * @param string $role
	 */
	public function set_user_role($user_id, $role)
	{
		if (empty($role)) {
			throw new \Exception('Role slug is missing');
		}
		if (!wp_roles()->is_role($role)) {
			throw new \Exception('Unknow user role');
		}
		$user = new \WP_User($user_id);
		$user->set_role($role);
	}

	/**
	 * Get user roles.
	 * @return array
	 **/
	public function get_current_user_roles()
	{
		if (is_user_logged_in()) {
			$user = wp_get_current_user();
			$roles = (array) $user->roles;
			return $roles;
		} else {
			return array();
		}
	}

	/**
	 * Check user role type.
	 * @return boolean
	 */
	public function is_staff_user()
	{
		if (current_user_can('manage_options') || in_array(self::ROLE_DSI_STAFF_EMPLOYEE, $this->get_current_user_roles())) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check user role type.
	 * @return boolean
	 */
	public function is_distributor_plus_user()
	{
		if (in_array(self::ROLE_DSI_DISTRIBUTOR_PLUS, $this->get_current_user_roles())) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check user role type.
	 * @return boolean
	 */
	public function is_distributor_user()
	{
		if (in_array(self::ROLE_DSI_DISTRIBUTOR, $this->get_current_user_roles())) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check user role type.
	 * @return boolean
	 */
	public function is_retail_user()
	{
		if (in_array(self::ROLE_DSI_RETAIL, $this->get_current_user_roles())) {
			return true;
		} else {
			return false;
		}
	}
}
