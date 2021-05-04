import { Line, Bar, } from 'vue-chartjs'

export default {
    extends: Bar,
    // props: {
    //       chartdata: {
    //         type: Object,
    //         // default: null,
    //         required:true
    //     },
    //     options: {
    //         type: Object,
    //         // default: null

    //     }
    // },watch: {
    //     chartData () {
    //       this.$data._chart.update()
    //     }
    //   },
    data() {
        return {
            chartdata: {
                labels: ['Pabili', 'Arkila', 'Padala', 'At Iba Pa'],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        }
    },
    mounted() {
        this.filldata()
        this.renderChart(this.chartdata, this.options)
    },
    methods:{
        filldata(){
            this.chartdata['datasets'] = [
                {
                    label: 'Total Requests',
                    backgroundColor: '#f87979',
                    data: [40, 80, 50, 20,0],
                },
            ]
        }
    }
}