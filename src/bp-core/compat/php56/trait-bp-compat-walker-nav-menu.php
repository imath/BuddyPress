<?php
/**
 * Walker_Nav_Menu Compat for PHP 5.6 and UP.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 5.1.0
 */

 // Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

trait BP_Compat_Walker_Nav_Menu {
	/**
	 * Compat method to extend Walker_Nav_Menu::walk() in PHP > 5.6.
	 *
	 * @since 5.1.0
	 *
	 * @param array $elements  See {@link Walker::walk()}.
	 * @param int   $max_depth See {@link Walker::walk()}.
	 * @param mixed ...$args   See {@link Walker::walk()}.
	 */
	public function walk( $elements, $max_depth, ...$args ) {
		return $this->do_walk( $elements, $max_depth, $args );
	}
}
