<script setup lang="ts">
import type { Post } from '~~/types/Post';

export interface Props {
  posts: Post[];
}

const props = defineProps<Props>();
</script>

<template>
<section class="px-6 lg:px-20 py-20 bg-background-light">
<div class="max-w-[1400px] mx-auto">
  <h2 class="text-6xl font-black text-deep-blue font-display mb-16 text-center italic">Latest Articles</h2>
  <div class="masonry-grid">
    <BlogPostCard v-for="(post, index) in posts" :key="index" :index="index" :post="post" />
    <article class="masonry-item group cursor-pointer">
      <div class="relative bg-white border-4 border-deep-blue rounded-[2.5rem] overflow-hidden shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-2 hover:translate-y-2 transition-all">
        <div class="aspect-[4/3] overflow-hidden">
          <img alt="Modern bright office workspace with laptop"
               class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700"
               src="https://lh3.googleusercontent.com/aida-public/AB6AXuC6eNr-ye-7t0x4BL7QCgVLY10DvXrFfZQDCn_VOtwZ84uCD0g3lNoz7k61oK7h1A-Gp-UXSdaK5hWZurS3nXFkE0HKLUoK2y_ZrzF_IO4sR2Hw7SUEtlMkMSZQvcktu7EiskbiVsiJjMFybdgaS5Gnyx5r38DJRTPQDaPNPCnKSax-qpWbjmtQOTbdvlzNpMZ8f_IGQr2Dxb8vdStvFObywpFfLAARmvgZJw5yKcRfOx2V1mo9vrtVIGeLJYR_QMP4PL794AvkT8I"/>
        </div>
        <div class="p-8">
          <span class="inline-block bg-primary text-white text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4">Work</span>
          <h3 class="text-3xl font-black text-deep-blue font-display leading-tight mb-4 group-hover:text-primary transition-colors">
            The Future of Remote Work is Hybrid</h3>
          <p class="text-deep-blue/70 font-medium mb-6">Explore how companies are adapting to a new
            normal, balancing office culture with the flexibility of working from home.</p>
          <div class="flex items-center gap-4 pt-6 border-t-2 border-dotted border-deep-blue/20">
            <img alt="Portrait of Sarah Jenkins"
                 class="size-12 rounded-blob object-cover border-2 border-primary"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuCyxKtAiVUPKjkhRMs9AS7fkuyunTrlftwZb730DuvtoJt0NhuvtARA7FLsVRYvoe-DQZhBBdY0Rg--WdP0Z-wmz5qdFFAaL7VnIW9wTMNMNiybL1vIgVUlvM2C1x5jCRqut2rU2byJK2syCoIl9FKiLUhY8Ej4_DdT0F7t0BSOpCExlNGSvHFoxvGiIAc3jZQO3pkebf-acMDgYR1YTeTL7gSzkUMxxYDuhqH7cYRYlClddaYX2KCgw30SnHdAQeVjad6LqSxOOF8"/>
            <div class="flex flex-col">
              <span class="text-sm font-black text-deep-blue">Sarah Jenkins</span>
              <span class="text-[10px] uppercase font-bold text-deep-blue/40 tracking-tighter">Oct 24 • 5 min read</span>
            </div>
          </div>
        </div>
      </div>
    </article>
    <article class="masonry-item group cursor-pointer">
      <div class="relative bg-turquoise/5 border-4 border-deep-blue rounded-[3rem] overflow-hidden shadow-[8px_8px_0px_0px_rgba(78,205,196,1)] hover:shadow-none hover:translate-x-2 hover:translate-y-2 transition-all">
        <div class="aspect-[1/1] overflow-hidden">
          <img alt="Minimalist living room with plants"
               class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700"
               src="https://lh3.googleusercontent.com/aida-public/AB6AXuCFtk5LfJWQ0WDRs_pQ0lQgKuM7Zj5g7RkHkckKFcbWemW3zVXvYeFM1wU0R08F_sbxqQd6GVWNskm6ntMcZR-BHkNGw86nSM9mQjUtjBaYErg3p_1_Wk5AT6Q6suX1i6I-FLftBU_dN14XEc61W5h2WwLGMs4zCHyfnS15KAT-lF-jSF79Y_BmoAOtdpv-VeqHG9M9qzX6XnzLiQSnAxCXXtBlZWHNw-JJGqV48LL7HI8cPKenJLYeS8uoTlJj744c4PkosI2REIE"/>
        </div>
        <div class="p-8">
          <span class="inline-block bg-deep-blue text-white text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4">Lifestyle</span>
          <h3 class="text-3xl font-black text-deep-blue font-display leading-tight mb-4 group-hover:text-turquoise transition-colors">
            Embracing Minimalism at Home</h3>
          <p class="text-deep-blue/70 font-medium mb-6">Less is more. Discover practical tips to
            declutter your living space and create a serene environment for better mental
            health.</p>
          <div class="flex items-center gap-4 pt-6 border-t-2 border-dotted border-deep-blue/20">
            <img alt="Portrait of Mark Doe"
                 class="size-12 rounded-blob object-cover border-2 border-turquoise"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuBp2uks4JlS7s574jMTCAtK22gP_8n_f5upeR4ERZ6LEkf5cIxEeRJ1sed9uh_Nmg3I7FDdSUP0aRDOXobnYDg6iddEUrC5scm93pcYg4MLoyW2m6hdUtpUD1Y-56-pp3FDeDngHtdGYvabPMd0-1Wx8GnZyd1I2ClOvgBiNSuFxrfWnulNwWn8Jbnw8SY2EvGaVSX80qsoOBhTiXIGRfcPCHswlpAqZfTK65WsgUH7sFAwGPBLxaJT37k-sfYpXyQ-wvyjWPbHwUI"/>
            <div class="flex flex-col">
              <span class="text-sm font-black text-deep-blue">Mark Doe</span>
              <span class="text-[10px] uppercase font-bold text-deep-blue/40 tracking-tighter">Oct 22 • 4 min read</span>
            </div>
          </div>
        </div>
      </div>
    </article>
    <article class="masonry-item group cursor-pointer">
      <div class="relative bg-sunny/10 border-4 border-deep-blue rounded-[2.5rem] overflow-hidden shadow-[8px_8px_0px_0px_rgba(255,230,109,1)] hover:shadow-none hover:translate-x-2 hover:translate-y-2 transition-all">
        <div class="aspect-[3/4] overflow-hidden">
          <img alt="Gourmet healthy salad dish"
               class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700"
               src="https://lh3.googleusercontent.com/aida-public/AB6AXuDDGd6_Ln76167CwcORpQeYsb7PEHR6H6D-a1Nr6xynETRFC-cVxIWPwqdBzrNl52sA5F2hq3le3kq8rXiCSBg6diQ22NHjzKN5AI8EZyRf-z_mYaEcilqfy04H3SK_Pzd3r50RtnPXPfn5egImVrGkQ4NWtOzhBYODyGIcx4HltZKYILLEe7If4WkDu15WUJFJoeRoXewIv0ViC6PeFPy0yHKxDgCwvRSFD8ptrF3HXEMWrfguFvveIy2FPAs9OLeABxUEMIpeIEg"/>
        </div>
        <div class="p-8">
          <span class="inline-block bg-primary text-white text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4">Food</span>
          <h3 class="text-3xl font-black text-deep-blue font-display leading-tight mb-4 group-hover:text-primary transition-colors">
            Seasonal Eating: Fall Edition</h3>
          <p class="text-deep-blue/70 font-medium mb-6">A guide to the best produce available this
            autumn and delicious recipes to warm up your kitchen.</p>
          <div class="flex items-center gap-4 pt-6 border-t-2 border-dotted border-deep-blue/20">
            <img alt="Portrait of Emily Chen"
                 class="size-12 rounded-blob object-cover border-2 border-primary"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuClX4-kUZkA4Vfgazhz8tQYKd-hmR5d42TteYEBpTWfepOyNCkJd2umCJQVajEqdNSRG1oYrMQdcgFSa4v6u8BIHTYfi4sG08L9Og8_RMel82GMgps8k4M2YK4PebSPs8SL69fZR2leYIXCyBTjCwR-iISXdhBkSgJmFtQLdY7DGA4ixbUZ4ezqudsdns_NeCIxkyT5PeR2gix2ohwwORICWpR8CSjVisavAlNLJDMulkFAfCOP48AFiBC5dU8mcortFc5Wl917asE"/>
            <div class="flex flex-col">
              <span class="text-sm font-black text-deep-blue">Emily Chen</span>
              <span class="text-[10px] uppercase font-bold text-deep-blue/40 tracking-tighter">Oct 20 • 6 min read</span>
            </div>
          </div>
        </div>
      </div>
    </article>
    <article class="masonry-item group cursor-pointer">
      <div class="relative bg-white border-4 border-deep-blue rounded-[3rem] overflow-hidden shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-2 hover:translate-y-2 transition-all">
        <div class="aspect-[16/9] overflow-hidden">
          <img alt="Students studying in a library"
               class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700"
               src="https://lh3.googleusercontent.com/aida-public/AB6AXuA5fqu8NumBzNeZW0EOmC0-ihVfv2Z5syrFJXXS_e3Z6kvpVDRWeDnJ2AQ2ZC-VBPNIf2lvD5m9QT-yCpjWKB6_lYSZxQuysY-KCMka_0EzaHFr_qXojYC0t7FdUGq0RYZ-Dj5n1jvo0BX_2p0S5hdlGaNuOmvBxZR6JKpjG4Afj2_q2e7xIy5OFVJyf7yiGnPWXXWIPYNtL84aRrOcgqsMsW1yxOQC0Zp9lpF4yiDHw_1Z5Fwo6RnneZbqH2J7jznrHAU01a7qXHQ"/>
        </div>
        <div class="p-8">
          <span class="inline-block bg-turquoise text-white text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4">Education</span>
          <h3 class="text-3xl font-black text-deep-blue font-display leading-tight mb-4">Top Learning
            Strategies for 2024</h3>
          <p class="text-deep-blue/70 font-medium mb-6">Cognitive science backed methods to improve
            retention and make your study sessions more effective.</p>
          <div class="flex items-center gap-4 pt-6 border-t-2 border-dotted border-deep-blue/20">
            <img alt="Portrait of Alex Ray"
                 class="size-12 rounded-blob object-cover border-2 border-turquoise"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIIrzXioEYIv2TMsUuM_qZr38a61Yxi3D7EpwjdXsPd-TyeJ9lJfp--pwGOdtd9XxbP38yHOq4BXBaF1wYMyg65xEYluKxM-r-zkWvDgxca7czB3yrxhYI0TjJJrq-eq6HdPpU59_QkVdxuE9oo7bUZ3WQekPqgDHszf8_Nxu3Xmun6NS_SKDlNVi4EYwTnDwKZxO9R7tv6pBJ98vYbrI5x4MqoN12f8vlwrBto4tcGEM_QLMp5Ua7Co-lWuuXUv5DGi8fuJsSHTY"/>
            <div class="flex flex-col">
              <span class="text-sm font-black text-deep-blue">Alex Ray</span>
              <span class="text-[10px] uppercase font-bold text-deep-blue/40 tracking-tighter">Oct 18 • 8 min read</span>
            </div>
          </div>
        </div>
      </div>
    </article>
    <article class="masonry-item group cursor-pointer">
      <div class="relative bg-primary/5 border-4 border-deep-blue rounded-[2.5rem] overflow-hidden shadow-[8px_8px_0px_0px_rgba(255,107,107,1)] hover:shadow-none hover:translate-x-2 hover:translate-y-2 transition-all">
        <div class="aspect-[4/5] overflow-hidden">
          <img alt="Person standing on a mountain peak looking at view"
               class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700"
               src="https://lh3.googleusercontent.com/aida-public/AB6AXuCXUsuIh9tbJybR0hTr7JeuQMzrgJQISTkkKStmDpjVLC9YYJSRFC0DLJ6kIYFg2FBsJH6BnQjXCFmXTCkf3QGVOtucf0aSkqBILlJhyTeSmMPVms4NQtKvqv54xFOCbbkK98A7MyAVfK9XxoMzvo6T3D5kXPet1BluBiOYsoxTI2gqeVIPtPjjRsm8bYNoPwOc6RwvghtM7zkVUs-TY2GAlkOxjncf65LnpRAZS1MnhUsSR1ueJf8TYFGHlMhm7Vy3BZtRxcnc0yQ"/>
        </div>
        <div class="p-8">
          <span class="inline-block bg-primary text-white text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4">Travel</span>
          <h3 class="text-3xl font-black text-deep-blue font-display leading-tight mb-4">Solo Travel:
            A Beginner's Guide</h3>
          <p class="text-deep-blue/70 font-medium mb-6">Why traveling alone can be the most rewarding
            experience of your life, and how to stay safe.</p>
          <div class="flex items-center gap-4 pt-6 border-t-2 border-dotted border-deep-blue/20">
            <img alt="Portrait of Jessica Park"
                 class="size-12 rounded-blob object-cover border-2 border-primary"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuAzD7F90KYjdpgWzntIO1rFEoLMi31GsUxL5RW4Li5CWPIYRiw7-I2AR9MzjyQSiYl5HAA8RmqWAZlEy5hdiSZTVdpRomMaZGPxbfp5PbpUpWZcd_8qIT8GUPhy8ekMrf3cmi1OAzS_EdQf2T6QIIt0FKHuF5EhyQKjvZLsew0zIvbnbazxDfUle_8WkXxqgAM_kW9VfsalNURrZPMw7f9c9yWkMKwuzRrcytjb09BLQQ4sifpzf3A650oh4tyQI0DXuqY38LFx3vg"/>
            <div class="flex flex-col">
              <span class="text-sm font-black text-deep-blue">Jessica Park</span>
              <span class="text-[10px] uppercase font-bold text-deep-blue/40 tracking-tighter">Oct 15 • 7 min read</span>
            </div>
          </div>
        </div>
      </div>
    </article>
    <article class="masonry-item group cursor-pointer">
      <div class="relative bg-white border-4 border-deep-blue rounded-[3rem] overflow-hidden shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-2 hover:translate-y-2 transition-all">
        <div class="aspect-[4/3] overflow-hidden">
          <img alt="People collaborating in a modern meeting room"
               class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700"
               src="https://lh3.googleusercontent.com/aida-public/AB6AXuBRLFiV57LSha9D-jdfypnURvMCRnzYuMqYFFMsOsGrff_hpcsjAqrDJpcCTUoCdENCrrOrKIODhfpNa7Hb_J1unJpTC8K5AIoPIiam3RNRPrAT2eZSBwws1NvJ1C-t3QMZpPb6_BYLPNjJeLzBhWZEK0GJnFwsr2js2cwYJ8QlbOhMAbnfUnipYnbI90C64Q28p2NnVQN8t1GVEI1qDPrCCSMEkJ_6baD85X72lPv6_kvYYv-00OcLEej0w5LrPjTkXiMDmt7j0bE"/>
        </div>
        <div class="p-8">
          <span class="inline-block bg-deep-blue text-white text-xs font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4">Business</span>
          <h3 class="text-3xl font-black text-deep-blue font-display leading-tight mb-4">Startups to
            Watch in Q4</h3>
          <p class="text-deep-blue/70 font-medium mb-6">A curated list of innovative companies
            disrupting their industries this quarter.</p>
          <div class="flex items-center gap-4 pt-6 border-t-2 border-dotted border-deep-blue/20">
            <img alt="Portrait of David Kim"
                 class="size-12 rounded-blob object-cover border-2 border-deep-blue"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuBNpvXw6Xbdat8ShdbiYL10q__yGRN0W029IKdmDBzAkt_55KCY2A-McYyX90GvMxrBrNgbYsy_ikeRopahouqVkcTeygeeToFYzVqougDAeMoz4lmZwBs31lFgoBi9_Kb2Y7vyTtMghG31wDKw7LZfoFJ_qaDhiwaW3b4aPJG9tgXyh-qcgRGo64yHQUDVicu4roKQy356E5_j4txjUU5nyOQcrrbwJFeP0yKFVcWS5gpttj0WAYW_cXlUsFBt484KdmIn_0ltg5w"/>
            <div class="flex flex-col">
              <span class="text-sm font-black text-deep-blue">David Kim</span>
              <span class="text-[10px] uppercase font-bold text-deep-blue/40 tracking-tighter">Oct 12 • 4 min read</span>
            </div>
          </div>
        </div>
      </div>
    </article>
  </div>
  <div class="mt-20 flex justify-center">
    <button class="group px-12 py-5 rounded-full border-4 border-deep-blue bg-sunny text-deep-blue font-black text-xl shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-2 hover:translate-y-2 transition-all flex items-center gap-4">
      <span>Load More Articles</span>
      <span class="material-symbols-outlined font-black group-hover:rotate-180 transition-transform duration-500">add</span>
    </button>
  </div>
</div>
</section>
</template>