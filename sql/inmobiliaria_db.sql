-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-09-2026 a las 21:20:02
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inmobiliaria_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `property_title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `agent` varchar(120) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(14,2) NOT NULL,
  `type` varchar(50) NOT NULL,
  `listing_type` varchar(20) NOT NULL,
  `location` varchar(200) NOT NULL,
  `google_maps_url` text DEFAULT NULL,
  `city` varchar(120) DEFAULT 'CDMX',
  `bedrooms` int(11) DEFAULT 0,
  `bathrooms` int(11) DEFAULT 0,
  `area_sqm` decimal(10,2) DEFAULT 0.00,
  `floors` int(11) DEFAULT 0,
  `has_parking` tinyint(1) DEFAULT 0,
  `parking_spaces` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `is_paused` tinyint(1) DEFAULT 0,
  `is_sold` tinyint(1) DEFAULT 0,
  `status` varchar(30) DEFAULT 'Disponible',
  `agent_name` varchar(120) DEFAULT NULL,
  `image_main` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `properties`
--

INSERT INTO `properties` (`id`, `title`, `description`, `price`, `type`, `listing_type`, `location`, `google_maps_url`, `city`, `bedrooms`, `bathrooms`, `area_sqm`, `floors`, `has_parking`, `parking_spaces`, `featured`, `is_paused`, `is_sold`, `status`, `agent_name`, `image_main`, `created_at`) VALUES
(2, 'Casa amueblada en venta', 'En planta baja cuenta con cocina equipada con cocina Integral, ½ baño, comedor, sala, patio trasero, cisterna, bodega y cuarto de servicio.\r\nPiso de mármol en sala, comedor.  \r\nPlanta Alta, 3 recamaras 2 de ellas con espacio para closet, 1 baño completo. Y piso laminado', 4500000.00, 'Casa', 'Venta', 'Apizaco', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d505.2710726006159!2d-98.1396457!3d19.4170943!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d0202422a7387f%3A0x77fcb5fbded2433d!2sSILE%20CUAUHTEMOC!5e1!3m2!1ses-419!2smx!4v1790789534962!5m2!1ses-419!2smx\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"strict-origin-when-cross-origin\"></iframe>', 'Tlaxcala', 4, 2, 175.00, 2, 0, 0, 1, 0, 0, 'Disponible', 'Lucia Lima', 'uploads/properties/153e75990187bdee-1790787993.jpg', '2026-09-30 17:06:33'),
(3, 'Bodega en Via corta Santa Ana-Puebla', 'Bodega de 368 mts2, ubicada en la magdalena Tlaltelulco, Tlax. a 250 metros de la vía corta Santa Ana-Puebla, \r\nCuenta con área de baños para hombre y mujeres, y una pequeña oficina. \r\nEntrar en la calle Morelos. \r\nTomar camión a puebla y pedir la parada en el café california o parada de los pulques\r\n\r\nPrecio de renta $ 15,000.00 el agua esta incluida en el precio de renta. \r\nLuz. 220 servicio de Luz subterráneo \r\n6.20 de frente X 23 de fondo.', 15000.00, 'Bodega', 'Renta', 'La Magdalena Tlatelulco', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d4045.7427024040417!2d-98.20022999999999!3d19.272855!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMTnCsDE2JzIyLjMiTiA5OMKwMTInMDAuOCJX!5e1!3m2!1ses-419!2smx!4v1790789318850!5m2!1ses-419!2smx\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"strict-origin-when-cross-origin\"></iframe>', 'Tlaxcala', 0, 2, 142.00, 0, 0, 0, 1, 0, 0, 'Disponible', 'Lucia Lima', 'uploads/properties/c60b7396d0a75420-1790789476.jpg', '2026-09-30 17:31:16'),
(4, 'Casa de 2 niveles', 'casa de 2 nieveles, planta baja cuenta con sala-comedor, cocina con tarja, 1/2 baño, cochera para 1 autp, árrea de servicio. planta alta son 2 recamaras, salita de TV y 1 baño completo', 1500000.00, 'Casa', 'Venta', 'Santa Cruz, Apizaco', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1010.6562309452199!2d-98.1315636!3d19.3987353!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d01ff82841f715%3A0x69876271ff93c90b!2sApizaco%20-%20Sta.%20Cruz%20Tlaxcala%201524%2C%20San%20Diego%2094%2C%20Esmeralda%2C%2090355%20Cdad.%20de%20Apizaco%2C%20Tlax.!5e1!3m2!1ses-419!2smx!4v1790791583149!5m2!1ses-419!2smx\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"strict-origin-when-cross-origin\"></iframe>', 'Tlaxcala', 4, 2, 85.00, 2, 0, 0, 1, 0, 0, 'Disponible', 'Lucia Lima', 'uploads/properties/26fdf2820e65f446-1790791747.jpg', '2026-09-30 18:09:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `url`) VALUES
(5, 2, 'uploads/properties/9a102e4f4df93e15-1790787993.jpg'),
(6, 2, 'uploads/properties/1a9215d4b84d8ae4-1790787993.jpg'),
(7, 2, 'uploads/properties/e8a148c7f0dce458-1790787993.jpg'),
(8, 2, 'uploads/properties/28c73a0b2b0b5fa9-1790787993.jpg'),
(9, 2, 'uploads/properties/fb13e491cc66a973-1790787993.jpg'),
(10, 2, 'uploads/properties/1345070e82d49d64-1790787993.jpg'),
(11, 2, 'uploads/properties/c4c965c23eccc9f6-1790787993.jpg'),
(12, 2, 'uploads/properties/5976b271f53555e2-1790787993.jpg'),
(13, 2, 'uploads/properties/29c2ce060ddd9c46-1790787993.jpg'),
(14, 2, 'uploads/properties/86f0bd9bd1d95961-1790787993.jpg'),
(15, 2, 'uploads/properties/47a65162746177be-1790787993.jpg'),
(16, 2, 'uploads/properties/f5909aa0dbcdaf39-1790787993.jpg'),
(17, 2, 'uploads/properties/b91a50c7adf78109-1790787993.jpg'),
(18, 2, 'uploads/properties/a33443fe3943c930-1790787993.jpg'),
(19, 3, 'uploads/properties/2957b1955ea2705d-1790789476.jpg'),
(20, 3, 'uploads/properties/e8f1e0e322c2516f-1790789476.jpg'),
(21, 3, 'uploads/properties/40a84b9a12b51e93-1790789476.jpg'),
(22, 3, 'uploads/properties/f3a8a2c63a0da5ee-1790789476.jpg'),
(23, 3, 'uploads/properties/e72fe443d8332ffe-1790789476.jpg'),
(24, 3, 'uploads/properties/0e24387c3b1e257f-1790789476.jpg'),
(25, 4, 'uploads/properties/75c8d02f9168ee66-1790791747.jpg'),
(26, 4, 'uploads/properties/1216ec90e29abe9b-1790791747.jpg'),
(27, 4, 'uploads/properties/aab2ea241934d5f9-1790791747.jpg'),
(28, 4, 'uploads/properties/9e2768fc168a4c6b-1790791747.jpg'),
(29, 4, 'uploads/properties/88d3c740e220f138-1790791747.jpg'),
(30, 4, 'uploads/properties/28912432333c45bc-1790791747.jpg'),
(31, 4, 'uploads/properties/4f4fbce061bc7aff-1790791747.jpg'),
(32, 4, 'uploads/properties/7f6bfda1e360824b-1790791747.jpg'),
(33, 4, 'uploads/properties/ad87cc04ce555448-1790791747.jpg'),
(34, 4, 'uploads/properties/b4543b7206ac62b7-1790791747.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(60) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('color_accent', '#ec4899'),
('color_danger', '#dc2626'),
('color_info', '#eab308'),
('color_neutral', '#57534e'),
('color_primary', '#f98f15'),
('color_success', '#10b981'),
('company_name', 'Monarka Inmobiliaria'),
('footer_address', 'Baltazar Maldonado Edif. 34, depto, 6, San Diego, Apizaco, Mexico, 90338'),
('footer_address_label', 'Oficinas'),
('footer_copyright', '© 2026 MonArka Inmobiliaria. Todos los derechos reservados.'),
('footer_description', 'Somos una inmobiliaria dedicada a ofrecer propiedades exclusivas con atención personalizada.'),
('footer_email', 'monarcainmobiliaria@infinitummail.com'),
('footer_email_label', 'Correo de contacto'),
('footer_hours', 'Lunes a Sábado · 09:00 - 19:00 hrs'),
('footer_hours_label', 'Horario de atención'),
('footer_legal_privacy', 'Aviso de Privacidad'),
('footer_legal_privacy_url', 'aviso-privacidad.php'),
('footer_legal_terms', 'Términos y Condiciones'),
('footer_legal_terms_url', '#'),
('footer_links_menu', 'Inicio | index.php\r\nPropiedades | propiedades.php\r\nServicios | servicios.php\r\nNosotros | nosotros.php\r\nContacto | contacto.php'),
('footer_links_menu_title', 'Navegación'),
('footer_links_services', 'Administración | servicios.php\r\nVenta y Renta | servicios.php\r\nAvaluos | servicios.php\r\nMantenimiento | servicios.php\r\nConstrucción | servicios.php'),
('footer_links_services_title', 'Nuestros Servicios'),
('footer_social_facebook', 'https://www.facebook.com/share/1JkvQ9NTfy/'),
('footer_social_instagram', ''),
('footer_social_linkedin', ''),
('footer_social_tiktok', 'https://www.tiktok.com/@monarkainmobiliaria?is_from_webapp=1&sender_device=pc'),
('footer_social_twitter', ''),
('footer_social_whatsapp', 'https://wa.me/message/YTZV3H5XQ7QUO1'),
('footer_social_youtube', ''),
('header_hours', 'Lunes-Viernes 10:00 am a 5:00 pm · Sábados 9:00 am a 2:00 pm'),
('header_location', 'Baltazar Maldonado Edif. 34, depto, 6, San Diego, Apizaco, Mexico, 90338'),
('header_location_url', 'https://maps.app.goo.gl/AxYs189WtvzvxAMw9'),
('header_phone', '+522414122871'),
('header_phone_label', 'Llamar'),
('header_social_facebook', 'https://www.facebook.com/share/1JkvQ9NTfy/'),
('header_social_instagram', ''),
('header_social_linkedin', ''),
('header_social_tiktok', 'https://www.tiktok.com/@monarkainmobiliaria?is_from_webapp=1&sender_device=pc'),
('header_social_twitter', ''),
('header_social_whatsapp', 'https://wa.me/message/YTZV3H5XQ7QUO1'),
('header_social_youtube', ''),
('header_topbar_enabled', '1'),
('logo', 'uploads/branding/00fb286f74e2993c-1790717996.webp'),
('primary_color', '#ee6820'),
('site_address', 'Baltazar Maldonado Edif. 34, depto, 6, San Diego, Apizaco, Mexico, 90338'),
('site_email', 'monarcainmobiliaria@infinitummail.com'),
('site_logo', 'uploads/branding/00fb286f74e2993c-1790717996.webp'),
('site_phone', '+52 241 106 6028'),
('site_whatsapp', '+5215512345678');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'Asesor Senior',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin General', 'admin@luxespace.com', 'admin123', 'Administrador', '2026-09-29 17:17:51'),
(2, 'Arturo Martinez', 'martinez1hernandez2@gmail.com', 'Arturo18', 'Administrador', '2026-09-29 22:07:03');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indices de la tabla `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
