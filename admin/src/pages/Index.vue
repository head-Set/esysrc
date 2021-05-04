<template>
  <q-page class="q-pb-md q-pr-sm">
    <div class="q-gutter-sm q-pb-md">
      <div class="row">
        <div
          class="col q-pt-md q-gutter-sm items-center gt-sm flex flex-center"
        >
          <q-list bordered dense class="dashboardDataList bg-white">
            <q-item-label header class="bg-Color text-white">
              <div class="row items-center q-pa-none justify-between">
                <div class="col">Pabili</div>
                <div class="col-4">
                  <q-btn size="sm" label="chart" outline />
                </div>
              </div>
            </q-item-label>
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.all }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.pending }}</q-item-section>
            </q-item>
          </q-list>
          <q-list bordered dense class="dashboardDataList bg-white">
            <q-item-label header class="text-center bg-Color text-white"
              >Arkila</q-item-label
            >
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side> {{ arkilaRequest.all }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ arkilaRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ arkilaRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ arkilaRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>
                {{ arkilaRequest.pending }}
              </q-item-section>
            </q-item>
          </q-list>
          <q-list bordered dense class="dashboardDataList bg-white">
            <q-item-label header class="text-center bg-Color text-white"
              >Padala</q-item-label
            >
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side> {{ padalaRequest.all }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ padalaRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ padalaRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ padalaRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>{{ padalaRequest.pending }}</q-item-section>
            </q-item>
          </q-list>
          <q-list bordered dense class="dashboardDataList bg-white">
            <q-item-label header class="text-center bg-Color text-white"
              >At Iba Pa</q-item-label
            >
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side> {{ atbpRequest.all }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ atbpRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ atbpRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ atbpRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>{{ atbpRequest.pending }}</q-item-section>
            </q-item>
          </q-list>
          <q-list bordered dense class="dashboardDataList bg-white">
            <q-item-label header class="text-center bg-Color text-white"
              >App Users</q-item-label
            >
            <q-item v-ripple>
              <q-item-section avatar>
                <q-img
                  src="~assets/customer.png"
                  spinner-color="primary"
                  spinner-size="82px"
                />
              </q-item-section>
              <q-item-section>El Customer</q-item-section>
              <q-item-section side> {{ users.customer }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section avatar>
                <q-img
                  src="~assets/customer.png"
                  spinner-color="primary"
                  spinner-size="82px"
                />
              </q-item-section>
              <q-item-section>El Rider</q-item-section>
              <q-item-section side>{{ users.rider }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section avatar>
                <q-img
                  src="~assets/customer.png"
                  spinner-color="primary"
                  spinner-size="82px"
                />
              </q-item-section>
              <q-item-section>El Atbp</q-item-section>
              <q-item-section side>{{ users.atbp }}</q-item-section>
            </q-item>
            <q-item />
          </q-list>
        </div>
      </div>
    </div>
    <div class="lt-md no-border">
      <q-list>
        <q-expansion-item expand-separator label="Pabili">
          <q-list class="q-px-md">
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side> {{ pabiliRequest.all }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>{{ pabiliRequest.pending }}</q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>

        <q-separator />
        <q-expansion-item expand-separator label="Arkila">
          <q-list class="q-px-md">
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side> {{ arkilaRequest.all }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ arkilaRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ arkilaRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ arkilaRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>
                {{ arkilaRequest.pending }}
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>

        <q-separator />
        <q-expansion-item expand-separator label="Padala">
          <q-list class="q-px-md">
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side> {{ padalaRequest.all }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ padalaRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ padalaRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ padalaRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>
                {{ padalaRequest.pending }}
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>

        <q-separator />
        <q-expansion-item expand-separator label="At Iba Pa">
          <q-list class="q-px-md">
            <q-item v-ripple>
              <q-item-section>All Request</q-item-section>
              <q-item-section side> {{ atbpRequest.all }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Finished Request</q-item-section>
              <q-item-section side>{{ atbpRequest.finish }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Done Request</q-item-section>
              <q-item-section side>{{ atbpRequest.done }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Working Request</q-item-section>
              <q-item-section side>{{ atbpRequest.working }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>Pending Request</q-item-section>
              <q-item-section side>
                {{ atbpRequest.pending }}
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>

        <q-separator />
        <q-expansion-item group="somegroup" label="App Users" default-opened>
          <q-list class="q-px-md">
            <q-item v-ripple>
              <q-item-section>El Customer</q-item-section>
              <q-item-section side> {{ users.customer }} </q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>El Rider</q-item-section>
              <q-item-section side>{{ users.rider }}</q-item-section>
            </q-item>
            <q-item v-ripple>
              <q-item-section>El Atbp</q-item-section>
              <q-item-section side>{{ users.atbp }}</q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
      </q-list>
    </div>
  </q-page>
</template>

<script>
export default {
  name: "ESYDasboard",
  meta: {
    title: "ESY Admin Dashboard",
  },
  data() {
    return {
      dashboardData: {},
      requests: {
        pabili: 0,
        arkila: 0,
        padala: 0,
        atbp: 0,
      },
      users: {
        atbp: 0,
        rider: 0,
        customer: 0,
      },
    };
  },
  computed: {
    indexdata() {
      return this.$store.state.Dashboard.dashboardData;
    },
    pabiliRequest: {
      get: function () {
        return this.requests.pabili;
      },
      set: function (pabiliData) {
        let data = {};
        let pending = 0;
        let working = 0;
        let done = 0;
        let finish = 0;
        pabiliData.map((val, index) => {
          if (val.job_status == "Pending") {
            return (pending += 1);
          }
          if (val.job_status == "Working") {
            working += 1;
          }
          if (val.job_status == "Done") {
            done += 1;
          }
          if (val.job_status == "Finish") {
            finish += 1;
          }
        });
        data = {
          all: pabiliData.length,
          pending,
          working,
          done,
          finish,
        };
        this.requests.pabili = data;
      },
    },
    arkilaRequest: {
      get: function () {
        return this.requests.arkila;
      },
      set: function (arkilaData) {
        let data = {};
        let pending = 0;
        let working = 0;
        let done = 0;
        let finish = 0;
        arkilaData.map((val, index) => {
          if (val.job_status == "Pending") {
            return (pending += 1);
          }
          if (val.job_status == "Working") {
            working += 1;
          }
          if (val.job_status == "Done") {
            done += 1;
          }
          if (val.job_status == "Finish") {
            finish += 1;
          }
        });
        data = {
          all: arkilaData.length,
          pending,
          working,
          done,
          finish,
        };
        this.requests.arkila = data;
      },
    },
    padalaRequest: {
      get: function () {
        return this.requests.padala;
      },
      set: function (padalaData) {
        let data = [];
        let pending = 0;
        let working = 0;
        let done = 0;
        let finish = 0;
        padalaData.map((val, index) => {
          if (val.job_status == "Pending") {
            return (pending += 1);
          }
          if (val.job_status == "Working") {
            working += 1;
          }
          if (val.job_status == "Done") {
            done += 1;
          }
          if (val.job_status == "Finish") {
            finish += 1;
          }
        });
        data = {
          all: padalaData.length,
          pending,
          working,
          done,
          finish,
        };
        this.requests.padala = data;
      },
    },
    atbpRequest: {
      get: function () {
        return this.requests.atbp;
      },
      set: function (atbpData) {
        let data = [];
        let pending = 0;
        let working = 0;
        let done = 0;
        let finish = 0;
        atbpData.map((val, index) => {
          if (val.job_status == "Pending") {
            return (pending += 1);
          }
          if (val.job_status == "Working") {
            working += 1;
          }
          if (val.job_status == "Done") {
            done += 1;
          }
          if (val.job_status == "Finish") {
            finish += 1;
          }
        });
        data = {
          all: atbpData.length,
          pending,
          working,
          done,
          finish,
        };
        this.requests.atbp = data;
      },
    },
  },
  watch: {
    indexdata(val) {
      this.dashboardData = val;

      // this.requests.pabili = val.pabiliRequest; // not done
      this.pabiliRequest = val.pabiliRequest;
      this.arkilaRequest = val.arkilaRequest;
      this.padalaRequest = val.padalaRequest;
      this.atbpRequest = val.atbpRequest;

      this.users.atbp = val.atbp;
      this.users.rider = val.rider;
      this.users.customer = val.customer;
    },
  },
  created() {
    this.$store.dispatch("Dashboard/getDashboard");
  },
};
</script>

<style>
</style>