<?php
/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-08-22
 * Time: 09:56
 */

namespace APH;

/**
 * Manage helpers for the FQ Accounts and related logic.
 * Eventually I'd like to see all the business logic codified here.
 *
 * Class FQ
 * @package APH
 */
class FQ
{

	const taxonomy = 'user-group';
	const AS_IDS = true;

	static $allowed_roles = [Roles::EOT, Roles::OOA, Roles::TVI];

	/**
	 * Give a user_id, return all possible EOT's this user can interact with.
	 * Used as an example as to how to use the sytem.
	 *
	 * @param $user_id
	 *
	 * @return array
	 */

	static function getUsersEOTs($user_id)
	{
		$allEOTs = FQ::getAllUsersWithRole('eot');
		$fq_account_ids = FQ::getAccountsForUser($user_id, FQ::AS_IDS);

		$account_eots = FQ::getUsersInAccount($allEOTs, $fq_account_ids);
		return $account_eots;
	}

	/**
	 * Given a list of FQ Account ('user-group' terms) Ids, and a list of users
	 * return a list of user id's that are also in that account.
	 *
	 * Inefficient, see if there's a WP_Query way to do this.
	 * May be a target for cacheing as it's called often.
	 *
	 * @param array $fq_ids
	 * @param array $users
	 *
	 * @return array
	 */

	static function getUsersInAccount(array $users, array $fq_ids)
	{

		$users_in_account = [];

		// Loop over all users that we have extracted, and select only the ones
		// that belong to one (or more) of the passed FQ accounts.
		foreach ($users as $user) {

			// Get a list of all FQ Accounts this user is a member of
			$accounts = self::getAccountsForUser($user->ID, FQ::AS_IDS);
			aph_write_log([$user->ID, $accounts], "FQAccount Membership");

			// find the common terms
			$common = array_intersect($fq_ids, $accounts);

			// If we have something in common
			if (count($common) > 0) {
				$users_in_account[] = $user->ID;
			}
		}

		return $users_in_account;
	}

	/**
	 * Given a user_id, return the user-groups this account is a member of (FQ Accounts)
	 *
	 * @param $user_id
	 * @param bool $return_ids
	 *
	 * @return array|\WP_Error
	 */
	static function getAccountsForUser($user_id, $return_ids = false)
	{

		$term_ids = [];

		$terms = wp_get_object_terms($user_id, FQ::taxonomy, array(
			'fields' => 'all_with_object_id'
		));

		// aph_write_log($terms, 'getAccountsForUser - Terms ($userid)');
		// may be a better way to do this
		if ($return_ids) {
			foreach ($terms as $term) {
				$term_ids[] = $term->term_id;
			}
			return $term_ids;
		} else {
			return $terms;
		}
	}

	/**
	 * Given a role, return *all* database users with that role.
	 * protect from a table scan by limiting the roles that can be passed.
	 *
	 * @todo: determine if WP caches this query, if not we should.
	 *
	 * @param $role
	 *
	 * @return array
	 */
	static function getAllUsersWithRole($role)
	{

		static $roles = [];

		// Cache this so we only have to do this once.
		if (isset($roles[$role])) return $roles[$role];

		if (!in_array($role, self::$allowed_roles)) {
			aph_write_log("Invalid role - getUsersWithRole ($role)", "error");
			return [];
		}

		// get all users with role, order by last name for consistency and display.
		$wp_user_query = new \WP_User_Query([
			'role' => $role,
			'meta_key' => 'last_name',
			'orderby' => 'meta_value',
			'order' => 'ASC'
		]);
		$role_users = $wp_user_query->get_results();

		$roles[$role] = $role_users;

		return $role_users;
	}

	/**
	 * A list of the roles we want to include in the chart.
	 * Broken out her so it only needs to be modified in one place.
	 *
	 * @return array
	 */
	static function getQuotaChartRoles()
	{
		// List of the roles we want in the chart
		return [Roles::EOT, Roles::OOA, Roles::TVI];
	}

	/**
	 * Here we're building up a data structure that includes All the FQ Accounts,
	 * indexed by id, with their members listed in their members field by role.
	 *
	 * This is done by
	 * 1. getting all the FQ accounts, and indexing them by id
	 * 2. Looping over each Role, and getting all users for each role ....
	 * 3.   Checking each user to see which FQ_Id's they are atttached to
	 * 4.     Attaching that user (by reference) to each FQ account in their appropriate role slot.
	 * 5. returning the data structure for display
	 *
	 * This assumes that each user can have at most 1 role (which is assumed in most
	 * of the system, so really a constraint)
	 *
	 * Note that this "feels" hacky, and is limited by the WP API since we don't have a
	 * database with normalized tables.
	 *
	 * Also, this doesn't scale since we're building the data up in memory ... too many FQ accounts,
	 * or admins for this account and this will eventually run out of memory. Judicious cacheing
	 * is used to minimimize the copies.
	 *
	 * (assumption is that the number of EOT's and accounts is constrained to some low-ish number)
	 *
	 * @param null $terms optional list of the FQ accounts we want data for, defaults to "all"
	 *
	 * @return array
	 * 
	 * EDIT: adding second param $the_roles, to dictate which roles are shown in the chart.
	 */
	static function getQuotaChartData($terms = null, $the_roles = [Roles::EOT, Roles::OOA, Roles::TVI])
	{

		if ($terms === []) return []; // Nothing to do

		if ($terms === null) {
			// get ALL FQ accounts, which are stored as terms
			$terms = get_terms(FQ::taxonomy);
		}
		// We now have a list of terms

		// Index these in a hashtable so that we can look them up more easily, keyed
		// on the FQid (term_id);
		$FQs = [];
		foreach ($terms as $term) {

			// Grab the quota balances
			$term->balance = get_field('fq_account_balance', 'user-group_' . $term->term_id);
			$term->outstanding = get_field('fq_outstanding_balance', 'user-group_' . $term->term_id);
			$term->available = get_field('fq_available_funding', 'user-group_' . $term->term_id);
			$term->overspent = get_field('fq_overspent', 'user-group_' . $term->term_id);

			$FQs[$term->term_id] = $term;
			$term->members = [];
		}

		// $roles = self::getQuotaChartRoles();
		$roles = $the_roles;

		/*
		Here we're grabbing ALL users and assigning them to terms only
		if the term has been loaded above.

		It may be more efficient to turn this sideways - loop through all
		terms and only get the users for those terms.  It was done this way
		because it minimizes the number of database queries in the case where
		we are getting all terms.
		*/

		foreach ($roles as $role) {
			$role_users = FQ::getAllUsersWithRole($role);

			foreach ($role_users as $user) {
				$role_term_ids = FQ::getAccountsForUser($user->ID, FQ::AS_IDS);

				foreach ($role_term_ids as $term_id) {
					// FIX: we only want to display users for terms we have previously loaded
					// without this, we get everyone
					if (!empty($FQs[$term_id])) {
						$FQs[$term_id]->members[$role][] = $user;
					}
				}
			}

		}

		return $FQs;
	}

}
