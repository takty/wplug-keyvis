<?php
/**
 * Utilities for Slider
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-02
 */

namespace wplug\slider;

function is_post_type( string $post_type ): bool {
	$post_id = get_post_id();
	$pt = get_post_type_in_admin( $post_id );
	return $post_type === $pt;
}

function get_post_id(): int {
	$post_id = '';
	if ( isset( $_GET['post'] ) || isset( $_POST['post_ID'] ) ) {
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post_ID'];
	}
	return (int) $post_id;
}

function get_post_type_in_admin( int $post_id ): string {
	$p = get_post( $post_id );
	if ( $p === null ) {
		if ( isset( $_GET['post_type'] ) ) return $_GET['post_type'];
		return '';
	}
	return $p->post_type;
}


// -----------------------------------------------------------------------------


function get_file_uri( string $path ): string {
	$path = wp_normalize_path( $path );

	if ( is_child_theme() ) {
		$theme_path = wp_normalize_path( defined( 'CHILD_THEME_PATH' ) ? CHILD_THEME_PATH : get_stylesheet_directory() );
		$theme_uri  = get_stylesheet_directory_uri();

		// When child theme is used, and libraries exist in the parent theme
		$tlen = strlen( $theme_path );
		$len  = strlen( $path );
		if ( $tlen >= $len || 0 !== strncmp( $theme_path . $path[ $tlen ], $path, $tlen + 1 ) ) {
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

function abs_url( string $base, string $rel ): string {
	if ( parse_url( $rel, PHP_URL_SCHEME ) != '' ) return $rel;
	$base = trailingslashit( $base );
	if ( $rel[0] === '#' || $rel[0] === '?' ) return $base . $rel;

	$pu = parse_url( $base );
	$scheme = isset( $pu['scheme'] ) ? $pu['scheme'] . '://' : '';
	$host   = isset( $pu['host'] )   ? $pu['host']           : '';
	$port   = isset( $pu['port'] )   ? ':' . $pu['port']     : '';
	$path   = isset( $pu['path'] )   ? $pu['path']           : '';

	$path = preg_replace( '#/[^/]*$#', '', $path );
	if ( $rel[0] === '/' ) $path = '';
	$abs = "$host$port$path/$rel";
	$re = [ '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' ];
	for ( $n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, -1, $n ) ) {}
	return $scheme . $abs;
}


// -----------------------------------------------------------------------------


function separate_line( $str ) {
	return array_map(
		function ( $s ) {
			return \st\separate_text_and_make_spans( $s );
		},
		preg_split( "/　　|<\s*br\s*\/?>/ui", $str )
	);
}

function separate_text_and_make_spans( $text ) {
	$parts = separate_text( $text );
	$ret = '';
	foreach ( $parts as $ws ) {
		$_w = esc_html( $ws[0] );
		$ret .= $ws[1] ? "<span>$_w</span>" : $_w;
	}
	return $ret;
}

function separate_text( $text ) {
	$PAIRS = ['S*' => 1, '*E' => 1, 'II' => 1, 'KK' => 1, 'HH' => 1, 'HI' => 1];
	$parts = [];
	$t_prev = '';
	$word = '';

	for ( $i = 0, $I = mb_strlen( $text ); $i < $I; $i += 1 ) {
		$c = mb_substr( $text, $i, 1 );
		$t = _get_ctype( $c );
		if ( isset( $PAIRS[ $t_prev . $t ] ) || isset( $PAIRS[ '*' . $t ] ) || isset( $PAIRS[ $t_prev . '*' ] ) ) {
			$word .= $c;
		} else if ( $t === 'O' ) {
			if ( $t_prev === 'O' ) {
				$word .= $c;
			} else {
				if ( ! empty( $word ) ) $parts[] = [ $word, true ];
				$word = $c;
			}
		} else {
			if ( ! empty( $word ) ) $parts[] = [ $word, ( $t_prev !== 'O' ) ];
			$word = $c;
		}
		$t_prev = $t;
	}
	if ( ! empty( $word ) ) $parts[] = [ $word, ( $t_prev !== 'O' ) ];
	return $parts;
}

function _get_ctype( $c ) {
	$CPATS = [
		'S' => '/[「『（［｛〈《【〔〖〘〚＜]/u',
		'E' => '/[」』）］｝〉》】〕〗〙〛＞、，。．？！を：]/u',
		'I' => '/[ぁ-んゝ]/u',
		'K' => '/[ァ-ヴーｱ-ﾝﾞｰ]/u',
		'H' => '/[一-龠々〆ヵヶ]/u',
	];
	foreach ( $CPATS as $t => $p ) {
		if ( preg_match( $p, $c ) === 1 ) return $t;
	}
	return 'O';
}
