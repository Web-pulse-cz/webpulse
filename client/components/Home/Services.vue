<script setup lang="ts">
import { ref } from "vue";
const { t } = useI18n();

const expandedIndex = ref<number | null>(null);
defineProps({
  services: {
    type: Array,
    required: true,
  },
});
</script>

<template>
  <div class="space-y-2">
    <BasePropsHeading type="h2" class="text-center">{{
      t("services.title")
    }}</BasePropsHeading>
    <div
      v-for="(service, index) in services"
      :key="index"
      @mouseenter="expandedIndex = index"
      @mouseleave="expandedIndex = null"
      class="overflow-hidden transition-all duration-500 ease-in-out rounded-lg cursor-pointer border border-dark"
    >
      <div class="p-6">
        <div class="grid grid-cols-8 gap-x-4">
          <p class="col-span-1">{{ `0${index + 1}` }}</p>
          <div class="col-span-7">
            <BasePropsHeading type="h3" margin-bottom="mb-0">{{
              service.name
            }}</BasePropsHeading>
            <div
              v-if="expandedIndex === index"
              class="mt-4 opacity-100 transition-opacity duration-500"
            >
              <div
                :class="[
                  expandedIndex !== index ? 'hidden' : '',
                  'transition-all duration-500 ease-in-out',
                ]"
              >
                <BasePropsParagraph>{{
                  service.description
                }}</BasePropsParagraph>
                <div class="flex items-center justify-between">
                  <BasePropsParagraph bold="bold">
                    Cena:
                    {{
                      $formatPrice(
                        service.price,
                        service.currency,
                        service.tax_rate,
                      )
                    }}
                    (cena za
                    {{
                      service.price_type === "hourly" ? "hodinu" : "projekt"
                    }})
                  </BasePropsParagraph>
                  <BaseButton
                    :href="`/services/${service.slug}`"
                    size="xl"
                    variant="primary"
                    >{{ t("services.inquiry") }}</BaseButton
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
