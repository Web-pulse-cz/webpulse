<script setup lang="ts">
import BenefitCard from '~/components/Home/BenefitCard.vue';

const { t } = useI18n();
</script>

<template>
  <LayoutContainer>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_2fr]">
      <div class="flex items-center justify-center">
        <HomeBenefitCard
          :title="t('benefitCard.pasportization.title')"
          :description="t('benefitCard.pasportization.description')"
          image="static/img/cards/FacilityManagment.svg"
          link="/sluzby"
        ></HomeBenefitCard>
      </div>
      <div class="mt-4 w-full md:mt-20">
        <Service8Buttons
          :name="[
            t('servicesButtons.reconstruction'),
            t('servicesButtons.technologicalPasportization'),
            t('servicesButtons.virtualTours'),
            t('servicesButtons.scanning3D'),
            t('servicesButtons.dronScanning'),
            t('servicesButtons.constructioPasportization'),
            t('servicesButtons.spatialPasportization'),
            t('servicesButtons.technicalPasportization'),
          ]"
          :links="[
            '/sluzby/rekonstrukce',
            '/sluzby/technologicka-pasportizace',
            '/sluzby/virtualni-prohlidky',
            '/sluzby/3d-skenovani',
            '/sluzby/skenovani-dronem',
            '/sluzby/stavebni-pasportizace',
            '/sluzby/prostorova-pasportizace',
            '/sluzby/technicka-pasportizace',
          ]"
        ></Service8Buttons>
      </div>
    </div>
  </LayoutContainer>

  <LayoutContainer>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_2fr]">
      <div class="flex items-center justify-center">
        <HomeBenefitCard
          :title="t('benefitCard.engineering.title')"
          :description="t('benefitCard.engineering.description')"
          image="static/img/cards/FacilityManagment.svg"
          link="/sluzby"
        ></HomeBenefitCard>
      </div>
      <div class="mt-4 w-full md:mt-20">
        <Service8Buttons
          :name="[
            t('servicesButtons.reconstruction'),
            t('servicesButtons.HVCAAudit'),
            t('servicesButtons.constructionProjectManagement'),
            t('servicesButtons.energyManagement'),
            t('servicesButtons.designAndArchitecture'),
            t('servicesButtons.designEngeneering'),
            t('servicesButtons.structuralAssessments'),
            t('servicesButtons.geodeticWork'),
          ]"
          :links="[
            '/sluzby/rekonstrukce',
            '/sluzby/audit-tzb',
            '/sluzby/rizeni-stavebnich-projektu',
            '/sluzby/energeticky-management',
            '/sluzby/design-a-architecture',
            '/sluzby/projekcni-cinnost',
            '/sluzby/staticke-posudky',
            '/sluzby/geodeticke-prace',
          ]"
        ></Service8Buttons>
      </div>
    </div>
  </LayoutContainer>

  <LayoutContainer>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_2fr]">
      <div class="flex items-center justify-center">
        <HomeBenefitCard
          :title="t('benefitCard.facilityManagement.title')"
          :description="t('benefitCard.facilityManagement.description')"
          image="static/img/cards/FacilityManagment.svg"
          link="/sluzby"
        ></HomeBenefitCard>
      </div>
      <div class="mt-4 w-full md:mt-20">
        <Service8Buttons
          :name="[
            t('servicesButtons.complexFacilityManagement'),
            t('servicesButtons.reconstruction'),
            t('servicesButtons.propertyRentability'),
            t('servicesButtons.wasteManagement'),
            t('servicesButtons.CAFMSoftware'),
            t('servicesButtons.technologicalPasportization'),
            t('servicesButtons.virtualTours'),
            t('servicesButtons.passportSpatial'),
          ]"
          :links="[
            '/sluzby/komplexni-sprava-objektu',

            '/sluzby/rekonstrukce',
            '/sluzby/rentabilita-objektu',
            '/sluzby/odpadovy-managment',
            '/sluzby/cafm-software',
            '/sluzby/technologicka-pasportizace',
            '/sluzby/virtualni-prohlidky',
            '/sluzby/asd',
          ]"
        ></Service8Buttons>
      </div>
    </div>
  </LayoutContainer>
</template>

<style scoped></style>
