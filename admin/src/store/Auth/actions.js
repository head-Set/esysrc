import { send } from '../../boot/axios';
import { Notify, Loading } from 'quasar'

export async function login({ commit }, postData) {
  Loading.show();
  await send.post('/signIn', postData)
    .then(({ data }) => {
      if (data.code == 400) {
        Notify.create({
          color: "negative",
          textColor: "white",
          icon: 'error',
          message: data.message,
        });
        return;
      }
      send.defaults.headers["Authorization"] = data.data.jwt;
      sessionStorage.setItem('esyAdmin', data.data.jwt);
      this.$router.push('/home/');
      Loading.hide();
      return
    }, (e) => {
      console.log(e)
      return;
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
export function logout({ commit }) {
  Loading.show();
  send.defaults.headers["Authorization"] = "";
  sessionStorage.removeItem('esyAdmin')
  setTimeout(() => {
    this.$router.push('/');
    Loading.hide();
  }, 2000);
}