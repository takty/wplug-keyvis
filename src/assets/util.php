<?php
/**
 * Utilities
 *
 * @package Wplug Keyvis
 * @author Takuto Yanagida
 * @version 2022-02-21
 */

namespace wplug\keyvis;

/**
 * Separates a line.
 *
 * @param string $str A string.
 * @return array An array of strings.
 */
function separate_line( string $str ): array {
	return array_map(
		function ( $s ) {
			return separate_text_and_make_spans( $s );
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
 * @access private
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
