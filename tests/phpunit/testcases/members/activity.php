<?php

/**
 * @group members
 * @group activity
 */
class BP_Tests_Members_Activity extends BP_UnitTestCase {

	/**
	 * @group activity_action
	 * @group bp_core_format_activity_action_new_member
	 */
	public function test_bp_members_format_activity_action_new_member() {
		$u = self::factory()->user->create();
		$a = self::factory()->activity->create( array(
			'component' => buddypress()->members->id,
			'type' => 'new_member',
			'user_id' => $u,
		) );

		$expected = sprintf( __( '%s became a registered member', 'buddypress' ), bp_core_get_userlink( $u ) );

		$a_obj = new BP_Activity_Activity( $a );

		$this->assertSame( $expected, $a_obj->action );
	}

	/**
	 * @group activity_action
	 * @group bp_members_format_activity_action_new_avatar
	 */
	public function test_bp_members_format_activity_action_new_avatar() {
		$u = self::factory()->user->create();
		$a = self::factory()->activity->create( array(
			'component' => 'members',
			'type' => 'new_avatar',
			'user_id' => $u,
		) );

		$expected = sprintf( __( '%s changed their profile picture', 'buddypress' ), bp_core_get_userlink( $u ) );

		$a_obj = new BP_Activity_Activity( $a );

		$this->assertSame( $expected, $a_obj->action );
	}

	/**
	 * @group bp_migrate_new_member_activity_component
	 */
	public function test_bp_migrate_new_member_activity_component() {
		global $wpdb;
		$bp = buddypress();

		$u1 = self::factory()->user->create();
		$u2 = self::factory()->user->create();
		$u3 = self::factory()->user->create();

		$au1 = self::factory()->activity->create( array(
			'component' => 'xprofile',
			'type' => 'new_member',
			'user_id' => $u1,
		) );

		$au2 = self::factory()->activity->create( array(
			'component' => 'xprofile',
			'type' => 'new_member',
			'user_id' => $u2,
		) );

		$au3 = self::factory()->activity->create( array(
			'component' => 'xprofile',
			'type' => 'new_member',
			'user_id' => $u3,
		) );

		bp_migrate_new_member_activity_component();

		$expected = array(
			$u1 => $au1,
			$u2 => $au2,
			$u3 => $au3,
		);

		$in = "'" . implode( "', '", array_keys( $expected ) ) . "'";
		$found = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, id FROM {$bp->members->table_name_last_activity} WHERE component = %s AND type = %s AND user_id IN ({$in}) ORDER BY user_id ASC",
				$bp->members->id,
				'new_member'
		), OBJECT_K );

		$found = array_map( 'intval', wp_list_pluck( $found, 'id' ) );

		$this->assertSame( $expected, $found );
	}
}
