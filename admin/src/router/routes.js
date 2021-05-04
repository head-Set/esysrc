import Dashboard from '../pages/Index.vue';
import EmailVerify from 'pages/Email.vue';
const routes = [
  {
    path: '/',
    component: () => import('layouts/IndexLayout.vue'),
    children: [
      { path: '', component: () => import('pages/signin.vue') }
    ]
  },
  {
    path: '/home',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: Dashboard },
      { path: '/providers', component: () => import('pages/Providers.vue') },
      { path: '/services', component: () => import('pages/Services.vue') },
      { path: '/admins', component: () => import('pages/Admins.vue') },
      { path: '/rewards', component: () => import('pages/Rewards.vue') },
    ]
  },

  // {
  //   path:'/verify',
  //   component:()=>import('layouts/Verify.vue'),
  //   children:[
  //     { path: '', component: EmailVerify },
  //   ]
  // },
  // Always leave this as last one,
  // but you can also remove it
  {
    path: '*',
    component: () => import('pages/Error404.vue')
  }
]

export default routes
