<?php

/** Filter names in tables list
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, https://www.vrana.cz/
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class AdminerTablesFilter extends Adminer\Plugin {
	function tablesPrint($tables) {
		?>
<script<?php echo Adminer\nonce(); ?>>
let tablesFilterTimeout = null;
let tablesFilterValue = '';

function tablesFilter() {
	const value = qs('#filter-field').value.toLowerCase();
	if (value == tablesFilterValue) {
		return;
	}
	tablesFilterValue = value;
	let reg;
	if (value != '') {
		reg = new RegExp('(' + value + ')', 'gi');
	}
	if (sessionStorage) {
		sessionStorage.setItem('adminer_tables_filter', value);
	}
	for (const table of qsa('li', qs('#tables'))) {
		let a = null;
		let text = table.getAttribute('data-table-name');
		if (text == null) {
			a = qsa('a', table)[1];
			text = a.innerHTML.trim();

			table.setAttribute('data-table-name', text);
			a.setAttribute('data-link', 'main');
		} else {
			a = qs('a[data-link="main"]', table);
		}
		updateSelectURL(table, value);
		if (value == '') {
			table.className = '';
			a.innerHTML = text;
		} else {
			table.className = text.match(reg) ? '' : 'hidden';
			a.innerHTML = text.replace(reg, '<strong>$1</strong>');
		}
	}
	updateTableCount();
}

function updateSelectURL(table, value) {
	const a = table.firstChild;
	const url = new URL(a.href);

	url.searchParams.set('filter', value);
	a.href = url.search;
}

function tablesFilterInput() {
	window.clearTimeout(tablesFilterTimeout);
	tablesFilterTimeout = window.setTimeout(tablesFilter, 200);
}

function updateTableCount() {
	qs('#table-count').innerText = qsa('li:not(.hidden)', qs('#tables')).length;
}

sessionStorage && document.addEventListener('DOMContentLoaded', () => {
	let db = qs('#dbs').querySelector('select');
	db = db.options[db.selectedIndex].text;
	if (db == sessionStorage.getItem('adminer_tables_filter_db') && sessionStorage.getItem('adminer_tables_filter')){
		qs('#filter-field').value = sessionStorage.getItem('adminer_tables_filter');
		tablesFilter();
	}
	sessionStorage.setItem('adminer_tables_filter_db', db);
	updateTableCount();
});
</script>
<p class="jsonly">
	<span style="display: flex; gap: 8px;">
		<input id="filter-field" autocomplete="off" accesskey="F" type="search" style="flex-grow: 1;">
		<span id="table-count" style="width: 30px;">
	</span>
<?php echo Adminer\script("qs('#filter-field').oninput = tablesFilterInput;"); ?>
<?php
	}

	protected $translations = array(
		'cs' => array(
			'' => 'Filtruje názvy v seznamu tabulek',
			'Filter' => 'Filtr',
		),
		'de' => array(
			'' => 'Filtern Sie Namen in der Tabellenliste',
		),
		'pl' => array(
			'' => 'Filtruj nazwy na liście tabel',
		),
		'ro' => array(
			'' => 'Nume de filtre în lista de tabele',
		),
		'ja' => array(
			'' => 'テーブル一覧をテーブル名でフィルタリング',
		),
	);
}
