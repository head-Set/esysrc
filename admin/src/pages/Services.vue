<template>
  <q-page class="flex flex-center">
    <q-table
      title="Services"
      :data="services"
      :card-container-class="cardContainerClass"
      :filter="filter"
      :columns="columns"
      no-data-label="I didn't find anything for you"
      :style="tableSize"
    >
      <template v-slot:top-right>
        <q-input borderless dense debounce="300" v-model="filter" placeholder="Search">
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
        <q-btn color="teal" :size="$q.screen.lt.md ? 'sm' : 'md' " outline icon="add" @click="openDialog=true" >
          <q-tooltip>
            Add Service
          </q-tooltip>
        </q-btn>
      </template>
      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td key="type" :props="props">
            {{ props.row.service_type }}
          </q-td>
          <q-td key="actions" :props="props" class="q-gutter-sm ">
            <q-btn icon="delete" color="red" raised round size="sm" @click="deleteService(props.row.id)" />
            <q-btn icon="visibility" class="bg-Color" raised round size="sm" style="color:#fff" @click="openView(props.row.id)" />
          </q-td>
        </q-tr>
      </template>
    </q-table>
    <AddService :openDialog="openDialog" @callback="openDialog = false" />
    <ViewService :openDialog="viewServiceDialog" :serviceid="selected" @callback="closeView" />
  </q-page>
</template>

<script>
import ViewService from '../components/ViewService.vue'
import AddService from '../components/AddService.vue'
export default {
  name: 'Services',
  meta:{
    title:'ESY Services Offered'
  },
  components:{AddService,ViewService},
  data(){
    return{
      openDialog:false,
      viewServiceDialog:false,
      selected:null,
      filter:"",
      columns: [
        { name: 'type', align: 'left', label: 'Service', field: 'service_type' },
        {
          name: "actions",
          align: "right",
          label: "Actions",
        },
      ],
    }
  },
  mounted(){
    this.$store.dispatch('Services/fetchServices')

  },
  computed:{
    services(){
      return this.$store.state.Services.services;
    },
    service(){
      return this.$store.state.Services.service;
    },
    tableSize(){
      let size = {};
      if(this.$q.screen.gt.sm){
        size = {
          width:'50vw'
        }
      }
      else{
        size = {
          width:'100vw'
        }
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
  },
  // watch:{
  //   service(val){
  //     if(Object.keys(val).length > 0 ){
  //       this.viewServiceDialog = true;
  //       return;
  //     }
  //     this.viewServiceDialog = false;
  //     return;
  //   }
  // },
  methods:{
    openView(id){
      this.viewServiceDialog = true;
      this.$store.dispatch('Services/ViewService',id);
      this.selected = id;
    },
    closeView(){
      this.viewServiceDialog =false;
      this.selected = 0
    },
    deleteService(id){
      this.$store.dispatch('Services/deleteService',id)
    }
  }
}
</script>
