<script setup>
import { defineComponent, h } from 'vue';
import { useSettingStore } from '~/../stores/settingStore.js';
import { useGlobalApi } from '~/../api/global.js';

const localePath = useLocalePath();

const settingStore = useSettingStore();
const props = defineProps({
  variant: {
    type: String,
    default: 'newsletter',
  },
});
const { t, locale } = useI18n();
const toast = useToast();

const navigation = {
  social: [
    {
      name: 'Linkedin',
      href: 'https://cz.linkedin.com/in/martin-hanzl-618784173',
      icon: defineComponent({
        render: () =>
          h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', {
              'fill-rule': 'evenodd',
              d: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
              'clip-rule': 'evenodd',
            }),
          ]),
      }),
    },
  ],
};
const email = ref('');

const handleSubmit = () => {
  const api = useApi();
  const {
    data: newsletterData,
    pending: newsletterPending,
    error: newsletterError,
  } = useAsyncData('newsletter', () =>
    api.global
      .newsletter(email.value, locale.value)
      .then((response) => {
        toast.success({
          title: 'Success!',
          message: 'Your action was completed successfully.',
          position: 'topRight',
        });
      })
      .catch((error) => {
        toast.error({ title: 'Error!', message: 'Something went wrong.', position: 'topRight' });
      }),
  );
};
</script>

<template>
  <footer class="bg-footerGray">
    <LayoutFooterContainer>
      <div class="xl:grid xl:grid-cols-4 xl:gap-8">
        <div class="space-y-4 xl:space-y-8">
          <NuxtLink :to="locale !== 'cs' ? `/${locale}` : '/'">
            <img src="/../public/static/img/LOGO-2.svg" alt="Company name" />
          </NuxtLink>
          <div>
            <p class="text-sm/6 text-textWhiteFooter">
              &copy; {{ new Date().getFullYear() }} Webpulse,
              {{ t('layout.footer.rightsReserved') }}.
            </p>
            <p class="text-sm/6 text-textWhiteFooter">
              {{ t('layout.footer.createdBy') }}
            </p>
          </div>
          <div class="flex gap-x-6">
            <a
              v-for="item in navigation.social"
              :key="item.name"
              :href="item.href"
              class="text-gray-400 hover:text-gray-300"
            >
              <span class="sr-only">{{ item.name }}</span>
              <component :is="item.icon" class="mb-4 size-6 xl:mb-0" aria-hidden="true" />
            </a>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-8 xl:col-span-2">
          <div
            v-if="
              settingStore.bottomMenu &&
              settingStore.bottomMenu['value'] &&
              settingStore.bottomMenu['value']['groups']
            "
            class="md:grid md:grid-cols-2 md:gap-8"
          >
            <div v-for="(group, index) in settingStore.bottomMenu['value']['groups']" :key="index">
              <BasePropsHeading type="h6" color="white" margin-bottom="mb-4 xl:mb-8">
                {{ group.name }}
              </BasePropsHeading>
              <ul role="list" class="mb-4 space-y-2 xl:space-y-4">
                <li v-for="(item, key) in group.submenu" :key="key">
                  <NuxtLink
                    :to="
                      localePath({
                        name: item.link !== '' && item.link !== null ? item.link : 'index',
                      })
                    "
                    class="text-sm/6 text-textWhiteFooter"
                    >{{ item.name }}</NuxtLink
                  >
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div>
          <BasePropsHeading type="h6" color="white" margin-bottom="mb-4 xl:mb-8">
            {{ t('layout.footer.stayInTouch') }}
          </BasePropsHeading>
          <div class="relative rounded-3xl">
            <form @submit.prevent="handleSubmit">
              <BaseFormInput
                v-model="email"
                name="email"
                :placeholder="t('layout.footer.email')"
                type="text"
                class="col-span-3 h-12 rounded-lg bg-[#707070] pr-12 text-white placeholder:text-white"
                :variant="variant"
              />
              <button
                class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300"
                type="submit"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="currentColor"
                  class="bi bi-send"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"
                  />
                </svg>
              </button>
            </form>
          </div>

          <p class="mt-2 text-sm/6 text-textWhiteFooter">
            {{ t('layout.footer.submitForm') }}
            <!-- TODO: Add link to privacy policy -->
            <NuxtLink to="#" class="textWhiteFooter underline">
              {{ t('layout.footer.personalInformation') }}</NuxtLink
            >
          </p>
        </div>
      </div>
    </LayoutFooterContainer>
  </footer>
</template>
