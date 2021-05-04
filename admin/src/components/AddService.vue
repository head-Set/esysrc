<template>
  <q-dialog v-model="openDialog" persistent>
    <q-card class="addServiceFormgtsm">
      <q-card-section class="bg-Color text-white">
        <div class="text-h6">Add Service</div>
      </q-card-section>
      <q-card-section>
        <q-input v-model="service" type="text" label="Service" />
      </q-card-section>
      <q-card-actions align="center">
        <q-btn flat label="Add" color="teal" @click="submit" />
        <q-btn flat color="red" label="Close" @click="closeDialog" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import { send } from "../boot/axios";
export default {
  name: "AddService",
  props: {
    openDialog: Boolean,
  },
  data() {
    return {
      service: "",
    };
  },
  methods: {
    submit() {
      if (this.service.length < 1) {
        this.$q.notify({
          color: "negative",
          textColor: "white",
          icon: "error",
          message: "Invalid Input!",
        });
        return;
      }
      this.$axios
        .post("/addService", { service_type: this.service })
        .then(({ data }) => {
          if (data.code == 200) {
            this.$store.dispatch("Services/fetchServices");
            this.$q.notify({
              color: "positive",
              textColor: "white",
              icon: "done",
              message: "New Service Added",
            });
            return;
          }
          this.$q.notify({
            color: "negative",
            textColor: "white",
            icon: "error",
            message: data.message,
          });
        })
        .catch((e) => {})
        .then(() => {});
    },
    closeDialog() {
      let dialog = this.openDialog;
      dialog = false;
      this.$emit("callback", false);
    },
  },
};
</script>
