import { send } from '../../boot/axios';
import { Notify,Loading } from 'quasar'
export async function fetchProvider({ commit }, data) {
  Loading.show();
  commit('setProviders', []);
  await send.post('/providers', data)
      .then(({ data }) => {
          if (data.code === 200) {
              commit('setProviders', data.data);
              return;
          }
          if (data.code === 400) {
              Notify.create({
                  color: "negative",
                  textColor: "white",
                  icon: 'error',
                  message:data.message
              });
              return;
          }
      }, e => {
          Notify.create({
              color: "negative",
              textColor: "white",
              icon: 'error',
              message: "Something went wrong!"
          });
      }).catch(e => {
          Notify.create({
              color: "negative",
              textColor: "white",
              icon: 'error',
              message: "Something went wrong!"
          });
      }).then(()=>{
        Loading.hide();
      })
}
export async function fetchOneProvider({ commit }, data) {
  Loading.show(); 
  send.get(`/provider/${data}`)
      .then(({ data }) => {
          if (data.code === 200) {
              commit('setProvider', data.data);
              return;
          }
          if (data.code === 400) {
              Notify.create({
                  color: "negative",
                  textColor: "white",
                  icon: 'error',
                  message: "No User Found!"
              });
              return;
          }
      }, e => {
          Notify.create({
              color: "negative",
              textColor: "white",
              icon: 'error',
              message: "Something went wrong!"
          });
      }).catch(e => {
          Notify.create({
              color: "negative",
              textColor: "white",
              icon: 'error',
              message: "Something went wrong!"
          });
      }).then(()=>{
        Loading.hide();
      });
}
export function clearProvider ({commit}){
  commit('clearProvider',null);
}
export function clearProviders ({commit}){
  commit('clearProvider',null);
}