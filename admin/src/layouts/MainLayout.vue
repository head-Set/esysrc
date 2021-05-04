<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
      <q-toolbar class="bg-Color">
        <q-btn
          flat
          dense
          raised
          round
          icon="menu"
          aria-label="Menu"
          @click="leftDrawerOpen = !leftDrawerOpen"
        />
          <!-- :icon="leftDrawerOpen ? 'arrow_left' :'arrow_right'" -->

        <q-toolbar-title>
          El-Service-Yu
        </q-toolbar-title>

        <div>
          <q-btn label="Log Out" @click="logout" />
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      content-class="bg-grey-1 q-pt-lg"
    >
      <q-list :padding="true" >
        <EssentialLink
          v-for="link in essentialLinks"
          :key="link.title"
          v-bind="link"
        />
      </q-list>
    </q-drawer>

    <q-page-container class="bg-grey-3">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import EssentialLink from 'components/EssentialLink.vue'

const linksData = [
  {
    title: 'Dashboard',
    icon: 'home',
    link: '/home'
  },
  {
    title: 'Providers',
    icon: 'group',
    link: '/providers'
  },
  {
    title: 'Services',
    icon: 'chat',
    link: '/services'
  },
  {
    title: 'Admins',
    icon: 'group',
    link: '/admins'
  },
  {
    title: 'Rewards Offered',
    icon: 'reward',
    link: '/rewards'
  },
];

export default {
  name: 'MainLayout',
  components: { EssentialLink },
  data () {
    return {
      leftDrawerOpen: false,
      essentialLinks: linksData
    }
  },
  methods:{
    logout(){
      this.$store.dispatch('Auth/logout')
    }
  }
}
</script>
