<script setup lang="ts">
import { ref } from 'vue';

const { t } = useI18n();

const expandedIndex = ref<number | null>(null);
defineProps({
  services: {
    type: Array,
    required: true,
  },
});

const demandDialog = ref({
  show: false,
  service_id: null,
});
function openDemandDialog(serviceId) {
  demandDialog.value.service_id = serviceId;
  demandDialog.value.show = true;
}
</script>

<template>
  <div class="space-y-2">
    <BasePropsHeading type="h2" class="text-center">
      {{ t('services.title') }}
    </BasePropsHeading>
    <div
      v-for="(service, index) in services"
      :key="index"
      class="border-dark cursor-pointer overflow-hidden rounded-lg border transition-all duration-500 ease-in-out"
      @mouseenter="expandedIndex = index"
      @mouseleave="expandedIndex = null"
    >
      <div class="p-6">
        <div class="grid grid-cols-8 gap-x-4">
          <p class="col-span-1">
            {{ `0${index + 1}` }}
          </p>
          <div class="col-span-7">
            <BasePropsHeading type="h3" margin-bottom="mb-0">
              {{ service.name }}
            </BasePropsHeading>
            <div class="mt-4 opacity-100 transition-opacity duration-500">
              <div>
                <BasePropsParagraph :html="service.description" />
                <div class="flex items-center justify-between">
                  <BasePropsParagraph bold="bold">
                    {{ t('services.price') }}:
                    {{ $formatPrice(service.price, service.currency, service.tax_rate) }}
                    ({{ t('services.pricePer') }}
                    {{
                      service.price_type === 'hourly' ? t('services.hour') : t('services.project')
                    }})
                  </BasePropsParagraph>
                  <NuxtImg
                    v-if="service.image"
                    :src="'/img/service/small/' + service.image"
                    :alt="service.name"
                    :lazyload="true"
                    fit="contain"
                    format="webp"
                    sizes="620px"
                  />
                  <BaseButton
                    :href="`/services/${service.slug}`"
                    size="xl"
                    variant="primary"
                    @click="openDemandDialog(service.id)"
                  >
                    {{ t('services.demand') }}
                  </BaseButton>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <HomeDemandDialog
      v-model:open="demandDialog.show"
      :service-id="demandDialog.service_id"
      :services="services"
      @close="demandDialog.show = false"
    />
  </div>
</template>
