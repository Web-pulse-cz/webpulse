<script setup lang="ts">
import { defineRule } from 'vee-validate';

const labelClasses = {
	dark: 'block text-xs lg:text-sm/6 font-medium text-primary',
	light: 'block text-xs lg:text-sm/6 font-medium text-light',
};

const inputClasses = {
	light: 'ring-secondary focus:ring-light',
	dark: 'ring-secondary focus:ring-secondary',
};

const model = defineModel({
	type: String,
	required: true,
});
const props = defineProps({
	variant: {
		type: String,
		required: false,
		default: 'dark',
	},
	rules: {
		type: String,
		required: false,
		default: '',
	},
	name: {
		type: String,
		required: true,
	},
	label: {
		type: String,
		required: true,
	},
	placeholder: {
		type: String,
		required: false,
		default: '',
	},
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
	rows: {
		type: Number,
		required: false,
		default: 4,
	},
	max: {
		type: Number,
		required: false,
		default: 255,
	},
});
defineRule('required', (value) => {
	if (!value) {
		return `Pole je povinné.`;
	}
	return true;
});
const labelClass = computed(() => {
	return labelClasses[props.variant] || labelClasses.dark;
});
const inputClass = computed(() => {
	return `text-primary mt-2 block w-full rounded-md border-0 py-1.5 lg:py-2 shadow-sm ring-1 ring-inset focus:ring-1 focus:ring-inset text-xs lg:text-sm/6 ${inputClasses[props.variant]}`;
});
</script>

<template>
	<div>
		<label
			:for="name"
			:class="labelClass"
		>{{ label
		}}<span
			v-if="rules.includes('required')"
			class="text-danger ml-1"
		>*</span></label>
		<div class="mt-2">
			<Field
				:id="name"
				v-model="model"
				:name="name"
			>
				<textarea
					:id="name"
					v-model="model"
					:rows="rows"
					:name="name"
					:maxlength="max"
					:autofocus="false"
					tabindex="-1"
					:class="[inputClass, { 'bg-light': disabled }]"
				/>
			</Field>
			<p
				v-if="model"
				class="text-end text-grayLight text-xs pt-1"
			>
				{{ model.length }} / {{ max }}
			</p>
		</div>
	</div>
</template>
