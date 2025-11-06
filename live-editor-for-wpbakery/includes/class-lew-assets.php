<?php
/**
 * Assets management for Live Editor for WPBakery
 *
 * @package LiveEditorWPBakery
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * LEW_Assets Class
 */
final class LEW_Assets {

	/**
	 * Singleton instances
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Asset name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Asset handles
	 *
	 * @var array
	 */
	private $handles = array();

	/**
	 * Get singleton instance
	 *
	 * @param string $name Asset name
	 * @return LEW_Assets
	 */
	public static function instance( $name = 'default' ) {
		if ( ! isset( self::$instances[ $name ] ) ) {
			self::$instances[ $name ] = new self( $name );
		}
		return self::$instances[ $name ];
	}

	/**
	 * Constructor
	 *
	 * @param string $name Asset name
	 */
	private function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Add asset handles
	 *
	 * @param array $handles Asset handles to add
	 * @return LEW_Assets Current instance for chaining
	 */
	public function add( $handles ) {
		if ( is_array( $handles ) ) {
			$this->handles = array_merge( $this->handles, $handles );
		}
		return $this;
	}

	/**
	 * Get assets data
	 *
	 * @return array Assets data
	 */
	public function get_assets() {
		global $wp_scripts, $wp_styles;

		$assets = array(
			'scripts' => array(),
			'styles'  => array(),
		);

		// Collect scripts
		foreach ( $this->handles as $handle ) {
			if ( isset( $wp_scripts->registered[ $handle ] ) ) {
				$script = $wp_scripts->registered[ $handle ];
				$assets['scripts'][ $handle ] = array(
					'src'  => $script->src,
					'deps' => $script->deps,
					'ver'  => $script->ver,
				);
			}
		}

		// Collect styles
		foreach ( $this->handles as $handle ) {
			if ( isset( $wp_styles->registered[ $handle ] ) ) {
				$style = $wp_styles->registered[ $handle ];
				$assets['styles'][ $handle ] = array(
					'src'   => $style->src,
					'deps'  => $style->deps,
					'ver'   => $style->ver,
					'media' => $style->args,
				);
			}
		}

		return $assets;
	}
}
