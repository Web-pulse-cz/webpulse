export function usePermissions(siteHashOverride?: Ref<string>) {
  const user = useSanctumUser();
  const injectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));
  const selectedSiteHash = siteHashOverride ?? injectedSiteHash;

  function getUserGroup() {
    if (!user?.value) return null;
    return (user.value as any).user_group ?? null;
  }

  function groupBelongsToSite(): boolean {
    const userGroup = getUserGroup();
    if (!userGroup) return false;

    // Superadmin group (ID 1) has access to all sites
    if (userGroup.id === 1) return true;

    if (userGroup.sites && userGroup.sites.length > 0 && selectedSiteHash.value) {
      return userGroup.sites.some((site: any) => site.hash === selectedSiteHash.value);
    }

    // Legacy groups without sites — allow access
    return true;
  }

  function moduleBelongsToSite(slug: string): boolean {
    if (!user?.value?.sites?.length) return true;

    const currentSite = user.value.sites.find((site: any) => site.hash === selectedSiteHash.value);

    if (
      currentSite &&
      currentSite.settings &&
      currentSite.settings.enabled_modules &&
      currentSite.is_active
    ) {
      return currentSite.settings.enabled_modules.includes(slug);
    }

    return false;
  }

  function canView(slug: string): boolean {
    if (!groupBelongsToSite()) return false;

    const userGroup = getUserGroup();
    if (!userGroup?.permissions) return false;

    const perm = userGroup.permissions.find((p: any) => p.slug === slug);

    return perm?.permissions?.view === true;
  }

  function canEdit(slug: string): boolean {
    if (!groupBelongsToSite()) return false;

    const userGroup = getUserGroup();
    if (!userGroup?.permissions) return false;

    const perm = userGroup.permissions.find((p: any) => p.slug === slug);

    return perm?.permissions?.edit === true;
  }

  function canDelete(slug: string): boolean {
    if (!groupBelongsToSite()) return false;

    const userGroup = getUserGroup();
    if (!userGroup?.permissions) return false;

    const perm = userGroup.permissions.find((p: any) => p.slug === slug);

    return perm?.permissions?.delete === true;
  }

  return {
    getUserGroup,
    groupBelongsToSite,
    moduleBelongsToSite,
    canView,
    canEdit,
    canDelete,
  };
}
