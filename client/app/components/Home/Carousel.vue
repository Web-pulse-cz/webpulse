<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  slides: {
    type: Array,
    required: true,
    default: () => [],
  },
  interval: {
    type: Number,
    default: 5000,
  },
  height: {
    type: String,
    default: '400px',
  },
});

const currentIndex = ref(0);
const timer = ref(null);

const next = () => {
  currentIndex.value = (currentIndex.value + 1) % props.slides.length;
};

const goToSlide = (index) => {
  currentIndex.value = index;
};

const startTimer = () => {
  stopTimer();
  timer.value = setInterval(next, props.interval);
};

const stopTimer = () => {
  if (timer.value) {
    clearInterval(timer.value);
  }
};

onMounted(() => {
  startTimer();
});

onBeforeUnmount(() => {
  stopTimer();
});

const isOpen = ref(false);
const { t } = useI18n();
</script>

<template>
  <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
  <div
    class="carousel relative overflow-hidden lg:rounded-lg"
    :style="{ height }"
    @mouseenter="stopTimer"
    @mouseleave="startTimer"
  >
    <div class="relative h-full w-full justify-between">
      <TransitionGroup name="slide">
        <div
          v-for="(slide, index) in slides"
          v-show="currentIndex === index"
          :key="index"
          class="absolute grid h-full w-full grid-cols-1 flex-col gap-6 py-6 md:grid-cols-2 md:flex-row"
        >
          <div class="order-2 flex flex-col justify-center py-4 md:order-1">
            <BasePropsHeading color="black" type="h1" class="line-clamp-2" margin-bottom="mb-2">
              {{ slide.title }}
              <span class="text-brand">{{ slide.titleColor }}</span>
            </BasePropsHeading>

            <BasePropsParagraph class="mb-2 md:mb-4"> {{ slide.description }}</BasePropsParagraph>

            <BaseButton class="w-36" variant="primary" size="xxl" @click="isOpen = true">
              {{ t('contactForm.button') }}
            </BaseButton>
          </div>

          <div
            class="order-1 flex h-full w-full items-start justify-center pb-0 md:order-2 md:items-end md:justify-end md:pb-16"
          >
            <img
              :src="slide.img"
              :alt="slide.title"
              class="max-h-[320px] w-auto object-contain md:max-h-[420px] lg:max-h-[520px]"
            />
          </div>
        </div>
      </TransitionGroup>
    </div>

    <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
      <button
        v-for="(_, index) in slides"
        :key="index"
        :class="[
          'h-2 w-2 rounded-full transition-all',
          currentIndex === index ? 'bg-brand' : 'bg-orange-300',
        ]"
        @click="goToSlide(index)"
      />
    </div>
  </div>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.5s ease;
}

.slide-enter-from {
  transform: translateX(100%);
}

.slide-leave-to {
  transform: translateX(-100%);
}

.slide-enter-to,
.slide-leave-from {
  transform: translateX(0);
}
</style>
