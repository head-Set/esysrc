import { send } from '../../boot/axios';
import { Notify, Loading } from 'quasar'

export function fetchRewards({commit}){
  Loading.show();
  send('/rewards')
  .then(({data})=>{
    if(data.code==400){
      return;
    }
    commit('setRewards',data.data)
  }).catch(e=>{

  }).then(()=>{
    Loading.hide();
  });
}