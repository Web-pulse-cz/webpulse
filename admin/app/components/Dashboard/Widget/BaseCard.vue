<script setup lang="ts">
import { colorClasses } from '~/components/Dashboard/widgets';

defineProps<{
	title: string;
	icon: unknown;
	color: string;
	count?: number;
	link?: string;
}>();
</script>

<template>
	<component
		:is="link ? 'NuxtLink' : 'div'"
		:to="link"
		class="group block h-full rounded-2xl bg-white p-6 ring-1 ring-slate-100 transition"
		:class="link ? 'hover:-translate-y-0.5 hover:ring-slate-200 hover:shadow-sm' : ''"
	>
		<div class="mb-5 flex items-center justify-between">
			<div class="flex items-center gap-3">
				<div
					class="flex size-9 items-center justify-center rounded-lg"
					:class="[colorClasses[color]?.bg, colorClasses[color]?.text]"
				>
					<component :is="icon" class="size-5" />
				</div>
				<LayoutTitle class="!mb-0">{{ title }}</LayoutTitle>
			</div>
			<div
				v-if="count !== undefined"
				class="flex items-baseline gap-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"
			>
				Celkem:
				<span class="text-sm/none font-black" :class="colorClasses[color]?.count">{{
					count || 0
				}}</span>
			</div>
		</div>

		<slot />
	</component>
</template>
