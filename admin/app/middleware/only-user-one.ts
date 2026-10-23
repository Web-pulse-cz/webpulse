export default defineNuxtRouteMiddleware(() => {
  const user = useSanctumUser<{ id: number }>();
  if (user.value?.id !== 1) {
    return navigateTo('/');
  }
});
