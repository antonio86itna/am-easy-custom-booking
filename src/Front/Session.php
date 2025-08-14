<?php
// phpcs:ignoreFile
/**
 * Session helper.
 *
 * @package AMCB
 */

namespace AMCB\Front;

/**
 * Handle simple session via transients.
 */
class Session {
	const TTL = 900; // 15 min.

	/**
	 * Get or generate session ID.
	 *
	 * @return string
	 */
	public static function id() {
		if ( empty( $_COOKIE['amcb_sid'] ) ) {
			$sid = wp_generate_uuid4();
			setcookie(
				'amcb_sid',
				$sid,
				time() + DAY_IN_SECONDS,
				COOKIEPATH,
				COOKIE_DOMAIN,
				is_ssl(),
				true
			);
			$_COOKIE['amcb_sid'] = $sid;
		}

                return sanitize_text_field( wp_unslash( $_COOKIE['amcb_sid'] ) );
	}

	/**
	 * Get session data.
	 *
	 * @param string|null $key Key to retrieve or null for all.
	 * @return mixed
	 */
	public static function get( $key = null ) {
		$data = get_transient( 'amcb_sess_' . self::id() );

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		return $key ? ( $data[ $key ] ?? null ) : $data;
	}

	/**
	 * Set session value.
	 *
	 * @param string $key Key.
	 * @param mixed  $val Value.
	 * @return void
	 */
	public static function set( $key, $val ) {
		$data         = self::get();
		$data[ $key ] = $val;
		set_transient( 'amcb_sess_' . self::id(), $data, self::TTL );
	}

	/**
	 * Destroy session data.
	 *
	 * @return void
	 */
	public static function destroy() {
		delete_transient( 'amcb_sess_' . self::id() );
	}
}
