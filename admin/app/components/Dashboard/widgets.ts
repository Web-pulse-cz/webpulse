import {
	ArchiveBoxIcon,
	CalendarDaysIcon,
	DocumentIcon,
	NewspaperIcon,
	RocketLaunchIcon,
} from '@heroicons/vue/24/outline';

export type WidgetSize = 'half' | 'full';

export interface WidgetDefinition {
	key: string;
	title: string;
	icon: unknown;
	component: string;
	props?: Record<string, unknown>;
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
		component: 'DashboardWidgetContentList',
		props: {
			endpoint: '/api/admin/post',
			link: '/obsah/clanky',
			color: 'indigo',
			emptyLabel: 'Zatím žádné články',
		},
		defaultPosition: 1,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'novelties',
		title: 'Novinky',
		icon: NewspaperIcon,
		component: 'DashboardWidgetContentList',
		props: {
			endpoint: '/api/admin/novelty',
			link: '/obsah/novinky',
			color: 'emerald',
			emptyLabel: 'Zatím žádné novinky',
		},
		defaultPosition: 2,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'events',
		title: 'Události',
		icon: CalendarDaysIcon,
		component: 'DashboardWidgetContentList',
		props: {
			endpoint: '/api/admin/event',
			link: '/obsah/udalosti',
			color: 'amber',
			emptyLabel: 'Zatím žádné události',
		},
		defaultPosition: 3,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'pages',
		title: 'Informační stránky',
		icon: DocumentIcon,
		component: 'DashboardWidgetContentList',
		props: {
			endpoint: '/api/admin/page',
			link: '/obsah/stranky',
			color: 'sky',
			emptyLabel: 'Zatím žádné stránky',
		},
		defaultPosition: 4,
		defaultSize: 'half',
		defaultEnabled: true,
	},
	{
		key: 'changelog',
		title: 'Changelog',
		icon: RocketLaunchIcon,
		component: 'DashboardWidgetChangelog',
		defaultPosition: 5,
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
};
