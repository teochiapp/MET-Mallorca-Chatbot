<?php
/**
 * Template del popup de homepage
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Backdrop para atenuar el fondo de la página -->
<div id="met-popup-backdrop" class="met-popup-backdrop"></div>

<!-- Popup de Homepage -->
<div id="met-homepage-popup" class="met-homepage-popup">
    <div class="met-popup-content">
        <!-- Language selector in top-right corner -->
        <div class="met-popup-language-selector">
            <button class="met-popup-lang-btn active" data-lang="es" title="Español">
                <span class="met-flag met-flag-es" aria-hidden="true"></span>
            </button>
            <button class="met-popup-lang-btn" data-lang="en" title="English">
                <span class="met-flag met-flag-en" aria-hidden="true"></span>
            </button>
            <button class="met-popup-lang-btn" data-lang="de" title="Deutsch">
                <span class="met-flag met-flag-de" aria-hidden="true"></span>
            </button>
        </div>

        <button id="met-popup-close" class="met-popup-close" aria-label="Cerrar popup">
            <i class="fas fa-times"></i>
        </button>

        <!-- Imagen de fondo con overlay -->
        <div class="met-popup-background">
            <div class="met-popup-overlay"></div>
        </div>

        <!-- Contenido del popup -->
        <div class="met-popup-inner">
            <div class="met-popup-text">
                <h2 id="met-popup-title-text">
                    <span class="met-popup-title-orange">¿NECESITAS UN </span><span
                        class="met-popup-title-white">TRASLADO?</span>
                </h2>
            </div>

            <!-- Contenedor para botón + mano (relativo) -->
            <div class="met-popup-button-wrapper">
                <button id="met-popup-action" class="met-popup-action">
                    <span class="met-popup-btn-white">HABLA </span><span class="met-popup-btn-blue">POR CHAT</span>

                    <!-- Icono de chat dentro del botón (lets-icons chat-alt-3-light) -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="letsicon-chat-alt-3-light">
                        <path
                            d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 13.4876 3.35471 14.8936 4 16.1272L3 21L7.8728 20C9.10636 20.6453 10.5124 21 12 21Z" />
                        <path d="M8.5 12.5H8.51" />
                        <path d="M12 12.5H12.01" />
                        <path d="M15.5 12.5H15.51" />
                    </svg>
                </button>

                <!-- Mano/cursor apuntando (absoluto, fuera del botón) -->
                <svg class="met-popup-cursor" width="65" height="58" viewBox="0 0 65 58" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#filter0_d_2022_12)">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M23.5677 33.5766L18.7534 31.7786C15.4662 30.5509 11.5384 32.1117 9.97474 35.2626L10.3391 34.5284C9.95103 35.3104 10.242 36.3334 10.9903 36.8108L20.1023 42.6246C21.5468 43.5463 24.1559 44.433 25.8805 44.5736C25.8805 44.5736 36.0319 45.1591 37.4744 47.1186L39.3007 49.5993L49.5135 42.0805L52.3063 40.0245L54.62 38.3212L57.1732 36.4415L55.3469 33.9608C53.9043 32.0013 53.2131 22.9935 53.2131 22.9935C53.0391 21.2718 52.0742 18.7233 51.0412 17.3201L43.4444 7.00124C41.7163 4.75063 38.4145 4.32643 36.0222 6.0876C34.8255 6.96867 34.5377 8.60807 35.3791 9.751L35.9875 10.5775C34.3056 8.29287 31.0038 7.86866 28.6115 9.62984C27.4148 10.5109 27.1269 12.1503 27.9684 13.2932L28.5768 14.1197C26.8949 11.8351 23.5931 11.4109 21.2008 13.1721C20.0041 14.0531 19.7162 15.6925 20.5577 16.8355L21.1661 17.6619C20.8958 17.2948 20.6418 17.0318 20.3958 16.8562L11.8898 6.68616C10.7395 5.31092 8.70279 5.08402 7.29269 6.12214C5.87277 7.16749 5.58229 9.14662 6.62195 10.5588L14.1536 20.7892L23.5677 33.5766Z"
                            fill="white" />
                        <path
                            d="M52.2493 16.4311C52.8722 17.2773 53.4294 18.398 53.8508 19.5025C54.2725 20.608 54.6008 21.8093 54.7053 22.8425L54.7075 22.8603L54.7089 22.8786L54.7087 22.88C54.7087 22.8808 54.7087 22.882 54.7088 22.8835C54.7091 22.8873 54.7104 22.8934 54.711 22.9013C54.7123 22.9173 54.7142 22.9415 54.7168 22.9734C54.722 23.0374 54.7291 23.1327 54.7397 23.2549C54.7609 23.4995 54.7935 23.8523 54.8364 24.2824C54.9225 25.1442 55.0514 26.31 55.2242 27.5319C55.3977 28.7591 55.6118 30.0177 55.8638 31.0724C56.1352 32.2077 56.3959 32.856 56.5547 33.0716L59.27 36.76L38.9818 51.6963L36.2664 48.0079C36.299 48.0522 36.2395 47.9585 35.9023 47.7933C35.5954 47.6431 35.1724 47.4883 34.6419 47.3364C33.5827 47.0333 32.2552 46.7879 30.9233 46.5961C29.6007 46.4057 28.3157 46.274 27.3586 46.19C26.8811 46.1481 26.4868 46.1185 26.2137 46.0991C26.0771 46.0895 25.9707 46.0818 25.8992 46.0772C25.8636 46.0749 25.8367 46.0731 25.819 46.072C25.8102 46.0715 25.8036 46.0721 25.7994 46.0718C25.7974 46.0717 25.7948 46.0711 25.794 46.071L25.7766 46.0705L25.7586 46.0691C23.7877 45.9084 20.9442 44.9409 19.2959 43.8893L10.1836 38.0759C9.03902 37.3456 8.47111 35.9478 8.73099 34.6451L8.63083 34.5963C8.63366 34.5906 8.63693 34.5845 8.63978 34.5788L8.99597 33.8618L9.03233 33.8799C11.1401 30.5315 15.5254 28.9719 19.2786 30.3736L19.3718 30.408L5.4134 11.4481C3.88636 9.3735 4.31172 6.45412 6.40335 4.91422C8.44899 3.40821 11.3801 3.73814 13.0408 5.72363L19.2299 13.1241C19.5088 12.6833 19.8722 12.2874 20.3114 11.9641C22.0423 10.6898 24.1872 10.3087 26.1178 10.7865C26.3566 9.86679 26.9097 9.01994 27.7224 8.42162C29.4534 7.14736 31.5983 6.76616 33.5288 7.24408C33.7677 6.3245 34.3202 5.47797 35.1327 4.87972C38.1448 2.66223 42.3806 3.15276 44.6341 6.08727L52.2493 16.4311Z"
                            stroke="#15699A" stroke-width="3" />
                    </g>
                    <defs>
                        <filter id="filter0_d_2022_12" x="0" y="0" width="64.3677" height="57.7937"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="1" />
                            <feGaussianBlur stdDeviation="1.5" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2022_12" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_2022_12" result="shape" />
                        </filter>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
</div>