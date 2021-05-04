<template>
  <q-dialog v-model="openDialog" persistent>
    <q-card :style="addAdminFormSize" :loading="loading">
      <q-card-section class="bg-Color text-white">
        <div class="text-h6">Add Admin</div>
      </q-card-section>
      <q-card-section class="gt-sm">
        <div class="row q-gutter-md items-start q-mb-md">
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="fname"
              type="text"
              label="First Name"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="mname"
              type="text"
              label="Middle Name"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="lname"
              type="text"
              label="Last Name"
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="extension"
              type="text"
              label="Extention Name"
              hint="Optional"
            />
          </div>
          <div class="col">
            <q-select
              filled
              v-model="gender"
              :options="options"
              label="Gender"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              filled
              v-model="bday"
              hint="birthday"
              mask="date"
              :rules="['date']"
            >
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy
                    ref="qDateProxy"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-date v-model="bday" landscape>
                      <div class="row items-center justify-end">
                        <q-btn
                          v-close-popup
                          label="Close"
                          color="primary"
                          flat
                        />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="contact"
              type="number"
              label="Contact Number"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="email"
              type="email"
              label="Email"
            />
          </div>
          <div class="col">
            <q-input
              v-model="pass"
              lazy-rules
              type="password"
              color="teal"
              debounce="1000"
              label="Password"
              dense
              outlined
              readonly
              hint="Click Button To generate Password"
            >
              <template v-slot:append>
                <q-btn
                  flat
                  class="text-teal-8"
                  icon="refresh"
                  @click="genPass"
                />
              </template>
            </q-input>
          </div>
        </div>
      </q-card-section>
      <q-card-section class="lt-md">
        <div class="row q-gutter-md items-start q-mb-md">
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="fname"
              type="text"
              label="First Name"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="mname"
              type="text"
              label="Middle Name"
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="lname"
              type="text"
              label="Last Name"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="extension"
              type="text"
              label="Extention Name"
              hint="Optional"
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-select
              filled
              v-model="gender"
              :options="options"
              label="Gender"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              filled
              v-model="bday"
              hint="birthday"
              mask="date"
              :rules="['date']"
            >
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy
                    ref="qDateProxy"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-date v-model="bday" landscape>
                      <div class="row items-center justify-end">
                        <q-btn
                          v-close-popup
                          label="Close"
                          color="primary"
                          flat
                        />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="contact"
              type="number"
              label="Contact Number"
            />
          </div>
          <div class="col">
            <q-input
              standout="bg-teal text-white"
              v-model="email"
              type="email"
              label="Email"
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="pass"
              lazy-rules
              type="password"
              color="teal"
              debounce="1000"
              label="Password"
              dense
              outlined
              readonly
              hint="Click Button To generate Password"
            >
              <template v-slot:append>
                <q-btn
                  flat
                  class="text-teal-8"
                  icon="refresh"
                  @click="genPass"
                />
              </template>
            </q-input>
          </div>
        </div>
      </q-card-section>
      <q-card-actions align="center">
        <q-btn flat color="red" label="Close" @click="closeDialog" />
        <q-btn flat color="teal" label="Add Admin" @click="submitNewAdmin" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import { blankChecker } from "../js/checkers";
export default {
  name: "AddAdmin",
  props: {
    openDialog: Boolean,
  },
  computed: {
    addAdminFormSize() {
      let size = null;
      if (this.$q.screen.gt.sm) {
        size = {
          width: "700px",
          maxWidth: "80vw",
        };
      } else {
        size = {
          width: "100vw",
        };
      }
      return size;
    },
  },
  data() {
    return {
      fname: "",
      mname: "",
      lname: "",
      extension: "",
      gender: "",
      bday: "2000/01/01",
      contact: "",
      email: "",
      pass: "",
      options: ["Male", "Female"],
      loading: false,
    };
  },
  methods: {
    submitNewAdmin() {
      const check = blankChecker([
        this.fname,
        this.lname,
        this.gender,
        this.contact,
        this.email,
        this.pass,
      ]);
      if (!check) {
        return this.$q.notify({
          color: "negative",
          textColor: "white",
          icon: "clear",
          message: "Invalid Form",
        });
      }
      this.loading = true;
      this.$axios
        .post("/addadmin",{
          fname:this.fname,
        mname:this.mname,
        lname:this.lname,
        extension:this.extension,
        gender:this.gender,
        contact:this.contact,
        email:this.email,
        pass:this.pass,
        birthdate:this.bday
        })
        .then(({ data }) => {
          if (data.code == 400) {
            return this.$q.notify({
              color: "danger",
              textColor: "white",
              icon: "error",
              message: 'Admin Already Exist',
              caption:'Failed Successfully'
            });
          }
          this.$q.notify({
            color: "positive",
            textColor: "white",
            message: 'New Admin Added',
            caption:'Successfully'
          });
        })
        .catch((e) => {})
        .then(() => {
          this.loading = false;
          this.$store.dispatch('Admin/fetchAdmins')
        });
    },
    onReset() {
      let self = this;
      self.fname = "";
      self.mname = "";
      self.extension = "";
      self.lname = "";
      self.gender = "";
      self.contact = "";
      self.email = "";
      self.pass = "";
    },
    genPass() {
      this.pass = Math.random().toString(36).slice(-8);
    },
    closeDialog() {
      let dialog = this.openDialog;
      dialog = false;
      this.onReset();
      this.$emit("callback", false);
    },
  },
};
</script>
