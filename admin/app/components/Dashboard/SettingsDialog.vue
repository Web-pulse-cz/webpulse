<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
	Dialog,
	DialogPanel,
	DialogTitle,
	TransitionChild,
	TransitionRoot,
} from '@headlessui/vue';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import draggable from 'vuedraggable';

import { availableWidgets, type WidgetConfig, type WidgetSize } from '~/components/Dashboard/widgets';
import { usePermissions } from '~/composables/usePermissions';

const show = defineModel('show', {
	type: Boolean,
	default: false,
});

const props = defineProps<{
	config: WidgetConfig[];
}>();

const emit = defineEmits<{
	(e: 'save', payload: WidgetConfig[]): void;
}>();

const { canView, moduleBelongsToSite } = usePermissions();

function widgetVisible(slug?: string): boolean {
	if (!slug) return true;
	return moduleBelongsToSite(slug) && canView(slug);
}

const visibleWidgets = computed(() =>
	availableWidgets.filter((w) => widgetVisible(w.permissionSlug)),
);

const visibleKeys = computed(() => new Set(visibleWidgets.value.map((w) => w.key)));

const localConfig = ref<WidgetConfig[]>([]);

watch(
	() => [show.value, props.config, visibleKeys.value],
	() => {
		if (show.value) {
			localConfig.value = props.config
				.filter((w) => visibleKeys.value.has(w.widget_key))
				.map((w) => ({ ...w }));
		}
	},
	{ immediate: true },
);

const getTitle = (key: string) =>
	availableWidgets.find((w) => w.key === key)?.title ?? key;

function applyPositions() {
	localConfig.value = localConfig.value.map((w, index) => ({
		...w,
		position: index + 1,
	}));
}

function save() {
	applyPositions();
	const hiddenConfigs = props.config.filter((w) => !visibleKeys.value.has(w.widget_key));
	emit('save', [...localConfig.value, ...hiddenConfigs]);
	show.value = false;
}

function resetDefaults() {
	localConfig.value = visibleWidgets.value
		.slice()
		.sort((a, b) => a.defaultPosition - b.defaultPosition)
		.map((w) => ({
			widget_key: w.key,
			position: w.defaultPosition,
			size: w.defaultSize,
			enabled: w.defaultEnabled,
		}));
}

function setSize(key: string, size: WidgetSize) {
	const item = localConfig.value.find((w) => w.widget_key === key);
	if (item) item.size = size;
}
</script>

<template>
	<TransitionRoot as="template" :show="show">
		<Dialog class="relative z-50" @close="show = false">
			<TransitionChild
				as="template"
				enter="ease-out duration-300"
				enter-from="opacity-0"
				enter-to="opacity-100"
				leave="ease-in duration-200"
				leave-from="opacity-100"
				leave-to="opacity-0"
			>
				<div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" />
			</TransitionChild>

			<div class="fixed inset-0 z-10 w-screen overflow-y-auto">
				<div class="flex min-h-full items-center justify-center p-4">
					<TransitionChild
						as="template"
						enter="ease-out duration-300"
						enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
						enter-to="opacity-100 translate-y-0 sm:scale-100"
						leave="ease-in duration-200"
						leave-from="opacity-100 translate-y-0 sm:scale-100"
						leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
					>
						<DialogPanel
							class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all sm:p-8"
						>
							<div class="mb-6 flex items-start justify-between gap-4">
								<div>
									<DialogTitle class="text-xl font-extrabold tracking-tight text-slate-900">
										Nastavení dashboardu
									</DialogTitle>
									<p class="mt-1 text-sm text-slate-500">
										Zapněte, seřaďte a zvolte velikost widgetů pro tento web.
									</p>
								</div>
								<button
									type="button"
									class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
									@click="show = false"
								>
									<XMarkIcon class="size-5" />
								</button>
							</div>

							<draggable
								v-model="localConfig"
								item-key="widget_key"
								handle=".drag-handle"
								class="space-y-2"
								:animation="150"
							>
								<template #item="{ element }">
									<div
										class="flex items-center gap-3 rounded-xl border border-slate-100 bg-white p-3 ring-1 ring-transparent transition hover:ring-slate-200"
									>
										<button
											type="button"
											class="drag-handle cursor-grab text-slate-400 hover:text-slate-600 active:cursor-grabbing"
											aria-label="Přesunout"
										>
											<Bars3Icon class="size-5" />
										</button>

										<label class="flex flex-1 items-center gap-3">
											<input
												v-model="element.enabled"
												type="checkbox"
												class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
											/>
											<span class="text-sm font-semibold text-slate-800">{{
												getTitle(element.widget_key)
											}}</span>
										</label>

										<div
											class="inline-flex rounded-lg bg-slate-100 p-0.5 text-[11px] font-bold uppercase tracking-widest"
										>
											<button
												type="button"
												class="rounded-md px-3 py-1.5 transition"
												:class="
													element.size === 'half'
														? 'bg-white text-indigo-600 shadow-sm'
														: 'text-slate-500 hover:text-slate-700'
												"
												@click="setSize(element.widget_key, 'half')"
											>
												Polovina
											</button>
											<button
												type="button"
												class="rounded-md px-3 py-1.5 transition"
												:class="
													element.size === 'full'
														? 'bg-white text-indigo-600 shadow-sm'
														: 'text-slate-500 hover:text-slate-700'
												"
												@click="setSize(element.widget_key, 'full')"
											>
												Celé
											</button>
										</div>
									</div>
								</template>
							</draggable>

							<div class="mt-6 flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
								<button
									type="button"
									class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600"
									@click="resetDefaults"
								>
									Obnovit výchozí
								</button>
								<div class="flex items-center gap-3">
									<BaseButton variant="secondary" size="md" @click="show = false">
										Zrušit
									</BaseButton>
									<BaseButton variant="primary" size="md" @click="save">
										Uložit
									</BaseButton>
								</div>
							</div>
						</DialogPanel>
					</TransitionChild>
				</div>
			</div>
		</Dialog>
	</TransitionRoot>
</template>
