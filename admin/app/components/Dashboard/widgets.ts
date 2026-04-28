import { markRaw, type Component } from 'vue';
import {
	AcademicCapIcon,
	ArchiveBoxIcon,
	AtSymbolIcon,
	CalendarDaysIcon,
	DocumentIcon,
	KeyIcon,
	NewspaperIcon,
	PhoneIcon,
	QuestionMarkCircleIcon,
	RocketLaunchIcon,
	UsersIcon,
} from '@heroicons/vue/24/outline';
import DashboardWidgetContentList from './Widget/ContentList.vue';
import DashboardWidgetChangelog from './Widget/Changelog.vue';

export type WidgetSize = 'half' | 'full';

export interface WidgetDefinition {
	key: string;
	title: string;
	icon: unknown;
	component: Component;
	props?: Record<string, unknown>;
	permissionSlug?: string;
	defaultPosition: number;
	defaultSize: WidgetSize;
	defaultEnabled: boolean;
}

export interface WidgetConfig {
	widget_key: string;
	position: number;
	size: WidgetSize;
	enabled: boolean;
}

export const availableWidgets: WidgetDefinition[] = [
	{
		key: 'posts',
		title: 'Články',
		icon: ArchiveBoxIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/post',
			link: '/obsah/clanky',
			color: 'indigo',
			emptyLabel: 'Zatím žádné články',
			columns: [
				{ key: 'id', name: 'ID', type: 'text', width: 50 },
				{ key: 'name', name: 'Název', type: 'text' },
				{ key: 'status', name: 'Stav', type: 'enum', hidden: true },
				{ key: 'active', name: 'Aktivní', type: 'status', hidden: true },
			],
			enums: {
				status: {
					draft: 'Koncept',
					published: 'Publikováno',
					archived: 'Archivováno',
				},
			},
		},
		permissionSlug: 'posts',
		defaultPosition: 1,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'novelties',
		title: 'Novinky',
		icon: NewspaperIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/novelty',
			link: '/obsah/novinky',
			color: 'emerald',
			emptyLabel: 'Zatím žádné novinky',
			columns: [
				{ key: 'id', name: 'ID', type: 'text', width: 50 },
				{ key: 'name', name: 'Název', type: 'text' },
				{ key: 'priority', name: 'Priorita', type: 'enum', hidden: true },
				{ key: 'active', name: 'Aktivní', type: 'status', hidden: true },
			],
			enums: {
				priority: { 1: 'Vysoká', 2: 'Normální', 3: 'Nízká' },
			},
		},
		permissionSlug: 'novelties',
		defaultPosition: 2,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'events',
		title: 'Události',
		icon: CalendarDaysIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/event',
			link: '/obsah/udalosti',
			color: 'amber',
			emptyLabel: 'Zatím žádné události',
			columns: [
				{ key: 'id', name: 'ID', type: 'text', width: 50 },
				{ key: 'name', name: 'Název', type: 'text' },
				{ key: 'code', name: 'Kód', type: 'text' },
				{ key: 'status', name: 'Stav', type: 'enum', hidden: true },
				{ key: 'start_date', name: 'Od', type: 'datetime', hidden: true },
				{ key: 'end_date', name: 'Do', type: 'datetime', hidden: true },
			],
			enums: {
				status: {
					draft: 'Koncept',
					published: 'Publikováno',
					archived: 'Archivováno',
				},
			},
		},
		permissionSlug: 'events',
		defaultPosition: 3,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'pages',
		title: 'Informační stránky',
		icon: DocumentIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/page',
			link: '/obsah/stranky',
			color: 'sky',
			emptyLabel: 'Zatím žádné stránky',
			columns: [
				{ key: 'id', name: 'ID', type: 'text', width: 50 },
				{ key: 'name', name: 'Název', type: 'text' },
				{ key: 'active', name: 'Aktivní', type: 'status', hidden: true },
			],
		},
		permissionSlug: 'pages',
		defaultPosition: 4,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'careers',
		title: 'Pracovní pozice',
		icon: AcademicCapIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/career',
			link: '/obsah/pracovni-pozice',
			color: 'rose',
			emptyLabel: 'Zatím žádné pracovní pozice',
			columns: [
				{ key: 'id', name: 'ID', type: 'text', width: 50 },
				{ key: 'name', name: 'Název', type: 'text' },
				{ key: 'code', name: 'Kód', type: 'text' },
				{ key: 'active', name: 'Aktivní', type: 'status', hidden: true },
				{ key: 'application_count', name: 'Žadatelů', type: 'number', hidden: true },
			],
		},
		permissionSlug: 'careers',
		defaultPosition: 5,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'restaurant_reservations',
		title: 'Rezervace stolů',
		icon: CalendarDaysIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/food/reservation',
			link: '/restaurace/rezervace',
			color: 'orange',
			emptyLabel: 'Zatím žádné rezervace',
			dateField: 'created_at',
			columns: [
				{ key: 'date', name: 'Datum', type: 'date' },
				{ key: 'time_from', name: 'Čas', type: 'text' },
				{ key: 'guest_full_name', name: 'Host', type: 'text' },
				{ key: 'guests_count', name: 'Hostů', type: 'number', hidden: true },
				{ key: 'table_number', name: 'Stůl', type: 'text', hidden: true },
				{
					key: 'status',
					name: 'Stav',
					type: 'mapped',
					map: {
						pending: { label: 'Čeká', class: 'bg-amber-100 text-amber-700' },
						confirmed: { label: 'Potvrzeno', class: 'bg-blue-100 text-blue-700' },
						seated: { label: 'Usazení', class: 'bg-emerald-100 text-emerald-700' },
						completed: { label: 'Dokončeno', class: 'bg-slate-100 text-slate-600' },
						cancelled: { label: 'Zrušeno', class: 'bg-red-100 text-red-700' },
						no_show: { label: 'Nedorazili', class: 'bg-red-50 text-red-500' },
					},
				},
			],
		},
		permissionSlug: 'reservations',
		defaultPosition: 6,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'apartment_reservations',
		title: 'Rezervace apartmánů',
		icon: KeyIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/apartment/reservation',
			link: '/ubytovani/rezervace',
			color: 'teal',
			emptyLabel: 'Zatím žádné rezervace',
			dateField: 'created_at',
			columns: [
				{ key: 'code', name: 'Kód', type: 'text' },
				{ key: 'apartment.name', name: 'Apartmán', type: 'text', hidden: true },
				{ key: 'guest_lastname', name: 'Host', type: 'text' },
				{ key: 'start_date', name: 'Od', type: 'date' },
				{ key: 'end_date', name: 'Do', type: 'date', hidden: true },
				{ key: 'total_price', name: 'Cena', type: 'number', hidden: true },
			],
		},
		permissionSlug: 'apartment_reservations',
		defaultPosition: 7,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'newsletters',
		title: 'Odběry newsletteru',
		icon: AtSymbolIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/newsletter',
			link: '/uzivatele/newslettery',
			color: 'pink',
			emptyLabel: 'Zatím žádné odběry',
			dateField: 'created_at',
			actions: [],
			columns: [
				{ key: 'id', name: 'ID', type: 'text', width: 50 },
				{ key: 'fullname', name: 'Jméno', type: 'text' },
				{ key: 'email', name: 'E-mail', type: 'text' },
				{ key: 'locale', name: 'Jazyk', type: 'text', hidden: true },
			],
		},
		permissionSlug: 'newsletters',
		defaultPosition: 8,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'demands',
		title: 'Poptávky',
		icon: QuestionMarkCircleIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/demand',
			link: '/uzivatele/poptavky',
			color: 'violet',
			emptyLabel: 'Zatím žádné poptávky',
			dateField: 'created_at',
			columns: [
				{ key: 'fullname', name: 'Jméno', type: 'text' },
				{ key: 'email', name: 'E-mail', type: 'text', hidden: true },
				{ key: 'phone', name: 'Telefon', type: 'text', hidden: true },
				{ key: 'service_name', name: 'Služba', type: 'text' },
				{ key: 'offer_price', name: 'Cena', type: 'number', hidden: true },
			],
		},
		permissionSlug: 'demands',
		defaultPosition: 9,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'contacts_to_call',
		title: 'Dnes kontaktovat',
		icon: PhoneIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/dashboard/contact',
			link: '/kontakty/hovory-schuzky',
			color: 'amber',
			emptyLabel: 'Dnes nemáte žádné hovory',
			paginate: false,
			dataKey: 'contactsToCall',
			columns: [
				{ key: 'firstname', name: 'Jméno', type: 'text' },
				{ key: 'lastname', name: 'Příjmení', type: 'text' },
				{ key: 'phone', name: 'Telefon', type: 'text' },
			],
			actions: [{ type: 'edit', path: '/kontakty', hash: '#proces' }],
		},
		permissionSlug: 'contacts',
		defaultPosition: 10,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'coming_meetings',
		title: 'Nadcházející schůzky',
		icon: UsersIcon,
		component: markRaw(DashboardWidgetContentList),
		props: {
			endpoint: '/api/admin/dashboard/contact',
			link: '/kontakty/hovory-schuzky',
			color: 'indigo',
			emptyLabel: 'Žádné naplánované schůzky',
			paginate: false,
			dataKey: 'comingEvents',
			columns: [
				{ key: 'firstname', name: 'Jméno', type: 'text' },
				{ key: 'lastname', name: 'Příjmení', type: 'text' },
				{ key: 'next_meeting', name: 'Termín', type: 'datetime' },
			],
			actions: [{ type: 'edit', path: '/kontakty', hash: '#proces' }],
		},
		permissionSlug: 'contacts',
		defaultPosition: 11,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'changelog',
		title: 'Changelog',
		icon: RocketLaunchIcon,
		component: markRaw(DashboardWidgetChangelog),
		defaultPosition: 12,
		defaultSize: 'half',
		defaultEnabled: true,
	},
];

export const defaultConfig = (): WidgetConfig[] =>
	availableWidgets.map((w) => ({
		widget_key: w.key,
		position: w.defaultPosition,
		size: w.defaultSize,
		enabled: w.defaultEnabled,
	}));

export const mergeConfig = (saved: WidgetConfig[]): WidgetConfig[] => {
	const byKey = new Map<string, WidgetConfig>();
	for (const w of availableWidgets) {
		byKey.set(w.key, {
			widget_key: w.key,
			position: w.defaultPosition,
			size: w.defaultSize,
			enabled: w.defaultEnabled,
		});
	}
	for (const s of saved) {
		if (byKey.has(s.widget_key)) {
			byKey.set(s.widget_key, { ...s });
		}
	}
	return Array.from(byKey.values()).sort((a, b) => a.position - b.position);
};

export const colorClasses: Record<string, { bg: string; text: string; count: string }> = {
	indigo: { bg: 'bg-indigo-50', text: 'text-indigo-600', count: 'text-indigo-600' },
	emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', count: 'text-emerald-600' },
	amber: { bg: 'bg-amber-50', text: 'text-amber-600', count: 'text-amber-600' },
	sky: { bg: 'bg-sky-50', text: 'text-sky-600', count: 'text-sky-600' },
	slate: { bg: 'bg-slate-100', text: 'text-slate-600', count: 'text-slate-600' },
	rose: { bg: 'bg-rose-50', text: 'text-rose-600', count: 'text-rose-600' },
	orange: { bg: 'bg-orange-50', text: 'text-orange-600', count: 'text-orange-600' },
	teal: { bg: 'bg-teal-50', text: 'text-teal-600', count: 'text-teal-600' },
	pink: { bg: 'bg-pink-50', text: 'text-pink-600', count: 'text-pink-600' },
	violet: { bg: 'bg-violet-50', text: 'text-violet-600', count: 'text-violet-600' },
};
