<?php
/**
 * Custom Field Utilities
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-26
 */

namespace wplug\slider;

// Multiple Post Meta ----------------------------------------------------------


function get_multiple_post_meta_from_env( string $base_key, array $keys ): array {
	$ret = [];

	// {$base_key}_{n}_{$key} (for backward compatibility)
	if ( isset( $_POST[ $base_key ] ) && is_numeric( $_POST[ $base_key ] ) ) {
		$count = (int) ( $_POST[ $base_key ] ?? 0 );

		for ( $i = 0; $i < $count; $i += 1 ) {
			$it  = [];
			foreach ( $keys as $key ) {
				$it[ $key ] = $_POST["{$base_key}_{$i}_{$key}"] ?? null;
			}
			$ret[] = $it;
		}
		return $ret;
	}
	// {$base_key}_{$key}[n] (Next best plan)
	if ( ! isset( $_POST[ $base_key ] ) ) {
		$count = count( $_POST[ "{$base_key}_{$keys[0]}" ] );

		for ( $i = 0; $i < $count; $i += 1 ) {
			$it = [];
			foreach ( $keys as $key ) {
				$it[ $key ] = $_POST["{$base_key}_{$key}"][ $i ] ?? null;
			}
			$ret[] = $it;
		}
		return $ret;
	}
	// {$base_key}[n][{$key}] (Best plan)
	if ( isset( $_POST[ $base_key ] ) && is_array( $_POST[ $base_key ] ) ) {
		foreach ( $_POST[ $base_key ] as $val ) {
			$it = [];
			foreach ( $keys as $key ) {
				$it[ $key ] = $val[ $key ] ?? null;
			}
			$ret[] = $it;
		}
		return $ret;
	}
	return [];
}


// -----------------------------------------------------------------------------


function get_multiple_post_meta( int $post_id, string $base_key, array $keys, ?string $special_key = null ): array {
	$ret = [];
	$val = get_post_meta( $post_id, $base_key, true );

	if ( is_numeric( $val ) ) {  // for backward compatibility
		$count = (int) $val;
		for ( $i = 0; $i < $count; $i += 1 ) {
			$it = [];
			foreach ( $keys as $key ) {
				$it[ $key ] = get_post_meta( $post_id, "{$base_key}_{$i}_{$key}", true );
			}
			$ret[] = $it;
		}
	} else if ( $special_key ) {
		$skv = null;
		$ret = json_decode( $val, true );
		if ( is_array( $ret ) ) {
			if ( isset( $ret['#'] ) ) {
				$skv = $ret[ $special_key ] ?? null;
				$ret = $ret['#'];
			}
		} else {
			$ret = [];
		}
		$ret[ $special_key ] = $skv;
	} else {
		$ret = json_decode( $val, true );
		if ( ! is_array( $ret ) ) $ret = [];
	}
	return $ret;
}

function update_multiple_post_meta( int $post_id, string $base_key, array $vals, ?array $keys = null, ?string $special_key = null ) {
	$val = get_post_meta( $post_id, $base_key, true );

	// Remove old style data
	if ( is_numeric( $val ) ) {
		$count = count( $vals );
		if ( null === $keys && 0 < $count ) {
			$keys = array_keys( reset( $vals ) );
		}
		$old_count = (int) $val;
		for ( $i = 0; $i < $old_count; $i += 1 ) {
			foreach ( $keys as $key ) {
				delete_post_meta( $post_id, "{$base_key}_{$i}_{$key}" );
			}
		}
		if ( 0 === $count ) {
			delete_post_meta( $post_id, $base_key );
		}
	}
	// Update data
	if ( $special_key ) {
		$skv = $vals[ $special_key ] ?? null;
		unset( $vals[ $special_key ] );

		if ( 0 === count( $vals ) && null === $skv ) {
			delete_post_meta( $post_id, $base_key );
		} else {
			foreach ( $vals as &$val ) {
				$it = [];
				foreach ( $keys as $key ) {
					$it[ $key ] = $val[ $key ];
				}
				$val = $it;
			}
			$vals = [ '#' => $vals, $special_key => $skv ];
		}
	} else {
		if ( 0 === count( $vals ) ) {
			delete_post_meta( $post_id, $base_key );
		} else {
			foreach ( $vals as &$val ) {
				$it = [];
				foreach ( $keys as $key ) {
					$it[ $key ] = $val[ $key ];
				}
				$val = $it;
			}
		}
	}
	$json = json_encode( $vals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $post_id, $base_key, addslashes( $json ) );  // Because the meta value is passed through the stripslashes() function upon being stored.
}
