/**
 * BS Overhead Toggles admin behaviour (tabs, switches, disclosure).
 */
(function () {
	'use strict';

	var root = document.querySelector('.bsot-app');
	if (!root) {
		return;
	}

	var statusOn = root.getAttribute('data-i18n-on');
	var statusOff = root.getAttribute('data-i18n-off');

	function setTab(slug) {
		root.querySelectorAll('[data-bsot-tab]').forEach(function (tab) {
			var selected = tab.getAttribute('data-bsot-tab') === slug;
			tab.setAttribute('aria-selected', selected ? 'true' : 'false');
			tab.tabIndex = selected ? 0 : -1;
		});
		root.querySelectorAll('[data-bsot-panel]').forEach(function (panel) {
			panel.hidden = panel.getAttribute('data-bsot-panel') !== slug;
		});
	}

	var tabs = root.querySelectorAll('[data-bsot-tab]');
	tabs.forEach(function (tab, index) {
		tab.tabIndex = tab.getAttribute('aria-selected') === 'true' ? 0 : -1;
		tab.addEventListener('click', function () {
			setTab(tab.getAttribute('data-bsot-tab'));
		});
		tab.addEventListener('keydown', function (event) {
			var next = index;
			if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
				next = (index + 1) % tabs.length;
			} else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
				next = (index - 1 + tabs.length) % tabs.length;
			} else if (event.key === 'Home') {
				next = 0;
			} else if (event.key === 'End') {
				next = tabs.length - 1;
			} else {
				return;
			}
			event.preventDefault();
			tabs[next].focus();
			setTab(tabs[next].getAttribute('data-bsot-tab'));
		});
	});

	root.querySelectorAll('[data-bsot-more]').forEach(function (button) {
		button.addEventListener('click', function () {
			var panelId = button.getAttribute('aria-controls');
			var panel = panelId ? document.getElementById(panelId) : null;
			if (!panel) {
				return;
			}
			var open = button.getAttribute('aria-expanded') === 'true';
			button.setAttribute('aria-expanded', open ? 'false' : 'true');
			panel.hidden = open;
		});
	});

	root.querySelectorAll('.bsot-toggle-label').forEach(function (label) {
		label.addEventListener('click', function (event) {
			var targetId = label.getAttribute('for');
			var sw = targetId ? document.getElementById(targetId) : null;
			if (!sw || sw.disabled) {
				return;
			}
			event.preventDefault();
			sw.click();
		});
	});

	root.querySelectorAll('[data-bsot-switch]').forEach(function (button) {
		button.addEventListener('click', function () {
			if (button.disabled) {
				return;
			}
			var row = button.closest('.bsot-toggle');
			var input = row ? row.querySelector('[data-bsot-switch-value]') : null;
			var status = row ? row.querySelector('[data-bsot-status]') : null;
			var on = button.getAttribute('aria-checked') !== 'true';
			button.setAttribute('aria-checked', on ? 'true' : 'false');
			if (input) {
				input.value = on ? '1' : '0';
			}
			if (status) {
				status.textContent = on ? (statusOn || 'Aktiv') : (statusOff || 'Inaktiv');
				status.classList.toggle('is-on', on);
				status.classList.toggle('is-off', !on);
			}
			var extra = row ? row.querySelector('[data-bsot-extra]') : null;
			if (extra) {
				extra.hidden = !on;
			}
		});
	});

	root.querySelectorAll('[data-bsot-confirm]').forEach(function (button) {
		button.addEventListener('click', function (event) {
			var message = button.getAttribute('data-bsot-confirm');
			if (message && !window.confirm(message)) {
				event.preventDefault();
			}
		});
	});
})();
