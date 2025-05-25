<script setup lang="ts">
const loading = ref(false);

provide('loading', loading);
</script>

<template>
	<div>
		<LayoutNavbar />
		<main>
			<NuxtPage />
		</main>
		<LayoutFooter />
		<LayoutCookiesBar />
	</div>
</template>
