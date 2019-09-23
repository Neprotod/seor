-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 21 2018 г., 15:47
-- Версия сервера: 5.6.38
-- Версия PHP: 5.5.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `tree_site`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cache`
--

CREATE TABLE `cache` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `table_name` varchar(255) NOT NULL COMMENT 'Имя таблицы',
  `status` int(1) NOT NULL COMMENT 'Если status = 1 значит, в этой таблице произошли изменения'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `cache`
--

INSERT INTO `cache` (`id`, `table_name`, `status`) VALUES
(1, 'style', 1),
(2, 'style_content', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID категории',
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'id родителя',
  `id_author` int(10) UNSIGNED NOT NULL COMMENT 'id автора',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Метка создания',
  `title` varchar(255) NOT NULL COMMENT 'Имя категории',
  `url` varchar(60) DEFAULT NULL COMMENT 'URL категории',
  `meta_title` varchar(255) NOT NULL COMMENT 'Мета заголовок',
  `meta_keywords` varchar(255) DEFAULT NULL COMMENT 'Ключевые слова',
  `description` text COMMENT 'Описание',
  `robots` set('index','follow','noindex','nofollow') DEFAULT NULL COMMENT 'Как индексировать',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT 'Отключить или нет',
  `static` int(1) NOT NULL DEFAULT '0' COMMENT '1 = статическая категория, у нее нет URL может использоваться как папка, для подключения стилей или меню',
  `modified` date DEFAULT NULL COMMENT 'Когда был изменен',
  `content` text COMMENT 'Содержимое',
  `annotation` text COMMENT 'Краткое содержание',
  `content_type` int(10) UNSIGNED NOT NULL COMMENT 'id содержимого',
  `extends_content` int(10) UNSIGNED DEFAULT NULL COMMENT 'Какой контент наследуется для содержимого (таблица content_type)',
  `css` text COMMENT 'Краткое содержание',
  `js` text COMMENT 'Краткое содержание',
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Позиция'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `id_author`, `date`, `title`, `url`, `meta_title`, `meta_keywords`, `description`, `robots`, `status`, `static`, `modified`, `content`, `annotation`, `content_type`, `extends_content`, `css`, `js`, `position`) VALUES
(8, 0, 1, '2015-04-07 13:22:44', 'Работы', 'design-works', 'Работы', NULL, '', 'noindex,nofollow', 0, 0, '2018-05-24', NULL, NULL, 2, 3, NULL, NULL, 0),
(9, 8, 1, '2015-04-07 13:22:44', 'Работы2', 'design-works/test', 'Работы2', NULL, NULL, 'index,follow', 1, 1, '2015-06-17', NULL, NULL, 2, 4, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `category_post`
--

CREATE TABLE `category_post` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `id_category` int(10) UNSIGNED NOT NULL COMMENT 'id категории ',
  `id_post` int(10) UNSIGNED NOT NULL COMMENT 'id поста',
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Позиция'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `category_post`
--

INSERT INTO `category_post` (`id`, `id_category`, `id_post`, `position`) VALUES
(1, 8, 1, 1),
(2, 8, 2, 2),
(4, 9, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `content_type`
--

CREATE TABLE `content_type` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID типа контента',
  `id_template` int(10) UNSIGNED NOT NULL COMMENT 'id темы',
  `id_type` int(10) UNSIGNED NOT NULL COMMENT 'id типа',
  `name` varchar(60) NOT NULL COMMENT 'Имя файла',
  `ext` varchar(10) DEFAULT NULL COMMENT 'Расширение файла, если не указано будет использоватся php',
  `title` varchar(60) DEFAULT NULL COMMENT 'Имя для пояснения',
  `description` text COMMENT 'Описание если нужно',
  `path` varchar(100) DEFAULT NULL COMMENT 'Если путь отличается от типа (считает от корня темы)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `content_type`
--

INSERT INTO `content_type` (`id`, `id_template`, `id_type`, `name`, `ext`, `title`, `description`, `path`) VALUES
(1, 1, 1, 'main', NULL, 'Главная страница', 'Описание', NULL),
(2, 1, 3, 'work', NULL, 'Работы', 'Содержимое наших работ', NULL),
(3, 1, 2, 'work', NULL, 'Работы', NULL, NULL),
(4, 1, 2, 'test', NULL, NULL, NULL, NULL),
(5, 1, 1, 'mains', NULL, 'Главная страница', 'Описание', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `image`
--

CREATE TABLE `image` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID связи контент - стиль',
  `id_table` int(10) UNSIGNED NOT NULL COMMENT 'id в таблице',
  `id_type` int(10) UNSIGNED NOT NULL COMMENT 'id типа',
  `name` varchar(60) DEFAULT NULL COMMENT 'Имя для тега alt',
  `file` varchar(255) NOT NULL COMMENT 'Путь к картинке',
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Позиция'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `image_resize`
--

CREATE TABLE `image_resize` (
  `original` varchar(255) NOT NULL COMMENT 'Оригинальный файл',
  `resize` varchar(255) NOT NULL COMMENT 'Путь к измененному файлу'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `page`
--

CREATE TABLE `page` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID страницы',
  `id_author` int(10) UNSIGNED NOT NULL COMMENT 'id автора',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Метка создания',
  `title` varchar(255) NOT NULL COMMENT 'Заговолок страницы',
  `url` varchar(60) DEFAULT NULL COMMENT 'URL страницы',
  `meta_title` varchar(255) NOT NULL COMMENT 'Мета заголовок',
  `description` text COMMENT 'Описание',
  `robots` set('index','follow','noindex','nofollow') DEFAULT NULL COMMENT 'Как индексировать',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT 'Отключить или нет',
  `comment_status` int(1) NOT NULL DEFAULT '0' COMMENT 'Статус комментариев',
  `modified` date DEFAULT NULL COMMENT 'Когда был изменен',
  `content` text COMMENT 'Содержимое',
  `annotation` text COMMENT 'Краткое содержание',
  `content_type` int(10) UNSIGNED NOT NULL COMMENT 'id содержимого',
  `css` text COMMENT 'Краткое содержание',
  `js` text COMMENT 'Краткое содержание',
  `meta_keywords` text COMMENT 'Ключевые слова',
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Позиция'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `page`
--

INSERT INTO `page` (`id`, `id_author`, `date`, `title`, `url`, `meta_title`, `description`, `robots`, `status`, `comment_status`, `modified`, `content`, `annotation`, `content_type`, `css`, `js`, `meta_keywords`, `position`) VALUES
(1, 1, '2015-05-29 21:00:00', 'Тест', 'test', 'Тест', 'опи\"сан\'ие', 'index,follow', 1, 0, '2018-05-24', '<p>текст</p>', NULL, 1, NULL, '$(document).ready(function(){\r\n        \r\n        \r\n            var elementOpen = {\r\n                /*\r\n                start : \"flipInX\",\r\n                end : \"fadeOut\",\r\n                \r\n                *************\r\n                start : \"rotateInDownLeft\",\r\n                end : \"rotateOutUpRight\",\r\n                \r\n                *************\r\n                start : \"rollIn\",\r\n                end : \"rollOut\",\r\n                *************\r\n                start : \"bounceInDown\",\r\n                end : \"bounceOutUp\",\r\n                \r\n                **************\r\n                start : \"fadeInLeft\",\r\n                end : \"fadeOutRight\",\r\n                \r\n                **************\r\n                start : \"fadeInDown\",\r\n                end : \"fadeOutDown\",\r\n                */\r\n                \r\n                start : \"fadeInDown\",\r\n                end : \"fadeOutDown\",\r\n                \r\n                parent : \"\",\r\n                find : \".description\",\r\n                init : function(){\r\n                    var $set = $(\"#servis .float > div\");\r\n                    var object = (this);\r\n                    \r\n                    // Сохраняем родителя, что бы хранить индетификатор\r\n                    var parent = $set.parents(\"#servis\");\r\n                    \r\n                    (object).parent = parent[0];\r\n                    (object).parent.box = \'\';\r\n                    (object).parent.object = \'\';\r\n                    \r\n                    \r\n                    $set.click(function(){\r\n                        if((object).parent.box){\r\n                            // Проверка совпадает ли с нажатым\r\n                            if((object).parent.box == (this)){\r\n                                (object).action((object).parent.object);\r\n                                (object).parent.box = \'\';\r\n                                (object).parent.object = \'\';\r\n                            }else{\r\n                                //обнуляем существующий\r\n                                (object).action((object).parent.object);\r\n                                (object).parent.box = (this);\r\n                                (object).parent.object = $(this).find((object).find);\r\n                                //создаем новое действие\r\n                                (object).action((object).parent.object);\r\n                            }\r\n                        }else{\r\n                            (object).parent.box = (this);\r\n                            (object).parent.object = $(this).find((object).find);\r\n                            (object).action((object).parent.object);\r\n                        }\r\n                    });\r\n                    \r\n                    $set.hover(function(e){\r\n                        // проверка\r\n                        var check = true;\r\n                        if(e.type == \'mouseleave\'){\r\n                            var fromElem = e.fromElement || e.relatedTarget;\r\n                            var test = $(fromElem).parents(\"#servis\");\r\n                            if(!test[0]){\r\n                                check = false;\r\n                            }\r\n                        }\r\n                        \r\n                        if(check == false){\r\n                            (object).resetClass((object).parent.object);\r\n                            (object).setClass((object).parent.object,(object).end);\r\n                            (object).parent.box = \'\';\r\n                            (object).parent.object = \'\';\r\n                            return;\r\n                        }\r\n                        \r\n                        if((object).parent.box){\r\n                            if((object).parent.box == (this)){\r\n                                (object).action((object).parent.object);\r\n                                (object).parent.box = \'\';\r\n                                (object).parent.object = \'\';\r\n                            }else{\r\n                                //обнуляем существующий\r\n                                (object).action((object).parent.object);\r\n                                (object).parent.box = (this);\r\n                                (object).parent.object = $(this).find((object).find);\r\n                                //создаем новое действие\r\n                                (object).action((object).parent.object);\r\n                            }\r\n                        }else{\r\n                            (object).parent.box = (this);\r\n                            (object).parent.object = $(this).find((object).find);\r\n                            (object).action((object).parent.object);\r\n                        }\r\n                    });\r\n                },\r\n                action : function(element){\r\n                    if(!element.hasClass(\'animated\')){\r\n                        element.addClass(\'animated\');\r\n                        element.css(\'display\',\'block\');\r\n                    }\r\n                    if(element.hasClass((this).start)){\r\n                        element.removeClass((this).start);\r\n                        element.addClass((this).end);\r\n                    }\r\n                    else if(element.hasClass((this).end)){\r\n                        element.removeClass((this).end);\r\n                        element.addClass((this).start);\r\n                    }\r\n                    else{\r\n                        element.addClass((this).start);\r\n                    }\r\n                },\r\n                resetClass : function(element){\r\n                    try{\r\n                        if(element.hasClass((this).start)){\r\n                            element.removeClass((this).start);\r\n                        }\r\n                        else if(element.hasClass((this).end)){\r\n                            element.removeClass((this).end);\r\n                        }\r\n                    }catch(e){\r\n                        return false;\r\n                    }\r\n                },\r\n                setClass : function(element,classes){\r\n                    try{\r\n                        element.addClass(classes);\r\n                    }catch(e){\r\n                        return false;\r\n                    }\r\n                }\r\n                \r\n            };\r\n            \r\n            elementOpen.init();\r\n            \r\n            \r\n            \r\n        });', NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `post`
--

CREATE TABLE `post` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID поста',
  `id_author` int(10) UNSIGNED NOT NULL COMMENT 'id автора',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Метка создания',
  `title` varchar(255) NOT NULL COMMENT 'Заговолок поста',
  `url` varchar(60) DEFAULT NULL COMMENT 'URL страницы',
  `meta_title` varchar(255) NOT NULL COMMENT 'Мета заголовок',
  `meta_keywords` text COMMENT 'Ключевые слова',
  `description` text COMMENT 'Описание',
  `robots` set('index','follow','noindex','nofollow') DEFAULT NULL COMMENT 'Как индексировать',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT 'Отключить или нет',
  `comment_status` int(1) NOT NULL DEFAULT '0' COMMENT 'Статус комментариев',
  `modified` date DEFAULT NULL COMMENT 'Когда был изменен',
  `content` text COMMENT 'Содержимое',
  `annotation` text COMMENT 'Краткое содержание',
  `content_type` int(10) UNSIGNED DEFAULT NULL COMMENT 'id содержимого, если null используется установленный в категории',
  `css` text COMMENT 'Краткое содержание',
  `js` text COMMENT 'Краткое содержание',
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Позиция'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `post`
--

INSERT INTO `post` (`id`, `id_author`, `date`, `title`, `url`, `meta_title`, `meta_keywords`, `description`, `robots`, `status`, `comment_status`, `modified`, `content`, `annotation`, `content_type`, `css`, `js`, `position`) VALUES
(1, 1, '2015-04-07 16:12:43', 'Тестовый пост 1', 'post-1', 'Тестовый пост 1', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 1, '2015-04-07 16:12:43', 'Тестовый пост 2', 'post-2', 'Тестовый пост 2', NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, 4, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `robots`
--

CREATE TABLE `robots` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `id_table` int(10) UNSIGNED NOT NULL COMMENT 'id таблицы',
  `id_type` int(10) UNSIGNED NOT NULL COMMENT 'id типа',
  `robots` set('index','follow','noindex','nofollow') DEFAULT NULL COMMENT 'Как индексировать'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID настройки',
  `name` varchar(255) NOT NULL COMMENT 'Техническое имя',
  `value` text NOT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT 'Имя настройки(не обязательно)',
  `description` text COMMENT 'Описание',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT 'Отключить или нет',
  `position` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Позиция'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `title`, `description`, `status`, `position`) VALUES
(1, 'site_name', 'TEST', 'Имя сайта', '', 0, 1),
(2, 'original', 'media/original', 'Путь к оригинальным файлам', 'Изменения этого параметра может вызвать отключение всех картинок', 1, 0),
(3, 'resize', 'media/resize', 'Путь к измененным по размеру файлам', NULL, 1, 0),
(4, 'admin_page', '1', 'Сколько страниц выводить в админке', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `style`
--

CREATE TABLE `style` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID стиля',
  `id_template` int(10) UNSIGNED NOT NULL COMMENT 'id темы',
  `id_type` int(10) UNSIGNED DEFAULT NULL COMMENT 'id типа? если null относится к типу темы',
  `name` varchar(60) NOT NULL COMMENT 'Имя файла',
  `title` varchar(60) DEFAULT NULL,
  `description` text COMMENT 'Описание если нужно',
  `path` varchar(100) DEFAULT NULL COMMENT 'Если путь отличается от типа (считает от корня темы)',
  `style_type` int(10) UNSIGNED NOT NULL COMMENT 'id типа стиля',
  `default` int(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'По умочанию?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `style`
--

INSERT INTO `style` (`id`, `id_template`, `id_type`, `name`, `title`, `description`, `path`, `style_type`, `default`) VALUES
(1, 1, NULL, 'animate', 'Анимация', 'Описание', NULL, 1, 1),
(2, 1, 1, 'page', NULL, NULL, NULL, 1, 0),
(5, 1, 2, 'work', NULL, NULL, NULL, 1, 0),
(56, 1, NULL, 'menu', NULL, NULL, NULL, 2, 1),
(57, 1, NULL, 'work', NULL, NULL, NULL, 1, 0),
(70, 1, NULL, 'common', NULL, NULL, NULL, 1, 1),
(77, 1, 3, 'work', NULL, NULL, NULL, 1, 0),
(80, 1, 1, 'work', NULL, NULL, NULL, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `style_content`
--

CREATE TABLE `style_content` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID связи контент - стиль',
  `id_table` int(10) UNSIGNED NOT NULL COMMENT 'id в таблице',
  `id_style` int(10) UNSIGNED NOT NULL COMMENT 'id стиля',
  `id_type` int(10) UNSIGNED NOT NULL COMMENT 'id типа',
  `status` int(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'отключить определенный css, 1 = отключено',
  `extends` int(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Если это категория, то все ее страницы могут наследовать данный стиль'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `style_content`
--

INSERT INTO `style_content` (`id`, `id_table`, `id_style`, `id_type`, `status`, `extends`) VALUES
(4, 1, 3, 1, 0, 0),
(5, 9, 5, 3, 0, 1),
(7, 10, 5, 3, 1, 1),
(8, 1, 57, 1, 0, 0),
(24, 1, 80, 1, 0, 0),
(33, 1, 2, 1, 1, 0),
(34, 9, 77, 3, 0, 0),
(38, 9, 57, 3, 0, 0),
(43, 8, 77, 3, 1, 0),
(45, 8, 57, 3, 1, 0),
(46, 8, 57, 3, 1, 1),
(51, 1, 56, 1, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `style_type`
--

CREATE TABLE `style_type` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID типа стиля',
  `name` varchar(20) NOT NULL COMMENT 'Имя типа стиля',
  `folder` varchar(100) NOT NULL COMMENT 'Папка в теме по умолчанию'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `style_type`
--

INSERT INTO `style_type` (`id`, `name`, `folder`) VALUES
(1, 'css', 'css'),
(2, 'js', 'js');

-- --------------------------------------------------------

--
-- Структура таблицы `template`
--

CREATE TABLE `template` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID страницы',
  `name` varchar(255) NOT NULL COMMENT 'Техническое имя',
  `title` varchar(255) NOT NULL COMMENT 'Зоголовок темы',
  `description` text COMMENT 'описание',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT 'Используется по умочанию?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `template`
--

INSERT INTO `template` (`id`, `name`, `title`, `description`, `status`) VALUES
(1, 'default', 'По умолчанию', 'Описание данной темы', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `type`
--

CREATE TABLE `type` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID страницы',
  `type` varchar(255) NOT NULL COMMENT 'Типы данных',
  `description` text COMMENT 'описание типа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Типы данных, как категория или страница';

--
-- Дамп данных таблицы `type`
--

INSERT INTO `type` (`id`, `type`, `description`) VALUES
(1, 'page', NULL),
(2, 'post', NULL),
(3, 'category', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `type_content`
--

CREATE TABLE `type_content` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID связи контент - тип',
  `id_table` int(10) UNSIGNED NOT NULL COMMENT 'id в таблице',
  `content_type` int(10) UNSIGNED NOT NULL COMMENT 'id типа в таблице content_type',
  `id_type` int(10) UNSIGNED NOT NULL COMMENT 'id типа',
  `extends_content` int(10) UNSIGNED DEFAULT NULL COMMENT 'Нужен только для категорий, ID типа который должны наследовать посты'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `type_content`
--

INSERT INTO `type_content` (`id`, `id_table`, `content_type`, `id_type`, `extends_content`) VALUES
(1, 1, 1, 1, NULL),
(2, 8, 2, 3, 3),
(3, 9, 2, 3, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `url`
--

CREATE TABLE `url` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID страницы',
  `url` varchar(255) NOT NULL COMMENT 'адрес',
  `id_type` int(10) UNSIGNED DEFAULT NULL COMMENT 'id типа(категория, страница итп)',
  `id_table` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID таблицы',
  `id_canonical` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID url в этой же таблице, NULL = нет канонического'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `url`
--

INSERT INTO `url` (`id`, `url`, `id_type`, `id_table`, `id_canonical`) VALUES
(1, 'test', 1, 1, NULL),
(2, 'design-works', 3, 8, NULL),
(4, 'design-works/post-1', 2, 1, NULL),
(5, 'design-works/post-2', 2, 2, NULL),
(6, 'design-works/test/post-1', 2, 1, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `url_drop`
--

CREATE TABLE `url_drop` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID страницы',
  `url_reset` varchar(255) NOT NULL COMMENT 'Удаленный адрес',
  `id_url` int(10) UNSIGNED DEFAULT NULL COMMENT 'Id типа(категория, страница итп) если null полностью удалена',
  `time_drop` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `url_drop`
--

INSERT INTO `url_drop` (`id`, `url_reset`, `id_url`, `time_drop`) VALUES
(1, '/test', 1, '2015-03-29 14:22:15');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID Пользователя',
  `login` varchar(60) NOT NULL COMMENT 'Логин пользователя',
  `pass` char(32) NOT NULL COMMENT 'Имя файла',
  `nicename` varchar(60) NOT NULL COMMENT 'Ник пользователя',
  `email` varchar(60) NOT NULL COMMENT 'Email адрес',
  `url` varchar(60) DEFAULT NULL COMMENT 'URL пользователя есть есть',
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время регистрации',
  `activation_key` varchar(60) DEFAULT NULL COMMENT 'Ключ активации',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '1 = пользователь включен',
  `display_name` varchar(60) NOT NULL COMMENT 'Желаемое отоброжаемое имя'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `login`, `pass`, `nicename`, `email`, `url`, `registered`, `activation_key`, `status`, `display_name`) VALUES
(1, 'admin', '60b1417cea46c76de1eba68fdb342757', 'admin', 'mail@mail.ru', NULL, '2015-03-29 14:09:20', NULL, 1, 'admin');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukTable` (`table_name`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukMetaTitle` (`meta_title`),
  ADD UNIQUE KEY `ukTitle` (`title`);

--
-- Индексы таблицы `category_post`
--
ALTER TABLE `category_post`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukCategoryPost` (`id_category`,`id_post`);

--
-- Индексы таблицы `content_type`
--
ALTER TABLE `content_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukTemplateTypeNameExp` (`id_template`,`id_type`,`name`,`ext`);

--
-- Индексы таблицы `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukTableTypeFile` (`id_table`,`id_type`,`file`);

--
-- Индексы таблицы `image_resize`
--
ALTER TABLE `image_resize`
  ADD PRIMARY KEY (`original`,`resize`);

--
-- Индексы таблицы `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukMetaTitle` (`meta_title`),
  ADD UNIQUE KEY `ukurl` (`url`);

--
-- Индексы таблицы `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukMetaTitle` (`meta_title`),
  ADD UNIQUE KEY `ukurl` (`url`);

--
-- Индексы таблицы `robots`
--
ALTER TABLE `robots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukTableTypeRobots` (`id_table`,`id_type`,`robots`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukName` (`name`);

--
-- Индексы таблицы `style`
--
ALTER TABLE `style`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukTemplateTypeNameStyle` (`id_template`,`id_type`,`name`,`style_type`);

--
-- Индексы таблицы `style_content`
--
ALTER TABLE `style_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukTableStyleTypeExtends` (`id_table`,`id_style`,`id_type`,`extends`);

--
-- Индексы таблицы `style_type`
--
ALTER TABLE `style_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukName` (`name`);

--
-- Индексы таблицы `template`
--
ALTER TABLE `template`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukName` (`name`);

--
-- Индексы таблицы `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukType` (`type`);

--
-- Индексы таблицы `type_content`
--
ALTER TABLE `type_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukTableContentTypeExtends` (`id_table`,`content_type`,`id_type`,`extends_content`);

--
-- Индексы таблицы `url`
--
ALTER TABLE `url`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukNameUrl` (`url`);

--
-- Индексы таблицы `url_drop`
--
ALTER TABLE `url_drop`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukResetUrl` (`url_reset`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ukLogin` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cache`
--
ALTER TABLE `cache`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID категории', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `category_post`
--
ALTER TABLE `category_post`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `content_type`
--
ALTER TABLE `content_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа контента', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `image`
--
ALTER TABLE `image`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - стиль';

--
-- AUTO_INCREMENT для таблицы `page`
--
ALTER TABLE `page`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `post`
--
ALTER TABLE `post`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID поста', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `robots`
--
ALTER TABLE `robots`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- AUTO_INCREMENT для таблицы `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID настройки', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `style`
--
ALTER TABLE `style`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID стиля', AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT для таблицы `style_content`
--
ALTER TABLE `style_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - стиль', AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT для таблицы `style_type`
--
ALTER TABLE `style_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа стиля', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `template`
--
ALTER TABLE `template`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `type`
--
ALTER TABLE `type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `type_content`
--
ALTER TABLE `type_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - тип', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `url`
--
ALTER TABLE `url`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `url_drop`
--
ALTER TABLE `url_drop`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID Пользователя', AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
