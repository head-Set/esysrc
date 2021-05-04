<template>
  <q-page class="flex flex-center">
    <q-table
      title="Rewards"
      :data="rewards"
      :columns="columns"
      row-key="points"
      :filter="filter"
      :style="tableSize"
      no-data-label="I didn't find anything for you"
    >
      <template v-slot:top-right>
        <q-btn
          color="teal"
          :size="$q.screen.lt.md ? 'sm' : 'md'"
          outline
          icon="add"
          @click="openDialog = true"
        >
          <q-tooltip> Add Reward </q-tooltip>
        </q-btn>
        <q-space />
        <!-- <q-input
          borderless
          dense
          debounce="300"
          v-model="filter"
          placeholder="Search"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input> -->
      </template>
      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td key="item" :props="props">
            {{ props.row.item }}
          </q-td>
          <q-td key="description" :props="props">
            {{ props.row.description }}
          </q-td>
          <q-td key="points" :props="props">
            <div class="text-pre-wrap">{{ props.row.points }}</div>
          </q-td>
          <q-td key="stock" :props="props">
            <div class="text-pre-wrap">{{ props.row.stock }}</div>
          </q-td>
          <q-td key="actions" :props="props" class="q-gutter-sm">
            <q-btn
              icon="edit"
              class="bg-Color"
              raised
              round
              size="sm"
              style="color: #fff"
              @click="updateMe(props.row)"
            />
            <q-btn
              icon="delete"
              @click="rmReward(props.row)"
              color="red"
              raised
              round
              size="sm"
            />
          </q-td>
        </q-tr>
      </template>
    </q-table>
    <AddRewards :openDialog="openDialog" @callback="openDialog = false" :selectedReward="selectedReward" />
  </q-page>
</template>

<script>
import AddRewards from "../components/AddRewards.vue";
export default {
  name: "OffredRewards",
  meta: {
    title: "ESY Offred Rewards",
  },
  components: { AddRewards },
  mounted() {
    this.$store.dispatch("Rewards/fetchRewards");
  },
  computed: {
    rewards() {
      return this.$store.state.Rewards.rewards;
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
  },
  data() {
    return {
      selectedReward:{},
      filter: "",
      openDialog: false,
      columns: [
        {
          name: "item",
          required: true,
          label: "Reward Item",
          align: "left",
          field: (row) => row.item,
          format: (val) => `${val}`,
        },
        {
          name: "description",
          align: "center",
          label: "Description",
          field: "description",
        },
        {
          name: "points",
          label: "Required Points",
          field: "points",
          sortable: true,
        },
        {
          name: "stock",
          label: "Stocks",
          field: "stock",
          sortable: true,
        },
        {
          name: "actions",
          align: "right",
          label: "Actions",
        },
      ],
    };
  },
  methods:{
    rmReward(val){
    },
    updateMe(val){
      this.selectedReward = val;
      this.openDialog =true;
    }
  }
};
</script>
