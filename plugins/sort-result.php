<?php

use const Adminer\ME;

use function Adminer\{h, bold, lang, adminer, is_view, support, checkbox, idf_escape, select_input, print_fieldset};

class AdminerSortResult
{
	public function __construct(
		private string $sortKey = 'primary_key',
		private bool $desc      = true,
	) {}

	function replacePrimaryKey(string &$val, array $indexes): void
	{
		if ('primary_key' != $val) return;

		$val = $indexes['PRIMARY']['columns'][0] ?? '';
	}

	/** Print order box in select
	 * @param list<string> $order result of selectOrderProcess()
	 * @param string[] $columns selectable columns
	 * @param Index[] $indexes
	 */
	function selectOrderPrint(array $order, array $columns, array $indexes): bool
	{
		print_fieldset("sort", lang('Sort'), $order);
		$i = 0;
		foreach ((array) $_GET["order"] as $key => $val) {

			$this->replacePrimaryKey($val, $indexes);

			if ($val != "") {
				echo "<div>" . select_input(" name='order[$i]'", $columns, $val, "selectFieldChange");
				echo checkbox("desc[$i]", 1, isset($_GET["desc"][$key]), lang('descending')) . "</div>\n";
				$i++;
			}
		}
		echo "<div>" . select_input(" name='order[$i]'", $columns, "", "selectAddRow");
		echo checkbox("desc[$i]", 1, false, lang('descending')) . "</div>\n";
		echo "</div></fieldset>\n";

		return true;
	}

	/** Process order box in select
	 * @param Field[] $fields
	 * @param Index[] $indexes
	 * @return list<string> expressions to join by comma
	 */
	function selectOrderProcess(array $fields, array $indexes): array
	{
		$return = array();
		foreach ((array) $_GET["order"] as $key => $val) {

			$this->replacePrimaryKey($val, $indexes);

			if ($val != "") {
				$return[] = (preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~', $val) ? $val : idf_escape($val)) //! MS SQL uses []
					. (isset($_GET["desc"][$key]) ? " DESC" : "");
			}
		}
		return $return;
	}

	/** Print table list in menu
	 * @param TableStatus[] $tables
	 */
	function tablesPrint(array $tables): bool
	{
		echo "<ul id='tables'>";
		foreach ($tables as $table => $status) {
			$table = "$table"; // do not highlight "0" as active everywhere
			$name = adminer()->tableName($status);
			if ($name != "" && !$status["partition"]) {
				echo '<li><a href="' . h(ME) . 'select=' . urlencode($table)
					. '&order[0]=' . $this->sortKey
					. ($this->desc ? '&desc[0]=1' : '') . '"'
					. bold($_GET["select"] == $table || $_GET["edit"] == $table, "select")
					. " title='" . lang('Select data') . "'>" . lang('select') . "</a> ";
				echo (support("table") || support("indexes")
					? '<a href="' . h(ME) . 'table=' . urlencode($table) . '"'
					. bold(in_array($table, array($_GET["table"], $_GET["create"], $_GET["indexes"], $_GET["foreign"], $_GET["trigger"], $_GET["check"], $_GET["view"])), (is_view($status) ? "view" : "structure"))
					. " title='" . lang('Show structure') . "'>$name</a>"
					: "<span>$name</span>"
				) . "\n";
			}
		}
		echo "</ul>\n";

		return true;
	}
}
