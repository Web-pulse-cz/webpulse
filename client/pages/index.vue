<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n();

const loading = inject('loading', ref(false));

const benefits = Array.from({ length: 5 }, (_, i) => ({
	title: t(`benefits.data.${i}.title`),
	description: t(`benefits.data.${i}.description`),
}));

const services = ref([]);

function loadServices() {
	loading.value = true;
	const client = useSanctumClient();

	client('/api/service/' + locale.value, {
		method: 'GET',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json',
		},
	})
		.then((response) => {
			services.value = response;
		})
		.catch(() => {
			services.value = [];
			console.error('Failed to load services');
		})
		.finally(() => {
			loading.value = false;
		});
}

onMounted(() => {
	loadServices();
});
</script>

<template>
	<div>
		<LayoutContainer class="space-y-24">
			<HomeHero />
			<div>
				<BasePropsHeading type="h2">
					{{ t("benefits.title") }}
				</BasePropsHeading>
				<div class="flex flex-wrap gap-6">
					<HomeBenefitCard
						v-for="(benefit, index) in benefits"
						:key="index"
						v-gsap.whenVisible.from="{ autoAlpha: 0 }"
						:title="benefit.title"
						:description="benefit.description"
						class="col-span-1"
					/>
				</div>
			</div>
			<HomeServices
				v-if="services && services.length"
				:services="services"
			/>
			<HomeTechnologies />
			<HomeContactForm />
		</LayoutContainer>
	</div>
</template>
