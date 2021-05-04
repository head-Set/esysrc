<template>
  <q-page padding class="flex flex-center">
    <q-card class="my-card" tag="form">
      <q-card-section align="center" class="bg-Color text-white">
        <div class="text-h6">Sign In</div>
        <q-space />
        <div class="text-h6">el-SERVICE-yu</div>
      </q-card-section>
      <q-card-section :padding="false" class="gt-sm" style="width: 30vw">
        <div class="q-pa-my">
          <q-input v-model="email" label="Email" label-color="black" borderless>
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>
        </div>

        <q-input
          v-model="password"
          label="Password"
          label-color="black"
          borderless
          :type="!isShow ? 'password' : 'text'"
        >
          <template v-slot:prepend>
            <q-icon name="lock" @click="isShow = true" />
          </template>
          <template v-slot:append>
            <q-btn
              class="txt-Color"
              :icon="isShow ? 'visibility_off' : 'visibility'"
              flat
              round
              @click="isShow = !isShow"
            />
          </template>
        </q-input>
      </q-card-section>
      <q-card-section class="lt-md" style="width: 95vw">
        <div class="q-pa-mb">
          <q-input v-model="email" label="Email" label-color="black" borderless>
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>
        </div>

        <q-input
          dense
          v-model="password"
          label="Password"
          label-color="black"
          borderless
          :type="!isShow ? 'password' : 'text'"
        >
          <template v-slot:prepend>
            <q-icon name="lock" @click="isShow = true" />
          </template>
          <template v-slot:append>
            <q-btn
              class="txt-Color"
              :icon="isShow ? 'visibility_off' : 'visibility'"
              flat
              round
              @click="isShow = !isShow"
            />
          </template>
        </q-input>
      </q-card-section>
      <q-card-actions align="center">
        <q-btn label="reset" color="red" type="reset" flat />
        <q-btn
          raised
          label="Submit"
          class="bg-Color text-white"
          type="submit"
          @click="login"
        />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script>
import { emailRules, blankChecker } from "../js/checkers";
export default {
  name: "SignIn",
  meta: {
    title: "ESY ADMIN",
  },
  data() {
    return {
      isShow: "",
      email: "",
      password: "",
    };
  },
  methods: {
    async login() {
      const emailCheck = emailRules(this.email);
      const valid = blankChecker([this.email, this.password]);
      if (emailCheck) {
        if (valid) {
          await this.$store.dispatch("Auth/login", {
            uOe: this.email,
            pass: this.password,
          });
          return;
        }
        this.$q.notify({
          color: "negative",
          textColor: "white",
          icon: "error",
          message: "Fill In All Inputs",
        });
        return;
      }
      this.$q.notify({
        color: "negative",
        textColor: "white",
        icon: "error",
        message: "Invalid Email",
      });
      return;
    },
  },
};
</script>