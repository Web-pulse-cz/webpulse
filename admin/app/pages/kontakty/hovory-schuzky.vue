<script setup lang="ts">
import { ref } from 'vue';

import { CalendarDaysIcon, PhoneIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const pageTitle = ref('Hovory a schůzky');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
	{
		name: 'Kontakty',
		link: '/kontakty',
		current: false,
	},
	{
		name: pageTitle.value,
		link: '/kontakty/hovory-schuzky',
		current: true,
	},
]);

const dashboard = ref<any>({});

async function loadDashboard() {
	loading.value = true;
	const client = useSanctumClient();

	await client('/api/admin/dashboard/contact', {
		method: 'GET',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
	})
		.then((response) => {
			dashboard.value = response;
		})
		.catch(() => {
			error.value = true;
			$toast.show({
				summary: 'Chyba',
				detail: 'Nepodařilo se načíst přehled. Zkuste to prosím později.',
				severity: 'error',
			});
		})
		.finally(() => {
			loading.value = false;
		});
}

useHead({
	title: pageTitle.value,
});

onMounted(() => {
	loadDashboard();
});
definePageMeta({
	middleware: 'sanctum:auth',
});
</script>

<template>
	<div class="space-y-6 pb-20">
		<LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="contacts" />

		<LayoutContainer>
			<div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
				<div class="flex items-center gap-3">
					<div
						class="relative flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
					>
						<PhoneIcon class="size-5" />
						<div
							class="absolute -right-1 -top-1 flex h-2.5 w-2.5 animate-pulse rounded-full bg-amber-400 ring-2 ring-white"
						/>
					</div>
					<LayoutTitle class="!mb-0">Dnes kontaktovat (Hovory)</LayoutTitle>
				</div>
				<div
					class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"
				>
					Celkem:
					<span class="text-sm/none font-black text-indigo-600">{{
						dashboard.contactsToCall?.length || 0
					}}</span>
				</div>
			</div>

			<BaseTable
				:items="dashboard.contactsToCall"
				:columns="[
					{ key: 'firstname', name: 'Jméno', type: 'text', sortable: false },
					{ key: 'lastname', name: 'Příjmení', type: 'text', sortable: false },
					{ key: 'phone', name: 'Telefonní číslo', type: 'text', sortable: false },
				]"
				:actions="[{ type: 'edit', path: '/kontakty', hash: '#proces' }]"
				:loading="loading"
				:error="error"
				singular="Hovor na dnes"
				plural="Hovory na dnes"
			/>
		</LayoutContainer>

		<LayoutContainer>
			<div class="mb-8 flex items-center gap-3">
				<div
					class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
				>
					<CalendarDaysIcon class="size-5" />
				</div>
				<LayoutTitle class="!mb-0">Nadcházející schůzky</LayoutTitle>
			</div>

			<BaseTable
				:items="dashboard.comingEvents"
				:columns="[
					{ key: 'firstname', name: 'Jméno klienta', type: 'text', sortable: false },
					{ key: 'lastname', name: 'Příjmení', type: 'text', sortable: false },
					{ key: 'next_meeting', name: 'Termín schůzky', type: 'datetime', sortable: false },
				]"
				:actions="[{ type: 'edit', path: '/kontakty', hash: '#proces' }]"
				:loading="loading"
				:error="error"
				singular="Naplánovaná schůzka"
				plural="Naplánované schůzky"
			/>
		</LayoutContainer>
	</div>
</template>
