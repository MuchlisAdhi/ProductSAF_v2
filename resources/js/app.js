import './bootstrap';
import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Swiper = Swiper;
window.SwiperModules = {
    Autoplay,
    EffectFade,
    Navigation,
    Pagination,
};

window.dispatchEvent(new CustomEvent('swiper:ready'));
