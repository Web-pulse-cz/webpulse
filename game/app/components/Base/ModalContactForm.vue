<script setup>
import {
  Dialog,
  DialogTitle,
  DialogDescription,
  DialogPanel,
  DialogOverlay,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue';
import { Form } from 'vee-validate';
import { ref } from 'vue';

const { t, locale } = useI18n();
const emit = defineEmits(['close']);
const api = useApi();
const toast = useToast();

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },

  variant: {
    type: String,
    default: 'dark',
  },
});

const onSubmit = async (values) => {
  try {
    await api.global.demand(values, locale.value);

    toast.success({
      title: 'Success!',
      message: 'Your action was completed successfully.',
      position: 'topRight',
    });

    emit('close');
  } catch (error) {
    toast.error({
      title: 'Error!',
      message: 'Something went wrong.',
      position: 'topRight',
    });

    console.error('Error submitting contact form:', error);
  }
};
</script>

<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-200/75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-full overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
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
              class="relative transform overflow-hidden rounded-3xl border bg-white p-16 py-16 text-left transition-all sm:my-8 sm:max-w-lg"
            >
              <div class="flex items-center justify-between px-4">
                <BasePropsHeading color="black" type="h5">
                  {{ t('contactForm.title') }}
                </BasePropsHeading>
                <button
                  aria-label="Close modal"
                  class="mb-8 text-4xl font-bold text-gray-600 hover:text-black"
                  @click="$emit('close')"
                >
                  &times;
                </button>
              </div>
              <Form @submit="onSubmit">
                <div class="grid grid-cols-1 gap-4">
                  <BaseFormInput
                    name="fullname"
                    :placeholder="t('contactForm.fullName')"
                    type="text"
                    class="h-14 w-full rounded-xl px-4"
                    :variant="variant"
                  />

                  <BaseFormInput
                    name="email"
                    :placeholder="t('contactForm.email')"
                    type="text"
                    class="h-14 rounded-xl px-4"
                    :variant="variant"
                  />

                  <BaseFormInput
                    name="phone"
                    :placeholder="t('contactForm.phone')"
                    type="text"
                    class="h-14 rounded-xl px-4"
                    :variant="variant"
                  />

                  <BaseFormTextarea
                    name="text"
                    :placeholder="t('contactForm.message')"
                    class="mb-16 h-14 rounded-xl px-4"
                    :variant="variant"
                  />
                  <p class="px-4 text-sm/6 text-black">
                    {{ t('contactForm.submitForm') }}
                    <!-- TODO: Add link to privacy policy -->
                    <NuxtLink to="#" class="text-brand">
                      {{ t('contactForm.personalInformation') }}</NuxtLink
                    >
                  </p>
                  <div class="px-4 text-start">
                    <BaseButton variant="primary" size="xxl">
                      {{ t('contactForm.submit') }}
                    </BaseButton>
                  </div>
                </div>
              </Form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
