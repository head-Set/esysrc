import { send } from '../../boot/axios';
import { Notify, Loading } from 'quasar'

export function getDashboard({ commit }) {
  Loading.show();
  send('/dashboard')
    .then(({ data }) => {
      commit('setDashboardData',data.data);
    }).catch(e => {
      Notify.create({
        color: "negative",
        textColor: "white",
        icon: 'error',
        message: e.message,
      });
      return;
    }).then(() => {
      Loading.hide();
    });
}