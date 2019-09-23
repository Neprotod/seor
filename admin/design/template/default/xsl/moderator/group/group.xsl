<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <!--
        Шаблон корневого элемента
    -->
    <xsl:param name="seesion_id" select="moderator/@session_id" />
    <xsl:template match="/">
        <!-- Переменная для переключателя -->
        <xsl:variable name="stat">
               <xsl:choose>
                    <xsl:when test="//status/perm">1</xsl:when>
                    <xsl:otherwise>0</xsl:otherwise>
                </xsl:choose>
        </xsl:variable>

        <div id="user_group" class="table_box">
            <div class="box">
                <a class="btn btn-outline-primary" href="{//moderator/@url}">К списку</a>
            </div>
            <form method="post" action="{moderator/@action}" data-toggle="validator" role="form">
                <input type="hidden" name="session_id" value="{$seesion_id}" />
                <div class="group_box card">
                    <!-- Запускает форму группы -->
                    <xsl:call-template name="group">
                        <xsl:with-param name="group" select = "moderator/group" />
                    </xsl:call-template>
                </div>
                <div class="permission_box card">
                    <h4>Правила</h4>
                    <span class="big">Правила могут быть либо только на разрешение, либо только на запрет использования</span>
                    <hr />
                    <div class="position-relative">
                        <span class="big">Разрешение или запрет</span>
                        <div class="status_control">
                            <input data-help="Переключает между режимами. Можно работать только в одном режиме, настройки предыдущего будут удалены." type="range" class="custom-range" name="status_control" min="0" max="1" step="1" value="{$stat}" />
                        </div>
                        <div id="for_close" data-curtail="Развернуть" class="btn btn-outline-secondary">Свернуть</div>
                    </div>
                    <!-- Для запрещения правил -->
                    <xsl:call-template name="ban">
                        <xsl:with-param name="status" select = "//status/ban" />
                    </xsl:call-template>
                    <!-- Для разрешения правил -->
                    <xsl:call-template name="permission">
                        <xsl:with-param name="status" select = "//status/perm" />
                    </xsl:call-template>
                    <div class="margin-t-10">
                        <button type="submit" class="btn btn-primary margin-auto">Применить</button>
                    </div>
                </div>
            </form>
        </div>
    </xsl:template>
    
    <!--
        Шаблон отображения группы
    -->
    <xsl:template name="group">
    
        <xsl:param name="group" />
        
        <xsl:variable name="id">
            <xsl:choose>
                <xsl:when test="$group/@id"><xsl:value-of select="$group/@id"/></xsl:when>
                <xsl:otherwise>new</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="for_h4">
            <xsl:choose>
                <xsl:when test="$id = 'new'">Создание группы</xsl:when>
                <xsl:otherwise>Описание группы</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <h4><xsl:value-of select="$for_h4"/></h4>
        <xsl:if test="$group/@id">
            <input type="hidden" name="group[{$group/@id}][id]" value="{$group/@id}" />
        </xsl:if>
        <div class="form-row">
            <div class="form-group col-md-6 control" data-help="Уникальное имя, используется в технических целях">
                <label>Техническое имя (type)</label>
                <input name="group[{$id}][type]" type="text" class="form-control" value="{$group/@type}" required="required" active-role="group_type" />
            </div>
            <div class="form-group col-md-6 control" data-help="Отображаемое имя группы (человекопонятное)">
                <label>Название (title)</label>
                <input name="group[{$id}][title]" type="text" class="form-control" value="{$group/title}" required="required" active-role="group_title" />
            </div> 
        </div>
        <div class="form-group">
            <label>Описание (description)</label>
            <input name="group[{$id}][description]" type="text" class="form-control" value="{$group/description}"  />
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Применить</button>
        </div>
    </xsl:template>
    
    <!--
        Шаблон отображения прав модуля
    -->
    <xsl:template match="module">
        <!-- переменная с пользовательскими правами -->
        <xsl:param name="status" />
        
        <!-- Ключ начального массива -->
        <xsl:param name="key" />
        
        <div class="module card card-hide" data-help="Можно свернуть">
            <div class="card-header"><xsl:value-of select="@class_name"/></div>
            <span class="card-title">
                <xsl:value-of select="description"/>
            </span>
            <div class="for_close card-body">
                <hr />
                <xsl:apply-templates select="method">
                    <xsl:with-param name="status" select="$status" />
                    <xsl:with-param name="key" select="$key" />
                </xsl:apply-templates>
                
                <xsl:if test="controller">
                    <div class="card control_model">
                        <div class="card-body">
                            <div class="card-header">Контроллеры</div>
                            <xsl:apply-templates select="controller">
                                <xsl:with-param name="status" select="$status" />
                                <xsl:with-param name="key" select="$key" />
                            </xsl:apply-templates>
                        </div>
                    </div>
                </xsl:if>
                <xsl:if test="model">
                    <div class="card control_model">
                        <div class="card-body">
                            <div class="card-header">Модели</div>
                            <xsl:apply-templates select="model ">
                                <xsl:with-param name="status" select="$status" />
                                <xsl:with-param name="key" select="$key" />
                            </xsl:apply-templates>
                        </div>
                    </div>
                </xsl:if>
            </div>
        </div>
    </xsl:template>
    
    <!--
        Шаблон если у контроллера есть права
    -->
    <xsl:template match="controller">
        <!-- переменная с пользовательскими правами -->
        <xsl:param name="status" />
        
        <!-- Ключ начального массива -->
        <xsl:param name="key" />
        
        <div class="controller">
            <span class="name card-subtitle mb-2 text-muted">
                (<xsl:value-of select="@class_name"/>)
                <xsl:value-of select="description"/>
            </span>
            <xsl:apply-templates select="method ">
                <xsl:with-param name="status" select="$status" />
                <xsl:with-param name="key" select="$key" />
            </xsl:apply-templates>
        </div>
    </xsl:template>
    
    <!--
        Шаблон если у модели есть права
    -->
    <xsl:template match="model">
        <!-- переменная с пользовательскими правами -->
        <xsl:param name="status" />
        
        <!-- Ключ начального массива -->
        <xsl:param name="key" />
        
        <div class="model">
            <span class="name card-subtitle mb-2 text-muted">
                (<xsl:value-of select="@class_name"/>)
                <xsl:value-of select="description"/>
            </span>
            <xsl:apply-templates select="method ">
                <xsl:with-param name="status" select="$status" />
                <xsl:with-param name="key" select="$key" />
            </xsl:apply-templates>
        </div>
    </xsl:template>
    
    <!--
        Шаблон прав
    -->
    <xsl:template match="method">
        <!-- переменная с пользовательскими правами -->
        <xsl:param name="status" />
        
        <!-- Ключ начального массива -->
        <xsl:param name="key" />
        
        <xsl:variable name="id" select="@id" />
        
       
        <div class="method">
            <label class="method-list row">
                <xsl:element name="input">
                    <xsl:attribute name="type">checkbox</xsl:attribute>
                    <xsl:attribute name="class">method_checkbox col</xsl:attribute>
                    <xsl:attribute name="name"><xsl:value-of select="concat($key,'[permission][',@id,'][id_permission]')"/></xsl:attribute>
                    <xsl:if test="$status/permission[@id_permission = $id]">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                    <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                </xsl:element>
                <div class="col padding">
                    <div class="head_perm">
                        <span class="string"><xsl:value-of select="@method"/></span>
                    </div>
                    <div class="footer_perm">
                        <xsl:value-of select="description"/>
                    </div>
                </div>
            </label>
            <div class="list-group">
                <xsl:apply-templates select="rule ">
                    <xsl:with-param name="status" select="$status" />
                    <xsl:with-param name="key" select="$key" />
                </xsl:apply-templates>
            </div>
        </div>
    </xsl:template>
    <!--
        Шаблон уточнения прав
    -->
    <xsl:template match="rule">
        <xsl:param name="status" />
        
        <!-- Ключ начального массива -->
        <xsl:param name="key" />
        
        <xsl:variable name="id" select="@id" />
        <label class="list-group-item rule">
            <div class="row">
                <xsl:element name="input">
                    <xsl:attribute name="type">checkbox</xsl:attribute>
                    <xsl:attribute name="class">rule_checkbox col</xsl:attribute>
                    <xsl:attribute name="name"><xsl:value-of select="concat($key,'[rule][',../@id,'][',@rule,'][id_rule]')"/></xsl:attribute>
                    <xsl:if test="$status/rule[@id_rule = $id]">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                    <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                </xsl:element>
                <div class="col padding">
                    <div class="head_perm">
                        <span class="string"><xsl:value-of select="@rule"/></span>
                    </div>
                    <div class="footer_perm">
                        <xsl:value-of select="description"/>
                    </div>
                </div>
            </div>
        </label>
    </xsl:template>
    
    <!--
        Шаблон запрета прав
    -->
    <xsl:template name="ban">
        <!-- переменная с пользовательскими правами -->
        <xsl:param name="status" />
        
        <div class="alert-danger permission collapse">
            <h6 class="text-center">Запрет использования</h6>
            <div class="column">
                <xsl:apply-templates select="moderator/module">
                    <xsl:with-param name="status" select = "$status" />
                    <!-- Ключ начального массива -->
                    <xsl:with-param name="key" select="'all[ban]'" />
                </xsl:apply-templates>
            </div>
        </div>
    </xsl:template>
    
    <!--
        Шаблон разрешения прав
    -->
    <xsl:template name="permission">
        <!-- Переменная с пользовательскими правами -->
        <xsl:param name="status" />
        
        <div class="alert-primary permission collapse">
            <h6 class="text-center">Разрешения использования</h6>
            <div class="column">
                <xsl:apply-templates select="moderator/module">
                    <xsl:with-param name="status" select="$status" />
                    <!-- Ключ начального массива -->
                    <xsl:with-param name="key" select="'all[perm]'" />
                </xsl:apply-templates>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>