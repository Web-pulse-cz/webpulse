<script setup lang="ts">
import { Form } from 'vee-validate';

const { t, locale } = useI18n();
const api = useApi();
const toast = useToast();

const props = defineProps({
  services: {
    type: Array,
    default: () => [],
  },
  type: {
    type: String,
    default: 'contact',
  },
  variant: {
    type: String,
    default: 'dark',
  },
});

const serviceId = defineModel('serviceId', {
  type: Number,
  default: null,
});

function submitForm(values) {
  useAsyncData('contactForm', () =>
    api.global
      .demand(values, locale.value)
      .then(() => {
        toast.success({
          title: 'Success!',
          message: 'Your action was completed successfully.',
          position: 'topRight',
        });
      })
      .catch((error) => {
        toast.error({ title: 'Error!', message: 'Something went wrong.', position: 'topRight' });
        console.error('Error submitting contact form:', error);
      }),
  );
}
</script>

<template>
  <div>
    <Form v-slot="{ values, handleSubmit }">
      <form class="grid grid-cols-6 gap-4" @submit.prevent="handleSubmit(submitForm)">
        <BaseFormInput
          name="fullname"
          :label="t('contactForm.fullName')"
          type="text"
          rules="required"
          required
          class="col-span-3"
          :variant="variant"
        />
        <br />
        <BaseFormInput
          name="email"
          :label="t('contactForm.email')"
          type="text"
          rules="required"
          required
          class="col-span-3"
          :variant="variant"
        />
        <BaseFormInput
          name="phone_prefix"
          :label="t('contactForm.phonePrefix')"
          type="text"
          rules="required"
          required
          class="col-span-1"
          :variant="variant"
        />
        <BaseFormInput
          name="phone"
          :label="t('contactForm.phone')"
          type="text"
          rules="required"
          required
          class="col-span-2"
          :variant="variant"
        />
        <BaseFormSelect
          v-if="type === 'service'"
          v-model="serviceId"
          name="service_id"
          :label="t('contactForm.service')"
          :options="
            services.map((service) => ({
              value: service.id,
              name: service.name,
            }))
          "
          class="col-span-3"
          :variant="variant"
        />
        <BaseFormInput
          v-if="type === 'service'"
          name="offer_price"
          :label="t('contactForm.offerPrice')"
          type="number"
          class="col-span-2"
          :variant="variant"
        />
        <BaseFormInput
          name="url"
          :label="t('contactForm.url')"
          type="text"
          class="col-span-3"
          :variant="variant"
        />
        <BaseFormTextarea
          name="text"
          :label="t('contactForm.message')"
          class="col-span-full"
          rules="required"
          :variant="variant"
        />
        <div class="col-span-full text-right">
          <BaseButton variant="primary" size="lg">
            {{ type === 'service' ? t('demand.submitButton') : t('contactForm.submit') }}
          </BaseButton>
        </div>
      </form>
    </Form>
  </div>
</template>
