<?php
/**
 * Class ReplaceProtectedPasswordTest
 *
 * @package Replace_Protected_Password
 */

class ReplaceProtectedPasswordTest extends WP_UnitTestCase {

    function test_options_page_has_multi_record() {
        $post_password_a = 'abcdefghijklmnopqrst';
        $post_password_n = '12345678901234567890';
        $count_a = 3;
        $count_n = 10;

        for( $i = 0; $i < $count_a; $i++ ) {
            $this->factory->post->create( array( 'post_title' => 'test', 'post_password' => $post_password_a ) );
        }
        for( $i = 0; $i < $count_n; $i++ ) {
            $this->factory->post->create( array( 'post_title' => 'test', 'post_password' => $post_password_n ) );
        }

        $rpp = new replace_protected_password();
        $rpp->register();

        ob_start();
        $rpp->options_page();
        $contents = ob_get_contents();
        ob_end_clean();

        $pos = strpos( $contents, sprintf( '<option class="level-0" value="%s">%s(%s)</option>', $post_password_a, $post_password_a, $count_a) );
        $this->assertTrue( ( $pos !== false ) );

        $pos = strpos( $contents, sprintf( '<option class="level-0" value="%s">%s(%s)</option>', $post_password_n, $post_password_n, $count_n) );
        $this->assertTrue( ( $pos !== false ) );
    }
}
