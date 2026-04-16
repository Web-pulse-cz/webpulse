export function usePermissions(siteHashOverride?: Ref<string>) {
  const user = useSanctumUser();
  const injectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));
  const selectedSiteHash = siteHashOverride ?? injectedSiteHash;

  function getUserGroup() {
    if (!user?.value) return null;
    return (user.value as any).user_group ?? null;
  }

  function isSuperAdmin(): boolean {
    const userGroup = getUserGroup();
    return userGroup?.id === 1;
  }

  function groupBelongsToSite(): boolean {
    if (isSuperAdmin()) return true;

    const userGroup = getUserGroup();
    if (!userGroup) return false;

    if (userGroup.sites && userGroup.sites.length > 0 && selectedSiteHash.value) {
      return userGroup.sites.some((site: any) => site.hash === selectedSiteHash.value);
    }

    // Legacy groups without sites — allow access
    return true;
  }

  function moduleBelongsToSite(slug: string): boolean {
    // Superadmin has access to all modules on all sites
    if (isSuperAdmin()) return true;

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
    // Superadmin can view everything
    if (isSuperAdmin()) return true;

    if (!groupBelongsToSite()) return false;

    const userGroup = getUserGroup();
    if (!userGroup?.permissions) return false;

    const perm = userGroup.permissions.find((p: any) => p.slug === slug);

    return perm?.permissions?.view === true;
  }

  function canEdit(slug: string): boolean {
    // Superadmin can edit everything
    if (isSuperAdmin()) return true;

    if (!groupBelongsToSite()) return false;

    const userGroup = getUserGroup();
    if (!userGroup?.permissions) return false;

    const perm = userGroup.permissions.find((p: any) => p.slug === slug);

    return perm?.permissions?.edit === true;
  }

  function canDelete(slug: string): boolean {
    // Superadmin can delete everything
    if (isSuperAdmin()) return true;

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
