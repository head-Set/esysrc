<template>
  <q-dialog v-model="isOpen" position="top" persistent>
    <q-card style="width: 350px">
      <q-card-section class="">
        <q-card>
          <q-img
            src="~assets/person.png"
            :ratio="16 / 9"
            spinner-color="teal"
            spinner-size="82px"
            contain
          >
            <template v-slot:error>
              <div
                class="absolute-full flex flex-center bg-negative text-white"
              >
                Cannot load image
              </div>
            </template>
          </q-img>
          <q-card-section class="bg-Color text-white text-center">
            <strong>{{ provider.name }}</strong>
          </q-card-section>
          <q-separator />
          <q-card-section class="bg-Color text-white">
            <div>Email: {{provider.email}}</div>
            <div>Contact: {{provider.contact}}</div>
            <div>Date Joined: {{provider.dateJoined}}</div>
            <div>Current Reward Points: {{provider.rewardspoint}}</div>
            <div>Total Stars: {{provider.stars}}</div>
            <div>Provider Type: {{provider.type}}</div>
            <div>Service to Provide: {{provider.serviceToProvide}}</div>
          </q-card-section>
          <q-card-actions align="center" class="q-pa-sm">
            <q-btn class="full-width" flat @click="clean"
              >close <template v-slot:loading> <q-spinner-gears /> </template
            ></q-btn>
          </q-card-actions>
        </q-card>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: "ProviderDetailes",
  props: {
    openDialog: Boolean,
    providerId: Number,
  },
  mounted() {},
  watch: {
    openDialog(val) {
      //test ko kung pwde ko ibukas na kpag meron o wala manag ung this.provider
      if (val) {
        this.$store.dispatch("Providers/fetchOneProvider", this.providerId);
      }
      if (!val) this.$store.dispatch("Providers/clearProvider");
    },
    provider(val) {
      const valid = Object.keys(val).length;
      if (valid) {
        this.isOpen = true;
      }
      if (!valid) {
        this.$q.notify({
          color: "negative",
          textColor: "white",
          icon: "error",
          message: "Something went wrong!",
        });
      }
    },
  },
  computed: {
    provider() {
      return this.$store.state.Providers.provider;
    },
  },
  data() {
    return {
      isOpen: false,
    };
  },
  methods: {
    clean() {
      let dialog = this.openDialog;
      let id = this.providerId;
      dialog = false;
      id = 0;
      this.isOpen = false;
      this.$emit("callback", false);
    },
  },
};
</script>
