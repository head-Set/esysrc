<template>
  <q-dialog v-model="openDialog" persistent>
    <q-card class="">
      <q-card-section class="flex flex-center no-wrap q-gutter-sm items-center">
        <q-radio v-model="addType" val="Rider" label="Rider" color="teal" />
        <q-radio
          v-model="addType"
          val="At Iba Pa"
          label="At Iba Pa"
          color="teal"
        />
      </q-card-section>
      <q-separator />
      <q-card-section class="gt-sm scroll">
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="fname"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Firstname"
              dense
              outlined
            />
          </div>
          <div class="col">
            <q-input
              v-model="mname"
              :rules="[
                (val) => val.length <= 2 || 'Please use maximum 2 characters',
              ]"
              lazy-rules
              max="2"
              hint="maximum 2 letters"
              type="text"
              color="teal"
              debounce="1000"
              label="Middle Initial"
              dense
              outlined
            />
          </div>
          <div class="col">
            <q-input
              v-model="lname"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Lastname"
              dense
              outlined
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="xname"
              type="text"
              color="teal"
              debounce="1000"
              label="Extension Name"
              dense
              outlined
              hint="Optional"
            />
          </div>
          <div class="col">
            <q-input
              v-model="bday"
              mask="date"
              type="text"
              :rules="[basicRule]"
              lazy-rules
              color="teal"
              debounce="1000"
              label="Birthday"
              dense
              readonly
              outlined
              hint="1980 - 2010"
            >
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy
                    ref="qDateProxy"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-date
                      :options="limitOpt"
                      color="teal"
                      :rules="['bday']"
                      v-model="bday"
                      @input="() => $refs.qDateProxy.hide()"
                    />
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>
          <div class="col">
            <q-select
              v-model="gender"
              :options="genderOpt"
              label="Gender"
              color="teal"
              outlined
              dense
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="pAdd"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Permanent Address"
              dense
              outlined
            />
          </div>
          <div class="col">
            <q-input
              v-model="rAdd"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Residential Address"
              dense
              outlined
            />
          </div>
          <div class="col">
            <q-input
              v-model="email"
              :rules="[emailRule]"
              lazy-rules
              type="email"
              color="teal"
              debounce="1000"
              label="Email"
              dense
              outlined
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <!-- :rules="[numOnly]"
                lazy-rules-->
            <q-input
              v-model="contact"
              :rules="[basicRule]"
              lazy-rules
              type="number"
              color="teal"
              debounce="1000"
              label="Contact Number"
              dense
              outlined
            />
          </div>
          <!-- <div class="col">
            <q-input
              v-model="uname"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Username"
              dense
              outlined
              readonly
              hint="Clict Button To generate Username"
            >
              <template v-slot:append>
                <q-btn
                  class="text-teal-8"
                  icon="refresh"
                  @click="genUname"
                  flat
                />
              </template>
            </q-input>
          </div> -->
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
        <div class="q-mt-sm" align="right">
          <div
            v-if="addType === 'Rider'"
            class="row justify-space-around q-gutter-lg"
          >
            <div class="col">
              <q-select
                v-model="vType"
                :options="vehicleType"
                label="Vehicle Type"
                color="teal"
                outlined
                :rules="[basicRule]"
                lazy-rules
                dense
                @input="clrVTypeDetailes"
              />
              <!-- style="width: 16.8vw" -->
            </div>
            <div class="col">
              <div v-if="vType === 'Jeep'">
                <q-input
                  v-model="vTypeDetailes"
                  type="text"
                  label="Route"
                  :rules="[basicRule]"
                  lazy-rules
                  color="teal"
                  debounce="1000"
                  dense
                  outlined
                />
              </div>
              <div v-if="vType === 'Tricycle'">
                <q-input
                  v-model="vTypeDetailes"
                  type="text"
                  label="Body Number"
                  :rules="[basicRule]"
                  lazy-rules
                  color="teal"
                  debounce="1000"
                  dense
                  outlined
                />
              </div>
            </div>
          </div>
          <div v-if="addType === 'At Iba Pa'">
            <q-select
              v-model="selectedSubService"
              multiple
              :options="subServicesmapped"
              counter
              options-cover
              stack-label
              outlined
              label="Select Services"
              hint="Select Services"
            />
            <!-- <q-input
              v-model="vType"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Service To Provide"
              dense
              outlined
            /> -->
          </div>
        </div>
      </q-card-section>
      <q-card-section class="lt-md scroll">
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="fname"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Firstname"
              dense
              outlined
            />
          </div>
          <div class="col">
            <q-input
              v-model="mname"
              :rules="[
                (val) => val.length <= 2 || 'Please use maximum 2 characters',
              ]"
              lazy-rules
              max="2"
              hint="maximum 2 letters"
              type="text"
              color="teal"
              debounce="1000"
              label="Middle Initial"
              dense
              outlined
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="lname"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Lastname"
              dense
              outlined
            />
          </div>
          <div class="col">
            <q-input
              v-model="xname"
              type="text"
              color="teal"
              debounce="1000"
              label="Extension Name"
              dense
              outlined
              hint="Optional"
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="bday"
              mask="date"
              type="text"
              :rules="[basicRule]"
              lazy-rules
              color="teal"
              debounce="1000"
              label="Birthday"
              dense
              readonly
              outlined
              hint="1980 - 2010"
            >
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy
                    ref="qDateProxy"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-date
                      :options="limitOpt"
                      color="teal"
                      :rules="['bday']"
                      v-model="bday"
                      @input="() => $refs.qDateProxy.hide()"
                    />
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>
          <div class="col">
            <q-select
              v-model="gender"
              :options="genderOpt"
              label="Gender"
              color="teal"
              outlined
              dense
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="pAdd"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Permanent Address"
              dense
              outlined
            />
          </div>
          <div class="col">
            <q-input
              v-model="rAdd"
              :rules="[basicRule]"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Residential Address"
              dense
              outlined
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <div class="col">
            <q-input
              v-model="email"
              :rules="[emailRule]"
              lazy-rules
              type="email"
              color="teal"
              debounce="1000"
              label="Email"
              dense
              outlined
            />
          </div>
          <div class="col">
            <!-- :rules="[numOnly]"
                lazy-rules-->
            <q-input
              v-model="contact"
              :rules="[basicRule]"
              lazy-rules
              type="number"
              color="teal"
              debounce="1000"
              label="Contact Number"
              dense
              outlined
            />
          </div>
        </div>
        <div class="row q-gutter-md items-start">
          <!-- <div class="col">
            <q-input
              v-model="uname"
              lazy-rules
              type="text"
              color="teal"
              debounce="1000"
              label="Username"
              dense
              outlined
              readonly
              hint="Clict Button To generate Username"
            >
              <template v-slot:append>
                <q-btn
                  class="text-teal-8"
                  icon="refresh"
                  @click="genUname"
                  flat
                />
              </template>
            </q-input>
          </div> -->
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
        <div class="q-mt-sm" align="right">
          <div
            v-if="addType === 'Rider'"
            class="row justify-space-around q-gutter-lg"
          >
            <div class="col">
              <q-select
                v-model="vType"
                :options="vehicleType"
                label="Vehicle Type"
                color="teal"
                outlined
                :rules="[basicRule]"
                lazy-rules
                dense
                @input="clrVTypeDetailes"
              />
              <!-- style="width: 16.8vw" -->
            </div>
            <div class="col">
              <div v-if="vType === 'Jeep'">
                <q-input
                  v-model="vTypeDetailes"
                  type="text"
                  label="Route"
                  :rules="[basicRule]"
                  lazy-rules
                  color="teal"
                  debounce="1000"
                  dense
                  outlined
                />
              </div>
              <div v-if="vType === 'Tricycle'">
                <q-input
                  v-model="vTypeDetailes"
                  type="text"
                  label="Body Number"
                  :rules="[basicRule]"
                  lazy-rules
                  color="teal"
                  debounce="1000"
                  dense
                  outlined
                />
              </div>
            </div>
          </div>
          <div v-if="addType === 'At Iba Pa'">
           <q-select
              v-model="selectedSubService"
              multiple
              :options="subServicesmapped"
              counter
              options-cover
              stack-label
              outlined
              label="Select Services"
              hint="Select Services"
            />
          </div>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-actions align="center">
        <q-btn @click="closeDialog" color="warning">Close</q-btn>
        <q-btn @click="resetForm" color="red">Reset</q-btn>
        <q-btn @click="addProvider" color="teal">Submit</q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: "AddProvider",
  props: {
    openDialog: Boolean,
  },
  computed: {
    genderOpt() {
      return ["Male", "Female"];
    },
    vehicleType() {
      return ["Tricycle", "Motor", "Car", "Jeep", "Bus", "Truck"];
    },
    subServices() {
      return this.$store.state.Services.service.subServices;
    },
  },
  data() {
    return {
      subServicesmapped:[],
      addType: "Rider",
      fname: "",
      mname: "",
      lname: "",
      xname: "",
      bday: "2000/01/01",
      gender: "",
      pAdd: "",
      rAdd: "",
      email: "",
      contact: "",
      uname: "",
      pass: "",
      vType: "",
      selectedSubService: [],
      vTypeDetailes: "",
    };
  },
  watch: {
    subServices(val){
     if(val){
        val.map((val) => {
       this.subServicesmapped.push({
          id: val.subid,
          label: val.subtype,
        });
      });
     }
    },
    addType(val) {
      if (val == "At Iba Pa") {
        this.$store.dispatch("Services/ViewService", 4);
      }
    },
  },
  methods: {
    cleanselectedSubService(val){
      let cleaned= [];
      val.map(val=>{
        cleaned.unshift(val.label)
      })
      return cleaned.toString();
    },
    addProvider() {
      const data = {
        fname: this.fname,
        mname: this.mname,
        lname: this.lname,
        extension: this.xname,
        birthdate: this.bday,
        // gender: this.gender,
        pAdd: this.pAdd,
        rAdd: this.rAdd,
        email: this.email,
        contact: this.contact,
        uname: "",
        pass: this.pass,
        // type: this.providerType,
        type: this.addType,
        vType: this.addType == 'At Iba Pa'? this.cleanselectedSubService(this.selectedSubService) : this.vType,
        vTypeDetailes: this.vTypeDetailes !== "" ? this.vTypeDetailes : "N/A",
      };
      this.$axios
        .post("/addProvider", data)
        .then(
          ({ data }) => {
            if (data.code === 200) {
              this.$q.notify({
                type: "positive",
                color: "positive",
                textColor: "white",
                icon: "done",
                message: "New Provider Added!",
              });
              return;
            }
            this.$q.notify({
              color: "negative",
              textColor: "white",
              icon: "error",
              message: data.message,
            });
            return;
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
            message: e,
          });
        })
        .then(() => {
          this.$store.dispatch("Providers/fetchProvider", {
            pType: this.addType,
          });
        });
    },
    resetForm() {
      this.fname = "";
      this.mname = "";
      this.lname = "";
      this.xname = "";
      this.bday = "2000/01/01";
      this.gender = "";
      this.pAdd = "";
      this.rAdd = "";
      this.email = "";
      this.contact = "";
      this.uname = "";
      this.pass = "";
      this.vType = "";
      this.vTypeDetailes = "";
    },
    limitOpt(date) {
      return date >= "1980/01/01" && date <= "2010/12/31";
    },
    clrVTypeDetailes(val) {
      this.vTypeDetailes = "";
    },
    genUname() {
      this.uname = Math.random().toString(36).slice(-8);
    },
    genPass() {
      this.pass = Math.random().toString(36).slice(-8);
    },
    basicRule(val) {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          resolve(!!val || "Required!");
        }, 1000);
      });
    },
    emailRule(val) {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          resolve(
            /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(val) ||
              "E-mail must be valid"
          );
        }, 1000);
      });
    },
    closeDialog() {
      let dialog = this.openDialog;
      dialog = false;
      this.resetForm();
      this.$emit("callback", false);
    },
  },
};
</script>
