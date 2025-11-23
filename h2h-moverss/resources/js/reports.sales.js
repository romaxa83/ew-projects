import { exportButtons } from '@/reports-helpers/export-buttons';
import { filterByMonth } from '@/reports-helpers/filter-by-month';
import { getRankColor } from '@/reports-helpers/get-rank-color';
import { DateService } from '@/services/date';

const RANK_TYPES = ['SalesRank', 'EfficiencyRank'];
const RANK_COLORS_RECORD = '__rankColor';
const USER_FIELD_PREFIX = 'user_';

let DT = {};

$.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
	console.log(message);
};

$(function () {
	$('#managers').select2({
		placeholder: 'Anyone',
	});

	$('#filter-form').submit(function (e) {
		e.preventDefault();

		const $dtTable = $('#dt-table');
		const $managersControl = $('#managers');
		const $managersOptions = $managersControl.find('option');
		const selectedManagers = $managersControl.val();
		const hasNoSelectedManagers = selectedManagers.length === 0;
		const formFilter = getFilterPayload().filter;
		const selectedTeam = formFilter.sales_team;
		const hasSelectedTeam = !!selectedTeam;

		if ($.fn.dataTable.isDataTable($dtTable)) {
			$dtTable.DataTable().destroy();
			$dtTable.empty();
		}

		const cols = [
			{ data: 'id', title: 'ID', visible: false },
			{ data: 'title', title: 'Metric', className: 'metric-col' },
		];

		if (hasNoSelectedManagers) {
			cols.push({
				data: 'user_0',
				title: 'Without manager',
				className: 'col-manager',
			});
		}

		$managersOptions
			.filter(function () {
				const value = $(this).prop('value');
				if (hasNoSelectedManagers) {
					if (hasSelectedTeam) {
						const team = $(this).data('sales_team');
						return team === selectedTeam;
					}
					// then add all managers one by one
					return true;
				} else if (selectedManagers.includes(value)) {
					// then add only selected managers
					return true;
				}
				return false;
			})
			.each(function () {
				const value = $(this).prop('value');
				const userKey = USER_FIELD_PREFIX + value;
				cols.push({
					data: userKey,
					title: $(this).text(),
					className: 'col-manager',
					render(data, type, row, meta) {
						if (
							row[RANK_COLORS_RECORD] &&
							row[RANK_COLORS_RECORD][userKey]
						) {
							const color = row[RANK_COLORS_RECORD][userKey];
							return `<div class="table-cell-unfold" style="background: ${color}">${data}</div>`;
						}
						return data;
					},
				});
			});

		initDatatable(
			cols,
			selectedManagers.map((id) => USER_FIELD_PREFIX + id)
		);

		return false;
	});

	$('#show-cols').change(function () {
		analyzeCols($(this).val());
	});

	filterByMonth({
		controlElement: $('#daterangepicker'),
		dateElement: $('#filter-date'),
		minDate: new DateService(new Date(2020, 0)),
		maxDate: DateService.fromToday(),
		initialDate: DateService.fromToday(),
		withSelectingYears: true,
	});

	exportButtons({
		buttons: $('.js-export-button'),
		getFilterPayload,
	});
});

const analyzeCols = (val) => {
	console.log('Report sales, analyzeCols');
	if ($.fn.DataTable.isDataTable('#dt-table')) {
		const api = $('#dt-table').DataTable();
		api.columns().every(function (index) {
			if (!index) {
				this.visible(false);
				return true;
			}
			var data = this.data();
			// console.log('index', index)
			// console.log('data', this.data())
			if (val === 'all') {
				this.visible(true);
				return true;
			} else if ('hide-no-data') {
				// var hide = true;
				if (index > 1) {
					var displayStatus = this.data().reduce(function (
						acc,
						curr
					) {
						return acc + (curr == '' ? 0 : 1);
					},
					0);
					this.visible(displayStatus);
				}
			}
		});
	}
};

const initDatatable = (columns, selectedKeys) => {
	console.log('Report sales, initDatatable');
	DT = $('#dt-table').DataTable({
		processing: true,
		searching: false,
		ordering: false,
        deferRender: true,
        orderClasses: false,
		columns,
		// serverSide: true,
		columnDefs: [],
		ajax: function (data, callback, settings) {
			return $.ajax({
				type: 'POST',
				data: $.extend({}, data, getFilterPayload()),
				url: $('#dt-table').data('route'),
			}).done(function (data) {
				updateRankPositions(data.data, selectedKeys);
				callback(data);
				analyzeCols($('#show-cols').val());
			});
		},

		scrollX: true,
		scrollCollapse: true,
		paging: false,
		fixedColumns: {
			leftColumns: 2,
		},
		dom:
			"<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6'>>" +
			"<'row'<'col-sm-12'tr>>" +
			"<'row'<'col-sm-12 col-md-5'><'col-sm-12 col-md-7'>>",

		// lengthMenu: [25, 50, 100],
	});
};

/**
 * @returns {{
 *    filter: {
 *      "date": string,
 *      "period-type": string,
 *      "sales_team": string,
 *      "move_type": string
 *    }
 * }}
 */
function getFilterPayload() {
	return { filter: $('#filter-form').serializeFormObject() };
}

/**
 * Mutates records by adding rank colors to them
 * @param {Object[]} records
 * @param {string[]} selectedKeys
 */
function updateRankPositions(records, selectedKeys) {
	records
		.filter((record) => RANK_TYPES.includes(record.type))
		.forEach((record) => {
			const ranks = Object.entries(record)
				.filter(([key, value]) => {
					const hasValue =
						key.startsWith(USER_FIELD_PREFIX) &&
						typeof value === 'number';
					if (hasValue && selectedKeys.length) {
						return selectedKeys.includes(key);
					}
					return hasValue;
				})
				.map(([key, value]) => ({ key, value }))
				.sort((a, b) => a.value - b.value);

			// We need at least 2 ranks to compare them between each other
			if (ranks.length >= 2) {
				const min = ranks[0].value;
				const max = ranks[ranks.length - 1].value;
				record[RANK_COLORS_RECORD] = {};
				ranks.forEach((rank) => {
					const percentage = +(
						(rank.value - min) /
						(max - min)
					).toFixed(2);
					record[RANK_COLORS_RECORD][rank.key] =
						getRankColor(percentage);
				});
			}
		});
}
