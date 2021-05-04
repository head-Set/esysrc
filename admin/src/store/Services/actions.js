import { send } from '../../boot/axios';
import { Notify, Loading } from 'quasar'

export function fetchServices({ commit }) {
  Loading.show();

  send('/services')
    .then(({ data }) => {
      if (data.code == 200) {
        commit('setServices', data.data);
        return
      }
      return Notify.create({
        color: "negative",
        textColor: "white",
        icon: 'error',
        message: "Something went wrong!"
      });
    }).catch(e => {
      console.log(e)
      return Notify.create({
        color: "negative",
        textColor: "white",
        icon: 'error',
        message: "Something went wrong!"
      });
    }).then(() => {
      Loading.hide();
    });
}
export function ViewService({commit},data){
  Loading.show();
  send(`/service/${data}`)
  .then(({data})=>{
    if(data.code==200){
      commit('setService',data.data);
      return;
    }
    Notify.create({
      color: "negative",
      textColor: "white",
      icon: 'error',
      message: data.message
    });
    commit('setService',[]);
    return
  }).catch(e=>{
    console.log(e)
      return Notify.create({
        color: "negative",
        textColor: "white",
        icon: 'error',
        message: "Something went wrong!"
      });
  }).then(()=>{
    Loading.hide();
  });
}

export function deleteService({commit,dispatch},data){
  Loading.show();
  send.delete(`/service/${data}`)
  .then(({data})=>{
    dispatch('fetchServices')
  }).catch(e=>{
    return Notify.create({
      color: "negative",
      textColor: "white",
      icon: 'error',
      message: "Something went wrong!"
    });
  }).then(()=>{
    Loading.hide();
  });
}

export function EditService({commit,dispatch},sendData){
  Loading.show();
  send.patch(`/service/${sendData.id}`,sendData)
  .then(({data})=>{
    dispatch('fetchServices')
  }).catch(e=>{
    return Notify.create({
      color: "negative",
      textColor: "white",
      icon: 'error',
      message: "Something went wrong!"
    });
  }).then(()=>{
    Loading.hide();
  });
}

export function addSubService({commit,dispatch},sendData){
  Loading.hide();
  send.post('/subService',sendData)
  .then(({data})=>{
    if(data.code==200){
      Notify.create({
        color:'positive',
        textColor:'white',
        icon:'done',
        "message":data.data.message
      });
      dispatch('ViewService',data.data.typeid)
      return;
    }
    Notify.create({
      color: "negative",
      textColor: "white",
      icon: 'error',
      message: data.message,
    });
    return
  }).catch(e=>{
    return Notify.create({
      color: "negative",
      textColor: "white",
      icon: 'error',
      message: "Something went wrong!"
    });
  }).then(()=>{
    Loading.hide();
  });
}

export function deleteSubService({commit,dispatch},sendData){
  Loading.show();
  send.delete(`/subService/${sendData.subid}`)
  .then(({data})=>{
    if(data.code==200){
      Notify.create({
        color:'positive',
        textColor:'white',
        icon:'done',
        message:data.data
      });
      dispatch('ViewService',sendData.typeid)
      return;
    }
    Notify.create({
      color:'positive',
      textColor:'white',
      icon:'done',
      message:data.message
    });
  }).catch(e=>{
    return Notify.create({
      color: "negative",
      textColor: "white",
      icon: 'error',
      message: "Something went wrong!"
    });
  }).then(()=>{
    Loading.hide();
  }); 
}