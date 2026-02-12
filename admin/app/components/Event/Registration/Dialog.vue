<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
import { useCountryStore } from '~/../stores/countryStore';

const countryStore = useCountryStore();

const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const item = defineModel('item', {
  type: Object,
  default: {
    id: null,
    firstname: '',
    lastname: '',
    email: '',
    phone: '',
    note: '',
    ico: '',
    dic: '',
    company: '',
    street: '',
    city: '',
    zip: '',
    country_id: 1,
    is_paid: false,
  },
});

const emit = defineEmits(['save']);
</script>

<template>
  <div>
    <TransitionRoot as="template" :show="show">
      <Dialog class="relative z-10">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-grayCustom/75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
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
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6"
              >
                <Form
                  @submit="
                    emit('save', item);
                    show = false;
                  "
                >
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        {{ item.id === null ? 'Vytvořit registraci' : 'Upravit registraci' }}
                      </DialogTitle>
                      <div class="mt-6 grid w-full grid-cols-2 gap-6">
                        <BaseFormInput
                          v-model="item.firstname"
                          name="firstname"
                          label="Jméno"
                          type="text"
                          rules="required|min:3"
                        />
                        <BaseFormInput v-model="item.ico" name="ico" label="IČO" type="text" />
                        <BaseFormInput
                          v-model="item.lastname"
                          name="lastname"
                          label="Příjmení"
                          type="text"
                          rules="required|min:3"
                        />
                        <BaseFormInput v-model="item.dic" name="dic" label="DIČ" type="text" />
                        <BaseFormInput
                          v-model="item.email"
                          name="email"
                          label="E-mail"
                          type="text"
                          rules="required|min:3"
                        />
                        <BaseFormInput
                          v-model="item.company"
                          name="company"
                          label="Společnost"
                          type="text"
                        />
                        <BaseFormInput
                          v-model="item.phone"
                          name="phone"
                          label="Telefon"
                          type="text"
                        />
                        <BaseFormInput
                          v-model="item.street"
                          name="street"
                          label="Ulice a číslo popisné"
                          type="text"
                        />
                        <BaseFormCheckbox
                          v-model="item.is_paid"
                          name="is_paid"
                          label="Je uhrazeno?"
                        />
                        <BaseFormInput v-model="item.city" name="city" label="Město" type="text" />
                        <div>&nbsp;</div>
                        <BaseFormInput v-model="item.zip" name="zip" label="PSČ" type="text" />
                        <div>&nbsp;</div>
                        <BaseFormSelect
                          v-model="item.country_id"
                          name="country_id"
                          label="Země"
                          :options="countryStore.countriesOptions"
                        />
                        <BaseFormTextarea
                          v-model="item.note"
                          name="note"
                          label="Poznámka"
                          class="col-span-full"
                        />
                      </div>
                    </div>
                  </div>
                  <div
                    class="mt-4 flex justify-end gap-x-4 lg:mt-6 lg:flex-row-reverse lg:justify-start"
                  >
                    <BaseButton type="submit" variant="success" size="lg"> Uložit </BaseButton>
                    <BaseButton
                      ref="cancelButtonRef"
                      type="button"
                      variant="secondary"
                      size="lg"
                      @click="show = false"
                    >
                      Zavřít
                    </BaseButton>
                  </div>
                </Form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>
