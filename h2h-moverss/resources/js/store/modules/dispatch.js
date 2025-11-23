import {
	DEFAULT_ALL,
	DEFAULT_SORT,
	getLogsApiResult,
	refetchChangelog,
} from '@/services/changelog';
import cloneDeep from 'lodash.clonedeep'; // initial state

// initial state
const state = () => ({
	hiddenWorks: 5,
	loading: new $.Deferred(),
	isLoaded: false,
	dispatchDay: null,
	changed: false, // Есть измененные данные
	works: {}, // Все работы
	trucks: {},
	virtual: {
		// Виртуальные задачи
		trucks: [],
		crews: [],
	},
	types: [],
	unique_work_types: [],
	changelog: {
		data: [],
		api: '/dispatch/logs',
		hasMore: false,
		sort: DEFAULT_SORT,
		all: DEFAULT_ALL,
		refetching: false,
		staticRequestParams: {},
	},
});

// getters
const getters = {
	isChanged: (state) => state.changed,
	getWorks: (state) => state.works,
	getTrucks: (state) => state.trucks,
	getActiveTrucks: (state) =>
		Object.values(state.trucks)
			.filter((item) => item.active)
			.sort((a, b) => a.title.localeCompare(b.title)),

	getWorksToSave: (state) => {
		console.log('getWorksToSave', state.works);
		if (state.works && Object.keys(state.works).length)
			return Object.filter(state.works, (item) => {
				return item.start_date == state.dispatchDay;
			});
		return state.works;
	},

	getUniqueWorksTypes: (state) => {
		let w_types = [];
		state.unique_work_types.forEach(function (k) {
			w_types.push({
				id: k,
				title: state.types['works'][k].title,
			});
		});

		return w_types;
	},

	virtualWorks: (state) => (type) => state.virtual[type],
	getTypes: (state) => (type) => state.types[type],
};

// mutations
const mutations = {
	setDispatchDay(state, payload) {
		state.dispatchDay = payload;
	},
	setChanged(state, status) {
		state.changed = status ? true : false;
	},

	setTypeRecords(state, { type, records }) {
		state.types[type] = records;
	},

	setTrucks(state, records) {
		state.trucks = records;
	},

	// Заливаем данные в хранилище + генерим виртуальные работы
	setWorks(state, payload) {
		let unique_work_types = [];

		Object.values(payload).forEach(function (item) {
			let work_types_keys = []; // Все существующие типы работ
			// Уникальные типы работ
			item.work_types.forEach(function (w_type) {
				work_types_keys.push(w_type.work_type_id);
				if (!unique_work_types.includes(w_type.work_type_id))
					unique_work_types.push(w_type.work_type_id);
			});
			item.work_types_keys = work_types_keys;

			// Добавляем randomRef
			let trucks_ids = [];
			if (item.dispatch_trucks.length) {
				item.dispatch_trucks.forEach(function (truck) {
					truck.randomRef = App.Miscs.generateToken();
					trucks_ids.push(truck.truck_id);
				});
			}
			item.dispatch_trucks_ids = trucks_ids;

			let employees_ids = [];
			if (item.dispatch_employees.length) {
				item.dispatch_employees.forEach(function (employee) {
					employee.randomRef = App.Miscs.generateToken();
					employees_ids.push(employee.employer_id);
				});
			}
			item.dispatch_employees_ids = employees_ids;
		});
		state.works = payload;
		state.unique_work_types = unique_work_types;

		let works_trucks = Object.values(cloneDeep(payload)).filter(function (
			item
		) {
			return item.trucks; // Фильтруем у кого нет траков
		});

		// Размножить на число траков
		works_trucks.forEach(function (item) {
			// Добавляем виртуальные траки
			for (let i = 0; i < item.trucks; i++) {
				let scheduled = !!(
					item.dispatch_trucks[i] && item.dispatch_trucks[i].id
				);
				let truck_id = item.dispatch_trucks[i]
					? item.dispatch_trucks[i].truck_id
					: null;
				let randomRef = item.dispatch_trucks[i]
					? item.dispatch_trucks[i].randomRef
					: App.Miscs.generateToken();

				let record = {
					position: i + 1,
					randomRef,
					truck_id,
					scheduled,
					...item,
				};
				state.virtual['trucks'].push(record);
			}
		});

		let works_crews = Object.values(cloneDeep(payload)).filter(function (
			item
		) {
			return item.employees; // Фильтруем у кого нет сотрудников
		});

		// Размножить на число Сотрудников
		works_crews.forEach(function (item) {
			// Добавляем виртуальные траки
			for (let i = 0; i < item.employees + state.hiddenWorks; i++) {
				let scheduled = !!(
					item.dispatch_employees[i] && item.dispatch_employees[i].id
				);
				let employee_id = item.dispatch_employees[i]
					? item.dispatch_employees[i].employer_id
					: null;
				let miscs = item.dispatch_employees[i]
					? item.dispatch_employees[i].miscs
					: null;
				let randomRef = item.dispatch_employees[i]
					? item.dispatch_employees[i].randomRef
					: App.Miscs.generateToken();

				let record = {
					position: i + 1,
					randomRef,
					employee_id,
					miscs,
					scheduled,
					...item,
				};
				state.virtual['crews'].push(record);
			}
		});
		state.isLoaded = true;
		state.loading.resolve(true);
	},

	updateEntityIds(state) {
		Object.entries(state.works).forEach(([key, work]) => {
			const truckIds = work.dispatch_trucks.map(
				(truck) => truck.truck_id
			);
			const employeeIds = work.dispatch_employees.map(
				(employee) => employee.employer_id
			);
			state.works[key].dispatch_trucks_ids = truckIds;
			state.works[key].dispatch_employees_ids = employeeIds;
		});
	},

	// Обновляем основную запись работ
	updateMainWork(state, { section, work_id, entity_id, randomRef }) {
		console.log('updateMainWork', {
			section,
			work_id,
			entity_id,
			randomRef,
		});
		// Обновить подвязку entity_id к работе
		Object.values(state.works).forEach(function (item) {
			if (item.id === work_id) {
				// Пытаемся обойти диспатчи
				let create_new = true,
					storage,
					entity_key;

				if (section === 'trucks') {
					storage = 'dispatch_trucks';
					entity_key = 'truck_id';
				} else if (section === 'crews') {
					storage = 'dispatch_employees';
					entity_key = 'employer_id';
				}

				if (Object.keys(item[storage]).length > 0) {
					item[storage].forEach(function (record, i) {
						// Попытка найти привязку трака в существующих подвязках
						if (record.randomRef === randomRef) {
							create_new = false;
							if (entity_id) {
								// Переназначаем трак
								record[entity_key] = entity_id;
							} else {
								// Удаляем подвязку
								item[storage].splice(i, 1);
							}
						}
					});
				}

				// Создаем работу, если мы не нашли ее в списке работ
				if (create_new) {
					item[storage].push({
						work_id: work_id,
						[entity_key]: entity_id,
						randomRef,
					});
				}
			}
		});
	},

	// Обновляем виртуальные работы
	updateVirtualWork(state, { section, work_id, entity_id, randomRef }) {
		state.virtual[section].forEach(function (item) {
			if (item.id === work_id && item.randomRef === randomRef) {
				if (section === 'trucks') {
					item.truck_id = entity_id;
				} else if (section === 'crews') {
					item.employer_id = entity_id;
				}
			}
		});
	},

	setChangelogStaticRequestParams(state, payload) {
		state.changelog.staticRequestParams = payload;
	},

	setChangelog(
		state,
		{ data, hasMore, sort = DEFAULT_SORT, all = DEFAULT_ALL }
	) {
		state.changelog.data = data;
		state.changelog.hasMore = hasMore;
		state.changelog.sort = sort;
		state.changelog.all = all;
	},

	setChangelogRefetching(state, value) {
		state.changelog.refetching = value;
	},
};

// actions
const actions = {
	initDispatchWorks({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.post('/dispatch/info', {
					start_date: payload.currentDate,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						// this.updated_at = resp.data.updated_at;
						commit('setChangelogStaticRequestParams', {
							start_date: payload.currentDate,
						});
						commit(
							'setChangelog',
							getLogsApiResult(resp.data.logs)
						);
						commit('setWorks', resp.data.works);
						commit('setTrucks', resp.data.trucks);
						commit('setTypeRecords', {
							type: 'works',
							records: resp.data.types.works,
						});
						resolve(resp.data);
					} else {
						reject(resp.data);
					}
				})
				.catch((error) => {
					reject(error.response?.data);
				});
		});
	},
	isWorksLoaded: ({ state }) => {
		return state.loading.promise().then(() => true);
	},

	refetchChangelog({ state, commit }, payload) {
		return refetchChangelog({
			payload,
			current: state.changelog,
			onStart: () => commit('setChangelogRefetching', true),
			onEnd: () => commit('setChangelogRefetching', false),
			onSuccess: (logs) => commit('setChangelog', logs),
		});
	},

	updateVirtualWorkTruck: ({ commit, state }, payload) => {
		payload = { section: 'trucks', ...payload };

		commit('updateVirtualWork', payload);
		commit('updateMainWork', payload);
		commit('setChanged', true);
	},

	updateVirtualWorkCrew: ({ commit, state }, payload) => {
		payload = { section: 'crews', ...payload };

		commit('updateVirtualWork', payload);
		commit('updateMainWork', payload);
		commit('setChanged', true);
	},
};

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations,
};
