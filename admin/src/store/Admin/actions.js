import { send } from '../../boot/axios';
import { Notify, Loading } from 'quasar'

export function fetchAdmins({commit}){
  Loading.show();
  send('/admins')
  .then(({data})=>{
    commit('setAdmins',data.data)
  }).catch(e=>{

  }).then(()=>{
    Loading.hide();
  });
}