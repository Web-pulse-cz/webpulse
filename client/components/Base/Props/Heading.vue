<script setup lang="ts">
const colorClasses = {
	light: 'text-light',
	primary: 'text-primary',
};

const typeClasses = {
	h1: 'text-5xl font-bold tracking-tight sm:text-7xl',
	h2: 'text-4xl font-semibold tracking-tight sm:text-5xl',
	h3: 'text-3xl font-semibold tracking-tight sm:text-4xl',
	h4: 'text-2xl font-medium tracking-tight sm:text-3xl',
	h5: 'text-xl font-medium tracking-tight sm:text-2xl',
	h6: 'text-lg font-medium tracking-tight sm:text-xl',
};

const props = defineProps({
	type: {
		type: String,
		default: 'h1',
		required: false,
	},
	color: {
		type: String,
		default: 'primary',
		required: false,
	},
	marginBottom: {
		type: String,
		default: 'mb-8',
		required: false,
	},
});

const baseClass = computed(() => {
	return `${typeClasses[props.type] || typeClasses.h1} ${colorClasses[props.color] || colorClasses.light} text-balance ${props.marginBottom} leading-tight tracking-tight`;
});
</script>

<template>
	<h1
		v-if="type === 'h1'"
		:class="baseClass"
	>
		<slot />
	</h1>
	<h2
		v-if="type === 'h2'"
		:class="baseClass"
	>
		<slot />
	</h2>
	<h3
		v-if="type === 'h3'"
		:class="baseClass"
	>
		<slot />
	</h3>
	<h4
		v-if="type === 'h4'"
		:class="baseClass"
	>
		<slot />
	</h4>
	<h5
		v-if="type === 'h5'"
		:class="baseClass"
	>
		<slot />
	</h5>
	<h6
		v-if="type === 'h6'"
		:class="baseClass"
	>
		<slot />
	</h6>
</template>
