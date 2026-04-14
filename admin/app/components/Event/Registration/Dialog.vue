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
      <Dialog class="relative z-50">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" />
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
                class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl shadow-slate-200/50 transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-8"
              >
                <Form
                  @submit="
                    emit('save', item);
                    show = false;
                  "
                >
                  <div class="w-full text-left">
                    <DialogTitle
                      as="h3"
                      class="mb-8 text-xl font-bold tracking-tight text-slate-900"
                    >
                      {{ item.id === null ? 'Vytvořit registraci' : 'Upravit registraci' }}
                    </DialogTitle>

                    <div class="grid w-full grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                      <BaseFormInput
                        v-model="item.firstname"
                        name="firstname"
                        label="Jméno"
                        type="text"
                        rules="required|min:3"
                      />
                      <BaseFormInput
                        v-model="item.lastname"
                        name="lastname"
                        label="Příjmení"
                        type="text"
                        rules="required|min:3"
                      />

                      <BaseFormInput
                        v-model="item.email"
                        name="email"
                        label="E-mail"
                        type="email"
                        rules="required|email"
                      />
                      <BaseFormInput
                        v-model="item.phone"
                        name="phone"
                        label="Telefon"
                        type="text"
                      />

                      <BaseFormInput
                        v-model="item.company"
                        name="company"
                        label="Společnost"
                        type="text"
                      />
                      <BaseFormCheckbox
                        v-model="item.is_paid"
                        name="is_paid"
                        label="Je uhrazeno?"
                        class="flex items-end pb-3"
                      />

                      <BaseFormInput v-model="item.ico" name="ico" label="IČO" type="text" />
                      <BaseFormInput v-model="item.dic" name="dic" label="DIČ" type="text" />

                      <div class="col-span-full my-2 h-px bg-slate-100"></div>

                      <BaseFormInput
                        v-model="item.street"
                        name="street"
                        label="Ulice a číslo popisné"
                        type="text"
                        class="col-span-full"
                      />

                      <BaseFormInput v-model="item.city" name="city" label="Město" type="text" />
                      <BaseFormInput v-model="item.zip" name="zip" label="PSČ" type="text" />

                      <BaseFormSelect
                        v-model="item.country_id"
                        name="country_id"
                        label="Země"
                        :options="countryStore.countriesOptions"
                        class="col-span-full"
                      />

                      <BaseFormTextarea
                        v-model="item.note"
                        name="note"
                        label="Poznámka"
                        class="col-span-full"
                        rows="3"
                      />
                    </div>
                  </div>

                  <div class="mt-10 flex flex-col gap-3 sm:flex-row-reverse sm:justify-start">
                    <BaseButton type="submit" variant="success" size="lg" class="w-full sm:w-auto">
                      Uložit
                    </BaseButton>
                    <BaseButton
                      ref="cancelButtonRef"
                      type="button"
                      variant="secondary"
                      size="lg"
                      class="w-full sm:w-auto"
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
