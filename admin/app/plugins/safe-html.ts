import type { DirectiveBinding } from 'vue';

export default defineNuxtPlugin((nuxtApp) => {
  const apply = (el: HTMLElement, binding: DirectiveBinding) => {
    el.innerHTML = sanitizeHtml(binding.value);
  };

  nuxtApp.vueApp.directive('safe-html', {
    mounted: apply,
    updated(el: HTMLElement, binding: DirectiveBinding) {
      if (binding.value !== binding.oldValue) apply(el, binding);
    },
    getSSRProps(binding: DirectiveBinding) {
      return { innerHTML: sanitizeHtml(binding.value) };
    },
  });
});
