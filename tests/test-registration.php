<?php

class Test_EMP_Registration extends WP_UnitTestCase {

    public function test_event_cpt_is_registered() {
        $obj = get_post_type_object( 'emp_event' );
        $this->assertNotNull( $obj );
        $this->assertTrue( $obj->public );
    }

    public function test_event_type_taxonomy_is_registered() {
        $tax = get_taxonomy( 'emp_event_type' );
        $this->assertNotNull( $tax );
        $this->assertTrue( $tax->show_ui );
    }
}
