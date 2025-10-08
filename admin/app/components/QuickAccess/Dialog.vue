<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Form } from 'vee-validate';
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const showDeleteDialog = ref(false);
const deleteDialogItem = ref(null);

const show = defineModel('show', {
  type: Boolean,
  default: false,
});
const form = defineModel('form', {
  type: Object,
  default: {
    id: null as number | null,
    name: '' as string,
    link: '' as string,
    target: '_self' as string,
  },
});

const { refreshIdentity } = useSanctumAuth();

async function submitForm() {
  const client = useSanctumClient();

  await client<{
    id: null;
    name: string;
    link: string;
    target: string;
  }>(
    form.value.id == null ? '/api/admin/quick-access' : '/api/admin/quick-access/' + form.value.id,
    {
      method: 'POST',
      body: JSON.stringify(form.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then(() => {
      toast.add({
        summary: 'Hotovo',
        detail:
          'Položka rychlého přístupu byla úspěšně ' +
          (form.value.id == null ? 'přidána' : 'upravena') +
          '.',
        severity: 'succcess',
        group: 'bc',
      });
      refreshIdentity();
    })
    .catch(() => {
      toast.add({
        summary: 'Chyba',
        detail:
          'Nepodařilo se ' +
          (form.value.id == null ? 'přidat' : 'upravit') +
          ' položku rychlého přístupu.',
        severity: 'error',
        group: 'bc',
      });
    })
    .finally(() => {
      showDeleteDialog.value = false;
      show.value = false;
    });
}
async function deleteItem() {
  const client = useSanctumClient();

  await client<{
    id: null;
    name: string;
    link: string;
    target: string;
  }>('/api/admin/quick-access/' + form.value.id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      toast.add({
        summary: 'Hotovo',
        detail: 'Položka rychlého přístupu byla úspěšně smazána.',
        severity: 'succcess',
        group: 'bc',
      });
      refreshIdentity();
    })
    .catch(() => {
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku rychlého přístupu.',
        severity: 'error',
        group: 'bc',
      });
    })
    .finally(() => {
      showDeleteDialog.value = false;
      show.value = false;
    });
}
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
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
              >
                <Form @submit="submitForm">
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle
                        as="h3"
                        class="mb-4 text-sm font-semibold text-grayDark lg:mb-6 lg:text-base"
                      >
                        {{
                          form.id == null
                            ? 'Přidat položku rychlého přístupu'
                            : 'Upravit položku rychlého přístupu'
                        }}
                      </DialogTitle>
                      <div class="mt-6 grid w-full grid-cols-1 gap-y-4">
                        <div class="col-span-full">
                          <BaseFormInput
                            v-model="form.name"
                            name="name"
                            rules="required"
                            label="Název"
                            type="text"
                          />
                        </div>
                        <div class="col-span-full">
                          <BaseFormInput
                            v-model="form.link"
                            name="link"
                            rules="required"
                            label="Odkaz"
                            type="text"
                          />
                        </div>
                        <div class="col-span-full">
                          <BaseFormSelect
                            v-model="form.target"
                            name="target"
                            rules="required"
                            label="Odkaz otveřít"
                            :options="[
                              { value: '_self', name: 'Ve stejném okně' },
                              { value: '_blank', name: 'V novém okně' },
                            ]"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    class="mt-4 flex justify-end gap-x-4 lg:mt-6 lg:flex-row-reverse lg:justify-start"
                  >
                    <BaseButton type="submit" variant="success" size="lg">
                      {{ form.id == null ? 'Přidat' : 'Uložit' }}
                    </BaseButton>
                    <BaseButton
                      v-if="form.id !== null"
                      type="button"
                      variant="danger"
                      size="lg"
                      @click="
                        showDeleteDialog = true;
                        deleteDialogItem = form;
                      "
                    >
                      Odstranit
                    </BaseButton>
                    <BaseButton
                      ref="cancelButtonRef"
                      type="button"
                      variant="secondary"
                      size="lg"
                      class="ml-4"
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
    <BaseDialogDelete
      v-model:show="showDeleteDialog"
      v-model:item="deleteDialogItem"
      @delete-item="deleteItem"
    />
  </div>
</template>
