<template>
  <q-page>
    <q-table
      grid=""
      title="ESY Admins"
      :data="admins"
      :columns="columns"
      row-key="name"
      :card-container-class="cardContainerClass"
      :rows-per-page-options="rowsPerPageOptions"
    >
      <template v-slot:top-right>
        <!-- <q-input borderless dense debounce="300" v-model="filter" placeholder="Search">
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input> -->
        <q-btn
          color="teal"
          :size="$q.screen.lt.md ? 'sm' : 'md'"
          outline
          icon="add"
          @click="openDialog = true"
        >
          <q-tooltip> Add Service </q-tooltip>
        </q-btn>
      </template>
      <template v-slot:item="props">
        <div class="q-pa-xs col-xs-12 col-sm-6 col-md-4">
          <q-card>
            <q-img
              src="~assets/person1.png"
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
              {{ props.row.fname }} {{ props.row.mname }} {{ props.row.lname }}
              {{ props.row.extension }}
            </q-card-section>
            <q-card-section class="q-pa-none">
              <q-expansion-item
                expand-separator
                class="text-center"
                label="See More"
              >
                <q-card class="text-left">
                  <q-card-section>
                    {{ props.row.email }}
                  </q-card-section>
                  <q-card-section>
                    {{ props.row.contact }}
                  </q-card-section>
                  <q-card-section>
                    {{ props.row.isAdmin }}
                  </q-card-section>
                  <!-- <q-card-actions align="center">
                    <q-btn flat label="Action" />
                  </q-card-actions> -->
                </q-card>
              </q-expansion-item>
            </q-card-section>
          </q-card>
        </div>
      </template>
    </q-table>
    <AddAdmin :openDialog="openDialog" @callback="openDialog = false" />
  </q-page>
</template>

<script>
import AddAdmin from "../components/AddAdmin.vue";
export default {
  name: "Admins",
  meta: {
    title: "ESY Admins",
  },
  components: { AddAdmin },
  mounted() {
    this.$store.dispatch("Admin/fetchAdmins");
  },
  computed: {
    adminTypeOpt() {
      return ["Super", "Admin"];
    },
    admins() {
      return this.$store.state.Admin.admins;
    },
    tableSize() {
      let size = {};
      if (this.$q.screen.gt.sm) {
        size = {
          width: "50vw",
        };
      } else {
        size = {
          width: "100vw",
        };
      }
      return size;
    },
    cardContainerClass() {
      if (this.$q.screen.gt.xs) {
        return (
          "grid-masonry grid-masonry--" + (this.$q.screen.gt.sm ? "4" : "2")
        );
      }

      return void 0;
    },

    rowsPerPageOptions() {
      if (this.$q.screen.gt.xs) {
        return this.$q.screen.gt.sm ? [3, 6, 12, 0] : [3, 6, 10, 0];
      }

      return [3];
    },
  },
  data() {
    return {
      adminType: "",
      openDialog: false,
      filter: "",
      columns: [
        {
          name: "fname",
          required: true,
          label: "Name",
          align: "left",
          field: (row) => row.fname,
          format: (val) => `${val}`,
        },
        {
          name: "email",
          align: "center",
          label: "Email",
          field: "email",
        },
        {
          name: "contact",
          label: "Contact",
          field: "contact",
        },
        // {
        //   name: "isAdmin",
        //   label: "Type",
        //   field: "isAdmin",
        // },
        {
          name: "actions",
          align: "right",
          label: "Actions",
          // field:'actions'
        },
      ],
    };
  },
  methods: {
    AdminType(val) {
      alert(val);
    },
    updateMe(val) {},
    rmReward(val) {},
  },
};
</script>
