<template>
  <q-page>
    <div class="q-pa-xs">
      <q-card class="my-card" flat>
        <q-card-actions align="center">
          <q-btn
          :color="providerToFetch == 'Rider' ? 'teal' : ''"
            flat
            label="Riders"
            size="lg"
            @click="fetchProvider('Rider')"
          />
          <q-btn 
          :color="providerToFetch == 'At Iba Pa' ? 'teal' : ''"
            flat
            label="At Iba Pa"
            size="lg"
            @click="fetchProvider('At Iba Pa')"
          />
          <q-btn flat label="Add Provider" size="lg" @click="openDialogAdd = true" />
        </q-card-actions>
        <q-card-section>
          <q-table
            grid
            dense
            :data="providers"
            :columns="columns"
            row-key="name"
            :filter="filter"
            hide-header
            no-data-label="I didn't find anything for you"
            pagination.sync="pagination"
            :card-container-class="cardContainerClass"
            :rows-per-page-options="rowsPerPageOptions"
          >
            <!-- :pagination="paginationcfg" -->
            <template v-slot:top-right>
              <q-input
                borderless
                dense
                debounce="300"
                v-model="filter"
                placeholder="Search"
              >
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
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
                    <strong>{{ props.row.name }}</strong>
                  </q-card-section>
                  <q-separator />
                  <!-- <q-card-section class="bg-Color text-white">
                    <div>
                      {{ props.row.email }}
                    </div>
                    <div>{{ props.row.dateJoined }}</div>
                  </q-card-section> -->
                  <q-card-actions align="center" class="q-pa-sm">
                    <q-btn class="full-width" flat
                       @click="isDialog(props.row.id)" >View
                      <q-tooltip content-class="bg-Color"
                        >See More...</q-tooltip
                      >
                      <template v-slot:loading> <q-spinner-gears /> </template
                    ></q-btn>
                  </q-card-actions>
                </q-card>
              </div>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>
    <ProviderDetailes :openDialog="openDialogDetails" :providerId="selected" @callback="openDialogDetails = false" />
    <AddProvider :openDialog="openDialogAdd"  @callback="openDialogAdd = false"/>
  </q-page>
</template>

<script>
import ProviderDetailes from '../components/ProviderDetailes.vue'
import AddProvider from '../components/AddProvider.vue'
export default {
  name: "ESYProviders",
  meta:{
    title:'ESY Service Providers'
  },
  components:{ProviderDetailes,AddProvider},
  data() {
    return {
      openDialogDetails:false,
      openDialogAdd:false,
      selected:0,
      paginationcfg: {
        sortBy: "name",
        descending: false,
        page: 2,
        rowsPerPage: 3,
        // rowsNumber: xx if getting data from a server
      },
      pagination: {
        page: 1,
        rowsPerPage: this.getItemsPerPage(),
      },
      filter: "",
      columns: [
        {
          name: "name",
          required: true,
          label: "Provider Name",
          align: "left",
          field: (row) => row.name,
          format: (val) => `${val}`,
          sortable: true,
        },
        {
          name: "email",
          align: "center",
          label: "Provider Email",
          field: "email",
        },
        { name: "date", label: "Provider Dated Joined", field: "dateJoined" },
      ],

      providerToFetch: null,
    };
  },
  computed: {
    providers() {
      return this.$store.state.Providers.providers;
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
        return this.$q.screen.gt.sm ? [ 3, 6, 12,0] : [ 3, 6,10,0];
      }

      return [3];
    },
  },
  watch: {
    providerToFetch(val) {
      this.$store.dispatch("Providers/fetchProvider", { pType: val });
    },
    "$q.screen.name"() {
      this.pagination.rowsPerPage = this.getItemsPerPage();
    },
  },
  methods: {
    isDialog(val){
      this.openDialogDetails = true;
      this.selected = val;
    },
    getItemsPerPage() {
      const { screen } = this.$q;
      if (screen.lt.sm) {
        return 3;
      }
      if (screen.lt.md) {
        return 6;
      }
      return 9;
    },
    fetchProvider(val) {
      this.providerToFetch = val;
    },
  },
};
</script>
