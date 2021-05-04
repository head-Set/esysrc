<template>
  <q-dialog v-model="openDialog" persistent class="q-ma-none">
    <q-card style="width: 100%">
      <!-- :style="dialogSize" -->
      <q-card-section>
        <div class="text-h6">Edit Service</div>
        <q-input
          v-model="servicetype"
          type="text"
          :label="servicetype == '' ? type : 'Edit Service'"
          floating-label
          :readonly="servicetype == '' ? true : false"
          outlined
        >
          <template v-slot:append>
            <q-btn
              color="warning"
              icon="edit"
              @click="editMe"
              v-if="servicetype == ''"
              outline
            />
            <q-btn
              outline
              color="teal"
              icon="send"
              @click="submitEdit"
              v-if="servicetype != ''"
            />
            <q-btn
              outline
              color="red"
              icon="close"
              @click="servicetype = ''"
              v-if="servicetype != ''"
            />
          </template>
        </q-input>
      </q-card-section>
       <q-card-section>
        <q-input
          v-model="addNewSubService"
          type="text"
          label="New Sub-Service"
          floating-label
          outlined
        >
          <template v-slot:append>
            <q-btn
              color="teal"
              :size="$q.screen.lt.md ? 'sm' : 'md'"
              outlined
              icon="send"
              @click="addSubService"
            >
              <!-- @click="openDialog = true" -->
              <q-tooltip> Add Sub-Service </q-tooltip>
            </q-btn>
          </template>
        </q-input>
      </q-card-section>
      <q-card-section class="">
        <q-table
          title="Sub Services"
          :data="service"
          :columns="columns"
          :style="tableSize"
          :filter="subfilter"
          no-data-label="I didn't find anything for you"
        >
          <template v-slot:top-right>
            <q-input
              borderless
              dense
              debounce="300"
              v-model="subfilter"
              placeholder="Search"
            >
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>
          <template v-slot:body="props">
        <q-tr :props="props">
          <q-td key="type" :props="props">
            {{ props.row.subtype }}
          </q-td>
          <q-td key="actions" :props="props" class="q-gutter-sm ">
            <q-btn icon="delete" color="red" raised round size="sm" @click="deleteSubService(props.row.subid)" />
          </q-td>
        </q-tr>
      </template> 
        </q-table>
      </q-card-section>
      <q-card-actions align="center">
        <q-btn flat label="Close" @click="closeDialog" color="red" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: "ViewService",
  props: {
    openDialog: Boolean,
    serviceid: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      servicetype: "",
      subfilter: "",
      addNewSubService: "",
      columns: [
        { name: "type", align: "left", label: "Sub-Service", field: "subtype" },
        {
          name: "actions",
          align: "right",
          label: "Actions",
        },
      ],
    };
  },
  computed: {
    type() {
      return this.$store.state.Services.service.type;
    },
    service() {
      return this.$store.state.Services.service.subServices;
    },
    tableSize() {
      let size = {};
      if (this.$q.screen.gt.sm) {
        size = {
          width: "34vw",
        };
      } 
      return size;
    },
    dialogSize() {
      let size = {};
      if (this.$q.screen.gt.sm) {
        size = {
          width: "80vw",
        };
      } else {
        size = {
          width: "100vw",
        };
      }
      return size;
    },
  },
  methods: {
    deleteSubService(val){
     this.$store.dispatch('Services/deleteSubService',{
       "subid":val,
       "typeid":this.serviceid
     }) 
    },
    addSubService(){
      this.$store.dispatch('Services/addSubService',{
        "service_type":this.type,
        "service":this.addNewSubService
      })
    },
    editMe() {
      this.servicetype = this.type;
    },
    submitEdit() {
      this.$store.dispatch('Services/EditService',{
        id:this.serviceid,
        updateService:this.servicetype
      });
    },
    closeDialog() {
      let dialog = this.openDialog;
      let id = this.serviceid;
      this.servicetype= "";
      dialog = false;
      id = null;
      this.$emit("callback", false);
    },
  },
};
</script>
