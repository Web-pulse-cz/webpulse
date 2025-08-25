<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  PauseCircleIcon,
  StarIcon,
  HeartIcon,
  ChatBubbleBottomCenterIcon,
  PhoneIcon,
  LifebuoyIcon,
} from '@heroicons/vue/24/outline';
import DumbbellIcon from '~/../public/static/img/icon/dumbbell.svg';
import SmileFullIcon from '~/../public/static/img/icon/smile-full.svg';
import SmileAudioIcon from '~/../public/static/img/icon/smile-audio.svg';
import SmileAudioBookIcon from '~/../public/static/img/icon/smile-audiobook.svg';
import SmileAudioDreamIcon from '~/../public/static/img/icon/smile-audiodream.svg';
import SmileBookIcon from '~/../public/static/img/icon/smile-book.svg';
import SmileBookDreamIcon from '~/../public/static/img/icon/smile-bookdream.svg';
import SmileDreamIcon from '~/../public/static/img/icon/smile-dream.svg';
import NoPepsiIcon from '~/../public/static/img/icon/no-pepsi.svg';

const props = defineProps({
  activities: {
    type: Array,
    required: true,
    default: [],
  },
});

const emit = defineEmits(['update-item', 'load-items', 'add-item']);

const currentDate = ref(new Date());
const selectedDate = ref(new Date());

const startOfMonth = computed(() => {
  const date = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
  let day = date.getDay();
  if (day === 0) {
    day = 7; // Adjust Sunday to be the last day of the week
  }
  return day - 1; // Adjust to make Monday the first day of the week
});

const isSmile = ref('empty');
function checkSmile(day: Date) {
  const activityIds = [];
  const formattedDay = day.getDate().toString().padStart(2, '0');
  props.activities.forEach((activity) => {
    if (activity.formatted_day === formattedDay) {
      if ([2, 3, 4, 5, 16].includes(activity.activity.id)) {
        activityIds.push(activity.activity.id);
      }
    }
  });

  if (activityIds.length === 0) {
    return 'empty';
  } else if (activityIds.length === 1) {
    if (activityIds.includes(3)) {
      return 'book';
    }
    if (activityIds.includes(4)) {
      return 'audio';
    }
    if ([2, 5, 16].includes(activityIds[0])) {
      return 'dream';
    }
  } else if (
    activityIds.includes(3) &&
    activityIds.includes(4) &&
    [2, 5, 16].some((id) => activityIds.includes(id))
  ) {
    return 'full';
  } else if (activityIds.includes(3) && activityIds.includes(4)) {
    return 'audiobook';
  } else if (activityIds.includes(3) && [2, 5, 16].some((id) => activityIds.includes(id))) {
    return 'bookdream';
  } else if (activityIds.includes(4) && [2, 5, 16].some((id) => activityIds.includes(id))) {
    return 'audiodream';
  } else if ([2, 5, 16].some((id) => activityIds.includes(id))) {
    return 'dream';
  } else {
    return 'empty';
  }
}

const daysInMonth = computed(() => {
  return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0).getDate();
});

const days = computed(() => {
  const daysArray = [];
  for (let i = 1; i <= daysInMonth.value; i++) {
    daysArray.push(new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), i));
  }
  return daysArray;
});

function prevMonth() {
  currentDate.value = new Date(currentDate.value.setMonth(currentDate.value.getMonth() - 1));
  emit('load-items', currentDate.value.getMonth() + 1, currentDate.value.getFullYear());
}

function nextMonth() {
  currentDate.value = new Date(currentDate.value.setMonth(currentDate.value.getMonth() + 1));
  emit('load-items', currentDate.value.getMonth() + 1, currentDate.value.getFullYear());
}

function selectDate(date: Date) {
  selectedDate.value = date;
  emit('add-item', selectedDate.value);
}

const activitiesByDay = computed(() => {
  return (day: Date) => {
    const formattedDay = day.getDate().toString().padStart(2, '0');
    return props.activities.filter((activity) => {
      return activity.formatted_day === formattedDay;
    });
  };
});
</script>

<template>
  <div>
    <div class="mb-4 flex items-center justify-between">
      <BaseButton variant="primary" size="lg" @click="prevMonth"> Předchozí měsíc </BaseButton>
      <h2 class="text-sm font-semibold text-primaryCustom lg:text-lg">
        {{
          currentDate
            .toLocaleDateString('cs-CZ', { month: 'long', year: 'numeric' })[0]
            .toUpperCase() +
          currentDate.toLocaleDateString('cs-CZ', { month: 'long', year: 'numeric' }).slice(1)
        }}
      </h2>
      <BaseButton variant="primary" size="lg" @click="nextMonth"> Následující měsíc </BaseButton>
    </div>
    <div class="grid grid-cols-7">
      <div
        class="lg:text-md rounded-l-lg bg-grayDark py-1.5 text-center text-xs font-semibold lg:py-2"
      >
        Po
      </div>
      <div class="lg:text-md bg-grayDark py-1.5 text-center text-xs font-semibold lg:py-2">Út</div>
      <div class="lg:text-md bg-grayDark py-1.5 text-center text-xs font-semibold lg:py-2">St</div>
      <div class="lg:text-md bg-grayDark py-1.5 text-center text-xs font-semibold lg:py-2">Čt</div>
      <div class="lg:text-md bg-grayDark py-1.5 text-center text-xs font-semibold lg:py-2">Pá</div>
      <div class="lg:text-md bg-grayDark py-1.5 text-center text-xs font-semibold lg:py-2">So</div>
      <div
        class="lg:text-md rounded-r-lg bg-grayDark py-1.5 text-center text-xs font-semibold lg:py-2"
      >
        Ne
      </div>
      <div v-for="n in startOfMonth" :key="n" class="p-2 lg:p-4" />
      <div
        v-for="day in days"
        :key="day"
        class="flex h-auto min-h-[128px] flex-col border border-gray-100 p-2"
      >
        <div
          :class="[
            'flex size-2 cursor-pointer items-center justify-center rounded-full p-[12px] text-xs font-medium text-primaryCustom lg:size-6 lg:p-[16px] lg:text-sm',
            { 'bg-primaryCustom text-white': day.toDateString() === selectedDate.toDateString() },
          ]"
          @click="selectDate(day)"
        >
          {{ day.getDate() }}
        </div>
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
          <div
            v-for="(activityItem, index) in activitiesByDay(day)"
            :key="index"
            :class="[
              'col-span-1 cursor-pointer',
              { hidden: [2, 3, 4, 5, 9, 16, 17, 24].includes(activityItem.activity.id) },
            ]"
            @click="emit('update-item', activityItem)"
          >
            <div
              v-if="[6, 8, 11, 12].includes(activityItem.activity.id) && !activityItem.completed"
              class="border-b-2 border-r-2 border-danger p-2 lg:p-4"
            />
            <div
              v-else-if="
                [6, 8, 11, 12].includes(activityItem.activity.id) && activityItem.completed
              "
              class="border-2 border-danger bg-danger p-2 lg:p-4"
            />
            <PhoneIcon
              v-if="activityItem.activity.id === 1"
              class="col-span-1 size-5 text-amber-600 lg:size-8"
            />
            <ChatBubbleBottomCenterIcon
              v-if="activityItem.activity.id === 27"
              class="col-span-1 size-5 text-purple-600 lg:size-8"
            />
            <HeartIcon
              v-if="activityItem.activity.id === 28 && !activityItem.completed"
              class="col-span-1 size-5 text-pink-600 lg:size-8"
            />
            <HeartIcon
              v-if="activityItem.activity.id === 28 && activityItem.completed"
              class="col-span-1 size-5 fill-pink-600 text-danger lg:size-8"
            />
            <PauseCircleIcon
              v-if="activityItem.activity.id === 10"
              class="col-span-1 size-5 text-danger lg:size-8"
            />
            <StarIcon
              v-if="activityItem.activity.id === 22"
              class="col-span-1 size-5 fill-danger text-danger lg:size-8"
            />
            <div
              v-if="activityItem.activity.id === 21"
              class="col-span-1 border-2 border-grayDark p-2 lg:p-4"
            />
            <div
              v-if="[13, 14, 23].includes(activityItem.activity.id)"
              class="col-span-1 border-2 border-primaryLight p-2 lg:p-4"
            />
            <div
              v-if="activityItem.activity.id === 18"
              class="text-md col-span-1 flex items-center justify-center font-semibold text-success lg:text-lg"
            >
              K
            </div>
            <div
              v-if="activityItem.activity.id === 20"
              class="text-md col-span-1 flex items-center justify-center font-semibold text-primaryLight lg:text-lg"
            >
              K
            </div>
            <div
              v-if="activityItem.activity.id === 25"
              class="text-md col-span-1 flex items-center justify-center font-semibold text-primaryLight lg:text-lg"
            >
              <DumbbellIcon class="size-5 fill-warning lg:size-8" />
            </div>
            <LifebuoyIcon
              v-if="activityItem.activity.id === 29"
              class="col-span-1 size-5 fill-success text-success lg:size-8"
            />
            <NoPepsiIcon
              v-if="activityItem.activity.id === 30"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
          </div>
          <div v-if="checkSmile(day) !== 'empty'" class="col-span-1 flex flex-col justify-center">
            <SmileBookIcon
              v-if="checkSmile(day) === 'book'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
            <SmileAudioIcon
              v-else-if="checkSmile(day) === 'audio'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
            <SmileBookIcon
              v-else-if="checkSmile(day) === 'book'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
            <SmileDreamIcon
              v-else-if="checkSmile(day) === 'dream'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
            <SmileAudioBookIcon
              v-else-if="checkSmile(day) === 'audiobook'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
            <SmileBookDreamIcon
              v-else-if="checkSmile(day) === 'bookdream'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
            <SmileAudioDreamIcon
              v-else-if="checkSmile(day) === 'audiodream'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
            <SmileFullIcon
              v-else-if="checkSmile(day) === 'full'"
              class="col-span-1 size-5 fill-success lg:size-8"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
