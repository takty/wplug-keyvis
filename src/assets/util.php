<?php
/**
 * Utilities
 *
 * @package Wplug Keyvis
 * @author Takuto Yanagida
 * @version 2021-08-27
 */

namespace wplug\keyvis;

/**
 * Gets the URL of the file.
 *
 * @param string $path The path of a file.
 * @return string The URL.
 */
function get_file_uri( string $path ): string {
	$path = wp_normalize_path( $path );

	if ( is_child_theme() ) {
		$theme_path = wp_normalize_path( defined( 'CHILD_THEME_PATH' ) ? CHILD_THEME_PATH : get_stylesheet_directory() );
		$theme_uri  = get_stylesheet_directory_uri();

		// When child theme is used, and libraries exist in the parent theme.
		$len_t = strlen( $theme_path );
		$len   = strlen( $path );
		if ( $len_t >= $len || 0 !== strncmp( $theme_path . $path[ $len_t ], $path, $len_t + 1 ) ) {
			$theme_path = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_template_directory() );
			$theme_uri  = get_template_directory_uri();
		}
		return str_replace( $theme_path, $theme_uri, $path );
	} else {
		$theme_path = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_stylesheet_directory() );
		$theme_uri  = get_stylesheet_directory_uri();
		return str_replace( $theme_path, $theme_uri, $path );
	}
}

/**
 * Gets the absolute URL of the relative URL.
 *
 * @param string $base A base URL.
 * @param string $rel  A relative URL.
 * @return string The absolute URL.
 */
function abs_url( string $base, string $rel ): string {
	$scheme = wp_parse_url( $rel, PHP_URL_SCHEME );
	if ( false === $scheme || '' !== $scheme ) {
		return $rel;
	}
	$base = trailingslashit( $base );
	if ( '#' === $rel[0] || '?' === $rel[0] ) {
		return $base . $rel;
	}
	$pu = wp_parse_url( $base );
	// phpcs:disable
	$scheme = isset( $pu['scheme'] ) ? $pu['scheme'] . '://' : '';
	$host   = isset( $pu['host'] )   ? $pu['host']           : '';
	$port   = isset( $pu['port'] )   ? ':' . $pu['port']     : '';
	$path   = isset( $pu['path'] )   ? $pu['path']           : '';
	// phpcs:enable
	$path = preg_replace( '#/[^/]*$#', '', $path );
	if ( '/' === $rel[0] ) {
		$path = '';
	}
	$abs = "$host$port$path/$rel";
	$re  = array( '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' );
	for ( $n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, -1, $n ) ) {}  // phpcs:ignore
	return $scheme . $abs;
}


// -----------------------------------------------------------------------------


/**
 * Separates a line.
 *
 * @param string $str A string.
 * @return array An array of strings.
 */
function separate_line( string $str ): array {
	return array_map(
		function ( $s ) {
			return \st\separate_text_and_make_spans( $s );
		},
		preg_split( '/　　|<\s*br\s*\/?>/ui', $str )
	);
}

/**
 * Separates a text and make spans.
 *
 * @param string $text A string.
 * @return string A string of spanned text.
 */
function separate_text_and_make_spans( string $text ): string {
	$parts = separate_text( $text );
	$ret   = '';
	foreach ( $parts as $ws ) {
		$_w   = esc_html( $ws[0] );
		$ret .= $ws[1] ? "<span>$_w</span>" : $_w;
	}
	return $ret;
}

/**
 * Separates a text.
 *
 * @param string $text A string.
 * @return array An array of text parts.
 */
function separate_text( string $text ): array {
	$pairs  = array(
		'S*' => 1,
		'*E' => 1,
		'II' => 1,
		'KK' => 1,
		'HH' => 1,
		'HI' => 1,
	);
	$parts  = array();
	$t_prev = '';
	$word   = '';

	for ( $i = 0, $len = mb_strlen( $text ); $i < $len; ++$i ) {
		$c = mb_substr( $text, $i, 1 );
		$t = _get_ctype( $c );
		if ( isset( $pairs[ $t_prev . $t ] ) || isset( $pairs[ '*' . $t ] ) || isset( $pairs[ $t_prev . '*' ] ) ) {
			$word .= $c;
		} elseif ( 'O' === $t ) {
			if ( 'O' === $t_prev ) {
				$word .= $c;
			} else {
				if ( ! empty( $word ) ) {
					$parts[] = array( $word, true );
				}
				$word = $c;
			}
		} else {
			if ( ! empty( $word ) ) {
				$parts[] = array( $word, ( 'O' !== $t_prev ) );
			}
			$word = $c;
		}
		$t_prev = $t;
	}
	if ( ! empty( $word ) ) {
		$parts[] = array( $word, ( 'O' !== $t_prev ) );
	}
	return $parts;
}

/**
 * Gets character types.
 *
 * @param string $c A character.
 * @return string The character type.
 */
function _get_ctype( string $c ): string {
	$ps = array(
		'S' => '/[「『（［｛〈《【〔〖〘〚＜]/u',
		'E' => '/[」』）］｝〉》】〕〗〙〛＞、，。．？！を：]/u',
		'I' => '/[ぁ-んゝ]/u',
		'K' => '/[ァ-ヴーｱ-ﾝﾞｰ]/u',
		'H' => '/[一-龠々〆ヵヶ]/u',
	);
	foreach ( $ps as $t => $p ) {
		if ( preg_match( $p, $c ) === 1 ) {
			return $t;
		}
	}
	return 'O';
}
