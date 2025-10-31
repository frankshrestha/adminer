<?php

use function Adminer\{h, lang, is_url, is_blob, is_utf8, target_blank};

class AdminerDisableMailLink
{
	/** Value printed in select table
	 * @param ?string $val HTML-escaped value to print
	 * @param ?string $link link to foreign key
	 * @param Field $field
	 * @param string $original original value before applying editVal() and escaping
	 */
	function selectVal(?string $val, ?string $link, array $field, ?string $original): string
	{
		$return = ($val === null ? "<i>NULL</i>"
			: (preg_match("~char|binary|boolean~", $field["type"]) && !preg_match("~var~", $field["type"]) ? "<code>$val</code>"
				: (preg_match('~json~', $field["type"]) ? "<code class='jush-js'>$val</code>"
					: $val)
			));
		if (is_blob($field) && !is_utf8($val)) {
			$return = "<i>" . lang('%d byte(s)', strlen($original)) . "</i>";
		}

		if (preg_match('~^mailto~', $link)) return "<a>$return</a>";

		return ($link ? "<a href='" . h($link) . "'" . (is_url($link) ? target_blank() : "") . ">$return</a>" : $return);
	}
}
