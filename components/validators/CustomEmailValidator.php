<?php

namespace app\components\validators;

use yii\validators\EmailValidator;

class CustomEmailValidator extends EmailValidator
{
	public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[А-Яа-яa-zA-Z0-9](?:[А-Яа-яa-zA-Z0-9-]*[А-Яа-яa-zA-Z0-9])?\.)+[А-Яа-яa-zA-Z0-9](?:[А-Яа-яa-zA-Z0-9-]*[А-Яа-яa-zA-Z0-9])?$/';

	protected function validateValue($value)
	{
		if (!is_string($value)) {
			$valid = false;
		} elseif (!preg_match('/^(?P<name>(?:"?([^"]*)"?\s)?)(?:\s+)?(?:(?P<open><?)((?P<local>.+)@(?P<domain>[^>]+))(?P<close>>?))$/i', $value, $matches)) {
			$valid = false;
		} else {
			if ($this->enableIDN) {
				$matches['local'] = idn_to_ascii($matches['local'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
				$matches['domain'] = idn_to_ascii($matches['domain'],  IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
				$value = $matches['name'] . $matches['open'] . $matches['local'] . '@' . $matches['domain'] . $matches['close'];
			}
			if (strlen($matches['local']) > 64) {
				// The maximum total length of a user name or other local-part is 64 octets. RFC 5322 section 4.5.3.1.1
				// http://tools.ietf.org/html/rfc5321#section-4.5.3.1.1
				$valid = false;
			}elseif(strlen($matches['domain']) > 64){
				$valid = false;
			} elseif (strlen($matches['local'] . '@' . $matches['domain']) > 254) {
				// There is a restriction in RFC 2821 on the length of an address in MAIL and RCPT commands
				// of 254 characters. Since addresses that do not fit in those fields are not normally useful, the
				// upper limit on address lengths should normally be considered to be 254.
				//
				// Dominic Sayers, RFC 3696 erratum 1690
				// http://www.rfc-editor.org/errata_search.php?eid=1690
				$valid = false;
			} else {
				//Added u modificator for pattern
				$valid = preg_match($this->pattern .'u', $value) || $this->allowName && preg_match($this->fullPattern . 'u', $value);
				if ($valid && $this->checkDNS) {
					$valid = checkdnsrr($matches['domain'], 'MX') || checkdnsrr($matches['domain'], 'A');
				}
			}
		}
		return $valid ? null : [$this->message, []];
	}
}