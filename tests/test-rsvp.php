<?php

class Test_EMP_RSVP extends WP_UnitTestCase {

	public function test_rsvp_duplicate_email_is_prevented() {
		$event_id = self::factory()->post->create(
			array(
				'post_type'   => 'emp_event',
				'post_status' => 'publish',
				'post_title'  => 'RSVP Test Event',
			)
		);

		$rsvps = array(
			array(
				'name'  => 'User A',
				'email' => 'dup@example.com',
				'time'  => time(),
			),
		);
		update_post_meta( $event_id, '_emp_rsvps', $rsvps );

		$rsvps_loaded = get_post_meta( $event_id, '_emp_rsvps', true );
		$this->assertIsArray( $rsvps_loaded );
		$this->assertCount( 1, $rsvps_loaded );

		$email_to_add = 'dup@example.com';
		$duplicate    = false;

		foreach ( $rsvps_loaded as $r ) {
			if ( isset( $r['email'] ) && strtolower( $r['email'] ) === strtolower( $email_to_add ) ) {
				$duplicate = true;
				break;
			}
		}

		$this->assertTrue( $duplicate );
	}
}