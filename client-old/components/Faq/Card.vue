<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { PlusSmallIcon, MinusSmallIcon } from '@heroicons/vue/24/outline';

interface faqProps {
  faq: {
    question: string;
    answer: string;
  }[];
}

const props = defineProps<faqProps>();
</script>

<template>
  <Disclosure v-slot="{ open }" as="div" class="pt-6 first:pt-0 last:pb-0">
    <dt>
      <DisclosureButton class="flex w-full items-start justify-between text-left text-gray-900">
        <BasePropsHeading type="h5">{{ faq.question }}</BasePropsHeading>
        <span class="ml-6 flex h-7 items-center">
          <PlusSmallIcon v-if="!open" class="size-6" aria-hidden="true" />
          <MinusSmallIcon v-else class="size-6" aria-hidden="true" />
        </span>
      </DisclosureButton>
    </dt>
    <DisclosurePanel as="dd" class="mt-2 pr-12">
      <p class="text-base/7 text-gray-600" v-html="faq.answer" />
    </DisclosurePanel>
  </Disclosure>
</template>
