<script setup lang="ts">
import { MinusSmallIcon, PlusSmallIcon } from '@heroicons/vue/24/outline';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';

defineProps<{
  faqs: { question: string; answer: string }[];
}>();
</script>

<template>
  <dl class="divide-y divide-gray-900/10">
    <Disclosure
      v-for="(faq, index) in faqs"
      :key="index"
      v-slot="{ open }"
      :default-open="index === 0"
      as="div"
      class="py-6 first:pt-0 last:pb-0"
    >
      <dt>
        <DisclosureButton class="flex w-full items-start justify-between text-left text-textBlack">
          <BasePropsHeading type="h4" class="text-xl/7 font-semibold">{{
            faq.question
          }}</BasePropsHeading>
          <span class="ml-6 flex h-7 items-center">
            <PlusSmallIcon v-if="!open" class="size-6" aria-hidden="true" />
            <MinusSmallIcon v-else class="size-6" aria-hidden="true" />
          </span>
        </DisclosureButton>
      </dt>

      <Transition
        enter-active-class="transition-all duration-1000 ease-out"
        enter-from-class="opacity-0 -translate-y-2 max-h-0"
        enter-to-class="opacity-100 translate-y-0 max-h-[500px]"
        leave-active-class="transition-all duration-500 ease-in-out"
        leave-from-class="opacity-100 translate-y-0 max-h-[500px]"
        leave-to-class="opacity-0 -translate-y-2 max-h-0"
      >
        <DisclosurePanel v-if="open" as="dd" class="mt-2 overflow-hidden pr-12">
          <p class="text-base/7 text-gray-600" v-html="faq.answer" />
        </DisclosurePanel>
      </Transition>
    </Disclosure>
  </dl>
</template>
