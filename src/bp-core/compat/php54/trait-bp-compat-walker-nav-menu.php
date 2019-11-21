<?php
/**
 * Walker_Nav_Menu Compat for PHP 5.4 and UP.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 5.1.0
 */

 // Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Create HTML list of BP nav items.
 *
 * @since 1.7.0
 */
class BP_Walker_Nav_Menu extends BP_Walker_Nav_Menu_Compat {
	/**
	 * Use the Compat Trait according to PHP version.
	 *
	 * @since 5.1.0
	 */
	use BP_Compat_Walker_Nav_Menu;
}

trait BP_Compat_Walker_Nav_Menu { // phpcs:ignore
	/**
	 * Compat method to extend Walker_Nav_Menu::walk() in PHP < 5.6.
	 *
	 * @since 5.1.0
	 *
	 * @param array $elements  See {@link Walker::walk()}.
	 * @param int   $max_depth See {@link Walker::walk()}.
	 */
	public function walk( $elements, $max_depth ) {
		$args = array_slice( func_get_args(), 2 );

		return $this->do_walk( $elements, $max_depth, $args );
	}
}
