-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Окт 25 2026 г., 19:14
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `php_blog_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `brief` text NOT NULL,
  `content` text NOT NULL,
  `category` enum('home','world','business','technology','science','culture','sport') NOT NULL DEFAULT 'home',
  `main_image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `brief`, `content`, `category`, `main_image`, `created_at`) VALUES
(1, 1, 'Welcome to $haNews!', 'This is a test post in the database. The main text of the article will appear here. An administrator can make it as long as they like.', '<h3>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</h3><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 'home', 'uploads/default_cover.jpg', '2026-10-24 17:29:56'),
(2, 1, 'The Rumble of the Century: Muhammad Ali vs. Joe Frazier', 'Beyond a boxing match—how three epic bouts between two legendary champions defined a generation, tested the limits of human endurance, and changed sports history forever.', '<p>In the annals of sports history, few rivalries transcend the boundaries of their respective games to become cultural touchstones. The trilogy between Muhammad Ali and Joe Frazier is the absolute gold standard of combat sports—not simply because of the sheer violence and brilliance displayed inside the squared circle, but because it mirrored the deep social, political, and emotional currents of a turbulent era. When these two undefeated champions collided, it was never just about a belt; it was a clash of philosophies, pride, and unbreakable wills.</p><p><br></p><p>To fully grasp the magnitude of their rivalry, one must understand who they were before the world forced them into opposing corners. Muhammad Ali was the charismatic, fast-talking iconoclast who revolutionized heavyweight boxing with speed, unorthodox angles, and unmatched psychological warfare. He had captured the imagination of the global public not only through his athletic wizardry but through his vocal stance against the Vietnam War and his fight for civil rights. Joe Frazier, on the other hand, represented the quintessential working-class hero. Born into poverty in the rural South, Frazier forged his legendary toughness in the slaughterhouses of Philadelphia, working grueling hours before training in unheated gyms. He possessed a relentless, forward-moving style built around suffocating pressure and arguably the most devastating left hook in boxing history.</p><p><br></p><h3>The Trilogy That Defined Modern Combat</h3><p><br></p><p>Their first encounter, on March 8, 1971, at Madison Square Garden, was billed as the \"Fight of the Century.\" Both men were undefeated, both had legitimate claims to the crown, and the cultural anticipation was deafening. For fifteen breathtaking rounds, the two warriors pushed each other to the absolute brink of human capability. Frazier’s relentless body attack and thunderous left hooks wore down Ali’s defenses, culminating in a historic knockdown in the 15th round that sealed a unanimous decision victory for Frazier. It was Ali’s first professional defeat, stripping away his aura of invincibility and cementing Frazier as an all-time great.</p><p><br></p><blockquote>Float like a butterfly, sting like a bee. His hands can\'t hit what his eyes can\'t see.</blockquote><p><br></p><p>Yet, boxing is a sport defined by rematches, and the narrative only grew more complex. Ali won the second bout in 1974 via a tactical decision, setting the stage for their legendary rubber match: the \"Thrilla in Manila\" on October 1, 1975. Fought in stifling, brutal heat in the Philippines, the third fight transcended athletic competition and became a brutal test of survival. Ali later described it as the closest he ever came to dying. For fourteen rounds, they exchanged hellish punishment, neither man willing to yield an inch of pride. Between rounds 14 and 15, Frazier’s legendary trainer, Eddie Futch, wisely intervened and stopped the fight, preventing his fighter from coming out for the final round—though Ali’s corner was simultaneously preparing to do the exact same thing.</p><p><br></p><p>Decades have passed since the final bell rang in Manila, but the legacy of Ali and Frazier remains etched into the bedrock of sports culture. Though their relationship was marred for years by harsh words and bitter misunderstandings—largely driven by Ali’s cruel promotional tactics—time eventually softened the edges, revealing a deep, undeniable mutual respect between two men who needed each other to achieve immortality. They were fierce antagonists who brought out the absolute best in one another, leaving behind a timeless reminder of what true courage looks like when two titans refuse to break.</p>', 'sport', 'uploads/1792951166_FraizierVsAliMain.jpg', '2026-10-25 17:59:26');

-- --------------------------------------------------------

--
-- Структура таблицы `post_images`
--

CREATE TABLE `post_images` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `post_images`
--

INSERT INTO `post_images` (`id`, `post_id`, `image_path`) VALUES
(1, 2, 'uploads/1792951166_0_FraizierVsAli4.jpg'),
(2, 2, 'uploads/1792951166_1_FraizierVsAli3.jpg'),
(3, 2, 'uploads/1792951166_2_FraizierVsAli2.jpg'),
(4, 2, 'uploads/1792951166_3_FraizierVsAli1.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('like','dislike') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'default_avatar.png',
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `avatar`, `role`, `created_at`, `remember_token`) VALUES
(1, 'Admin', 'Admin@example.com', '$2y$10$A8zHSjbIQqLWkb4HqD5JP.4IhU8gWgrHAtZeyu1FsGozrhKC8f9c6', 'default_avatar.png', 'admin', '2026-10-24 17:19:48', NULL),
(2, 'User', 'User@example.com', '$2y$10$Nts816lq1RnbvdX68Kym5.W46aJqVQrOsDWH7JuS/6SYe0TBOCAXu', 'default_avatar.png', 'user', '2026-10-24 17:20:13', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `post_images`
--
ALTER TABLE `post_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Индексы таблицы `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_post_reaction` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `post_images`
--
ALTER TABLE `post_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `post_images`
--
ALTER TABLE `post_images`
  ADD CONSTRAINT `post_images_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
