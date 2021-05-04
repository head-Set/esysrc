<template>
  <q-layout view="hHh Lpr fFf"> <!-- Be sure to play with the Layout demo on docs -->

    <!-- (Optional) The Header -->
    <q-header elevated class="bg-Color">
      <!-- <q-toolbar>
        <q-toolbar-title>
          El-Service-Yu
        </q-toolbar-title>
      </q-toolbar> -->
    </q-header>

    <!-- (Optional) The Footer -->
    <q-footer class="bg-Color">
    </q-footer>

    <q-page-container>
      <!-- This is where pages get injected -->
      <router-view />
    </q-page-container>

  </q-layout>
</template>

<script>
export default {
  // name: 'LayoutName',

  data () {
    return {
      leftDrawer: true
    }
  }
}
</script>
