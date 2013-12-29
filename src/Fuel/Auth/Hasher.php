<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Auth;

/**
 * Auth hasher class.
 *
 * This class provides the Auth package with several hashing mechanisms
 *
 * @package  Fuel\Auth
 *
 * @since  2.0.0
 */
class Hasher
{
	/* PBKDF2 Implementation (described in RFC 2898)
	 *
	 *  @param string p password
	 *  @param string s salt
	 *  @param int c iteration count (use 1000 or higher)
	 *  @param int kl derived key length
	 *  @param string a hash algorithm
	 *
	 *  @return string derived key
	 */
	public function pbkdf2( $p, $s, $c, $kl, $a = 'sha256' )
	{
		$hl = strlen(hash($a, null, true)); # Hash length
		$kb = ceil($kl / $hl);              # Key blocks to compute
		$dk = '';                           # Derived key

		# Create key
		for ( $block = 1; $block <= $kb; $block ++ )
		{
			# Initial hash for this block
			$ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);

			# Perform block iterations
			for ( $i = 1; $i < $c; $i ++ )
			{
				# XOR each iterate
				$ib ^= ($b = hash_hmac($a, $b, $p, true));
			}
			$dk .= $ib; # Append iterated block
		}

		# Return derived key of correct length
		return substr($dk, 0, $kl);
	}
}
