<?php
/**
 * @package    Fuel\Auth
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
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
	/**
	 * Generate a very random salt
	 *
	 * @param  int  $length  required length of the salt string
	 *
	 * @return  string  generated random salt
	 *
	 * @since 2.0.0
	 */
	public function salt($length)
	{
		// use the openssl randomizer if available
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$salt = base64_encode(openssl_random_pseudo_bytes($length));
		}

		// if not, use mycrypts urandom generator
		elseif (function_exists('mcrypt_create_iv'))
		{
			$salt = base64_encode(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
		}

		// if not, use hash with sha512
		elseif (function_exists('hash') and in_array('sha512', hash_algos()))
		{
			$salt = '';
			while (strlen($salt) < $length+24)
			{
				$salt .= base64_encode(hash('sha512', microtime(true), true));
			}
		}

		// if all else fails, use sha1
		else
		{
			$salt = '';
			while (strlen($salt) < $length+24)
			{
				$salt .= base64_encode(sha1(microtime(true), true));
			}
		}

		// remove unwanted characters, cut it to length, and return it
		return substr(str_replace(array('/','+','='), '', $salt), 0, $length);
	}

	/**
	 * PHP native crypt() Implementation
	 *
	 * @param  string  $p  password
	 * @param  string  $s  salt
	 *
	 * @return  string  derived key
	 *
	 * @since 2.0.0
	 */
	public function crypt($p, $s)
	{
		return crypt($p, $s);
	}

	/**
	 * bcrypt() implementation using PHP's native crypt()
	 *
	 * @param  string  $p  password
	 * @param  string  $s  salt
	 *
	 * @return  string  derived key
	 *
	 * @since 2.0.0
	 */
	public function bcrypt($p, $s)
	{
		// make sure the salt is prefixed correctly
		if (strpos($s, '$2y$12$') !== 0)
		{
			$s = '$2y$12$'.$s;
		}

		if (strlen($s) !== 29)
		{
			throw new AuthException('A bcrypt() salt needs to be a 22-character string, excluding the prefix!');
		}

		return crypt($p, $s);
	}


	/**
	 * PBKDF2 Implementation (described in RFC 2898)
	 *
	 * @param string p password
	 * @param string s salt
	 * @param int c iteration count (use 1000 or higher)
	 * @param int kl derived key length
	 * @param string a hash algorithm
	 *
	 * @return string derived key
	 *
	 * @since 2.0.0
	 */
	public function pbkdf2($p, $s, $c, $kl, $a = 'sha256' )
	{
		// use the build-in function if possible
		if (function_exists('hash_pbkdf2'))
		{
			return hash_pbkdf2($a, $p, $s, $c, $kl, true);
		}
		else
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
}
