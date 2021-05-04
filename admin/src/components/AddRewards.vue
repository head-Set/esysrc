<template>
  <q-dialog v-model="openDialog" persistent>
    <q-card class="my-card">
      <q-card-section class="bg-Color text-white">
        <div class="text-h6">Add Reward</div>
      </q-card-section>
      <q-card-section>
        <q-input
          v-model="reward"
          :rules="[basicRule]"
          lazy-rules
          type="text"
          hint="max 20 Character"
          color="teal"
          debounce="1000"
          label="Reward Item"
          dense
          outlined
        />
        <q-input
          v-model="desc"
          :rules="[basicRule]"
          lazy-rules
          type="textarea"
          hint="max 20 Character"
          color="teal"
          debounce="1000"
          label="Reward Description"
          dense
          outlined
        />
        <div class="row justify-center">
          <q-input
            class="col"
            v-model="points"
            :rules="[basicRule]"
            lazy-rules
            type="number"
            color="teal"
            debounce="1000"
            label="Rewuired Points"
            dense
            borderless
          />
          <q-input
            class="col"
            v-model="stock"
            :rules="[basicRule]"
            lazy-rules
            type="number"
            color="teal"
            debounce="1000"
            label="Stocks"
            dense
            borderless
          />
        </div>
      </q-card-section>
      <q-card-actions align="center">
        <q-btn flat label="Close" @click="closeDialog" />
        <q-btn flat label="Submit" @click="submitReward" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: "AddReward",
  props: {
    openDialog: Boolean,
    selectedReward: Object,
  },
  mounted(){
    console.log(Object.keys(this.selectedReward).length)
  },
  data() {
    return {
      reward: "",
      desc: "",
      points: 0,
      stock: 0,
    };
  },

  watch: {
    selectedReward(val) {
      let self = this;
      if (val) {
        self.reward = val.item;
        self.desc = val.description;
        self.points = val.points;
        self.stock = val.stock;
        return;
      }
    },
  },
  methods: {
    submitReward() {
      const data = {
        item: this.reward,
        desc: this.desc,
        points: this.points,
        stock: this.stock,
      };
      if(Object.keys(this.selectedReward).length==0){
        this.add(data);
      }else{
        this.update(data);
      }
    },
    add(data) {
      // Loading.show();
      this.$axios
        .post("/addRewards", data)
        .then(
          ({ data }) => {
            if (data.code === 200) {
              this.$q.notify({
                type: "positive",
                color: "positive",
                textColor: "white",
                icon: "done",
                message: data.message,
                caption: `${data.data} Reward Added`,
              });
              return;
            }
            if (data.code === 400) {
              this.$q.notify({
                color: "negative",
                textColor: "white",
                icon: "error",
                message: "Something went wrong!",
              });
              return;
            }
          },
          (e) => {
            this.$q.notify({
              color: "negative",
              textColor: "white",
              icon: "error",
              message: "Something went wrong!",
            });
          }
        )
        .catch((e) => {
          this.$q.notify({
            color: "negative",
            textColor: "white",
            icon: "error",
            message: "Something went wrong!",
          });
        })
        .then(async () => {
          // Loading.hide();
          await this.$store.dispatch("Rewards/fetchRewards");
        });
    },
    update(data) {
      data['id'] = this.selectedReward.id;
      // Loading.show();
      this.$axios
        .post(`/rewards/${data.id}`, data)
        .then(
          ({ data }) => {
            if (data.code === 200) {
              this.$q.notify({
                type: "positive",
                color: "positive",
                textColor: "white",
                icon: "done",
                message: data.message,
                caption: `${data.data} Reward Updated`,
              });
              return;
            }
            if (data.code === 400) {
              this.$q.notify({
                color: "negative",
                textColor: "white",
                icon: "error",
                message: data.message,
              });
              return;
            }
          },
          (e) => {
            this.$q.notify({
              color: "negative",
              textColor: "white",
              icon: "error",
              message: e,
            });
          }
        )
        .catch((e) => {
          this.$q.notify({
            color: "negative",
            textColor: "white",
            icon: "error",
            message: e,
          });
        })
        .then(async () => {
          // Loading.hide();
          await this.$store.dispatch("Rewards/fetchRewards");
        });
      return;
    },
    closeDialog() {
      let dialog = this.openDialog;
      dialog = false;
      this.$emit("callback", false);
    },
    basicRule(val) {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          resolve(!!val || "Required!");
        }, 1000);
      });
    },
  },
};
</script>
