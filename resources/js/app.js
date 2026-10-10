import axios from 'axios';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { registerStorefront } from './storefront';
import { registerStoreToasts } from './store-toasts';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
registerStorefront(Alpine);
registerStoreToasts(Alpine);
Livewire.start();
