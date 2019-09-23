-- Создаем базу данных
CREATE DATABASE IF NOT EXISTS seor_db CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Используем базу данных
USE seor_db;
-- *******************************************************
-- Таблица адресов


-- Для кеширования данных, в XML формате

CREATE TABLE IF NOT EXISTS cache
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    table_name VARCHAR(255) NOT NULL COMMENT 'Имя таблицы',
    status INT(1) NOT NULL COMMENT 'Если status = 1 значит, в этой таблице произошли изменения',
    CONSTRAINT pkId PRIMARY KEY (id), CONSTRAINT ukTable UNIQUE KEY (table_name)
)ENGINE = INNODB;

INSERT INTO cache(table_name,status) VALUES
    ('style',0),
    ('style_content',0);

-----------------------------------------------------------------
-------------------------------------------------------------------
--------------------------------------------------------------------





-- Админ пользователи

CREATE TABLE IF NOT EXISTS admin_user
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID Пользователя',
    login VARCHAR(60) NOT NULL COMMENT 'Логин пользователя',
    pass CHAR(32) NOT NULL COMMENT 'Пароль',
    email VARCHAR(60) NOT NULL COMMENT 'Email адрес',
    registered TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время регистрации',
    activation_key VARCHAR(60) NULL DEFAULT NULL COMMENT 'Ключ активации',
    status INT(1) NOT NULL DEFAULT 1 COMMENT '1 = пользователь включен',
    display_name VARCHAR(60) NOT NULL COMMENT 'Желаемое отоброжаемое имя',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'id типа пользователя, администратон он или модератор',
    PRIMARY KEY (id), CONSTRAINT ukLogin UNIQUE KEY (login),
    INDEX ixIdType(id_type),
    CONSTRAINT fkIdType FOREIGN KEY (id_type)
            REFERENCES admin_type (id) 
)ENGINE = INNODB;
INSERT INTO admin_user(login,pass,email,display_name,id_type) VALUES
    ("admin","852456a","mail@webdzen.com","Разработчик",1);

-- Админ типы как модератор, администратор

CREATE TABLE IF NOT EXISTS admin_type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID Пользователя',
    type VARCHAR(60) NOT NULL COMMENT 'Тип пользователя',
    title VARCHAR(60) NOT NULL COMMENT 'Название для чтения',
    description TEXT NULL DEFAULT NULL COMMENT 'Описание',
    PRIMARY KEY (id), CONSTRAINT ukType UNIQUE KEY (type), CONSTRAINT ukTitle UNIQUE KEY (title)
)ENGINE = INNODB;
INSERT INTO admin_type(type,title) VALUES
    ('development','Разработчик');
    
    
-- Как индексировать (если нужно) НУЖНО ОПТИМИЗИРОВАТЬ

CREATE TABLE IF NOT EXISTS robots
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    robots SET('index','noindex','follow','nofollow') NULL DEFAULT NULL COMMENT 'Как индексировать',
    title VARCHAR(60) NOT NULL COMMENT 'Название для чтения',
    PRIMARY KEY (id), CONSTRAINT ukTableTypeRobots UNIQUE KEY (robots)
)ENGINE = INNODB;

INSERT INTO robots(robots) VALUES
    ('index,follow'),
    ('index,nofollow'),
    ('noindex,follow'),
    ('noindex,nofollow');
    
-- Настройки

CREATE TABLE IF NOT EXISTS settings
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID настройки',
    name VARCHAR(255) NOT NULL COMMENT 'Техническое имя',
    value TEXT NOT NULL COMMENT 'Значение',
    title VARCHAR(255) NULL DEFAULT NULL COMMENT 'Имя настройки(не обязательно)',
    description TEXT NULL DEFAULT NULL COMMENT 'Описание',
    status INT(1) NOT NULL DEFAULT 1 COMMENT 'Отключить или нет',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (name)
)ENGINE = INNODB;

INSERT INTO settings(name,value,title,description,position) VALUES
     ("site_name","seor","Имя сайта",NULL,1),
     ("original","media/original","Путь к оригинальным файлам","Изменения этого параметра может вызвать отключение всех картинок",0),
     ("resize","media/resize","Путь к измененным по размеру файлам",NULL,0),
     ("admin_page","1","Сколько страниц выводить в админке",NULL,0);


-- Темы (type).

CREATE TABLE IF NOT EXISTS template
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID темы',
    name VARCHAR(255) NOT NULL COMMENT 'Техническое имя',
    title VARCHAR(255) NOT NULL COMMENT 'Зоголовок темы',
    description TEXT NULL DEFAULT NULL COMMENT 'Описание',
    status INT(1) NOT NULL DEFAULT 0 COMMENT 'Используется по умочанию?',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (name)
)ENGINE = INNODB;

INSERT INTO template(name,title,status) VALUES
    ('default','По умолчанию',1);
    

INSERT INTO template(name,title,status) VALUES
    ('default','По умолчанию',1);

    
    

-- Типы для mcm

CREATE TABLE IF NOT EXISTS mcm_type (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа',
    type VARCHAR(20) NOT NULL COMMENT 'Тип класса как module, controller, model',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (type)
)ENGINE = InnoDB;

INSERT INTO mcm_type(type) VALUES
    ('module'),
    ('controller'),
    ('model');

SELECT id, type
FROM  mcm_type;   
-- Модуль контроллер модель MCM. Сортируется внутри PHP
  
CREATE  TABLE IF NOT EXISTS mcm (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID класса',
    class_name VARCHAR(20) NOT NULL COMMENT 'Имя класса',
    description VARCHAR(60) NULL DEFAULT NULL COMMENT 'Описание класса',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'ID класса',
    id_class INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID класса',
    admin INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 - если административный',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (class_name, id_type, id_class, admin),
    INDEX ixIdType(id_type),
    CONSTRAINT fkType FOREIGN KEY (id_type)
            REFERENCES mcm_type (id)
)ENGINE = InnoDB;

-- Права на методы

CREATE  TABLE IF NOT EXISTS permission (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_mcm INT(10) UNSIGNED NOT NULL COMMENT 'ID класса',
    method VARCHAR(40) NOT NULL COMMENT 'Имя метода (правила)',
    description VARCHAR(60) NULL DEFAULT NULL COMMENT 'Описание',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (id_mcm, method),
    INDEX ixIdMcm (id_mcm),
    CONSTRAINT fkMcm FOREIGN KEY (id_mcm)
            REFERENCES mcm (id) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE = InnoDB;

-- Уточнения для прав.

CREATE  TABLE IF NOT EXISTS rule (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_permission INT(10) UNSIGNED NOT NULL COMMENT 'ID прав',
    rule VARCHAR(40) NOT NULL COMMENT 'Имя метода (правила)',
    description VARCHAR(60) NULL DEFAULT NULL COMMENT 'Описание',
    sql_stat INT(1) NOT NULL DEFAULT 0 COMMENT 'Генерировалось с помощью sql запроса или нет',
    CONSTRAINT pkID PRIMARY KEY (id),
    CONSTRAINT ukPermRul UNIQUE KEY (id_permission, rule, sql_stat),
    INDEX ixPermission (id_permission),
    CONSTRAINT fkPermission FOREIGN KEY (id_permission)
            REFERENCES permission (id) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE = InnoDB;

-- Связь admin_type и permission.

CREATE  TABLE IF NOT EXISTS admin_permission (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_admin_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа пользователя',
    id_permission INT(10) UNSIGNED NOT NULL COMMENT 'ID прав',
    status INT(1) UNSIGNED NOT NULL COMMENT '1 - разрешен 0 - запрещен',
    CONSTRAINT pkID PRIMARY KEY (id), CONSTRAINT ukAdminPermission UNIQUE KEY (id_admin_type, id_permission),
    INDEX ixPermission (id_permission),
    CONSTRAINT fkPermissionAdmin FOREIGN KEY (id_permission)
            REFERENCES permission (id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX ixType (id_admin_type),        
    CONSTRAINT fkTypeAdmin FOREIGN KEY (id_admin_type)
            REFERENCES admin_type (id) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE = InnoDB;

-- Связь admin_type и rule.

CREATE  TABLE IF NOT EXISTS admin_rule (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_admin_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа пользователя',
    id_rule INT(10) UNSIGNED NOT NULL COMMENT 'ID прав',
    status INT(1) UNSIGNED NOT NULL COMMENT '1 - разрешен 0 - запрещен',
    CONSTRAINT pkID PRIMARY KEY (id), CONSTRAINT ukAdminRule UNIQUE KEY (id_admin_type, id_rule)
    INDEX ixPermission (id_rule),
    CONSTRAINT fkRuleAdmin FOREIGN KEY (id_rule)
            REFERENCES rule (id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX ixType (id_admin_type),        
    CONSTRAINT fkIdTypeAdmin FOREIGN KEY (id_admin_type)
            REFERENCES admin_type (id) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE = InnoDB;
-- Типы данных (type).

CREATE TABLE IF NOT EXISTS type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа',
    type VARCHAR(255) NOT NULL COMMENT 'Типы данных',
    description TEXT NULL COMMENT 'Описание типа',
    PRIMARY KEY (id), CONSTRAINT ukType UNIQUE KEY (type)
)ENGINE = INNODB, COMMENT = "Типы данных, как категория или страница";

INSERT INTO type(type) VALUES
    ('page'),
    ('category'),
    ('vacancy'),
    ('specialization'),
    ('action');

-- Типы контента (связь какой файл к какому типу контента относится)

CREATE TABLE IF NOT EXISTS content_type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа контента',
    id_template INT(10) UNSIGNED NOT NULL COMMENT 'ID темы',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа',
    name VARCHAR(60) NOT NULL COMMENT 'Имя файла',
    exp VARCHAR(10) NULL DEFAULT NULL COMMENT 'Расширение файла, если не указано будет использоватся php',
    title VARCHAR(60) NULL DEFAULT NULL COMMENT 'Имя для пояснения',
    description TEXT NULL DEFAULT NULL COMMENT 'Описание если нужно',
    path VARCHAR(100) NULL DEFAULT NULL COMMENT 'Если путь отличается от типа (считает от корня темы)',
    class VARCHAR(100) NULL DEFAULT NULL COMMENT 'Имя модели в модуле, если NULL будет по умолчанию',
    PRIMARY KEY (id), CONSTRAINT ukTemplateTypeNameExp UNIQUE KEY (id_template,id_type,name,exp),
    INDEX ixTemplate (id_template),
    CONSTRAINT fkContentTypeTemplate FOREIGN KEY (id_template)
            REFERENCES template (id),
    INDEX ixType (id_type),
    CONSTRAINT fkContentTypeType FOREIGN KEY (id_type)
            REFERENCES type (id)
)ENGINE = INNODB;    

-- Таблица адресов
    
CREATE TABLE IF NOT EXISTS url
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы',
    url VARCHAR(255) NULL COMMENT 'Сам URL',
    id_type INT(10) UNSIGNED NULL COMMENT 'id типа(категория, страница итп)',
    id_table INT(10) UNSIGNED NULL COMMENT 'ID в связанной таблице.',
    id_canonical INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID на эту же таблицу, если есть кононичкская ссылка.',
    post INT(10) UNSIGNED NULL COMMENT DEFAULT NULL 'Является ли запрос post.',
    regex INT(1) UNSIGNED NULL DEFAULT NULL COMMENT 'Является ли строка регулярным выражением',
    id_content_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Тип контента по умолчанию.',
    PRIMARY KEY (id), 
    CONSTRAINT ukNameUrl UNIQUE KEY (url),
    CONSTRAINT ukTypeTable UNIQUE KEY (id_type,id_table),
    INDEX ixType(id_type),
    INDEX ixRegex(regex),
    INDEX ixPost(post),
    INDEX ixRegex(regex),
    INDEX ixContentType(id_content_type),
    CONSTRAINT fkUrlContentType FOREIGN KEY (id_content_type)
            REFERENCES content_type (id),
    CONSTRAINT fkUrlType FOREIGN KEY (id_type)
            REFERENCES type (id),
    CONSTRAINT fkUrlCanonical FOREIGN KEY (id_canonical)
            REFERENCES url (id)
    CONSTRAINT fkUrlPost FOREIGN KEY (post)
            REFERENCES url (id) ON DELETE SET NULL;
)ENGINE = INNODB;

-- Таблица удаленных URL

CREATE TABLE IF NOT EXISTS url_drop
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы',
    url_reset VARCHAR(255) NOT NULL COMMENT 'Удаленный адрес',
    id_url INT(10) UNSIGNED NULL COMMENT 'id удаленного URL',
    time_drop TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время записи',
    PRIMARY KEY (id), CONSTRAINT ukResetUrl UNIQUE KEY (url_reset),
    INDEX ixIdUrl(id_url),
    CONSTRAINT fkUrlDropId FOREIGN KEY (id_url)
            REFERENCES url (id) ON DELETE CASCADE
)ENGINE = INNODB;

INSERT INTO url_drop (url_reset,id_url) VALUES
    ("testin",1) ON DUPLICATE KEY UPDATE  id_url = 2;

-- Доступные у темы css и JS

CREATE TABLE IF NOT EXISTS style_type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа стиля',
    name VARCHAR(20) NOT NULL COMMENT 'Имя типа стиля',
    folder VARCHAR(100) NOT NULL COMMENT 'Папка в теме по умолчанию',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (name)
)ENGINE = INNODB;

INSERt INTO style_type(name,folder) VALUES
    ('css','css'),
    ('js','js');

-- Доступные у темы css и JS

CREATE TABLE IF NOT EXISTS style
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID стиля',
    id_template INT(10) UNSIGNED NOT NULL COMMENT 'ID темы',
    id_type INT(10) UNSIGNED NULL COMMENT 'ID типа? если null относится к типу темы',
    name VARCHAR(60) NOT NULL COMMENT 'Имя файла',
    title VARCHAR(60) NULL DEFAULT NULL COMMENT 'Заголовок',
    description TEXT NULL DEFAULT NULL COMMENT 'Описание если нужно',
    path VARCHAR(100) NULL DEFAULT NULL COMMENT 'Если путь отличается от типа (считает от корня темы)',
    style_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа стиля',
    `default` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'По умочанию?',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukTemplateTypeNameStyle UNIQUE KEY (id_template,id_type,name,style_type),
    INDEX ixIdTemplate(id_template),
    CONSTRAINT fkStyleTemplate FOREIGN KEY (id_template)
            REFERENCES template (id),
    INDEX ixIdStyleType(style_type),
    CONSTRAINT fkStyleStyleType FOREIGN KEY (style_type)
            REFERENCES style_type (id),
    INDEX ixIdType(id_type),
    CONSTRAINT fkStyleType FOREIGN KEY (id_type)
            REFERENCES type (id)
)ENGINE = INNODB;

ALTER TABLE style ADD position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция';
-- Какие стили, к каким страницам 

CREATE TABLE IF NOT EXISTS style_content
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_table INT(10) UNSIGNED NOT NULL COMMENT 'ID связи контент - стиль.',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа',
    id_style INT(10) UNSIGNED NOT NULL COMMENT 'ID стиля',
    status INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Отключить определенный css, 0 = отключено',
    extends INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Если это категория, то все ее страницы могут наследовать данный стиль',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ID стиля',
    PRIMARY KEY (id), CONSTRAINT ukTableStyleTypeExtends UNIQUE KEY (id_table,id_style,id_type,extends),
    INDEX ixIdStyle(id_style),
    CONSTRAINT fkStyleContentStyle FOREIGN KEY (id_style)
            REFERENCES style (id),
    INDEX ixIdType(id_type),
    CONSTRAINT fkStyleContentType FOREIGN KEY (id_type)
            REFERENCES type (id)
)ENGINE = INNODB;

-- Какие стили, к каким типам данных 

CREATE TABLE IF NOT EXISTS style_content
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_content_type INT(10) UNSIGNED NOT NULL COMMENT 'ID связи content_type.',
    id_style INT(10) UNSIGNED NOT NULL COMMENT 'ID стиля',
    status INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Отключить определенный css, 0 = отключено',
    extends INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Если это категория, то все ее страницы могут наследовать данный стиль',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ID стиля',
    PRIMARY KEY (id), CONSTRAINT ukTableStyleTypeExtends UNIQUE KEY (id_content_type,id_style),
    INDEX ixIdStyle(id_style),
    CONSTRAINT fkStyleContentStyle FOREIGN KEY (id_style)
            REFERENCES style (id),
    INDEX ixIdContentType(id_content_type),
    CONSTRAINT fkStyleContentCType FOREIGN KEY (id_content_type)
            REFERENCES content_type (id)
)ENGINE = INNODB;


-- Связь типа данных со страницей, может быть только одна на каждую тему

CREATE TABLE IF NOT EXISTS type_content
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - тип',
    id_table INT(10) UNSIGNED NOT NULL COMMENT 'ID в связанной таблице',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа',
    content_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа в таблице content_type',
    extends_content INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Нужен только для категорий, ID типа который должны наследовать посты',
    PRIMARY KEY (id), CONSTRAINT ukTableContentTypeExtends UNIQUE KEY (id_table,content_type,id_type,extends_content),
    INDEX ixContentType(content_type),
    CONSTRAINT fkTypeContentContentType FOREIGN KEY (content_type)
            REFERENCES content_type (id),
    INDEX ixIdType(id_type),
    CONSTRAINT fkTypeContentType FOREIGN KEY (id_type)
            REFERENCES type (id)
)ENGINE = INNODB;


-- Имена полей.

CREATE TABLE IF NOT EXISTS fields_name
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - тип',
    name VARCHAR(60) NULL DEFAULT NULL COMMENT 'Имя поля',
    title VARCHAR(255) NULL DEFAULT NULL COMMENT 'Заголовое поля для описания',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (name)
)ENGINE = INNODB;

-- Любые дополнительные поля.

CREATE TABLE IF NOT EXISTS fields
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - тип',
    id_table INT(10) UNSIGNED NOT NULL COMMENT 'ID в связанной таблице',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа',
    id_name INT(10) UNSIGNED NOT NULL COMMENT 'Имя поля',
    var VARCHAR(255) NULL DEFAULT NULL COMMENT 'Значения поля, либо var или text',
    text TEXT NULL DEFAULT NULL COMMENT 'Текстовое поле, либо var или text',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id),
    INDEX ixIdTableType(id_table, id_type),
    INDEX ixIdType(id_type),
    CONSTRAINT fkFieldsType FOREIGN KEY (id_type)
            REFERENCES type (id),
    INDEX ixIdName(id_name),
    CONSTRAINT fkFieldsName FOREIGN KEY (id_name)
            REFERENCES fields_name (id)
)ENGINE = INNODB;

-- Имена картинок

CREATE TABLE IF NOT EXISTS image_name
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - тип',
    name VARCHAR(60) NULL DEFAULT NULL COMMENT 'Имя поля',
    title VARCHAR(255) NULL DEFAULT NULL COMMENT 'Заголовое поля для описания',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (name)
)ENGINE = INNODB;

-- Картинки для содержимого

CREATE TABLE IF NOT EXISTS image
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_name INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID в image_name для поиска картинки',
    id_table INT(10) UNSIGNED NOT NULL COMMENT 'ID в таблице',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа',
    file VARCHAR(255) NOT NULL COMMENT 'Путь к картинке',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukTableTypeFile UNIQUE KEY (id_table,id_type,file),
    INDEX ixIdType(id_type),
    CONSTRAINT fkImageType FOREIGN KEY (id_type)
            REFERENCES type (id),
    INDEX ixIdName(id_name),
    CONSTRAINT fkImageName FOREIGN KEY (id_name)
            REFERENCES image_name (id)
)ENGINE = INNODB;


-- Сохраняем пути изменения размера изображения

CREATE TABLE IF NOT EXISTS image_resize
(
    original VARCHAR(255) NOT NULL COMMENT 'Оригинальный файл',
    resize VARCHAR(255) NOT NULL COMMENT 'Путь к измененному файлу',
    CONSTRAINT pkOriginalResize PRIMARY KEY (original,resize)
)ENGINE = INNODB;

-- [ВАРИАНТ 2] Сохраняем пути изменения размера изображения

CREATE TABLE IF NOT EXISTS image_resize
(
    original VARCHAR(255) NOT NULL COMMENT 'Оригинальный файл',
    resize VARCHAR(255) NOT NULL COMMENT 'Путь к измененному файлу',
    id_image INT(10) UNSIGNED NULL COMMENT 'ID в таблице',
    CONSTRAINT pkOriginalResize PRIMARY KEY (original,resize),
    INDEX ixIdIamge(id_image),
    CONSTRAINT fkImageResizeID FOREIGN KEY (id_image)
            REFERENCES image (id)
)ENGINE = INNODB;


-- Дополнительные стили которые хранятся в базе

CREATE TABLE IF NOT EXISTS js_css
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_table INT(10) UNSIGNED NOT NULL COMMENT 'ID в таблице',
    id_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа',
    css TEXT NULL DEFAULT NULL COMMENT 'Свой CSS',
    js TEXT NULL DEFAULT NULL COMMENT 'Свой JS',
    PRIMARY KEY (id), CONSTRAINT ukTableTypeFile UNIQUE KEY (id_table,id_type),
    INDEX ixIdType(id_type),
    CONSTRAINT fkJsCssType FOREIGN KEY (id_type)
            REFERENCES type (id)
)ENGINE = INNODB;


-- Статические страницы

CREATE TABLE IF NOT EXISTS page
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы',
    id_admin_user INT(10) UNSIGNED NOT NULL COMMENT 'ID автора',
    url INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'id URL есть он есть',
    url_name VARCHAR(60) NULL DEFAULT NULL COMMENT 'Личный URL нужен для корректного создания полного URL',
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Метка создания',
    title VARCHAR(255) NOT NULL COMMENT 'Заговолок страницы',
    meta_title VARCHAR(255) NOT NULL COMMENT 'Мета заголовок',
    meta_keywords TEXT NULL DEFAULT NULL COMMENT 'Ключевые слова',
    description TEXT NULL DEFAULT NULL COMMENT 'Описание',
    robots  INT(10) UNSIGNED NULL COMMENT 'ID robots',
    status INT(1) NOT NULL DEFAULT 1 COMMENT 'Отключить или нет',
    modified DATE NULL DEFAULT NULL COMMENT 'Когда был изменен',
    content TEXT NULL COMMENT 'Содержимое',
    content_type INT(10) UNSIGNED NOT NULL COMMENT 'ID содержимого',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukMetaTitle UNIQUE KEY (meta_title), CONSTRAINT ukUrl UNIQUE KEY (url),
    INDEX ixAdminUser (id_admin_user),
    CONSTRAINT fkPageAdminUser FOREIGN KEY (id_admin_user)
            REFERENCES admin_user (id),
    INDEX ixRobots (robots),
    CONSTRAINT fkPageRobots FOREIGN KEY (robots)
            REFERENCES robots (id),
    INDEX ixContentType (content_type) ON UPDATE CASCADE,
    CONSTRAINT fkPageContentType FOREIGN KEY (content_type)
            REFERENCES content_type (id),
    INDEX ixURL (url),
    CONSTRAINT fkPageURL FOREIGN KEY (url)
            REFERENCES url (id),
)ENGINE = INNODB;

-- Категории

CREATE TABLE IF NOT EXISTS categories
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID категории',
    parent_id INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'id родителя',
    id_admin_user INT(10) UNSIGNED NOT NULL COMMENT 'id автора',
    url INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'id URL есть он есть',
    url_name VARCHAR(60) NULL DEFAULT NULL COMMENT 'Имя URL для связи',
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Метка создания',
    title VARCHAR(255) NOT NULL COMMENT 'Имя категории',
    meta_title VARCHAR(255) NOT NULL COMMENT 'Мета заголовок',
    meta_keywords TEXT NULL DEFAULT NULL COMMENT 'Ключевые слова',
    description TEXT NULL DEFAULT NULL COMMENT 'Описание',
    robots  INT(10) UNSIGNED NULL COMMENT 'ID robots',
    status INT(1) NOT NULL DEFAULT 1 COMMENT 'Отключить или нет',
    static INT(1) NOT NULL DEFAULT 0 COMMENT '1 = статическая категория, у нее нет URL может использоваться как папка, для подключения стилей или меню',
    modified DATE NULL DEFAULT NULL COMMENT 'Когда был изменен',
    content TEXT NULL COMMENT 'Содержимое',
    annotation TEXT NULL DEFAULT NULL COMMENT 'Краткое содержание',
    content_type INT(10) UNSIGNED NOT NULL COMMENT 'id содержимого',
    extends_content INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Какой контент наследуется для содержимого (таблица content_type)',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukMetaTitle UNIQUE KEY (meta_title), CONSTRAINT ukTitle UNIQUE KEY (title),
    INDEX ixAdminUser (id_admin_user),
    CONSTRAINT fkCategodiesAdminUser FOREIGN KEY (id_admin_user)
            REFERENCES admin_user (id),
    INDEX ixRobots (robots),
    CONSTRAINT fkCategodiesRobots FOREIGN KEY (robots)
            REFERENCES robots (id) ON UPDATE CASCADE,
    INDEX ixContentType (content_type),
    CONSTRAINT fkCategodiesContentType FOREIGN KEY (content_type)
            REFERENCES content_type (id),
    INDEX ixExtContent (extends_content),
    CONSTRAINT fkCategodiesExtContentType FOREIGN KEY (extends_content)
            REFERENCES content_type (id),
    INDEX ixURL (url),
    CONSTRAINT fkCategodiesURL FOREIGN KEY (url)
            REFERENCES url (id),
    INDEX ixParent(parent_id),
    CONSTRAINT fkCategodiesParent FOREIGN KEY (parent_id)
            REFERENCES categories (id) ON DELETE CASCADE;
)ENGINE = INNODB;

-- Связь, страница - категория.

CREATE TABLE IF NOT EXISTS category_page
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_category INT(10) UNSIGNED NOT NULL COMMENT 'id категории ',
    id_page INT(10) UNSIGNED NOT NULL COMMENT 'id поста',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukCategoryPost UNIQUE KEY (id_category,id_page),
    INDEX ixIdCategory(id_category),
    CONSTRAINT fkCPCategory FOREIGN KEY (id_category)
            REFERENCES categories (id) ON DELETE CASCADE,
    INDEX ixIdPage(id_page),
    CONSTRAINT fkCPPage FOREIGN KEY (id_page)
            REFERENCES page (id) ON DELETE CASCADE
)ENGINE = INNODB;

-- Таблица action.

CREATE TABLE IF NOT EXISTS action
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_mcm_type INT(10) UNSIGNED NOT NULL COMMENT 'id в mcm_type ',
    class VARCHAR(60) NOT NULL COMMENT 'Имя класса',
    method VARCHAR(60) NULL DEFAULT NULL COMMENT 'Имя метода',
    parent VARCHAR(60) NULL DEFAULT NULL COMMENT 'Имя родительского модуля, нужно для моделей и контроллеров',
    admin INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    description VARCHAR(255) NULL DEFAULT NULL COMMENT 'Описание действия',
    PRIMARY KEY (id), CONSTRAINT ukTypeClassMethodAdmin UNIQUE KEY (id_mcm_type,class,method,parent,admin),
    INDEX ixIdType(id_mcm_type),
    CONSTRAINT fkActionMcmType FOREIGN KEY (id_mcm_type)
            REFERENCES mcm_type (id)
)ENGINE = INNODB;

-- Таблица action_list в ней связывается URL и action.

CREATE TABLE IF NOT EXISTS action_list
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_url INT(10) UNSIGNED NOT NULL COMMENT 'id в url',
    id_action INT(10) UNSIGNED NOT NULL COMMENT 'id в action',
    PRIMARY KEY (id), CONSTRAINT ukUrlAction UNIQUE KEY (id_url,id_action),
    INDEX ixIdUrl(id_url),
    INDEX ixIdAction(id_action),
    CONSTRAINT fkActionListUrl FOREIGN KEY (id_url)
            REFERENCES url (id) ON DELETE CASCADE,
    CONSTRAINT fkActionListAction FOREIGN KEY (id_action)
            REFERENCES action (id) ON DELETE CASCADE
)ENGINE = INNODB;

-- Таблица action_params в ней начальные параметры для action связанных в action_list.

CREATE TABLE IF NOT EXISTS action_params
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_action_list INT(10) UNSIGNED NOT NULL COMMENT 'id в action_list',
    param TEXT NULL DEFAULT NULL COMMENT 'параметр для функции',
    PRIMARY KEY (id),
    INDEX ixIdActionList(id_action_list),
    CONSTRAINT fkActionParamsActionList FOREIGN KEY (id_action_list)
            REFERENCES action_list (id) ON DELETE CASCADE
)ENGINE = INNODB;

-- Типы пользователей

CREATE TABLE IF NOT EXISTS user_type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа',
    type VARCHAR(30) NOT NULL COMMENT 'Типы пользователя',
    description TEXT NULL COMMENT 'Описание типа',
    legal INT(1) NOT NULL COMMENT '1 = юридическое лицо',
    employer INT(1) NOT NULL DEFAULT 1 COMMENT '1 = работодатель',
    PRIMARY KEY (id), CONSTRAINT ukType UNIQUE KEY (type)
)ENGINE = INNODB, COMMENT = "Типы данных пользователя";


-- Пользователи

CREATE TABLE IF NOT EXISTS user
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID Пользователя',
    email VARCHAR(60) NOT NULL COMMENT 'Email адрес это и логин',
    second_email VARCHAR(60) NULL DEFAULT NULL COMMENT 'Дополительная почта',
    pass CHAR(32) NOT NULL COMMENT 'Пароль',
    id_user_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'id типа пользователя',
    registered TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время регистрации',
    activation VARCHAR(60) NULL DEFAULT NULL COMMENT 'Ключ активации',
    status INT(1) NOT NULL DEFAULT 1 COMMENT '1 = пользователь включен',
    logo VARCHAR(60) NULL DEFAULT NULL COMMENT 'адрес на logo',
    count_view INT(10) UNSIGNED NOT NULL DEFAULT 0 NULL COMMENT 'Количество просмотров',
    PRIMARY KEY (id), CONSTRAINT ukemail UNIQUE KEY (email),
    INDEX ixIdUserType(id_user_type),
    CONSTRAINT fkIdUserUserType FOREIGN KEY (id_user_type)
            REFERENCES user_type (id) 
)ENGINE = INNODB;

-- Любые дополнительные поля пользователя.

CREATE TABLE IF NOT EXISTS fields_user
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID связи контент - тип',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID в связанной таблице',
    id_name INT(10) UNSIGNED NOT NULL COMMENT 'Имя поля в таблице fields_name',
    var VARCHAR(255) NULL DEFAULT NULL COMMENT 'Значения поля, либо var или text',
    text TEXT NULL DEFAULT NULL COMMENT 'Значения поля, либо var или text',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukUserNameVar UNIQUE KEY (id_user, id_name, var),
    INDEX ixIdUser(id_user),
    CONSTRAINT fkFieldsUserUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    INDEX ixIdName(id_name),
    CONSTRAINT fkFieldsUserName FOREIGN KEY (id_name)
            REFERENCES fields_name (id)
)ENGINE = INNODB;
-- Сессия пользователей

CREATE TABLE IF NOT EXISTS session
(
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID в связанной таблице',
    name VARCHAR(30) NOT NULL COMMENT 'Имя значения',
    value TEXT NULL DEFAULT NULL COMMENT 'Значение',
    PRIMARY KEY (id_user, name),
    INDEX ixIdUser(id_user),
    CONSTRAINT fkSeesionIDUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB;

-- Токены аутентификации.

CREATE TABLE IF NOT EXISTS tokens
(
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID в связанной таблице',
    token CHAR(32) NOT NULL COMMENT 'Токен',
    lifetime TIMESTAMP NOT NULL COMMENT 'Время жизни',
    PRIMARY KEY (id_user),
    INDEX ixIdUser(id_user),
    CONSTRAINT fkTokenUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB;

-- Пользовательские страницы

CREATE TABLE IF NOT EXISTS user_page
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страницы',
    url INT(10) UNSIGNED NOT NULL COMMENT 'id URL есть он есть',
    title VARCHAR(255) NULL DEFAULT NULL COMMENT 'Заговолок страницы',
    status INT(1) NOT NULL DEFAULT 1 COMMENT 'Отключить или нет',
    content TEXT NULL DEFAULT NULL COMMENT 'Содержимое',
    content_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID типа контента',
    user_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID типа пользователя',
    PRIMARY KEY (id), CONSTRAINT ukUrl UNIQUE KEY (url, user_type),
    INDEX ixContentType (content_type),
    INDEX ixUserType (user_type),
    CONSTRAINT fkUserPageContentType FOREIGN KEY (content_type)
            REFERENCES content_type (id),
    CONSTRAINT fkUserPageUserType FOREIGN KEY (user_type)
            REFERENCES user_type (id),
    INDEX ixIdURL(url),
    CONSTRAINT fkUserPageURL FOREIGN KEY (url)
            REFERENCES url (id)
)ENGINE = INNODB;

/*
    ФИНАНСОВАНЯ СИСТЕМА
*/

-- Финансы аккаунта

CREATE TABLE IF NOT EXISTS accounts
(
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID в связанной таблице',
    seor DECIMAL(10,3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Seor монет на аккаунте',
    days INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Сколько бесплатных дней',
    clicks INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных кликов по объявлениям',
    adc INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных объявлений',
    took_days TIMESTAMP NULL DEFAULT NULL COMMENT 'Когда в последний раз снимали дни, NULL значит дней нету и нужно снять как появятся',
    message INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Количество сообщений, добавляется автоматически через тригер',
    notification INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Количество уведомлений, добавляется автоматически через тригер',
    expiration TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата истечения оплаты аккаунта',
    complete INT(1) NOT NULL DEFAULT 0 COMMENT 'Аккаунть польностью заполнен и готов к использованию.',
    birthday DATE NULL DEFAULT NULL COMMENT "День рождения",
    name VARCHAR(80) NULL DEFAULT NULL COMMENT 'Имя компании или человека',
    id_country INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID страны',
    PRIMARY KEY (id_user),
    INDEX ixIdUser(id_user),
    CONSTRAINT fkAccountsUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
    INDEX ixIdCountry(id_country),
    CONSTRAINT fkAccountsCountry FOREIGN KEY (id_country)
            REFERENCES country_code (id)
)ENGINE = INNODB;

-- Пополнение аккаунта

-- Система оплаты pay_type
CREATE TABLE IF NOT EXISTS pay_type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа',
    type VARCHAR(30) NOT NULL COMMENT 'Типы оплаты',
    title VARCHAR(60) NULL COMMENT 'Описание типа',
    PRIMARY KEY (id), CONSTRAINT ukType UNIQUE KEY (type)
)ENGINE = INNODB, COMMENT = "Типы оплаты";

-- Имена валют
CREATE TABLE IF NOT EXISTS currency_name 
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа',
    name VARCHAR(10) NOT NULL COMMENT 'Имя валюты',
    title VARCHAR(60) NULL DEFAULT NULL COMMENT 'Описание',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (name)
)ENGINE = INNODB;

-- Валюты
CREATE TABLE IF NOT EXISTS currency 
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа',
    id_currency_name INT(10) UNSIGNED NOT NULL COMMENT 'ID в currency_name',
    rate DECIMAL(10,3) NOT NULL DEFAULT 0 COMMENT 'Цена',
    date TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Время изменрения',
    PRIMARY KEY (id),
    INDEX ixIdCurrency(id_currency_name),
    CONSTRAINT fkIdCurrencyCurrencyName FOREIGN KEY (id_currency_name)
            REFERENCES currency_name (id)
)ENGINE = INNODB, COMMENT = "Валюты, цена в гривнах";

-- Соотношение валюты по отношению к SEOR

CREATE TABLE IF NOT EXISTS currency_rate
(
    id_currency INT(10) UNSIGNED NOT NULL COMMENT 'ID в currency',
    for_currency INT(10) UNSIGNED NOT NULL COMMENT 'ID в currency с которым идет сравнение',
    rate DECIMAL(10,3) NOT NULL DEFAULT 0 COMMENT 'Соотношения',
    PRIMARY KEY (id_currency),
    INDEX ixIdCurrency(id_currency),
    INDEX ixForCurrency(for_currency),
    CONSTRAINT fkIdCurrencyRateCurrency FOREIGN KEY (id_currency)
            REFERENCES currency (id) ON DELETE CASCADE,
    CONSTRAINT fkIdCurrencyRateForCurrency FOREIGN KEY (for_currency)
            REFERENCES currency (id)
)ENGINE = INNODB, COMMENT = "Валюты, цена в гривнах";

-- Логи всех оплат и начислений 
/*
Осноыные поля
amount  - сумма перевода
cmd – тип перевода;
business – E-mail от аккаунта Paypal продавца;
currency_code
return 
cancel_return
undefined_quantity - количество пробуренных товаров
item_name
notify_url - возврат результата

*/
-- Логи входа, какой агент и ip адрес

CREATE TABLE IF NOT EXISTS logs_login
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    user_agent VARCHAR(120) NULL DEFAULT NULL COMMENT 'USER AGENT',
    ip VARCHAR(40) NULL DEFAULT NULL COMMENT 'IP адрес',
    count INT(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Количество входов',
    PRIMARY KEY (id), CONSTRAINT ukIUI UNIQUE KEY (id_user, user_agent, ip),
    INDEX ixUser(id_user),
    CONSTRAINT fkLogsLoginUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Логи входа, какой агент и ip адрес";

-- Логи активации

CREATE TABLE IF NOT EXISTS logs_activation
(
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    activation VARCHAR(40) NULL DEFAULT NULL COMMENT 'Ключ активации',
    time TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Время активации',
    PRIMARY KEY (id_user),
    CONSTRAINT fkLogsActivationUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Каким ключом был активирован";

ALTER TABLE logs_activation ADD time TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Время активации';

-- Логи времени входа

CREATE TABLE IF NOT EXISTS logs_time
(
    id_logs_login INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    time TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Время входа',
    CONSTRAINT fkLogsTimeUser FOREIGN KEY (id_logs_login)
            REFERENCES logs_login(id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Время входа пользователя";


-- Типы уведомлений

CREATE TABLE IF NOT EXISTS notification_type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    type VARCHAR(30) NOT NULL COMMENT 'Типы уведомлений',
    title VARCHAR(60) NULL COMMENT 'Описание типа',
    PRIMARY KEY (id), CONSTRAINT ukType UNIQUE KEY (type)
)ENGINE = INNODB, COMMENT = "Типы уведомлений";

-- Уведомления пользователей

CREATE TABLE IF NOT EXISTS notification
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    title VARCHAR(60) NOT NULL COMMENT 'Заголовок',
    content TEXT NOT NULL COMMENT 'Содержимое',
    time TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Дата уведомления',
    id_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Тип объявления',
    seen INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Просмотрено ли 1=просмотрено',
    PRIMARY KEY (id),
    CONSTRAINT fkNotUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkNotType FOREIGN KEY (id_type)
            REFERENCES notification_type (id)
)ENGINE = INNODB, COMMENT = "Уведомления пользователей";

-- Уведомления пользователей

CREATE TABLE IF NOT EXISTS ads
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    title VARCHAR(255) NOT NULL COMMENT 'Заголовок',
    salary INT(10) UNSIGNED NOT NULL COMMENT 'Оклад',
    id_currency INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'id валюты',
    description TEXT NOT NULL COMMENT 'Содержимое',
    time TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата создания, появляется только после модерации',
     INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = отключено, 1 = включено, 2 = удалено',
    approved INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = не проверено, 1 = проверено, 2 = не прошло модерацию',
    id_country INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID страны',
    seen INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Просмотрел ли модератор 0 = не просмотрено',
    pay INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Оплачена ли вакансия',
    time_create TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания, появляется только после модерации',
    count_view INT(10) UNSIGNED NOT NULL DEFAULT 1 NULL COMMENT 'Количество просмотров',
    PRIMARY KEY (id),
    INDEX ixUser(id_user),
    INDEX ixCurrencyName(id_currency),
    INDEX ixCountry(id_country),
    INDEX ixCountry(status),
    INDEX ixTime(time),
    CONSTRAINT fkAdsUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkAdsIdCurrencyName FOREIGN KEY (id_currency)
            REFERENCES currency_name (id),
    CONSTRAINT fkADSCountry FOREIGN KEY (id_country)
            REFERENCES country_code (id)
)ENGINE = INNODB, COMMENT = "Объявления";

-- Подсчет объявлений

CREATE TABLE IF NOT EXISTS ads_count
(
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    `all` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Все объявлени',
    active INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Активны',
    disabled INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Отключенных',
    moder INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'На модерации',
    draft INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'В черновике',
    PRIMARY KEY (id_user),
    CONSTRAINT fkAdsCountUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Подсчет объявлений";


-- Объявления и специализации

CREATE TABLE IF NOT EXISTS ads_specialization
(
    id_ads INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_specialization INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID специализации',
    PRIMARY KEY (id_ads, id_specialization),
    INDEX ixAds(id_ads),
    INDEX ixSpecialization(id_specialization),
    CONSTRAINT fkAdsSpecAds FOREIGN KEY (id_ads)
            REFERENCES ads (id) ON DELETE CASCADE,
    CONSTRAINT fkAdsSpecSpec FOREIGN KEY (id_specialization)
            REFERENCES specialization_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Объявления и специализации";


-- Объявления и языки

CREATE TABLE IF NOT EXISTS ads_language
(
    id_ads INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_language INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID языка',
    PRIMARY KEY (id_ads, id_language),
    INDEX ixAds(id_ads),
    INDEX ixLanguage(id_language),
    CONSTRAINT fkAdsLangAds FOREIGN KEY (id_ads)
            REFERENCES ads (id) ON DELETE CASCADE,
    CONSTRAINT fkAdsLandLand FOREIGN KEY (id_language)
            REFERENCES language_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Объявления и языки";



-- Промокоды

CREATE TABLE IF NOT EXISTS promo
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    promo CHAR(16) NOT NULL COMMENT 'Промокод',
    time TIMESTAMP NULL DEFAULT NULL COMMENT 'Срок годности промокода, NULL = бессрочно',
    seor DECIMAL(10,3) NOT NULL DEFAULT 0 COMMENT 'Seor монет',
    days INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Сколько бесплатных дней',
    clicks INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных кликов по объявлениям',
    adc INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных объявлений',
    status INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0 = промокод не активен ',
    once INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 = одноразовый промокод',
    PRIMARY KEY (id), CONSTRAINT ukPromo UNIQUE KEY (promo)
)ENGINE = INNODB, COMMENT = "Промокоды";

-- Логи активации промокодов

CREATE TABLE IF NOT EXISTS logs_promo
(
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    id_promo INT(10) UNSIGNED NOT NULL COMMENT 'id промокода',
    time TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Время активации',
    PRIMARY KEY (id_user, id_promo),
    CONSTRAINT fkLogsPromoUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkLogsPromoPromo FOREIGN KEY (id_promo)
            REFERENCES promo (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Какой пользователь какой промокод использовал";

-- Логи удаления аккаунтов

CREATE TABLE IF NOT EXISTS logs_drop
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID лога',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    seor DECIMAL(10,3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Seor монет на аккаунте',
    days INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Сколько бесплатных дней',
    clicks INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных кликов по объявлениям',
    ads INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных объявлений',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время удаления',
    PRIMARY KEY (id),
    CONSTRAINT fkLogsDropUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Логи удаления аккаунтов";

-- Коды языков

CREATE TABLE IF NOT EXISTS language_code
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID языка',
    title VARCHAR(15) NOT NULL COMMENT 'Название языка',
    code CHAR(3) NOT NULL COMMENT 'Код языка',
    PRIMARY KEY (id), UNIQUE KEY(code)
)ENGINE = INNODB, COMMENT = "Коды языков";

-- Названия зыков на языке пользователя

CREATE TABLE IF NOT EXISTS language
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID языка',
    name VARCHAR(20) NOT NULL COMMENT 'Название языка',
    id_code INT(10) UNSIGNED NOT NULL COMMENT 'ID языка в language_code для указания',
    id_code_translate INT(10) UNSIGNED NOT NULL COMMENT 'ID языка в language_code на каком языке название',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция языка в выдаче',
    PRIMARY KEY (id), UNIQUE KEY(id_code, id_code_translate),
    INDEX ixIdCode(id_code),
    INDEX ixIdCodeTranslate(id_code_translate),
    CONSTRAINT fkLaguageCode FOREIGN KEY (id_code)
            REFERENCES language_code (id) ON DELETE CASCADE,
    CONSTRAINT fkLaguageCodeTranslate FOREIGN KEY (id_code_translate)
            REFERENCES language_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Переводы языков";

-- Коды стран

CREATE TABLE IF NOT EXISTS country_code
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID страны',
    title VARCHAR(15) NOT NULL COMMENT 'Название страны',
    phone VARCHAR(5) NOT NULL COMMENT 'Код телефона',
    PRIMARY KEY (id), UNIQUE KEY(title)
)ENGINE = INNODB, COMMENT = "Коды стран";

-- Названия стран на языке пользователя

CREATE TABLE IF NOT EXISTS country
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID языка',
    name VARCHAR(20) NOT NULL COMMENT 'Название языка',
    id_country_code INT(10) UNSIGNED NOT NULL COMMENT 'ID языка в language_code для указания',
    id_code_translate INT(10) UNSIGNED NOT NULL COMMENT 'ID языка в language_code на каком языке название',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция языка в выдаче',
    PRIMARY KEY (id), CONSTRAINT ukCountryTraslate UNIQUE KEY(id_country_code, id_code_translate), CONSTRAINT ukCountryName UNIQUE KEY(name, id_code_translate),
    INDEX ixIdCountryCode(id_country_code),
    INDEX ixIdCodeTranslate(id_code_translate),
    CONSTRAINT fkCountryCode FOREIGN KEY (id_country_code)
            REFERENCES country_code (id) ON DELETE CASCADE,
    CONSTRAINT fkCountryCodeTranslate FOREIGN KEY (id_code_translate)
            REFERENCES language_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Переводы стран";
/*
INSERT INTO country (name, id_country_code, id_code_translate)
    SELECT title AS name, id AS id_country_code, 36 AS id_code_translate
        FROM country_code;*/

-- Коды специализаций

CREATE TABLE IF NOT EXISTS specialization_code
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID специализации',
    title VARCHAR(50) NOT NULL COMMENT 'Название специализации',
    PRIMARY KEY (id), UNIQUE KEY(title)
)ENGINE = INNODB, COMMENT = "Коды специализаций";

-- Специализация

CREATE TABLE IF NOT EXISTS specialization(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID специализации',
    name VARCHAR(20) NOT NULL COMMENT 'Название специлазиции на языке пользователя',
    id_specialization_code INT(10) UNSIGNED NOT NULL COMMENT 'ID специализации',
    id_code_translate INT(10) UNSIGNED NOT NULL COMMENT 'ID языка в language_code на каком языке название',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция в выдаче',
    PRIMARY KEY (id), CONSTRAINT ukSpecializationTraslate UNIQUE KEY(name, id_code_translate),
    INDEX ixIdSpecCode(id_specialization_code),
    INDEX ixIdCodeTranslate(id_code_translate),
    CONSTRAINT fkSpecCode FOREIGN KEY (id_specialization_code)
            REFERENCES specialization_code (id) ON DELETE CASCADE,
    CONSTRAINT fkSpecCodeTranslate FOREIGN KEY (id_code_translate)
            REFERENCES language_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Переводы специализаций";

-- Тех поддержка accounts support тут создаются тикеты

CREATE TABLE IF NOT EXISTS accounts_support
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID аккаунта',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    title VARCHAR(255) NOT NULL COMMENT 'Тема обращения',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время открытия тикета',
    last_activ TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'Последняя активность',
    status INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0 = тикет закрыт',
    new_admin INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Новые сообщения для админа',
    new_user INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Новые сообщения для пользователя',
    PRIMARY KEY (id),
    INDEX ixUser (id_user),
    CONSTRAINT fkAccSuppUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Тикеты тех поддержки";

-- Тех поддержка ответы на тикеты

CREATE TABLE IF NOT EXISTS support_message
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID аккаунта',
    id_accounts_support INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    message TEXT NOT NULL COMMENT 'Сообщение',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время сообщения',
    id_admin_user INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'id административного пользователя, который отвечает на обращение',
    seen INT(1) UNSIGNED NULL DEFAULT 1 COMMENT '1 = администратор не видел, 2 = пользователь не видел NULL = сообщение просмотрено',
    PRIMARY KEY (id),
    INDEX ixAccSupport (id_accounts_support),
    INDEX ixAdminUser (id_admin_user),
    CONSTRAINT fkSuppMessAccSupp FOREIGN KEY (id_accounts_support)
            REFERENCES accounts_support (id) ON DELETE CASCADE,
    CONSTRAINT fkSuppMessAdmUser FOREIGN KEY (id_admin_user)
            REFERENCES admin_user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Ответы на тикеты";


-- Покупка вакансии.

CREATE TABLE IF NOT EXISTS pay_ads
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID покупки',
    id_ads INT(10) UNSIGNED NOT NULL COMMENT 'ID вакансии',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID покупателя',
    id_user_employer INT(10) UNSIGNED NOT NULL COMMENT 'ID продавца',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время покупки',
    PRIMARY KEY (id), CONSTRAINT ukAdsUser UNIQUE KEY (id_ads, id_user),
    INDEX ixAds (id_ads),
    INDEX ixUser (id_user),
    INDEX ixUserEmpl (id_user_employer),
    CONSTRAINT fkPayAdsAds FOREIGN KEY (id_ads)
            REFERENCES ads (id) ON DELETE CASCADE,
    CONSTRAINT fkPayAdsUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkPayAdsUserEmp FOREIGN KEY (id_user_employer)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Покупка вакансии";

-- Покупка контактов.

CREATE TABLE IF NOT EXISTS pay_contacts
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID покупки',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID покупателя',
    id_user_employer INT(10) UNSIGNED NOT NULL COMMENT 'ID продавца',
    time TIMESTAMP NULL DEFAULT NULL COMMENT 'Время покупки',
    expiration TIMESTAMP NULL DEFAULT NULL COMMENT 'Срок годности',
    hours INT(10) UNSIGNED NOT NULL DEFAULT 12 COMMENT 'На сколько часов открывать',
    PRIMARY KEY (id), CONSTRAINT ukAdsUser UNIQUE KEY (id_user, id_user_employer),
    INDEX ixUser (id_user),
    INDEX ixUserEmpl (id_user_employer),
    CONSTRAINT fkPayContactUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkPayContactUserEmp FOREIGN KEY (id_user_employer)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Покупка контактов (время их отображения)";

-- Описание внутренних операций.

CREATE TABLE IF NOT EXISTS orders_detail
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    name VARCHAR(60) NOT NULL COMMENT 'Техническое имя',
    description VARCHAR(255) NOT NULL COMMENT 'Описание операции',
    PRIMARY KEY (id)
)ENGINE = INNODB, COMMENT = "Описание операций";

-- Описание внутренних операций.

CREATE TABLE IF NOT EXISTS orders_action
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    name VARCHAR(60) NOT NULL COMMENT 'Техническое имя операции',
    description VARCHAR(255) NOT NULL COMMENT 'Описание операции',
    PRIMARY KEY (id)
)ENGINE = INNODB, COMMENT = "Действия операций";

-- Внутренних операций.

CREATE TABLE IF NOT EXISTS orders
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID покупки',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID покупателя',
    id_orders_detail INT(10) UNSIGNED NOT NULL COMMENT 'Описание операции',
    amount DECIMAL(10,3) NOT NULL COMMENT 'Сумма',
    state INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 - успешно 0 - заблокировано, например недостаточно средств',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время покупки',
    transaction CHAR(32) NOT NULL COMMENT 'Код транзакции',
    PRIMARY KEY (id),
    INDEX ixUser (id_user),
    INDEX ixOrderDetail (id_orders_detail),
    CONSTRAINT fkOrdersUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkOrdersDetail FOREIGN KEY (id_orders_detail)
            REFERENCES orders_detail (id)
)ENGINE = INNODB, COMMENT = "Все внутренние финансовые операции";

-- Внешние операций.

CREATE TABLE IF NOT EXISTS orders_pay
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID заказа',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID покупателя',
    id_orders_detail INT(10) UNSIGNED NOT NULL COMMENT 'ID описание операции',
    amount DECIMAL(10,3) NOT NULL COMMENT 'Сумма',
    id_currency INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'id валюты',
    state INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 - успешно 0 - заблокировано, например недостаточно средств',
    id_pay_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID типа оплаты, заполнячется после оплаты',
    id_order_action INT(10) UNSIGNED NOT NULL COMMENT 'ID действия после покупки',
    time TIMESTAMP NULL DEFAULT NULL COMMENT 'Время покупки',
    params TEXT NULL DEFAULT NULL COMMENT 'Параметры платежной системы', 
    PRIMARY KEY (id),
    INDEX ixUser (id_user),
    INDEX ixPayType (id_pay_type),
    INDEX ixOrderAction (id_order_action),
    INDEX ixIdCurrency (id_currency),
    INDEX ixOrderDetail (id_orders_detail),
    CONSTRAINT fkOrdersPayUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkOrdersPayPayType FOREIGN KEY (id_pay_type)
            REFERENCES pay_type (id),
    CONSTRAINT fkOrdersPayAction FOREIGN KEY (id_order_action)
            REFERENCES orders_action (id),
    CONSTRAINT fkOrderPayCurrency FOREIGN KEY (id_currency)
            REFERENCES currency (id),
    CONSTRAINT fkOrderPayDetail FOREIGN KEY (id_orders_detail)
            REFERENCES orders_detail (id)
)ENGINE = INNODB, COMMENT = "Банковские операции";


-- Типы в прайсе.

CREATE TABLE IF NOT EXISTS price_type
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID покупки',
    name VARCHAR(30) NOT NULL COMMENT 'Техническое имя',
    description VARCHAR(255) NOT NULL COMMENT 'Описание',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (name)
   
)ENGINE = INNODB, COMMENT = "Типы прайса, как ads, account";

-- Типы в прайсе.

CREATE TABLE IF NOT EXISTS price
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID покупки',
    id_price_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа прайса',
    name VARCHAR(30) NOT NULL COMMENT 'Техническое имя',
    title VARCHAR(120) NOT NULL COMMENT 'Заголовок',
    description VARCHAR(255) NULL DEFAULT NULL COMMENT 'Описание',
    amount DECIMAL(10,3) UNSIGNED NOT NULL COMMENT 'Сумма',
    clicks INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных кликов по объявлениям',
    ads INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Бесплатных объявлений',
    PRIMARY KEY (id), CONSTRAINT ukName UNIQUE KEY (id_price_type, name),
    INDEX ixPriceType(id_price_type),
    CONSTRAINT fkPriceType FOREIGN KEY (id_price_type)
            REFERENCES price_type (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Прайс";

-- Уточнение прайса.

CREATE TABLE IF NOT EXISTS price_clarification
(
    id_price INT(10) UNSIGNED NOT NULL COMMENT 'ID прайса',
    id_user_type INT(10) UNSIGNED NOT NULL COMMENT 'ID типа пользователя',
    amount DECIMAL(10,3) UNSIGNED NULL DEFAULT NULL COMMENT 'Сумма',
    clicks INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Бесплатных кликов по объявлениям',
    adc INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Бесплатных объявлений',
    PRIMARY KEY (id_price, id_user_type),
    CONSTRAINT fkPriceClarifPrice FOREIGN KEY (id_price)
            REFERENCES price (id) ON DELETE CASCADE,
    CONSTRAINT fkPriceClarifUserType FOREIGN KEY (id_user_type)
            REFERENCES user_type (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Уточнение для прайса, если мы хотим задать сумму для конкретного типа пользователя";

-- Заметки модератора.

CREATE TABLE IF NOT EXISTS moderator_notes
(
   id INT(10) UNSIGNED NULL AUTO_INCREMENT COMMENT 'ID', 
   id_ads INT(10) UNSIGNED NULL COMMENT 'ID объявления, если заполнено, то заметка для объявления', 
   id_user INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID пользователя, если заполнено, то заметка для пользователя',
   note TEXT NOT NULL COMMENT 'Заметка',
   PRIMARY KEY (id),
   INDEX ixIdAds(id_ads),
   INDEX ixIdUser(id_user),
   CONSTRAINT fkModerNoteAds FOREIGN KEY (id_ads)
            REFERENCES ads (id) ON DELETE CASCADE,
   CONSTRAINT fkModerNoteUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Заметки модераторов по поводу не правильно заполненного объявления или аккаунта";

-- Номера телефонов

CREATE TABLE IF NOT EXISTS phone
(
    id INT(10) UNSIGNED NULL AUTO_INCREMENT COMMENT 'ID',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'id пользователя',
    id_country_code INT(10) UNSIGNED NOT NULL COMMENT 'id страны',
    phone VARCHAR(20) NOT NULL COMMENT 'номер телефона',
    position INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Позиция',
    PRIMARY KEY (id), CONSTRAINT ukUserCount UNIQUE KEY (id_user, id_country_code, phone),
    INDEX ixIdUser(id_user),
    INDEX ixIdcountry(id_country_code),
    CONSTRAINT fkPhoneUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkPhoneCountryCode FOREIGN KEY (id_country_code)
            REFERENCES country_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Телефоны пользователей";

-- Пользователи и специализации

CREATE TABLE IF NOT EXISTS user_specialization
(
    id_user INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_specialization INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID специализации',
    PRIMARY KEY (id_user, id_specialization),
    INDEX ixUser(id_user),
    INDEX ixSpecialization(id_specialization),
    CONSTRAINT fkUserSpecUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkUserSpecSpec FOREIGN KEY (id_specialization)
            REFERENCES specialization_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Пользователи и специализации";


-- Пользователи и языки

CREATE TABLE IF NOT EXISTS user_language
(
    id_user INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    id_language INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'ID языка',
    PRIMARY KEY (id_user, id_language),
    INDEX ixUser(id_user),
    INDEX ixLanguage(id_language),
    CONSTRAINT fkUserLangUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkUserLandLand FOREIGN KEY (id_language)
            REFERENCES language_code (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Пользователи и языки";

-- Данные для верификации пользователей

CREATE TABLE IF NOT EXISTS user_verification
(
    id_user INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    detail TEXT NOT NULL COMMENT 'Данные документа',
    PRIMARY KEY (id_user),
    CONSTRAINT fkVerifUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Данные для верификации пользователей";



-- Покупка вакансии.

CREATE TABLE IF NOT EXISTS pay_user
(
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID покупки',
    id_user_worker INT(10) UNSIGNED NOT NULL COMMENT 'ID продавца',
    id_user INT(10) UNSIGNED NOT NULL COMMENT 'ID покупателя',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время покупки',
    PRIMARY KEY (id), CONSTRAINT ukAdsUser UNIQUE KEY (id_user_worker, id_user),
    INDEX ixUserWorker (id_user_worker),
    INDEX ixUser (id_user),
    CONSTRAINT fkPayUserWorker FOREIGN KEY (id_user_worker)
            REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT fkPayUserUser FOREIGN KEY (id_user)
            REFERENCES user (id) ON DELETE CASCADE
)ENGINE = INNODB, COMMENT = "Покупка контактов работника";

-- ТЕСТОВЫЙ ЗАПРОС

UPDATE ads
            SET 
                count_view = count_view + 1
            WHERE id = '30';


SELECT al.id_ads, al.id_language
                    FROM ads_language al
                    INNER JOIN language l ON al.id_language = l.id
                    WHERE id_ads IN('1','2','3')
                    ORDER BY l.position DESC;




SELECT a.id, a.id_user, a.title, a.salary, a.id_currency, a.description, a.time, a.status, a.approved, a.id_country, if(pa.id OR pc.id, 1, 0) AS pay, if(pc.id, TIMEDIFF(pc.expiration, NOW()), NULL) AS expiration
                    FROM ads a
                    LEFT JOIN pay_ads pa ON pa.id_ads = a.id AND pa.id_user = 8
                    LEFT JOIN pay_contacts pc ON pc.id_user = 8 AND pc.id_user_employer = a.id_user AND pc.expiration > NOW()
                    WHERE a.id = 2 AND a.approved = 1
                    \G

                    
SELECT p.id, pt.name AS type_name, p.name, p.title, 
    if(pc.amount IS NULL, p.amount, pc.amount) AS amount,
    if(pc.clicks IS NULL, p.clicks, pc.clicks) AS clicks,
    if(pc.adc IS NULL, p.adc, pc.adc) AS adc
    FROM price p
    INNER JOIN price_type pt ON pt.id = p.id_price_type
    LEFT JOIN price_clarification pc ON pc.id_price = p.id AND id_user_type = 1
    ORDER BY p.id;
/*
INSERT INTO specialization (name, id_specialization_code, id_code_translate)
    SELECT title AS name, id AS id_specialization_code, 36 AS id_code_translate
        FROM specialization_code;*/
/*-------------------------------*/

/*---------ТРИГЕРЫ--------------*/

-- Подсчет вакансий при обновлении

DROP TRIGGER IF EXISTS update_ads;
DELIMITER |
CREATE TRIGGER update_ads AFTER UPDATE ON ads 
    FOR EACH ROW
    BEGIN
        IF NEW.status != OLD.status OR NEW.approved != OLD.approved THEN
            INSERT INTO ads_count (id_user, `all`, active, disabled, moder, draft)
                (SELECT a.id_user, COUNT(al.id) AS `all`, COUNT(act.id) AS active, COUNT(dis.id) AS disabled, COUNT(moder.id) AS moder, COUNT(dra.id) AS draft
                   FROM ads a
                   LEFT JOIN ads al ON a.id = al.id AND al.status != 3
                   LEFT JOIN ads act ON a.id = act.id AND act.status = 1 AND act.approved = 1
                   LEFT JOIN ads dis ON a.id = dis.id AND dis.status = 0 AND dis.approved = 1
                   LEFT JOIN ads moder ON a.id = moder.id AND moder.status = 0 AND moder.approved != 1
                   LEFT JOIN ads dra ON a.id = dra.id AND dra.status = 3
                   WHERE a.id_user = NEW.id_user AND a.status != 2
                   LIMIT 1)
                   ON DUPLICATE KEY UPDATE `all` = VALUES(`all`), active = VALUES(active), disabled = VALUES(disabled), moder = VALUES(moder), draft = VALUES(draft);
        END IF;
    END|
DELIMITER ;

-- Подсчет вакансий при добавлении

DROP TRIGGER IF EXISTS insert_ads;
DELIMITER |
CREATE TRIGGER insert_ads AFTER INSERT ON ads 
    FOR EACH ROW
    BEGIN
        INSERT INTO ads_count (id_user, `all`, active, disabled, moder, draft)
            (SELECT a.id_user, COUNT(al.id) AS `all`, COUNT(act.id) AS active, COUNT(dis.id) AS disabled, COUNT(moder.id) AS moder, COUNT(dra.id) AS draft
               FROM ads a
               LEFT JOIN ads al ON a.id = al.id AND al.status != 3
               LEFT JOIN ads act ON a.id = act.id AND act.status = 1 AND act.approved = 1
               LEFT JOIN ads dis ON a.id = dis.id AND dis.status = 0 AND dis.approved = 1
               LEFT JOIN ads moder ON a.id = moder.id AND moder.status = 0 AND moder.approved != 1
               LEFT JOIN ads dra ON a.id = dra.id AND dra.status = 3
               WHERE a.id_user = NEW.id_user AND a.status != 2
               LIMIT 1)
               ON DUPLICATE KEY UPDATE `all` = VALUES(`all`), active = VALUES(active), disabled = VALUES(disabled), moder = VALUES(moder), draft = VALUES(draft);
    END|
DELIMITER ;

-- Подсчет вакансий при удалении

DROP TRIGGER IF EXISTS delete_ads;
DELIMITER |
CREATE TRIGGER delete_ads AFTER DELETE ON ads 
    FOR EACH ROW
    BEGIN
        INSERT INTO ads_count (id_user, `all`, active, disabled, moder, draft)
            (SELECT a.id_user, COUNT(al.id) AS `all`, COUNT(act.id) AS active, COUNT(dis.id) AS disabled, COUNT(moder.id) AS moder, COUNT(dra.id) AS draft
               FROM ads a
               LEFT JOIN ads al ON a.id = al.id AND al.status != 3
               LEFT JOIN ads act ON a.id = act.id AND act.status = 1 AND act.approved = 1
               LEFT JOIN ads dis ON a.id = dis.id AND dis.status = 0 AND dis.approved = 1
               LEFT JOIN ads moder ON a.id = moder.id AND moder.status = 0 AND moder.approved != 1
               LEFT JOIN ads dra ON a.id = dra.id AND dra.status = 3
               WHERE a.id_user = OLD.id_user AND a.status != 2
               LIMIT 1)
               ON DUPLICATE KEY UPDATE `all` = VALUES(`all`), active = VALUES(active), disabled = VALUES(disabled), moder = VALUES(moder), draft = VALUES(draft);
    END|
DELIMITER ;

-- Обновление соотношения валют

DROP TRIGGER IF EXISTS update_currency_rate;
DELIMITER |
CREATE TRIGGER update_currency_rate AFTER UPDATE ON currency 
    FOR EACH ROW
    BEGIN
        UPDATE currency_rate r
        INNER JOIN currency a ON r.id_currency = a.id
        INNER JOIN currency b ON r.for_currency = b.id
        SET r.rate = b.rate/a.rate;
    END|
DELIMITER ;


-- Вставка сообщения в support

DROP TRIGGER IF EXISTS insert_support;
DELIMITER |
CREATE TRIGGER insert_support AFTER INSERT ON support_message 
    FOR EACH ROW
    BEGIN
        IF NEW.id_admin_user IS NULL THEN
            UPDATE accounts_support 
            SET 
                new_admin = new_admin + 1,
                last_activ = NOW()
            WHERE id = NEW.id_accounts_support;
        ELSE
            UPDATE accounts_support 
            SET 
                new_user = new_user + 1,
                last_activ = NOW()
            WHERE id = NEW.id_accounts_support;
        END IF;
    END|
DELIMITER ;


-- Обнуляем значение просмотров для администратора или пользователя

DROP TRIGGER IF EXISTS update_support;
DELIMITER |
CREATE TRIGGER update_support AFTER UPDATE ON accounts_support 
    FOR EACH ROW
    BEGIN
        IF OLD.new_admin <> NEW.new_admin AND NEW.new_admin = 0 THEN
            UPDATE support_message 
            SET 
                seen = NULL
            WHERE id_accounts_support = NEW.id AND seen = 1;
        END IF;
        IF OLD.new_user <> NEW.new_user AND NEW.new_user = 0 THEN
            UPDATE support_message 
            SET 
                seen = NULL
            WHERE id_accounts_support = NEW.id AND seen = 2;
        END IF;
    END|
DELIMITER ;

-- Увеличивает значение уведомления на 1 у пользователя

DROP TRIGGER IF EXISTS update_user_notification;
DELIMITER |
CREATE TRIGGER update_user_notification AFTER INSERT ON notification 
    FOR EACH ROW
    BEGIN
        UPDATE accounts 
        SET 
            notification = notification + 1
        WHERE id_user = NEW.id_user;
    END|
DELIMITER ;

-- Обнуление просмотренных уведомлений у пользователя

DROP TRIGGER IF EXISTS update_accounts_notification;
DELIMITER |
CREATE TRIGGER update_accounts_notification AFTER UPDATE ON accounts 
    FOR EACH ROW
    BEGIN
        IF OLD.notification <> NEW.notification THEN
            UPDATE notification 
            SET 
                seen = 0
            WHERE id_user = NEW.id_user AND seen = 1;
        END IF;
    END|
DELIMITER ;
/*-------------------------------*/



-------------------------------------
     
CREATE  TABLE IF NOT EXISTS test (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    int_test UTC_TIMESTAMP NOT NULL DEFAULT TIMESTAMP COMMENT 'Имя метода (правила)',
    PRIMARY KEY (id)
)ENGINE = InnoDB;

-- ************************ВРЕМЯ**********************
/*set @@session.time_zone = '+01:00';*/

-----------------------
--LEFT JOIN url up ON up.id = u.id_canonical
SELECT jc.id, jc.id_table, jc.id_type, jc.css, jc.js
FROM js_css jc
INNER JOIN type t ON t.id = jc.id_type
WHERE t.type = "page" AND jc.id_table = 1;

SELECT id, id_category, id_page, position FROM category_page cp WHERE id_categpry IN ('6','7','8','9','11','12')

EXPLAIN
SELECT c.id, c.parent_id, u.login, DATE_FORMAT(c.date,"%Y-%m-%d") AS date, c.title, url.url, c.meta_title, c.description, r.id AS robots,r.robots AS robots_name, c.status, c.static, DATE_FORMAT(c.modified,"%Y-%m-%d") AS modified, c.content_type, ct.name AS file_name, ct.exp, ct.class, c.position
                FROM categories c
                INNER JOIN admin_user u ON c.id_admin_user = u.id
                INNER JOIN type t ON t.type = 'category'
                INNER JOIN robots r ON c.robots = r.id
                LEFT JOIN content_type ct ON c.content_type = ct.id
                LEFT JOIN url url ON c.url = url.id
                ORDER BY c.parent_id, c.position
\G


UPDATE session SET value = CASE
WHEN name = 'last_active' THEN '123' WHEN name = 'new' THEN '456' WHEN name = 'test' THEN 'testing'
END
WHERE name IN ('last_active','new','test') AND id_user = "8";